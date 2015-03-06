<?php
/**
 * Flashes Class, uses the Yii framework's "user flash" to send messages across pages.
 * 
 * This simplifies the flash messaging by just rendering any messages in the flash queue. The queue
 * is a part of the Yii framework. It uses the Jquery UI CSS classes to display the messages. Further
 * CSS changes can be made in css/main.css using the class ".flash".
 * 
 * @author      Ryan Carney-Mogan
 * @category    Core_Classes
 * @version     2.0.1
 * @copyright   Copyright (c) 2013 University of Colorado Boulder (http://colorado.edu)
 * 
 */
class Flashes {
    
    /**
     * Render
     * 
     * Renders the HTML output for each of the flash messages.
     * 
     */
    public static function render() 
    {
        $buffer = "";
        ob_start();
        $flashes = Yii::app()->user->getFlashes();
        foreach($flashes as $key=>$message) {
            switch($key) {
                case "success":
                    $buffer .= '<div class="alert dismissible alert-success"><button type="button" class="close" data-dismiss="alert">×</button><p><span class="icon icon-checkmark"> </span> '.$message.'</p></div>';
                break;
                case "error":
                    $buffer .= '<div class="alert dismissible alert-danger"><button type="button" class="close" data-dismiss="alert">×</button><p><span class="icon icon-spam"> </span> '.$message.'</p></div>';
                break;
                case "warning":
                    $buffer .= '<div class="alert dismissible alert-warning"><button type="button" class="close" data-dismiss="alert">×</button><p><span class="icon icon-warning"> </span> '.$message.'</p></div>';
                break;
                case "info":
                default:
                    $buffer .= '<div class="alert dismissible alert-info"><button type="button" class="close" data-dismiss="alert">×</button><p><span class="icon icon-info"> </span> '.$message.'</p></div>';
                break;
            }
        }
        $buffer .= ob_get_contents();
        ob_end_clean();
        
        echo $buffer;
    }
    
    
    
    public static function create_flash($type,$messages="")
    {
        if(empty($messages)) {
            return;
        }
        if(is_string($messages)) {
           Yii::app()->user->setFlash($type,$messages);
        }
        else if(is_array($messages)) {
            foreach($messages as $key=>$message) {
                if(is_array($message)) {
                    $message = implode("<br/>",$message);
                }
                $message = (StdLib::is_programmer()) ? $key.": ".$message : $message;
                Yii::app()->user->setFlash($type,$message);
            }
        }
    }
}
