<?php
/**
 * Created by PhpStorm.
 * User: doanhcn2
 * Date: 18/03/2019
 * Time: 13:32
 */

namespace Magenest\Feedback\Block\Customer;


use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use function PHPSTORM_META\elementType;

class Feedback extends Template
{
    protected $customerSession;
    protected $_orderCollectionFactory;
    protected $orderRepository;
    protected $_productLoader;
    protected $_storeManager;
    protected $feedbackCollectionFactory;
    protected $_productImageHelper;

    public function __construct(
        Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        Session $customerSession,
        \Magento\Catalog\Model\ProductFactory $_productLoader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magenest\Knockout\Model\ResourceModel\Feedback\CollectionFactory $feedbackCollectionFactory,
        \Magento\Catalog\Helper\Image $productImageHelper,
        array $data = [])
    {
        $this->customerSession = $customerSession;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->_productLoader = $_productLoader;
        $this->_storeManager = $storeManager;
        $this->feedbackCollectionFactory = $feedbackCollectionFactory;
        $this->_productImageHelper = $productImageHelper;
        parent::__construct($context, $data);
    }

    public function setActive($path)
    {
        $this->_activeLink = $this->_completePath($path);
        return $this;
    }

    public function getProductBought(){
        // get customer ID from session
        $customerId = $this->customerSession->getCustomer()->getId();
        // get order which bought by customer
        $orders = $this->_orderCollectionFactory->create()->addFieldToSelect('entity_id')->addFieldToFilter('customer_id', $customerId);
        $orderedProduct = [];
        // get product info
        foreach ($orders as $item){
            $order = $this->orderRepository->get($item['entity_id']);
            $orderedItems = $order->getAllVisibleItems();

            foreach ($orderedItems as $item) {
                $store = $this->_storeManager->getStore();
                $product = $this->_productLoader->create()->load($item->getData('product_id'));
                $orderedProduct[] = [
                    'order_id' => $order->getEntityId(),
                    'product_id' => $product->getId(),
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'image' => $this->_productImageHelper->init($product, 'product_thumbnail_image')
                        ->constrainOnly(TRUE)
                        ->keepAspectRatio(TRUE)
                        ->keepTransparency(TRUE)
                        ->keepFrame(FALSE)
                        ->resize(240, 300)->getUrl(),
                    'url' => $product->getProductUrl(),
                    'comment_status' => false,
                    'message' => '',
                    'customer_id' => $customerId
                ];
            }

        }
        return $orderedProduct;
    }

    public function getListProduct(){
        $listProducts = $this->getProductBought();
//        $feedbackCollection = $this->feedbackCollectionFactory->create();
        foreach ($listProducts as $index => $product){
            $feedback = $this->feedbackCollectionFactory->create()
                ->addFieldToFilter('order_id', $product['order_id'])
                ->addFieldToFilter('product_id', $product['product_id'])
                ->addFieldToFilter('customer_id', $product['customer_id']);
            if (count($feedback->getData())){
                $listProducts[$index]['comment_status'] = true;
                $listProducts[$index]['message'] = $feedback->getData()[0]['message'];
            }
        }

        return json_encode($listProducts);
    }

}