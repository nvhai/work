<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */

namespace IWD\BluePaySubs\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Install attributes
 */
class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    /**
     * Product Attribute
     * Subscription Active
     */
    const SUBS_ACTIVE = 'subs_active';

    /**
     * Product Attribute
     * Subscription Can Used Onetime Purchase
     */
    const SUBS_ONETIME = 'subs_onetime';

    /**
     * Product Attribute
     * Subscription Serialized Options
     */
    const SUBS_OPTIONS = 'subs_options';

    /**
     * Subscription Product Attribute Group
     */
    const SUBS_ATTRIBUTE_GROUP = 'Subscription';

    /**
     * @var \Magento\Catalog\Setup\CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * Init
     *
     * @param \Magento\Catalog\Setup\CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        \Magento\Catalog\Setup\CategorySetupFactory $categorySetupFactory
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    /**
     * Installs data for a module
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);

        $setup->startSetup();

        try {
            $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);
            $attributeSetId = $categorySetup->getAttributeSetId($entityTypeId, 'Default');

            $attributeGroupId = $categorySetup->getAttributeGroup(
                $entityTypeId,
                $attributeSetId,
                self::SUBS_ATTRIBUTE_GROUP
            );

            if (!$attributeGroupId) {
                $categorySetup->addAttributeGroup($entityTypeId, $attributeSetId, self::SUBS_ATTRIBUTE_GROUP, 65);
            }

            $categorySetup->updateAttributeGroup(
                $entityTypeId,
                $attributeSetId,
                self::SUBS_ATTRIBUTE_GROUP,
                'attribute_group_code',
                'bluepay-subscription'
            );
            $categorySetup->updateAttributeGroup(
                $entityTypeId,
                $attributeSetId,
                self::SUBS_ATTRIBUTE_GROUP,
                'tab_group_code',
                'advanced'
            );
            $categorySetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                self::SUBS_ACTIVE,
                [
                    'type' => 'int',
                    'label' => 'Enabled',
                    'input' => 'boolean',
                    'sort_order' => 100,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'apply_to' => 'simple,virtual,downloadable,configurable',
                    'group' => self::SUBS_ATTRIBUTE_GROUP,
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true,
                    'used_for_promo_rules' => true,
                    'required' => false,
                ]
            );

            $categorySetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                self::SUBS_ONETIME,
                [
                    'type'                  => 'int',
                    'label'                 => 'Enabled one-time purchase',
                    'input'                 => 'boolean',
                    'sort_order'            => 200,
                    'global'                => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'apply_to'              => 'simple,virtual,downloadable,configurable',
                    'group'                 => self::SUBS_ATTRIBUTE_GROUP,
                    'is_used_in_grid'       => true,
                    'is_visible_in_grid'    => false,
                    'is_filterable_in_grid' => true,
                    'used_for_promo_rules'  => true,
                    'required'              => false,
                ]
            );

            $categorySetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                self::SUBS_OPTIONS,
                [
                    'type'                  => 'text',
                    'label'                 => 'Options',
                    'input'                 => 'text',
                    'sort_order'            => 300,
                    'global'                => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'apply_to'              => 'simple,virtual,downloadable,configurable',
                    'group'                 => self::SUBS_ATTRIBUTE_GROUP,
                    'note'                  => 'Subscription interval options',
                    'is_used_in_grid'       => true,
                    'is_visible_in_grid'    => false,
                    'is_filterable_in_grid' => true,
                    'required'              => false,
                ]
            );


        } catch (\Exception $e) {
            echo $e->getMessage();
        }
        $setup->endSetup();
    }
}
