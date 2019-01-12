<?php

namespace Convert\CategoriesCollection\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Catalog\Setup\CategorySetupFactory;

class InstallData implements InstallDataInterface
{
    
    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    private $categorySetupFactory;

    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     */
    public function __construct(CategorySetupFactory $categorySetupFactory)
    {
        $this->categorySetupFactory = $categorySetupFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        
        // set new resource model paths
        /** @var \Magento\Catalog\Setup\CategorySetup $categorySetup */
        
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        $entityTypeId = $categorySetup->getEntityTypeId(\Magento\Catalog\Model\Category::ENTITY);
        $attributeSetId = $categorySetup->getDefaultAttributeSetId($entityTypeId);
        
        $categories_attributes = [
            'is_shop_by_style' => [
                'type' => 'int',
                'label' => 'Is Shop By Style',
                'input' => 'select',
                'required' => false,
                'sort_order' => 26,
                'default' => '0',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'group' => 'General'
            ]
        ];
        
        foreach($categories_attributes as $item => $data) {
            $categorySetup->addAttribute(\Magento\Catalog\Model\Category::ENTITY, $item, $data);
        }
        
        $idg =  $categorySetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'General');
        
        foreach($categories_attributes as $item => $data) {
            $categorySetup->addAttributeToGroup(
                $entityTypeId,
                $attributeSetId,
                $idg,
                $item,
                $data['sort_order']
            );
        }
        
        $setup->endSetup();
    }
}