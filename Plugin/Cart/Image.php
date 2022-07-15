<?php

namespace Racadtech\Udraw\Plugin\Cart;

class Image
{
    protected \Racadtech\Udraw\Helper\Udraw $udrawHelper;
    protected \Magento\Catalog\Model\Product $product;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\Checkout\Model\Session $checkoutSession;

    public function __construct(
        \Racadtech\Udraw\Helper\Udraw $udrawHelper,
        \Magento\Catalog\Model\Product $product,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->udrawHelper = $udrawHelper;
        $this->product = $product;
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Responsible for changing mini cart item image.
     *
     * @param $subject
     * @param $proceed
     * @param $item
     * @return array
     */
    public function afterGetItemData($subject, $proceed, $item): array
    {
        $result = (!is_array($proceed)) ? $proceed($item) : $proceed;

        $udrawPreviewImage = $this->udrawHelper->getUdrawPreviewFromCartQuoteItem($item);
        if ($udrawPreviewImage != null) {
            $result['product_image']['src'] = $udrawPreviewImage;
        }
        return $result;
    }

    /**
     * Responsible for changing main cart item image.
     *
     * @param $item
     * @param $result
     * @return mixed
     */
    public function afterGetImage($item, $result)
    {
        $quoteItem = $item->getItem();
        $udrawPreviewImage = $this->udrawHelper->getUdrawPreviewFromCartQuoteItem($quoteItem);
        if ($udrawPreviewImage != null) {
            $result->setImageUrl($udrawPreviewImage);
        }

        return $result;
    }

    /**
     * Response for changing order summary images during checkout.
     *
     * @param $item
     * @param $result
     * @return array|mixed
     */
    public function afterGetImages($item, $result)
    {
        if (is_array($result)) {
            foreach ($result as $key => $value) {
                $quoteItem = $this->udrawHelper->getCartItemById($key);
                $udrawPreviewImage = $this->udrawHelper->getUdrawPreviewFromCartQuoteItem($quoteItem);
                if ($udrawPreviewImage != null) {
                    $result[$key]['src'] = $udrawPreviewImage;
                }
            }
        }

        return $result;
    }
}
