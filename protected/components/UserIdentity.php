<?php

/**
 * UserIdentity represents the data needed to identity a user.
 * It contains the authentication method that checks if the provided
 * data can identity the user.
 */
class UserIdentity extends CUserIdentity
{
	/**
	 * Authenticates a user.
	 * The example implementation makes sure if the username and password
	 * are both 'demo'.
	 * In practical applications, this should be changed to authenticate
	 * against some persistent user identity storage (e.g. database).
	 * @return boolean whether authentication succeeds.
	 */
	public function authenticate()
	{
		$this->errorCode=self::ERROR_NONE;

		$authenticated = false;
		$username = $this->username;
		$password = $this->password;

		$adauth = new ADAuth("adcontroller");
        
		if($adauth->authenticate($username, $password)){
			// Authenticated
			$user = new UserObj($username);
            if(!$user->loaded) {
                $info = $adauth->lookup_user();
                $user->email = @$info[0]["mail"][0];
                if($user->email == "") {
                    $user->email = $username."@colorado.edu";
                }
                if(!$user->save()) {
                    $this->errorCode = 2;
                } else {
                    $user->login();
                    $user->doExpireDocs();
                }
            }
		} else {
			$this->errorCode=1;
		}
		return !$this->errorCode;
	}
}