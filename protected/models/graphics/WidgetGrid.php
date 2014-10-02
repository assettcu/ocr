<?php

class WidgetGrid extends Graphics {
	
	public $header_bg_color;
	public $content_bg_color;
	public $options;
	
	protected $grid;
	protected $numcols = 0;
	
	public function __construct(){
		$this->width = "100%";
		$this->height = "auto";
		$this->header_bg_color = "#ccc";
		$this->content_bg_color = "#ccd";
		$this->content_border = "1px #303 solid";
		$this->col_width = "100%";
		$this->header_classes = "";
		$this->content_classes = "";
		$this->list = array();
		$this->options = new GraphicsOptions();
	}
	
	private function loadOptions(){

		if(count($this->cells)>0){
			$width = (100 / count($this->cells[1]));
			$this->col_width = $width."%";
		}
		
		$options["header"]["width"] = $this->col_width;
		$options["content"]["width"] = $this->col_width;
		
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
	
	public function addGridItem($list){
		if(!empty($list) and is_array($list)){
			foreach($list as $content){
				$this->grid[]=$content;
			}
		}
	}
	
	public function render(){
		$output = $this->getHTML();
		print $output;
	}
	
	public function setHeader($header){
		$this->headers[] = $header; 
	}
	
	public function setCell($rownum,$colnum,$content){
		$this->cells[$rownum][$colnum] = $content; 
	}
	
	public function addCell2Row($rownum,$content){
		$this->cells[$rownum][] = $content;
	}
	
	public function addGrid($list){
		foreach($list as $header=>$items){
			$this->setHeader($header);
			$row = 0;
			foreach($items as $cell){
				$this->addCell2Row($row,$cell);
				$row++;
			}
		}
	}
	
	public function getHTML(){
		
		$this->loadOptions();

		$table_options = $this->options->getOptions("table");
		$header_options = $this->options->getOptions("header");
		$content_options = $this->options->getOptions("content");
		$table = "";
		
		if(!empty($this->cells)){
			$table = "<table style=\"$table_options\">";
			
			// If there are headers, append them to the table
			if(isset($this->headers) and !empty($this->headers)){
				$table .= "<thead><tr>";
				foreach($this->headers as $header){
						$table .= 
<<<TABLE
	<th class="ui-corner-all" style="$header_options">$header</th>
TABLE;
				}
				$table .= "</tr></thead>";
			}
			
			// If there are cell values (there should!), append them
			$table .= "<tbody>";
			foreach($this->cells as $row){
				$table .= "<tr>";
				foreach($row as $cell){
					if(get_class($cell)=="WidgetCell"){
						$classes = $cell->getClasses();
						$options = $cell->getOptions();
						$style = $options->getOptions("Cell");
						$content = $cell->getContent();
						$value = $cell->getValue();
						$table .= 
<<<TABLE
	<td class="$classes" CellValue="$value" style="$content_options;$style" >$content</td>
TABLE;
					} else {

<<<TABLE
	<td>$cell</td>
TABLE;
					}
				}
				$table .= "</tr>";
			}
			$table .= "</tbody></table>";
		}
		
		return $table;
	}
	
}