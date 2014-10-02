<?php

class Manager
{
  
  public function load_NC_files()
  {
    $this->files = array();
    $conn = Yii::app()->db;
    $query = "
      SELECT    fileid
      FROM      {{files}}
      WHERE     status != \"Completed\" and status != \"Expired\" and status != \"Downloaded\"
      AND       username = :username
      ORDER BY  date_uploaded DESC;
    ";
    $username = Yii::app()->user->name;
    $command = $conn->createCommand($query);
    $command->bindParam(":username",$username);
    $result = $command->queryAll();
    
    if(!$result or empty($result)) return array();
    
    foreach($result as $row)
      $this->files[$row["fileid"]] = new FilesObj($row["fileid"]);
    
    return $this->files;
  }
  
  public function load_C_files()
  {
    $this->files = array();
    $conn = Yii::app()->db;
    $query = "
      SELECT    fileid
      FROM      {{files}}
      WHERE     (status = \"Completed\" or status = \"Expired\" or status = \"Downloaded\")
      AND       username = :username
      ORDER BY  date_uploaded DESC;
    ";
    $username = Yii::app()->user->name;
    $command = $conn->createCommand($query);
    $command->bindParam(":username",$username);
    $result = $command->queryAll();
    
    if(!$result or empty($result)) return array();
    
    foreach($result as $row)
      $this->files[$row["fileid"]] = new FilesObj($row["fileid"]);
    
    return $this->files;
  }
  
  public function load_files($status="")
  {
    if($status=="") return $this->load_all_files();
   
    if($status == "C") {
        return $this->load_C_files();
    }
    else if($status == "NC") {
        return $this->load_NC_files();
    }
    
    $this->files = array();
    $conn = Yii::app()->db;
    $query = "
      SELECT    fileid
      FROM      {{files}}
      WHERE     status = :status
      AND       username = :username
      ORDER BY  date_uploaded DESC;
    ";
    $command = $conn->createCommand($query);
    $username = Yii::app()->user->name;
    $command->bindParam(":status",$status);
    $command->bindParam(":username",$username);
    $result = $command->queryAll();
    
    if(!$result or empty($result)) return array();
    
    foreach($result as $row)
      $this->files[$row["fileid"]] = new FilesObj($row["fileid"]);
    
    return $this->files;
    
  }
  
  public function load_all_files()
  {
    $this->files = array();
    $conn = Yii::app()->db;
    $query = "
      SELECT    fileid
      FROM      {{files}}
      WHERE     username = :username
      ORDER BY  date_uploaded DESC;
    ";
    $command = $conn->createCommand($query);
    $username = Yii::app()->user->name;
    $command->bindParam(":username",$username);
    $result = $command->queryAll();
    
    if(!$result or empty($result)) return array();
    
    foreach($result as $row)
      $this->files[$row["fileid"]] = new FilesObj($row["fileid"]);
    
    return $this->files;
  }
  
  public function update_files()
  {
    if(!isset($this->files)) return false;
    
    foreach($this->files as $file)
    {
      // Don't worry about the non-active statuses
      if($file->status == "Downloaded" or $file->status == "Failed" or $file->status == "Expired") continue;
      
      // File's been in Queue for over 15 minutes (probably failed)
      if(file_exists($file->get_scanin_folder().$file->filename) and $file->date_uploaded < date("Y-m-d H:i:s",strtotime("-15 minutes")))
      {
        $file->status = "Failed";
        $file->save();
        continue;
      }
      // File still waiting in Queue
      if(file_exists($file->get_scanin_folder().$file->filename) and $file->date_uploaded > date("Y-m-d H:i:s",strtotime("-15 minutes")))
      {
        $file->status = "Queued";
        $file->save();
        continue;
      }
      // File still waiting in Queue for more than 15 minutes
      else if(file_exists($file->get_scanin_folder().$file->filename) and $file->date_uploaded < date("Y-m-d H:i:s",strtotime("-15 minutes")))
      {
          $file->status = "Failed";
          $file->save();
          continue;
      }
      
      // File is in limbo (processing)
      if(!file_exists($file->get_scanin_folder().$file->filename) and !file_exists($file->get_scanout_folder().$file->filename) and $file->date_uploaded > date("Y-m-d H:i:s",strtotime("-1 hour")))
      {
        $file->status = "Processing";
        $file->save();
        continue;
      }
      else if(!file_exists($file->get_scanin_folder().$file->filename) and !file_exists($file->get_scanout_folder().$file->filename) and $file->date_uploaded > date("Y-m-d H:i:s",strtotime("-1 hour")))
      {
        $file->status = "Failed";
        $file->save();
        continue;
      }
      
      // File is completed
      if(!file_exists($file->get_scanin_folder().$file->filename) and file_exists($file->get_scanout_folder().$file->filename))
      {
          if(isset($file->date_completed) and !empty($file->date_completed) and $file->date_completed < date("Y-m-d H:i:s",strtotime("-1 week"))) {
              $file->status = "Expired";
          }
          else if($file->status != "Completed"){
              $file->status = "Completed";
              $file->date_completed = date("Y-m-d H:i:s");
          }
          $file->save();
          continue;
      }
    }
    
    return $this->files;
  }
  
}