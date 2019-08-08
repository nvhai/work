<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * Subscription product option ID
     */
    const SUBS_PRODUCT_OPTION_ID = 'subs_product_option_id';

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
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $version = $context->getVersion();

        if (version_compare($version, '1.2.0', '<')) {
            $this->addProductSubscriptionAttributeOption($setup);
        }

        if (version_compare($version, '1.2.1', '<')) {
            $this->updateEnabledAttribute($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param ModuleDataSetupInterface $setup
     */
    protected function addProductSubscriptionAttributeOption(ModuleDataSetupInterface $setup)
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
                'Subscription'
            );

            if (!$attributeGroupId) {
                $categorySetup->addAttributeGroup($entityTypeId, $attributeSetId, 'Subscription', 65);

                $categorySetup->updateAttributeGroup(
                    $entityTypeId,
                    $attributeSetId,
                    'Subscription',
                    'attribute_group_code',
                    'bluepay-subscription'
                );
                $categorySetup->updateAttributeGroup(
                    $entityTypeId,
                    $attributeSetId,
                    'Subscription',
                    'tab_group_code',
                    'advanced'
                );
            }

            $categorySetup->addAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                self::SUBS_PRODUCT_OPTION_ID,
                [
                    'type' => 'int',
                    'label' => 'Product Custom Option Id',
                    'input' => '',
                    'sort_order' => 400,
                    'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                    'apply_to' => 'simple,virtual,downloadable,configurable',
                    'group' => 'Subscription',
                    'note' => 'Subscription Product Option Id',
                    'is_used_in_grid' => true,
                    'is_visible_in_grid' => false,
                    'is_filterable_in_grid' => true,
                    'used_for_promo_rules' => false,
                    'required' => false,
                    'visible' => false
                ]
            );


        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    protected function updateEnabledAttribute(ModuleDataSetupInterface $setup)
    {
        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);

        $setup->startSetup();

        try {
            $categorySetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                InstallData::SUBS_ACTIVE,
                'frontend_label',
                'Subscription Enabled'
            );

            $categorySetup->updateAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                InstallData::SUBS_ACTIVE,
                'source_model',
                \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class
            );
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
