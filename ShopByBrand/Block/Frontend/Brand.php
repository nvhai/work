<?php
/**
 * Created by PhpStorm.
 * User: nvhai
 * Date: 06/30/2016
 * Time: 02:34 PM
 */
namespace Vnecoms\ShopByBrand\Block\Frontend;

/**
 * One page checkout success page
 */
class Brand extends \Magento\Framework\View\Element\Template
{
    /**
     * Search constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Vnecoms\AdvancedFaq\Model\Source\Group $group
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context);

    }


}