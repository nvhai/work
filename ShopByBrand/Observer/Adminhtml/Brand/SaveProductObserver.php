<?php
namespace Vnecoms\ShopByBrand\Observer\Adminhtml\Brand;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js as JsHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Vnecoms\ShopByBrand\Model\ResourceModel\Brands;

class SaveProductObserver implements ObserverInterface
{
    protected $jsHelper;
    /**
     * @var \Vnecoms\ShopByBrand\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollection;
    /**
     * @var \Vnecoms\ShopByBrand\Model\ResourceModel\Brands\CollectionFactory
     */
    protected $brandsCollection;
    /**
     * @var \Vnecoms\ShopByBrand\Model\Product
     */
    protected $productModel;
    protected $_resources;

    public function __construct(
        Context $context,
        JsHelper $jsHelper,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Vnecoms\ShopByBrand\Model\Product $product,
        \Vnecoms\ShopByBrand\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Vnecoms\ShopByBrand\Model\ResourceModel\Brands\CollectionFactory $brandsCollectionFactory

    ){
        $this->jsHelper = $jsHelper;
        $this->_resources = $resourceConnection;
        $this->productModel = $product;
        $this->productCollection = $productCollectionFactory->create();
        $this->brandsCollection = $brandsCollectionFactory->create()->addOrder('brands_id',SORT_ASC);

    }
    /**
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $data = $observer;
        $connection= $this->_resources->getConnection();
        $productTable = $this->_resources->getTableName('ves_shopbybrand_brands_product');
        if(isset($data['brands_id'])){


        }else{
            $brand = $this->brandsCollection->addFieldToSelect('brands_id');
            $brand->getselect()->limit(1);
            foreach ($brand as $brands){
                $brandsId = $brands['brands_id'];
            }
            $product = $data['products'];
            $productArr = $this->jsHelper->decodeGridSerializedInput($product);
            foreach ($productArr as $key => $value){
                $position = $value['position'];
                $sql = "INSERT INTO " . $productTable . "(brands_id, product_id, position) VALUES ('$brandsId', '$key','$position')";
                $connection->query($sql);
            }


        }



    }
}
