<?php
class Page {
	public $document;
	protected $style;
	protected $moduleAppList = array();
	protected $classList = array();
	protected $dir;
	protected $properties = array();
	protected $actionlist = array();
	protected $exeptions = array();
	protected $extensions = array();
	protected $doc;
	protected $config;
	protected $user;
	protected $data = array();

	/**
	 *
	 * Constructs the Page object either for declaring the
	 * @param boolean $edit
	 * @param string $dir
	 */
	public function __construct($edit = false, $dir = "") {
		$this->dir = $dir ? $dir.'/' : "";
		if ($edit) {
			$this->config = new DOMDocument();
			$this->config->load("config.xml");
			$this->loadExtensions();
			$this->loadProperties($this->config);
			$this->doc = new DOMDocument();
			$site = $this->config->getElementsByTagName("site")->item(0);
			$desc = $site->getElementsByTagName("desc")->item(0)->getAttribute("src");
			$this->properties['desc'] = $desc;
			$this->doc->load($desc);
		}
	}

	public function registerModuleApp($name, $class, $category = "") {
		if (class_exists($class)) {
			$this->classList[$name]['class'] = $class;
			$this->classList[$name]['category'] = $category;
			return true;
		} else {
			return false;
		}
	}

	function loadActions(DOMDocument $config = null) {
		if (!isset($config))
			$config = $this->config;
		$actions = $config->getElementsByTagName("actions")->item(0);
		if ($actions) {
			$on = $actions->getAttribute("on");
			foreach ($actions->getElementsByTagName("action") as $action) {
				$name = $action->getAttribute("name");
				$class = $action->getAttribute("class");
				$access = $action->getAttribute("access");
				$category = $action->getAttribute("category");
				$this->actionlist[$name]['class'] = $class;
				$this->actionlist[$name]['access'] = $access;
				$this->actionlist[$name]['category'] = $category;
			}
			if (!is_array($this->properties['actions'])) {
				$this->properties['actions'] = array();
			}
			$this->properties['actions'] = array_merge($this->properties['actions'], $this->actionlist);
		}
	}


	function runActions($on = "action") {
		$key = $_GET[$on];
		if (is_string($key)&&class_exists($class = $this->actionlist[$key]['class'])) {
			$res = $this->executeAction($key, $command, array_merge($_GET, $_POST));
			if ($res instanceof JSON && !$_GET['site']) {
				echo $res;
				exit();
			}
		}
		if (is_array($acts = $_POST[$on][0])) {
			$suc = true;
			$json = new JSON();
			foreach ($acts as $k => $act) {
				$key = $act["_name"];
				$command = $act["_command"];
				$suc = $suc && $res = $this->executeAction($key, $command, $act);
				if ($res instanceof JSON) {
					$json->add($k, $res);
				}
			}
			if ($suc) {
				header("content-type: text/json;");
				echo $json;
				exit();
			}
		} elseif ($act = $_POST[$on]) {
			$key = $act["_name"];
			$command = $act["_command"];
			$suc = $this->executeAction($key, $command?$command:Action::perform, $act);
			if ($suc instanceof JSON) {
				header("content-type: text/json;");
				echo $suc;
				exit();
			}
		}

	}

	protected function executeAction($key, $command, $args) {
		if (class_exists($class = $this->actionlist[$key]['class'])) {
			$handler = new $class($key);
			if ($handler instanceof Action) {
				$allowed = $this->actionlist[$key]['access'];
				$category = $this->actionlist[$key]['category'];
				if ((!($allowed==="restricted")||$this->user->isLoggedIn())&&
				(!$category||$this->user->isPermitted($category))) {
					$result = $handler->performAction($command?$command:Action::perform, $args);
					$this->user->logAction($key, $command, $args);
					if (is_array($result)) {
						$json = new JSON();
						foreach ($result as $key => $re) {
							if ($key==="_data"&& is_array($re)) {
								$this->data = array_merge($this->data, $re);
							} else {
								$json->add($key, $re);
							}
						}
						if (!$json->isEmpty()) {
							return $json;
						}
					}
				}
			}
		}
		return false;
	}

	/**
	 *
	 * Loads the settings defined in the config.xml
	 */
	function loadConfig($config = "config.xml") {
		$this->config = new DOMDocument();
		$this->config->load($config);

		/*$mysql = $this->config->getElementsByTagName("mysql")->item(0);
		$connect = $mysql->getElementsByTagName("connect")->item(0);
		$connection = new Connection($connect->getAttribute("host"), $connect->getAttribute("username"),
		$connect->getAttribute("password"), $connect->getAttribute("database"));*/

		$this->user = new User($_SESSION['user'], $_SESSION['password']);

		$this->loadExtensions();

		$site = $this->config->getElementsByTagName("site")->item(0);
		$cases = $site->getElementsByTagName("case");
		if ($cases) {
			foreach ($cases as $case) {
				$on = $case->getAttribute("on");
				$value = $case->getAttribute("value");
				$class = $case->getAttribute("class");
				if ($value&&$_GET[$on]==$value&&class_exists($class)) {
					$handler = new $class($value);
					if ($handler instanceof CaseHandler) {
						$result = $handler->actOnCase($_GET);
						echo $result;
						exit();
					}
				}
			}
		}
		$this->loadProperties($this->config);
		$desc = $site->getElementsByTagName("desc")->item(0)->getAttribute("src");
		$this->properties['desc'] = $desc;

		$this->doc = new DOMDocument();
		$this->doc->load($desc);
	}

	/**
	 *
	 * Loads extensions as described in the config.xml
	 */
	protected function loadExtensions() {
		$import = $this->config->getElementsByTagName("import")->item(0);
		if ($import) {
			$extensions = $import->getElementsByTagName("extension");
			if ($extensions) {
				spl_autoload_register(array($this, "autoLoadLibrary"));
				foreach ($extensions as $lib) {
					$name = $lib->getAttribute("name");
					$config = $lib->getAttribute("config");
					$path = 'extensions/'.$name;
					if (is_dir($path)&&is_file($path.'/'.$config)) {
						$this->extensions[$name] = $path;
						$doc = new DOMDocument();
						$doc->load($path.'/'.$config);
						$this->loadActions($doc);
						$this->loadProperties($doc);
						$this->properties['extensions'][$name]['name'] = $name;
						$this->properties['extensions'][$name]['config'] = $config;
					}
				}
			}
		}
	}

	/**
	 *
	 * Loads moduleapps and categories into the class list and the properties array.
	 * 
	 */
	protected function loadProperties(DOMDocument $config) {
		$moduleapps = $config->getElementsByTagName("moduleapps")->item(0);
		$categories = array();
		$mapps = $moduleapps->getElementsByTagName("object");
		if ($mapps) {
			foreach ($mapps as $obj) {
				$class = $obj->getAttribute("class");
				$app = $obj->getAttribute("app");
				$category = $obj->getAttribute("category");
				$edit = $obj->getAttribute("edit");
				$i = count($this->properties['apps']);
				$this->properties['apps'][$app]['name'] = $app;
				$this->properties['apps'][$app]['class'] = $class;
				$this->properties['apps'][$app]['category'] = $category;
				$this->properties['apps'][$app]['edit'] = $edit;
				if ($category) {
					$categories[$category] = $category;
				}
				$props = $obj->childNodes;
				if ($props) {
					foreach ($props as $prop) {
						if ($prop->nodeName === "prop") {
							$c = count($this->properties['apps'][$app]['props']);
							$this->properties['apps'][$app]['props'][$c]['name'] = $prop->getAttribute("name");
							$this->properties['apps'][$app]['props'][$c]['type'] = $prop->getAttribute("type");
						} elseif ($prop->nodeName === "child") {
							$child = $prop;
							$c = count($this->properties['apps'][$app]['childs']);
							$ch = $this->properties['apps'][$app]['childs'][$c];
							$ch['name'] = $child->getAttribute("name");
							foreach ($child->getElementsByTagName("prop") as $prop) {
								$l = count($ch['props']);
								$ch['props'][$l]['name'] = $prop->getAttribute("name");
								$ch['props'][$l]['type'] = $prop->getAttribute("type");
							}
							$this->properties['apps'][$app]['childs'][$c] = $ch;
						}
					}
				}
				$this->registerModuleApp($app, $class, $category);
			}
		}
		if ($this->user) {
			$this->user->setCategories($categories);
		}
	}

	public function convertToArray(DOMElement $node) {
		$array = array();
		$array['name'] = $node->tagName;
		if ($node->hasAttributes()) {
			foreach ($node->attributes as $key => $att) {
				$array['attr'][$key] = utf8_decode($att->value);
			}
		}
		if ($node->hasChildNodes()) {
			foreach ($node->childNodes as $child) {
				if ($child->nodeType === XML_ELEMENT_NODE) {
					$array['childs'][] = $this->convertToArray($child);
				}
			}
		}
		return $array;
	}

	protected function loadModuleApplication($name, $node, $element) {
		$element->addChild($elem = new Div());
		$args = array("_element" => $elem, "_document" => $this->document,
		"_name" => $name, "_style" => $this->style, "_data" => $this->data);
		if ($node&&$node->hasAttributes()) {
			foreach ($node->attributes as $key => $att) {
				$args[$att->name] = $att->value;
			}
		}
		if ($node&&$node->hasChildNodes()) {
			$children = array();
			foreach ($node->childNodes as $child) {
				if ($child->nodeType === XML_ELEMENT_NODE) {
					$children[] = $this->convertToArray($child);
				}
			}
			$args["_children"] = $children;
		}
		$modapp = $this->moduleAppList[$name];
		if (!$modapp) {
			$class = $this->classList[$name]['class'];
			$category = $this->classList[$name]['category'];
			if (class_exists($class)&&(!$category||$this->user->isPermitted($category))) {
				$modapp = $this->moduleAppList[$name] = new $class($name);
			} else {
				$handler = $this->exeptions['noaccess'];
				$class = $this->classList[$handler]['class'];
				if (class_exists($class)) {
					$modapp = $this->moduleAppList[$name] = new $class($handler);
				}
			}
		}
		$args = array_merge($_GET, $args);
		if (isset($modapp)) {
			$result = $modapp->createInstance($args);
			if (is_array($result)) {
				$this->data = array_merge($this->data, $result);
			}
		}
	}

	public function listModuleApplications($mod) {
		$list = array();
		$module = $this->doc->getElementsByTagName($mod)->item(0);
		$switches = 0;
		foreach ($module->childNodes as $node) {
			if ($node->nodeType === XML_ELEMENT_NODE && $node->nodeName !== "load") {
				switch ($node->nodeName) {
					case "switch":
						$on = $node->getAttribute("on");
						$cases = $node->getElementsByTagName("case");
						foreach ($cases as $casel) {
							$case = $casel->getAttribute("value");
							foreach ($casel->getElementsByTagName("app") as $app) {
								$i = count($list);
								$list[$i]['name'] = $app->getAttribute("name");
								$list[$i]['element'] = $app;
								$list[$i]['case'] = $case;
								$list[$i]['switch'] = $switches;
								foreach ($app->attributes as $attr) {
									$list[$i]["att"][$attr->name] = $attr->value;
								}
								foreach ($app->childNodes as $child) {
									if ($child->nodeType == XML_ELEMENT_NODE) {
										$list[$i]["childs"][] = $this->convertToArray($child);
									}
								}
							}
						}
						$switches++;
						break;
					case "app":
						$i = count($list);
						$list[$i]['name'] = $node->getAttribute("name");
						$list[$i]['element'] = $node;
						foreach ($node->attributes as $attr) {
							$list[$i]["att"][$attr->name] = $attr->value;
						}
						foreach ($node->childNodes as $child) {
							if ($child->nodeType === XML_ELEMENT_NODE) {
								$list[$i]["childs"][] = $this->convertToArray($child);
							}
						}
						break;
					default :
						$i = count($list);
					$list[$i]['name'] = $node->nodeName;
					$list[$i]['element'] = $node;
					foreach ($node->attributes as $attr) {
						$list[$i]["att"][$attr->name] = $attr->value;
					}
					foreach ($node->childNodes as $child) {
						if ($child->nodeType === XML_ELEMENT_NODE) {
							$list[$i]["childs"][] = $this->convertToArray($child);
						}
					}
					break;
				}
			}
		}
		return $list;
	}

	public function getXMLDoc() {
		return $this->doc;
	}

	public function getConfig() {
		return $this->config;
	}

	public function create() {
		if ($this->properties['restricted']==="true"&&!$this->user->isLoggedIn()) {
			$handler = $this->exeptions['login'];
			$this->document->body->emptyChildList();
			$this->loadModuleApplication($handler, null, $this->document->body);
		} else {
			foreach ($this->document->getModules() as $key => $element) {
				$module = $this->doc->getElementsByTagName($key)->item(0);
				foreach ($module->childNodes as $node) {
					if ($node->nodeType === XML_ELEMENT_NODE && $node->nodeName != "load") {
						switch ($node->nodeName) {
							case "switch":
								$on = $node->getAttribute("on");
								$pi = $_GET[$on];
								$cases = $node->getElementsByTagName("case");
								$found = false;
								$default = false;
								foreach ($cases as $case) {
									if ($case->getAttribute("value") === $pi) {
										foreach ($case->getElementsByTagName("app") as $app) {
											$this->loadModuleApplication($app->getAttribute("name"), $app, $element);
											$found = true;
										}
									} if ($case->getAttribute("default") === "true") {
										$default = $case;
									}
								}
								if (!$found&&$default) {
									foreach ($default->getElementsByTagName("app") as $app) {
										$this->loadModuleApplication($app->getAttribute("name"), $app, $element);
									}
								}
								break;
							case "app":
								$this->loadModuleApplication($node->getAttribute("name"), $node, $element);
								break;
							default:
								$this->loadModuleApplication($node->nodeName, $node, $element);
							break;
						}
					}
				}
			}
		}
	}

	/**
	 *
	 * Loads settings in the load element form the current template.
	 */
	function load() {
		$load = $this->doc->getElementsByTagName("load")->item(0);

		if ($stel = $load->getElementsByTagName("style")->item(0)) {
			$style = $stel->getAttribute("src");
			$this->properties['style'] = $style;
			$this->style = $styleManager = new StyleManager($style);
			$this->document = $styleManager->extractDocument();
		}

		$link = $load->getElementsByTagName("link");
		foreach ($link as $eli) {
			switch ($eli->getAttribute("type")) {
				case 'css':
					$src = $eli->getAttribute("src");
					$this->document -> head -> linkStyleSheet($this->dir.$src);
					$this->properties['css'][] = $src;
					break;
				case 'script':
					$src = $eli->getAttribute("src");
					$this->document -> head -> addScript($this->dir.$src);
					$this->properties['script'][] = $src;
					break;
			}
		}

		$element = $load->getElementsByTagName("title")->item(0);
		if ($element) {
			$title = utf8_decode($element->getAttribute("value"));
			$icon = $element->getAttribute("icon");
			$this->document->head->setTitle($title);
			$this->document->head->setIcon($icon);
			$this->properties['title'] = $title;
			$this->properties['icon'] = $icon;
		}

		$exeptions = $load->getElementsByTagName("exeption");
		if ($exeptions) {
			foreach ($exeptions as $exeption) {
				$type = $exeption->getAttribute("type");
				$handler = $exeption->getAttribute("handler");
				$this->exeptions[$type] = $handler;
			}
		}

		$options = $load->getElementsByTagName("option");
		if ($options) {
			foreach ($options as $option) {
				$name = $option->getAttribute("name");
				$value = $option->getAttribute("value");
				switch ($name) {
					case 'restricted':
						$this->properties['restricted'] = $value;
						break;
				}
			}
		}
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $module
	 * @param string $name
	 * @param array $args
	 * @param array $children
	 * @param int $index
	 */
	public function appendApplication($module, $name, $args = array(), $children = array(), $index = -1) {
		$module = $this->doc->getElementsByTagName($key)->item(0);
		$childs = $module->childNodes;
		$length = $childs->length;
		$application = $this->properties['apps'][$name];
		if ($application) {
			$app = $this->doc->createElement($name);
			foreach ($args as $key => $arg) {
				if (is_string($key)) {
					$app->setAttribute($key, $arg);
				}
			}
			foreach ($children as $key => $child) {
				$chi = $this->doc->createElement($child['name']);
				$ars = $child['args'];
				foreach ($ars as $key => $arg) {
					$chi->setAttribute($key, $arg);
				}
				$app->appendChild($chi);
			}
			if ($index < 0 || $index >= $length) {
				$index = $length;
				$module->appendChild($app);
			} else {
				$ref = $childs->item($index);
				$module->insertBefore($app, $ref);
			}
		}
	}

	/**
	 *
	 * Loads classes which is not yet declared and is not present in the default library.
	 * @param string $className
	 */
	public function autoLoadLibrary($className) {
		$path = false;
		foreach ($this->extensions as $lib) {
			if (is_file($lib.'/'.$className.'.php')) {
				$path = $lib.'/'.$className.'.php';
				break;
			}
		}
		if ($path) {
			set_include_path('extensions/');
			require $path;
		}
	}

	public function getProperties() {
		return $this->properties;
	}

	public function getStyle() {
		return $this->style;
	}

	public function saveTemplate() {
		$file = $this->properties['desc'];
		return $this->doc->save($file);
	}

	public function saveConfig() {
		$file = "config.xml";
		return $this->config->save($file);
	}
}