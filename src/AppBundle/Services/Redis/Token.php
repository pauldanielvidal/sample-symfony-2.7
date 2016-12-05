<?php

namespace AppBundle\Service\Redis;

use Psr\Log\LoggerInterface;

class Token extends Base
{
    protected $tokenPrefix;
    protected $ttl;

    public function __construct( array $config, LoggerInterface $logger)
    {
        parent::__construct($config,$logger);

        $this->tokenPrefix =    "box:token_";
        $this->ttl =            50*60; //50 minutes
    }

    /**
     * Function to get box token of a user
     * @param String $peoplesoftId peoplesoftid of the user
     **
     * @return String
     */
    public function getBoxToken($peoplesoftId) {
        $this->log("RETRIEVING BOX TOKEN FOR " . $peoplesoftId);

        $key = $this->tokenPrefix . $peoplesoftId;

        $value = $this->redis->get($key);

        return $value;
    }

    /**
     * Function to save box token of a user
     * @param String $peoplesoftId peoplesoftid of the user
     * @param String $value value to be saved
     *
     * @return String
     */
    public function setBoxToken($peoplesoftId,$value) {
        $this->log("SETTING NEW BOX TOKEN FOR " . $peoplesoftId);

        $key = $this->tokenPrefix . $peoplesoftId;

        $newValue = "";
        if( $this->redis->set($key,$value,$this->ttl) ) {
            $newValue = $this->redis->get($key);
        }

        return $newValue;
    }
}
