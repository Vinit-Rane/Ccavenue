<?php

namespace Magedev\Ccavenue\Controller\Response;

use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\Order\Payment\Transaction;
use \Magento\Framework\App\Action\Context;
use Magedev\Ccavenue\Model\Ccavenue;

class cancel extends \Magento\Framework\App\Action\Action
{
    const CARD_NAME = 'card_name';
    const PAYMENT_MODE = 'payment_mode';
    const BANK_REF_NO = 'bank_ref_no';
    const TRACKING_ID = 'tracking_id';
    const ORDER_ID = 'order_id';
    const ORDER_STATUS = 'order_status';

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    protected $_model;

    /**
     * cancel constructor.
     * @param Context $context
     * @param Ccavenue $model
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        Ccavenue $model,
        OrderFactory $orderFactory
    ) {
        parent::__construct($context);

        $this->_model = $model;
        $this->_orderFactory = $orderFactory;
    }

    public function execute(){
        $responseData = [];
        $encResponse = $_POST["encResp"];

        $decryptedText = $this->_model->getDecryptedData($encResponse);
        $decryptValues = explode('&', $decryptedText);
        $dataSize = sizeof($decryptValues);
        for($i = 0; $i < $dataSize; $i++)
        {
            $information=explode('=',$decryptValues[$i]);
            $responseData[$information[0]] = $information[1];
        }

        $status = isset($responseData['order_status']) ? strtolower($responseData['order_status']) : '';
        //$failureMessage = isset($responseData['failure_message']) ? $responseData['failure_message']: 'The order has been cancelled.';
        $failureMessage = isset($responseData['status_message']) ? $responseData['status_message']: 'The order has been cancelled.';

        $order = $this->getOrder($responseData['order_id']);
        try{

            $additionalInformation = [
                self::PAYMENT_MODE => $responseData[self::PAYMENT_MODE],
                self::CARD_NAME => $responseData[self::CARD_NAME],
                self::TRACKING_ID => $responseData[self::TRACKING_ID],
                self::BANK_REF_NO => $responseData[self::BANK_REF_NO],
                'status_code' => $responseData['status_code'],
                'order_status' => $responseData['order_status'],
            ];

            $payment = $order->getPayment();
            $payment->setLastTransId($responseData[self::TRACKING_ID]);
            $payment->setTransactionId($responseData[self::TRACKING_ID]);
            $payment->setCcType($responseData[self::CARD_NAME]);
            $payment->setBankRefNo($responseData[self::BANK_REF_NO]);
            $payment->setPaymentMode($responseData[self::PAYMENT_MODE]);
            $payment->setOrderStatus($responseData[self::ORDER_STATUS]);

            // set card details to additional info
            $payment->setAdditionalData(json_encode($additionalInformation));
            foreach ($additionalInformation as $key => $value){
                $payment->setTransactionAdditionalInfo($key, $value);
            }
            $transaction = $payment->addTransaction(Transaction::TYPE_VOID);
            $transaction->setIsClosed(1);
            $transaction->save();
            $payment->addTransactionCommentsToOrder(
                $transaction,
                $failureMessage
            );
            $payment->setParentTransactionId(null);
            $payment->save();

            $this->declineOrder($order, $failureMessage);
            $this->messageManager->addError($failureMessage);
            return $this->resultRedirectFactory->create()->setUrl($this->getFailureUrl());
        } catch (\Exception $e){
            if($order && $order->getId()){
                $this->declineOrder($order, $failureMessage);
            }
            $this->messageManager->addError('Something went wrong while processing your order. Please try again later.');
            return $this->resultRedirectFactory->create()->setUrl($this->getFailureUrl());
        }
    }

    public function getFailureUrl(){
        $url = $this->_objectManager->get('\Magento\Framework\UrlInterface');
        return $url->getUrl('checkout/onepage/failure');
    }

    private function getOrder($orderId)
    {
        return $this->_orderFactory->create()->loadByIncrementId($orderId);
    }

    protected function declineOrder(\Magento\Sales\Model\Order $order, $message)
    {
        try {
            $order->registerCancellation($message)->save();
        } catch (\Exception $e) {

        }
    }

}