<?php
namespace Vnecoms\ShopByBrand\Model\ResourceModel\Brands;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Catalog\Model\Product;
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = \Vnecoms\ShopByBrand\Model\Brands::BRANDS_ID;
    /**
     * @var array
     */
    protected $_joinedFields = [];
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vnecoms\ShopByBrand\Model\Brands', 'Vnecoms\ShopByBrand\Model\ResourceModel\Brands');
    }
    /**
     * @param $product
     * @return $this
     */
    public function addProductFilter($product)
    {
        if ($product instanceof Product) {
            $product = $product->getId();
        }
        if (!isset($this->_joinedFields['product'])) {
            $this->getSelect()->join(
                ['related_product' => $this->getTable('ves_shopbybrand_brands_product')],
                'related_product.brands_id = main_table.brands_id',
                ['position']
            );
            $this->getSelect()->where('related_product.product_id = ?', $product);
            $this->_joinedFields['product'] = true;
        }
        return $this;
    }
}