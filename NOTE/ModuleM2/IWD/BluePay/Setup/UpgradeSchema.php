<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePay\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Psr\Log\LoggerInterface;

/**
 * Class UpgradeSchema
 * @package IWD\BluePay\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * UpgradeSchema constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param LoggerInterface $logger
     */
    public function __construct(EavSetupFactory $eavSetupFactory, LoggerInterface $logger)
    {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.1.0', '<')) {
            $this->addCardTable($setup);
        }
    }

    /**
     * Add table iwd_bluepay_card
     *
     * @param $installer
     */
    private function addCardTable(SchemaSetupInterface $installer)
    {
        try {
            $table = $installer->getConnection()
                ->newTable(
                    $installer->getTable('iwd_bluepay_card')
                )
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                    'Entity Id'
                )
                ->addColumn(
                    'customer_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    ['unsigned' => true],
                    'Customer Id'
                )
                ->addColumn(
                    'customer_email',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'unique' => false],
                    'Customer Email'
                )
                ->addColumn(
                    'masked_account',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => true, 'unique' => false],
                    'Masked Account'
                )
                ->addColumn(
                    'trans_id',
                    Table::TYPE_BIGINT,
                    null,
                    ['nullable' => true, 'unique' => false],
                    'Transaction Id'
                )
                ->addColumn(
                    'expires',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Expiration Date'
                )
                ->addColumn(
                    'payment_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => true, 'unique' => false],
                    'Order Payment Id'
                )
                ->addColumn(
                    'customer_ip',
                    Table::TYPE_TEXT,
                    32,
                    ['nullable' => true, 'unique' => false],
                    'Customer IP'
                )
                ->addColumn(
                    'hash',
                    Table::TYPE_TEXT,
                    128,
                    ['nullable' => false, 'default' => '0'],
                    'Card hash'
                )
                ->addColumn(
                    'additional',
                    Table::TYPE_TEXT,
                    null,
                    [],
                    'Additional Data'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Created At Date'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_DATETIME,
                    null,
                    [],
                    'Updated At Date'
                )->addForeignKey(
                    $installer->getFkName('iwd_bluepay_card', 'customer_id', 'customer_entity', 'entity_id'),
                    'customer_id',
                    $installer->getTable('customer_entity'),
                    'entity_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_SET_NULL
                );

            $installer->getConnection()->createTable($table);
        } catch (\Exception $e) {
            $this->logger->critical('IWD BluePay installation: ' . $e->getMessage());
        }
    }
}
