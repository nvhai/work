<?php
namespace Vnecoms\ShopByBrand\Controller\Adminhtml\Brands;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\RedirectFactory;
use Vnecoms\ShopByBrand\Model\Brand\Image as ImageModel;
use Vnecoms\ShopByBrand\Model\Upload;
use Magento\Backend\Helper\Js as JsHelper;
class Save extends Action
{
    /**
     * @var \Vnecoms\ShopByBrand\Model\Brands
     */
    protected $_model;
    /**
     * @var Upload
     */
    protected $_uploadModel;
    /**
     * @var ImageModel
     */
   protected $_imageModel;
    /**
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;
    /**
     * @var \Vnecoms\ShopByBrand\Model\ResourceModel\Brands\CollectionFactory
     */
    protected $_brandsCollection;

    /**
     * @param Action\Context $context

     */
    public function __construct(
        Action\Context $context,
        Registry $registry,
        RedirectFactory $redirectFactory,
        ImageModel $imageModel,
        Upload $upload,
        JsHelper $jsHelper,
        \Vnecoms\ShopByBrand\Model\Brands $model,
        \Vnecoms\ShopByBrand\Model\ResourceModel\Brands\CollectionFactory $brandsCollectionFactory
    ) {
        parent::__construct($context);
        $this->jsHelper = $jsHelper;
        $this->_model = $model;
        $this->_uploadModel =$upload;
        $this->_imageModel = $imageModel;
        $this->_brandsCollection = $brandsCollectionFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Vnecoms_ShopByBrand::shopbybrand');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            /** @var \Vnecoms\ShopByBrand\Model\Brands $model */
            $model = $this->_model;
            $id = $this->getRequest()->getParam('brands_id');
            if ($id) {
                $model->load($id);
            }

            $logo = $this->_uploadModel->uploadFileAndGetName('brand_thumbnail_image', $this->_imageModel->getBaseDir(), $data);
            $data['brand_thumbnail_image'] = $logo;
            $banner = $this->_uploadModel->uploadFileAndGetName('brand_image', $this->_imageModel->getBaseDir(), $data);
            $data['brand_image'] = $banner;

            try {
                if(!isset($data['brands_id'])){
                    $checkBrand = $this->_brandsCollection
                        ->addFieldToFilter(
                            array(
                                'brand_name',
                                'url_key'),
                            array(
                                $data['brand_name'],
                                $data['url_key'])
                        )->getData();
                    if($checkBrand!=null){
                        $this->messageManager->addSuccess(__('Brand exist'));
                        return $resultRedirect->setPath('*/*/');
                   }
                }
                $model->setData($data);
                $products = $this->getRequest()->getPost('products', -1);
                
                if ($products != -1) {
                    $model->setProductsData($this->jsHelper->decodeGridSerializedInput($products));
                }
                $this->_eventManager->dispatch(
                    'shopbybrand_brands_prepare_save',
                    [
                        'brand' => $model,
                        'request' => $this->getRequest()
                    ]
                );
                $model->save();
                $this->messageManager->addSuccess(__('Brand saved'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['brands_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the brand'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['brands_id' => $this->getRequest()->getParam('brands_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}