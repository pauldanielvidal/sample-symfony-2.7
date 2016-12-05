<?php

namespace AppBundle\Service\Redis;

use Psr\Log\LoggerInterface;

use Redis;

/**
 * Redis Service implementation
 *     tested with:
 *          "snc/redis-bundle": "~2.0"
 *          "symfony/monolog-bundle": "~2.4"
 *          "symfony/monolog-bridge": "~2.4"
 *
 * PHP version 5.6
 *
 */
class Base
{
    protected $logger;

    protected $env;

    protected $redis;
    protected $redisPrefix;

    protected $logUuid;

    public function __construct( array $config, LoggerInterface $logger)
    {
        $this->logger               = $logger;

        $this->env                  = $config['symfony_environment'];

        $this->redisPrefix          = "study:" . $this->env . ":";

        $this->redis = new Redis();
        $this->redis->connect($config['redis_host'], $config['redis_port']);
        $this->redis->setOption(Redis::OPT_PREFIX, $this->redisPrefix);
    }

    /**
     * Function that logs a message, prefixing the Class and function name to help debug
     *
     * @param String $msg Message to be logged
     *
     **/
    protected function log($msg)
    {
        $matches = array();

        preg_match('/[^\\\\]+$/', get_class($this), $matches);

        if( $this->logUuid ) {
            $this->logger->info(
                $this->logUuid
                . " Redis Service: "
                . $matches[0]
                . ":"
                . debug_backtrace()[1]['function']
                . " - "
                . $msg
            );
        } else {
            $this->logger->info(
                'Redis Service: '
                . $matches[0]
                . ":"
                . debug_backtrace()[1]['function']
                . " - "
                . $msg);
        }
    }

    /**
     * Function to get the value of the given key with the preset PREFIX
     * @param String $key redis key to be searched
     **
     * @return String
     */
    public function get($key) {
        $this->log('RETRIEVING VALUE for ' . $this->redisPrefix . $key);

        $value = $this->redis->get($key);

        return $value;
    }

    /**
     * Function to get the value of the given key EXACT MATCH
     * @param String $key redis key to be searched
     **
     * @return String
     */
    public function getExact($key) {
        $this->log('RETRIEVING VALUE for ' . $key);

        $this->redis->setOption(Redis::OPT_PREFIX, "");

        $value = $this->redis->get($key);

        $this->redis->setOption(Redis::OPT_PREFIX, $this->redisPrefix);

        return $value;
    }

    /**
     * Function to set the value of the given key with the preset PREFIX
     * @param String $key redis key to be searched
     * @param String $value value to be saved
     * @param int $ttl number of seconds until this key would auto expire;
     **
     * @return String
     */
    public function set($key,$value,$ttl=-1) {
        $this->log('SETTING VALUE for ' . $this->redisPrefix. $key);

        $newValue = "";
        if( $this->redis->set($key,$value,$ttl) ) {
            $newValue = $this->redis->get($key);
        }

        return $newValue;
    }

    /**
     * Function to set the value of the given key EXACT MATCH
     * @param String $key redis key to be searched
     * @param String $value value to be saved
     * @param int $ttl number of seconds until this key would auto expire;
     **
     * @return String
     */
    public function setExact($key,$value,$ttl=-1) {
        $this->log('SETTING VALUE for ' . $key);

        $this->redis->setOption(Redis::OPT_PREFIX, "");

        $newValue = "";
        if( $this->redis->set($key,$value,$ttl) ) {
            $newValue = $this->redis->get($key);
        }

        $this->redis->setOption(Redis::OPT_PREFIX, $this->redisPrefix);

        return $newValue;
    }

    public function setLogUuid($logUuid) {
        $this->logUuid = $logUuid;
    }

}
