<?php
/**
 * Created by PhpStorm.
 * User: doanhcn2
 * Date: 20/03/2019
 * Time: 16:03
 */

namespace Magenest\Feedback\Model;



use Magento\Framework\Model\AbstractModel;

class Feedback extends AbstractModel
{
    public function _construct()
    {
        $this->_init('Magenest\Feedback\Model\ResourceModel\Feedback');
    }
}