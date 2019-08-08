<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Model\ResourceModel;

use IWD\BluePaySubs\Setup\InstallSchema;

/**
 * Log resource model
 */
class Log extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = InstallSchema::TABLE_IWD_BLUEPAY_SUBS_LOG . '_resource';

    /**
     * Event object
     *
     * @var string
     */
    protected $_eventObject = 'resource';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(InstallSchema::TABLE_IWD_BLUEPAY_SUBS_LOG, 'log_id');
    }
}
