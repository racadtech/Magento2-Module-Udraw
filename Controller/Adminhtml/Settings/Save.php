<?php

namespace Racadtech\Udraw\Controller\Adminhtml\Settings;

use Magento\Framework\Controller\ResultFactory;
use Racadtech\Udraw\Helper\Udraw;
use Racadtech\Udraw\Model\Settings;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var Racadtech\Udraw\Helper\Udraw
     */
    protected $udrawHelper;

    /**
     * @var Racadtech\Udraw\Model\SettingsFactory
     */
    protected $settingsFactory;

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
        \Racadtech\Udraw\Model\SettingsFactory $settingsFactory,
        \Racadtech\Udraw\Helper\Udraw $udrawHelper,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->udrawHelper = $udrawHelper;
        $this->settingsFactory = $settingsFactory;
        $this->logger = $logger;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $containsErrors = false;

        $postData = $this->getRequest()->getParams();
        $settings = $this->settingsFactory->create()->getCollection();
        foreach ($settings as $udrawSetting) {
            if (array_key_exists($udrawSetting->getName(), $postData)) {
                $udrawSetting->setValue($postData[$udrawSetting->getName()]);
            }
        }
        // Store new settings into database.
        $settings->save();

        // Validate the uDraw credentials when saving settings.
        $isUdrawCredentialsValid = $this->udrawHelper->validateApiCredentials(
            $this->udrawHelper->getUdrawSettingValue('udraw_api_key'),
            $this->udrawHelper->getUdrawSettingValue('udraw_secret_key')
        );

        if (!$isUdrawCredentialsValid) {
            $this->messageManager->addErrorMessage(__('Api Credentials are Invalid.'));
            $containsErrors = true;
            $this->udrawHelper->setUdrawSetting('udraw_api_key', "invalid");
            $this->udrawHelper->setUdrawSetting('udraw_secret_key', "invalid");
        }

        if (!$containsErrors) {
            $this->messageManager->addSuccessMessage(__('Updated Settings Successfully.'));
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/');
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('Magento_Backend::admin');
    }
}
