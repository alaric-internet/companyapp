<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-27
 * Time: 21:28
 */

namespace Alaric;

use Alaric\Cache\Factory as CacheFactory;
use Alaric\CacheInterface;


class Cache implements CacheInterface
{
    /**
     * @var \Alaric\Cache\File
     */
    public $adapter;

    /**
     * Cache constructor.
     * @param array $config
     * @throws Exception
     */
    public function __construct($config) {
        $this->adapter = CacheFactory::load($config);
    }

    /**
     * Gets the last key stored by the cache
     * @param string $lastKey
     * @return boolean
     */
    public function setLastKey($lastKey)
    {
        return $this->adapter->setLastKey($lastKey);
    }

    /**
     * Returns a cached content
     * @param string $keyName
     * @param int    $lifetime
     * @return mixed
     * @throws Exception
     */
    public function get($keyName, $lifetime = null)
    {
        return $this->adapter->get($keyName, $lifetime);
    }

    /**
     * Stores cached content into the file
     * @param string $keyName
     * @param string $content
     * @param int    $lifetime
     * @return boolean
     * @throws Exception
     */
    public function save($keyName = null, $content = null, $lifetime = null)
    {
        return $this->adapter->save($keyName, $content, $lifetime);
    }

    /**
     * Deletes a value from the cache by its key
     * @param string $keyName
     * @return boolean
     */
    public function delete($keyName)
    {
        return $this->adapter->delete($keyName);
    }

    /**
     * Query the existing cached keys
     * @param string $prefix
     * @return array
     */
    public function queryKeys($prefix)
    {
        return $this->adapter->queryKeys();
    }

    /**
     * Checks if cache exists and it hasn't expired
     * @param string $keyName
     * @param int    $lifetime
     * @return boolean
     * @throws Exception
     */
    public function exists($keyName, $lifetime = null)
    {
        return $this->adapter->exists($keyName, $lifetime);
    }

    /**
     * Immediately invalidates all existing items.
     * @return boolean
     */
    public function flush()
    {
        return $this->adapter->flush();
    }
}