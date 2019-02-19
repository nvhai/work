<?php
namespace Vnecoms\ShopByBrand\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime as LibDateTime;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\Store;
use Vnecoms\ShopByBrand\Model\Brands as BrandsModel;
use Magento\Framework\Event\ManagerInterface;
use Magento\Catalog\Model\Product;
use Vnecoms\ShopByBrand\Model\Brand\Product as BrandProduct;

/**
 * Department post mysql resource
 */
class Brands extends AbstractDb
{
    /**
     * Store model
     *
     * @var \Magento\Store\Model\Store
     */
    protected $store = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var string
     */
    protected $brandProductTable;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * @var \Vnecoms\ShopByBrand\Model\Brand\Product
     */
    protected $brandProduct;
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        LibDateTime $dateTime,
        ManagerInterface $eventManager,
        BrandProduct $brandProduct
    ){
        $this->storeManager     = $storeManager;
        $this->dateTime         = $dateTime;
        $this->eventManager     = $eventManager;
        $this->brandProduct    = $brandProduct;

        parent::__construct($context);
        $this->brandProductTable  = $this->getTable('ves_shopbybrand_brands_product');

    }
    
    protected function _construct()
    {
        // Table Name and Primary Key column
        $this->_init('ves_shopbybrand_brands', 'brands_id');
    }
    /**
     * Assign brands to store views
     *
     * @param AbstractModel|\Vnecoms\ShopByBrand\Model\Brands $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {

        $this->saveProductRelation($object);

        return parent::_afterSave($object);
    }

    /**
     * @param BrandsModel $brand
     * @return array
     */
    public function getProductsPosition(BrandsModel $brand)
    {
        $select = $this->getConnection()->select()->from(
            $this->brandProductTable,
            ['product_id', 'position']
        )
            ->where(
                'brands_id = :brands_id'
            );
        $bind = ['brands_id' => (int)$brand->getId()];
        return $this->getConnection()->fetchPairs($select, $bind);
    }


    /**
     * @param BrandsModel $brand
     * @return $this
     */
    protected function saveProductRelation(BrandsModel $brand)
    {

        $brand->setIsChangedProductList(false);
        $id = $brand->getId();
        $products = $brand->getProductsData();
        if ($products === null) {
            return $this;
        }
        $oldProducts = $brand->getProductsPosition();
        $insert = array_diff_key($products, $oldProducts);

        $delete = array_diff_key($oldProducts, $products);
        $update = array_intersect_key($products, $oldProducts);
        $_update = array();
        foreach ($update as $key=>$settings) {
            if ( isset($oldProducts[$key]) && isset($settings['position']) &&
                $oldProducts[$key] != $settings['position']
            ) {
                $_update[$key] = $settings;
            }
        }
        $update = $_update;
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['product_id IN(?)' => array_keys($delete), 'brands_id=?' => $id];
            $adapter->delete($this->brandProductTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];

            foreach ($insert as $productId => $position) {

                $data[] = [
                    'brands_id' => (int)$id,
                    'product_id' => (int)$productId,
                    'position' => (int)$position['position']
                ];
            }
            $adapter->insertMultiple($this->brandProductTable, $data);
        }

        if (!empty($update)) {
            foreach ($update as $productId => $position) {
                $where = ['brands_id = ?' => (int)$id, 'product_id = ?' => (int)$productId];
                $bind = ['position' => (int)$position['position']];
                $adapter->update($this->brandProductTable, $bind, $where);
            }
        }

        if (!empty($insert) || !empty($delete)) {
            $productIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'vnecoms_shopbybrand_change_products',
                ['brands' => $brand, 'product_ids' => $productIds]
            );
        }

        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $brand->setIsChangedProductList(true);
            $productIds = array_keys($insert + $delete + $update);
            $brand->setAffectedProductIds($productIds);
        }
        return $this;
    }


}