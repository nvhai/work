<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Convert\CategoriesCollection\Block;

/**
 * Main contact form block
 */
class Products extends \Magento\Catalog\Block\Product\AbstractProduct
{
	/**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * Product collection factory
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
	
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
	protected $_count;
	
	/**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;
	
	protected $date;
	
	/**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;
	
	protected $_resource;
	
	protected $_productloader;  
	
	protected $_productRepository;
	
	protected $_productsFactory;
	
	protected $_collectionConfigurable;
	
    /**
     * @param Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\App\Http\Context $httpContext,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\Url\Helper\Data $urlHelper,
		\Magento\Framework\Data\Form\FormKey $formKey,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\ProductFactory $_productloader,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory $collectionFactory,
		\Magento\Reports\Model\ResourceModel\Product\CollectionFactory $productsFactory,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $collectionConfigurable,
        array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
		$this->_objectManager = $objectManager;
        $this->httpContext = $httpContext;
		$this->urlHelper = $urlHelper;
		$this->_resource = $resource;
		$this->_productloader = $_productloader;
		$this->_collectionFactory = $collectionFactory;
		$this->_productRepository = $productRepository;
		$this->_productsFactory = $productsFactory;
		$this->formKey = $formKey;
		$this->_date = $date;
		$this->_collectionConfigurable = $collectionConfigurable;
        parent::__construct(
            $context,
            $data
        );
    }
	
	public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }
	
	public function getModel($model){
		return $this->_objectManager->create($model);
	}
	
	public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $url = $this->getAddToCartUrl($product);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED =>
                    $this->urlHelper->getEncodedUrl($url),
            ]
        ];
    }
	
	/**
     * Product collection initialize process
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|Object|\Magento\Framework\Data\Collection
     */
    public function getProductCollection()
    {
		$newCollection = [];
		
		$pageSize=($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 10;
		
		$productCollection = $this->_collectionFactory->create()
									->setModel('Magento\Catalog\Model\Product')
									->setPeriod('month');
									
		if($productCollection){
			$productCollections = $productCollection->getColumnValues('product_id');
			
			if($productCollections){
				$newCollection = $this->_productsFactory->create()
										->addFieldToFilter('entity_id',array('in',$productCollections))
										->setPageSize($pageSize)
										->setCurPage(1);
			}
		}
            
		return $newCollection;
    }
	
	/**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
   
	public function getProductById($productId){
		$parentProduct = $this->_collectionConfigurable->getParentIdsByChild($productId);
		if(isset($parentProduct[0])){
			return $this->_productRepository->getById($parentProduct[0]);
		}
		
		return $this->_productRepository->getById($productId);
	}
}

