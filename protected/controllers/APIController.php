<?php
header('Access-Control-Allow-Origin: *');

require "BaseController.php";

class APIController extends BaseController
{
    /**
     * Upload File
     *
     * Make a copy of the file 
     * 
     * @param   string  file_dir    file directory path
     * @param   string  file_name   name of file in directory
     * @return  json    file_id     unique id of file
     */
    public function actionUploadFile()
    {
        # Look to AjaxController for similar functionality
        # Use Rest API for processing passed in parameters and returning statuses
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        $required = array("file_dir", "file_name");
        $keys = array_keys($request);
        
        # Not all parameters sent
        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308); 
        }

        $fileDir = $request['file_dir'];
        $fileName = $request['file_name'];

        # Check if the file location is a file
        if(!is_dir($fileDir) || !is_file($fileDir.$fileName)) {
            return RestUtils::sendResponse(308, "Invalid directory or file name.");
        }

        # Create Target directory if it doesn't exist yet
        $inDir = OCR_FILE_IN."API"."/";
        $outDir = OCR_FILE_OUT."API"."/";
        if(!is_dir($inDir)) {
            mkdir($inDir);
        }

        # !!! Should check input and output directory for duplicate names !!!
        if(is_file($inDir.$fileName) || is_file($outDir.$fileName)) {
            return RestUtils::sendResponse(308, "File already exists.");
        }

        # Copy file to OCR IN directory
        copy($fileDir.$fileName, $inDir.$fileName);

        # Create file object
        $fileobj = new FilesObj();
        $fileobj->filename = $fileName;
        $fileobj->filesize = filesize($fileDir.$fileName);
        $fileobj->date_uploaded = date("Y-m-d H:i:s");
        $fileobj->username = "API";
        $fileobj->save();
        
        # Process finished successfully
        $return = json_encode(array("id"=>$fileobj->{$fileobj->uniqueid}));
        RestUtils::sendResponse(200, $return);
    }
    
    /**
     * File Status
     * 
     * Ping the service for the status on a file. The file may be:
     * Queued, Processing, Completed, Downloaded, Expired, or Failed
     * 
     * @param   integer file_id     unique id of file
     * @return  json    status of file conversion JSON string
     */
    public function actionFileStatus()
    {
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        $required = array("file_id");
        $keys = array_keys($request);

        # Not all parameters sent
        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308, "Missing file id number."); 
        }

        $fileId = $request['file_id'];

        $manager = new Manager();
        $manager->load_file_by_id($fileId);
        $files = $manager->update_files();
        $return = array();
        foreach($files as $file)
        {
            $return[$file->fileid] =
                array(
                    "status" => $file->status
                );
        }
        
        return print json_encode($return);
    }
    
    /**
     * Download File
     * 
     * Force download a file.
     * 
     * @param   integer     file_id     unique id of file to download
     * @return  json        path and filename of requested file
     */
    public function actionDownloadFile()
    {
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        $required = array("file_id");
        $keys = array_keys($request);

        // Not all parameters sent
        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308, "Missing file id number."); 
        }

        $fileId = $request['file_id'];

        $file = new FilesObj($fileId);
        return print json_encode(Array("file_path"=>OCR_FILE_OUT."API\\", "file_name"=>$file->filename));
    }
    
    /**
     * Remove File
     * 
     * Remove file from the "scanout" folder.
     * 
     * @param   integer     file_id     unique id of file to delete from out folder
     * @return  json        result as failure or success
     */
    public function actionRemoveFile()
    {
        $rest = new RestServer();
        $request = RestUtils::processRequest();
        $required = array("file_id");
        $keys = array_keys($request);

        # Not all parameters sent
        if(count(array_intersect($required, $keys)) != count($required)) {
            return RestUtils::sendResponse(308, "Missing file id number."); 
        }

        $fileId = $request['file_id'];

        $file = new FilesObj($fileId);
        $file->username = "API";
        $deleted = $file->delete();
        return print json_encode(Array("deleted"=>$deleted));
    }
}
