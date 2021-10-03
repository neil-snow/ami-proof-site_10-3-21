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

$LOGGEDIN		= $_SESSION["LOGGEDIN"];
$sesUser			= $_SESSION["sesUser"];
$info				= $_REQUEST["info"];

				
/* Double check both logged in vars to ensure no tampering! */
if(!$LOGGEDIN || !$sesUser->LOGGEDIN){
		header("location: clients.relogin.php?info=" . urlencode("Your login has been unexpectedly terminated."));
	exit();
} else {
	$sesUser->loadPermissions();
}



?>
<!DOCTYPE html>
<html>
<head>
<title> AMI Graphics Online Proof Site </title>
<meta name=viewport content="width=device-width, initial-scale=1">
<link rel="stylesheet" type="text/css" href="<?=$STYLESHEET?>">
<script type="text/javascript">
	/* reference popups to window main */
	window.name = "main";
</script>
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/solid.css" integrity="sha384-r/k8YTFqmlOaqRkZuSiE9trsrDXkh07mRaoGBMoDcmA58OHILZPsk29i2BsFng1B" crossorigin="anonymous">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/regular.css" integrity="sha384-IG162Tfx2WTn//TRUi9ahZHsz47lNKzYOp0b6Vv8qltVlPkub2yj9TVwzNck6GEF" crossorigin="anonymous">
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/fontawesome.css" integrity="sha384-4aon80D8rXCGx9ayDt85LbyUHeMWd3UiBaWliBlJ53yzm9hqN21A+o1pqoyK04h+" crossorigin="anonymous">
</head>
<body>
<?if($sesUser->ADMIN){?>

<div class="main-interface" style="overflow: hidden;">
	<div class="col col-main-nav" id="menu">
		<?php require_once('main.navigation.php'); ?>
	</div>
	<div class="col col-main-content" id="main_panel">
		<button class="btn toggle-button">
			<svg class="icon icon-menu-toggle" aria-hidden="true" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 100 100">
				<g class="svg-menu-toggle">
					<path class="line line-1" d="M5 13h90v14H5z"/>
					<path class="line line-2" d="M5 43h90v14H5z"/>
					<path class="line line-3" d="M5 73h90v14H5z"/>
				</g>
			</svg>
		</button>
		<?php 

		//if (isset($_GET['search']) || (isset($_REQUEST['route']) && $_REQUEST['route'] == 'search') ) {
			?>
			<div class="search-wrap" style="background: #fff;width: 98%;">
				<form method="post" action="<?php echo ami_interface_url('search'); ?>" style="padding: 10px;">
					<label>SEARCH</label> <input type="text" name="search_field" placeholder="by client, job, product, proof or filename." style="width: 250px;max-width: 50%;height:22px;line-height: 22px;" />
					<input type="submit" name="submit" value="Submit" class="btn" />
				</form>
			</div>
			<?php
		//}

		$file = 'jobs.manage.frameless';

		if (isset($_REQUEST['route'])) {

			$route = $_REQUEST['route'];

			$allowed_routes = array(
				'jobs.add', 'jobs.edit', 'jobs.upload', 'jobs.manage.admin', 'jobs.manage', 'jobs.chooser',
				'proof.view',
				'user.add', 'user.manage', 'user.edit',
				'group.add', 'group.manage', 'group.edit',
				'clients.add', 'clients.edit', 'clients.chooser',
				'permissions.manage', 'permissions.edit', 'permissions.add',
				'queue.proof', 'queue.print',
				'banner.report', 'adhesive.report',
				'cat.add', 'cat.manage', 'cat.edit',
				'promobanner', 'promobanner.chooser', 'search'
			);

			if (in_array($route, $allowed_routes)) {
				$file = $route . '.frameless';
			}

		}

		require_once($file . '.php'); 

		?>
	</div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/slideout/1.0.1/slideout.min.js"></script>
<script  type="text/javascript">

  var slideout = new Slideout({
    'panel': document.getElementById('main_panel'),
    'menu': document.getElementById('menu'),
    'padding': 180,
    'tolerance': 70
  });

  // Toggle button
  document.querySelector('.toggle-button').addEventListener('click', function() {
    slideout.toggle();
  });
</script>
<? } else { ?>
<div class="main-interface" style="overflow: hidden;">
	<div class="col" style="float:left;width: 100%;">
		<?php 

		$file = 'jobs.manage.frameless';

		if (isset($_REQUEST['route'])) {

			$route = $_REQUEST['route'];

			$allowed_routes = array(
				'jobs.add', 'jobs.edit', 'jobs.upload', 'jobs.manage', 'jobs.chooser', 'jobs.reorder',
			);

			if (in_array($route, $allowed_routes)) {
				$file = $route . '.frameless';
			}

		}

		require_once($file . '.php'); 

		?>
	</div>
</div>
<? } ?>
<iframe src="heartbeat.php" style="width: 1px;height: 1px;margin: 0;padding: 0;border: 0;"></frame>
</body>
</html>