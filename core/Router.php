<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-30
 * Time: 19:54
 */

namespace Alaric;

use Alaric\Router\Route;
use Exception;

class Router
{
    protected $_uriSource;

    protected $_namespace = null;

    protected $_module = null;

    protected $_controller = null;

    protected $_action = null;

    protected $_params = [];

    protected $_routes;

    protected $_matchedRoute;

    protected $_matches;

    protected $_wasMatched = false;

    protected $_defaultNamespace;

    protected $_defaultModule;

    protected $_defaultController;

    protected $_defaultAction;

    protected $_defaultParams = [];

    protected $_removeExtraSlashes;

    protected $_notFoundPaths;

    const POSITION_FIRST = 0;

    const POSITION_LAST = 1;

    public function __construct($defaultRoutes = true) {
        $routes = [];

        if($defaultRoutes){
            $routes[] = new Route("#^/([\\w0-9\\_\\-]+)[/]{0,1}$#u", [
                "controller" => 1
			]);

			$routes[] = new Route("#^/([\\w0-9\\_\\-]+)/([\\w0-9\\.\\_]+)(/.*)*$#u", [
                "controller" => 1,
				"action" => 2,
				"params" => 3
			]);
        }

        $this->_routes = $routes;
    }

    public function setUriSource($uri){
        $this->_uriSource = $uri;

        return $this;
    }

    public function getRewriteUri()
    {
        if ($this->_uriSource) {
            return $this->_uriSource;
        } else {
            if ($_SERVER["REQUEST_URI"]) {
                $urlParts = explode("?", $_SERVER["REQUEST_URI"]);
                $realUri = $urlParts[0];
                if (!empty($realUri)) {
                    return $realUri;
                }
            }

            return "/";
        }
    }

    public function removeExtraSlashes($remove)
	{
		$this->_removeExtraSlashes = $remove;
		return $this;
	}

    public function setDefaultNamespace($namespaceName)
	{
		$this->_defaultNamespace = $namespaceName;
		return $this;
	}

    public function setDefaultModule($moduleName)
	{
		$this->_defaultModule = $moduleName;
		return $this;
	}

    public function setDefaultController($controllerName)
	{
		$this->_defaultController = $controllerName;
		return $this;
	}

    public function setDefaultAction($actionName)
	{
		$this->_defaultAction = $actionName;
		return $this;
	}

    public function setDefaults($defaults)
	{
		if($defaults["namespace"]) {
			$this->_defaultNamespace = $defaults["namespace"];
		}

		if($defaults["module"]){
		    $this->_defaultModule = $defaults["module"];
        }

		if($defaults["controller"]){
		    $this->_defaultController = $defaults["controller"];
        }

		if($defaults["action"]){
		    $this->_defaultAction = $defaults["action"];
        }

		if($defaults["params"]){
		    $this->_defaultParams = $defaults["params"];
        }

		return $this;
	}

    public function getDefaults()
	{
		return [
			"namespace" =>  $this->_defaultNamespace,
			"module" =>     $this->_defaultModule,
			"controller" => $this->_defaultController,
			"action" =>     $this->_defaultAction,
			"params" =>     $this->_defaultParams
		];
	}

    public function handle($uri = null){
        if(!$uri) {
            $realUri = $this->getRewriteUri();
		} else {
            $realUri = $uri;
		}

        if($this->_removeExtraSlashes && $realUri != "/") {
            $handledUri = rtrim($realUri, "/");
		} else {
            $handledUri = $realUri;
		}

        $currentHostName = null;
        $routeFound = false;
        $parts = [];
        $params = [];
        $matches = null;
        $this->_wasMatched = false;
        $this->_matchedRoute = null;

        /** @var Route $route */
        foreach (array_reverse($this->_routes) as $route) {
            $params = [];
			$matches = null;

			$methods = $route->getHttpMethods();
            $pattern = $route->getCompiledPattern();
            if(strpos($pattern, "^") !== false) {
                $routeFound = preg_match($pattern, $handledUri, $matches);
            } else {
                $routeFound = $pattern == $handledUri;
            }

            if($routeFound !== false) {
                $paths = $route->getPaths();
                $parts = $paths;

                if(is_array($matches)){
                    foreach ($parts as $part => $position){
                        if(!is_string($position) && !is_integer($position)){
                            continue;
                        }

                        if(!empty($matches[$position])){
                            $parts[$part] = $matches[$position];
                        }else{
                            if(is_integer($position)){
                                unset($parts[$part]);
                            }
                        }
                    }

                    $this->_matches = $matches;
                }
                $this->_matchedRoute = $route;

                break;
			}
        }

        if($routeFound !== false) {
            $this->_wasMatched = true;
		} else {
            $this->_wasMatched = false;
		}

        if($routeFound === false) {
            $notFoundPaths = $this->_notFoundPaths;
			if($notFoundPaths !== null) {
                $parts = Route::getRoutePaths($notFoundPaths);
                $routeFound = true;
			}
		}

        $this->_namespace  = $this->_defaultNamespace;
        $this->_module     = $this->_defaultModule;
        $this->_controller = $this->_defaultController;
        $this->_action     = $this->_defaultAction;
        $this->_params     = $this->_defaultParams;

        if($routeFound !== false){
            if(!empty($parts["namespace"])){
                if(!is_numeric($parts["namespace"])){
                    $this->_namespace = $parts["namespace"];
                }

                unset($parts["namespace"]);
            }

            if(!empty($parts["controller"])) {
                if(!is_numeric($parts["controller"])) {
                    $this->_controller = $parts["controller"];
				}
				unset($parts["controller"]);
			}

            if(!empty($parts["action"])) {
                if(!is_numeric($parts["action"])) {
                    $this->_action = $parts["action"];
                }
                unset($parts["action"]);
            }

            if(!empty($parts["params"])) {
                if(is_string($parts["params"])) {
                    $parts["params"] = trim($parts["params"], "/");
					if($parts["params"] !== "") {
                        $params = explode("/", $parts["params"]);
					}
				}

				unset($parts["params"]);
			}

            if(count($params)) {
                $this->_params = array_merge($params, $parts);
			} else {
                $this->_params = $parts;
			}
        }
    }

    /**
     * @param     $route
     * @param int $position
     * @return $this
     * @throws Exception
     */
    public function attach($route, $position = Router::POSITION_LAST)
	{
		switch($position) {
			case self::POSITION_LAST:
				$this->_routes[] = $route;
				break;
			case self::POSITION_FIRST:
				$this->_routes = array_merge([$route], $this->_routes);
				break;
			default:
				throw new Exception("Invalid route position");
		}

        return $this;
    }

    public function add($pattern, $paths = null, $httpMethods = null, $position = Router::POSITION_LAST){
        $route = new Route($pattern, $paths, $httpMethods);

        $this->attach($route, $position);

		return $route;
    }

    public function addGroup($namespace, $members)
    {
        foreach($members as $item)
        {
            $pattern = $item['pattern'];
            $httpMethods = isset($item['method']) ? $item['method'] : null;
            $position = isset($item['position']) ? $item['position'] : Router::POSITION_LAST;
            $paths = $item['path'];
            $paths['namespace'] = $namespace;
            $this->add($pattern, $paths, $httpMethods, $position);
        }
    }

    public function addGet($pattern, $paths = null, $position = Router::POSITION_LAST)
	{
		return $this->add($pattern, $paths, "GET", $position);
	}

    public function addPost($pattern, $paths = null, $position = Router::POSITION_LAST)
    {
        return $this->add($pattern, $paths, "POST", $position);
    }

    public function addPut($pattern, $paths = null, $position = Router::POSITION_LAST)
    {
        return $this->add($pattern, $paths, "PUT", $position);
    }

    public function addPatch($pattern, $paths = null, $position = Router::POSITION_LAST)
    {
        return $this->add($pattern, $paths, "PATCH", $position);
    }

    public function addDelete($pattern, $paths = null, $position = Router::POSITION_LAST)
    {
        return $this->add($pattern, $paths, "DELETE", $position);
    }

    public function addOptions($pattern, $paths = null, $position = Router::POSITION_LAST)
    {
        return $this->add($pattern, $paths, "OPTIONS", $position);
    }

    public function addHead($pattern, $paths = null, $position = Router::POSITION_LAST)
    {
        return $this->add($pattern, $paths, "HEAD", $position);
    }

    /**
     * @param array|string $paths
     * @return Router $this
     * @throws Exception
     */
    public function notFound($paths)
	{
		if(!is_array($paths) && !is_string($paths)){
			throw new Exception("The not-found paths must be an array or string");
		}
        $this->_notFoundPaths = $paths;
		return $this;
	}

    public function clear()
	{
		$this->_routes = [];
	}

    public function getNamespaceName()
	{
		return $this->_namespace;
	}

    public function getModuleName()
	{
		return $this->_module;
	}

    public function getControllerName()
	{
		return $this->_controller;
	}

    public function getActionName()
	{
		return $this->_action;
	}

    public function getParams()
	{
		return $this->_params;
	}

    public function getMatchedRoute()
	{
		return $this->_matchedRoute;
	}

    public function getMatches()
	{
		return $this->_matches;
	}

    public function wasMatched()
	{
		return $this->_wasMatched;
	}

    public function getRoutes()
	{
		return $this->_routes;
	}
}