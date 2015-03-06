<?php
header('Access-Control-Allow-Origin: *');

require "BaseController.php";

class AJAXController extends BaseController
{

    public function actionUploadFiles()
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
        
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        # Nothing is required
        $required = array();
        $keys = array_keys($request);
        
        # Must be logged in
        if(Yii::app()->user->isGuest) {
            return RestUtils::sendResponse(310); 
        }
        
        # Not all parameters sent
        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308); 
        }
        
        // HTTP headers for no cache etc
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
        
        // Settings
        $targetDir = OCR_TEMP.Yii::app()->user->name."/";
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
        if (isset($_SERVER["HTTP_CONTENT_TYPE"])){
            $contentType = $_SERVER["HTTP_CONTENT_TYPE"];
        }
        else if (isset($_SERVER["CONTENT_TYPE"])) {
            $contentType = $_SERVER["CONTENT_TYPE"];
        }
        else {
            die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open content type."}, "id" : "id"}');
        }
        
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

        return print '{"jsonrpc" : "2.0", "result" : null, "id" : "id"}';
    }
    
    public function actionAddUploadedFile()
    {
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        $required = array("filename","size");
        $keys = array_keys($request);
        
        # Must be logged in
        if(Yii::app()->user->isGuest) {
            return RestUtils::sendResponse(310); 
        }
        
        # Not all parameters sent
        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308); 
        }
        
        # Directories for files
        $targetDir = OCR_TEMP.Yii::app()->user->name."/";
        $newdir = OCR_FILE_IN.Yii::app()->user->name;
        
        # Make if doesn't exist
        if(!is_dir($newdir)) {
            mkdir($newdir);
        }
        $file = $targetDir.(preg_replace('/[^\w\._]+/', '_', $request["filename"]));
        
        # File exists, let's process it
        if(is_file($file)) {
            $newfile = OCR_FILE_IN.Yii::app()->user->name."/".$request["filename"];
            $oldfile = OCR_TEMP.Yii::app()->user->name."/".$request["filename"];
            
            copy($file,$newfile);
            unlink($file);
            
            if(is_file($oldfile)) {
                unlink($oldfile);
            }
            $fileobj = new FilesObj();
            $fileobj->filename = $request["filename"];
            $fileobj->filesize = $request["size"];
            $fileobj->date_uploaded = date("Y-m-d H:i:s");
            $fileobj->username = Yii::app()->user->name;
            $fileobj->save();
        }
        
        # Process finished successfully
        RestUtils::sendResponse(200,"Success");
    }
    
    public function actionRemoveFiles()
    {
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        $required = array("fileids");
        $keys = array_keys($request);
        
        # Must be logged in
        if(Yii::app()->user->isGuest) {
            return RestUtils::sendResponse(310); 
        }
        
        # Not all parameters sent
        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308); 
        }
        
        $ids = $request["fileids"];
        $fileids = explode(",",$ids);
        if(empty($fileids)) return print "No ids were set";
        
        foreach($fileids as $fileid) {
            $file = new FilesObj($fileid);
            $file->delete();
        }
        
        RestUtils::sendResponse(200,"Success");
    }
    
    public function actionReprocessFiles()
    {
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        $required = array("fileids");
        $keys = array_keys($request);
        
        # Must be logged in
        if(Yii::app()->user->isGuest) {
            return RestUtils::sendResponse(310); 
        }
        
        # Not all parameters sent
        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308); 
        }
        
        $ids = $request["fileids"];
        $fileids = explode(",",$ids);
        if(empty($fileids)) return print "No ids were set";
        
        foreach($fileids as $fileid) {
            $file = new FilesObj($fileid);
            $file->reprocess();
        }
        
        RestUtils::sendResponse(200,"Success");
    }
    
    public function actionUpdateFiles()
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
    
    public function actionLoadProcessedFiles()
    {
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        $required = array("fileid");
        $keys = array_keys($request);
        
        # Must be logged in
        if(Yii::app()->user->isGuest) {
            return RestUtils::sendResponse(310); 
        }
        
        # Not all parameters sent
        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308); 
        }
        StdLib::Functions();
        $fileid = $request["fileid"];
        $file = new FilesObj($fileid);
        ob_start();
        ?>
        <tr>
            <td class="calign"><input type="checkbox" name="processed-boxes" value="<?php echo $file->fileid; ?>" /></td>
            <td><?php echo $file->filename; ?></td>
            <td class="calign"><?php echo formatBytes($file->filesize,2);?></td>
            <td class="calign"><?php echo StdLib::format_date($file->date_completed, "nice"); ?></td>
            <td class="calign"><button class="download-button" title="Download Processed PDF" value="<?php echo $file->fileid; ?>">Download</button></td>
        </tr>
        <?php
        $contents = ob_get_contents();
        ob_end_clean();
        
        return print $contents;
    }
    
}
    