<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-30
 * Time: 15:57
 */

namespace Alaric;

class Loader
{
    protected $_classes = [];

    protected $_namespaces = [];

    protected $_files = [];

    protected $_directories = [];

    protected $_registered = false;

    public function registerNamespaces($namespaces, $merge = false)
    {
        $preparedNamespaces = $this->prepareNamespace($namespaces);

        if($merge){
            foreach ($preparedNamespaces as $name => $paths){
                if(!isset($this->_namespaces[$name])){
                    $this->_namespaces[$name] = [];
                }

                $this->_namespaces[$name] = array_merge($this->_namespaces[$name], $paths);
            }
        }else{
            $this->_namespaces = $preparedNamespaces;
        }
    }

    protected function prepareNamespace($namespace){
        $prepared = [];

        foreach ($namespace as $name => $paths){
            if(!is_array($paths)){
                $localPaths = [$paths];
            }else{
                $localPaths = $paths;
            }

            $prepared[$name] = $localPaths;
        }

        return $prepared;
    }

    public function getNamespaces(){
        return $this->_namespaces;
    }

    public function registerDirs($directories, $merge){
        if($merge){
            $this->_directories = array_merge($this->_directories, $directories);
        }else{
            $this->_directories = $directories;
        }

        return $this;
    }

    public function getDirs(){
        return $this->_directories;
    }

    public function registerFiles($files, $merge){
        if($merge){
            $this->_files = array_merge($this->_files, $files);
        }else{
            $this->_files = $files;
        }
    }

    public function getFiles(){
        return $this->_files;
    }

    public function registerClasses($classes, $merge){
        if($merge){
            $this->_classes = array_merge($this->_classes, $classes);
        }else{
            $this->_classes = $classes;
        }
    }

    public function getClasses()
    {
        return $this->_classes;
    }

    public function register($prepend = false)
    {
        if($this->_registered === false){

            $this->loadFiles();

            spl_autoload_register([$this, "autoLoad"], true, $prepend);

            $this->_registered = true;
        }

        return $this;
    }

    public function unregister()
    {
        if($this->_registered === true){
            spl_autoload_unregister([$this, "autoLoad"]);
            $this->_registered = false;
        }

        return $this;
    }

    public function loadFiles()
    {
        foreach ($this->_files as $filePath){
            if(is_file($filePath)){

                require $filePath;
            }
        }
    }

    public function autoLoad($className)
    {
        $classes = $this->_classes;

        if(!empty($classes[$className])){
            require $classes[$className];

            return true;
        }

        $ds = DIRECTORY_SEPARATOR;
        $ns = "\\";

        foreach ($this->_namespaces as $nsPrefix => $directories){
            if(!strpos($className, $nsPrefix) === 0){
                continue;
            }

            $fileName = substr($className, strlen($nsPrefix . $ns));

            if(!$fileName){
                continue;
            }

            $fileName = str_replace($ns, $ds, $fileName);

            foreach ($directories as $directory){
                $fixedDirectory = rtrim($directory, $ds) . $ds;

                $filePath = $fixedDirectory . $fileName . ".php";
                if(is_file($filePath)){
                    require $filePath;

                    return true;
                }
            }
        }

        $nsClassName = str_replace($ns, $ds, $className);

        foreach ($this->_directories as $directory){
            $fixedDirectory = rtrim($directory, $ds) . $ds;

            $filePath = $fixedDirectory . $nsClassName . ".php";

            if(is_file($filePath)){

                require $filePath;

                return true;
            }
        }

        return false;
    }
}