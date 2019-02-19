<?php
namespace Vnecoms\ShopByBrand\Model;

use \Magento\Framework\Model\AbstractModel;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\Db;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
/**
 * @method Brands setUpdatedAt(\string $date)
 * @method Brands setAvatar(\string $avatar)
 * @method string getAvatar()
 * @method ResourceModel\Brands _getResource()
 * @method ResourceModel\Brands getResource()
 * @method Brands setProductsData(array $products)
 * @method Brands setIsChangedProductList(\bool $changed)
 * @method Brands setAffectedProductIds(array $productIds)
 * @method array|null getProductsData()
 * @method int getPosition()
 */
class Brands extends AbstractModel
{
    const BRANDS_ID = 'brands_id'; // We define the id fieldname
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'shopbybrand'; // parent value is 'core_abstract'

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;


    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $productCollection;

    /**
     * Name of object id field
     *
     * @var string
     */
    protected $_idFieldName = self::BRANDS_ID;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     * @param FilterManager $filter
     * @param Context $context
     * @param Registry $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        FilterManager $filter,
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ){
        $this->productCollectionFactory  = $productCollectionFactory;
        $this->filter                    = $filter;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    protected function _construct()
    {
        $this->_init('Vnecoms\ShopByBrand\Model\ResourceModel\Brands');
    }
    /**
     * @return array|mixed
     */
    public function getProductsPosition()
    {

        if (!$this->getId()) {
            return array();
        }
        $array = $this->getData('products_position');
        if (is_null($array)) {
            $array = $this->getResource()->getProductsPosition($this);
            $this->setData('products_position', $array);
        }
        return $array;
    }
    /**
     * @param string $attributes
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getSelectedProductsCollection($attributes = '*')
    {
        if (is_null($this->productCollection)) {
            $collection = $this->productCollectionFactory->create();
            $collection->addAttributeToSelect($attributes);
            $collection->joinField(
                'position',
                'ves_shopbybrand_brands_product',
                'position',
                'product_id=entity_id',
                '{{table}}.brands_id='.$this->getId(),
                'inner'
            );
            $this->productCollection = $collection;
        }
        return $this->productCollection;
    }
}

