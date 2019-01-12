<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Convert\CategoriesCollection\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\Product\Gallery\ReadHandler as GalleryReadHandler;

/**
 * Catalog image helper
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Image extends AbstractHelper
{
	protected $_imageHelper;
	
	protected $galleryReadHandler;
	
	protected $_productloader;  
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     */
	 
	
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
		\Magento\Catalog\Helper\Image $imageHelper,
		GalleryReadHandler $galleryReadHandler,
		\Magento\Catalog\Model\ProductFactory $productloader
    ) {
        parent::__construct($context);
		$this->_imageHelper = $imageHelper;
		$this->galleryReadHandler = $galleryReadHandler;
		$this->_productloader = $productloader;
    }
	
	public function getGhostImage($product, $width, $height){
		$product = $this->_productloader->create()->load($product->getId());
		$attributeImage = $product->getCustomAttribute('ghost');
		if($attributeImage && basename($product->getData('ghost')) !=  'no_selection'){
			return $this->_imageHelper->init($product, 'ghost')->setImageFile($attributeImage->getValue())->resize($width, $height)->getUrl();
		}
		
		return $this->_imageHelper->init($product, 'category_page_grid')->resize($width, $height)->getUrl();
	}
	
	public function addGallery($product)
    {
        $this->galleryReadHandler->execute($product);
    }
}
