<?PHP

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
//checkAdmin();

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
<body>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.25/datatables.min.css"/>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.25/datatables.min.js"></script>
<form name="frmEdit" method="post" action="<?=$ref?>">
<table width="98%" cellpadding="2" cellspacing="0" border="0" >
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
<? /*=$output*/ ?>

<!-- <tr>
	<td>&nbsp;</td>
	<td align="left"><input type="button" name="btnSubmit" value="CONTINUE" onclick="frmEdit.submit()"></td>
</tr> -->

<tr>
	<td colspan="2">
		<table id="client_table" width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#EFEFEF">
			<thead>
				<tr>
				<td>ACTION</td>
				<td>CLIENT NAME</td>
				<td>CITY</td>
				<td>STATE</td>
				<td>CONTACT</td>
				<td>PHONE</td>
				<td>FLORIDA EMAIL</td>
				<td>EMAIL</td>
				<td>FAX</td>
				<td>TRACKING EMAIL</td>
				<td>RESELLER</td>
				</tr>
			</thead>	
		</table>	
	</td>
</tr>

</table>
</form>

<?php 
if(!isset($ref) && $ref == ''){
    $ref = "main.interface.php?route=clients.edit";
}
?>
<!--<form id="editClientForm" name="editClientForm" method="post" action="<?php echo $ref; ?>">
	<input type="hidden" id="chooserId" name="chooserId" value="" />
	<input type="hidden" id="action" name="action" value="setClient" />
</form>-->

<!--<form id="deleteClientForm" name="deleteClientForm" method="post" action="<?=$ref?>">
	<input type="hidden" id="id" name="id" value="" />
	<input type="hidden" id="action" name="action" value="del" />
</form>-->

<form id="selectClientForm" name="selectClientForm" method="post" action="<?php echo $ref; ?>">
	<input type="hidden" id="chooserId" name="chooserId" value="" />
	<input type="hidden" id="action" name="action" value="setClient" />
</form>
<style type="text/css">
	#client_table_wrapper{
		width:98% !important;
		overflow:scroll;
	}
</style>
<script type="text/javascript" >
	$(document).ready(function(){
		$('#client_table').DataTable({
			'responsive': true,
			'processing': true,
			'serverSide': true,
			"pageLength": 25,
			"lengthMenu": [ [25, 50, 100, -1], [25, 50, 100, "All"] ],
			'ajax': {
				'url':'controllers/client.php',
				'type': 'POST',
				'data': {action:'client_list', ref : '<?php echo $ref; ?>'}
			},
			'columns': [
				{ data: 'action', orderable: false, targets: -1 },
				{ data: 'sName' },
				{ data: 'sCity' },
				{ data: 'sState' },
				{ data: 'sContact' },
				{ data: 'sContactPhone' },
				{ data: 'sFloridaContactEmail' },
				{ data: 'sContactEmail' },
				{ data: 'sContactFax' },
				{ data: 'sTrackingEmail' },
				{ data: 'Reseller' },
			]
		});
	});

//	function editClient(id){
//		$("#editClientForm #chooserId").val(id);
//		$("#editClientForm").submit();
//	}

	function selectClient(id){
		$("#selectClientForm #chooserId").val(id);
		$("#selectClientForm").submit();
	}

//	function deleteClient(id){
//		var conf = confirm("Are you sure you want to delete this client?\n\nWARNING: ALL JOBS / FILES WILL BE DELETED!!!");
//		if(conf){
//			$("#deleteClientForm #id").val(id);
//			$("#deleteClientForm").submit();
//		}
//	}
</script>