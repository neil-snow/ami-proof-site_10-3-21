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
$info				= (isset($_REQUEST["info"])) ? $_REQUEST["info"] : "";
$alpha			= $_REQUEST["alpha"];

/* Double check both logged in vars to ensure no tampering! */
if(!$LOGGEDIN || !$sesUser->LOGGEDIN){
		header("location: clients.relogin.php?info=" . urlencode("Your login has been unexpectedly terminated."));
	exit();
}

/* Make sure they have 'master' privelages to be at this page */
checkAdmin();

//////////////////////////////////

/* Check for actions */
if($action == "del" && $_REQUEST["id"] >= 0){
	if($CLIENTS->delete($id)){
		$info = "Client removed successfully at " . date("h:m:s a");	
		/* remove permissions */
		$sql = "delete from " . $PERM_TABLE . " where sLocation='" . $URL_CLIENTS . "?id=$id'";
		$res = mysql_query($sql);
		
		//reload permissions!
		$sesUser->loadPermissions();
	}
}


array_push($CLIENTS_NO_EDIT,"sFilename");
array_push($CLIENTS_NO_EDIT,"sAddress1");
array_push($CLIENTS_NO_EDIT,"sAddress2");
array_push($CLIENTS_NO_EDIT,"sZip");
array_push($CLIENTS_NO_EDIT,"sCountry");
array_push($CLIENTS_NO_EDIT,"sShipAddress1");
array_push($CLIENTS_NO_EDIT,"sShipAddress2");
array_push($CLIENTS_NO_EDIT,"sShipZip");
array_push($CLIENTS_NO_EDIT,"sShipCity");
array_push($CLIENTS_NO_EDIT,"sShipState");
array_push($CLIENTS_NO_EDIT,"sShipCountry");
array_push($CLIENTS_NO_EDIT,"sDesc");
//array_push($CLIENTS_NO_EDIT,"sContact");
//array_push($CLIENTS_NO_EDIT,"sContactEmail");

$CLIENTS = new collection($CLIENTS_TABLE, "SQLobj", $CLIENTS_NO_EDIT, $CLIENTS_DESC);



if($alpha == "num"){
	$sqlParam = "select * from " . $CLIENTS_TABLE . " where sName REGEXP '^[^a-zA-Z].*' order by sName ASC";
}else if(strlen($alpha) == 1){
	$sqlParam = "select * from " . $CLIENTS_TABLE . " where sName like '" . $alpha . "%' order by sName ASC";
}else{
	$sqlParam = "select * from " . $CLIENTS_TABLE . " order by sName ASC";
}


/* LOAD ENTIRE CALENDAR DB INFO */
$CLIENTS->loadSQL($sqlParam);


$out = $CLIENTS->printRows("<td><input type=\"radio\" name=\"cId\" value=\"r[id]\" onclick=\"parent.document.frmEdit.chooserId.value=this.value;\"></td>%%%");


if(strlen($info)>0)
	$info = formatinfo($info);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title> CLIENT MANAGEMENT </title>
<link rel="stylesheet" type="text/css" href="<?=$STYLESHEET?>">
<script language="javascript">

function del(id,name){
	
	if(confirm("Are you sure you want to delete?\n\nClient: " + name)){
		url = "<?php echo ami_interface_url('clients.manage'); ?>&action=del&id=" + id
		location.replace(url)
	}

	
}

</script>

<style type="text/css">
	@media screen and (max-width: 768px) {

		.floatThead-table { display: none; }

		  table.chooser-table {
		    border: 0;
		  }

		  table.chooser-table caption {
		    font-size: 1.3em;
		  }
		  
		  table.chooser-table thead {
		    border: none;
		    clip: rect(0 0 0 0);
		    height: 1px;
		    margin: -1px;
		    overflow: hidden;
		    padding: 0;
		    position: absolute;
		    width: 1px;
		  }
		  
		 table.chooser-table tr {
		    border-bottom: 3px solid #ddd;
		    display: block;
		    margin-bottom: .625em;
		    background-color: #f8f8f8;
  			border: 1px solid #ddd;
		  }
		  
		  table.chooser-table td {
		  	border-top: 0 !important;
		  	border-right: 0 !important;
		    border-bottom: 1px solid #ddd;
		    display: block;
		    text-align: right !important;
		    padding: 4px;
		    font-size: 12px !important;
		    clear: both;
		  }
		  
		  table.chooser-table td::before {
		    /*
		    * aria-label has no advantage, it won't be read inside a table
		    content: attr(aria-label);
		    */
		    content: attr(data-label);
		    float: left;
		    font-family: 'Gotham-Bold';
		    text-transform: uppercase;
		  }

		  table.chooser-table td::after {
		  	 content: "\00a0\00a0";
		  }
		  
		  table.chooser-table td:last-child {
		    border-bottom: 0;
		  }
	}
</style>

</head>
<body marginheight="0" marginwidth="0" bgcolor="#FFFFFF" style="padding:0px;background-color:#FFFFFF">
<table width="98%" cellpadding="2" cellspacing="0" border="0" class="chooser-table">
	<thead>
		<tr>
			<td class="COLHEAD" style="background-color:#FFFFFF" nowrap>&nbsp;</td>
			<td class="COLHEAD" style="background-color:#FFFFFF" nowrap>CLIENT NAME</td>
			<td class="COLHEAD" style="background-color:#FFFFFF" nowrap>CITY</td>
			<td class="COLHEAD" style="background-color:#FFFFFF" nowrap>STATE</td>
			<td class="COLHEAD" style="background-color:#FFFFFF" nowrap>CONTACT</td>	
			<td class="COLHEAD" style="background-color:#FFFFFF" nowrap>PHONE</td>
			<td class="COLHEAD" style="background-color:#FFFFFF" nowrap>FLORIDA EMAIL</td>
                        <td class="COLHEAD" style="background-color:#FFFFFF" nowrap>EMAIL</td>
			<td class="COLHEAD" style="background-color:#FFFFFF" nowrap>FAX</td>
			<td class="COLHEAD" style="background-color:#FFFFFF" nowrap>TRACKING EMAIL</td>
			<td class="COLHEAD" style="background-color:#FFFFFF" nowrap>RESELLER</td>
		</tr>
	</thead>
	<tbody>
		<?=$out?>
	</tbody>
</table>

</body>
</html>