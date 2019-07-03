<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-27
 * Time: 21:33
 */

namespace Alaric\Cache;

interface CacheInterface
{
    /**
     * Gets the last key stored by the cache
     * @param string $lastKey
     * @return boolean
     */
    public function setLastKey($lastKey);

    /**
     * Returns a cached content
     * @param string $keyName
     * @param int $lifetime
     * @return mixed
     */
    public function get($keyName, $lifetime = null);

    /**
     * Stores cached content into the file
     * @param string $keyName
     * @param string $content
     * @param int $lifetime
     * @return boolean
     */
    public function save($keyName = null, $content = null, $lifetime = null);

    /**
     * Deletes a value from the cache by its key
     * @param string $keyName
     * @return boolean
     */
    public function delete($keyName);

    /**
     * Query the existing cached keys
     * @param string $prefix
     * @return array
     */
    public function queryKeys($prefix = null);

    /**
     * Checks if cache exists and it hasn't expired
     * @param string $keyName
     * @param int $lifetime
     * @return boolean
     */
    public function exists($keyName, $lifetime = null);

    /**
     * Immediately invalidates all existing items.
     * @return boolean
     */
    public function flush();
}