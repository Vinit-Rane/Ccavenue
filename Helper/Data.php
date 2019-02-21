<?php

namespace Magedev\Ccavenue\Helper;

use Magento\Framework\App\Helper\Context;
use Magedev\Ccavenue\Logger\CcavenueLogger;


class Data extends \Magento\Framework\App\Helper\AbstractHelper{

    const XML_PATH_DEBUG = 'payment/ccavenue/debug';

    /**
     * Path to store config if extension is enabled
     *
     * @var string
     */
    const PAYMENT_CCAVENUE_ACTIVE = 'payment/ccavenue/active';

    /**
     * @var CcavenueLogger
     */
    private $logger;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;


    public function __construct(
        Context $context,
        CcavenueLogger $logger
    ) {
        $this->logger = $logger;
        $this->urlBuilder = $context->getUrlBuilder();
        parent::__construct($context);
    }

    /**
     * Check if extension enabled
     *
     * @return string|null
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(
            self::PAYMENT_CCAVENUE_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Restart Url Builder
     *
     * @return string
     */
    public function getRestartUrl()
    {
        return $this->urlBuilder->getUrl('ccavenue/checkout/restart/');
    }

    /**
     * Write to log
     *
     * @param $type
     * @param $data
     */
    public function addTolog($type, $data)
    {
        $debug = $this->getConfig(self::XML_PATH_DEBUG);
        if ($debug) {
            if ($type == 'error') {
                $this->logger->addErrorLog($type, $data);
            } else {
                $this->logger->addInfoLog($type, $data);
            }
        }
    }
}