<?php

namespace Racadtech\Udraw\Block\Adminhtml\Settings;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\View\Helper\SecureHtmlRenderer;

class Edit extends Container
{
    public function __construct(Context $context, array $data = [], ?SecureHtmlRenderer $secureRenderer = null)
    {
        parent::__construct($context, $data, $secureRenderer);
    }

    protected function _construct()
    {
        $this->_objectId = 'udraw_setting_form';
        $this->_controller = 'adminhtml_settings';
        $this->_blockGroup = 'Racadtech_Udraw';
        parent::_construct();

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
    }
}
