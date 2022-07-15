<?php

namespace Racadtech\Udraw\Model\ResourceModel\Pricematrix;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Racadtech\Udraw\Model\Settings;
use Racadtech\Udraw\Model\ResourceModel\Settings as SettingsResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'udraw_pricematrix_id';
    protected $_eventPrefix = 'racadtech_udraw_pricematrix_collection';
    protected $_eventObject = 'pricemarix_collection';

    protected function _construct()
    {
        $this->_init('Racadtech\Udraw\Model\Pricematrix', 'Racadtech\Udraw\Model\ResourceModel\Pricematrix');
    }
}
