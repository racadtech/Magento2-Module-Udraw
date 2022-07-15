<?php

namespace Racadtech\Udraw\Model\ResourceModel\Templates;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Racadtech\Udraw\Model\Settings;
use Racadtech\Udraw\Model\ResourceModel\Settings as SettingsResource;

class Collection extends AbstractCollection
{
    protected $_idFieldName = 'udraw_template_id';
    protected $_eventPrefix = 'racadtech_udraw_templates_collection';
    protected $_eventObject = 'templates_collection';

    protected function _construct()
    {
        $this->_init('Racadtech\Udraw\Model\Templates', 'Racadtech\Udraw\Model\ResourceModel\Templates');
    }
}
