<?php

namespace Racadtech\Udraw\Controller\Adminhtml\Settings;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected \Magento\Framework\View\Result\PageFactory $_pageFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_pageFactory->create();
        $resultPage->setActiveMenu('Racadtech_Udraw::udraw_settings');
        $resultPage->getConfig()->getTitle()->prepend(__('uDraw Settings'));

        $resultPage->getLayout()
            ->addBlock('Racadtech\Udraw\Block\Adminhtml\Settings\Edit', 'udrawsettings', 'content')
            ->setEditMode(true);

        return $resultPage;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Magento_Backend::admin');
    }
}
