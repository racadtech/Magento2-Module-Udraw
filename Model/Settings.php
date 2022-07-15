<?php

namespace Racadtech\Udraw\Model;

use Magento\Framework\Model\AbstractModel;

class Settings extends AbstractModel
{
    protected $_eventPrefix = 'racadtech_udraw_settings';

    protected function _construct()
    {
        $this->_init(\Racadtech\Udraw\Model\ResourceModel\Settings::class);
    }
}
