<?PHP

if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
    $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $location);
    exit;
}

include("inc/db.inc.php");
include("inc/classes.inc.php");
include("inc/jobs.conf.php");
session_start();

$username		= $_POST["username"];
$passwd			= $_POST["passwd"];
$action			= $_REQUEST["action"];

$LOGGEDIN		= $_SESSION["LOGGEDIN"];
$sesUser			= $_SESSION["sesUser"];

if($action == "login"){
		//reset session vars, overwrite any existing values
		$LOGGEDIN = FALSE;
		$sesUser = new user($username,$passwd);
				
		if($sesUser->LOGGEDIN == TRUE){
			//register with session
			session_register("sesUser");
			session_register("LOGGEDIN");
			
			$_SESSION['LOGGEDIN'] = TRUE;
			//load permissions
			$sesUser->loadPermissions();
			$search = "s:" . strlen($sesUser->fields["id"]) . ":\"" . $sesUser->fields["id"] . "\";";
			$sql = "select id from " . $GLOBALS["GROUP_TABLE"] . " where wName='" . $ADMIN_GROUP . "' and csvMembers like '%" . $search . "%'";
			$res = mysql_query($sql);
			if(mysql_num_rows($res)>0)
				$sesUser->ADMIN = TRUE;
			else
				$sesUser->ADMIN = FALSE;
			
			$permKeys = $sesUser->perms;
			$calIds = array();
			foreach($permKeys as $k=>$j){
				$v = $j["view"];
				if(sizeof($v) > 0){	
					/* check for initial url in location, we only care about calendar urls */
					if(substr($k,0,strlen($URL_CLIENTS)) == $URL_CLIENTS){
						/* grab id of client user can add too */
						if(preg_match("/[.]*id=([0-9]+)/s",$k,$matches)){
							array_push($calIds, $matches[1]);
						}
					}
				}
			}
			
			//if this user is only associated with one client set that client id in the session
			
			$clientID = null;
			if(sizeof($calIds) == 1){
				$clientID = $calIds[0];
			}
			session_register("clientID");
			
			$query = "select count(*) from auth where name = '$username' and pass = '$passwd'";
			$result = mysql_query($query);
			
			$count = mysql_result($result,0,0);
			if($count>0){
				session_register("username");
				$dual = TRUE;
				$info = "Please Choose an area";
				header("location: main.interface.php?info=" . urlencode("Welcome back " . $sesUser->fields["sName"] . "."));
				
			}else{
				$_SESSION["sesUser"] = $sesUser;

				header("location: main.interface.php?info=" . urlencode("Welcome back " . $sesUser->fields["sName"] . "."));
				exit();
			}

			
		}else{
		//Check for other login area
			$query = "select count(*) from auth where name = '$username' and pass = '$passwd'";
			$result = mysql_query($query);
			
			
			$count = mysql_result($result,0,0);
			if($count>0){
				session_register("username");
				session_register("LOGGEDIN");
				
				$_SESSION['LOGGEDIN'] = TRUE;
				$_SESSION['username'] = $username;
				header("location: ../clientlogin/welcome.php?user=$username"); //tag on info strin gif you want
				exit();
			}else{
			$info = "ERROR: Invalid Username / Password!";
			//don't register anything with session
			}
		}	
	
}else if($action == "out"){
	session_unregister("sesUser");
	$LOGGEDIN = false;
	session_unregister("LOGGEDIN");
	session_destroy();
	header("location: " . $_SERVER["PHP_SELF"] . "?info=" . urlencode("Logout successful."));
	exit();	
	
}

if($LOGGEDIN) {
	header("Location: main.interface.php");
	exit;
}











if(isset($info) && strlen($info) > 0){
	$info = formatInfo($info);
}




?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">

<html>
<head>
<title> AMI Graphics Online Proof Site </title>
<meta name=viewport content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="<?=$STYLESHEET?>">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/brands.css" integrity="sha384-n9+6/aSqa9lBidZMRCQHTHKJscPq6NW4pCQBiMmHdUCvPN8ZOg2zJJTkC7WIezWv" crossorigin="anonymous">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/fontawesome.css" integrity="sha384-vd1e11sR28tEK9YANUtpIOdjGW14pS87bUBuOIoBILVWLFnS+MCX9T6MMf0VdPGq" crossorigin="anonymous">
<style type="text/css">

	.social-links {
	    vertical-align: middle;
	    padding-top: 15px;
	}

	.social-links a {
		width: 40px;
	    height: 40px;
	    margin-right: 12px;
	    border-radius: 0px;
	    font-size: 30px;
	    line-height: 40px;
	    background: #ffffff;
    	color: #555555;
    	display: inline-block;
    	text-align: center;
	}

	.social-links a i {
		display: inline-block;
		padding-top: 5px;
	}

	.social-links a:hover {
	    background: #00af40 !important;
	    text-decoration: none;
	    color: #fff;
	}

	.login-wrap {
		max-width: 826px;margin: 0 auto;padding: 0 10px;
	}

	.login-header-wrap {
		overflow: hidden;border-bottom: 1px solid #adadad;
	}

	.login-header-wrap .left-header {
		float: left;
	}

	.login-header-wrap .right-header {
		float: right;
	}

	.login-form-wrap {
		overflow: hidden;padding-top: 20px;
	}

	.login-form-wrap .welcome-wrap {
		float: left;width: 46%;
	}

	.login-form-wrap .welcome-wrap h1 {
		font-family: 'Gotham-Medium';font-size: 30px;padding-bottom: 15px;
	}

	.login-form-wrap .welcome-wrap p {
		font-size: 16px;line-height: 25px;font-family: 'Gotham-Book';text-align: justify;
	}

	.login-form-wrap .form-wrap {
		float: right;width: 46%;
	}

	.login-form-wrap .form-wrap h1 {
		font-family: 'Gotham-Medium';font-size: 30px;border-bottom: 1px solid #adadad;padding-bottom: 15px;
	}

	.login-form-wrap .form-wrap input {
		padding: 0 10px;width: 100%;height: 40px;line-height: 40px;border: 1px solid #575858;border-radius: 3px;
	}

	.login-form-wrap .form-wrap .submit {
		background: #01a951;text-align: center;width: 100%;height: 40px;line-height: 40px;border-radius: 3px;color: #fff;font-size: 22px;border: 0;
	}

	.footer-wrap {
		background: #424244;padding: 60px 0;margin-top: 40px;
	}

	.footer-inner {
		max-width: 826px;margin: 0 auto;color: #fff;line-height: 20px;overflow: hidden;padding: 0 10px;
	}

	.footer-inner .address-wrap {
		float: left;width: 45%;overflow: hidden;
	}

	.footer-inner .address-single-wrap {
		float: left;width: 48%;font-family: 'Gotham-Book';font-size: 14px;line-height: 20px;
	}

	.footer-inner .address-single-wrap span {
		color: #01a951;font-size: 16px;font-family: 'Gotham-Medium';display: block;padding-bottom: 5px;
	}

	.footer-inner .footer-right {
		float: right;width: 44%;
	}

	@media only screen and (max-width: 766px) {

		.left-header {
			width: 100%;
		}

		.right-header {
			display: none;
		}

		.left-header img {
			max-width: 100%;
		}

		.login-form-wrap .welcome-wrap {
			width: 80%;
			float: none;
			margin: 0 auto;

		}

		.login-form-wrap .form-wrap {
			width: 80%;
			float: none;
			margin: 0 auto;
		}

		.footer-inner .address-wrap {
			width: 100%;
			padding-bottom: 40px;
		}

		.footer-inner .footer-right {
			width: 100%;
		}
	  
	}

</style>
</head>
<body style="background: #fff;padding: 0;margin: 0;">
<form name="frLogin" action="<?=$_SERVER["PHP_SELF"]?>" method="post">

	<div class="login-wrap">

		<div class="login-header-wrap">
			<div class="left-header">
				<img src="assets/images/ami-header-left.jpg" alt="" />
			</div>
			<div class="right-header">
				<img src="assets/images/ami-header-right.jpg" alt="" />
			</div>
		</div>

		<div class="login-form-wrap">
			<div class="welcome-wrap">
				<h1>WELCOME</h1>
				<p>Welcome to the AMI Graphics Proof Site, our innovative, custom-built online proofing and production assistant. This tool allows you to log in from any internet connected device to upload files, view and request changes to proofed files, approve files for print, and track your signage order once shipped.</p>
			</div>
			<div class="form-wrap">


<!-- MAINTENANCE MESSAGE -->
<!-- <h1>CLIENT LOGIN</h1> -->
<!-- table width="98%" cellpadding="4" cellspacing="0" border="0">
					<tr>
						<td width="100" style="font-size: 16px;line-height: 40px;">WE ARE CURRENTLY UNDERGOING MAINTENANCE. PLEASE CHECK BACK AT A LATER TIME.</td>
					</tr>
</table -->

<!-- COMMENT OUT ENTIRE CODE AND TABLE STARTING FROM HERE -->
				<h1>CLIENT LOGIN</h1>

				<?=$info?>

				<table width="98%" cellpadding="4" cellspacing="0" border="0">
			

				<? if(!$dual){ ?>
					<tr>
						<td width="100" style="font-size: 16px;line-height: 40px;">USERNAME</td>
						<td ><input type="text" name="username" size="20" maxlength="20"></td>
					</tr>
					<tr>
						<td colspan="2" style="height: 10px;font-size: 10px;"></td>
					</tr>
					<tr>
						<td width="100" style="font-size: 16px;line-height: 40px;">PASSWORD</td>
						<td ><input type="password" name="passwd" size="20" maxlength="20"></td>
					</tr>
					<tr>
						<td colspan="2" style="height: 10px;font-size: 10px;"></td>
					</tr>
					<tr>
						<td colspan="2" align="right"><input type="submit" name="btnSubmit" value="LOGIN" class="submit"></td>
					</tr>
				<? }else{ ?>
					<tr>
						<td colspan="2"><a href="main.interface.php?info=<? echo urlencode("Welcome back " . $sesUser->fields["sName"]."."); ?>">AMI Proofing System</a></td>
					</tr>
					<tr>
						<td colspan="2"><a href=" ../clientlogin/welcome.php?user=<? echo $username; ?>">AMI Amusement Park Gallery</a></td>
					</tr>
				<? }?>
				</table>

<!-- COMMENT OUT TO HERE -->

			</div>
		</div>
	</div>

	<div class="footer-wrap">

		<div class="footer-inner">

			<div class="address-wrap">
				<div class="address-single-wrap">
					<span>NEW HAMPSHIRE</span>
					223 Drake Hill Rd Strafford, NH 03884<br />
					<br />
					Phone: 603-664-7174<br />
					Fax: 603-664-7167<br />
					<a href="https://amigraphics.com/" target="_blank">amigraphics.com</a>
				</div>
				<div class="address-single-wrap">
					<span>FLORIDA</span>
					1302 SW 42nd Avenue Ocala, FL 34474<br />
					<br />
					Phone: 1-800-238-6645<br />
					Fax: 352-629-3263<br />
					<a href="https://amiresellers.com/" target="_blank">amiresellers.com</a>
				</div>
			</div>
			<div class="footer-right">
				<img src="assets/images/ami-footer-logo.png" alt="" width="187" height="62" style="margin-bottom: 10px;" />
				<div style="font-family: 'Gotham-Book';">
					COPYRIGHT <?php echo date('Y'); ?> AMI GRAPHICS. ALL RIGHTS RESERVED.<br />
					<a href="https://amigraphics.com/privacy-policy" target="_blank" style="color: #fff;">PRIVACY POLICY</a> | <a href="https://amigraphics.com/terms-conditions" target="_blank" style="color: #fff;">TERMS & CONDITIONS</a>
				</div>
				<div class="social-links">
					<a target="_blank" href="https://www.facebook.com/pages/AMI-Graphics-LLC/223853474334070"><i class="fab fa-facebook-f"></i></a>
					<a target="_blank" href="https://twitter.com/AMIGraphics"><i class="fab fa-twitter"></i></a>
					<a target="_blank" href="http://www.pinterest.com/amigraphics/"><i class="fab fa-pinterest"></i></a>
					<a target="_blank" href="https://instagram.com/amigraphics/"><i class="fab fa-instagram"></i></a>
					<a target="_blank" href="https://www.linkedin.com/company/ami-graphics"><i class="fab fa-linkedin-in"></i></a>
					<a target="_blank" href="https://www.flickr.com/photos/amigraphics/"><i class="fab fa-flickr"></i></a>
				</div>
			</div>

		</div>

	</div>
	
	<input type="hidden" name="action" value="login">
</form>

<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
 
  ga('create', 'UA-12029970-3', 'auto');
  ga('send', 'pageview');
 
</script>

</body>
</html>
