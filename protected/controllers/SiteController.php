<?php

class SiteController extends Controller
{
	/**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	public function actionMaintenance()
	{
		$this->render('maintenance');
	}
	
	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
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
	
	public function actionInstructions()
	{
		
		$this->render('instructions');
	}
	
	public function actionService()
	{
		$this->noGuest();
		
		$this->render('service');
	}
	
    public function action_upload_files()
    {
        
        /**
         * upload.php
         *
         * Copyright 2009, Moxiecode Systems AB
         * Released under GPL License.
         *
         * License: http://www.plupload.com/license
         * Contributing: http://www.plupload.com/contributing
         */
        
        // HTTP headers for no cache etc
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        
        // Settings
        $targetDir = Yii::app()->params["OCR_FILE_IN_TEMP"].Yii::app()->user->name."/";
        if(!is_dir($targetDir)) {
            mkdir($targetDir);
        }

        $cleanupTargetDir = true; // Remove old files
        $maxFileAge = 5 * 3600; // Temp file age in seconds
        
        // 5 minutes execution time
        @set_time_limit(5 * 60);
        
        // Uncomment this one to fake upload time
        // usleep(5000);
        
        // Get parameters
        $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
        $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
        $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
        
        // Clean the fileName for security reasons
        $fileName = preg_replace('/[^\w\._]+/', '_', $fileName);
        
        // Make sure the fileName is unique but only if chunking is disabled
        if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
            $ext = strrpos($fileName, '.');
            $fileName_a = substr($fileName, 0, $ext);
            $fileName_b = substr($fileName, $ext);
        
            $count = 1;
            while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
                $count++;
        
            $fileName = $fileName_a . '_' . $count . $fileName_b;
        }
        
        $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
        
        // Create target dir
        if (!file_exists($targetDir))
            @mkdir($targetDir);
        
        // Remove old temp files    
        if ($cleanupTargetDir) {
            if (is_dir($targetDir) && ($dir = opendir($targetDir))) {
                while (($file = readdir($dir)) !== false) {
                    $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
        
                    // Remove temp file if it is older than the max age and is not the current file
                    if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
                        @unlink($tmpfilePath);
                    }
                }
                closedir($dir);
            } else {
                die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
            }
        }   
        
        // Look for the content type header
        if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
            $contentType = $_SERVER["HTTP_CONTENT_TYPE"];
        
        if (isset($_SERVER["CONTENT_TYPE"]))
            $contentType = $_SERVER["CONTENT_TYPE"];
        
        // Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
        if (strpos($contentType, "multipart") !== false) {
            if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {
                // Open temp file
                $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
                if ($out) {
                    // Read binary input stream and append it to temp file
                    $in = @fopen($_FILES['file']['tmp_name'], "rb");
        
                    if ($in) {
                        while ($buff = fread($in, 4096))
                            fwrite($out, $buff);
                    } else
                        die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                    @fclose($in);
                    @fclose($out);
                    @unlink($_FILES['file']['tmp_name']);
                } else
                    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            } else
                die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
        } else {
            // Open temp file
            $out = @fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
            if ($out) {
                // Read binary input stream and append it to temp file
                $in = @fopen("php://input", "rb");
        
                if ($in) {
                    while ($buff = fread($in, 4096))
                        fwrite($out, $buff);
                } else
                    die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
        
                @fclose($in);
                @fclose($out);
            } else
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
        }
        
        // Check if file has been uploaded
        if (!$chunks || $chunk == $chunks - 1) {
            // Strip the temp .part suffix off 
            rename("{$filePath}.part", $filePath);
        }

        die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');

    }
    
    public function action_add_uploaded_file()
    {
        $targetDir = Yii::app()->params["OCR_FILE_IN_TEMP"].Yii::app()->user->name."/";
        $newdir = Yii::app()->params["OCR_FILE_IN"].Yii::app()->user->name;
        if(!is_dir($newdir)) {
            mkdir($newdir);
        }
		$file = $targetDir.(preg_replace('/[^\w\._]+/', '_', $_REQUEST["filename"]));
		
    	if(is_file($file)) {
    		$newfile = Yii::app()->params["OCR_FILE_IN"].Yii::app()->user->name."/".$_REQUEST["filename"];
    		$oldfile = Yii::app()->params["OCR_FILE_OUT"].Yii::app()->user->name."/".$_REQUEST["filename"];
    		
    		copy($file,$newfile);
			unlink($file);
			
			if(is_file($oldfile)) {
				unlink($oldfile);
			}
	        $fileobj = new FilesObj();
	        $fileobj->filename = $_REQUEST["filename"];
	        $fileobj->filesize = $_REQUEST["size"];
	        $fileobj->date_uploaded = date("Y-m-d H:i:s");
	        $fileobj->username = Yii::app()->user->name;
	        $fileobj->save();
    	}
		
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
	
	/*
	 *   AJAX CALLS BELOW!!
	 */
	
	public function action_reprocess_files()
	{
		$ids = $_REQUEST["fileids"];
		$fileids = explode(",",$ids);
		if(empty($fileids)) return print "No ids were set";
		
		foreach($fileids as $fileid)
		{
			$file = new FilesObj($fileid);
			$file->reprocess();
		}
		return print 1;
	}
	
	public function action_remove_files()
	{
		$ids = $_REQUEST["fileids"];
		$fileids = explode(",",$ids);
		if(empty($fileids)) return print "No ids were set";
		
		foreach($fileids as $fileid)
		{
			$file = new FilesObj($fileid);
			$file->delete();
		}
		
		return print 1;
	}
	
	public function action_update_files()
	{
		$manager = new Manager();
		$manager->load_all_files();
		$files = $manager->update_files();
		$return = array();
		foreach($files as $file)
		{
			$return[$file->fileid] =
				array(
					"status" => $file->status,
					"disp_status" => $file->get_status_text()
				);
		}
		
		return print json_encode($return);
	}
	
	public function action_load_processed_files()
	{
		function formatBytes($bytes, $precision = 2) { 
			$units = array('B', 'KB', 'MB', 'GB', 'TB'); 

			$bytes = max($bytes, 0); 
			$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
			$pow = min($pow, count($units) - 1); 

			// Uncomment one of the following alternatives
			// $bytes /= pow(1024, $pow);
			$bytes /= (1 << (10 * $pow)); 

			return round($bytes, $precision) . ' ' . $units[$pow]; 
		} 
		$fileid = $_REQUEST["fileid"];
		$file = new FilesObj($fileid);
		ob_start();
		?>
		<tr>
			<td class="calign"><input type="checkbox" name="processed-boxes" value="<?=$file->fileid?>" /></td>
			<td><?=$file->filename?></td>
			<td class="calign"><?=formatBytes($file->filesize,2);?></td>
			<td class="calign"><?=StdLib::format_date($file->date_completed,"nice");?></td>
			<td class="calign"><button class="download-button" title="Download Processed PDF" value="<?=$file->fileid?>">Download</button></td>
		</tr>
		<?php
		$contents = ob_get_contents();
		ob_end_clean();
		
		return print $contents;
	}
	
	
	/**
	 * This is the action to handle external exceptions.
	 */
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

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
	
	private function noGuest()
	{
		if(Yii::app()->user->isGuest)
		{
			$this->redirect(Yii::app()->createUrl('site/login',array('redirect'=>"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])));
		}
	}
	
	private function makeSSL()
	{
		if($_SERVER['SERVER_PORT'] != 443) {
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			exit();
		}
	}

	private function makeNonSSL()
	{
		if($_SERVER['SERVER_PORT'] == 443) {
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
			exit();
		}
	}
}