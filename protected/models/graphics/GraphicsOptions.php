<?php

class GraphicsOptions {

		public function addOptions($options){
			if(!empty($options) and is_array($options)){
				foreach($options as $type=>$option){
					if(!isset($this->$type)) $this->$type = "";
					if(!empty($option) and is_array($option)){
						foreach($option as $param=>$value){
							$this->$type = "$param:$value;" . $this->$type;
						}
					}
				}
			}
		}

		public function getOptions($option){
			if(isset($this->$option))
				return $this->$option;
			else
				return "";
		}

}