<?php
/*
 * BSD 2-Clause License
 *
 * Copyright (c) 2026, J. von Rotz
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

$path = "/var/www/html/pastedata";

if($_SERVER["REQUEST_METHOD"] != "POST") {
	/* we want to read an existing date */
	if(!isset($_GET["id"])) {
		header("Content-Type: text/html");
		header($_SERVER["SERVER_PROTOCOL"]." 200 OK");
		echo "<html><head><title>Paste</title></head><body><h1>Paste</h2><p>This is a web based clipboard. To paste stuff, do something like this:</p><pre>echo helloworld | curl -X POST --data @- https://".$_SERVER["SERVER_NAME"]."/</pre><p>You will get an identification string. When handling newlines or when sending binary data, use the <code>--data-binary</code> option for curl.</p><p>To retrieve stuff do:</p><pre>curl https://".$_SERVER["SERVER_NAME"]."/?id=&lt;identification-string&gt;</pre></p><hr><p>Aliases for <i>csh</i>:<pre>alias pst 'curl -X POST --data-binary @- https://".$_SERVER["SERVER_NAME"]."/'
alias ret 'curl https://".$_SERVER["SERVER_NAME"]."/\?id=\!:1'</pre>Aliases/functions for <i>bash</i>:<pre>alias pst='curl -X POST --data-binary @- https://".$_SERVER["SERVER_NAME"]."/'
ret() { curl https://".$_SERVER["SERVER_NAME"]."/\?id=\${1}; }</pre></p><hr><small>Copyright (c) 2026 J. von Rotz <a href=\"https://github.com/jovoro/paste\"><address>https://github.com/jovoro/paste</address></a></small></body></html>";
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
		echo "Path $path is not writable";
		exit();
	}
}


?>
