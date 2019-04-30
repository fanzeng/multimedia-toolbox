<?php
session_start();
// echo session_id();
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);
	if(!isset($_SESSION["session_initialised"])) {
		$hash = bin2hex(random_bytes(16));
		$uniqid = uniqid();
		$_SESSION["user_id"] = $uniqid; 
		exec("mkdir -p ./data/" . $uniqid . "/source_images/ 2>&1", $std_out);
	    $_SESSION["session_initialised"] = 0;
	    $_SESSION["frame_id"] = 0;
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

	} else if (isset($_POST["delete_all_user_data"])) {
		echo exec("rm -rf ./data/* 2>&1", $std_out);
		echo "All user files are deleted.";
		complete_session_destroy();

	} else {
		echo "Welcome.<br>";
		echo  "Your user id is: <br> " . $_SESSION["user_id"] . '<br>';
	
		exec('ls ./data/' . $_SESSION["user_id"] . '/source_images/*.morph.* | wc -l 2>&1', $std_out);
		echo "Existing number of frames: " . $std_out[0] . "<br>";

		if(isset($_POST["number_of_frames"])) {
			if ($_POST["start_frame_number"] != 0) {
				$_SESSION["frame_id"] = (int)$_POST["start_frame_number"];
			}
			foreach($_FILES["files_to_upload"]["tmp_name"] as $key=>$tmp_name){
		    	$number_of_frames = $_POST["number_of_frames"];
		    	$temp_name = $_FILES["files_to_upload"]["tmp_name"][$key];
		    	$name = $_FILES["files_to_upload"]["name"][$key];
		    	$ext = pathinfo($name, PATHINFO_EXTENSION);
				check_uploaded_file();
				$upload_success = True;
				$temp_target_file = "./data/" . $_SESSION["user_id"]  . "/source_images/temp_target_file." . $ext ;
				if (move_uploaded_file($temp_name, $temp_target_file) != True ) {
					$upload_sucess = False;
				}
				for ($frame_num = 0; $frame_num < $number_of_frames; $frame_num++) {
					$target_file_name = get_target_file_name($ext);
					exec("cp " . $temp_target_file . " " . $target_file_name, $std_out);
					$frame_num_submitted = $frame_num;
				}
				$frame_num_submitted = $frame_num;
		   		if ($upload_success) {
		   			echo "All good. <br>";
			    } else {
			        echo "[WARN] Sorry, there was an error uploading your file.";
			    }
		       	echo "[INFO] " . $frame_num_submitted . " frame(s) of " . basename($name). " has been uploaded. <br>";

			}
		} else if (isset($_POST["make_video"])) {
			if(isset($_SESSION["session_initialised"])) {
				$sh_string = 'video_width=' . $_POST["video_width"] . ' && ';
				$sh_string = $sh_string . 'video_height=' . $_POST["video_height"] . ' && ';
				$sh_string = $sh_string . 'frames_per_second=' . $_POST["frames_per_second"] . ' && ';
				$sh_string = $sh_string . 'quality_ratio=' . $_POST["quality_ratio"] . ' && ';
				$sh_string = $sh_string . 'dirname=' . $_SESSION["user_id"] . ' && ';
				$sh_string = $sh_string . 'echo dirname=\$dirname && ';
				$sh_string = $sh_string . '/usr/bin/ffmpeg -y -i ./data/\$dirname/source_images/"%05d.morph.jpg' . '" -r "\$frames_per_second" -crf \"\$quality_ratio\" -s \"\$video_width\"x\"\$video_height\"  ./data/"\$dirname"/output.mp4';

				exec('echo "' . $sh_string  . ' "> ' . './data/' . $_SESSION["user_id"] . '/run.sh');
				exec("chmod +x ./data/" . $_SESSION["user_id"] . "/run.sh", $std_out);
				echo exec("./data/" . $_SESSION["user_id"] . "/run.sh 2>&1", $std_out);
				foreach($std_out as $out) {
					echo $out;
				}
				echo '<h1>Download the video here (right click, save as): <a href="./data/' .  $_SESSION["user_id"] . '/output.mp4"' . '>output.mp4</a></h1><br>';
			} else {
				echo "Please click Start Over.";
			}
		} else {
			echo "No work to do.<br>";
		}
	}
	function check_uploaded_file() {
		$is_good_image = 1;
		// $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
  //   	$check = getimagesize($temp_name);
  //   	if($check !== false) {
  //       	echo "File is an image - " . $check["mime"] . ".";
  //       	$isGoodImage = True;
  //   	} else {
  //       	echo "File is not an image.";
  //       	$isGoodImage = False;
  //  		}
  		return $is_good_image;
	}

	function get_target_file_name($ext) {
		$target_dir = "./data/" . $_SESSION["user_id"] . "/source_images/";
		$frame_id_str = sprintf('%05d', $_SESSION["frame_id"]) ;
		$target_file_name = $target_dir . $frame_id_str . ".morph." . $ext;
		// echo "[INFO] Uploading file: " . $target_file_name . " . ";
		$_SESSION["frame_id"] = (int)$_SESSION["frame_id"] + 1;
		// echo "[INFO] Submitted " . $_SESSION["frame_id"] . " frames.<br>";
		return $target_file_name;
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<title>Online Video Creator</title>
	<link rel="stylesheet" type="text/css" href="../../css/default.css">
	<script type="text/javascript" src="../../vendors/scripts/jquery.min.js"></script>
	<script type="text/javascript" src="../../vendors/scripts/angular.min.js"></script>
	<script type="text/javascript" src="scripts/videoCreator.js"></script>
</head>
<body>
	<br>Disclaimer: Your images will be deleted after use. But keep in mind this is on external web hosting. Please use with discretions. <br><br>
	Step 1: Upload key frames in order (must be image files with same extension):<br>
	<form id="upload_files" action="#" method="post" enctype="multipart/form-data">
	<input type="file" name="files_to_upload[]" multiple="multiple" onmousedown="onBrowseBtnClick()" value="files_to_upload" id="files_to_upload">
	<label for="files_to_upload">Select image files to upload.</label><br>
	<input id="number_of_frames" name="number_of_frames" value="1">
	<label for="number_of_frames">Specify number of frames in the output video each uploaded image is displayed.</label><br>
	<input id="start_frame_number" name="start_frame_number" value="0">
	<label for="start_frame_number">Specify the start frame number to insert the new frames.</label><br>
	<input type="submit" value="Upload Images" name="upload_images">
	<p>Message: <span id="upload_images_message"></p>
	<p>If a dialogue pops-up asking whether to submit again, cancel and manually hit enter in the address bar. The existing frame number should then be updated.</p>
	</form>
	<br><br>



	<!-- <frame-preview></frame-preview> -->

	Step 2: Specify video parameters:<br>
	<form action="#" method="post" enctype="multipart/form-data">
	<label for="video_width">Video width</label><br>
	<input id="video_width" name="video_width" value="640"><br>
	<label for="video_height">Video height</label><br>
	<input id="video_height" name="video_height" value="480"><br>
	<label for="frames_per_second">Frames per second</label><br>
	<input id="frames_per_second" name="frames_per_second" value="24"><br>
	<label for="quality_ratio">Quality ratio (0: Best, 51: Worst)</label><br>
	<input id="quality_ratio" name="quality_ratio" value="23"><br>
	<input type="submit" value="Make Video" name="make_video"><br>
	</form>


	<br><br>
	<form action="#" method="post" enctype="multipart/form-data">
	<input type="submit" value="Start Over" name="start_over">
	Pressing this button will delete all previous uploaded images.<br>

<!-- 	</form>
	<form action="#" method="post" enctype="multipart/form-data">
	<input type="submit" value="Delete All User Data" name="delete_all_user_data">
	</form> -->
    <!-- <a href="index.php">Back to index</a> -->
</body>
</html>
