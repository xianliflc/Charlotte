<?php

namespace Charlotte\CDoc;

use Charlotte\Core\Config;

abstract class Doc {

    protected $config;

    protected $template_path;

    protected $directories;

    protected $allowedFileExtensions;

    protected $files;

    protected $annotations;

	protected $data;
	
	protected const MAPPINGS = [
			"description" => "Description",
			"siteurl" => "RequestUrl",
			"parameter" => "RequestParameter",
			"response_detail" => "ResponseProperty",
			"response" => "ResponseExample",
			"format" => "RequestFormat",
			"method" => "RequestMethod",
			"notice" => "RequestNotice",
			"request" => "RequestExample",
			"success" => "ResponseErrorExample",
            "exception" => "ResponseException",
            "header" => "RequestHeader"
	];

    public function __construct(Config $config = null)
    {
        $this->setConfig($config);
        $this->files = array();
        $this->directories = array();
        $this->allowedFileExtensions = array();
        $this->annotations = array();
        $this->data = array();

    }

    /**
     * @param Config|null $config
     * @throws \Exception
     */
    protected function setConfig(Config $config = null) {
        if ($config !== null) {
            if (!$config->has('doc->settings')) {
                throw new \Exception('missing config for doc', 500);
			}
			$this->config = $config->get('doc');
			if ($config->has('doc->mappings') ) {
				$this->config['mappings'] = $this->overWriteConfig(self::MAPPINGS, $config->get('doc->mappings'));
			} else {
				$this->config['mappings'] = self::MAPPINGS;
			}
    
            
        }
	}
	

	private function overWriteConfig($target, $overwrite) {
        $result = $target;
        if (gettype($target) !== 'array') {
            return $overwrite;
        }

        foreach ($overwrite as $key => $value) {
            if (!array_key_exists($key, $result)) {
                $result[$key] = $value;
            } elseif (array_key_exists($key, $result) && $result[$key] !== $value) {
                $result[$key] = $this->overWriteConfig($result[$key], $value);
            } else {
                continue;
            }
        }
        return $result;
    }

    /**
     * @param null $config
     * @return $this
     * @throws \Exception
     */
    public function useConfig($config = null) {
        if ($config instanceof Config) {
            $this->setConfig($config);
        } elseif (is_array($config) && !empty($config)) {
            if (array_key_exists('settings', $config)) {
                foreach ($config['settings'] as $key => $value){
                    if(isset($this->config['settings'][$key])){
                        $this->config['settings'][$key] = $value;
                    }
                }
            }
            if (array_key_exists('mappings', $config)) {
                foreach ($config['mappings'] as $key => $value){
                    if(isset($this->config['mappings'][$key])){
                        $this->config['mappings'][$key] = $value;
                    }
                }
            }
        }
        
        return $this;
    }

    /**
     * @param array $settings
     * @return Doc
     * @throws \Exception
     */
    public function useSettings(array $settings = array()) {
        return $this->useConfig(array('settings' => $settings));
    }

    /**
     * @param array $rules
     * @return Doc
     * @throws \Exception
     */
    public function useRules(array $rules = array()) {
        return $this->useConfig(array('mappings' => $settings));
    }

    /**
     * @return mixed
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function build() {
        $this->getAllowedFileExtensions();
        $this->getResources($this->config['settings']['resource_path']);
        $this->getAnnotations();
        $this->refine();

        return $this;
    }

    /**
     *
     */
    protected function getAllowedFileExtensions() {
        $this->allowedFileExtensions = explode('|', $this->config['settings']['allowed_files']);
    }

    /**
     * @param $path
     */
    protected function getResources($path) {
        $dirs = glob($path .'/*');
        foreach ($dirs as $dir) {
            if (is_dir($dir)) {
                $this->directories[] = $dir;
                $this->getResources($dir);
            } else {
                $file_info = pathinfo($dir);
                if (array_key_exists('extension', $file_info) && in_array($file_info['extension'], $this->allowedFileExtensions)) {
                    $this->files[] = $dir;
                }
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function getAnnotations() {
        $outside_resource = false;
        if (isset($this->config['settings']['outside']) && $this->config['settings']['outside'] === true) {
            $outside_resource = true;
        }
        
        try{
            foreach($this->files as $file) {
                if (!$outside_resource) {
                    $info = pathinfo($file);
                    $dir = str_replace($this->config['settings']['resource_path'], 
                                        $this->config['settings']['package_prefix'], 
                                        $info['dirname'] 
                    );
                    $classpath = str_replace('/', '\\', $dir . '/'. $info['filename']);
                    $this->getFileAnnotation($classpath);
                } else {

                    $this->getExternalFileAnnotation($file);
                }

            }
        } catch(\Exception $e) {
            throw new \Exception('', 502);
        }
            

        
    }

    /**
     */
    protected function getExternalFileAnnotation($file) {
        $info = pathinfo($file);
		if (!isset($this->annotations[$file])) {
            $file_content = file_get_contents($file);
            preg_match_all('/\/\*\*([\s\S]*?)\*\/[\s\S]*class[\s\S]*\{/', $file_content, $matches);
            preg_match_all('/\/\*\*([\s\S]*?)\*\//', $file_content, $methods_matches);
            $blocks = array();

            if (isset($matches[1])) {
                $class_annotation = implode("\n", $matches[1]);
                $class_annotation = str_replace('* ', '', $class_annotation);
                $this->annotations[$file]['class'] = 
                array (
                    'comment' => self::parseAnnotations($class_annotation)
                 );;
            }
            
            if (isset($methods_matches[1]) && count($methods_matches[1]) > 1) {
                for($index = 1; $index < count($methods_matches[1]); $index++) {
                    $this->annotations[$file]['methods'][] = [
                        'comment' => self::parseAnnotations($methods_matches[1][$index]),
                        'fileName'	=> $info['filename']
                    ];
                }

            }
		}
    }

    /**
     * @param $filename
     * @throws \ReflectionException
     */
    protected function getFileAnnotation($filename) {
		if (!isset($this->annotations[$filename])) {
			$class = new \ReflectionClass($filename);
			$this->annotations[$filename]['class'] = $this->getClassAnnotation($class);
			$this->annotations[$filename]['methods'] = $this->getMethodAnnotations($class);
		}
    }

    /**
     * @param $class
     * @return array
     */
    protected function getClassAnnotation($class)
	{
		return array (
			         'comment' => self::parseAnnotations($class->getDocComment()),
			         'parentClass' => $class->getParentClass()->name,
			         'fileName'	=> $class->getFileName(),
                  );
    }

    /**
     * @param $docblock
     * @return array
     * @throws \Exception
     */
    protected static function parseAnnotations($docblock)
	{
		$annotations = [];
		$docblock = substr($docblock, 3, -2);
		if (preg_match_all('/@(?<name>[0-9A-Za-z_-]+)[\s\t]*\((?<args>.*)\)[\s\t]*\r?$/im', $docblock, $matches)) {
			$numMatches = count($matches[0]);
			for ($i = 0; $i < $numMatches; ++$i) {
				if (isset($matches['args'][$i])) {
					$argsParts = trim($matches['args'][$i]);
					$name      = $matches['name'][$i];
					$value     = self::parseArgs($argsParts);
				} else {
					$value = [];
				}
				$annotations[$name][] = $value;
			}
		}
		return $annotations;
    }

    /**
     * @param $class
     * @return array
     * @throws \ReflectionException
     */
    protected function getMethodAnnotations($class)
	{
        $result = array();
		foreach ($class->getMethods() as $object) {
            if(in_array(strtolower($object->name), ['get_instance', 'getinstance', 'create_instance', 'createinstance']) || 
                $object->name === $class->getConstructor()->name) continue;
			$method = new \ReflectionMethod($object->class, $object->name);
            $result[$object->name] = $this->getMethodAnnotation($method);
		}
        return $result;
	}

    /**
     * @param $method
     * @return array
     * @throws \Exception
     */
	protected function getMethodAnnotation($method)
	{
	    return [
	               'comment' => self::parseAnnotations($method->getDocComment()),
	               'fileName'	=> $method->getFileName(),
	               'method_attribute' => \Reflection::getModifierNames($method->getModifiers()),
	           ];
    }

    /**
     *
     */
    protected function refine()
	{
		foreach($this->annotations as $class => $annotation){
			if(isset($annotation['class']['comment']['group'])){
				$this->data[$annotation['class']['comment']['group'][0]['name']][$class] = array(
					'class' => $annotation['class'],
					'methods' => $annotation['methods'],
				);
			}
		}
	}

    /**
     * @param $content
     * @return array|bool|int|string
     * @throws \Exception
     */
    protected static function parseArgs($content)
	{
		$data  = array();
		$len   = strlen($content);
		$i     = 0;
		$var   = '';
		$val   = '';
		$level = 1;
		$prevDelimiter = '';
		$nextDelimiter = '';
		$nextToken     = '';
		$composing     = false;
		$isPlain       = true;
		$delimiter     = null;
		$quoted        = false;
		$tokens        = array('"', '"', '{', '}', ',', '=');
		while ($i <= $len) {
			$c = substr($content, $i++, 1);
            $bracketType = 0;
		    if ($c === '"') {
				$delimiter = $c;
				//opening delimiter
				if (!$composing && empty($prevDelimiter) && empty($nextDelimiter)) {
					$prevDelimiter = $nextDelimiter = $delimiter;
					$val           = '';
					$composing     = true;
					$quoted        = true;
				} else {
					// closing delimiter
					if ($c !== $nextDelimiter) {
						throw new \Exception(sprintf(
							"Parse Error: enclosing error -> expected: [%s], given: [%s]",
							$nextDelimiter, $c
						), 502);
					}
					// validating syntax
					if ($i < $len) {
						if (',' !== substr($content, $i, 1)) {
							throw new \Exception(sprintf(
								"Parse Error: missing comma separator near: ...%s<--",
								substr($content, ($i-10), $i)
							), 502);
						}
					}
					$prevDelimiter = $nextDelimiter = '';
					$composing     = false;
					$delimiter     = null;
				}
			} elseif (!$composing && in_array($c, $tokens)) {
				switch ($c) {
				    case '=':
						$prevDelimiter = $nextDelimiter = '';
						$level     = 2;
						$composing = false;
						$isPlain   = false;
						$quoted     = false;
						break;
					case ',':
						$level = 3;
						// the string is not enclosed
						if ($composing === true && !empty($prevDelimiter) && !empty($nextDelimiter)) {
							throw new \Exception(sprintf(
								"Parse Error: enclosing error -> expected: [%s], given: [%s]",
								$nextDelimiter, $c
							), 502);
						}
						$prevDelimiter = $nextDelimiter = '';
                        break;
                    case '[':
                        $bracketType = 1;
                    case '(':
                        $bracketType = 2;
                    case '{':
                        $bracketType = 3;
						$subc = '';
						$subComposing = true;
						while ($i <= $len) {
							$c = substr($content, $i++, 1);
							if (isset($delimiter) && $c === $delimiter) {
								throw new \Exception(sprintf(
									"Parse Error: Composite variable is not enclosed correctly."
								), 502);
							}
                            if (($bracketType === 3 &&$c === '}') || 
                            ($bracketType === 1 &&$c === ']') ||
                            ($bracketType === 2 &&$c === ')')) {
								$subComposing = false;
								break;
							}
							$subc .= $c;
						}
						// the variable is not enclosed by '}'
						if ($subComposing) {
						    throw new \Exception(sprintf(
						        "Parse Error: Composite variable is not enclosed correctly. near: ...%s'",
						        $subc
						    ), 502);
						}
                        $val = self::parseArgs($subc);
                        $bracketType = 0;
						break;
				}
			} else {
				if ($level == 1) {
					$var .= $c;
				} elseif ($level == 2) {
					$val .= $c;
				}
			}
		    if ($level === 3 || $i === $len) {
				if ($isPlain === true && $i === $len) {
					$data = self::castValue($var);
				} else {
					$data[trim($var)] = self::castValue($val, !$quoted);
				}
				$level = 1;
				$var   = $val = '';
				$composing = false;
				$quoted = false;
			}
		}
		return $data;
    }

    /**
     * @param $val
     * @param bool $trim
     * @return array|bool|int|string
     */
    protected static function castValue($val, $trim = false)
	{
		if (is_array($val)) {
			foreach ($val as $key => $value) {
				$val[$key] = self::castValue($value, $trim);
			}
		} elseif (is_string($val)) {
			if ($trim) {
				$val = trim($val);
			}
			$tmp = strtolower($val);
			if ($tmp === 'false' || $tmp === 'true') {
				$val = $tmp === 'true';
			} elseif (is_numeric($val)) {
				return $val + 0;
			}
			unset($tmp);
		}
		return $val;
    }

    /**
     * @return array
     */
    public function getData() {
        return $this->data;
	}
	
	public abstract function render($dependencies, $template);

	public abstract function exportTo($format, $options);
	
}