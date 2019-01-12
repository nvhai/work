<?php
namespace Convert\CategoriesCollection\Observer;

use Magento\Framework\Exception\CouldNotSaveException;

class MinPriceEvent implements \Magento\Framework\Event\ObserverInterface
{
    private $category = null;
	
    protected $productRepository;
	
    protected $searchCriteria;
	
    protected $filterGroup;
	
    private $_configuableModel;
	
    private $productStatus;
	
    private $productVisibility;


	public function __construct(
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
		\Magento\Framework\Api\SearchCriteriaInterface $criteria,
		\Magento\Framework\Api\Search\FilterGroup $filterGroup,
		\Magento\Framework\Api\FilterBuilder $filterBuilder,
		\Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $configuableModel,
		\Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
		\Magento\Catalog\Model\Product\Visibility $productVisibility
	){
		$this->productRepository = $productRepository;
		$this->searchCriteria = $criteria;
		$this->filterGroup = $filterGroup;
		$this->filterBuilder = $filterBuilder;
		$this->_configuableModel = $configuableModel;
		$this->productStatus = $productStatus;
		$this->productVisibility = $productVisibility;
	}
	
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
         $this->category = $observer->getEvent()->getCategory();
         $request = $observer->getEvent()->getRequest()->getPostValue();
		 
		 if(isset($request['min_price']) && $request['min_price'] > 0){
			$productPositions = [];
			
			$this->filterGroup->setFilters([
				$this->filterBuilder
					->setField('special_price')
					->setValue($request['min_price'])
					->setConditionType('lt')
					->create()
			]);
			
			
			$this->searchCriteria->setFilterGroups([$this->filterGroup]);
			$products = $this->productRepository->getList($this->searchCriteria);
			$productCollection = $products->getItems();
			
			if(count($productCollection)){
				foreach ($productCollection as $product) {
					$parentProduct = $this->_configuableModel->getParentIdsByChild($product->getId());
					if(isset($parentProduct[0])){
						foreach($parentProduct as $_parentProduct){
							if (!array_key_exists($_parentProduct, $productPositions)) {
								$productPositions[$_parentProduct] = 0;
							}
						}
					}else {
						$productPositions[$product->getId()] = 0;
					}
				}
			}
			
			$this->category->setPostedProducts($productPositions);
			
			return $this->category;
		 }
		 
		 return;
    }
}