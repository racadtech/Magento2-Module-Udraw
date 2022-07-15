<?php

namespace Racadtech\Udraw\Observer;

use Magento\Quote\Model\Quote\Item;

class CheckoutCartProductAddAfterObserver implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected \Psr\Log\LoggerInterface $logger;

    /**
     * @var \Racadtech\Udraw\Helper\Udraw
     */
    protected \Racadtech\Udraw\Helper\Udraw $udrawHelper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected \Magento\Framework\App\RequestInterface $request;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected \Magento\Framework\Serialize\Serializer\Json $serializer;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Racadtech\Udraw\Helper\Udraw $udrawHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->logger = $logger;
        $this->udrawHelper = $udrawHelper;
        $this->request = $request;

        $this->serializer = $serializer;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var Item $item */
        $item = $observer->getQuoteItem();

        $postParams = $this->request->getParams();

        $additionalOptions = [];

        if ($additionalOption = $item->getOptionByCode('additional_options')) {
            $additionalOptions = (array) $this->serializer->unserialize($additionalOption->getValue());
        }

        $udrawDataArray = [];

        if ($udrawReorderData = $item->getOptionByCode('udraw_reorder_data')) {
            $udrawDataArray = (array) $this->serializer->unserialize($udrawReorderData->getValue());
        }

        $udrawDataArray = $this->addUdrawPostData($udrawDataArray, 'udraw_designer_data', 'udraw_designer_data');
        $udrawDataArray = $this->addUdrawPostData($udrawDataArray, 'udraw_pricematrix_selected_saved', 'udraw_pricematrix_selected_saved');
        $udrawDataArray = $this->addUdrawPostData($udrawDataArray, 'udraw_pricematrix_qty', 'udraw_pricematrix_qty');
        $udrawDataArray = $this->addUdrawPostData($udrawDataArray, 'udraw_pricematrix_selected_options', 'pricematrix_selected_options');
        $udrawDataArray = $this->addUdrawPostData($udrawDataArray, 'udraw_pricematrix_price_breaks', 'udraw_pricematrix_price_breaks');
        $udrawDataArray = $this->addUdrawPostData($udrawDataArray, 'udraw_pricematrix_default_sku', 'udraw_pricematrix_default_sku');
        $udrawDataArray = $this->addUdrawPostData($udrawDataArray, 'udraw_pricematrix_product_preview', 'udraw_pricematrix_product_preview');
        $udrawDataArray = $this->addUdrawPostData($udrawDataArray, 'udraw_gosendex_uploaded_artwork', 'udraw_gosendex_uploaded_artwork');

        if (key_exists('udraw_designer_data', $postParams)) {
            // Placeholder if item is order. This will hold PDF print path.
            $udrawDataArray['udraw_designer_print'] = '';
        }

        if (key_exists('pricematrix_selected_options', $udrawDataArray)) {
            $udrawPriceMatrixSelectedOptions = json_decode(base64_decode($udrawDataArray['pricematrix_selected_options']));

            $priceMatrixOptions = [];
            if (is_array($udrawPriceMatrixSelectedOptions)) {

                // No need to record Qty as a product attribute as we will now specify qty directly on the product
                // line.  If we ever revert back, we can uncomment this condition below to include it again.
                //
                //if ($udrawPriceMatrixQty > 0) {
                //    $priceMatrixOptions['Qty'] = $udrawPriceMatrixQty;
                //}

                foreach ($udrawPriceMatrixSelectedOptions as $priceMatrixSelectedOption) {
                    $priceMatrixOptions[$priceMatrixSelectedOption->name] = $priceMatrixSelectedOption->value;
                }

                foreach ($priceMatrixOptions as $key => $value) {
                    if ($key == '' || $value == '') {
                        continue;
                    }

                    $additionalOptions[] = [
                        'label' => $key,
                        'value' => $value
                    ];
                }
            }
        }

        // Check to see if there is any uploaded artwork via GoSendEx.
        if (key_exists('udraw_gosendex_uploaded_artwork', $udrawDataArray)) {
            $goSendExUploadedArtwork = json_decode(base64_decode($udrawDataArray['udraw_gosendex_uploaded_artwork']));
            if ($goSendExUploadedArtwork != null) {
                $additionalOptions[] = [
                    'label' => __('Uploaded Artwork'),
                    'value' => $goSendExUploadedArtwork->filename
                ];
            }
        }

        if (count($udrawDataArray) > 0) {
            $item->addOption([
                'code' => 'udraw_data',
                'value' => $this->serializer->serialize($udrawDataArray)
            ]);
        }

        if (count($additionalOptions) > 0) {
            $item->addOption([
                'code' => 'additional_options',
                'value' => $this->serializer->serialize($additionalOptions)
            ]);
        }

        if (key_exists('udraw_pricematrix_price', $postParams) && key_exists('udraw_pricematrix_qty', $postParams)) {
            $udrawPriceMatrixPrice = floatval($postParams['udraw_pricematrix_price']);
            $udrawPriceMatrixQty = intval($postParams['udraw_pricematrix_qty']);

            $qtyBasePrice = $this->udrawHelper->calculateQtyPriceFromQuoteItem($item, $udrawPriceMatrixQty);
            $additionalPrice = $udrawPriceMatrixPrice - ($qtyBasePrice * $udrawPriceMatrixQty);

            $udrawDataArray['udraw_pricematrix_additional_price'] = floatval($additionalPrice);

            $item->addOption([
                'code' => 'udraw_data',
                'value' => $this->serializer->serialize($udrawDataArray)
            ]);

            //$item->setCustomPrice($udrawPriceMatrixPrice);
            //$item->setOriginalCustomPrice($udrawPriceMatrixPrice);
            // Use below to set qty and price per qty.
            $unitBasePrice = floatval($udrawPriceMatrixPrice / $udrawPriceMatrixQty);
            $item->setQty($udrawPriceMatrixQty);
            $item->setCustomPrice($unitBasePrice);
            $item->setOriginalCustomPrice($unitBasePrice);

            $item->setIsSuperMode(true);
        }

        /* TODO: */
        // Edit Cart - May need to remove option and readd them
        // Pre-fill remarks on product edit pages
        // Check for comparability with custom option
        return $this;
    }

    private function addUdrawPostData($udrawDataArray, $postParam, $udrawKeyName) : array
    {
        if (key_exists($postParam, $this->request->getParams())) {
            $udrawDataArray[$udrawKeyName] = $this->request->getParams()[$postParam];
        }
        return $udrawDataArray;
    }
}
