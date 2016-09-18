<?php
/**
*
*/
class FlowplayerPlugin implements Action, ModuleApp {

	protected $key;	public function __construct($key) {
		$this->key = $key;
	}
	
	public function performAction($command, $args) {
			switch ($this->key) {
						case "":
						break;
		}
	}
								
	public function createInstance($args) {
			$content = $args["_element"];
			$document = $args["_document"];		
			$style = $args["_style"];
			$source = $args["src"];
			switch ($this->key) {			
				case "player":				
				$document->head->addScript('extensions/flowplayer/flowplayer/flowplayer-3.2.9.min.js');
				$content->addChild(new HtmlElement("h1", false, "Videoavspiller"));		
				$content->addChild(new Link($source ? $source : "extensions/flowplayer/flowplayer-700.flv", "", array("id" => "player",
								"style" => "display:block;width:425px;height:300px;margin:10px auto")));				
				$content->script("flowplayer('player', 'extensions/flowplayer/flowplayer/flowplayer-3.2.10.swf', {".	
							"clip: {autoPlay: false, autoBuffering: true}});");				
				break;		
				}	
			}
	}