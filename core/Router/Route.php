<?php
/**
 * Created by PhpStorm.
 * User: fesiong
 * Date: 2019-06-30
 * Time: 19:58
 */

namespace Alaric\Router;

use mysql_xdevapi\Exception;

class Route
{
    protected $_pattern;

    protected $_compiledPattern;

    protected $_paths;

    protected $_methods;

    protected $_id;

    protected $_name;

    protected $_match;

    protected $_converters;

    protected static $_uniqueId;

    public function __construct($pattern, $paths = null, $httpMethods = null)
    {
        $this->reConfigure($pattern, $paths);

        $this->_methods = $httpMethods;

        if(self::$_uniqueId === null){
            self::$_uniqueId = 0;
        }

        $this->_id = self::$_uniqueId;

        self::$_uniqueId = $this->_id + 1;
    }

    public function compilePattern($pattern)
    {
        if(strpos($pattern, ":") !== false){
            $idPattern = "/([\\w0-9\\_\\-]+)";

            if(strpos($pattern, "/:module") !== false){
                $pattern = str_replace("/:module", $idPattern, $pattern);
            }

            if(strpos($pattern, "/:controller") !== false){
                $pattern = str_replace("/:controller", $idPattern, $pattern);
            }

            if(strpos($pattern, "/:namespace") !== false){
                $pattern = str_replace("/:namespace", $idPattern, $pattern);
            }

            if(strpos($pattern, "/:action") !== false){
                $pattern = str_replace("/:action", $idPattern, $pattern);
            }

            if(strpos($pattern, "/:params") !== false){
                $pattern = str_replace("/:params", $idPattern, $pattern);
            }

            if(strpos($pattern, "/:int") !== false){
                $pattern = str_replace("/:int", $idPattern, $pattern);
            }
        }

        if(strpos($pattern, "(") !== false){
            return "#^" . $pattern . "$#u";
        }

        if(strpos($pattern, "[") !== false){
            return "#^" . $pattern . "$#u";
        }

        return $pattern;
    }

    public function via($httpMethods)
	{
		$this->_methods = $httpMethods;
		return $this;
	}

	public function extractNamedParams($pattern)
    {
        $prevCh = "";
        $marker = $bracketCount = $parenthesesCount = $foundPattern = $intermediate = $numberMatches = 0;
        $notValid = false;

        if(strlen($pattern) <= 0){
            return false;
        }

        $matches = [];
        $route = "";

        for ($cursor = 0; $cursor < strlen($pattern); $cursor++){
            $ch = $pattern[$cursor];
            if($parenthesesCount == 0){
                if($ch == "{"){
                    if($bracketCount == 0){
                        $marker = $cursor + 1;
                        $intermediate = 0;
                        $notValid = false;
                    }

                    $bracketCount++;
                }else{
                    if($ch == "}"){
                        $bracketCount--;
                        if($intermediate > 0){
                            if($bracketCount == 0){
                                $numberMatches++;
                                $variable = null;
                                $regexp = null;
                                $item = substr($pattern, $marker, $cursor - $marker);
                                for ($cursorVar = 0; $cursorVar < strlen($item);$cursorVar++) {
                                    $ch2 = $item[$cursorVar];
                                    if($cursorVar == 0 && !(($ch2 >= 'a' && $ch2 <= 'z') || ($ch2 >= 'A' && $ch2 <= 'Z'))) {
                                        $notValid = true;
                                        break;
                                    }

                                    if (($ch2 >= 'a' && $ch2 <= 'z') || ($ch2 >= 'A' && $ch2 <= 'Z') || ($ch2 >= '0' && $ch2 <='9') || $ch2 == '-' || $ch2 == '_' || $ch2 ==  ':') {
                                        if($ch2 == ':') {
                                            $variable = (string) substr($item, 0, $cursorVar);
                                            $regexp = (string) substr($item, $cursorVar + 1);
											break;
										}
                                    } else {
                                        $notValid = true;
										break;
									}

								}

                                if(!$notValid){
                                    $tmp = $numberMatches;

                                    if($variable && $regexp){
                                        $foundPattern = 0;
                                        for($i = 0; $i <strlen($regexp); $i++){
                                            $ch2 = $regexp[$i];
                                            if(!$foundPattern){
                                                if($ch2 == '('){
                                                    $foundPattern = 1;
												}
                                            } else {
                                                if($ch2 == ')'){
                                                    $foundPattern = 2;
													break;
												}
                                            }
                                        }
                                        if($foundPattern != 2){
                                            $route .= "(" . $regexp . ")";
										} else {
                                            $route .= $regexp;
										}
                                        $matches[$variable] = $tmp;
                                    } else {
                                        $route .= "([^/]*)";
                                        $matches[$item] = $tmp;
									}
                                } else {
                                    $route .= "{" . $item . "}";
								}

                                continue;
                            }
                        }
                    }
                }
            }
            if($bracketCount == 0) {
                if($ch == '(') {
                    $parenthesesCount++;
				} else {
                    if($ch == ')') {
                        $parenthesesCount--;
						if($parenthesesCount == 0) {
                            $numberMatches++;
						}
					}
                }
            }

            if($bracketCount > 0) {
                $intermediate++;
			} else {
                if($parenthesesCount == 0 && $prevCh != "\\") {
                    if($ch == '.' || $ch == '+' || $ch == '|' || $ch == '#') {
                        $route .= "\\";
					}
                }
                $route .= $ch;
				$prevCh = $ch;
			}
        }

        return [$route, $matches];
    }

    public function reConfigure($pattern, $paths = null)
    {
        $routePaths = self::getRoutePaths($paths);
        if(strpos($pattern, "#") !== 0) {
			if(strpos($pattern, "{") !== false) {
			    $extracted = $this->extractNamedParams($pattern);
			    $pcrePattern = $extracted[0];
			    $routePaths = array_merge($routePaths, $extracted[1]);
			} else {
                $pcrePattern = $pattern;
			}

			$compiledPattern = $this->compilePattern($pcrePattern);
		} else {
            $compiledPattern = $pattern;
		}

        $this->_pattern = $pattern;
        $this->_compiledPattern = $compiledPattern;
        $this->_paths = $routePaths;
    }

    public static function getRoutePaths($paths = null){
        if($paths !== null){
            if(is_string($paths)){
                $moduleName = $controllerName = $actionName = null;

                $parts = explode("::", $paths);

                switch (count($parts)){
                    case 3:
                        $moduleName = $parts[0];
                        $controllerName = $parts[1];
                        $actionName = $parts[2];
                        break;
                    case 2:
                        $controllerName = $parts[0];
						$actionName = $parts[1];
                        break;
                    case 1:
                        $controllerName = $parts[0];
                        break;
                }

                $routePaths = [];

                if($moduleName !== null) {
                    $routePaths["module"] = $moduleName;
				}

                if($controllerName !== null){
                    if(strpos($controllerName, "\\") !== false){
                        $controllerArray = explode("\\", $controllerName);
                        $realClassName = array_pop($controllerArray);

                        $namespaceName = implode("\\", $controllerArray);
                    }else{
                        $realClassName = $controllerName;
                    }

                    $routePaths["controller"] = $realClassName;
                }

                if($actionName !== null) {
                    $routePaths["action"] = $actionName;
				}
            } else {
                $routePaths = $paths;
			}
        } else {
            $routePaths = [];
		}

        if(!is_array($routePaths)) {
            throw new Exception("The route contains invalid paths");
        }

		return $routePaths;
    }

    public function getName()
    {
        return $this->_name;
    }

    public function setName($name)
	{
		$this->_name = $name;
		return $this;
	}

    public function match($callback)
	{
		$this->_match = $callback;
		return $this;
	}

    public function getMatch()
	{
		return $this->_match;
	}

    public function getPattern()
	{
		return $this->_pattern;
	}

    public function getCompiledPattern()
	{
		return $this->_compiledPattern;
	}

    public function getPaths()
	{
		return $this->_paths;
	}

    public function getReversedPaths()
	{
		$reversed = [];
		foreach($this->_paths as $path => $position) {
			$reversed[$position] = $path;
		}
        return $reversed;
    }

    public function setHttpMethods($httpMethods)
	{
		$this->_methods = $httpMethods;
		return $this;
	}

    public function getHttpMethods()
	{
		return $this->_methods;
	}

    public function convert($name, $converter)
	{
		$this->_converters[$name] = $converter;
		return $this;
	}

    public function getConverters()
	{
		return $this->_converters;
	}

    public static function reset()
	{
		self::$_uniqueId = null;
	}
}