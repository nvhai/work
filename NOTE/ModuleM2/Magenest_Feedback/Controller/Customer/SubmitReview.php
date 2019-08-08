<?php
/**
 * Created by PhpStorm.
 * User: doanhcn2
 * Date: 20/03/2019
 * Time: 15:17
 */

namespace Magenest\Feedback\Controller\Customer;


use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class SubmitReview extends Action
{
    protected $feedbackFactory;

    public function __construct(Context $context, \Magenest\Feedback\Model\FeedbackFactory $feedbackFactory)
    {
        $this->feedbackFactory = $feedbackFactory;
        parent::__construct($context);
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        // TODO: Implement execute() method.
        $response = $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
            ->setData([
                'status'  => true,
            ]);
        try {
            $formDataRequest = $this->getRequest()->getParams();
            $feedbackModel = $this->feedbackFactory->create();
            $feedbackModel->setData($formDataRequest);
            $feedbackModel->save();

        } catch (\Exception $exception){
            $response = $this->resultFactory
                ->create(\Magento\Framework\Controller\ResultFactory::TYPE_JSON)
                ->setData([
                    'status'  => false,
                    'message' => $exception
                ]);
        } finally {
            return $response;
        }
    }
}