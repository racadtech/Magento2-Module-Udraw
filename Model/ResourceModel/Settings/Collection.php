<?php

namespace Racadtech\Udraw\Model\ResourceModel\Settings;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Racadtech\Udraw\Model\Settings;
use Racadtech\Udraw\Model\ResourceModel\Settings as SettingsResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'udraw_settings_id';
    protected $_eventPrefix = 'racadtech_udraw_settings_collection';
    protected $_eventObject = 'settings_collection';

    protected function _construct()
    {
        $this->_init('Racadtech\Udraw\Model\Settings', 'Racadtech\Udraw\Model\ResourceModel\Settings');
    }
}
