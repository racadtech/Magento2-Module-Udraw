<?php
namespace Racadtech\Udraw\Controller\Adminhtml\Pricematrix;

class Manage extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected \Magento\Framework\View\Result\PageFactory $resultPageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Racadtech_Udraw::manage_pricematrix');

        $resultPage->getLayout()
            ->addBlock('Racadtech\Udraw\Block\Adminhtml\Pricematrix\Manage', 'udrawpricematrix', 'content')
            ->setEditMode(true);

        return $resultPage;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Racadtech_Udraw::pricematrix_create');
    }
}
