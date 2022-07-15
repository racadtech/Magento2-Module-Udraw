<?php

namespace Racadtech\Udraw\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Settings extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('racadtech_udraw_settings', 'udraw_settings_id');
    }
}
