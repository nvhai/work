<?php

namespace Convert\CategoriesCollection\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

class AbstractCategories extends Template {

    /**
     * @var Category
     */
    protected $_categoryInstance;

    /**
     * Category collection factory
     *
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $_categoryRepository;
	
	/**
     * Store Manage
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
	
    protected $registry;
    
    /**
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
	
    public function __construct(
        Context $context,
		Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {       
         parent::__construct($context);
		 $this->registry = $registry;
         $this->_categoryRepository = $categoryRepository;
         $this->_categoryInstance = $categoryFactory->create();
         $this->_storeManager = $storeManager;
    }
    
    /**
     * Retrieve category
     *
     * @return \Magento\Framework\Data\Tree\Node\Collection
     */
	
	public  function getCategory($categoryId) {
		
		$category = $this->_categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());

        return $category;
    }
	
	public function getCurrentCategory(){
		
		$category = $this->registry->registry('current_category');
		return $category->getId();
		
	}
    
    /**
     * Get url for category data
     *
     * @param Category $category
     * @return string
     */
    public function getCategoryUrl($category)
    {
        if ($category instanceof Category) {
            $url = $category->getUrl();
        } else {
            $url = $this->_categoryInstance->setData($category->getData())->getUrl();
        }

        return $url;
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
        if ($image) {
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
			if($imageContent == 'gray_image'){
				$url = $this->_storeManager->getStore()->getBaseUrl(
						\Magento\Framework\UrlInterface::URL_TYPE_MEDIA
					) . 'wysiwyg/placeholder-category-thumb-gray.jpg'; 
			} elseif ($imageContent == 'catalogues_image'){
                $url = $this->getViewFileUrl('/images/place_catalogues.jpg'); 
            } else {
				$url = $this->_storeManager->getStore()->getBaseUrl(
						\Magento\Framework\UrlInterface::URL_TYPE_MEDIA
					) . 'wysiwyg/placeholder-category-thumb.jpg'; 
			}
		}
        return $url;
    }
}