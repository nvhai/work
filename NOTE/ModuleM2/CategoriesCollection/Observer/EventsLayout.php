<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace Convert\CategoriesCollection\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;

class EventsLayout implements ObserverInterface {
    
    private $registry;
	
    private $_categoryFactory;

	public function __construct(
		Registry $registry,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory
	){
		$this->_categoryFactory = $categoryFactory;
		$this->registry = $registry;
	}
    
    public function execute(Observer $observer) {
        
        /** @var \Magento\Framework\View\Layout $layout */

        $layout = $observer->getLayout();
        
        $action = $observer->getData('full_action_name');
        if ($action != 'catalog_category_view'){
			if ($action == 'catalog_product_view'){
				$product = $this->registry->registry('current_product');
				
				if (!$product)
				return $this;
				
				$categories = $product->getCategoryIds();
				
				if(count($categories) ){
					foreach($categories as $_categoryId){
						$_category = $this->_categoryFactory->create()->load($_categoryId);
						if($_category->getIsEventPage()){
							$layout->getUpdate()->addHandle('catalog_product_view_events');
							break;
						}
					}
				}
			}
            return $this;
		}else {
			$category = $this->registry->registry('current_category');
        
			if (!$category)
				return $this;
			
			if($category->getIsEventPage()){
				$layout->getUpdate()->addHandle('catalog_category_events');
			}elseif($category->getIsCapsulePage() || $category->getParentCategory()->getIsCapsulePage()){
				if($category->getIsCapsulePage()){
					$layout->getUpdate()->addHandle('catalog_category_landing_capsules');
				}else {
					$layout->getUpdate()->addHandle('catalog_category_capsules');
				}
			}
		}
    }
}
