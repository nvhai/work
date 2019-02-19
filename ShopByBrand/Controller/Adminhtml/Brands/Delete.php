<?php
namespace Vnecoms\ShopByBrand\Controller\Adminhtml\Brands;

use Magento\Backend\App\Action;

class Delete extends Action
{
    protected $_model;

    /**
     * @param Action\Context $context
     * @param \Maxime\Jobs\Model\Department $model
     */
    public function __construct(
        Action\Context $context,
        \Vnecoms\ShopByBrand\Model\Brands $model
    ) {
        parent::__construct($context);
        $this->_model = $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vnecoms_ShopByBrand::shopbybrand');
    }

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('brands_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->_model;
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('Brand deleted'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['brands_id' => $id]);
            }
        }
        $this->messageManager->addError(__('Brand does not exist'));
        return $resultRedirect->setPath('*/*/');
    }
}
