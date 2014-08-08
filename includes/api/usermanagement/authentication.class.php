<?php
/**
 * UPB_Authentication is apart of the UPB user API and provides easy access to
 * the forum's user authentication functions.
 *
 * Example:
 * include_once("/path/to/upb/includes/api/usermanagement/authentication.class.php");
 *
 * $auth = new UPB_Authentication($tdb);
 * if( $auth->access("loggedin") == true )
 * {
 *     echo "Yay! I'm logged in.";
 * }
 * else
 * {
 *     echo "Aww.. I'm not logged in.";
 * }
 *
 * @author Tim Hoeppner <timhoeppner@gmail.com> (Design work and implementation)
 * @author ???
 *
 */

include_once(dirname( __FILE__ )."/../../upb.initialize.php");
include_once(dirname( __FILE__ )."/authentication.base.php");

//include_once(dirname( __FILE__ )."/../../../config.php");

if(!defined("DB_DIR"))
{
	die("The UPB_Authentication class cannot find the database directory and cannot function without it.");
}

class UPB_Authentication extends UPB_AuthenticationBase
{
	var $_cache;
	var $func;

	function UPB_Authentication(&$tdb)
	{
		// At the present state we will just use our standard authentication mechanism
		// but we need an instance of the function class for that.
		$this->func = &$tdb;
	}

	function login($username, $password, $duration = -1)
	{
	}

	function logoff()
	{
	}

	/**
	 *
	 * @param string $itemType - "loggedin", "upgrade", "config", "category", "forum", "topic", "post"
	 * @param char $accessType - r: read, w: write, c: create, m: moderate, a: administrate
	 * @param int $itemId      - Id number of the config item, category, or forum
	 * @param int $topicId     - Id number of the associated topic if applicable
	 * @param int $postId      - Id number of the associated post if applicable
	 *
	 * @return true if access is granted, false if access is denied.
	 */
	function access($itemType, $accessType = "r", $itemId = 0, $topicId = 0, $postId = 0)
	{
		// For all of the "Item Types" we need to determine if the user is actually logged in
		$loggedin = $this->func->is_logged_in();
		$access_granted = false;

		// Guests have access to some items but we can't rely on their cookies if they arn't
		// logged in, so we populate the cookie artificially.
		if( $loggedin == false )
		{
            $_COOKIE["power_env"] = 0;
            $_COOKIE["id_env"] = 0;
		}

		// Ensure the item Id is an integer
		$itemId = (int)$itemId;

		switch($itemType)
		{
			case "loggedin":
				$access_granted = $loggedin;
				break;

			case "upgrade":
				$access_granted = $this->_upgradeAccess();
				break;

			case "config":
				$access_granted = $this->_configAccess($accessType, $itemId);
				break;

			case "category":
				$access_granted = $this->_categoryAccess($accessType, $itemId);
				break;

			case "forum":
				$access_granted = $this->_forumAccess($accessType, $itemId);
				break;

			case "topic":
				$access_granted = $this->_topicAccess($accessType, $itemId, $topicId);
				break;

			case "post":
				$access_granted = $this->_postAccess($accessType, $itemId, $topicId, $postId);
		}

		return $access_granted;
	}

	/**
	 * Displays the login form
	 *
	 * @param string* $formData - If this is not null then the form data will be
	 * 	dumped here instead of displayed.
	 *
	 * @return void
	 */
	function displayLoginForm(&$formData = null)
	{
	}

	/**
	 * Validates the login form fields.
	 *
	 * @return bool true on success, false on failure.
	 */
	function validateLoginForm()
	{
	}
}
?>