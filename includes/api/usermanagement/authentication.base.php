<?php
/**
 * Internal functions used by UPB_Authentication
 *
 * @author Tim Hoeppner <timhoeppner@gmail.com>
 *
 */

class UPB_AuthenticationBase
{
    function _upgradeAccess()
    {
        $access = false;

        // Only an administrator has priveledge to upgrade the forum
        if( $_COOKIE["power_env"] >= 3 )
        {
            $access = true;
        }

        return $access;
    }

	function _configAccess($accessType, $itemId)
	{
        // Currently it doesn't matter what access type is required, only administrators
        // can modify configuration.

        $access = false;

        if( $_COOKIE["power_env"] >= 3 )
        {
            $access = true;
        }

        return $access;
	}

	function _categoryAccess($accessType, $itemId)
	{
        $access = false;

        $query = $this->func->get("cats", $itemId);

        if( isset($query[0]) )
        {
            $row = $query[0];

            if( $accessType == 'r' && $row["view"] <= $_COOKIE["power_env"] )
            {
                $access = true;
            }

            if( $accessType == 'c' && $_COOKIE["power_env"] >= 3 )
            {
                $access = true;
            }

            if( $accessType == 'm' && $_COOKIE["power_env"] >= 2 )
            {
                $access = true;
            }

            if( $accessType == 'a' && $_COOKIE["power_env"] >= 3 )
            {
                $access = true;
            }
        }

        return $access;
	}

	function _forumAccess($accessType, $itemId)
	{
        // This is kind of a waste since we already run this query in viewforum.php but
        // once we implement the new group system we will need the authentication setup
        // this way.

        $access = false;

        $query = $this->func->get("forums", $itemId);

        if( isset($query[0]) )
        {
            $row = $query[0];

            if( $accessType == 'r' && $row["view"] <= $_COOKIE["power_env"] )
            {
                $access = true;
            }

            if( $accessType == 'w' && $row["reply"] <= $_COOKIE["power_env"] )
            {
                $access = true;
            }

            if( $accessType == 'c' && $row["post"] <= $_COOKIE["power_env"] )
            {
                $access = true;
            }

            if( $accessType == 'm' && $_COOKIE["power_env"] >= 2 )
            {
                $access = true;
            }

            if( $accessType == 'a' && $_COOKIE["power_env"] >= 3 )
            {
                $access = true;
            }
        }

        return $access;
	}

	function _topicAccess($accessType, $itemId, $topicId)
	{
        // Again this is a wasteful query but we want to keep this API as independant
        // as possible and can't have it rely on any preconditions.

        $access = false;

        $query = $this->func->get("forums", $itemId);

        if( isset($query[0]) )
        {
            $row = $query[0];

            if( $accessType == 'r' && $row["view"] <= $_COOKIE["power_env"] )
            {
                $access = true;
            }

            if( $accessType == 'w' && $row["reply"] <= $_COOKIE["power_env"] )
            {
                $access = true;
            }

            if( $accessType == 'm' && $_COOKIE["power_env"] >= 2 )
            {
                $access = true;
            }

            if( $accessType == 'a' && $_COOKIE["power_env"] >= 3 )
            {
                $access = true;
            }
        }

        return $access;
	}

	function _postAccess($accessType, $itemId, $topicId, $postId)
	{
        // Again this is a wasteful query but we want to keep this API as independant
        // as possible and can't have it rely on any preconditions.

        $access = false;

        $this->func->setFp("posts", $itemId);
        $query = $this->func->get("posts", $postId);

        if( isset($query[0]) )
        {
            $row = $query[0];

            // NOTE: If you have access to read the associated topic of a post then you
            //       have permission to view all of the posts in the topic and so there
            //       is no need to grant read permission to a particular post. The more
            //       appropriate request would be to check for read access of the associated
            //       topic.

            if( $accessType == 'w' && $row["user_id"] == $_COOKIE["id_env"] )
            {
                $access = true;
            }

            if( $accessType == 'm' && $_COOKIE["power_env"] >= 2 )
            {
                $access = true;
            }

            if( $accessType == 'a' && $_COOKIE["power_env"] >= 3 )
            {
                $access = true;
            }
        }

        return $access;
	}
}

?>