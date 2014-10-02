<?php

class WidgetList extends Graphics {

	public $header_bg_color;
	public $content_bg_color;
	public $id = "";
	public $col_headers = array("","");

	protected $list;

	public function __construct(){
		$this->width = "100%";
		$this->height = "auto";
		$this->header_bg_color = "#eee";
		$this->header_width = "50%";
		$this->content_width = "50%";
		$this->content_bg_color = "#eef";
		$this->list = array();
		$this->options = new GraphicsOptions();
	}

	private function loadOptions(){

		$options["header"]["width"] = $this->header_width;
		$options["content"]["width"] = $this->content_width;
		$options["header"]["background-color"] = $this->header_bg_color;
		$options["content"]["background-color"] = $this->content_bg_color;

		$this->options->addOptions($options);
	}

	public function addListItem($list){
		if(!empty($list) and is_array($list)){
			foreach($list as $header=>$content){
				$this->list[$header]=$content;
			}
		} else {
			$this->list[] = $list;
		}
	}

	public function render(){
		$output = $this->getHTML();
		print $output;
	}

	public function getHTML(){

		$this->loadOptions();

		$header_options = $this->options->getOptions("header");
		$content_options = $this->options->getOptions("content");

		if(!empty($this->list)){
			$list = "";
			foreach($this->list as $title=>$content){
				$list .=
<<<LIST
		<tr>
			<td class="list-label" style="$header_options">$title</td>
			<td class="list-content" style="$content_options">$content</td>
		</tr>
LIST;
			}
		}

		$output =
<<<HTML
<table style="border-spacing:3px;" id="{$this->id}">
	<thead>
		<tr>
			<th class="col1">{$this->col_headers[0]}</th>
			<th class="col2">{$this->col_headers[1]}</th>
		</tr>
	</thead>
	<tbody>
		$list
	</tbody>
</table>
HTML;
		return $output;
	}

}