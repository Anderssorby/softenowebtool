<?
/**
 *
 */
class DOMStructure {
	protected $document;
	protected $dom;
	public function __construct(DOMDocument $document) {
		$doc = $document -> documentElement;
		$this -> loadFromElement($doc);
	}

	/**
	 * 
	 */
	public static function parseHTMLElements($string) {
		$nodeList = array();
		$pattern = '/(<[^>]*[^\/]>)/i';
		$split = preg_split($pattern, $string, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
		print_r($split);
		for ($i = 1; $s = $split[$i]; $i++) {
			$nd = '/([[:alpha:]])\s([[:alpha:]]+="[[:alnum:]]*")*/';
			$inn = preg_split($nd, $s, 0, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			$tagname = $inn[0];
			for ($j = 1; $a = $inn[$j]; $j++) {
				$th = '/([[:alpha:]]+)="([[:alnum:]]*)"/';
				$sa = preg_split($th, $a);
				$attributes[$j-1]['name'] = $sa[0];
				$attributes[$j-1]['value'] = $sa[1];
			}
			var_dump($attributes);
			echo "\nchildren:".$children = $s[$i+3];
			$hasEndTag = $s[2]?TRUE:FALSE;
			$node = new HtmlElement($tagname, $hasEndTag);
			$pchilds = $children ? DOMStructure::parseHTMLElements($children) : array();
			foreach ($pchilds as $c) {
				$node->addChild($c);
			}
			$nodeList[] = $node;
		}
		return $nodeList;
	}

	public function loadFromElement(DOMNode $el) {
		$nodeName = $el -> nodeName;
		$node;
		switch ($nodeName) {
			case 'button' :
				$node = new Button();
				$att = $el -> attributes;
				for ($i = 0; $a = $att -> item($i); $i++) {
					$name = $a -> name;
					$value = $a -> value;
					$node -> setAttribute($name, $value);
				}
				break;
			case 'div' :
				$node = new Div();
				$att = $el -> attributes;
				for ($i = 0; $a = $att -> item($i); $i++) {
					$name = $a -> name;
					$value = $a -> value;
					$node -> setAttribute($name, $value);
				}
				break;
			default :
				$node = new HtmlElement($nodeName);
				$att = $el -> attributes;
				for ($i = 0; $a = $att -> item($i); $i++) {
					$name = $a -> name;
					$value = $a -> value;
					$node -> setAttribute($name, $value);
				}
				break;
		}
		
		$children = $el -> childNodes;
		for ($i = 0; $child = $children -> item($i); $i++) {
			$node->addChild($this -> loadFromElement($child));
		}
		return $node;
	}

}
