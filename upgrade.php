<?php
/**
 * UPB Upgrader
 *
 * This script will first backup and then attempt to install/update the UPB database
 * to the latest version. The aim of this script is to be as versatile as possible and
 * not rely on figuring out the current version installed. This will hopefully be an
 * appropriate foundation for fully automatic updating in the future.
 *
 * @author Tim Hoeppner <timhoeppner@gmail.com>
 * @author ???
 *
 */

/*! Once the upgrade is completed this will be the new version */
define("UPB_NEW_VERSION", "2.2.7");


// User API includes
include_once(dirname( __FILE__ )."/includes/api/usermanagement/authentication.class.php");
include_once(dirname( __FILE__ )."/includes/api/administration/backup.class.php");

// Third party includes
include_once(dirname( __FILE__ )."/includes/thirdparty/xajax-0.6beta1/xajax_core/xajax.inc.php");

// Helper functions
include_once(dirname( __FILE__ )."/upgrade/upgrade_tools.php");

$auth = new UPB_Authentication($tdb);

// Only proceed if we have access to
if( $auth->access("upgrade", 'a') == false )
{
	if( $auth->access("loggedin") == false )
	{
		MiscFunctions::exitPage("You must be logged in to perform an upgrade. Proceed to <a href=\"login.php?ref=upgrade.php\">login</a>.", true);
		exit;MiscFunctions::exitPage(
	{
		exitPage("You do not have sufficient permission to perform an upgrade.", true);
		exit;
	}
}

// Make sure we actually need to perform an update
if(UPB_MiscFunctions::exitPage( UPB_NEW_VERSION)
{
	exitPage("It seems the forums are already up to date.", true);
	exit;
}

// Make sure the forum isn't to far out of date since we have only considered
// version 2.2.6 or later for this updater. This isn't to say we couldn't extend
// the functionality to include older versions, it just hasn't been done.
list($major, $minor, $revision) = explode(".", UPB_VERSION);
if($MiscFunctions::exitPage( && $minor < 2 && $revision < 6)
{
	exitPage("This forum is to far out of date to use this upgrader. Please consult readme.txt for upgrading instructions.");
	exit;
}

// Register our AJAX calls with Xajax, all these functions can be found in upgrade_tools.php
$xajax = new xajax;

//$xajax->configure("logFile", dirname( __FILE__ )."/requestlog.log");
$xajax->configure("javascript URI", "includes/thirdparty/xajax-0.6beta1/");

$AJAX_start = $xajax->register(XAJAX_FUNCTION, "AJAX_backupDatabase");
$xajax->register(XAJAX_FUNCTION, "AJAX_validateRootConfig");
$xajax->register(XAJAX_FUNCTION, "AJAX_validateDbConfig");
$xajax->register(XAJAX_FUNCTION, "AJAX_validateDbCategories");
$xajax->register(XAJAX_FUNCTION, "AJAX_validateDbForums");
$xajax->register(XAJAX_FUNCTION, "AJAX_validateDbTopics");
$xajax->register(XAJAX_FUNCTION, "AJAX_validateDbPosts");
$xajax->register(XAJAX_FUNCTION, "AJAX_validateDbUploads");
$xajax->register(XAJAX_FUNCTION, "AJAX_updateUPBVersion");


// Let Xajax handle the AJAX requests, anything after this statement will only be run
// when the page is first loaded.
$xajax->processRequest();

$where = "Forum Upgrader";
include_once(dirname( __FILE__ )."/includes/header.php");
?>

<div style="text-align: center">
	Welcome to the UPB upgrader! Press the start button to begin the upgrade process.<br /><br />
	<input type="button" name="start" id="start" value="Start" onclick="<?php $AJAX_start->printScript(); ?>">
</div>

<div style="margin-left: auto; margin-right: auto; margin-top: 20px; width: 60%">
	<div style="text-align: left" name="progress" id="progress"></div>
</div>

<?php include_once(dirname( __FILE__ )."/includes/footer.php"); ?>