<?php

namespace Racadtech\Udraw\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order\Item;
use Psr\Log\LoggerInterface;
use Racadtech\Udraw\Helper\Udraw;

class SalesModelServiceQuoteSubmitBeforeObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var Udraw
     */
    protected Udraw $udrawHelper;

    /**
     * @var Json
     */
    protected Json $serializer;

    public function __construct(
        LoggerInterface $logger,
        Udraw $udrawHelper,
        Json $serializer
    ) {
        $this->logger = $logger;
        $this->udrawHelper = $udrawHelper;

        $this->serializer = $serializer;
    }

    public function execute(Observer $observer)
    {
        try {
            $quote = $observer->getQuote();
            $order = $observer->getOrder();
            $quoteItems = [];

            // Map Quote Item with Quote Item Id
            foreach ($quote->getAllVisibleItems() as $quoteItem) {
                $quoteItems[$quoteItem->getId()] = $quoteItem;
            }

            foreach ($order->getAllVisibleItems() as $orderItem) {
                $quoteItemId = $orderItem->getQuoteItemId();
                $quoteItem = $quoteItems[$quoteItemId];

                $additionalOptions = $quoteItem->getOptionByCode('additional_options');
                if ($additionalOptions) {
                    // Get Order Item's other options
                    $options = $orderItem->getProductOptions();
                    // Set additional options to Order Item
                    $options['additional_options'] = $this->serializer->unserialize($additionalOptions->getValue());
                    $orderItem->setProductOptions($options);
                }

                $udrawDataOption = $quoteItem->getOptionByCode('udraw_data');
                if ($udrawDataOption) {
                    $options['udraw_data'] = $this->serializer->unserialize($udrawDataOption->getValue());
                    if (key_exists('udraw_designer_data', $options['udraw_data'])) {
                        $udrawDesignerData = $this->udrawHelper->convertEncodedUdrawDesignToObject($options['udraw_data']['udraw_designer_data']);
                        if ($udrawDesignerData !== null) {
                            $options['udraw_data']['udraw_designer_print'] = $this->udrawHelper->requestUdrawDesignPDF(
                                $this->udrawHelper->getUdrawSettingValue('udraw_api_key'),
                                $udrawDesignerData->designKey
                            );
                        }
                    }
                    $orderItem->setProductOptions($options);

                    if (key_exists('pricematrix_selected_options', $options['udraw_data'])) {
                        $udrawPriceMatrixSelectedOptions = json_decode(base64_decode($options['udraw_data']['pricematrix_selected_options']));

                        if (is_array($udrawPriceMatrixSelectedOptions)) {
                            // Update product SKU based on price matrix selections

                            // Loop through price matrix selections and see if 'BaseSKU' is set.  If 'BaseSKU' is defined
                            // we will override the product's base sku with the one defined in price matrix.
                            $foundBaseSku = false;
                            foreach ($udrawPriceMatrixSelectedOptions as $priceMatrixSelectedOption) {
                                if (property_exists($priceMatrixSelectedOption, "meta")) {
                                    if (is_array($priceMatrixSelectedOption->meta)) {
                                        foreach ($priceMatrixSelectedOption->meta as $priceMatrixSelectedMeta) {
                                            if ($priceMatrixSelectedMeta->key == "basesku") {
                                                // Found BaseSKU set, so we will override the product's SKU.
                                                $orderItem->setSku($priceMatrixSelectedMeta->value);
                                                $foundBaseSku = true;
                                                break;
                                            }
                                        }
                                    }
                                }
                                if ($foundBaseSku) {
                                    break;
                                } // no need to loop through anymore since 'BaseSKU' was found.
                            }

                            // Loop through price matrix selections and append to base sku.
                            foreach ($udrawPriceMatrixSelectedOptions as $priceMatrixSelectedOption) {
                                // Default empty sku placeholder if defined.
                                $defaultSku = $options['udraw_data']['udraw_pricematrix_default_sku'];
                                $updatedSku = ($defaultSku != "") ? $orderItem->getSku() . "-" . $defaultSku : "";
                                if (property_exists($priceMatrixSelectedOption, "meta")) {
                                    if (is_array($priceMatrixSelectedOption->meta)) {
                                        foreach ($priceMatrixSelectedOption->meta as $priceMatrixSelectedMeta) {
                                            if ($priceMatrixSelectedMeta->key == "sku") {
                                                $updatedSku = $orderItem->getSku() . "-" . $priceMatrixSelectedMeta->value;
                                            }
                                        }
                                    }
                                }

                                if ($updatedSku != "") {
                                    $orderItem->setSku($updatedSku);
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // catch error if any
            $this->logger->error($e->getMessage());
        }
    }
}
