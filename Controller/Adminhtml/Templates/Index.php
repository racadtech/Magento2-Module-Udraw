<?php

namespace Racadtech\Udraw\Controller\Adminhtml\Templates;

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
        $resultPage->setActiveMenu('Racadtech_Udraw::manage_template');
        $resultPage->addBreadcrumb(__('Udraw'), __('Udraw'));
        $resultPage->addBreadcrumb(__('Templates'), __('Templates'));
        $resultPage->getConfig()->getTitle()->prepend(__('uDraw Templates'));

        return $resultPage;
    }

    /**
     * Validate permissions to access this controller.
     *
     * @return bool
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Racadtech_Udraw::templates_create');
    }
}
