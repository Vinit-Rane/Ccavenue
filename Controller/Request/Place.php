<?php

namespace Magedev\Ccavenue\Controller\Request;

use Magedev\Ccavenue\Helper\Data as DataHelper;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magedev\Ccavenue\Model\Ccavenue;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Place extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var DataHelper
     */
    protected $dataHelper;
    /**
     * @var PaymentHelper
     */
    protected $paymentHelper;

    protected $resultPageFactory;

    public function __construct(
        Context $context,
        DataHelper $dataHelper,
        PaymentHelper $paymentHelper,
        Session $checkoutSession,
        PageFactory $resultPageFactory
    )
    {
        $this->dataHelper = $dataHelper;
        $this->paymentHelper = $paymentHelper;
        $this->_checkoutSession = $checkoutSession;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Send request to Ccavenue
     */
    public function execute()
    {
        try{
            $order = $this->_checkoutSession->getLastRealOrder();

            if (!$order) {
                $msg = __('Order not found.');
                $this->dataHelper->addTolog('error', $msg);
                $this->_redirect('checkout/cart');
                return;
            }

            $payment = $order->getPayment();
            if (!isset($payment)) {
                $this->_redirect('checkout/cart');
                return;
            }

            $method = $order->getPayment()->getMethod();
            $methodInstance = $this->paymentHelper->getMethodInstance($method);
            if ($methodInstance instanceof Ccavenue) {
                $resultPage = $this->resultPageFactory->create();
                return $resultPage;
            } else {
                $msg = __('Payment Method not found');
                $this->messageManager->addErrorMessage($msg);
                $this->dataHelper->addTolog('error', $msg);
                $this->_checkoutSession->restoreQuote();
                $this->_redirect('checkout/cart');
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e){
            $this->messageManager->addError($e->getMessage());
            $this->_checkoutSession->restoreQuote();
            $this->_redirect('checkout/cart');
        } catch (\Exception $e){
            $this->_checkoutSession->restoreQuote();
            //$this->messageManager->addError('Something went wrong while processing your order. Please try again later.');
            $this->messageManager->addError($e->getMessage());
            $this->_redirect('checkout/cart');
        }
    }

}