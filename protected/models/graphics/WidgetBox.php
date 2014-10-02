<?php

class WidgetBox extends Graphics {
	
	public $header;
	public $content;
	
	private $type;
	
	public function __construct(){
		parent::__construct();
		$this->width = "auto";
		$this->height = "auto";
		$this->header = "";
		$this->content = "";
		$this->type = "header1";
	}
	
	private function loadOptions(){
		switch($this->type){
			case "header1":
				$options["container"]["padding"] = "5px";
				$options["container"]["float"] = "left";
				$options["container"]["display"] = "block";
				$options["container"]["margin-right"] = "10px";
				$options["container"]["margin-bottom"] = "10px";
				$options["header"]["padding"] = "5px";
				$options["header"]["text-align"] = "center";
				$options["header"]["margin-bottom"] = "10px";
				$options["content"]["padding"] = "5px";
				$options["content"]["font-size"] = "12px";
			break;
			case "header2":
				$options["container"]["padding"] = "1px";
				$options["container"]["float"] = "left";
				$options["container"]["display"] = "block";
				$options["container"]["margin-right"] = "0px";
				$options["container"]["margin-bottom"] = "5px";
				$options["header"]["padding"] = "1px";
				$options["header"]["padding-left"] = "10px";
				$options["header"]["text-align"] = "left";
				$options["header"]["margin-bottom"] = "3px";
				$options["content"]["padding"] = "5px";
				$options["content"]["font-size"] = "12px";
			break;
			default: break;
		}
		$options["container"]["width"] = $this->width;
		$options["container"]["height"] = $this->height;
		
		$this->options->addOptions($options);
	}
	
	public function addOption($option){
		$this->options->addOptions($option);
	}
	
	public function addHeaderClass($class){
		$this->header_classes .= "$class ";
	}
	
	public function addContentClass($class){
		$this->content_classes .= "$class ";
	}
	
	public function setType($type){
		$this->type = $type;
	}
	
	public function render(){
		$output = $this->getHTML();
		print $output;
	}
	
	public function addOptions($option){
		$this->options->addOptions($option);
	}

	public function getHTML(){
		
		$this->loadOptions();
		
		$container = $this->options->getOptions("container");
		$header = $this->options->getOptions("header");
		$content = $this->options->getOptions("content");
		
		$output = 
<<<HTML
<div class="ui-widget ui-widget-content ui-corner-all" style="$container">
HTML;
		if($this->header != ""){
			$output .= 
<<<HTML
	<div class="ui-widget ui-widget-header ui-corner-all" style="$header">
		$this->header
	</div>
HTML;
		}
		$output .=
<<<HTML
	<div style="$content" class="widget-content">$this->content</div>
</div>
HTML;

		return $output;
	}
	
	public function addContent($addition){
		$this->content = $this->content.$addition;
	}
	
}