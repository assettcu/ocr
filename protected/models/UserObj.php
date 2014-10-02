<?php

class UserObj extends FactoryObj
{
	public function __construct($username=null)
	{
		parent::__construct("username","users",$username);
	}
	
	public function pre_save()
	{
		if(!$this->loaded and !isset($this->date_created))
		{
			$this->date_created = date("Y-m-d H:i:s");
		}
	}
	
    public function login()
    {
        if($this->loaded) {
            $this->date_lastlogin = date("Y-m-d H:i:s");
            $this->save();
        }
    }
    
	public function check_pass_strength()
	{
		return true;
	}
    
    public function doExpireDocs()
    {
        $conn = Yii::app()->db;
        $query = "
            UPDATE      {{files}}
            SET         status = 'Expired'
            WHERE       date_completed < :date_completed
            AND         username = :username;
        ";
        $transaction = $conn->beginTransaction();
        try {
            $command = $conn->createCommand($query);
            $command->bindParam(":username",$this->username);
            $expire_date = date("Y-m-d H:i:s",strtotime("-24 hours"));
            $command->bindParam(":date_completed",$expire_date);
            $command->execute();
            $transaction->commit();
        }
        catch(Exception $e) {
            $transaction->rollBack();
            // Don't do anything here
        }
    }
}

?>