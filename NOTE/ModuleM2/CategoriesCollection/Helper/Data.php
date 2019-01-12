<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Convert\CategoriesCollection\Helper;

use Magento\Framework\App\Area;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;

/**
 * Catalog image helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Data extends AbstractHelper
{
	protected $_customerSeasion;  
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
	 
	/**
     * Store Manage
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
	protected $_registry;
	
	const XML_PATH_ACCESSORIES_HOMEWARE_CATEGORIES = 'categories_collection_section/accessories_homeware_settings/accessories_homeware_ids';
	
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
		\Magento\Customer\Model\Session $customerSeasion,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Locale\Resolver $locale,
		\Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
		$this->_customerSeasion = $customerSeasion;
        $this->_storeManager = $storeManager;
		$this->_locale = $locale;
		$this->_registry = $registry;
    }
	
	public function checkCustomerLogin(){
		return $this->_customerSeasion->isLoggedIn();
	}
	
	public function getImageCapsulePlaceholder(){
		$url = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . 'wysiwyg/placeholder-category-capsule.jpg'; 
		return $url;
	}
	
	public function getCurrentLocale(){
		$currentStore = $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
		return strtolower($currentStore.'_is_current');
	}
	
	public function IsHomewares()
    {
		$is_homewares = false;
        if($current_category = $this->_registry->registry('current_category')){
			if($current_category->getData('is_homewares')){
				$is_homewares = true;
			}
		}
		
		return $is_homewares;
    }
	
	public function getOptionsJson($product_id){
		$stocks = array();
		$singleStocks = array();
		$attributeOptions = array('minder_colour'=>0, 'minder_size'=>0);
		
		if($product_id){
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$stockObject=$objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface');
			$_product = $objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
			
			$productAttributeOptions = $_product->getTypeInstance(true)->getConfigurableAttributesAsArray($_product);
			
			$firstOption = 'minder_colour';
			$checkFirst = false;
			$attr_ids = array();
			$i = 0;
			
			if(count($productAttributeOptions)>0)
			{
				foreach ($productAttributeOptions as $productAttribute) 
				{	$i++;
					if($productAttribute['attribute_code'] == 'minder_colour' && !$checkFirst){
						$firstOption = 'minder_colour';
						$checkFirst = true;
						$attr_ids['first'] = $productAttribute['attribute_id'];
					}
					if($productAttribute['attribute_code'] == 'minder_size' && !$checkFirst){
						$firstOption = 'minder_size';
						$checkFirst = true;
						$attr_ids['first'] = $productAttribute['attribute_id'];
					}
					if($i == count($productAttributeOptions)){
						$attr_ids['last'] = $productAttribute['attribute_id'];
					}
					$attributeOptions[$productAttribute['attribute_code']] = 1;
				}
			}
		
			$_childrens = $_product->getTypeInstance()->getUsedProductIds($_product);
			foreach ($_childrens as $simpleId) {
				$stock = array();
				$_subproduct = $objectManager->create('Magento\Catalog\Model\Product')->load($simpleId);
				$productStockObj = $stockObject->getStockItem($_subproduct->getId());
				$isInStock = $productStockObj['is_in_stock'];
				if($attributeOptions['minder_colour'] ==1 && $attributeOptions['minder_size'] ==1) {
					$minder_colour = (int) $_subproduct->getMinderColour();
					$minder_size = (int) $_subproduct->getMinderSize();
					if($firstOption == 'minder_colour'){
						$stock[$minder_colour][$minder_size] = $isInStock;
					}
					if($firstOption == 'minder_size'){
						$stock[$minder_size][$minder_colour] = $isInStock;
					}
					$stocks[] = $stock;
				} elseif($attributeOptions['minder_colour'] == 1 && $attributeOptions['minder_size'] == 0) {
					$minder_colour = (int) $_subproduct->getMinderColour();
					
					$singleStocks[$minder_colour] = $isInStock;
				} elseif($attributeOptions['minder_size'] == 1 && $attributeOptions['minder_colour'] == 0) {
					$minder_size = (int) $_subproduct->getMinderSize();
					
					$singleStocks[$minder_size] = $isInStock;
				}				
			}			
			$newArrays = array();
			if(!empty($stocks) && count($stocks) > 0) {				
				foreach($stocks as $key => $values) {
					if(!empty($values) && count($values) > 0){
						foreach($values as $k => $vs) {
							if(!empty($vs) && count($vs) > 0){
								foreach($vs as $vv => $vvv) {
									$newArrays[$k][$vv] = $vvv;
								}
							}
						}
					}
				}
			}
			if(empty($stocks) && !empty($singleStocks) && count($singleStocks)){
				$newArrays = $singleStocks;
			}
			$newArrays = array('attr_ids' => $attr_ids, 'data' => $newArrays);
			return json_encode($newArrays);
		}
	}
	
	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field, ScopeInterface::SCOPE_STORE, $storeId
		);
	}
	
	public function getSubcatgeories($categoryId)
	{
		$arraySubCateg = array();
		$CategoryRepository = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\CategoryRepository');
		$CategoryFactory = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\CategoryFactory');
    
		$category = $CategoryFactory->create()->load($categoryId);
		if($category->getId()){
			$_category = $CategoryRepository->get($categoryId);
			$collection = $CategoryFactory->create()->getCollection()->addAttributeToSelect('*')
				  ->addAttributeToFilter('is_active', 1)
				  ->setOrder('position', 'ASC')
				  ->addIdFilter($_category->getChildren());
			if($collection){
				foreach($collection as $category){
					$arraySubCateg[] = $category->getId();
					if($category->getChildrenCount() > 0){
						$subArray =$this->getSubcatgeories($category->getId());
						$arraySubCateg = array_merge($arraySubCateg ,$subArray);
					}
				}
			}
		}
		return $arraySubCateg;
	}
	
	public function isAccessoriesandHomeware(){
		if($current_category = $this->_registry->registry('current_category')){
			$storeId = $this->_storeManager->getStore()->getId();
			$accessories_homewareIds = $this->getConfigValue(self::XML_PATH_ACCESSORIES_HOMEWARE_CATEGORIES, $storeId);
			$allSubCategIds = array();
			
			if($accessories_homewareIds && $accessories_homewareIds != ''){
				if(in_array($current_category->getId(), explode(',', $accessories_homewareIds))){
					return true;
				}

				foreach(explode(',', $accessories_homewareIds) as $accessories_homewareId){
					$allSubCategIds = $this->getSubcatgeories($accessories_homewareId);
					if(in_array($current_category->getId(), $allSubCategIds)){
						return true;
					}
				}
			}
		}
		
		return false;
	}
}
