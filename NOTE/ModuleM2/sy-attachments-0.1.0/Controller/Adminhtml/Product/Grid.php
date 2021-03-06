<?php
/**
 * Attachments
 * 
 * @author Slava Yurthev
 */
namespace SY\Attachments\Controller\Adminhtml\Product;

class Grid extends \Magento\Backend\App\Action {
	protected $resultRawFactory;
	protected $layoutFactory;
	public function __construct(
			\Magento\Backend\App\Action\Context $context,
			\Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
			\Magento\Framework\View\LayoutFactory $layoutFactory
		){
		parent::__construct($context);
		$this->resultRawFactory = $resultRawFactory;
		$this->layoutFactory = $layoutFactory;
	}
	public function execute(){
		$resultRaw = $this->resultRawFactory->create();
		return $resultRaw->setContents(
			$this->layoutFactory->create()->createBlock(
				'SY\Attachments\Block\Adminhtml\Catalog\Product\Edit\Tab\Attachments',
				'catalog.product.attachments.grid'
			)->toHtml()
		);
	}
	protected function _isAllowed(){
		return $this->_authorization->isAllowed('SY_Attachments::index');
	}
}