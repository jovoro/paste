<?php
$path = "/var/www/html/pastedata";

if($_SERVER["REQUEST_METHOD"] != "POST") {
	/* we want to read an existing date */
	if(!isset($_GET["id"])) {
		header($_SERVER["SERVER_PROTOCOL"]." 400 Bad Request");
		echo "Need id to know what to read";
		exit();
	}

	$fname = $path."/".preg_replace('/\.+/', '', $_GET["id"]);
	if(!file_exists($fname)) {
		header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
		exit();
	}

	header('Content-Length: '.filesize($fname));
	header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
	readfile($fname);
	exit();
} else {
	$bytes = random_bytes(5);
	$hex = bin2hex($bytes);
	if(is_writable($path)) {
		$fname = $path."/".$hex;
		if(!$fp = fopen($fname, 'w')) {
			header($_SERVER["SERVER_PROTOCOL"]." 500 Unable to open");
			echo "cannot open $fname for writing";
			exit();
		}

		$data = file_get_contents("php://input");
		if (fwrite($fp, $data) === FALSE) {
			header($_SERVER["SERVER_PROTOCOL"]." 500 Unable to write");
			echo "Cannot write to file $fname";
			exit();
		}

		fclose($fp);
		header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
		echo $hex."\n";
	} else {
		header($_SERVER["SERVER_PROTOCOL"]." 500 Unable to write");
		echo "Path $fname is not writable";
		exit();
	}
}


?>
