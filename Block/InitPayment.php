<?php

namespace Magedev\Ccavenue\Block;

use Magento\Checkout\Model\Session;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Model\OrderFactory;
use Magedev\Ccavenue\Helper\Data as DataHelper;
use Magedev\Ccavenue\Model\Ccavenue;

class InitPayment extends Template
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var DataHelper
     */
    private $dataHelper;

    /**
     * @var Ccavenue
     */
    protected $_model;

    /**
     * @var OrderFactory
     */
    protected $salesOrderFactory;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        Ccavenue $model,
        OrderFactory $salesOrderFactory,
        DataHelper $dataHelper,
        array $data = []
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->_model = $model;
        $this->salesOrderFactory = $salesOrderFactory;
        $this->dataHelper = $dataHelper;
        parent::__construct($context, $data);
    }

    /**
     * Disable caching of block.
     *
     * @return null
     */
    public function getCacheLifetime()
    {
        return null;
    }

    private function getOrder($orderId)
    {
        return $this->salesOrderFactory->create()->load($orderId);
    }

    public function getAccessCode()
    {
        return $this->_model->getAccessCode();
    }

    public function getEncryptedData()
    {
        $orderId = $this->checkoutSession->getLastRealOrder()->getId();
        $order = $this->getOrder($orderId);
        return $this->_model->getEncryptedData($order);
    }

    public function generateUrl()
    {
        $orderId = $this->checkoutSession->getLastRealOrder()->getId();
        $order = $this->getOrder($orderId);

        $baseUrl = $this->_model->getServerBaseUrl();
        $queryData = [
            'command' => 'initiateTransaction',
            'encRequest' => $this->_model->getEncryptedData($order),
            'access_code' => $this->_model->getAccessCode()
        ];

        return $baseUrl.'?'. http_build_query($queryData);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->dataHelper->getRestartUrl();
    }
}