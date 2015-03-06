<?php

require "BaseController.php";

class SiteController extends BaseController
{
    /** DEFAULT ACTIONS **/
    public function beforeAction($action) {
        // Include Google Analytics
        new GoogleAnalytics();
        return $action;
    }
    
    
	public function actionMaintenance()
	{
		$this->render('maintenance');
	}

    public function actionError()
    {
        if($error=Yii::app()->errorHandler->error)
        {
            if(Yii::app()->request->isAjaxRequest)
                echo $error['message'];
            else
                $this->render('error', $error);
        }
    }
	
	public function actionIndex()
	{
        // Force log out
        if(!Yii::app()->user->isGuest) {
            $this->render('service');
        }
        else {
            $this->makeSSL();
            $params = array();
            $model = new LoginForm;
            
            $redirect = (isset($_REQUEST["redirect"])) ? $_REQUEST["redirect"] : "index";
            
            // collect user input data
            if (isset($_POST['username']) and isset($_POST["password"])) {
                $model->username = $_POST["username"];
                $model->password = $_POST["password"];
                // validate user input and redirect to the previous page if valid
                if ($model->validate() && $model->login()) {
                    $this->redirect($redirect);
                    exit;
                } else {
                    Yii::app()->user->setFlash("error", $model->getError("password"));
                }
            }
            $params["model"] = $model;
            
            // display the login form
            $this->render('index',$params);
        }
	}
	
    /** NORMAL PAGES **/
	public function actionInstructions()
	{
		$this->render('instructions');
	}
	
	public function actionService()
	{
		$this->noGuest();
		
		$this->render('service');
	}

	public function actionDownload()
	{
		$fileid = $_REQUEST["fileid"];
		$file = new FilesObj($fileid);
		$file->status = "Downloaded";
        $file->save();
		
		if ($file->file_exists("scanout")) {
			ob_clean();
			ob_start();
			header('Content-Description: File Transfer');
			header('Content-Type: application/pdf');
			header('Content-Disposition: attachment; filename="'.basename($file->filename).'"');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file->get_filepath("scanout")));
			readfile($file->get_filepath("scanout"));
			ob_end_flush();
			exit;
		}
	}
	
	public function actionMassDownload()
	{
		$ids = $_REQUEST["fileids"];
		$fileids = explode(",",$ids);
		if(empty($fileids)) return false;
		
		$zip = new ZipArchive();
		$zipname = StdLib::rand_string(15).".zip";
		$zippath = getcwd()."/".$zipname;
		if($zip->open($zippath,ZIPARCHIVE::OVERWRITE)!==true)
		{
			return false;
		}
		
		foreach($fileids as $fileid)
		{
			$file = new FilesObj($fileid);
			if(!$file->loaded) continue;
			$zip->addFile($file->get_filepath("scanout"),$file->filename);
		}
		$zip->close();
		$downloadname = "PDF Download - ".date("Y-M-d Hi a").".zip";
		header("Content-type: application/zip");
		header("Content-Disposition: attachment; filename=".$downloadname);
		header("Pragma: no-cache");
		header("Expires: 0");
		readfile("$zippath");
		
		unlink($zippath);
		exit;
	}
	
	

}