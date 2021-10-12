<?php require_once('frame.php');
  session_start();
  $document_root = $_SERVER['DOCUMENT_ROOT'];
  if ($document_root === '/var/www/html/multimedia_toolbox/') { // If this is on local machine.
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    $_SESSION['local_machine'] = True;
    $local_machine = True;
  }

  $user_data_dir = "../../data/user_data";
  if(!isset($_SESSION['session_initialised'])) {

    $hash = bin2hex(random_bytes(16));
    $uniqid = uniqid();
    $_SESSION['user_id'] = $uniqid; 
    exec("mkdir -p $user_data_dir/" . $uniqid . "/source_images/ 2>&1", $std_out);
    foreach($std_out as $out) {
      echo $out;
    }
    exec("mkdir -p $user_data_dir/" . $uniqid . "/expanded_frames/ 2>&1", $std_out);

      $_SESSION['session_initialised'] = 0;
        $_SESSION['source_image_id'] = 0;  // This is the immutable unique id for each uploaded image
      $_SESSION['array_srcFilenames'] = array();
        $_SESSION['array_frames'] = array();
  }

  if (isset($_POST['start_over'])) {
    $std_out = array();
    echo exec("rm -rf $user_data_dir/" . $_SESSION['user_id'] . "/* 2>&1", $std_out);
    if (empty($std_out)) {
      complete_session_destroy();
      echo "All your files have been deleted.";
    } else {
      echo "Something went wrong. Not all your files have been deleted. ";
      foreach($std_out as $out) {
        echo $out . "<br>";
      }
    }

  } else if (isset($_POST['delete_all_user_data'])) {
    echo exec("rm -rf ./data/* 2>&1", $std_out);
    echo "All user files are deleted.";
    complete_session_destroy();

  } else {
    echo "Welcome.<br>";
    echo  "Your user id is: <br> " . $_SESSION['user_id'] . '<br>';
  
    exec("ls $user_data_dir/" . $_SESSION['user_id'] . '/source_images/* | wc -l 2>&1', $std_out);
    echo "Existing number of frames: " . $std_out[0] . "<br>";

    if(isset($_POST['numRepetition'])) {
      upload_images();
    } else if (isset($_POST['make_video'])) {
      make_video();
    } else {
      echo "No work to do.<br>";
    }
  }
  function update_json() {
    global $user_data_dir;
      $json_file = "$user_data_dir/" . $_SESSION['user_id'] . "/frames.json";

    $handle = fopen($json_file, 'w') or die('Cannot open file:  '.$json_file);
    $json_data = json_encode($_SESSION, JSON_PRETTY_PRINT);
    echo $json_data;
    fwrite($handle, $json_data);
  }



  function upload_images() {
    // if ($_POST['start_frame_number'] != 0) {
    //   $_SESSION['frame_id'] = (int)$_POST['start_frame_number';
    // }
    global $user_data_dir;
		foreach($_FILES["files_to_upload"]["tmp_name"] as $key=>$tmp_name){
	    	$number_of_frames = (int)$_POST["number_of_frames"];
	    	$temp_name = $_FILES["files_to_upload"]["tmp_name"][$key];
	    	$name = $_FILES["files_to_upload"]["name"][$key];
	    	$ext = pathinfo($name, PATHINFO_EXTENSION);
			check_uploaded_file();
			$upload_success = True;
      $srcFilename = "$user_data_dir/" . $_SESSION['user_id']  . "/source_images/source_image_" . (string)$_SESSION['source_image_id'] . "." . $ext ;
      echo $srcFilename;
      if (move_uploaded_file($temp_name, $srcFilename) != True ) {
        $upload_sucess = False;
        echo "Failed to upload the file(s).";
      } else {
        array_push($_SESSION['array_srcFilenames'], $srcFilename);
        $order = count($_SESSION['array_frames']);
        $frame = new Frame($order, $srcFilename, $numRepetition, true);
        array_push($_SESSION['array_frames'], $frame);
        update_json();
        $_SESSION['source_image_id']++;
      }

    }
  }


  function expand_frames() {
    $_SESSION['frame_id'] = 0;
    global $user_data_dir;
    $target_dir = "$user_data_dir/" . $_SESSION['user_id'] . "/expanded_frames/";
    if (ctype_alnum(substr($user_data_dir, -1))) {
      exec("rm $user_data_dir/" . $_SESSION['user_id'] . "/expanded_frames/*.morph.jpg", $std_out);
    } else {
      die('$use_data_dir is invalid!');
    }

    for ($source_image_num = 0; $source_image_num < count($_SESSION['array_frames']); $source_image_num++) {
      $frame = $_SESSION['array_frames'][$source_image_num];
      var_dump($frame);
      $srcFilename = $frame->srcFilename;
      $ext = pathinfo($srcFilename, PATHINFO_EXTENSION);
      if ($ext !== "jpg") {
        exec("convert " . $srcFilename . " " . pathinfo($srcFilename, PATHINFO_DIRNAME) . "/" . pathinfo($srcFilename, PATHINFO_FILENAME) . ".jpg");
      }
      for($repeat_num = 0; $repeat_num < $frame->numRepetition; $repeat_num++) {
        $target_file_name = get_target_file_name($target_dir, "jpg");
        exec("cp " . $srcFilename . " " . $target_file_name, $std_out);
      }
     }
  }

  function make_video() {
    global $user_data_dir;

    if(isset($_SESSION['session_initialised'])) {
      expand_frames();
			$sh_string = 'video_width=' . $_POST["video_width"] . ' && ';
			$sh_string = $sh_string . 'video_height=' . $_POST["video_height"] . ' && ';
			$sh_string = $sh_string . 'frames_per_second=' . $_POST["frames_per_second"] . ' && ';
			$sh_string = $sh_string . 'quality_ratio=' . $_POST["quality_ratio"] . ' && ';
			$sh_string = $sh_string . 'dirname=' . $_SESSION["user_id"] . ' && ';
			$sh_string = $sh_string . 'echo dirname=\$dirname && ';
			$sh_string = $sh_string . '/usr/bin/ffmpeg -y -i ' . $user_data_dir . '/\$dirname/expanded_frames/%05d.morph.jpg' . ' -r "\$frames_per_second" -crf \"\$quality_ratio\" -s \"\$video_width\"x\"\$video_height\" ' . $user_data_dir . '/"\$dirname"/output.mp4';

			exec('echo "' . $sh_string  . ' "> ' . "$user_data_dir/" . $_SESSION["user_id"] . '/run.sh');
			exec("chmod +x $user_data_dir/" . $_SESSION["user_id"] . "/run.sh", $std_out);
			echo exec("$user_data_dir/" . $_SESSION["user_id"] . "/run.sh 2>&1", $std_out);
			foreach($std_out as $out) {
				echo $out;
			}
			echo '<h1>Download the video here (right click, save as): <a href="' . $user_data_dir . '/' .  $_SESSION["user_id"] . '/output.mp4"' . '>output.mp4</a></h1><br>';
		} else {
      echo 'Please click Start Over.';
    }
  }

  function check_uploaded_file() {
    $is_good_image = 1;
    // $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
  //     $check = getimagesize($temp_name);
  //     if($check !== false) {
  //         echo "File is an image - " . $check'mime'] . ".";
  //         $isGoodImage = True;
  //     } else {
  //         echo "File is not an image.";
  //         $isGoodImage = False;
  //      }
      return $is_good_image;
  }

  function get_target_file_name($target_dir, $ext) {
    $frame_id_str = sprintf('%05d', $_SESSION['frame_id']) ;
    $target_file_name = $target_dir . $frame_id_str . '.morph.' . $ext;
    $_SESSION['frame_id'] = (int)$_SESSION['frame_id'] + 1;
    return $target_file_name;
  }

  function complete_session_destroy() {
    unset($_SESSION['session_initialised']);
    if (isset($_COOKIE[session_name()]))
      setcookie(session_name(), '', time()-3600, "/");
      //clear session from globals
      $_SESSION = array();
      //clear session from disk
      session_destroy();
      header('Refresh:1');
  }
?>

<!DOCTYPE html>
<html lang="en" ng-app="framePreviewApp">
<head>
  <title>Online Video Creator</title>
  <link rel="stylesheet" type="text/css" href="../../public_html/css/default.css">
  <script type="text/javascript" src="../../public_html/vendor/scripts/jquery.min.js"></script>
  <script type="text/javascript" src="../../public_html/vendor/scripts/angular.min.js"></script>
  <script type="text/javascript" src="scripts/videoCreator.js"></script>
  <script type="text/javascript" src="scripts/app.module.js"></script>
  <script type="text/javascript" src="scripts/frame-preview/frame-preview.module.js"></script>
  <script type="text/javascript" src="scripts/frame-preview/frame-preview.component.js"></script>
</head>
<body>
  <br>Disclaimer: Your images will be deleted after use. But keep in mind this is on external web hosting. Please use with discretions. <br><br>
  <h2>Step 1: Upload key frames in order (must be image files with same extension):</h2><br>
  <form id="upload_files" action="#" method="post" enctype="multipart/form-data">
    <input type="file" name="files_to_upload[]" multiple="multiple" onmousedown="onBrowseBtnClick()" value="files_to_upload" id="files_to_upload">
    <label for="files_to_upload">Select image files to upload.</label><br>
    <input id="numRepetition" name="numRepetition" value="1">
    <label for="numRepetition">Specify number of frames in the output video each uploaded image is displayed.</label><br>
    <input id="start_frame_number" name="start_frame_number" value="0">
    <label for="start_frame_number">Specify the start frame number to insert the new frames.</label><br>
    <input type="submit" value="Upload Images" name="upload_images">
    <p>Message: <span id="upload_images_message"></p>
    <p>If a dialogue pops-up asking whether to submit again, cancel and manually hit enter in the address bar. The existing frame number should then be updated.</p>
  </form>
  <br><br>

  <frame-preview></frame-preview>

  <h2>Step 2: Specify video parameters:</h2><br>
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
  <!--
    <form action="#" method="post" enctype="multipart/form-data">
    <input type="submit" value="Delete All User Data" name="delete_all_user_data">
    </form>
  -->
  </form>
  <!-- <a href="index.php">Back to index</a> -->
</body>
</html>
