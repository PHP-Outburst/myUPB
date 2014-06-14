<?php
/**
 * 
 * The UPB_Registration class is apart of the UPB API and allows easy
 * and simple access to registering new users in the database.
 * 
 * @author Tim Hoeppner <timhoeppner@gmail.com> (Design work and implementation)
 * @author ???
 *
 */

class UPB_Registration
{
	function UPB_Registration()
	{
	}

	/**
	 * Checks if this is the first user and automatically makes the
	 * first user an Admin. This should solve the issue of quitting
	 * the installer early and not inserting an admin account.
	 *
	 * @param UPB_User $userdata - user class containing the user data
	 *
	 * @return TRUE on success, FALSE on failure.
	 */
	function register($userdata)
	{
	}

	/**
	 * Dumps the user register form or store it in $formData if it
	 * is set to anything.
	 * 
	 * @param UPB_User $defaultUserData - Default user data (if any)
	 * @param string &$formData - If set, output will be stored here
	 * 		instead of dumped to stdout
 	 *
	 * @return void
	 */
	function displayRegisterForm($defaultUserData, &$formData = null)
	{
		$msg = $defaultUserData."<br />";
		
		if($formData != null)
		{
			$formData = $msg;
		}
		else
		{
			echo $msg;
		}
	}

	/**
	 * Validates the register form data.
	 *
	 * @return bool true on success, false on failure.
	 */
	function validateRegisterForm()
	{
	}
}

/*$register = new UPB_Registration();

echo "using regular dump:<br />";
$register->displayRegisterForm("test");

$data = "";
echo "<br />usin reference dump:<br />";
$register->displayRegisterForm("reference", $data);
echo $data;*/

?>