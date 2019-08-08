<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * Counter for 'FAILED PAYMENT' status
     */
    const PAYMENT_FAILED_RUN_COUNT = 'payment_failed_run_count';

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.2.2', '<')) {
            $this->updateSubscriptionTable($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    protected function updateSubscriptionTable(SchemaSetupInterface $setup)
    {
        if($setup->getConnection()->isTableExists($setup->getTable(InstallSchema::TABLE_IWD_BLUEPAY_SUBS))) {
            $setup->getConnection()->addColumn(
                $setup->getTable(InstallSchema::TABLE_IWD_BLUEPAY_SUBS),
                self::PAYMENT_FAILED_RUN_COUNT,
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length' => 11,
                    'nullable' => false,
                    'default' => '0',
                    'comment' => 'Failed Payment Run Count'
                ]
            );
        }
    }

}
