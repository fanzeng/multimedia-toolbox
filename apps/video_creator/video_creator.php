<?php require_once 'frame.php';
session_start();
$documentRoot = $_SERVER['document_root'];
if ($documentRoot === '/var/www/html/multimedia_toolbox/') { // If this is on local machine.
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    $_SESSION['isLocalHost'] = true;
    $isLocalHost = true;
}

// CORS allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}

$userDataPath = '../../data/user_data';
if (!isset($_SESSION['isSessionInitialised'])) {

    $hash = bin2hex(random_bytes(16));
    $uniqid = uniqid();
    $_SESSION['userId'] = $uniqid;
    exec("mkdir -p $userDataPath/" . $uniqid . '/source_images/ 2>&1', $std_out);
    foreach ($std_out as $out) {
        echo $out;
    }
    exec("mkdir -p $userDataPath/" . $uniqid . '/expanded_frames/ 2>&1', $std_out);
    exec("touch $userDataPath/" . $uniqid . '/frames.json 2>&1', $std_out);

    $_SESSION['isSessionInitialised'] = 0;
    $_SESSION['sourceImageId'] = 0; // This is the immutable unique id for each uploaded image
    $_SESSION['arrSourceFilenames'] = array();
    $_SESSION['arrFrames'] = array();
}

if (isset($_POST['startOver'])) {
    $std_out = array();
    echo exec("rm -rf $userDataPath/" . $_SESSION['userId'] . '/* 2>&1', $std_out);
    if (empty($std_out)) {
        completeSessionDestroy();
        echo 'All uploaded files have been deleted.';
    } else {
        echo 'Something went wrong. Not all uploaded files have been deleted. ';
        foreach ($std_out as $out) {
            echo $out . '<br>';
        }
    }

} else if (isset($_POST['deleteAllUserData'])) {
    echo exec('rm -rf ./data/* 2>&1', $std_out);
    echo 'All user files are deleted.';
    completeSessionDestroy();

} else {
    echo 'Welcome.<br>';
    echo 'Your user id is: <br> ' . $_SESSION['userId'] . '<br>';

    exec("ls $userDataPath/" . $_SESSION['userId'] . '/source_images/* | wc -l 2>&1', $std_out);
    echo 'Existing number of frames: ' . $std_out[0] . "<br>";

    if (isset($_POST['numRepetition'])) {
        uploadImages();
        echo 'Images uploaded';
    } else if (isset($_POST['makeVideo'])) {
        makeVideo();
    } else {
        echo 'No work to do.<br>';
    }
}
function updateJson()
{
    global $userDataPath;
    $json_file = "$userDataPath/" . $_SESSION['userId'] . '/frames.json';

    $handle = fopen($json_file, 'w') or die('Cannot open file:  ' . $json_file);
    $json_data = json_encode($_SESSION, JSON_PRETTY_PRINT);
    echo $json_data;
    fwrite($handle, $json_data);
}

function uploadImages()
{
    // if ($_POST['start_frame_number'] != 0) {
    //   $_SESSION['frameId'] = (int)$_POST['start_frame_number';
    // }
    global $userDataPath;
    foreach ($_FILES['filesToUpload']['tmp_name'] as $key => $tmpName) {
    echo 'hahaha' . $tmpName;

        $numRepetition = (int) $_POST["numRepetition"];
        $tempName = $_FILES["filesToUpload"]["tmp_name"][$key];
        $name = $_FILES["filesToUpload"]["name"][$key];
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        checkUploadedFile();
        $upload_success = true;
        $srcFilename = "$userDataPath/" . $_SESSION['userId'] . '/source_images/source_image_' . (string) $_SESSION['sourceImageId'] . '.' . $ext;
        echo $srcFilename;
        if (move_uploaded_file($tempName, $srcFilename) != true) {
            $upload_sucess = false;
            echo "Failed to upload the file(s).";
        } else {
            array_push($_SESSION['arrSourceFilenames'], $srcFilename);
            $order = count($_SESSION['arrFrames']);
            $frame = new Frame($order, $srcFilename, $numRepetition, true);
            array_push($_SESSION['arrFrames'], $frame);
            updateJson();
            $_SESSION['sourceImageId']++;
        }

    }
}

function expandFrames()
{
    $_SESSION['frameId'] = 0;
    global $userDataPath;
    $target_dir = "$userDataPath/" . $_SESSION['userId'] . "/expanded_frames/";
    if (ctype_alnum(substr($userDataPath, -1))) {
        exec("rm $userDataPath/" . $_SESSION['userId'] . "/expanded_frames/*.morph.jpg", $std_out);
    } else {
        die('$userDataPath is invalid!');
    }

    for ($sourceImageNum = 0; $sourceImageNum < count($_SESSION['arrFrames']); $sourceImageNum++) {
        $frame = $_SESSION['arrFrames'][$sourceImageNum];
        var_dump($frame);
        $srcFilename = $frame->srcFilename;
        $ext = pathinfo($srcFilename, PATHINFO_EXTENSION);
        if ($ext !== "jpg") {
            exec("convert " . $srcFilename . " " . pathinfo($srcFilename, PATHINFO_DIRNAME) . "/" . pathinfo($srcFilename, PATHINFO_FILENAME) . ".jpg");
        }
        for ($repeat_num = 0; $repeat_num < $frame->numRepetition; $repeat_num++) {
            $target_file_name = getTargetFileName($target_dir, "jpg");
            exec("cp " . $srcFilename . " " . $target_file_name, $std_out);
        }
    }
}

function makeVideo()
{
    global $userDataPath;

    if (isset($_SESSION['isSessionInitialised'])) {
        expandFrames();
        $sh_string = 'video_width=' . $_POST['videoWidth'] . ' && ';
        $sh_string = $sh_string . 'video_height=' . $_POST['videoHeight'] . ' && ';
        $sh_string = $sh_string . 'frames_per_second=' . $_POST['framesPerSecond'] . ' && ';
        $sh_string = $sh_string . 'quality_ratio=' . $_POST['qualityRatio'] . ' && ';
        $sh_string = $sh_string . 'dirname=' . $_SESSION['userId'] . ' && ';
        $sh_string = $sh_string . 'echo dirname=\$dirname && ';
        $sh_string = $sh_string . '/usr/bin/ffmpeg -y -i ' . $userDataPath . '/\$dirname/expanded_frames/%05d.morph.jpg' . ' -r "\$frames_per_second" -crf \"\$quality_ratio\" -s \"\$video_width\"x\"\$video_height\" ' . $userDataPath . '/"\$dirname"/output.mp4';
        echo 'echo "' . $sh_string  . ' "> ' . "$userDataPath/" . $_SESSION['userId'] . '/run.sh';
        exec('echo "' . $sh_string  . ' "> ' . "$userDataPath/" . $_SESSION['userId'] . '/run.sh');
        exec("chmod +x $userDataPath/" . $_SESSION['userId'] . '/run.sh', $std_out);
        echo exec("$userDataPath/" . $_SESSION['userId'] . '/run.sh 2>&1', $std_out);
        foreach ($std_out as $out) {
            echo $out;
        }
        echo '<h1>Download the video here (right click, save as): <a href="' . $userDataPath . '/' . $_SESSION['userId'] . '/output.mp4"' . '>output.mp4</a></h1><br>';
    } else {
        echo 'Please click Start Over.';
    }
}

function checkUploadedFile()
{
    $is_good_image = 1;
    // $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    //     $check = getimagesize($tempName);
    //     if($check !== false) {
    //         echo "File is an image - " . $check'mime'] . ".";
    //         $isGoodImage = True;
    //     } else {
    //         echo "File is not an image.";
    //         $isGoodImage = False;
    //      }
    echo 'Checking uploaded files.';
    return $is_good_image;
}

function getTargetFileName($target_dir, $ext)
{
    $frameId_str = sprintf('%05d', $_SESSION['frameId']);
    $target_file_name = $target_dir . $frameId_str . '.morph.' . $ext;
    $_SESSION['frameId'] = (int) $_SESSION['frameId'] + 1;
    return $target_file_name;
}

function completeSessionDestroy()
{
    unset($_SESSION['isSessionInitialised']);
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, "/");
    }

    //clear session from globals
    $_SESSION = array();
    //clear session from disk
    session_destroy();
    header('Refresh:1');
}
?>
