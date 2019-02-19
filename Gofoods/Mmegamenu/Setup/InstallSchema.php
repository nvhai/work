<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Gofoods\Mmegamenu\Setup;

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
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();
		

        $installer->getConnection()->addColumn(
                $installer->getTable('mgs_megamenu'), 'icon-menu', [
            'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            'length' => 255,
            'nullable' => true,
            'comment' => 'Icon Menu'
            ]
        );

        $installer->endSetup();

    }
}
