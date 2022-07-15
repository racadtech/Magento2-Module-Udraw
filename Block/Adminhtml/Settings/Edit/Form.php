<?php

namespace Racadtech\Udraw\Block\Adminhtml\Settings\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    protected $_settingsCollection;
    protected $_settingsCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Racadtech\Udraw\Model\ResourceModel\Settings\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_settingsCollectionFactory = $collectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _init()
    {
        parent::_construct();
        $this->setId('udraw_settings_form');
        $this->setTitle(__('uDraw Settings'));
    }

    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = $this->_formFactory->create();
        $settings = $this->getSettings();

        $sections = [];
        foreach ($settings as $item) {
            if ($item->getControlType() == "hidden") {
                continue;
            }

            if (!in_array($item->getSection(), $sections)) {
                $sections[] = $item->getSection();
            }
        }

        for ($x = 0; $x < count($sections); $x++) {
            $fieldset = $form->addFieldset(
                'udraw_settings_fieldset_' . $x,
                ['legend' => __($sections[$x]), 'class' => 'fieldset-wide']
            );
            foreach ($settings as $item) {
                if ($item->getControlType() != "hidden" && $item->getSection() == $sections[$x]) {
                    $fieldset->addField(
                        $item->getName(),
                        $item->getControlType(),
                        [
                            'name' => $item->getName(),
                            'label' => $item->getLabel(),
                            'required' => true,
                            'value' => $item->getValue()
                        ]
                    );
                }
            }
        }

        $form->setUseContainer(true);
        $form->setId('udraw_settings_edit_form');
        $form->setAction($this->getUrl('*/*/save', ['_current' => true]));
        $form->setMethod('post');
        $this->setForm($form);
    }

    public function getFormHtml()
    {
        $html = parent::getFormHtml();
        $html .= $this->setTemplate('Racadtech_Udraw::settings/edit.phtml')->toHtml();

        return $html;
    }

    public function getSettings()
    {
        if (null === $this->_settingsCollection) {
            $this->_settingsCollection = $this->_settingsCollectionFactory->create();
        }
        return $this->_settingsCollection;
    }

    public function getSaveUrl() : string
    {
        return $this->getUrl('udraw/settings/edit');
    }
}
