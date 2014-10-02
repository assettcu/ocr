<?php

class FilesObj extends FactoryObj
{
	public $target_path = "C:/web/OCR/scanin/";
	public $target_path_out = "C:/web/OCR/scanout/";
	public $apath = "C:/web/OCR/preserve/";
	
	public function __construct($fileid=null)
	{
		parent::__construct("fileid","files",$fileid);
		if($this->loaded)
		{
			if($this->status=="Completed" and !file_exists($this->get_filepath('scanout')))
			{
				$this->status = "Expired";
				$this->save();
			}
			if($this->status=="Expired" and $this->date_completed < date("Y-m-d H:i:s",strtotime("-1 day")))
			{
				$this->delete();
			}
		}
	}
	
	public function pre_load()
	{
		if(!$this->is_valid_id())
		{
			if(isset($this->username,$this->filename))
			{
				$conn = Yii::app()->db;
				$query = "
					SELECT		fileid
					FROM			{{files}}
					WHERE			username = :username
					AND				filename = :filename
					LIMIT			1;
				";
				$command = $conn->createCommand($query);
				$command->bindParam(":username",$this->username);
				$command->bindParam(":filename",$this->filename);
				$this->fileid = $command->queryScalar();
			}
		}
	}
	
	public function pre_delete()
	{
		if(file_exists($this->get_filepath("scanin")) and $this->status=="Queued")
		{
			unlink($this->get_filepath("scanin"));
		}
		if(file_exists($this->get_filepath("scanout")) and ($this->status!="Queued" or $this->status!="Processing"))
		{
			unlink($this->get_filepath("scanout"));
		}
	}
	
	public function reprocess()
	{
		if(!$this->loaded) return false;
		if(file_exists($this->get_filepath("scanout")))
		{
			copy($this->get_filepath("scanout"),$this->get_filepath("scanin"));
			unlink($this->get_filepath("scanout"));
			$this->status = "Queued";
			$this->date_uploaded = date("Y-m-d H:i:s");
			$this->date_completed = "0000-00-00 00:00:00";
			if(!$this->save()) var_dump($this->get_error());
		} else {
			print "NOT FOUND:".$this->get_filepath("scanout");
		}
	}
	
	public function get_scanin_folder($username="")
	{
		if(!isset($this->username) and $username=="") return "";
		if($username != "") return $this->target_path.$username."/";
		return $this->target_path.$this->username."/";
	}
	
	public function get_scanout_folder($username="")
	{
		if(!isset($this->username) and $username=="") return "";
		if($username != "") return $this->target_path_out.$username."/";
		return $this->target_path_out.$this->username."/";
	}
	
	public function upload_file($file)
	{
		$file->name = str_replace("?","",$file->name);
		$tpath = $this->get_scanin_folder($file->username);
		if(!is_dir($tpath)) mkdir($tpath);
		
		$apath = $this->apath.$file->username."/";
		if(!is_dir($apath)) mkdir($apath);
		
		$filepath = $tpath.$file->name;
		$archivepath = $apath.$file->name;
		
		move_uploaded_file($file->tmp_name,$filepath);
		copy($filepath,$archivepath);
		
		$this->filename				= $file->name;
		$this->filesize				= $file->size;
		$this->username				= @$file->username;
		$this->status 				= @$file->status;
		$this->date_uploaded		= date("Y-m-d H:i:s");
		
		if(file_exists($this->get_scanout_folder($file->username).$file->name))
		{
			unlink($this->get_scanout_folder($file->username).$file->name);
			$rfile = new FilesObj();
			$rfile->username = $this->username;
			$rfile->filename = $file->name;
			$rfile->load();
			if($rfile->loaded) $rfile->delete();
		}
		
		return $this;
	}
	
	public function get_status_text()
	{
		switch($this->status)
		{
			case "Queued": 			return "<span style='color:#777;'>queued</span>";
			case "Failed": 			return "<span style='color:#a00;'>&lt; failed &gt;</span>";
			case "Processing":	return "<span style='color:#aaa;font-style:italic;'>processing</span>";
			default: return strtolower($this->status);
		}
	}
	
	public function file_exists($folder="scanout")
	{
		return (is_file($this->get_filepath($folder)));
	}
	
	public function get_filepath($folder="scanout")
	{
		if($folder=="scanout") return $this->get_scanout_folder($this->username).$this->filename;
		if($folder=="scanin") return $this->get_scanin_folder($this->username).$this->filename;
		return "";
	}
	
}

?>