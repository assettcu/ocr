<?php
header('Access-Control-Allow-Origin: *');

require "BaseController.php";

class APIController extends BaseController
{
    /**
     * Upload File
     * 
     * @todo create function
     */
    public function actionUploadFile()
    {
        # Look to AjaxController for similar functionality
        # Use Rest API for processing passed in parameters and returning statuses
    }
    
    /**
     * File Status
     * 
     * Ping the service for the status on a file. The file may be:
     * Queued, Processing, Completed, Downloaded, Expired, or Failed
     * 
     * @todo create function
     */
    public function actionFileStatus()
    {
        
    }
    
    /**
     * Download File
     * 
     * Force download a file.
     * 
     * @todo create function
     */
    public function actionDownloadFile()
    {
        
    }
    
    /**
     * Remove File
     * 
     * Remove file from the "scanout" folder.
     * 
     * todo: define function
     */
    public function actionRemoveFile()
    {
        
    }
}
