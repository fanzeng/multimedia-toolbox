<?php
	session_start();
	$document_root = $_SERVER['DOCUMENT_ROOT'];
	if ($document_root === "/var/www/html/multimedia_toolbox/") { // If this is on local machine.
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);
		error_reporting(E_ALL);
		$_SESSION["local_machine"] = True;
		$local_machine = True;
	}

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


	$user_data_dir = "../../data/user_data";
	if(!isset($_SESSION["session_initialised"])) {
		$hash = bin2hex(random_bytes(16));
		$uniqid = uniqid();
		$_SESSION["user_id"] = $uniqid; 
		exec("mkdir -p $user_data_dir/" . $uniqid . "/ 2>&1", $std_out);

	    $_SESSION["session_initialised"] = 0;
	}

	if (isset($_POST["start_over"])) {
		$std_out = array();
		echo exec("rm -rf $user_data_dir/" . $_SESSION["user_id"] . "/* 2>&1", $std_out);
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
		$original_file_name = "$user_data_dir/" . $_SESSION["user_id"]  . "/original."  . $ext;
		if (move_uploaded_file($temp_name, $original_file_name) != True ) {
			$upload_sucess = False;
		}
		$_SESSION["original_file_name"] = $original_file_name;
	}

	if (isset($_POST["convert"])) {
		convert_rainbow_to_gray();
	}

	function convert_rainbow_to_gray() {
		global $user_data_dir;
		$local_machine = (isset($_SESSION["local_machine"]) && $_SESSION["local_machine"] === True);
		if ($local_machine) { // If this is on local machine.
			$cmd_str = "/home/fzeng/.virtualenvs/cv/bin/python";
		} else {
			$cmd_str = "python";
		}
		$cmd_str .= " scripts/convert_rainbow_scale_bar_to_gray.py";
		$cmd_str .= " --in_image_file " . $_SESSION["original_file_name"];
		$cmd_str .= " --out_path $user_data_dir" . "/" . $_SESSION["user_id"];
		$cmd_str .= " --rainbow_position " . $_POST["rainbow_position"];
		$cmd_str .= " --dark_end " . $_POST["dark_end"];
		$cmd_str .= " --bright_end " . $_POST["bright_end"];
		if (isset($_POST["is_vertical"])) {
			$cmd_str .= " -is_vertical";
		}
		if (isset($_POST["is_full_sat"])) {
			$cmd_str .= " -is_full_sat";
		}

		exec($cmd_str . " 2>&1", $std_out);

		if ($local_machine) {
			echo $cmd_str . "\n";
			foreach($std_out as $out) {
				echo $out . "\n";
			}
		}

		$_SESSION["converted_file_name"] = $user_data_dir . '/' .  $_SESSION["user_id"] . "/converted.png";
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Rainbow to Gray Image Converter</title>
	<link rel="stylesheet" type="text/css" href="../../public_html/css/default.css">
	<script type="text/javascript" src="../../vendors/scripts/jquery.min.js"></script>
	<script type="text/javascript" src="../../public_html/vendors/scripts/angular.min.js"></script>

</head>
<body>
	<br>Disclaimer: Your images will be deleted after use. But keep in mind this is on external web hosting. Please use with discretions. <br><br>


	<h2>Step 1: Upload the image:</h2><br>
	<form id="upload_image" action="#" method="post" enctype="multipart/form-data">
		<input type="file" name="image_to_upload" value="image_to_upload" id="image_to_upload">
		<label for="image_to_upload">Select image file to upload.</label><br>
		<input type="submit" value="Upload Image" name="upload_image">
		<p>Message: <span id="upload_image_message"></span></p>
	</form>

	<h2>Step 2: Specify where the rainbow color bar is:</h2><br>
	<form id="specify_rainbow_ends" action="#" method="post" enctype="multipart/form-data">
		<input id="dark_end" name="dark_end" value="100">
		<label for="dark_end">Specify the "dark end" of the rainbow color bar.</label><br>
		<input id="bright_end" name="bright_end" value="400">
		<label for="bright_end">Specify the "bright end" of the rainbow color bar.</label><br>
		<input id="rainbow_position" name="rainbow_position" value="400">
		<label for="rainbow_position">Specify the position of the rainbow color bar.</label><br>
		<input type="checkbox" value="My color bar is vertical" name="is_vertical">
		<label for="is_vertical">Check if the rainbow color bar is vertical.</label><br>
		<input type="checkbox" value="My figure has full saturation" name="is_full_sat">
		<label for="is_full_sat">Check if figure color has full saturation but color bar doesn't.</label><br>
		<input type="submit" value="Convert" name="convert" <?php echo isset($_SESSION["original_file_name"])?'':'disabled'?>>
		<p>Message: <span id="specify_rainbow_ends_message"></span></p>
		<?php
			if (isset($_SESSION["converted_file_name"])) {
				echo '<h2>Your image has been converted (right click, save as):<br><br> <img src="' . $user_data_dir . '/' .  $_SESSION["user_id"] . '/converted.png"' . '></img></h2><br>';
			}
		?>
	</form>

	<br><br>
	<form action="#" method="post" enctype="multipart/form-data">
	<input type="submit" value="Start Over" name="start_over">
		<p>Message: <span id="upload_image_message">Pressing this button will delete all previous data.</span></p>
	</form>

	<br>
</body>
</html>
