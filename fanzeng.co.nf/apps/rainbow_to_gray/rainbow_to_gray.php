<?php
	session_start();
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	function complete_session_destroy() {
		unset($_SESSION["session_initialised"]);
		if ( isset( $_COOKIE[session_name()] ) )
			setcookie( session_name(), "", time()-3600, "/");
			//clear session from globals
			$_SESSION = array();
			//clear session from disk
			session_destroy();
			header("Refresh:1");
	}
	
	if(!isset($_SESSION["session_initialised"])) {
		$hash = bin2hex(random_bytes(16));
		$uniqid = uniqid();
		$_SESSION["user_id"] = $uniqid; 
		exec("mkdir -p ./data/" . $uniqid . "/ 2>&1", $std_out);

	    $_SESSION["session_initialised"] = 0;
	}

	if (isset($_POST["start_over"])) {
		$std_out = array();
		echo exec("rm -rf ./data/" . $_SESSION["user_id"] . "/* 2>&1", $std_out);
		if (empty($std_out)) {
			complete_session_destroy();
			echo "All your files have been deleted.";
		} else {
			echo "Something went wrong. Not all your files have been deleted. ";
			foreach($std_out as $out) {
				echo $out . "<br>";
			}
		}
	}
	if (isset($_POST["upload_image"])) {
    	$temp_name = $_FILES["image_to_upload"]["tmp_name"];
	   	$name = $_FILES["image_to_upload"]["name"];
		$upload_success = True;
    	$ext = pathinfo($name, PATHINFO_EXTENSION);
		$original_file_name = "./data/" . $_SESSION["user_id"]  . "/original."  . $ext;
		if (move_uploaded_file($temp_name, $original_file_name) != True ) {
			$upload_sucess = False;
		}
		$_SESSION["original_file_name"] = $original_file_name;
	}

	if (isset($_POST["convert"])) {
		$cmd_str = "python";
		$document_root = $_SERVER['DOCUMENT_ROOT'];
		if ($document_root == "/var/www/html/fanzeng.co.nf/") { // If this is on local machine.
			$cmd_str = "../../bin/virtualenvs/plt/bin/python";
		}
		$cmd_str .= " scripts/convert_rainbow_scale_bar_to_gray.py";
		$cmd_str .= " --in_image_file " . $_SESSION["original_file_name"];
		$cmd_str .= " --out_path ./data/" . $_SESSION["user_id"] . "/";
		$cmd_str .= " --bar_middle 390";
		$cmd_str .= " --bar_left 430";
		$cmd_str .= " --bar_right 940";
		echo $cmd_str;
		echo exec($cmd_str . " 2>&1", $std_out);
		foreach($std_out as $out) {
			echo $out . "\n";
		}
	}


?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Online Video Creator</title>
	<link rel="stylesheet" type="text/css" href="../../css/default.css">
	<script type="text/javascript" src="../../vendors/scripts/jquery.min.js"></script>

</head>
<body>
	<br>Disclaimer: Your images will be deleted after use. But keep in mind this is on external web hosting. Please use with discretions. <br><br>


	Step 1: Upload the image:<br>
	<form id="upload_image" action="#" method="post" enctype="multipart/form-data">
		<input type="file" name="image_to_upload" value="image_to_upload" id="image_to_upload">
		<label for="image_to_upload">Select image file to upload.</label><br>
		<input type="submit" value="Upload Image" name="upload_image">
		<p>Message: <span id="upload_image_message"></span></p>
	</form>

	Step 1: Specify rainbow ends:<br>
	<form id="specify_rainbow_ends" action="#" method="post" enctype="multipart/form-data">
		<input id="dark_end" name="dark_end" value="100">
		<label for="dark_end">Specify the "dark end" of the rainbow.</label><br>
		<input id="bright_end" name="bright_end" value="500">
		<label for="bright_end">Specify the "bright end" of the rainbow.</label><br>
		<input id="rainbow_row" name="rainbow_row" value="500">
		<label for="bright_end">Specify the position of the rainbow.</label><br>
		<input type="submit" value="Convert" name="convert">
		<p>Message: <span id="specify_rainbow_ends_message"></span></p>
	</form>

	<br><br>
	<form action="#" method="post" enctype="multipart/form-data">
	<input type="submit" value="Start Over" name="start_over">
		<p>Message: <span id="upload_image_message">Pressing this button will delete all previous data.</span></p>
	</form>

	<br>
</body>
</html>
