<?php
namespace Racadtech\Udraw\Controller\Designer;

use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;
use Racadtech\Udraw\Helper\Udraw;

class Asset extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    /**
     * @var UrlFactory
     */
    protected UrlFactory $urlFactory;

    /**
     * @var RawFactory
     */
    protected RawFactory $resultRawFactory;

    /**
     * @var Udraw
     */
    protected Udraw $udrawHelper;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory,
        Udraw $udrawHelper
    ) {
        $this->udrawHelper = $udrawHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRawFactory = $resultRawFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultRawFactory->create();

        $assetType = $this->getRequest()->getParam('type', 'css');
        if ($assetType == 'js') {
            $result->setHeader('Content-Type', 'text/javascript');
            $result->setContents(base64_decode($this->udrawHelper->getUdrawSettingValue('custom_designer_js')));
        } else {
            $result->setHeader('Content-Type', 'text/css');
            $result->setContents(base64_decode($this->udrawHelper->getUdrawSettingValue('custom_designer_css')));
        }
        return $result;
    }
}
