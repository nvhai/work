<?php
/**
 * Copyright © 2018 IWD Agency - All rights reserved.
 * See LICENSE.txt bundled with this module for license details.
 */
namespace IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab;

use Magento\Framework\DataObject;
use IWD\BluePaySubs\Helper\Data as Helper;

/**
 * Class Frequency
 * @package IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab
 */
class Frequency extends \IWD\BluePaySubs\Block\Customer\Subscriptions\Edit\Tab
{
    /**
     * @return array
     */
    public function getFrequencies()
    {
        $frequencies = [];
        $subs = $this->getCurrentSubscription();
        if($options = $this->helper->getFrequenciesOptionArray($subs, true)) {
            foreach ($options as $id => $label) {
                $frequency = [];
                $isSelected = strripos((string) $label, (string) $this->getSummaryTabInfo(false));
                $frequency['value'] = $id;
                $frequency['is_selected'] = $isSelected !== false ?: false;
                $frequency['label'] = $label;
                $frequencies[] = new DataObject($frequency);
            }
        }

        return $frequencies;
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getSummaryTabInfo($withPrice = true)
    {
        $subs = $this->getCurrentSubscription();
        return $this->helper->generateOptionTitle($subs->getData(), $withPrice);
    }

    /**
     * @return string
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/bsubs_edit/frequency', ['id' => $this->getCurrentSubscription()->getId()]);
    }
}