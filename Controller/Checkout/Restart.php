<?php

namespace Magedev\Ccavenue\Controller\Checkout;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;

/**
 * Class Restart
 * @package Magedev\Ccavenue\Controller\Checkout
 */
class Restart extends Action
{

    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * Reset constructor.
     *
     * @param Context $context
     * @param Session $checkoutSession
     */
    public function __construct(
        Context $context,
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    /**
     * Return from loading page after back button.
     */
    public function execute()
    {
        $this->messageManager->addNoticeMessage(__('Payment cancelled, please try again.'));
        $this->checkoutSession->restoreQuote();
        $this->_redirect('checkout/cart');
    }
}
