<?php
namespace Racadtech\Udraw\Block\Adminhtml;

use Magento\Framework\View\Element\Template;

class BaseTemplate extends Template
{
    /**
     * @var \Racadtech\Udraw\Helper\Udraw
     */
    protected \Racadtech\Udraw\Helper\Udraw $udrawHelper;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected \Magento\Backend\Model\Auth\Session $authSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected \Magento\Framework\Data\Form\FormKey $formKey;

    public function __construct(
        Template\Context $context,
        \Racadtech\Udraw\Helper\Udraw $udrawHelper,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Data\Form\FormKey $formKey,
        array $data = []
    ) {
        $this->udrawHelper = $udrawHelper;
        $this->authSession = $authSession;
        $this->formKey = $formKey;

        parent::__construct($context, $data);
    }

    /**
     * Get uDraw Api Key
     *
     * @return string
     */
    public function getUdrawApiKey() : string
    {
        return $this->getUdrawSetting('udraw_api_key');
    }

    /**
     * Get uDraw Secret Key
     *
     * @return string
     */
    public function getUdrawSecretKey() : string
    {
        return $this->getUdrawSetting('udraw_secret_key');
    }

    public function generateAuthToken() : string
    {
        return $this->udrawHelper->generateAuthToken(
            $this->getUdrawApiKey(),
            $this->getUdrawSecretKey(),
            hash('sha512', $this->authSession->getUser()->getId() . '_' . $this->authSession->getUser()->getUserName()),
            true
        );
    }

    /**
     * Generic function to get any uDraw saved setting.
     *
     * @param string $settingName uDraw Setting Name
     * @return string
     */
    public function getUdrawSetting(string $settingName) : string
    {
        return $this->udrawHelper->getUdrawSettingValue($settingName);
    }

    /**
     * Get the Form Key
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFormKey(): string
    {
        return $this->formKey->getFormKey();
    }
}
