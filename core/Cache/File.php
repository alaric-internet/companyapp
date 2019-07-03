<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-27
 * Time: 21:28
 */

namespace Alaric\Cache;

use DirectoryIterator;
use Exception;

class File implements CacheInterface
{
    protected $_options = [];

    protected $_prefix = "";

    protected $_lastKey = "";

    protected $_lastLifetime = null;

    protected $_fresh = false;

    protected $_started = false;

    /**
     * File constructor.
     * @param array $options
     * @throws Exception
     */
    public function __construct($options) {
        if(!isset($options["cacheDir"])) {
            throw new Exception("Cache directory must be specified with the option cacheDir");
        }

        if($options["prefix"]){
            $this->_prefix = $options["prefix"];
        }

        $this->_options = $options;
    }

    /**
     * Gets the last key stored by the cache
     * @param string $lastKey
     * @return boolean
     */
    public function setLastKey($lastKey)
    {
        $this->_lastKey = $this->_prefix . $this->getKey($lastKey);

        return true;
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
        $this->_lastKey = $this->_prefix . $this->getKey($keyName);

        $cacheFile = $this->_options['cacheDir'] . $this->_lastKey;

        if(file_exists($cacheFile)){
            clearstatcache(true, $cacheFile);
            $modifiedTime = filemtime($cacheFile);

            $cachedContent = file_get_contents($cacheFile);
            if($cachedContent === false) {
                throw new Exception("Cache file ". $cacheFile. " could not be opened");
            }

            $cachedContent = unserialize($cachedContent);

            if(!$lifetime){
                $lifetime = $cachedContent['lifetime'];
            }

            if($lifetime + $modifiedTime > time()){
                return $cachedContent['content'];
            }
        }

        return null;
    }

    /**
     * Stores cached content into the file
     * @param string  $keyName
     * @param string  $content
     * @param int     $lifetime
     * @return boolean
     * @throws Exception
     */
    public function save($keyName = null, $content = null, $lifetime = null)
    {
        if($keyName){
            $this->_lastKey = $this->_prefix . $this->getKey($keyName);
        }

        if(!$this->_lastKey){
            throw new Exception("Cache must be started first");
        }

        $cacheFile = $this->_options['cacheDir'] . $this->_lastKey;

        $cacheContent = [
            "lifetime" => $lifetime,
            "content"  => $content
        ];

        $status = file_put_contents($cacheFile, serialize($cacheContent));

        if($status === false){
            throw new Exception("Cache file ". $cacheFile . " could not be written");
        }

        return $status !== false;
    }

    /**
     * Deletes a value from the cache by its key
     * @param string $keyName
     * @return boolean
     */
    public function delete($keyName)
    {
        $cacheFile = $this->_options['cacheDir'] . $this->_prefix . $this->getKey($keyName);

        if(file_exists($cacheFile)){
            return unlink($cacheFile);
        }

        return false;
    }

    /**
     * Query the existing cached keys
     * @param string $prefix
     * @return array
     */
    public function queryKeys($prefix = null)
    {
        $keys = [];
        $prefixedKey = $this->_prefix;

        if($prefix){
            $prefixedKey = $this->_prefix . $this->getKey($prefix);
        }

        $cacheFiles = new DirectoryIterator($this->_options['cacheDir']);
        foreach ($cacheFiles as $item){
            if($item->isDir() === false){
                $key = $item->getFilename();

                if($prefixedKey) {
                    if (strpos($key, $prefixedKey) === 0) {
                        $keys[] = $key;
                    }
                }else{
                    $keys[] = $key;
                }
            }
        }

        return $keys;
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
        if(!$keyName){
            $lastKey = $this->_lastKey;
        }else{
            $lastKey = $this->_prefix . $this->getKey($keyName);
        }

        if($lastKey){
            $cacheFile = $this->_options['cacheDir'] . $lastKey;

            if(file_exists($cacheFile)){
                if(!$lifetime){
                    $cachedContent = file_get_contents($cacheFile);
                    if($cachedContent === false) {
                        throw new Exception("Cache file ". $cacheFile. " could not be opened");
                    }

                    $cachedContent = unserialize($cachedContent);
                    $lifetime = $cachedContent['lifetime'];
                }

                clearstatcache(true, $cacheFile);

                $modifiedTime = filemtime($cacheFile);

                if($modifiedTime + $lifetime > time()){
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Return a file-system safe identifier for a given key
     * @param string $key
     * @return string
     */
    public function getKey($key)
    {
        return md5($key);
    }

    /**
     * Immediately invalidates all existing items.
     * @return boolean
     */
    public function flush()
    {
        $cacheFiles = new DirectoryIterator($this->_options['cacheDir']);
        foreach ($cacheFiles as $item){
            if($item->isDir() === false){
                $key = $item->getFilename();
                $cacheFile = $item->getPathName();

                if(!$this->_prefix || strpos($key, $this->_prefix) === 0) {
                    if(!unlink($cacheFile)) {
						return false;
                    }
                }
            }
        }

        return true;
    }
}