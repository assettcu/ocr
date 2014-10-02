<?php

class PerformanceObj extends FactoryObj
{
  public function __construct($fileid)
  {
    parent::__construct("fileid","performance",$fileid);
  }
  
  public function pre_save()
  {
    // If a valid fileid is passed but cannot be found, add the date_created param
    if($this->is_valid_id() and !$this->loaded)
    {
      $this->date_created = date("Y-m-d H:i:s");
    }
  }
  
}