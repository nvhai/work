<?php
namespace Vnecoms\ShopByBrand\Model\Brand;

use Vnecoms\ShopByBrand\Model\ResourceModel\Brands\CollectionFactory;
use Magento\Catalog\Model\Product as ProductModel;

class Product
{
    /**
     * @var \Vnecoms\ShopByBrand\Model\BrandsFactory
     */
    protected $authorCollectionFactory;

    /**
     * @param CollectionFactory $brandCollectionFactory
     */
    public function __construct(CollectionFactory $brandCollectionFactory)
    {
        $this->brandCollectionFactory = $brandCollectionFactory;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return \Vnecoms\ShopByBrand\Model\Brands[]
     */
    public function getSelectedBrands(ProductModel $product)
    {
        if (!$product->hasSelectedBrands()) {
            $brands = [];
            foreach ($this->getSelectedBrandsCollection($product) as $brand) {
                $brands[] = $brand;
            }
            $product->setSelectedBrands($brands);
        }
        return $product->getData('selected_brands');
    }

    /**
     * @access public
     * @param \Magento\Catalog\Model\Product $product
     * @return \Vnecoms\ShopByBrand\Model\ResourceModel\Brands\Collection
     */
    public function getSelectedBrandsCollection(ProductModel $product)
    {
        $collection = $this->brandCollectionFactory->create()
            ->addProductFilter($product);
        return $collection;
    }
}
