<?php
namespace Vnecoms\ShopByBrand\Controller\Adminhtml\Brands;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Backend\App\Action;
use Vnecoms\ShopByBrand\Model\BrandsFactory;
use Magento\Framework\Registry;
class Products extends Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;
    /**
     * @var BrandsFactory
     */
    protected $brandsFactory;
    /**
     * @var Registry
     */
    protected $_coreRegistry;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        LayoutFactory $resultLayoutFactory,
        PageFactory $resultPageFactory,
        BrandsFactory $brandsFactory
    ){
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->_coreRegistry = $registry;
        $this->brandsFactory = $brandsFactory;

    }
    public function execute(){
        $brandsId  = (int) $this->getRequest()->getParam('brands_id');
        /** @var \Vnecoms\ShopByBrand\Model\Brands */
        $brand    = $this->brandsFactory->create();
        if ($brandsId) {
            $brand->load($brandsId);
        }
        $this->_coreRegistry->register('shopbybrand_brands', $brand);

        $resultLayout = $this->resultLayoutFactory->create();
        /** @var \Vnecoms\ShopByBrand\Block\Adminhtml\Brand\Edit\Tab\Product $productsBlock */
        $productsBlock = $resultLayout->getLayout()->getBlock('brand.edit.tab.product');
        if ($productsBlock) {
          
            $productsBlock->setBrandsProducts($this->getRequest()->getPost('brand_product', null));
        }
        return $resultLayout;

    }
    /**
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vnecoms_ShopByBrand::shopbybrand');
    }

}