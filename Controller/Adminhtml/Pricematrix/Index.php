<?php

namespace Racadtech\Udraw\Controller\Adminhtml\Pricematrix;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Mangento\Backend\Model\View\Result\Page;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected PageFactory $resultPageFactory;

    public function __construct(Context $context, PageFactory $resultPageFactory)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Racadtech_Udraw::manage_pricematrix');
        $resultPage->addBreadcrumb(__('Udraw'), __('Udraw'));
        $resultPage->addBreadcrumb(__('Price Matrix'), __('Price Matrix'));
        $resultPage->getConfig()->getTitle()->prepend(__('Price Matrix'));

        return $resultPage;
    }

    /**
     * Validate permissions to access this controller.
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Racadtech_Udraw::pricematrix_create');
    }
}
