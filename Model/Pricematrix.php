<?php

namespace Racadtech\Udraw\Model;

use Magento\Framework\Model\AbstractModel;

class Pricematrix extends AbstractModel
{
    protected $_eventPrefix = 'racadtech_udraw_pricematrix';

    protected function _construct()
    {
        $this->_init(\Racadtech\Udraw\Model\ResourceModel\Pricematrix::class);
    }
}
