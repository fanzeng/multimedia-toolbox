<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fan Zeng's multimedia toolbox. Welcome</title>
  	<link rel="stylesheet" type="text/css" href="https://fanzeng.github.io/fanzengau.com/public/css/fanzeng.css">
	<link rel="stylesheet" type="text/css" href="css/multimedia_toolbox.css">
</head>
<body>
<div class="docktop_bg" id="docktop_bg">
	<div class="docktop_fg" id="docktop_fg">
		<h2>This website contains a few useful multimedia tools</h2>
		<p>My name is Fan Zeng, and I am a software engineer in Brisbane, Australia.</p>
		<a href="https://www.linkedin.com/in/fan-zeng-06692916/">LinkedIn</a>
		<a href="https://github.com/fanzeng">https://github.com/fanzeng</a>
		</p>
	</div>
</div>
<div style="height: 100px"></div>

<div class="column_uneven_8_2_left">
	<h1>Online video creator</h1>
	<a href="../apps/video_creator/video_creator.php">Online video creator</a>

	<h1>Rainbow to gray converter</h1>
	<a href="../apps/rainbow_to_gray/rainbow_to_gray.php">Rainbow to gray converter</a>

	<h1>Dev</h1>
	<a href="../apps/dev/simulated_terminal.php">Simulated Terminal</a>

</div>


<div class="column_uneven_8_2_right">
	<h2></h2>
		<a href="https://www.linkedin.com/in/fanzengau/"><img src="resource/icon/resized/icon_linkedin.png" height="32" width="32" alt="LinkedIn"></a><br>
		<a href="https://github.com/fanzeng"><img src="resource/icon/resized/icon_github.png" height="32" width="32" alt="Github"></a><br>
</div>

</body>

</html>

<?php
	$document_root = $_SERVER['DOCUMENT_ROOT'];
	if ($document_root === "/var/www/html/multimedia_toolbox/") { // If this is on local machine.
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		$_SESSION["local_machine"] = True;
		$local_machine = True;
	}

	$log_message_php = "$document_root" . '/apps/utils/log_message.php';
 	include "$log_message_php";
    $path = "$document_root" . "/log/";
	$postfix = basename(__FILE__) . '.SERVER';
	$msg = print_r($_SERVER, TRUE) . "\n";
	log_message($path, $msg, $postfix);
?>