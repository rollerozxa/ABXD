<?php
//  AcmlmBoard XD - 404
//  Access: all

// Some servers use one response, some use another. For safety, use both.
header('HTTP/1.1 404 Not Found');
header('Status: 404 Not Found');

$title = "404 - Not found";

Kill('The page you are looking for was not found.<br><br>
	<a href="/?page=board">Return to the board index</a>', "404 - Not found");
