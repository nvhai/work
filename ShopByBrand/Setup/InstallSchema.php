<?php
/**
 * Copyright � 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Vnecoms\ShopByBrand\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        /**
         * Create table 'ves_shopbybrand_brands'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_shopbybrand_brands')
        )->addColumn(
            'brands_id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Brands Id'
        )->addColumn(
            'brand_name',
            Table::TYPE_TEXT,
            null,
            ['nullable' => true],
            'Brand Name'
        )->addColumn(
            'url_key',
            Table::TYPE_TEXT,
            null,
            ['nullable'=> false],
            'Url Key'
        )->addColumn(
            'page_title',
            Table::TYPE_TEXT,
            null,
            ['nullable'=> true],
            'Page Title'
        )->addColumn(
            'brand_thumbnail_image',
            Table::TYPE_TEXT,
            null,
            ['nullable'=> true],
            'Brand Thumbnail Image'
        )->addColumn(
            'brand_image',
            Table::TYPE_TEXT,
            null,
            ['nullable'=> true],
            'Brand Image'
        )->addColumn(
            'sort_order',
            Table::TYPE_SMALLINT,
            null,
            ['nullable'=> true],
            'Sort Order'
        )->addColumn(
            'banner_url',
            Table::TYPE_TEXT,
            null,
            ['nullable'=> true],
            'Banner Url'
        )->addColumn(
            'brand_is_featured',
            Table::TYPE_SMALLINT,
            null,
            ['nullable'=> true],
            'Brand Is Featured'
        )->addColumn(
            'status',
            Table::TYPE_SMALLINT,
            null,
            ['nullable'=> true],
            'Status'
        )->addColumn(
            'brand_short_description',
            Table::TYPE_TEXT,
            null,
            ['nullable'=> true],
            'Brand Short Description'
        )->addColumn(
            'brand_description',
            Table::TYPE_TEXT,
            null,
            ['nullable'=> true],
            'Brand Description'
        )->addColumn(
            'meta_keywords',
            Table::TYPE_TEXT,
            null,
            ['nullable'=> true],
            'Meta Keywords'
        )->addColumn(
            'meta_description',
            Table::TYPE_TEXT,
            null,
            ['nullable'=> true],
            'Meta Description'
        );

        $installer->getConnection()->createTable($table);
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ves_shopbybrand_brands_product')
        )->addColumn(
            'brands_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false,],
            'Brands Id'
        )->addColumn(
            'product_id',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => true],
            'Product Id'
        )->addColumn(
            'position',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => true],
            'Position'
        );
        $installer->getConnection()->createTable($table);
        $installer->endSetup();
    }
}