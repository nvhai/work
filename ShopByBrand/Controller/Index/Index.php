<?php
/**
 * Created by PhpStorm.
 * User: nvhai
 * Date: 06/30/2016
 * Time: 02:28 PM
 */
namespace Vnecoms\ShopByBrand\Controller\Index;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
class Index extends Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resutlPageFactory;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlinterface;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfigObject;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigObject
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\UrlInterface $urlinterface
     */

    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigObject,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\UrlInterface $urlinterface
    ){
        parent::__construct($context);
        $this->_resutlPageFactory = $pageFactory;
        $this->_scopeConfigObject = $scopeConfigObject;
        $this->_session = $session;
        $this->_urlinterface = $urlinterface;
    }
    public function execute(){
        $result = $this->_resutlPageFactory->create();
        return $result;
    }
}
