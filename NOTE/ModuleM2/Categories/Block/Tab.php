<?php

namespace Convert\CategoriesCollection\Block;

class Tab extends \Convert\CategoriesCollection\Block\AbstractCategories {
	
	public function getTabCollection(){
		$result = [];
		$categoryIds = explode(",",$this->getCategoryIds());
		
		foreach($categoryIds as $_id){
			$category = $this->getCategory($_id);
			if($category){
				$result[] = $category;
			}
		}
		
		return $result;
		
	}
	
	 /**
     * @param string $id
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getImageUrl($id, $imageContent = NULL)
    {
		$_category = $this->_categoryRepository->get($id, $this->_storeManager->getStore()->getId());
        $image = $_category->getCateCollectionThumb();
        $url = false;
        if ($image != '') {
            if (is_string($image)) {
                $url = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . 'catalog/category/' . $image;
            } else {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
		}else {
			$url = $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . 'wysiwyg/placeholder-category-thumb-2.jpg';
		}
        return $url;
    }
	
}