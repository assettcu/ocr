<?php
/**
 * Factory Class
 *
 * This class allows abstraction of the SQL database into objects with common procedures.
 * 
 * The greatest advantage of this class is the ability to save, load, delete rows from
 * the Database as if they were loaded objects. There are procedures for pre- and post-
 * save, load, delete for additional functionality. Tables must have a unique identifier
 * and no more than one.
 * 
 * Objects that extend this class call the parent constructor with the uniqueid, table,
 * and the row id to load. This class also takes care of whether to insert or update rows.
 * 
 * @author      Ryan Carney-Mogan
 * @category    Core_Classes
 * @version     1.0.1
 * @copyright   Copyright (c) 2013 University of Colorado Boulder (http://colorado.edu)
 */

class FactoryObj
{
    # Private local variables
	private $error_msg = "";       # Error message for when procedures fail
    
    # Public local variables
	public $loaded = false;        # Whether the object was loaded from the DB

	/**
     * Constructor initializes unique table, unique id, and whether an object
     * should be loaded from the table.
     * 
     * @param   (string)            $uniqueid   Name of field in the table that is the Primary Key
     * @param   (string)            $table      Name of the table
     * @param   (string/integer)    $id         (Optional) Loads a row with this ID from table
     */
	public function __construct($uniqueid,$table,$id=null)
	{
		$this->uniqueid = $uniqueid;
		$this->table = $table;
		$this->{$this->uniqueid} = $id;
		$this->load();
	}

    /**
     * Is Valid ID
     * 
     * Checks to see if local ID is a valid ID.
     * 
     * @return (boolean)
     */
	public function is_valid_id()
	{
		return (isset($this->{$this->uniqueid}) and !is_null($this->{$this->uniqueid}) and $this->{$this->uniqueid}!="" and (!is_numeric($this->{$this->uniqueid}) or $this->{$this->uniqueid}>0));
	}

    /**
     * Pre-Save
     * 
     * Procedure to run before saving procedure.
     */
	protected function pre_save()
	{
		/** This function is meant to be overloaded **/
	}

    /**
     * Post-Save
     * 
     * Procedure to run after saving procedure.
     */
	protected function post_save()
	{
		/** This function is meant to be overloaded **/
	}

    /**
     * Pre-Load
     * 
     * Procedure to run before loading procedure.
     */
	protected function pre_load()
	{
		/** This function is meant to be overloaded **/
	}

    /**
     * Post-Load
     * 
     * Procedure to run after loading procedure.
     */
	protected function post_load()
	{
		/** This function is meant to be overloaded **/
	}

    /**
     * Pre-Delete
     * 
     * Procedure to run before deleting procedure.
     */
	public function pre_delete()
	{
		/** This function is meant to be overloaded **/
	}
	
    /**
     * Load
     * 
     * Procedure to load an object from the DB based on local table.
     * Local variables are loaded dynamically.
     * 
     * @return (boolean)
     */
	public function load()
	{
		# Run pre-load procedure before continuing
		$this->pre_load();
		
		# Load up DB connection from Yii init
		$conn = Yii::app()->db;
		
        # If the ID is invalid then return not loaded (skipping post-load procedure)
		if(!$this->is_valid_id()) return false;
        
        # Query sets up lookup based on uniqueid passed in the constructor
		$query = "
			SELECT		*
			FROM		{{".$this->table."}}
			WHERE		".$this->uniqueid." = :".$this->uniqueid.";
		";
        # Create command and bind local uniqueid (SQL injection prevention)
		$command = $conn->createCommand($query);
		$command->bindParam(":".$this->uniqueid,$this->{$this->uniqueid});

        # Execute query and return false if row could not be found
		$result = $command->queryRow();
		if(!$result or count($result)==0) return false;

		# Loop through each field and load it into the contact object
		foreach($result as $index=>$val) {
			$this->$index = $val;
		}
        
        # Set class as loaded
		$this->loaded = true;
        
        # Run post-load procedure before exiting
		$this->post_load();
        
        # Return success
		return true;
	}

    /**
     * Save
     * 
     * Procedure to save an object to the DB based on local table.
     * Local variables are found then checked against the table's fields.
     * Matching up local variables with the table's fields we can ignore variables that
     * do not match up.
     * 
     * @return (boolean)
     */
	public function save()
	{
	    # Run the pre-save procedure before anything else
		$this->pre_save();

        # Grab the local variables to check against the unique table
		$vars = get_object_vars($this);

		# Remove the variables which are not database vars
		foreach($vars as $var=>$val) {
		    # Check to see if variable is a column in the unique table
            if(!$this->is_column($var)) {
                unset($vars[$var]);
            }
		}

        # Use Run Check to check local variables for special conditions before saving.
        # This is mainly useful for form checking (eg. phone numbers must match ###-###-#### before saving)
		if($this->run_check())
		{
		    # Begin a transaction (Check Yii documentation for more: @link http://www.yiiframework.com/doc/api/1.1/CDbTransaction)
			$transaction = Yii::app()->db->beginTransaction();
            
			# Check to see if ID is valid and the row exists in the database
			# This will determine whether we need to run an "UPDATE" query or an "INSERT" query
			if($this->is_valid_id() and $this->exists())
			{
				$set_fields = "";

                # Set up query text for each local variable found as a column in the table
				foreach($vars as $var=>$val) {
                    $set_fields .= "{$var} = :{$var},";
				}

                # Remove extra comma
				$set_fields = substr($set_fields,0,-1);
                
                # Set up UPDATE query
				$query = "
					UPDATE		{{".$this->table."}}
					SET			{$set_fields}
					WHERE		`".$this->uniqueid."` = :".$this->uniqueid.";
				";
                
                # Create command and bind parameters (SQL injection safe)
				$command = Yii::app()->db->createCommand($query);
				$command->bindParam(":".$this->uniqueid,$this->uniqueid);

			}
            # We need to run an INSERT since we couldn't find a row with this unique id value in the table
			else
			{
			    # Init temp variables
				$field_names = "";
				$field_values = "";
                
                # Iterate over local vars to include in INSERT query
				foreach($vars as $var=>$val)
				{
				    # Skip unique ID and empty vals
					if($var==$this->uniqueid and (is_null($val) or empty($val))) {
					    continue;
                    }
					$field_names .= "{$var},";
					$field_values .= ":{$var},";
				}
                
                # Remove extra commas
				$field_names = substr($field_names,0,-1);
				$field_values = substr($field_values,0,-1);
                
                # Set up INSERT query
				$query = "
					INSERT INTO		{{".$this->table."}}
					(	{$field_names} 	)
					VALUES
					(	{$field_values}	);
				";
                
                # Set up command
				$command = Yii::app()->db->createCommand($query);
			}
			# Loop through and bind the parameters
			foreach($vars as $var=>$val)
			{
				if($var==$this->uniqueid and (is_null($val) or empty($val))) continue;
				$command->bindParam(":{$var}",$this->$var);
			}
            
            # Try and execute query
			try
			{
				$command->execute();
				if(!$this->is_valid_id()) $this->{$this->uniqueid} = Yii::app()->db->getLastInsertId();
				$transaction->commit();
			}
            # Catch errors and call local set_error to raise flag and set error
			catch(Exception $e)
			{
				$transaction->rollBack();
				$this->set_error($e);
				return false;
			}
            
            # Run post-save procedure before exiting
			$this->post_save();
            
            # Return success
			return true;
		}
		# Failed Run Check, return failure 
		else {
		    return false;
		}
	}

	/**
     * Run Check
     * 
     * This function should be overloaded. It runs special checks against local variables
     * to make sure they meet certain criteria. It's really good for checking local variables against
     * table column sizes. 
     * 
     * For instance, a column "firstname" may only have a varchar of 15 in the
     * table. You can use Run Check to test if the local variable $firstname is greater than 15 characters.
     * If so you can return false and prevent the class from saving the truncated data to the database.
     * 
     * This functions works especially well while paired with set_error() since set_error() returns true
     * you can use an if-statement to check a condition and return !set_error() with a specific message
     * for why the function returns false.
     * 
     * @return  (boolean)
     */
	public function run_check()
	{
		return true;
	}

    /**
     * Exists
     * 
     * Checks to see if the row with the set unique id exists in the database. This is useful for
     * testing conditions as well as pre-load procedure stuff.
     * 
     * @return  (boolean)
     */
	public function exists()
	{
	    # Yii DB connection
		$conn = Yii::app()->db;
		
		# Setup the query for testing EXISTENCE
		$query = "
			SELECT		COUNT(*) as existing
			FROM		{{".$this->table."}}
			WHERE		".$this->uniqueid." = :".$this->uniqueid.";
		";
        
        # Create command and bind unique id (SQL injection safe)
		$command = $conn->createCommand($query);
		$command->bindParam(":".$this->uniqueid,$this->{$this->uniqueid});
        
        # Query scalar (should return count as an integer)
		$result = $command->queryScalar();
        
        # Return whether there were results or not from the DB
		return ((integer)$result!=0);
	}

    /**
     * Is Column
     * 
     * Well is it? PUNK??
     * Checks table if a variable is a column name. Used in conjunction with save() since it looks up
     * local variables and tests whether they belong to a table or not.
     * 
     * @param   (string)    $column     Possible column name to test
     * @return  (boolean)
     */
	public function is_column($column)
	{
		$conn = Yii::app()->db;
		$query = "
			SHOW COLUMNS FROM {{".$this->table."}};
		";
		$result = $conn->createCommand($query)->queryAll();
		if(!$result) return false;
		foreach($result as $col)
		{
			if($col["Field"]==$column) return true;
		}
		return false;
	}

    /**
     * Set Error
     * 
     * Set's local error message and returns true (useful for procedures that return right after setting message)
     * 
     * @param   (string)    $msg    Message to set
     * @return  (boolean)
     */
	public function set_error($msg)
	{
		$this->error_msg = $msg;
		return true;
	}

    /**
     * Delete
     * 
     * Deletes a row from the database corresponding to the local unique id variable. Since it returns
     * the status of the deletion there is no post-delete procedure.
     * 
     * @return (boolean)
     */
	public function delete()
	{
	    # Run the pre-delete procedure before anything else
		$this->pre_delete();

        # If not a valid id then there's no row to find to delete
		if(!$this->is_valid_id()) {
		    return false;
        }
        
        # Yii DB Connection
		$conn = Yii::app()->db;
        
        # Set up query to DELETE row
		$query = "
			DELETE FROM		{{".$this->table."}}
			WHERE		".$this->uniqueid." = :".$this->uniqueid.";
		";
        
        # Create command and bind unique id (SQL injection safe)
		$command = $conn->createCommand($query);
		$command->bindParam(":".$this->uniqueid,$this->{$this->uniqueid});
        
        # Return execution of query
		return $command->execute();
	}

    /**
     * Get Error
     * 
     * Returns the local error message.
     * 
     * @return (string)
     */
	public function get_error()
	{
		return $this->error_msg;
	}
}


?>