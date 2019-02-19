<?php
namespace Gofoods\Mmegamenu\Plugin\Block\Adminhtml\User\Edit\Tab;

class Main
{
	private $registry;

    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }
    /**
     * Get form HTML
     *
     * @return string
     */
    public function aroundGetFormHtml(
        \MGS\Mmegamenu\Plugin\Block\Adminhtml\User\Edit\Tab $subject,
        \Closure $proceed
    )
    {
        $form = $subject->getForm();
        if (is_object($form)) {
             $baseFieldset = $form->getElement('base_fieldset');
            /** @var $model \Magento\User\Model\User */
            $model = $this->registry->registry('mmegamenu_mmegamenu');
            $data = $model->getData();
            $baseFieldset->addField(
                'icon-menu',
                'image',
                [
                    'name' => 'icon-menu',
                    'label' => __('Icon Menu'),
                    'title' => __('Icon-Menu'),                
                    'class' => 'select',
                    'required' => true
                    
                ]
            );

            $subject->setForm($form);
        }

        return $proceed();

        
    }
}