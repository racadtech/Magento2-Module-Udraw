<?php

namespace Racadtech\Udraw\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Setup\Exception;
use Psr\Log\LoggerInterface;
use Racadtech\Udraw\Helper\Udraw;

class ViewOrderUdrawOptions implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    protected LoggerInterface $logger;
    protected Http $request;
    protected \Magento\Sales\Api\OrderRepositoryInterface $order;
    protected Udraw $udrawHelper;
    protected int $orderId;

    public function __construct(
        LoggerInterface $logger,
        Http $request,
        \Magento\Sales\Api\OrderRepositoryInterface $order,
        Udraw $udrawHelper
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->order = $order;
        $this->udrawHelper =$udrawHelper;

        try {
            $this->orderId = intval($this->request->getParam('order_id'));
        } catch (Exception $exception) {
            $this->orderId = -1;
        }
    }

    public function getUdrawData(): array
    {
        return $this->udrawHelper->getUdrawDataFromOrder($this->orderId);
    }

    public function getUdrawBaseApiUrl() : string
    {
        return $this->udrawHelper->getApiBaseUrl();
    }
}
