<?PHP

/*******************************/
/******** PAGE SETUP ***********/
/*******************************/

$LOGGEDIN		= $_SESSION["LOGGEDIN"];
$sesUser			= $_SESSION["sesUser"];
$action			= $_REQUEST["action"];
$archive		= $_REQUEST["archive"];
$jobFilter		= (!isset($_REQUEST["jFil"])) ? "#0" : "#" . $_REQUEST["jFil"];
$jobSQLFilter	= (strlen($jobFilter)>2) ? " t1.sColor='" . $jobFilter . "'" : "1";
$info				= (isset($_REQUEST["info"])) ? $_REQUEST["info"] : "";
$sqo				= (isset($_REQUEST["sqo"])) ? $_REQUEST["sqo"] : "t1.dtStamp";
$alpha			= (isset($_REQUEST["alpha"])) ? $_REQUEST["alpha"] : "";
$sqom				= (substr($sqo,0,5) == "t1.dt") ? "desc" : "asc";
$calId			= (isset($_REQUEST["calId"]) && $_REQUEST["calId"] > 0) ? $_REQUEST["calId"] : 0;
$logo 			= "&nbsp;";
$clientBilling = "&nbsp;";
$clientShipping = "&nbsp;";

if($alpha == "num"){
	$alphaLimit = "and t1.sName REGEXP '^[^a-zA-Z].*'";
}else if(strlen($alpha) == 1){
	$alphaLimit = "and t1.sName like '" . $alpha . "%'";
}else{
	$alphaLimit = "";
}


$sqlParam = "select t1.*, DATE_FORMAT(t1.dtDate,'%c/%e/%y %l:%i %p') as dtStart, DATE_FORMAT(t1.dtStamp,'%c/%e/%y %l:%i %p') as dtRevised, t2.sName as clientName from " . $JOBS_TABLE . " as t1, " . $CLIENTS_TABLE . " as t2 where " . $jobSQLFilter . " and t2.id=t1.iCal " . $alphaLimit . " order by " . $sqo . " DESC";

//$sqlParam 		= "select *, DATE_FORMAT(dtDate,'%c/%e/%y %l:%i %p') as dtStart, DATE_FORMAT(dtStamp,'%c/%e/%y %l:%i %p') as dtRevised from " . $JOBS_TABLE . " where " . $jobSQLFilter . " order by dtStamp desc";
/* Double check both logged in vars to ensure no tampering! */

if(!$LOGGEDIN || !$sesUser->LOGGEDIN){
		header("location: clients.relogin.php?info=" . urlencode("Your login has been unexpectedly terminated."));
	exit();
}

/* Make sure they have 'master' privelages to be at this page */
//checkAdmin();

//////////////////////////////////

/* CHECK FOR ACTIONS */

if($action == "delJob" && $_REQUEST["id"] > 0){
	/*
	** DELETE A JOB
	**
	** 1. Remove From Jobs Table
	** 2. Remove Files
	** 3. Remove Notes
	*/
	
	$id = $_REQUEST["id"];
	
	/*
	** 1. Remove from jobs table
	*/
	
	if($JOBS->delete($id)){
		$info = "Job removed successfully at " . date("h:m:s a");	
		
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
				
				/*
				** 3. DELETE NOTE(s)
				*/
				$sql = "select id from " . $NOTES_TABLE . " where iRefId='" . $r["id"] . "'";
				$res = mysql_query($sql);
				if(mysql_num_rows($res) > 0){
					while($r = mysql_fetch_array($res)){
						$NOTES->delete($r["id"]);
					}	
				}
			}
		}
		
		
	}else{
		$info = "Error: The job could not be removed.";
	}
	header("location: " . $_SERVER["PHP_SELF"] . "?info=" . urlencode($info));
	exit(); 
}else if($action == "chooseClient"){
	 //load chooser
	/* redirect to calendar id request page, pass array of calendars to make things quicker */
	$ref = ami_interface_url('jobs.manage');
	$titlebar = "PLEASE CHOOSE A CLIENT";
	if($sesUser->ADMIN){
	$abc = preg_split('//', "ABCDEFGHIJKLMNOPQRSTUVWXYZ", -1, PREG_SPLIT_NO_EMPTY);
	$buttons = "<a href=\"clients.chooser.php?alpha=\" target=\"frClients\">A-Z</a>&nbsp;&nbsp;\n";
	$buttons .= "<a href=\"clients.chooser.php?alpha=num\" target=\"frClients\">#</a> \n";
	foreach($abc as $letter){
		$buttons .= "<a href=\"clients.chooser.php?alpha=$letter\" target=\"frClients\">$letter</a> \n";
	}
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
	//header("location: $URL_CHOOSER");
	echo '<meta http-equiv="refresh" content="0; url=' . $URL_CHOOSER . '">';
	exit();	
}else if($action == "setClient"){
	//just returned from chooser
	unset($_SESSION["chooserField"]);
	unset($_SESSION["ref"]);
	unset($_SESSION["titlebar"]);
	session_unregister("chooserField");
	session_unregister("ref");
	session_unregister("titlebar");
		
	$calId = $_POST["chooserId"];
}


array_push($JOBS_NO_EDIT,"sColor");
array_push($JOBS_NO_EDIT,"tNotes");
array_push($JOBS_NO_EDIT,"bLocked");
array_push($JOBS_NO_EDIT,"dtRevised");
array_push($JOBS_NO_EDIT,"dtDate");
array_push($JOBS_NO_EDIT,"dtStart");
array_push($JOBS_NO_EDIT,"clientName");
$JOBS = new collection($JOBS_TABLE, "SQLobj", $JOBS_NO_EDIT, $JOBS_DESC);


/*if($calId <= 0 && $sesUser->ADMIN){
    $ref = ami_interface_url('jobs.manage');
    session_register("ref");
    echo '<meta http-equiv="refresh" content="0; url=' . $URL_CHOOSER . '">';
    exit();
}*/


/* CALENDAR DROP DOWN MENU */
/* parse permissions array for current user */
$permKeys = $sesUser->perms;
$calIds = array();
foreach($permKeys as $k=>$j){
	$v = $j["view"];
	if(sizeof($v) > 0){	
		/* check for initial url in location, we only care about calendar urls */
		if(substr($k,0,strlen($URL_CLIENTS)) == $URL_CLIENTS){
			/* grab id of calendar user can add too */
			if(preg_match("/[.]*id=([0-9]+)/s",$k,$matches)){
				array_push($calIds, $matches[1]);
			}
		}
	}
}
//load calendar drop down, or auto-set calId if only one calendar is visible
$multiClient = "";
if(sizeof($calIds) > 1){
	//$calOut = "CLIENT: <select name=\"calId\">";
	//$calOut .= "\n\t<option value=\"0\" " . (($calId == 0) ? "selected" : "") . ">All Clients</option>";
	$multiClient = "<a href=\"" . ami_interface_url('jobs.manage') . "&action=chooseClient\">&gt;&nbsp;CHOOSE CLIENT</a>";
	$sqlIds = "";
	foreach($calIds as $v){
		$sqlIds .= " or iCal='$v'";
	//	$sql = "select sName from $CLIENTS_TABLE where id='$v'";
	//	$res = mysql_query($sql);
	//	$calOut .= "\n\t<option value=\"$v\" " . (($calId == $v) ? "selected" : "") . ">" . mysql_result($res,0) . "</option>";
		
	}
	//$sqlParam = "select *, DATE_FORMAT(dtDate,'%c/%e/%y %l:%i %p') as dtStart, DATE_FORMAT(dtStamp,'%c/%e/%y %l:%i %p') as dtRevised from " . $JOBS_TABLE . " where " . $jobSQLFilter . " and (" . substr($sqlIds,4) . ") order by dtStamp desc";
	if ($archive == 1) {
		$sqlParam = "select t1.*, DATE_FORMAT(t1.dtDate,'%c/%e/%y %l:%i %p') as dtStart, DATE_FORMAT(t1.dtStamp,'%c/%e/%y %l:%i %p') as dtRevised, t2.sName as clientName from " . $JOBS_TABLE . " as t1, " . $CLIENTS_TABLE . " as t2 where " . $jobSQLFilter . " and (" . substr($sqlIds,4) . ") and t2.id=t1.iCal " . $alphaLimit . " AND (DATE_FORMAT(t1.dtStamp,'%Y') < '2011') order by " . $sqo . " DESC";
	} else {
		$sqlParam = "select t1.*, DATE_FORMAT(t1.dtDate,'%c/%e/%y %l:%i %p') as dtStart, DATE_FORMAT(t1.dtStamp,'%c/%e/%y %l:%i %p') as dtRevised, t2.sName as clientName from " . $JOBS_TABLE . " as t1, " . $CLIENTS_TABLE . " as t2 where " . $jobSQLFilter . " and (" . substr($sqlIds,4) . ") and t2.id=t1.iCal " . $alphaLimit . " AND (DATE_FORMAT(t1.dtStamp,'%Y') >= '2011') order by " . $sqo . " DESC";
	}
	//$calOut .= "</select>";
	
}else if(sizeof($calIds) == 1){
	//$calOut = "<div style=\"visibility:hidden\"><select name=\"calId\"><option value=\"" . $calIds[0] . "\">[ No Choices Avail. ]</option></select></div>";
	$calId = $calIds[0];
}	

/*
** LOAD JOB FILTER DROP DOWN
*/


$jobStatus = "<select name='jobFilter' id='jobFilter'>";
$jobStatus .= "<option value='-1'>All Jobs</option>";
$jobStatus .= "<option value='339900' " . (($jobFilter == "#339900") ? "selected" : "") . ">Open</option>";
$jobStatus .= "<option value='000000' " . (($jobFilter == "#000000") ? "selected" : "") . ">Closed</option>";

$jobStatus .= "</select>";



if(isset($calId) && $calId > 0){
	/*
	** LOAD CLIENT INFO (a single client is visible)
	*/
	$sql = "select * from $CLIENTS_TABLE where id='" . $calId . "'";
	$res = mysql_query($sql);
	if(mysql_num_rows($res)>0){
		$r = mysql_fetch_array($res);
		$clientBilling = "<strong class='gb-bold billing-address-field'>BILLING ADDRESS:</strong><br>\n";
 		$clientBilling .= "<table class=\"billing-address-field\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n<tr>";
 		$clientBilling .= "<td nowrap class='head-address'>\n";
		$clientBilling .= "<strong>" . $r["sName"] . "</strong><br>\n";
		$clientBilling .= $r["sAddress1"] . "<br>\n";
		if(strlen($r["sAddress2"] > 0))
			$clientBilling .= $r["sAddress2"] . "<br>\n";
		$clientBilling .= $r["sCity"] . ", " . $r["sState"] . " " . $r["sZip"] . "<br><br>\n";
		$clientBilling .= "Description:<br>" . $r["sDesc"] . "<br><br>\n";
		$clientBilling .= "</td></tr></table>\n";
		
		$clientShipping = "<strong class='gb-bold shipping-address-field'>SHIPPING ADDRESS:</strong><br>\n";
 		$clientShipping .= "<table class=\"shipping-address-field\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n<tr>";
 		$clientShipping .= "<td nowrap  class='head-address'>\n";
		$clientShipping .= "<strong>" . $r["sName"] . "</strong><br>\n";
		$clientShipping .= $r["sShipAddress1"] . "<br>\n";
		if(strlen($r["sShipAddress2"] > 0))
			$clientShipping .= $r["sShipAddress2"] . "<br>\n";
		$clientShipping .= $r["sShipCity"] . ", " . $r["sShipState"] . " " . $r["sShipZip"] . "<br><br>\n";
		$clientShipping .= "</td></tr></table>\n";
		
		
		
		if(strlen($r["sFilename"]) > 0 && file_exists($CLIENT_UPLOAD_PATH . "/" . $r["sFilename"])){
			$logo = "<img class= \"client-logo-mb\" src=\"" . $CLIENT_UPLOAD_PATH . "/" . $r["sFilename"] . "\" border=\"0\">";
		}	
		
	
		
		/* ami contact output */
		$amiContact = "<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
      $amiContact	.= "<tr><td style=\"padding:0px;\" class='head-address'><strong class='gb-bold'>YOUR AMI CONTACT IS</strong></td></tr>\n";
      $amiContact .= "<tr><td style=\"padding-bottom:0px;\" class='head-address'>" . $r["sContact"] . "</td></tr>\n";
       if ($r["sFloridaContactEmail"]) {
   		$amiContact .= "<tr><td style=\"padding-top:0px;padding-bottom:0px\" class='head-address'><a href=\"mailto:" . $r["sFloridaContactEmail"] . "\">" . $r["sFloridaContactEmail"] . "</a></td></tr>\n";
	    } else {

		    if ($r["sContactEmail"]) {
		   		$amiContact .= "<tr><td style=\"padding-top:0px;padding-bottom:0px\"  class='head-address'><a href=\"mailto:" . $r["sContactEmail"] . "\">" . $r["sContactEmail"] . "</a></td></tr>\n";
		    }

		}
      $amiContact .= "<tr><td style=\"padding-top:0px;\"  class='head-address'>" . $r["sContactPhone"] . "</td></tr>\n";
	  //$amiContact .= "<tr><td style=\"padding-top:0px;\">Fax: 603-664-7167</td></tr>\n";
      $amiContact	.= "</table>";
		
	}else{
		$clientBilling = "Client info is not currently available."; 
	}
}


/* LOAD JOB OUTPUT */

if(sizeof($calIds) > 0){
	// Track where the user is coming from when editing a job
	$_SESSION['from_admin'] = false;

	if($calId > 0){
		
//		$sqlParam = "select t1.*, DATE_FORMAT(t1.dtDate,'%c/%e/%y %l:%i %p') as dtStart, DATE_FORMAT(t1.dtStamp,'%c/%e/%y %l:%i %p') as dtRevised from " . $JOBS_TABLE . " as t1 where " . $jobSQLFilter . " and t1.iCal='" . $calId . "' " . $alphaLimit . " order by " . $sqo . " " . $sqom;
//		
//		$JOBS->loadSQL($sqlParam);
//		$out = $JOBS->printRows("<td data-label='Actions'> <a href=\"" . ami_interface_url('jobs.edit') . "&id=r[id]&cId=r[iCal]\">View / Edit</a></td><td data-label='Color'><span style=\"width:12px;height:12px;font-size:12px;background-color:r[sColor]\">&nbsp;</span></td>%%%<td data-label='Start Date'>r[dtStart]</td><td data-label='Last Update'>r[dtRevised]</td>");
		$clientHeader = "";
	}else{
		
//		$JOBS->loadSQL($sqlParam);
//
//		$out = $JOBS->printRows("<td data-label='Actions'> <a href=\"" . ami_interface_url('jobs.edit') . "&id=r[id]&cId=r[iCal]\">View / Edit</a></td><td data-label='Client'>r[clientName]</td><td data-label='Color'><span style=\"width:12px;height:12px;font-size:12px;background-color:r[sColor]\">&nbsp;</span></td>%%%<td data-label='Start Date'>r[dtStart]</td><td data-label='Last Update'>r[dtRevised]</td>");
		$clientHeader = "<td class=\"COLHEAD\" style=\"background-color:#FFFFFF;\" nowrap>"
                        //. "<a href=\"" . $_SERVER["PHP_SELF"] . "?alpha=" . $alpha . "&sqo=clientName\" style=\"color:#000000\">"
                        . "CLIENT"
                        //. "</a>"
                        . "</td>"; 
	}
	
	
}else{
	$info = "Error: Your username does not have permission to do anything. Please contact the webmaster.";
}

if($sesUser->ADMIN){
$abc = preg_split('//', "ABCDEFGHIJKLMNOPQRSTUVWXYZ", -1, PREG_SPLIT_NO_EMPTY);
$buttons = "<a href=\"" . $_SERVER["PHP_SELF"] . "?alpha=\" >A-Z</a>&nbsp;&nbsp;\n";
$buttons .= "<a href=\"" . $_SERVER["PHP_SELF"] . "?alpha=num\">#</a> \n";
foreach($abc as $letter){
	$buttons .= "<a href=\"" . $_SERVER["PHP_SELF"] . "?alpha=$letter\" >$letter</a> \n";
}
}

if(strlen($info) > 0){
	$info = formatInfo($info);
}else
	$info = "&nbsp;";

?>

<script language="javascript">

function pageJump(calId){
	show 		= document.navForm.jobFilter.options[document.navForm.jobFilter.selectedIndex].value
	url = "<?php echo ami_interface_url('jobs.manage'); ?>&calId=" + calId + "&jFil=" + show
	document.location.replace(url)
}

function printIt(){
	if(window.print){
		window.print();
	}else if(parent.right.print){
		parent.right.print();
	}else if(document.print){
		document.print();
	}else{
		alert("Your browser does not support printing from Javascript. \nPlease select print from the file menu.");
	}
}

</script>

<style type="text/css">
        
        .dt-button{
                border-radius: 4px;
                background: #00af40;
                color: #fff;
                padding: 0px 10px 0;
                height: 30px;
                display: inline-block;
                line-height: 22px;
                border: 0;
                text-transform: uppercase;
        }
        
	@media screen and (max-width: 528px) {

		.client-logo-mb { max-width: 50px;height: auto; }

	}

	@media screen and (max-width: 768px) {

		.billing-address-field, .shipping-address-field { display: none; }

		.floatThead-table { display: none; }

		  table.jobs-table {
		    border: 0;
		  }

		  table.jobs-table caption {
		    font-size: 1.3em;
		  }
		  
		  table.jobs-table thead {
		    border: none;
		    clip: rect(0 0 0 0);
		    height: 1px;
		    margin: -1px;
		    overflow: hidden;
		    padding: 0;
		    position: absolute;
		    width: 1px;
		  }
		  
		 table.jobs-table tr {
		    border-bottom: 3px solid #ddd;
		    display: block;
		    margin-bottom: .625em;
		    background-color: #f8f8f8;
  			border: 1px solid #ddd;
		  }
		  
		  table.jobs-table td {
		  	border-top: 0 !important;
		  	border-right: 0 !important;
		    border-bottom: 1px solid #ddd;
		    display: block;
		    text-align: right !important;
		    padding: 4px;
		    font-size: 12px !important;
		  }

		  table.jobs-table td[data-label="Start Date"] {
		  	display: none;
		  }

		  table.jobs-table td[data-label="Last Update"] {
		  	display: none;
		  }

		   table.jobs-table td[data-label="Color"] {
		  	display: none;
		  }
		  
		  table.jobs-table td::before {
		    /*
		    * aria-label has no advantage, it won't be read inside a table
		    content: attr(aria-label);
		    */
		    content: attr(data-label);
		    float: left;
		    font-family: 'Gotham-Bold';
		    text-transform: uppercase;
		  }
		  
		  table.jobs-table td:last-child {
		    border-bottom: 0;
		  }

		  .a-2-z-list {
		  	float: none !important;
		  	width: 100% !important;
		  	text-align: right;
		  	padding-bottom: 10px;
		  }
        }
</style>

<form name="navForm">
		
<table width="98%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td colspan="11" style="vertical-align:bottom">
	<table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
          <tr> 
          	
          	<td valign="bottom" style="vertical-align:top" <?=($multiClient && strlen($clientHeader)=="0")?"style=\"border-right:1px solid #333333\"":""?>>
          		<?=$multiClient?> | <a href="javascript:;" onClick="history.go(-1)">> PREVIOUS PAGE</a>
				<?if(!$sesUser->ADMIN){?>
				<p><b>Actions</b><br>
				&nbsp; <a href="<?php echo ami_interface_url('jobs.manage'); ?>" class="sub">View Jobs</a><br>
				&nbsp; <a href="clients.login.php?action=out" class="sub">Logout</a><br>
				</p>
				<? } ?>
				</td>
          	<td valign="bottom" style="vertical-align:top">
          		<?=$logo?></td>
    			<td>
 					<?=$clientBilling?></td>
				<td>
					<?=$clientShipping?></td>
				<td>
		  			<?=$amiContact?><?if(!$sesUser->ADMIN){?><br /><a href="javascript:void();" onclick="javascript:window.open('address.pop.php','Address','toolbar=no,location=no,directories=no,status=no,scrollbars=no,resizable=no,copyhistory=yes,width=250,height=250,left=150,top=20');return false;">AMI Address</a><? } ?></td>
 			</tr>
 			<tr>
 				<td colspan="4" style="vertical-align:bottom"><?=$info?></td>
 				<td style="text-align:left;vertical-align:bottom" nowrap>
				<?php if($sesUser->ADMIN){?>
                                <table width="100%" cellpadding="0" border="0" cellspacing="0">
       				<tr>
       					<td style="text-align:left">
       						SHOW: <?=$jobStatus?></td>
       					<td style="text-align:right">
<!--        			     		<input type="button" onclick="pageJump('<?=$calId?>')" value="GO">-->
                                                <input type="button" onclick="loadJobTable()" value="GO">
                                        </td>
        			   </tr>

        			</table>
                                &nbsp;<br />
<!--					<a href="<?php echo ami_interface_url('jobs.manage', array('archive' => 1)); ?>">Pre-2011 Jobs</a> | -->
<!--                                        <a href="<?php echo ami_interface_url('jobs.manage'); ?>">Current Jobs</a>-->
<!--                                        <a href="javascript:void();" onClick="loadJobTable(null, true)">My Jobs</a> | -->
                                        <a href="javascript:void();" onClick="loadJobTable(true, null)">Pre-2011 Jobs</a> | 
                                        <a href="javascript:void();" onClick="getCurrentJob()">Current Jobs</a>
                                <?php } ?>
					</td>
		   </tr>
		   <tr>
				<td colspan="5"><a href='#' onclick='printIt(); return false' class="btn">Print Page</a></td>
				
		   </tr>
		   <tr>
<!--		   	<td colspan="5" align="right" valign="bottom" style="text-align:right;vertical-align:bottom"><?=$buttons?></td>-->
		   </tr>
	</table></td>
</tr>
<tr>
	<td style="padding:0px">
		<table width="100%" cellpadding="0" cellspacing="0" border="0" class="jobs-table" id="job_table">
		<thead>
			<tr>
				<td class="COLHEAD" style="background-color:#FFFFFF" nowrap>CLICK TO PREVIEW</td>
                                <?php if($calId <= 0) { ?>
				<?=$clientHeader?>
                                <?php } ?>
				<td class="COLHEAD" style="background-color:#FFFFFF;" nowrap>&nbsp;</td>
				<td class="COLHEAD" style="background-color:#FFFFFF;" nowrap>
<!--                                    <a href="<?=$PHP_SELF?>?alpha=<?=$alpha?>&sqo=sName" style="color:#000000">-->
                                        JOB NAME
<!--                                    </a>-->
                                </td>
				<td class="COLHEAD" style="background-color:#FFFFFF;" nowrap>
<!--                                    <a href="<?=$PHP_SELF?>?alpha=<?=$alpha?>&sqo=dtDate" style="color:#000000">-->
                                        START DATE
<!--                                    </a>-->
                                </td>
				<td class="COLHEAD" style="background-color:#FFFFFF;" nowrap>
<!--                                    <a href="<?=$PHP_SELF?>?alpha=<?=$alpha?>&sqo=dtStamp" style="color:#000000">-->
                                        LAST UPDATE
<!--                                    </a>-->
                                </td>
				
			</tr>
		</thead>
		<?php
                /*=$out*/
                ?>
		</table></td>
</tr>
<tr>
	<!--<td style="border-top: 4x solid #E6E6E6;padding:1px">
		<table width="100%" cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td width="10" style="padding:1px">
				<a href="#" onclick="window.print()"><img src="images/icon_printer.gif" border="0"></a></td>
			<td style="padding:1px;vertical-align:middle">
				<a href="#" onclick="window.print()" style="color:#000000">&nbsp;PRINT THIS PAGE</a></td>
		</tr>
		</table></td>-->
</tr>
</table>
<br>
<br>
</form>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.25/datatables.min.css"/>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.25/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.0/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/2.0.0/js/buttons.html5.min.js"></script>
<script type="text/javascript" >
    $(document).ready(function(){
        loadJobTable();
    });
    
    function loadJobTable(isArchive = null, myJob = null)
    {
        
        $('#job_table').DataTable().destroy();
        
        var job_type = $("#jobFilter option:selected").val();
        $('#job_table').DataTable({
                /*dom: 'Bfrtlip',
                buttons: [
                    {
                        extend: 'excel',
                        text: 'Export to Excel'
                    },
                    {
                        extend: 'pdf',
                        text: 'Export to PDF'
                    },
                    {
                        extend: 'csv',
                        text: 'Export to Csv'
                    }
                ],*/
                destroy: true,
                'responsive': true,
                'processing': true,
                'serverSide': true,
                "pageLength": 25,
                "lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
                'ajax': {
                        'url':'controllers/job.php',
                        'type': 'POST',
                        'data': {action:'job_list', calId:'<?php echo $calId ;?>', job_type:job_type, isArchive:isArchive, myJob:myJob, alpha: '<?php echo isset($_GET['alpha'])?$_GET['alpha']:''; ?>'}
                },
                'columns': [
                        { data: 'action', orderable: false, targets: -1, width: "5%" },
                        <?php if($calId <= 0) { ?>
                        { data: 'clientName', width: "20%"},
                        <?php } ?>        
                        { data: 'sColor', width: "5%"},
                        { data: 'sName', width: "30%"},
                        { data: 'dtStart', width: "20%"},
                        { data: 'dtRevised', width: "20%"},
                ]
        });
    }
    
    function getCurrentJob()
    {
        $('#jobFilter').prop('selectedIndex',0);
        loadJobTable();
    }   
</script>
