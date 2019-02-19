<?php
namespace Vnecoms\ShopByBrand\Block\Adminhtml\Brand\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
class Main extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface
     */
    protected $_groupRepository;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Convert\DataObject
     */
    protected $_objectConverter;

    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Vnecoms\ShopByBrand\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Vnecoms\ShopByBrand\Model\Config\Source\Yesno
     */
    protected $_yesNo;

    /**
     * @var \Vnecoms\ShopByBrand\Model\Config\Source\Status
     */
    protected $_brandStatus;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Api\GroupRepositoryInterface $groupRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Convert\DataObject $objectConverter
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Api\GroupRepositoryInterface $groupRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Convert\DataObject $objectConverter,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\ObjectManager\ObjectManager $objectManager,
        \Vnecoms\ShopByBrand\Model\Wysiwyg\Config $wysiwygConfig,
        \Vnecoms\ShopByBrand\Model\Config\Source\Yesno $yesNo,
        \Vnecoms\ShopByBrand\Model\Config\Source\Status $status,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_groupRepository = $groupRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_objectConverter = $objectConverter;
        $this->_objectManager = $objectManager;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->_yesNo = $yesNo;
        $this->_brandStatus = $status;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare content for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabLabel()
    {
        return __(' General Information ');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     * @codeCoverageIgnore
     */
    public function getTabTitle()
    {
        return __(' General Information ');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     * @codeCoverageIgnore
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Form
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('brands_form');
        $this->setTitle(__('Brand Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Vnecoms\ShopByBrand\Model\Brands $model */
        $model = $this->_coreRegistry->registry('shopbybrand_brands');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('brand_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('General Information')]
        );

        if ($model->getId()) {
            $fieldset->addField('brands_id', 'hidden', ['name' => 'brands_id']);
        }

        $fieldset->addField(
            'brand_name',
            'text',
            [
                'name' => 'brand_name',
                'label' => __(' Name'),
                'title' => __(' Name'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __(' Sort Order'),
                'title' => __(' Sort Order'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'url_key',
            'text',
            [
                'name' => 'url_key',
                'label' => __('Url Key'),
                'title' => __('Url Key'),
                'required' => true
            ]
        );
        $fieldset->addField(
            'page_title',
            'text',
            [
                'name' => 'page_title',
                'label' => __('Page Title'),
                'title' => __('Page Title'),
                'required' => false
            ]
        );
        $fieldset->addType('image', '\Vnecoms\ShopByBrand\Block\Adminhtml\Brand\Helper\Image');
        $fieldset->addField(
            'brand_thumbnail_image',
            'image',
            [
                'name' => 'brand_thumbnail_image',
                'label' => __('Logo'),
                'title' => __('Logo')
            ]
        );
        
        $fieldset->addField(
            'brand_image',
            'image',
            [
                'name' => 'brand_image',
                'label' => __('Banner'),
                'title' => __('Banner')
            ]
        );
        
        /*logo*/

        $fieldset->addField(
            'banner_url',
            'text',
            [
                'name' => 'banner_url',
                'label' => __('Banner click-through URL'),
                'title' => __('Banner click-through URL'),
                'required' => false
            ]
        );
        $fieldset->addField(
            'brand_is_featured',
            'select',
            [
                'name' => 'brand_is_featured',
                'label' => __('Is Featured'),
                'title' => __('Is Featured'),
                'required' => false,
                'options' =>$this->_yesNo->getOptionArray()
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => false,
                'options' =>$this->_brandStatus->getOptionArray()
            ]
        );
        $wysiwygConfig = $this->_wysiwygConfig->getConfig();
        $fieldset->addField(
            'short_description',
            'editor',
            [
                'name' => 'short_description',
                'label' => __('Short Description'),
                'title' => __('Short Description'),
                'required' => false,
                'config'  =>$wysiwygConfig
            ]
        );
        $fieldset->addField(
            'description',
            'editor',
            [
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'required' => false,
                'config'  =>$wysiwygConfig
            ]
        );
        $fieldset->addField(
            'meta_keywords',
            'textarea',
            [
                'name' => 'meta_keywords',
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords'),
                'required' => false
            ]
        );
        $fieldset->addField(
            'meta_description',
            'textarea',
            [
                'name' => 'meta_description',
                'label' => __('Meta Description'),
                'title' => __('Meta Description'),
                'required' => false
            ]
        );

        if($model->getData('image')){
            $model->setData('image','learning/images'.$model->getData('image'));
        }
        $form->setValues($model->getData());
        //$form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
