<?php require_once('frame.php');

session_start();
// echo session_id();
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(E_ALL);

	$user_data_dir = "../../data/user_data";
    $json_file = "$user_data_dir/" . $_SESSION["user_id"] . "/frames.json";
    $handle = fopen($json_file, 'w');
    $post_string = json_encode($_POST, JSON_NUMERIC_CHECK|JSON_PRETTY_PRINT);
    fwrite($handle, $post_string);
  	fclose($handle);
	$json_obj = json_decode($post_string, true);
	$_SESSION["array_frames"] = array();
	foreach ($json_obj["array_frames"] as $frame) {
		$f = new Frame($frame["order"], $frame["source_file_name"], $frame["number_of_frames"]);
		array_push($_SESSION["array_frames"], $f);
	}
    $_SESSION["source_image_num"] = count($_SESSION["array_frames"]);
	echo "session_string=" . var_dump($json_obj["array_frames"]);

?>

