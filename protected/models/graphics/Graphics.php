<?php

class Graphics {
	
	public $width;
	public $height;
	
	protected $options;
	
	public function __construct(){
		$this->options = new GraphicsOptions();
	}
	
	public function loadTemplate($template){
		$xml = simplexml_load_file(getcwd()."\\protected\\models\\graphics\\templates\\".$template.".xml");
		$children = $xml->children();
		foreach($children as $child){
			$return = $this->xmlLoadOptions($child,$xml);
		}
	}
	
	public function xmlLoadOptions($obj,$xml){
		$name = $obj->getName();
		if(isset($obj->options[0])){
			foreach($xml->$name->options[0]->attributes() as $a=>$b){
				$this->addOption(array($name=>array($a=>$b)));
			}
		} else return false;
		return true;
	}
	
}