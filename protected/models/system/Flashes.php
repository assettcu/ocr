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
 * @version     1.0.1
 * @copyright   Copyright (c) 2013 University of Colorado Boulder (http://colorado.edu)
 * 
 */
class Flashes {
    
    public $buffer = "";
    
    /*
     * Constructor sets up the local flashes variable
     */
    public function __construct() 
    {
        $this->flashes = Yii::app()->user->getFlashes();
    }
    
    /**
     * Render
     * 
     * Renders the HTML output for each of the flash messages.
     * 
     * @param   (boolean)   $closeicon  Whether to include a close icon or not
     */
    public function render($closeicon=true) 
    {
        ob_start();
        $flashes = $this->flashes;
        if(!empty($flashes)) {
            foreach($flashes as $key=>$message) {
                $icon = $this->get_icon($key);
                $iconclose = ($closeicon===true) ? $this->get_closingicon() : "";
                switch($key) {
                    case "success": echo '<div class="ui-state-highlight flash ui-corner-all">'.$icon.$iconclose.$message.'</div>'; break;
                    case "error": echo '<div class="ui-state-error flash ui-corner-all">'.$icon.$iconclose.$message.'</div>'; break;
                    case "normal": echo '<div class="ui-widget-content flash ui-corner-all">'.$icon.$iconclose.$message.'</div>'; break;
                    case "warning": echo '<div class="ui-state-highlight flash ui-corner-all">'.$icon.$iconclose.$message.'</div>'; break;
                    default: echo '<div class="ui-state-active flash ui-corner-all">'.$icon.$iconclose.$message.'</div>'; break;
                }
                
            }
        }
        # Script to hide flash if the "close" icon is clicked
        if($closeicon===true) {
            echo '<script>jQuery(document).ready(function(){$("div.flash div.flash-close-icon").click(function(){$(this).parent().hide("blind");})});</script>';
        }
        $this->buffer .= ob_get_contents();
        ob_end_clean();
        
        echo $this->buffer;
    }
    
    /**
     * Get Icon
     * 
     * Returns the HTML for the icon that depends on the status of the message ("error","success","warning").
     * 
     * @param   (string)    $type   Type of message icon needed ("error","success","warning")
     * @return  (string)
     */
    public function get_icon($type)
    {
        $icon = "<div class='message-icon'>";
        switch($type) {
            case "success":     $icon .= StdLib::load_image("check-64","16px");         break;
            case "error":       $icon .= StdLib::load_image("attention","16px");        break;
            case "warning":     $icon .= StdLib::load_image("flag_mark_red","16px");    break;  
            default:            $icon .= StdLib::load_image("flag_mark_blue","16px");   break;
        }
        if($icon!="") {
            $icon .= "</div>";
        }
        
        return $icon;
    }
    
    public function get_closingicon()
    {
        $icon =  "<div class='flash-close-icon' title='Close this flash'>".StdLib::load_image("close","12px")."</div>";
        return $icon;
    }
    
    public function jscript_message($message,$type,$success="",$error="") {
        echo "
            $.ajax({
                type:       'POST',
                url:        '".Yii::app()->createUrl('site/_CreateMessage')."',
                data:       'message=".urlencode($message)."&type=".urlencode($type)."',
                success:    function(data) {
                    $success
                },
                error:      function(data) {
                    $error
                }
            });
        ";
    }
}
