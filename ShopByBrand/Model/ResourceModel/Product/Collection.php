<?php
namespace Vnecoms\ShopByBrand\Model\ResourceModel\Product;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'brands_id';


    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Vnecoms\ShopByBrand\Model\Product', 'Vnecoms\ShopByBrand\Model\ResourceModel\Product');
    }


}