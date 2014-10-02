<?php
/**
 * Mail Class, creates a mailing class for emailing users through SMTP on ASSETT servers.
 * 
 * Sets up magic for emailing users. Typically we could just use the PHPMailer class, however, we
 * have several parameters which are common across all platforms. Such as host, SMTP, passwords, and Authentication.
 * The idea is to symlink one class across all custom apps for simple email functionality.
 * 
 * @author      Ryan Carney-Mogan
 * @category    Core_Classes
 * @version     1.0.1
 * @copyright   Copyright (c) 2013 University of Colorado Boulder (http://colorado.edu)
 * 
 */

class Mail
{   
    # Sets whether the mail class actually got loaded and params set
    public $mailer              = null;
    public $mailer_loaded       = false;
    
    # Configuration settings, internal only
    private $MAILER_issmtp      = true;                               # Set mailer to SMTP 
    private $MAILER_auth        = true;                               # Enable SMTP authentication
    private $MAILER_host        = "assett.colorado.edu";              # SMTP server
    private $MAILER_port        = 25;                                 # SMTP port
    private $MAILER_username    = "mailer@assett.colorado.edu";       # SMTP username
    private $MAILER_password    = "tartarizes transistorization";     # SMTP password
    private $MAILER_secure      = "ssl";                              # SMTP account as SSL
    private $MAILER_debug       = 1;                                  # Enables debug information (0=no messages, 1=errors and messages, 2=messages only)
    
    /**
     * Constructor sets up (@var $mailer)
     */
    public function __construct() {
        
        # Set the timezone you goon
        date_default_timezone_set('America/Denver');
        
        # Require the PHPMailer class
        require_once('/mailer/class.phpmailer.php');
        
        # Create a new mailer and initialize parameters
        $this->mailer             = new PHPMailer();
        $this->mailer_init();
    }
    
    /**
     * Mailer Initialization
     *
     * Function to load the mailer parameters
     */
    public function mailer_init() {
        if($this->MAILER_issmtp) {
            $this->mailer->IsSMTP();
        }
        $this->mailer->SMTPDebug    = $this->MAILER_debug;
        $this->mailer->SMTPAuth     = $this->MAILER_auth;
        $this->mailer->Host         = $this->MAILER_host;
        $this->mailer->Port         = $this->MAILER_port;
        $this->mailer->Username     = $this->MAILER_username;
        $this->mailer->Password     = $this->MAILER_password;
        $this->mailer->SMTPSecure   = $this->MAILER_secure;
        
        # Set the mailer as being loaded and configured
        $this->mailer_loaded        = true;
    }
    
    /**
     * Send Mail
     * 
     * Function to send mail. This is the actual meat of the class.
     * 
     * @param (array,string)    $from       Array contains both name and email, String is just email
     * @param (array)           $to         Array of all emails to send to
     * @param (string)          $subject    Subject of the email
     * @param (string)          $text       Text of the email
     * @return (boolean)
     */
    public function send_mail($from,$to,$subject,$text) {

        $mail = $this->mailer;

        # Setup "From" portion of email
        if(is_array($from)) {
            $from_email = array_shift(array_values($from));
            $from_name  = array_shift(array_keys($from));
            $mail->SetFrom($from_email,$from_name);
            $mail->AddReplyTo($from_email,$from_name);
        } else {
            $mail->SetFrom($from);
            $mail->AddReplyTo($from);
        }
        
        # Subject and Body of email
        $mail->Subject    = $subject;
        $mail->Body       = $text;

        # Setup "To" portion of email
        if(is_array($to)) {
            foreach($to as $address) {
                $mail->AddAddress($address);
            }
        } else if(is_string($to)) {
            $mail->AddAddress($to);
        }
        
        # Send or save error
        if(!$mail->Send()) {
            $this->error = $mail->ErrorInfo;
            return false;
        } else {
            $this->error = "";
            return true;
        }
    }

}