<?PHP

/*******************************/
/******** PAGE SETUP ***********/
/*******************************/

$LOGGEDIN		= $_SESSION["LOGGEDIN"];
$sesUser			= $_SESSION["sesUser"];
$id				= $_REQUEST["id"];
$action			= $_REQUEST["action"];
$info				= "";

/* Double check both logged in vars to ensure no tampering! */
if(!$LOGGEDIN || !$sesUser->LOGGEDIN){
		header("location: clients.relogin.php?info=" . urlencode("Your login has been unexpectedly terminated."));
	exit();
}



/* Make sure they have 'master' privelages to be at this page */
checkAdmin();


/////////////////////////////////////////////
if($action == "save"){
	/* ERROR CHECKING */
	$_POST["F_sFilename_W"] = 200;
	$_POST["F_sFilename_H"] = 100;	
	if($info == "")
		$info = $CLIENTS->save($id);		
}else if($action == "del" && $_REQUEST["id"] >= 0){
	
	/* remove logo file and/or thumbnail if it exists */
	$sql = "select sFilename from " . $CLIENTS_TABLE . " where id='" . $_REQUEST["id"] . "' limit 1";
	$res = mysql_query($sql);
	if(mysql_num_rows($res) > 0){
		$r = mysql_fetch_array($res);
		$thumbName = (strrpos($r["sFilename"],".")) ? substr($r["sFilename"], 0, strrpos($r["sFilename"], ".")) . "(" . substr($r["sFilename"], (strrpos($r["sFilename"], ".") + 1)) . ")"  : $r["sFilename"];
		$thumbName .= ".jpg";
		//delete thumb
		if(file_exists($CLIENT_UPLOAD_DIR . "/" . $THUMBNAIL_DIR . "/" . $thumbName)){
			@unlink($CLIENT_UPLOAD_DIR . "/" . $THUMBNAIL_DIR . "/" . $thumbName);
		}
		//delete main file
		if(file_exists($CLIENT_UPLOAD_DIR . "/" . $r["sFilename"])){
			@unlink($CLIENT_UPLOAD_DIR . "/" . $r["sFilename"]);
		}
	}
	
	if($CLIENTS->delete($id)){
		$info = "Client removed successfully at " . date("h:m:s a");	
		/* remove permissions */
		$sql = "delete from " . $PERM_TABLE . " where sLocation='" . $URL_CLIENTS . "?id=$id'";
		$res = mysql_query($sql);
		
		//reload permissions!
		$sesUser->loadPermissions();
		
		/* DELETE ALL JOBS!!! */
		$sql = "select * from " . $JOBS_TABLE . " where iCal='" . $id . "'";
		$res = mysql_query($sql);
		while($r = mysql_fetch_array($res)){
			/*
			** DELETE A JOB
			**
			** 1. Remove From Jobs Table
			** 2. Remove Files
			** 3. Remove Notes
			*/
			
			$id = $r["id"];
			
			/*
			** 1. Remove from jobs table
			*/
			
			if($JOBS->delete($id)){
				//$info = "Job removed successfully at " . date("h:m:s a");	
				
				/*
				** 2. DELETE FILE(s)
				**
				** 2.1. Load file info
				** 2.2. Delete db info
				** 2.3. Delete thumbnail (if any)
				** 2.4. Delete file (if any?)
				**
				*/
				$sql = "select * from " . $IMAGES_TABLE . " where iRefId='" . $id . "'";
				$res = mysql_query($sql);
				if(mysql_num_rows($res) > 0){
				 	while($r = mysql_fetch_array($res)){
						$IMAGES->delete($r["id"]);
						
						$thumbName = (strrpos($r["sFilename"],".")) ? substr($r["sFilename"], 0, strrpos($r["sFilename"], ".")) . "(" . substr($r["sFilename"], (strrpos($r["sFilename"], ".") + 1)) . ")"  : $r["sFilename"];
						$thumbName .= ".jpg";
						//delete thumb
						if(file_exists($CLIENT_UPLOAD_DIR . "/" . $THUMBNAIL_DIR . "/" . $thumbName)){
							@unlink($CLIENT_UPLOAD_DIR . "/" . $THUMBNAIL_DIR . "/" . $thumbName);
						}
						//delete main file
						if(file_exists($CLIENT_UPLOAD_DIR . "/" . $r["sFilename"])){
							@unlink($CLIENT_UPLOAD_DIR . "/" . $r["sFilename"]);
						}
						
						
					}
				}
				
				/*
				** 3. DELETE NOTE(s)
				*/
				$sql = "select id from " . $NOTES_TABLE . " where iRefId='" . $id . "'";
				$res = mysql_query($sql);
				if(mysql_num_rows($res) > 0){
					while($r = mysql_fetch_array($res)){
						$NOTES->delete($r["id"]);
					}	
				}
			}
			
		}
		
		//reset id
		$id = 0;
	}
}else if($action == "setClient"){
	//just returned from chooser
	unset($_SESSION["chooserField"]);
	unset($_SESSION["ref"]);
	unset($_SESSION["titlebar"]);
	session_unregister("chooserField");
	session_unregister("ref");
	session_unregister("titlebar");
	$id = $_POST["chooserId"];
}


if(!$id > 0){
	//load chooser
	/* redirect to calendar id request page, pass array of calendars to make things quicker */
	$ref = ami_interface_url('clients.edit');
	$titlebar = "PLEASE CHOOSE A CLIENT";
	$abc = preg_split('//', "ABCDEFGHIJKLMNOPQRSTUVWXYZ", -1, PREG_SPLIT_NO_EMPTY);
	$buttons = "<a href=\"clients.chooser.php?alpha=\" target=\"frClients\">A-Z</a>&nbsp;&nbsp;\n";
	$buttons .= "<a href=\"clients.chooser.php?alpha=num\" target=\"frClients\">#</a> \n";
	foreach($abc as $letter){
		$buttons .= "<a href=\"clients.chooser.php?alpha=$letter\" target=\"frClients\">$letter</a> \n";
	}
	$chooserField 	= 	"<tr><td colspan=\"2\">&nbsp;</td></tr>";
	$chooserField 	.= "<tr><td colspan=\"2\">&nbsp;</td></tr>";
	$chooserField 	.= "<tr><td colspan=\"2\"><iframe name=\"frClients\" src=\"clients.chooser.php\"  frameborder=\"0\" marginwidth=\"0\" marginheight=\"0\" width=\"100%\" height=\"300\" scrolling=\"yes\"></iframe></td></tr>";
	$chooserField	.= "<tr><td>&nbsp;</td><td style=\"text-align:right\">" . $buttons . "</td></tr>";
	$chooserField 	.=	"<tr><td colspan=\"2\"><input type=\"hidden\" name=\"chooserId\" value=\"\"></td></tr>";
	$chooserField	.= "<tr><td colspan=\"2\"><input type=\"hidden\" name=\"action\" value=\"setClient\"></td></tr>\n";
	session_register("chooserField");
	session_register("ref");
	session_register("titlebar");

	echo '<meta http-equiv="Refresh" content="0; ' . $URL_CHOOSER . '&info=' . urlencode($info) . '" />';
	//header("location: $URL_CHOOSER&info=" . urlencode($info));
	exit();	
}
				

//always just show this delete button because a non-admin cannot access this page anyways!
$btnDelete = "&nbsp;&nbsp;&nbsp;&nbsp;<input type=\"button\" name=\"btnDel\" value=\"DELETE\" onclick=\"del('" . $id . "')\">";



/* If custom fields are included add the name of the field to the hidden fieldlist[] html array and the ->save() method will store it */
$output = $CLIENTS->edit("$id");

if(strlen($info) > 0){
	$info = formatInfo($info);
}

?>

<link rel="stylesheet" type="text/css" href="assets/css/fine-uploader-new.css">
<script type="text/javascript" src="jquery.js"></script>
<script type="text/javascript" src="assets/js/jquery.fine-uploader.min.js"></script>

<script language="javascript">

function del(id){
	if(confirm("Are you sure you want to delete this client?\n\nWARNING: ALL JOBS / FILES WILL BE DELETED!!!")){
		url = "<?php echo ami_interface_url('clients.edit'); ?>&action=del&id=" + id
		location.replace(url)
	}
}

</script>

<form name="frmEdit" enctype="multipart/form-data" method="post" action="">
<table width="98%" cellpadding="2" cellspacing="0" border="0">
<tr>
	<td colspan="2" style="vertical-align:bottom">
	<a href="javascript:history.go(-1)">> PREVIOUS PAGE</a>
	<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
	          <tr> 
	            <td><img src="images/spacer.gif" width="1" height="72" border="0"></td>
	           </tr>
	           <tr>
	            <td style="text-align:left;vertical-align: bottom"><?=$info?></td>
	        </tr>
	      </table></td>
</tr>
<tr>
	<td class="COLHEAD" colspan="2" style="background-image:url(images/nav_bg.gif);background-repeat:repeat-x;" nowrap>EDIT CLIENT</td>
</tr>
<?=$output?>
<tr>
	<td>&nbsp;</td>
	<td align="left" width="100%"><input type="button" name="btnSubmit" value="SAVE" onclick="frmEdit.submit()"><?=$btnDelete?></td>
</tr>
</table>
<input type="hidden" name="action" value="save">
<input type="hidden" name="id" value="<?=$id?>">
</form>

<?php require_once('uploader.template_layout.php'); ?>

<script type="text/javascript">
	var uploader = new qq.FineUploader({
        element: document.getElementById("uploader"),
        sizeLimit : 256000000,
        multiple : false,
        request: {
            endpoint: "uploader.php"
        },
        deleteFile: {
            enabled: false,
            endpoint: "uploader.php"
        },
        chunking: {
            enabled: true,
            concurrent: {
                enabled: true
            },
            success: {
                endpoint: "uploader.php?done"
            }
        },
        resume: {
            enabled: true
        },
        retry: {
            enableAuto: true,
            showButton: true
        },
        callbacks: {
        	onComplete : function(id, fileName, response, xhr) {

        		//console.log(response);
        		jQuery('input[name="F_sFilename"]').val(response.uuid + '/' + fileName);

        	}
        }
    });
</script>
