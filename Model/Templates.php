<?php

namespace Racadtech\Udraw\Model;

use Magento\Framework\Model\AbstractModel;

class Templates extends AbstractModel
{
    protected $_eventPrefix = 'racadtech_udraw_templates';

    protected function _construct()
    {
        $this->_init(\Racadtech\Udraw\Model\ResourceModel\Templates::class);
    }
}
