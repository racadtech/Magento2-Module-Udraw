<?php

namespace Racadtech\Udraw\Plugin\Sales\Order;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Racadtech\Udraw\Helper\Udraw;

class Reorder
{
    private ManagerInterface $messageManager;
    private Cart $cart;
    private OrderLoaderInterface $orderLoader;
    private Registry $registry;
    private RedirectFactory $resultRedirectFactory;
    private ProductRepositoryInterface $productRepository;
    private StoreManagerInterface $storeManager;
    private Json $serializer;
    private Udraw $udrawHelper;

    public function __construct(
        OrderLoaderInterface  $orderLoader,
        RedirectFactory $resultRedirectFactory,
        Cart $cart,
        ManagerInterface $messageManager,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        Json $serializer,
        StoreManagerInterface $storeManager,
        Udraw $udrawHelper
    ) {
        $this->registry = $registry;
        $this->orderLoader = $orderLoader;
        $this->cart = $cart;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->serializer = $serializer;
        $this->udrawHelper = $udrawHelper;
    }
    public function aroundExecute(
        \Magento\Sales\Controller\AbstractController\Reorder $subject
    ): \Magento\Framework\Controller\ResultInterface {
        $result = $this->orderLoader->load($subject->getRequest());
        if ($result instanceof \Magento\Framework\Controller\ResultInterface) {
            return $result;
        }
        $order = $this->registry->registry('current_order');
        $udrawOrderData = $this->udrawHelper->getUdrawDataFromOrder(intval($order->getId()));
        $resultRedirect = $this->resultRedirectFactory->create();
        $cart = $this->cart;
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $foundUdrawProduct = false;
                for ($x = 0; $x < count($udrawOrderData); $x++) {
                    if ($udrawOrderData[$x]['order_item_id'] == intval($item->getId())) {
                        $foundUdrawProduct = true;
                        $storeId = $this->storeManager->getStore()->getId();
                        $product = $this->productRepository->getById($item->getProductId(), false, $storeId, true);

                        if (key_exists('udraw_designer_data', $udrawOrderData[$x]['udraw_data'])) {
                            $udrawOrderData[$x]['udraw_data']['udraw_designer_data'] =
                                base64_encode($this->serializer->serialize($udrawOrderData[$x]['udraw_data']['udraw_designer_data']));
                        }

                        $product->addCustomOption('udraw_reorder_data', $this->serializer->serialize($udrawOrderData[$x]['udraw_data']));
                        $cart->addProduct($product, $item->getQtyOrdered());
                        break;
                    }
                }
                if (!$foundUdrawProduct) {
                    $cart->addOrderItem($item);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/history');
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('We can\'t add this item to your shopping cart right now.')
                );
                return $resultRedirect->setPath('checkout/cart');
            }
        }

        $cart->save();
        return $resultRedirect->setPath('checkout/cart');
    }
}
