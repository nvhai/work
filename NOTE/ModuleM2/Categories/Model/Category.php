<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace MGS\Mpanel\Model;

class Category extends \Magento\Catalog\Model\Category
{
   
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        // If Flat Index enabled then use it but only on frontend
        if ($this->flatState->isAvailable() && !$this->getDisableFlat()) {
            $this->_init('Magento\Catalog\Model\ResourceModel\Category\Flat');
            $this->_useFlatResource = true;
        } else {
            $this->_init('Magento\Catalog\Model\ResourceModel\Category');
        }
    }
	
	public function getDisableFlat(){
		if($this->getUrlInstance()->getCurrentUrl() == $this->getUrlInstance()->getUrl('mpanel/category/save')){
			return true;
		}
		return false;
	}
}
