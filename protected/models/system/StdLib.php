<?php
/**
 * Standard Library, a static-method-only class for common functions.
 * 
 * The purpose of this class was to extend some of the basic, common functions found across the sites and collaborate
 * them into one class. They can then be accessed using the scope resolution "::" - eg. StdLib::function_name();
 * 
 * There are two functions specific to where this application is hosted on ASSETT servers.
 * @func make_path_web() and @func make_path_local() which convert paths from one form to another.
 * 
 * @author      Ryan Carney-Mogan
 * @category    Core_Classes
 * @version     1.0.7
 * @copyright   Copyright (c) 2013 University of Colorado Boulder (http://colorado.edu)
 * 
 */
 
class StdLib
{
    # Public image cache
    public static $image_cache = array();
    
    /**
     * Format Date
     * 
     * A small function to format dates according to strings or in a custom format.
     * 
     * @param   (string)  $date     Date in text form or numeric form
     * @param   (string)  $format   The format in which to format the date
     * @return  (string)
     */
    public static function format_date($date,$format)
    {
        if(!is_numeric($date))
          $date = strtotime($date);
        if($format=="nice")
          $format = "d M Y H:i a";
        if($format=="short-normal")
          $format = "M jS, Y H:i a";
        if($format=="normal")
          $format = "F jS, Y H:i a";
        if($format=="normal-notime")
          $format = "F jS, Y";
        if($format=="nice-notime")
          $format = "d M Y";
        
        return date($format,$date);
    }
    
    public static function time_ago($past_time,$round=FALSE)
    {
        $time = strtotime($past_time);
        
        $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
        $lengths = array("60","60","24","7","4.35","12","10");
        
        $now = time();
        
        $difference     = $now - $time;
        $tense         = "ago";
        
        for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
            $difference /= $lengths[$j];
        }
        
        $difference = round($difference);
        
        if($difference != 1) {
            $periods[$j].= "s";
        }
        
        return "$difference $periods[$j] ago";
    }
    
    /**
     * Get Library Path
     * 
     * Function to get the path to the library. Library contains common javascript, prototype, images, css, etc.
     * 
     * @param   (boolean)   $local  Determines whether to get the local path or the URL path.
     * @return  (string)
     */
    public static function get_library_path($local=false) {
        return ($local === true) ? LOCAL_IMAGE_LIBRARY : WEB_IMAGE_LIBRARY;
    }
    
    /**
     * Make Path Web
     * 
     * Function to convert a path into it's web equivalent. 
     * Specific to this application's location on ASSETT servers.
     * 
     * @param   (string)    $path   Path to convert.
     * @return  (string)            Converted path.
     */
    public static function make_path_web($path) {
        $path = str_replace(LOCAL_LIBRARY_PATH,WEB_LIBRARY_PATH,$path);
        $path = str_replace("\\","/",$path);
        return $path;
    }
    
    /**
     * Make Path Local
     * 
     * Function to convert a path into it's local equivalent. 
     * Specific to this application's location on ASSETT servers.
     * 
     * @param   (string)    $path   Path to convert.
     * @return  (string)            Converted path.
     */
    public static function make_path_local($path) {
        $path = str_replace(WEB_LIBRARY_PATH,LOCAL_LIBRARY_PATH,$path);
        return $path;
    }
    
    /**
     * Load Image
     * 
     * Function to dynamically find and load images. It searches the library for any images that match a certain name.
     * If there is more than one image with the same name it will grab the first one it finds.
     * 
     * @param   (string)    $image  Image name to load
     * @param   (string)    $width  Width of image
     * @param   (string)    $height Height of image
     * @param   (string)    $type   What type of library image to look for
     * @return  (string)
     */
    public static function load_image($image,$width="auto",$height="auto")
    {
        $image_parts = explode(":",$image);
        $image = array_pop($image_parts);
        $subdir = implode("/",$image_parts);
        
        # Load cached version of image
        $websrc = self::get_cache($image);
        $websrc = self::make_path_web($websrc);
        
        # If image is not cached, find it then cache it
        if($websrc === false or $websrc == "") {
            $websrc = self::load_image_source($image,$subdir);
            $websrc = self::make_path_web($websrc);
            StdLib::$image_cache[$image] = $websrc;
        }
        ob_start();
        ?><img src="<?=$websrc?>" width="<?=$width?>" height="<?=$height?>" /><?php
        $contents = ob_get_contents();
        ob_end_clean();

        return $contents;
    }
    
    /**
     * Get Cache
     * 
     * Returns a stored image in the Cookie headers. Used to skip recursively searching for images in
     * the library which can take months (execution-time speaking).
     * 
     * @param   (string)    $image      The name of the image to lookup
     * @return  (string,boolean)        Returns the path of the image or false if it can't find it
     */
    public static function get_cache($image) {
        if(isset(StdLib::$image_cache[$image])) {
            return StdLib::$image_cache[$image];
        }
        return false;
    }
    
    /**
     * Load Image Source
     * 
     * Function sets up and calls a recursive function to find an image in the image directorys. Returns a web
     * link to the image.
     * 
     * @param   (string)    $image      Image name to find.
     * @param   (string)    $subdir     Subdirectory to start looking for matching image.
     * @return  (string,boolean)        Returns web based path if found, false if not found
     */
    public static function load_image_source($image,$subdir="")
    {
        # Get the URL target directory
        $target_dir = self::get_library_path(true).$subdir;
        
        # Find the image in the target directory
        $image = self::find_image_path($image, $target_dir);
        
        # Return the web path of the image
        return self::make_path_web($image);
    }

    /**
     * Find Image Path
     * 
     * A function that recursively checks directories to find an image based on the name.
     * If the image being searched has no extension it will look for any matches to the image name.
     * 
     * @param   (string)    $image      Image name to search for
     * @param   (string)    $path       Directory path to search for image
     * @return  (string,boolean)        Returns path of image if found, false if not found
     */
    public static function find_image_path($image,$path="",$depth=0)
    {
        # If no path supplied, get current working directory
        if($path == "") {
            $path = LOCAL_IMAGE_LIBRARY;
        }
        
        # Only go three levels deep
        if($depth==3) { return false; }
        
        # If it's a directory then scan its files
        if(is_dir($path)) {
            $files = scandir($path);
            foreach($files as $file) {
                # Skip the . and .. folders
                if($file == ".." or $file == ".") continue;
                
                if(is_dir($path.$file)) {
                    $return = self::find_image_path($image, $path.$file."/",$depth+1);
                    if($return !== false) {
                        return $return;
                    }
                }
                if($file == $image and is_file($path.$file)) {
                    return $path.$file;
                } else {
                    # Strip extension off file (extension independent)
                    $fileparts = explode(".",$file);
                    array_pop($fileparts);
                    $file_trim = implode(".",$fileparts);
                    
                    # If the file matches the stripped filename, return
                    if($file_trim == $image and is_file($path.$file)) {
                        return $path.$file;
                    }
                }
            }
        }
        # Image not found in this path
        return false;
    }

    /**
     * Make Button
     * 
     * Function to create a button with simple features. Icons can be added dynamically.
     * 
     * @param   (string)    $text       The text the button will have.
     * @param   (string)    $id         The ID of the button (used for javascript functionality)
     * @param   (string)    $icon       Name of the icon to append (NULL by default)
     * @param   (string)    $classes    Classes to add to the button for additional CSS-ing (Blank by default)
     * @return  (string)                Returns the rendered output as text
     */
    public static function make_button($text,$id,$icon=null,$classes="")
    {
        ob_start();
        ?>
        <button id="<?php echo $id; ?>" classes="<?php echo $classes; ?>">
            <?php if(!is_null($icon)) : ?>
            <span class="message-icon"><?php echo StdLib::load_image($icon,"16px"); ?></span> 
            <?php endif; ?>
            <?php echo $text; ?>
        </button>
        <?php
        $button = ob_get_contents();
        ob_end_clean();
        
        return $button;
    }
     
     
    /**
     * Vdump
     * 
     * Simple function that outputs a variable dump and quits the program.
     * Note that ajax calls will only return on exit.
     * 
     */
    public static function vdump($var,$pre=true)
    {
        if($pre) echo "<pre>";
        var_dump($var);
        exit;
    }
    
    
    
    /**
     * External Call
     */
    public static function external_call($url, $data = array()) 
    {
        $restreq = new RestRequest($url,"post");
        $restreq->buildPostBody($data);
        $restreq->execute();
        return json_decode($restreq->getResponseBody(), true);
    }
    
    /**
     * Curl Post Call
     */
    public static function post($url, $data = array()) 
    {
        $ch = curl_init();
        
        $fields_string = array();
        foreach($data as $key=>$value) {
             $fields_string[] = $key.'='.json_encode($value); 
        }
        $fields_string = implode("&",$fields_string);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($data));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $result = curl_exec($ch) or die(curl_error($ch));
        curl_close($ch);
        
        return $result;
    }
    
    /**
     * Programmer Only
     */
    public static function is_programmer()
    {
        return ($_SERVER["REMOTE_ADDR"] == "128.138.182.157");
    }
    
    /**
     * On Campus
     * 
     * Checks to see if user is on campus or not
     */
    public static function on_campus()
    {
        $requestip = $_SERVER["REMOTE_ADDR"];
        $return = StdLib::external_call("//compass.colorado.edu/resources/api/iscampusnetwork",array("ip"=>$requestip));
        return $return["connection"];
    }
    
    /**
     * Set Debug State
     * 
     * Sets the debug state for a specific page or the entire application.
     * Modifies the ini file to output specific error messages depending on the state.
     */
    public static function set_debug_state($state=0) 
    {
        switch(strtoupper($state)) {
            # Production standards
            case "PRODUCTION":
            case "PROD":
            case "0":
                set_time_limit(30);
                ini_set("display_errors",0);
                error_reporting("E_ALL & ~E_DEPRECATED");
            break;
            
            # Development standards
            case "DEVELOPMENT":
            case "DEV":
            case "1":
                set_time_limit(6000);
                ini_set("display_errors",1);
                error_reporting("E_ALL | E_STRICT");// Active assert and make it quiet
                
                assert_options(ASSERT_ACTIVE, 1);
                assert_options(ASSERT_WARNING, 0);
                assert_options(ASSERT_QUIET_EVAL, 1);
                
                // Create a handler function
                function my_assert_handler($file, $line, $code,$desc = "")
                {
                    echo "
                    <hr />
                        <strong>Assertion Failed</strong><br/>
                        <span style='color:#0099cc;'>File </span>'$file'<br />
                        <span style='color:#009900;'>Line </span>'$line'<br />
                        <span style='color:#009900;'>Description </span>'$desc'<br />
                    <hr />
                    ";
                    die();
                }
                
                // Set up the callback
                assert_options(ASSERT_CALLBACK, 'my_assert_handler');
            break;
            
            # Default to Production values
            default:
                set_time_limit(30);
                ini_set("display_errors",0);
                error_reporting("E_ALL & ~E_DEPRECATED");
            break;
        }    
    }

    public static function pre() {
        echo "<pre>";
    }
    
    public static function Functions() {
        require_once ROOT."/protected/models/Functions.php";
    }
}
