<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to Fan Zeng's Multimedia Toolbox</title>
  	<link rel="stylesheet" type="text/css" href="./css/default.css">
</head>
<body>
<div class="dockbg">
	<div class="dock">
		<h2>This website contains a few useful multimedia tools</h2>
		<p>Fan Zeng is an experienced programer and professional software engineer. He currently lives in Brisbane.</p>
		<p><a href="mailto:fanzengau@gmail.com">fanzengau@gmail.com</a><br>
		<a href="https://www.linkedin.com/in/fan-zeng-06692916/">LinkedIn</a>
		<a href="https://github.com/Fan-Zeng">https://github.com/Fan-Zeng</a>
		</p>
	</div>
</div>
<br><br><br><br><br><br><br><br>


<div class="column_uneven_8_2_left">
	<h2>Online video creator</h2>
	<a href="apps/video_creator/video_creator.php">Online video creator</a>

	<h2>Rainbow to gray converter</h2>
	<a href="apps/rainbow_to_gray/rainbow_to_gray.php">Rainbow to gray converter</a>

	<h2>Dev</h2>
	<a href="apps/dev/simulated_terminal.php">Simulated Terminal</a>

</div>


<div class="column_uneven_8_2_right">
	<h2></h2>
	<a href="https://www.linkedin.com/in/fan-zeng-06692916/">LinkedIn</a><br>
	<a href="https://github.com/Fan-Zeng">https://github.com/Fan-Zeng</a><br>
	<a href="mailto:fanzengau@gmail.com">fanzengau@gmail.com</a><br>
</div>

</body>

</html>

<?php
	$document_root = $_SERVER['DOCUMENT_ROOT'];
	$log_message_php = "$document_root" . '/utils/log_message.php';
 	include "$log_message_php";
    $path = "$document_root" . "/log/";
	$postfix = basename(__FILE__) . '.SERVER';
	$msg = print_r($_SERVER, TRUE) . "\n";
	log_message($path, $msg, $postfix);
?>