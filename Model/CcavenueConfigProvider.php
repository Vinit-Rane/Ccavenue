<?php

namespace Magedev\Ccavenue\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magedev\Ccavenue\Model\Ccavenue;

class CcavenueConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCode = Ccavenue::PAYMENT_METHOD_CCAVENUE_CODE;

    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $method;

    /**
     * @var Ccavenue
     */
    protected $ccavenue;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * CcavenueConfigProvider constructor.
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     * @param Ccavenue $ccavenue
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        Ccavenue $ccavenue
    ) {
        $this->escaper = $escaper;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->ccavenue = $ccavenue;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                'ccavenue' => [
                    "title" => $this->ccavenue->getTitle(),
                     "url" => (string)$this->ccavenue->getRequestUrl()
                ],
            ],
        ] : [];
    }
}
