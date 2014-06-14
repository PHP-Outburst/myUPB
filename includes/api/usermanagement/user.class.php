<?php
/**
 * 
 * UPB_User is a class that contains a single user table row along with several
 * support functions that are performed on a specific user. Generally this class
 * will be returned from the UPB_Authentication class and used to represent the
 * actual user on the forums.
 * 
 * @author Tim Hoeppner <timhoeppner@gmail.com> (Design work and implementation)
 * @author ???
 *
 */

class UPB_User
{
	var $userId;
	
	/**
	 * Class initializer
	 * 
	 * @param int $userId - If this is greater than 0 then this
	 * 			method will automatically retrieve the user record
	 */
	function UPB_User($userId = 0)
	{
		$this->userId = $userId;
		
		// Attempt to fetch user row
		if($userId > 0)
		{
			//
		}
	}

	/**
	 * Returns the latest time at which the user visited a new 
	 * topic on the previous session.
	 * 
	 * @return int - Unix timestamp of the last visit stored in the DB
	 */
	function getDbLastVisit()
	{
	}

	/**
	 * Returns the time of users newest topic visited on this
	 * particular session. Cannot return anything less than
	 * getDbLastVisit().
	 * 
	 * @return int - Unix timestamp of the last session visit
	 */
	function getSessionLastVisit()
	{
	}

	/**
	 * Returns an array of topic id's that have been added to the
	 * session data using addSessionViewedTopic().
	 * 
	 * @return int[] - Array of topic Id's or empty array
	 */
	function getSessionViewedTopics()
	{
	}

	/**
	 * Sets database last visit to the session lastvisit.
	 *
	 * I guess the big challenge here is how to determine when a
	 * user is finished a particular session...
	 * 
	 * @return void
	 */
	function setDbLastVisit()
	{
	}

	/**
	 * This function is called anytime a user visits a topic. The
	 * session last visit is only updated if the viewed topic is
	 * newer than the existing session last visit.
	 * 
	 * @return void
	 */
	function setSessionLastVisit()
	{
	}

	/**
	 * This function is also called anytime a user visits a topic.
	 * The purpose of this function is to keep track of all the
	 * topics viewed by the user in a single session.
	 * 
	 * @return void
	 */
	function addSessionViewedTopic($t_id)
	{
	}
}
?>