<?php
if (get_magic_quotes_gpc()) {
    function stripslashes_deep($value)
    {
        $value = is_array($value) ?
                    array_map('stripslashes_deep', $value) :
                    stripslashes($value);

        return $value;
    }

    $_POST = array_map('stripslashes_deep', $_POST);
    $_GET = array_map('stripslashes_deep', $_GET);
    $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
    $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}

// Set session timeout (in seconds)

$cat_array[] = "Backlit";
$cat_array[] = "DashMax";
$cat_array[] = "Lexview";
$cat_array[] = "Ice Mesh";
$cat_array[] = "Pounced";
$cat_array[] = "Banner Mesh";
$cat_array[] = "Mounted";
$cat_array[] = "Photo Paper";
$cat_array[] = "Flatbed";

/*************************************
** AMUSEMENT MEDIA 						**
** client proofing system				**
**												**
** modified from AMI/Calendar			**
** AMI/calendars 	== clients			**
** AMI/events		==	jobs				**
**												**
** Last Modified: 6/16/04				**
** by Neil - SAS							**
**												**
**************************************

/* CONSTANTS */

$USER_TABLE			= "users";
$CATEGORY_TABLE 	= "category";
$PERM_TABLE			= "permissions";
$CLIENTS_TABLE		= "clients";
$GROUP_TABLE		= "usergroups";
$JOBS_TABLE			= "jobs";
$IMAGES_TABLE		= "media";
$NOTES_TABLE		= "notes";
$TRACKING_TABLE		= "tracking";
$PROMOBANNER_TABLE  = 'promobanner';

/* URLS USED FOR PERMSSIONS */
$URL_CLIENTS 		= "clients.manage.php";
$URL_PERM 			= "permissions.manage.php";
$URL_JOBS 			= "jobs.manage.php";
$URL_CHOOSER 		= ami_interface_url('jobs.chooser');
$CAL_DENIED 		= "jobs.manage.php";
$JOBS_DENIED 		= "jobs.manage.php";
$URL_VIEW 			= "jobs.manage.php";
$SITE_LINK			= "https://phpstack-221683-2059324.cloudwaysapps.com";

//$CLIENT_UPLOAD_DIR = "/var/www/html/AmusementMedia/web/login/images/uploads";
//$CLIENT_UPLOAD_DIR 	= "/www3/amusementmedia/www/login/images/uploads";
//$CLIENT_UPLOAD_DIR 	= "/home/amuse/public_html/login/images/uploads";
//$CLIENT_UPLOAD_DIR 	= "/home/stageamuse/public_html/login/images/uploads";
$CLIENT_UPLOAD_DIR  = "/home/221683.cloudwaysapps.com/fveapdmkgw/public_html/images/uploads/files";
$CLIENT_UPLOAD_PATH 	= "images/uploads/files";

//$CLIENT_WIP_UPLOAD_DIR 	= "/var/www/html/AmusementMedia/web/login/images/uploads/wip";
//$CLIENT_WIP_UPLOAD_DIR 	= "/www3/amusementmedia/www/login/images/uploads/wip";
//$CLIENT_WIP_UPLOAD_DIR 	= "/home/amuse/public_html/login/images/uploads/wip";
//$CLIENT_WIP_UPLOAD_DIR 	= "/home/stageamuse/public_html/login/images/uploads/wip";
$CLIENT_WIP_UPLOAD_DIR 	= "/home/221683.cloudwaysapps.com/fveapdmkgw/public_html/images/uploads/wip";
$CLIENT_WIP_UPLOAD_PATH 	= "images/uploads/wip";

$USER_NO_EDIT 		= array("id","iAccess","iRegionId","dtStamp","sColor");
$USER_DESC 			= array("sPhone1"=>"Work Phone","sPhone2"=>"Evening Phone","dtDate"=>"Date Added");

$GROUPS_NO_EDIT 	= array("id","dtStamp");
$GROUPS_DESC 		= array("wName"=>"Group Name","csvMembers"=>"Members");

$CLIENTS_NO_EDIT 	= array("id", "dtStamp", "wOwner");
$CLIENTS_DESC 		= array("sShipAddress1"=>"(ship)Address 1", "sShipAddress2"=>"(ship)Address 2", "sShipCity"=>"(ship)City", "sShipState"=>"(ship)State", "sShipZip"=>"(ship)Zip","sShipCountry"=>"(ship)Country", "sFilename"=>"Logo", "sDesc"=>"Description", "sAddress1"=>"Address 1", "sAddress2"=>"Address 2", "sContact"=>"AMI Contact Name", "sContactEmail"=>"AMI Contact Email", "sContactPhone"=>"AMI Contact Phone", 'sFloridaContactEmail' => 'AMI Florida Email', 'bReseller' => 'Reseller', 'sTrackingEmail' => 'Tracking Email');

$PERMS_NO_EDIT 	= array("id","sTitle","sLocation","wUsername","csvView","csvEdit","dtStamp");
$PERMS_DESC 		= array("bDel"=>"Delete");

$JOBS_NO_EDIT 		= array("id","iCatId", "dtStamp","iCal", "wOwner","dtDate", "bLocked");
$JOBS_DESC 			= array("tNotes"=>"Description", "wOwner"=>"Creator / Owner", "sColor"=>"Status");

$IMAGES_NO_EDIT 	= array("id","iRefId","dtStamp", "wOwner","dtDate","sSize","bAccept","bChanges","bProof", "bLocked", "sApproveDate", "sQueueDate", "sPrinterDate", "sPrintDate", "sShipDate", "bPrinter", "bShipped", "bQueue", "sBoxed", 'sReorderDate');
$IMAGES_DESC 		= array("sTurnaround" => "Turnaround", "dtDue"=>"Due Date", "sQty"=>"Quantity","sPrintSize"=>"Printed Size","sFilename"=>"File", "tDesc"=>"Description","bChanges"=>"Accept with changes");

$NOTES_NO_EDIT 	= array("id","sName","sEmail", "wOwner","dtDate", "dtStamp", "iRefId", "sDate");
$NOTES_DESC 		= array();

$TRACKING_NO_EDIT 	= array("id","iMediaId");
$TRACKING_DESC 		= array();

$CATEGORY_NO_EDIT 	= array("id","dtStamp");
$CATEGORY_DESC 		= array("sName"=>"Category Name", "sDesc"=>"Description");

$PROMOBANNER_NO_EDIT 	= array("id","dtStamp");
$PROMOBANNER_DESC 		= array("sFileName"=>"Banner Image", "sTitle"=>"Banner Title",
								"tBody"=>"Promo Message Body", 'sUrl' => 'URL');


$DEFAULT_ENDUSER_VIEW	= array("sName","tNotes","sColor");
$DEFAULT_ENDUSER_EDIT	= array("");
$DEFAULT_ENDUSER_DEL		= 0;
$DEFAULT_ENDUSER_ADD		= 0;
$DEFAULT_ENDUSER_UPLOAD	= 1;

$ALLOWED_FILE_TYPES = array("image/pjpeg","image/gif","application/pdf","image/x-photoshop","image/jpeg","image/jp2","image/jpx","image/png","image/tiff","image/tiff-fx","application/octet-stream", "application/postscript", "application/x-shockwave-flash","application/x-zip-compressed","application/zip", "image/vnd.adobe.photoshop");

$THUMBNAIL_DIR 	= "thumbs";
$THUMBNAIL_WIDTH 	= "300";
$THUMBNAIL_HEIGHT = "50";

$STYLESHEET 		= "inc/jobs.css";
$STYLESHEET_URL 	= "/login/inc/jobs.css";

/* maximum number of _____ allowed */
$CLIENT_MAX 		= 10000;
$GROUP_MAX 			= 10000;
//add 1 to the user max because of the CIRCUS user
$USER_MAX 			= 10001;

$CIRCUS_NAME 		= "circus";

$COLOR_OPTIONS = '';
//$COLOR_OPTIONS 	.= "\n\t<option value=\"#FF0000\" style=\"color:#FF0000\">Red</option>";
//$COLOR_OPTIONS 	.= "\n\t<option value=\"#FF00CC\" style=\"color:#FF00CC\">Pink</option>";
//$COLOR_OPTIONS 	.= "\n\t<option value=\"#9900FF\" style=\"color:#9900FF\">Purple</option>";
//$COLOR_OPTIONS 	.= "\n\t<option value=\"#0099FF\" style=\"color:#0099FF\">Light Blue</option>";
//$COLOR_OPTIONS 	.= "\n\t<option value=\"#006633\" style=\"color:#006633\">Dark Green</option>";
//$COLOR_OPTIONS 	.= "\n\t<option value=\"#FF9900\" style=\"color:#FF9900\">Orange</option>";
//$COLOR_OPTIONS 	.= "\n\t<option value=\"#666666\" style=\"color:#666666\">Grey</option>";
//$COLOR_OPTIONS 	.= "\n\t<option value=\"#000000\" style=\"color:#000000\">Black</option>";
//$COLOR_OPTIONS 	.= "\n\t<option value=\"#990000\" style=\"color:#990000\">Working</option>";
//$COLOR_OPTIONS 	.= "\n\t<option value=\"#006600\" style=\"color:#006600\">Printed</option>";
$COLOR_OPTIONS 	.= "\n\t<option value=\"#339900\" style=\"color:#339900\">Open</option>";
$COLOR_OPTIONS 	.= "\n\t<option value=\"#000000\" style=\"color:#000000\">Closed</option>";



$ADMIN_GROUP 		= "ADMIN";
/* COLLECTION OF ALL USERS */
/* the 'user' class is only used to login and set session vars */
/* the SQLobj class is used for the collection and handles add/edit/delete */
$USERS 				= new collection($USER_TABLE,"SQLobj",$USER_NO_EDIT, $USER_DESC);

/* COLLECTION OF ALL GROUPS */
$GROUPS 				= new collection($GROUP_TABLE,"SQLobj",$GROUPS_NO_EDIT, $GROUPS_DESC);

/* COLLECTION OF ALL CLIENTS */
$CLIENTS				= new collection($CLIENTS_TABLE, "SQLobj", $CLIENTS_NO_EDIT, $CLIENTS_DESC);
$CLIENTS->setUploadDir($CLIENT_UPLOAD_DIR);
$CLIENTS->setThumbs(FALSE);


/* COLLECTION OF ALL PERMISSIONS */
$PERMS 				= new collection($PERM_TABLE, "SQLobj", $PERMS_NO_EDIT, $PERMS_DESC);

/* COLLECTION OF ALL JOBS */
$JOBS 				= new collection($JOBS_TABLE, "SQLobj", $JOBS_NO_EDIT, $JOBS_DESC);

$CATEGORY 			= new collection($CATEGORY_TABLE, "SQLobj", $CATEGORY_NO_EDIT, $CATEGORY_DESC);

/* COLLECTION OF ALL IMAGES */
$IMAGES 				= new collection($IMAGES_TABLE,"SQLobj",$IMAGES_NO_EDIT, $IMAGES_DESC);
$IMAGES->setUploadDir($CLIENT_UPLOAD_DIR);
$IMAGES->setThumbDir($THUMBNAIL_DIR);
$IMAGES->setThumbs(TRUE);

/* COLLECTION OF ALL NOTES */
$NOTES				= new collection($NOTES_TABLE, "SQLobj", $NOTES_NO_EDIT, $NOTES_DESC);

/* COLLECTION OF ALL TRACKING */
$TRACKING			= new collection($TRACKING_TABLE, "SQLobj", $TRACKING_NO_EDIT, $TRACKING_DESC);

/* COLLECTION OF ALL NOTES */
$PROMOBANNER		= new collection($PROMOBANNER_TABLE, "SQLobj", $PROMOBANNER_NO_EDIT, $PROMOBANNER_DESC);
$PROMOBANNER->setUploadDir($CLIENT_UPLOAD_DIR);
$PROMOBANNER->setThumbs(FALSE);

function formatInfo($info){
	$color = (preg_match("/^error/i",$info)) ? "#990000" : "#006600";

	$info = urldecode($info);

	$info = "<span style=\"font-size: 16px;color: $color\">$info</span>";	
	return $info;
}	

function checkAdmin(){
	/* see if the user is at admin status otherwise redirect */
	$user = $_SESSION["sesUser"];
	if($user->ADMIN != TRUE){

		$url = ami_interface_url('jobs.manager');
		header("location: " . $url);
		exit();
	}	
}



function htmlMail($to, $subject, $text, $html, $from, $femail){
	//$to - who the email is to, can be an array or single email address
	//$subject - subject of th email
	//$text - plain text body for email
	//$html - html body for the email, head / body tags added automatically 
	//$from - who the email is from "Neil"
	//$femail - the email address of the user sending the message "neilsnow@mac.com"
	$html = "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\">
				<html>
				<head>
				<title> AMI: Client Proof System </title>
				<link rel=\"stylesheet\" type=\"text/css\" href=\"" . $GLOBALS["STYLESHEET_URL"] . "\">
				<style type=\"text/css\">
					td{
						font-family: verdana,helvetica,arial;
						font-size: 11px;
					}
				</style>
				</head>
				<body>\nFrom Email : ".$femail."\n\n\n" . $html . "\n</body>\n</html>";
			
		
	
	$message="This is a multi-part message in MIME format.";
	$message.="\n\n------=_NextPart_000_02D6_01C351F3.CCB591A0\nContent-Type: multipart/alternative;\n\tboundary=\"----=_NextPart_001_02D7_01C351F3.CCB591A0\"\n";
	$message.="\n\n------=_NextPart_001_02D7_01C351F3.CCB591A0\nContent-Type: text/plain;\n\tcharset=\"iso-8859-1\"\nContent-Transfer-Encoding: 7bit";
	$message.="\n\n$text";
	$message.="\n\n------=_NextPart_001_02D7_01C351F3.CCB591A0\nContent-Type: text/html;\n\tcharset=\"iso-8859-1\"\nContent-Transfer-Encoding: 7bit";
	$message.="\n\n$html";
	$message.="\n\n------=_NextPart_001_02D7_01C351F3.CCB591A0--";
	$message.="\n------=_NextPart_000_02D6_01C351F3.CCB591A0--";

		require_once('class.phpmailer.php');
		
		$mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
		
		$mail->IsSMTP(); // telling the class to use SMTP
		
		try {
		  // GMAIL SETTINGS

		
		  //$mail->SMTPDebug  = 2;                      // enables SMTP debug information (for testing)
		  $mail->SMTPAuth   = true;                  	// enable SMTP authentication
		  $mail->SMTPSecure = "tls";                 	// sets the prefix to the servier
		  $mail->Host       = "smtp-relay.gmail.com";   // sets GMAIL as the SMTP server
		  $mail->Port       = 587;                   	// set the SMTP port for the GMAIL server - CAN ALSO BE 587
		  $mail->Username   = "polarissigns@ami-graphics.com";  // GMAIL username
		  $mail->Password   = "Media123";            	// GMAIL password
		  //$mail->AddReplyTo('amiclientproof@amigraphics.com', 'NoReply');
		  
		  //WORLDPATH SETTINGS

		  /*
		  $mail->Host		= "mail.amusementmedia.com";
		  $mail->SMTPDebug  = 0;                        // enables SMTP debug information (for testing)
		  $mail->SMTPAuth   = false;                    // enable SMTP authentication
		  $mail->Host       = "localhost";        	// sets Amusement Media as the SMTP server
		  $mail->Port       = 25;                	// set the SMTP port for the SMTP server
		  */
		  
		  $mail->AddReplyTo('noreply@amusementmedia.com', 'NoReply');

		  
		if(is_array($to)){	
			foreach($to as $v)
			$mail->AddAddress($v);
		}else{
			$mail->AddAddress($to);
		}
		

		// debug mail
		//$mail->AddAddress('testgroup@amusementmedia.com');
		//$mail->AddAddress('snow.neil@gmail.com');

		  $mail->SetFrom('amiclientproof@amigraphics.com', 'AMI Client Proof');
		  $mail->Subject = $subject;
		  $mail->AltBody = $html;
		  $mail->MsgHTML($html);
		  $mail->Send();
           error_log("Sent $subject to " . (is_array($to) ? implode(', ', $to) : $to));

           //echo 'mail!<br />';
           //exit;
		
		} catch (phpmailerException $e) {
			echo $e->errorMessage();
			exit;
		  error_log($e->errorMessage()); //Pretty error messages from PHPMailer
		} catch (Exception $e) {
			echo $e->getMessage();
			exit;
		  error_log($e->getMessage()); //Boring error messages from anything else!
		}

		/*
	if(is_array($to)){	
		foreach($to as $v)
			mail($v,$subject,$message,$headers);	
	}else{
		mail($to,$subject,$message,$headers);	
	}
	*/
	return true;
}


function loadAdminEmails(){
	$ret = array();
	$sql = "select csvMembers from " . $GLOBALS["GROUP_TABLE"] . " where wName='" . $GLOBALS["ADMIN_GROUP"] . "'";
	$res = mysql_query($sql);
	if(mysql_num_rows($res)>0){
		$r = mysql_fetch_assoc($res);
		$admins = unserialize($r["csvMembers"]);
		foreach($admins as $v){
			$sql = "select sEmail from " . $GLOBALS["USER_TABLE"] . " where id='" . $v . "'";
			$res = mysql_query($sql);
			if(mysql_num_rows($res) > 0){
				$email = mysql_result($res,0);
				if(strlen($email) > 0){
					array_push($ret, $email);	
				}
			}
			
		}
			
	}
	
	return $ret;
}

function emailToAddress($clientId, $defaultEmail) {
	if ($clientId > 0) {
		$sql = "select sContactEmail from clients where id='" . $clientId . "'";
		$res = mysql_query($sql);

		if(mysql_num_rows($res)>0){
			$r = mysql_fetch_array($res);
		
		if ($r && $r["sContactEmail"] != "" && $r["sContactEmail"] == "baasupport@amigraphics.com") {
	            return "baasupport@amigraphics.com";
	        }
	        if ($r && $r["sContactEmail"] != "" && $r["sContactEmail"] == "amiclientprooffl@amigraphics.com") {
	            return "amiclientproofFL@amigraphics.com";
	        }
	        if ($r && $r["sContactEmail"] != "" && $r["sContactEmail"] == "mattg@amigraphics.com") {
	            return "mgahm-amiclientproof@amigraphics.com";
	        }
	        if ($r && $r["sContactEmail"] != "" && $r["sContactEmail"] == "mattg@ami-graphics.com") {
	            return "mgahm-amiclientproof@amigraphics.com";
	        }
			if ($r && $r["sContactEmail"] != "" && $r["sContactEmail"] == "bryan@amigraphics.com") {
	            return "bryansproofs@amigraphics.com";
	        }
			if ($r && $r["sContactEmail"] != "" && $r["sContactEmail"] == "stacey@amigraphics.com") {
	            return "staceysproofs@amigraphics.com";
	        }
			if ($r && $r["sContactEmail"] != "" && $r["sContactEmail"] == "timg@amigraphics.com") {
	            return "timsproofs@amigraphics.com";
	        }
			if ($r && $r["sContactEmail"] != "" && $r["sContactEmail"] == "garrett@amigraphics.com") {
	            return "garrettsproofs@amigraphics.com";
	        }
			if ($r && $r["sContactEmail"] != "" && $r["sContactEmail"] == "sander@amigraphics.com") {
	            return "sandersproofs@amigraphics.com";
	        }
			if ($r && $r["sContactEmail"] != "" && $r["sContactEmail"] == "mike@amigraphics.com") {
	            return "mikesproofs@amigraphics.com";
	        }
			if ($r && $r["sContactEmail"] != "" && $r["sContactEmail"] == "info@amigraphics.com") {
	            return "infosproofs@amigraphics.com";
	        }
			
/*		if ($r && $r["sContactEmail"] != "" && $r["sContactEmail"] == "steve@amigraphics.com") {
		    return "stevesubmissions@amigraphics.com";
			} */
/*	        if ($r && $r["sContactEmail"] != "" && $r["sContactEmail"] == "jim.reilly@amigraphics.com") {
	            return "jim-amiclientproof@amigraphics.com";
	        } */

/* NEW EMAIL GROUPS ADDED by NS 8.14.21 */			



		}
	}

	return $defaultEmail;
}

function convert_smart_quotes($string) {
    $search = array(chr(145), 
                    chr(146), 
                    chr(147), 
                    chr(148), 
                    chr(151)); 

    $replace = array("'", 
                     "'", 
                     '"', 
                     '"', 
                     '-'); 
    return str_replace($search, $replace, $string);
}


function doitwell($text)
    {
        $badwordchars=array("\xe2\x80\x98", "\xe2\x80\x99", "\xe2\x80\x9c", "\xe2\x80\x9d", "\xe2\x80\x93", "\xe2\x80\x94", "\xe2\x80\xa6");
        $fixedwordchars=array("'", "'", '"', '"', '-', '--', '...');
        
        return str_replace($badwordchars,$fixedwordchars,$text);
    }


?>