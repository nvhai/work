<?php
/**
 * Created by PhpStorm.
 * User: doanhcn2
 * Date: 18/03/2019
 * Time: 11:43
 */

namespace Magenest\Feedback\Setup;


use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{

    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        // TODO: Implement install() method.
        $installer = $setup;
        $installer->startSetup();
        /**
         * Create table 'feedback'
         */
        $table = $installer->getConnection()
            ->newTable($installer->getTable('magenest_feedback'))
            ->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'feedback id'
            )
            ->addColumn(
                'product_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                32,
                ['nullable' => false],
                'product id'
            )
            ->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                32,
                ['nullable' => false],
                'order id'
            )
            ->addColumn(
                'customer_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                32,
                ['nullable' => false],
                'customer id'
            )
            ->addColumn(
                'message',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['nullable' => FALSE],
                'User message'
            )
            ->addColumn(
                'status',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                3,
                ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                'feedback status'
            )
            ->addColumn(
                'crated_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                90,
                ['nullable' => FALSE],
                'created time'
            )
            ->setComment('customer feedback product which ordered   ');
        $installer->getConnection()->createTable($table);
        $setup->endSetup();
    }
}