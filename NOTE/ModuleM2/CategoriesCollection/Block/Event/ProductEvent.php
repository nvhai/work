<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Convert\CategoriesCollection\Block\Event;

/**
 * Main contact form block
 */
class ProductEvent extends \Magento\Framework\View\Element\Template
{    
    protected $_productCollectionFactory;
	
	protected $_date;
        
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,        
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,  
		\Magento\Framework\Stdlib\DateTime\DateTime $date,      
        array $data = []
    )
    {    
        $this->_productCollectionFactory = $productCollectionFactory;    
		$this->_date = $date;
        parent::__construct($context, $data);
    }
	
	public function getCurrentDate(){
		return $this->_date->gmtDate();
	}
    
    public function getProductCollection()
    {
		$date = $this->getCurrentDate();
		$now = date('Y-m-d', strtotime($date));
		
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addAttributeToFilter('date', ['gteq' => $now]);
        return $collection;
    }
}
