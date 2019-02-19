<?php
namespace Vnecoms\ShopByBrand\Controller\Adminhtml\Brands;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action;
class Index extends Action
{

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context,PageFactory $resultPageFactory){
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
    public function execute(){
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Vnecoms_ShopByBrand::shopbybrand');
        $resultPage->addBreadcrumb(__('Manage Brand'), __('Manage Brand'));
        $resultPage->addBreadcrumb(__('Manage Brand'), __('Manage Brand'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Brand'));

        return $resultPage;
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