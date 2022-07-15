<?php
namespace Racadtech\Udraw\Controller\Pricematrix;

use Magento\Framework\UrlFactory;

class Asset extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\UrlFactory
     */
    protected $urlFactory;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Racadtech\Udraw\Helper\Udraw
     */
    protected $udrawHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Racadtech\Udraw\Helper\Udraw $udrawHelper,
        UrlFactory $urlFactory
    ) {
        $this->udrawHelper = $udrawHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->urlModel = $urlFactory->create();

        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultRawFactory->create();

        $accessKey = $this->getRequest()->getParam('access_key', '');
        $priceMatrixInstance = ($accessKey != "") ? $this->udrawHelper->getPricematrixInstance($accessKey) : null;
        if ($priceMatrixInstance != null) {
            $result->setHeader('Content-Type', 'text/xml');
            $result->setContents(base64_decode($priceMatrixInstance->getPriceData()));
        } else {
            $assetType = $this->getRequest()->getParam('type', '');
            if ($assetType == "css" || $assetType == "js") {
                if ($assetType == 'js') {
                    $result->setHeader('Content-Type', 'text/javascript');
                    $result->setContents(base64_decode($this->udrawHelper->getUdrawSettingValue('custom_pricematrix_js')));
                } else {
                    $result->setHeader('Content-Type', 'text/css');
                    $result->setContents(base64_decode($this->udrawHelper->getUdrawSettingValue('custom_pricematrix_css')));
                }
            } else {
                $result->setHeader('Content-Type', 'text/html');
                $result->setContents('Invalid Resource.');
            }
        }
        return $result;
    }
}
