<?php
/**
 * AD Authentication Class, connects to the Active Directory to authenticate/look-up users.
 * 
 * The purpose of this class is mainly authentication but it has additional tools to look-up user information.
 * 
 * 
 * @author      Ryan Carney-Mogan
 * @category    Core_Classes
 * @version     1.0.1
 * @copyright   Copyright (c) 2013 University of Colorado Boulder (http://colorado.edu)
 * 
 */
 
class ADAuth {
	
	public $ldap_connected			= false;
	public $ldap_authenticated		= false;
	private $ldap_conn				= null;
	private $ldap_ad_host 			= 'ldap://dc11.ad.colorado.edu/';
	private $ldap_directory_host 	= 'ldap://directory.colorado.edu/';
	private $ldap_type 				= "";
	private $ldap_port 				= 636;
	private $ldap_user_prefix 		= 'AD\\';
	
	/**
	 *  Constructor sets up connection type and initializes connection
	 */
	public function __construct($type="directory") 
	{
		$this->ldap_type = $type;
		$this->connect();
	}
	
    /**
     * LDAP Connect
     * 
     * Function to connect to the Active Directory.
     * 
     * There are two connection types, the Active Directory Controller and the Directory.
     * Both house separate pieces of information about users. ADC has more group and technical
     * information about users. Directory has more public facing information, such as names, emails, and addresses.
     * Both can search CU's Identikey's to find users, but only the ADC serves up the identikey's in their
     * info.
     * 
     */
	public function connect()
	{
		if($this->ldap_type=="directory") {
			$this->ldap_conn = @ldap_connect( $this->ldap_directory_host, $this->ldap_port );
		} else {
			$this->ldap_conn = @ldap_connect( $this->ldap_ad_host, $this->ldap_port );
		}
		if ( !$this->ldap_conn ) {
			echo "could not connect.";
			return;
		}
		ldap_set_option( $this->ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3 );  //Set the LDAP Protocol used by your AD service
		ldap_set_option( $this->ldap_conn, LDAP_OPT_REFERRALS, 0 ); // Necessary to do anything in CU's directory
        
		$this->ldap_connected = true;
	}
	
    /**
     * Change Controller
     * 
     * Switch controllers from ADC <-> Directory
     * 
     * @param   (string)    $controller     Which controller to switch to.
     */
	public function change_controller($controller)
	{
		$this->ldap_type = $controller;
		$this->close_connection();
		$this->connect();
	}
	
    /**
     * Close Connection
     * 
     * Closes the AD connection
     */
	public function close_connection()
	{
		if($this->ldap_connected) {
			ldap_close($this->ldap_conn);
			$this->ldap_connected = false;
			$this->ldap_authenticated = false;
		}
	}
	
    /**
     * Bind User
     * 
     * Used to bind connection with the AD. The binding is required for looking up users
     * in the ADC.
     * 
     * @param   (string)    $username       The username to bind
     * @param   (string)    $password       The password to bind
     */
	public function bind_user($username,$password)
	{
	    # Set the local user account to the binding username
		$this->username = $username;
        
		# Bind the user credentials to the connection (this checks to see if the user crendentials are valid or not)
		$authentication_success = @ldap_bind( $this->ldap_conn, $this->ldap_user_prefix . $username, $password );
		$this->ldap_authenticated = $authentication_success;
	}

    /**
     * Bind Anonymously
     * 
     * Used to create ldap connections without username and password. Only usable for Directory
     * connections.
     */
	public function bind_anon()
	{
		# Bind the user credentials to the connection (this checks to see if the user crendentials are valid or not)
		$authentication_success = @ldap_bind( $this->ldap_conn );
		$this->ldap_authenticated = $authentication_success;
	}
	
    /**
     * Authenticate
     * 
     * Authenticate users by binding their accounts with the AD and checking to see if it was
     * successful. Returns true/false if authentication was successful.
     * 
     * @param   (string)    $username   The username to authenticate
     * @param   (string)    $password   The password to authenticate
     * @return  (boolean)
     */
	public function authenticate($username,$password)
	{
		// Reset the authentication flag
		$this->ldap_authenticated = false;
		// Authenticate
		$this->bind_user($username,$password);
		// Return status
		return $this->ldap_authenticated;
	}

    /**
     * Lookup User by Username
     * 
     * Look up user by their usernames. Works for both ADC and Directory connections.
     * 
     * @param   (string)    $username   (Optional) Username to lookup
     * @return  (array,boolean)
     */
	public function lookup_user($username="")
	{
	    # If parameter and local usernames are unset, return
		if($username=="" and (!isset($this->username) or $this->username == "")) {
			return false;
		} else if($username=="") {
			$username = $this->username;
		}
		
		if($this->ldap_type == "directory") {
			$results = ldap_search( $this->ldap_conn, 'OU=people,DC=colorado,DC=edu', "(uid=$username)" );
		} else {
			$results = ldap_search( $this->ldap_conn, 'DC=ad,DC=colorado,DC=edu', "(CN=$username)" );
		}
		
		$info = ldap_get_entries( $this->ldap_conn, $results );
		return $info;
	}

    /**
     * Lookup User by Name
     * 
     * Look up a user by their name. Can be first or last name (though looking up users by first name
     * will most likely have limited results depending on how many hits). Works for both ADC 
     * and Directory connections.
     * 
     * @param   (string)    $name   The name to lookup
     * @return  (array,boolean)
     */
	public function lookup_user_byname($name)
	{
		if($this->ldap_type == "adcontroller") {
			$results = @ldap_search( $this->ldap_conn, 'DC=ad,DC=colorado,DC=edu', "(displayname=$name)");
		} else {
			$results = @ldap_search( $this->ldap_conn, 'OU=people,DC=colorado,DC=edu', "(cn=$name)" );
		}
		
		if(!$results) {
			return false;
		}
		
		$info = ldap_get_entries( $this->ldap_conn, $results );
		return $info;
	}
	
	/**
     * Lookup User By ID
     * 
     * Looks up user by their uuid which is exclusively found in the ADC. Requires authentication.
     * 
     * @param   (numeric)   $uuid   The Unique User Identification number
     * @return  (array,boolean)
     */
	public function lookup_user_byid($uuid)
	{
		if($this->ldap_type != "directory" or $uuid == "") {
			return false;
		}
		
		$results = @ldap_search( $this->ldap_conn, 'OU=people,DC=colorado,DC=edu', "(cuedupersonuuid=$uuid)" );
		if(!$results) {
			return false;
		}
		
		$info = ldap_get_entries( $this->ldap_conn, $results );
		return $info;
	}
	
    /**
     * Get Memberships
     * 
     * Get the groups the user belongs to. Requires authenticated users since groups are
     * managed by the ADC.
     * 
     * @return  (array,boolean)
     */
	public function get_memberships()
	{
		if(!isset($this->userinfo)) {
			$this->userinfo = $this->lookup_user();
		}
		if(empty($this->userinfo)) {
			return false;
		}
		$memberof = $this->userinfo[0]["memberof"];
		
		$groups = array();
		for( $a=0; $a<$memberof["count"]; $a++) {
			list($membership,$ou) 	= explode(",",$memberof[$a],3);
			$membership 			= str_replace("CN=","",$membership);
			$ou 					= str_replace("OU=","",$ou);
			$groups[] 				= array("cn"=>$membership,"ou"=>$ou);
		}
		return $groups;
	}

    /**
     * Is Member
     * 
     * Compares user's groups with @param $group and is case insensative.
     * 
     * @param   (string)    $group  See if local user is member of group
     * @return  (boolean)
     */
	public function is_member($group) 
	{
		$membership_groups = $this->get_memberships();
		foreach($membership_groups as $membership_group) {
			if(strtolower($membership_group["cn"])==strtolower($group)) {
			    return true;
            }
		}
		return false;
	}

}
?>