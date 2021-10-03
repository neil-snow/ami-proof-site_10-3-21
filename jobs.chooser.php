<?PHP
include("inc/db.inc.php");
include("inc/classes.inc.php");
include("inc/jobs.conf.php");
session_start();



/*******************************/
/******** PAGE SETUP ***********/
/*******************************/

$LOGGEDIN		= $_SESSION["LOGGEDIN"];
$sesUser			= $_SESSION["sesUser"];
$action			= $_REQUEST["action"];
$info				= (isset($_GET["info"])) ? $_GET["info"] : "";

/* Double check both logged in vars to ensure no tampering! */
if(!$LOGGEDIN || !$sesUser->LOGGEDIN){
		header("location: clients.relogin.php?info=" . urlencode("Your login has been unexpectedly terminated."));
	exit();
}

/* Make sure they have 'master' privelages to be at this page */
checkAdmin();

/////////////////////////////////////////////



								
// redirect back where we came from and let that page handle the actions, this code below was old

/*
if($action == "insert"){
	
	if(isset($_POST["chooserVal"])){
		$cId = $_POST["chooserVal"];
		$url = $_SESSION["ref"] . "?cId=" . $cId;
		session_unregister("ref");
		session_unregister("chooserField");
		session_unregister("titlebar");
		header("location: $url");
		exit();	
		
	}
}

*/

/* If custom fields are included add the name of the field to the hidden fieldlist[] html array and the ->save() method will store it */
$output 		= $_SESSION["chooserField"];
$titlebar 	= $_SESSION["titlebar"]; 
$ref			= $_SESSION["ref"];


if(strlen($info) > 0){
	$info = formatInfo($info);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title> <?=$titlebar?> </title>
<link rel="stylesheet" type="text/css" href="<?=$STYLESHEET?>">
</head>
<body>
<form name="frmEdit" method="post" action="<?=$ref?>">
<table width="98%" cellpadding="2" cellspacing="0" border="0">
<tr>
	<td colspan="2" style="vertical-align:bottom">
	<a href="javascript:history.go(-1)">> PREVIOUS PAGE</a>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
	          <tr> 
	            <td ><img src="images/spacer.gif" width="1" height="72" border="0"></td>
	           </tr>
	           <tr>
	            <td style="text-align:left;vertical-align: bottom"><?=$info?></td>
	        </tr>
	      </table></td>
</tr>
<tr>
	<td class="COLHEAD" colspan="2" style="background-image:url(images/nav_bg.gif);background-repeat:repeat-x;"><?=$titlebar?></td>
</tr>
<?=$output?>
<tr>
	<td>&nbsp;</td>
	<td align="left"><input type="button" name="btnSubmit" value="CONTINUE" onclick="frmEdit.submit()"></td>
</tr>
</table>
</form>

</body>
</html>