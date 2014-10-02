<?php
/**
 * Imager Class, sets up and renders images based on settings.
 * 
 * The purpose of this class is to create HTML images based on PHP inputs. It allows creating simple, straight-forward
 * function calls and parameter setting. It also lets applications crop images and create new versions of the images.
 * 
 * @author      Ryan Carney-Mogan
 * @category    Core_Classes
 * @version     1.0.1
 * @copyright   Copyright (c) 2013 University of Colorado Boulder (http://colorado.edu)
 * 
 */
 
class Imager
{
  
    public $loaded      = false;
    public $imagepath   = "";
    public $styles      = array();
    public $attributes  = array();
    
    /**
     * Constructor sets up the image for rendering if @param $image_src is not null.
     * 
     * @param   (string)    $image_src  (Optional) The local image path.
     */
    public function __construct($image=null)
    {
        if(is_null($image)) return;
        
        if(is_file($image)) $this->loaded = true;
        else return;
        
        $this->load_image_paths($image);
        $this->load_image_specs();
    }
  
    /**
     * Load Image Paths
     * 
     * This function sets up the image paths for both local and web use.
     * 
     * @param   (string)    $image_path     The image's path.
     * @return  (object)                    Returns this class for linked method calling.
     */
    public function load_image_paths($image_path)
    {
        $this->imagepath = $image_path;
        $this->imagehttp = str_replace(getcwd(),Yii::app()->baseUrl,$image_path);
        $this->imagehttp = str_replace("/","\\",$this->imagehttp);
        $this->imagehttp = str_replace("c:\\web\\assettdev.colorado.edu\\","//assettdev.colorado.edu/",$this->imagehttp);
        $this->imagehttp = str_replace("c:\\web\\compass.colorado.edu\\","//compass.colorado.edu/",$this->imagehttp);
        
        return $this;
    }

    /**
     * Load Image Specifications
     * 
     * This functions finds the image's specifications and loads them into the local variables.
     * 
     * @return  (object)                    Returns this class for linked method calling.
     */
    public function load_image_specs()
    {
        # Only if an image path exists
        if(!isset($this->imagepath)) return;
        
        # Get the image size specs
        list($imgwidth, $imgheight, $imgtype, $imgattr)= getimagesize($this->imagepath);
        
        # Localize image's specifications
        $this->width    = $imgwidth;
        $this->height   = $imgheight;
        $this->type     = $imgtype;
        $this->attr     = $imgattr;
        
        return $this;
    }
  
    /**
     * Add Attribute
     * 
     * Add an attibute to the local attributes table.
     * 
     * @return  (object)                    Returns this class for linked method calling.
     */
    public function add_attribute($attr,$val)
    {
        $this->attributes[$attr] = $val;
    }
  
    /**
     * Crop
     * 
     * Takes an image and crops it to a new image. The new cropped image is overwritten (for now).
     * 
     * @param   (string)    $width      Width of new crop image
     * @param   (string)    $height     Height of new crop image
     * @return  (object)                    Returns this class for linked method calling.
     */
    public function crop($width,$height="auto")
    {
        # Can't have both width and height set to auto
        if($width=="auto" and $height=="auto") return;
        
        # Keep original dimensions
        $original_width = $this->width;
        $original_height = $this->height;
        
        # Load image resource
        $image = imagecreatefromjpeg($this->imagepath);
        
        # If height is set to "auto", maintain aspect ratio and create new height based on width
        if($height=="auto") {
          $thumb_height = ($this->height * $width) / $this->height;
        }
        else {
          $thumb_height = $height;
        }
        
        # Initialize aspect ratios
        $original_aspect = $this->width / $this->height;
        $thumb_aspect = $thumb_width / $thumb_height;
        
        # Maintain aspect ratio
        if ( $original_aspect >= $thumb_aspect )
        {
           # If image is wider than thumbnail (in aspect ratio sense)
           $new_height = $thumb_height;
           $new_width = $width / ($height / $thumb_height);
        }
        else {
           # If the thumbnail is wider than the image
           $new_width = $thumb_width;
           $new_height = $height / ($width / $thumb_width);
        }
        
        $thumb = imagecreatetruecolor( $thumb_width, $thumb_height );
        
        # Resize and crop
        $hoz_center = 0 - ($new_width - $thumb_width) / 2;
        $ver_center = 0 - ($new_height - $thumb_height) / 2;
        imagecopyresampled( $thumb,         # The new cropped image
                            $image,         # The original image to crop    
                            $hoz_center,    # Center the image horizontally
                            $ver_center,    # Center the image vertically
                            0,              # Starting x coordinate
                            0,              # Starting y coordinate
                            $new_width,     # Cropped width
                            $new_height,    # Cropped height
                            $width,         # Width of original image
                            $height         # Height of original image
        );
        
        # Get filename and replace old image with new cropped image
        $filename = getcwd()."/images/thumbs/".$this->filename;
        imagejpeg($thumb, $filename, 80);
    }
  
    /**
     * Resize
     * 
     * Takes an image and resizes it depending on aspect ratios. Just changes the width/height local
     * variables.
     * 
     * @param   (string)    $width      Width of new crop image
     * @param   (string)    $height     Height of new crop image
     * @return  (object)                Returns this class for linked method calling.
     */
    public function resize($width,$height="auto")
    {
        if($width=="auto" and $height=="auto") return;
        
        $original_width = $this->width;
        $original_height = $this->height;
        
        if($width!="auto") {
            if(strstr($width,"%")) {
                $width = (float)str_replace("%","",$width);
                $width = $width / 100;
                $this->width = $original_width * $width;
                if($height=="auto") {
                    $this->height = $original_height * $width;
                }
            } 
            else {
                $this->width = $width;
            }
        }
        if($height!="auto") {
            if(strstr($height,"%")) {
                $height = (float)str_replace("%","",$height);
                $height = $height / 100;
                $this->height = $this->height * $height;
            } 
            else {
                $this->height = $height;
            }
        }
        else {
            $this->height = ($this->width * $original_height) / $original_width;
        }
    }
  
    /**
     * Render
     * 
     * Creates HTML output of image and its local variables. Renders output.
     * 
     */
    public function render()
    {
        # Start the output buffer and load image html
        ob_start();
        ?><img src=":imagesrc:" width=":width:" height=":height:" :attributes: :styles: /><?php
        $contents = ob_get_contents();
        ob_end_clean();
          
        # Replace all replaceables with local properties
        $contents = str_replace(":imagesrc:",$this->imagehttp,$contents);
        $contents = str_replace(":width:",$this->width,$contents);
        $contents = str_replace(":attributes:",$this->render_attributes(),$contents);
        $contents = str_replace(":height:",$this->height,$contents);
        $contents = str_replace(":styles:",$this->render_styles(),$contents);
        
        # Output rendering
        echo $contents;
    }
  
    /**
     * Render Styles
     * 
     * Creates HTML output of image styles.
     * 
     * @return (string)
     */
    public function render_styles()
    {
        $styles = "";
        if(empty($this->styles)) {
            return $styles;
        }
        $styles = "style=\"";
        foreach($this->styles as $attr=>$val) {
            $styles .= $attr.":".$val.";";
        }
        $styles .= "\"";
        return $styles;
    }
      
    /**
     * Render Attributes
     * 
     * Creates HTML output of image attributes.
     * 
     * @return (string)
     */
    public function render_attributes()
    {
        $attributes = "";
        if(empty($this->attributes)) {
            return $attributes;
        }
        foreach($this->attributes as $attr=>$val) {
            $attributes .= $attr."='".$val."' ";
        }
        return $attributes;
    }
  
}