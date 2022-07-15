<?php

namespace Racadtech\Udraw\Block\Adminhtml\Pricematrix\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $priceMatrixInstance;
    /**
     * @var \Racadtech\Udraw\Helper\Udraw
     */
    protected \Racadtech\Udraw\Helper\Udraw $udrawHelper;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected \Magento\Backend\Model\Auth\Session $authSession;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Racadtech\Udraw\Helper\Udraw $udrawHelper,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        $this->udrawHelper = $udrawHelper;
        $this->authSession = $authSession;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _init()
    {
        parent::_construct();
        $this->setId('udraw_pricematrix_form');
        $this->setTitle(__('Price Matrix'));
    }

    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = $this->_formFactory->create();
        $settings = $this->getSettings();

        $fieldset = $form->addFieldset(
            'udraw_pricematrix_fieldset',
            ['class' => 'fieldset-wide']
        );

        $accessKey = $this->getRequest()->getParam('access_key', '');
        $this->priceMatrixInstance = ($accessKey != "") ? $this->udrawHelper->getPricematrixInstance($accessKey) : null;

        $fieldset->addField(
            'udraw_pricematrix_access_key',
            'hidden',
            [
                'name' => 'udraw_pricematrix_access_key',
                'value' => $accessKey
            ]
        );

        $fieldset->addField(
            'udraw_pricematrix_name',
            'text',
            [
                'name' => 'udraw_pricematrix_name',
                'label' => 'Name',
                'required' => true,
                'value' => ($this->priceMatrixInstance != null) ? $this->priceMatrixInstance->getName() : '',
            ]
        );

        $fieldset->addField(
            'udraw_pricematrix_price_data',
            'textarea',
            [
                'name' => 'udraw_pricematrix_price_data',
                'required' => true,
                'style' => 'display:none;',
                'value' => ($this->priceMatrixInstance != null) ? base64_decode($this->priceMatrixInstance->getPriceData()) : '',
            ]
        );

        $form->setUseContainer(true);
        $form->setId('udraw_pricematrix_edit_form');
        $form->setAction($this->getUrl('*/*/save', ['_current' => true]));
        $form->setMethod('post');
        $this->setForm($form);
    }

    public function getFormHtml()
    {
        $html = parent::getFormHtml();
        $html .= $this->setTemplate('Racadtech_Udraw::pricematrix/manage.phtml')->toHtml();

        return $html;
    }

    public function getSaveUrl() : string
    {
        return $this->getUrl('udraw/pricematrix/manage');
    }

    public function isEditMode() : bool
    {
        return $this->priceMatrixInstance != null;
    }

    public function getPricematrixInstance()
    {
        return $this->priceMatrixInstance;
    }

    public function getUdrawApiKey() : string
    {
        return $this->udrawHelper->getUdrawSettingValue('udraw_api_key');
    }

    public function generateAuthToken() : string
    {
        return $this->udrawHelper->generateAuthToken(
            $this->udrawHelper->getUdrawSettingValue('udraw_api_key'),
            $this->udrawHelper->getUdrawSettingValue('udraw_secret_key'),
            hash('sha512', $this->authSession->getUser()->getId() . '_' . $this->authSession->getUser()->getUserName()),
            true
        );
    }
}
