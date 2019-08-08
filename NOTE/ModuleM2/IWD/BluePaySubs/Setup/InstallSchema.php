<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * DB setup script for Subscriptions
 */
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    const TABLE_IWD_BLUEPAY_SUBS = 'iwd_bluepay_subs';
    const TABLE_IWD_BLUEPAY_SUBS_LOG = 'iwd_bluepay_subs_log';

    /**
     * DB setup code
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        try {
            /**
             * Create table 'iwd_subs'
             */
            if (!$setup->getConnection()->isTableExists($setup->getTable(self::TABLE_IWD_BLUEPAY_SUBS))) {
                $table = $setup->getConnection()->newTable(
                    $setup->getTable(self::TABLE_IWD_BLUEPAY_SUBS)
                )->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'Subscription ID'
                )->addColumn(
                    'rebill_id',
                    Table::TYPE_BIGINT,
                    null,
                    ['nullable' => true, 'unique' => false],
                    'Rebill Id'
                )->addColumn(
                    'transaction_id',
                    Table::TYPE_BIGINT,
                    null,
                    ['nullable' => true, 'unique' => false],
                    'Transaction Id'
                )->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Creation Time'
                )->addColumn(
                    'updated_at',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Updated Time'
                )->addColumn(
                    'last_date',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Last Date'
                )->addColumn(
                    'next_date',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Next Date'
                )->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    32,
                    [],
                    'Status'
                )->addColumn(
                    'store_id',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Store ID'
                )->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Customer ID'
                )->addColumn(
                    'quote_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false],
                    'Source Quote ID'
                )->addColumn(
                    'period_interval',
                    Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                    'Period Interval'
                )->addColumn(
                    'period',
                    Table::TYPE_TEXT,
                    32,
                    [],
                    'Period'
                )->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    32,
                    [],
                    'Product Name/Description'
                )->addColumn(
                    'amount',
                    Table::TYPE_DECIMAL,
                    '12,4',
                    [],
                    'Amount'
                )->addColumn(
                    'cycles',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Cycles'
                )->addColumn(
                    'cycles_run_count',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Cycles Run Count'
                )->addColumn(
                    'additional_information',
                    Table::TYPE_TEXT,
                    '2M',
                    [],
                    'Additional Info'
                )->addIndex(
                    $setup->getIdxName(self::TABLE_IWD_BLUEPAY_SUBS, ['status', 'next_date']),
                    ['status', 'next_date']
                )->addForeignKey(
                    $setup->getFkName(self::TABLE_IWD_BLUEPAY_SUBS, 'quote_id', 'quote', 'entity_id'),
                    'quote_id',
                    $setup->getTable('quote'),
                    'entity_id',
                    Table::ACTION_CASCADE
                )->setComment(
                    'IWD Subs'
                );

                $setup->getConnection()->createTable($table);
            }
            /**
             * Create table 'iwd_subs_log'
             */
            if (!$setup->getConnection()->isTableExists($setup->getTable(self::TABLE_IWD_BLUEPAY_SUBS_LOG))) {
                $table = $setup->getConnection()->newTable(
                    $setup->getTable(self::TABLE_IWD_BLUEPAY_SUBS_LOG)
                )->addColumn(
                    'log_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true],
                    'Log ID'
                )->addColumn(
                    'subs_id',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Subs ID'
                )->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Creation Time'
                )->addColumn(
                    'status',
                    Table::TYPE_TEXT,
                    32,
                    [],
                    'Status'
                )->addColumn(
                    'order_increment_id',
                    Table::TYPE_TEXT,
                    32,
                    [],
                    'Order Increment ID'
                )->addColumn(
                    'transaction_id',
                    Table::TYPE_BIGINT,
                    null,
                    ['nullable' => true, 'unique' => false],
                    'Transaction Id'
                )->addColumn(
                    'agent_id',
                    Table::TYPE_INTEGER,
                    null,
                    [],
                    'Agent ID'
                )->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Description'
                )->addColumn(
                    'additional_information',
                    Table::TYPE_TEXT,
                    '2M',
                    [],
                    'Additional Info'
                )->addIndex(
                    $setup->getIdxName(self::TABLE_IWD_BLUEPAY_SUBS_LOG, ['subs_id']),
                    ['subs_id']
                )->addForeignKey(
                    $setup->getFkName(
                        self::TABLE_IWD_BLUEPAY_SUBS_LOG,
                        'subs_id',
                        self::TABLE_IWD_BLUEPAY_SUBS,
                        'entity_id'
                    ),
                    'subs_id',
                    $setup->getTable(self::TABLE_IWD_BLUEPAY_SUBS),
                    'entity_id',
                    Table::ACTION_CASCADE
                )->setComment(
                    'IWD Subs Log'
                );

                $setup->getConnection()->createTable($table);
            }
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        $setup->endSetup();
    }
}
