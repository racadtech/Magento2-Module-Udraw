<?php

namespace Racadtech\Udraw\Controller\Adminhtml\Templates;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;

class Save extends \Magento\Backend\App\Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var \Racadtech\Udraw\Helper\Udraw
     */
    protected $udrawHelper;

    /**
     * @var \Racadtech\Udraw\Model\TemplatesFactory
     */
    protected $templatesFactory;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Racadtech\Udraw\Helper\Udraw $udrawHelper,
        \Racadtech\Udraw\Model\TemplatesFactory $templatesFactory,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->udrawHelper = $udrawHelper;
        $this->templatesFactory = $templatesFactory;
        $this->authSession = $authSession;
        $this->logger = $logger;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $postData = $this->getRequest()->getParams();
        $accessKey = (array_key_exists('access_key', $postData)) ? $postData['access_key'] : "";

        $templateInstance = $this->udrawHelper->getTemplateInstance($accessKey);
        if ($templateInstance != null) {
            // Updating existing Template
            $templateInstance->setName($postData['name']);
            $templateInstance->setPreview($postData['preview']);
            $templateInstance->setDesign($postData['design']);
            $templateInstance->setDesignCropped($postData['designCropped']);
            $templateInstance->setDesignWidth($postData['width']);
            $templateInstance->setDesignHeight($postData['height']);
            $templateInstance->setDesignPages($postData['pages']);

            $isUpdated = $templateInstance->save();

            if ($isUpdated) {
                $this->messageManager->addSuccessMessage('uDraw Template Updated.');
            } else {
                $this->messageManager->addErrorMessage('Failed to Update Template');
            }
        } else {
            // Creating new uDraw Template.
            $udrawTemplate = $this->templatesFactory->create();
            $accessKey =  ($postData['designKey'] != null) ? $postData['designKey'] : uniqid();

            $udrawTemplate->addData([
                'name' => $postData['name'],
                'access_key' => $accessKey,
                'design' => $postData['design'],
                'design_cropped' => $postData['designCropped'],
                'preview' => $postData['preview'],
                'design_width' => $postData['width'],
                'design_height' => $postData['height'],
                'design_pages' => $postData['pages'],
                'create_user' => $this->authSession->getUser()->getUserName()
            ]);

            $isCreated = $udrawTemplate->save();
            if ($isCreated) {
                $this->messageManager->addSuccessMessage('uDraw Template Created.');
            } else {
                $this->messageManager->addErrorMessage('Failed to create Template');
            }
        }
        return $this->resultRedirectFactory->create()->setPath('udraw/templates/index');
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Racadtech_Udraw::templates_create');
    }
}
