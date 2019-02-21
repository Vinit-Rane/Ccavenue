<?php

namespace Magedev\Ccavenue\Logger;

use \Monolog\Logger;

/**
 * Class CcavenueLogger
 *
 * @package Magedev\Ccavenue\Logger
 */
class CcavenueLogger extends Logger
{
    public function __construct($name = 'payment_log')
    {
        $this->name = $name;
        parent::__construct($name);
    }

    /**
     * Add info data to Log
     *
     * @param $type
     * @param $data
     */
    public function addInfoLog($type, $data)
    {
        if (is_array($data)) {
            $this->addInfo($type . ': ' . json_encode($data));
        } elseif (is_object($data)) {
            $this->addInfo($type . ': ' . json_encode($data));
        } else {
            $this->addInfo($type . ': ' . $data);
        }
    }

    /**
     * Add error data to Log
     *
     * @param $type
     * @param $data
     */
    public function addErrorLog($type, $data)
    {
        if (is_array($data)) {
            $this->addError($type . ': ' . json_encode($data));
        } elseif (is_object($data)) {
            $this->addError($type . ': ' . json_encode($data));
        } else {
            $this->addError($type . ': ' . $data);
        }
    }
}
