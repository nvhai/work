<?php
/**
 * Created by PhpStorm.
 * User: doanhcn2
 * Date: 20/03/2019
 * Time: 16:06
 */

namespace Magenest\Feedback\Model\ResourceModel;


use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Feedback extends AbstractDb
{

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        // TODO: Implement _construct() method.
        $this->_init('magenest_feedback', 'entity_id');
    }
}