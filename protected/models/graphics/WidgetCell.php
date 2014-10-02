<?php

class WidgetCell extends Graphics {
	
	protected $style;
	protected $classes;
	protected $content;
	
	private $options_rendered;
	
	public function __construct($params=array()){
		$this->classes = "";
		$this->options = new GraphicsOptions();
		$this->content = "";
		if(!empty($params)){
			if(isset($params["classes"])){
				if(is_array($params["classes"])){
					foreach($params["classes"] as $class){
						$this->addClass($class);
					}
				} else {
					$this->classes = $params["classes"];
				}
			}
			if(isset($params["options"])){
				$this->options->addOptions(array("Cell"=>$params["options"]));
			}
			if(isset($params["content"])){
				$this->content = $params["content"];
			}
			if(isset($params["value"])){
				$this->value = $params["value"];
			}
		}
	}
	
	public function addContent($content){
		$this->content .= $content;
	}
	
	public function addClass($class){
		$this->classes .= "$class ";
	}
	
	public function getContent(){
		return $this->content;
	}
	
	public function getClasses(){
		return $this->classes;
	}
	
	public function getOptions(){
		return $this->options;
	}
	
	public function getValue(){
		if(isset($this->value)) return $this->value;
		return "";
	}
	
}