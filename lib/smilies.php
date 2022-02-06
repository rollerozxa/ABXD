<?php
function loadSmilies() {
	global $smilies, $smiliesReplaceOrig, $smiliesReplaceNew;

	$rSmilies = Query("select * from {smilies} order by length(code) desc");
	$smilies = [];

	while($smiley = Fetch($rSmilies))
		$smilies[] = $smiley;

	$smiliesReplaceOrig = $smiliesReplaceNew = [];
	foreach ($smilies as $smile) {
		$smiliesReplaceOrig[$smile['code'][0]][] = '/\G(?<!\w)'.preg_quote($smile['code'], "/").'(?![\w\/])/';
		$smiliesReplaceNew[$smile['code'][0]][] = resourceLink("img/smilies/".$smile['image']);
	}
}

function loadSmiliesOrdered() {
	global $smiliesOrdered;

	$rSmilies = Query("select * from {smilies}");
	$smilies = [];

	while($smiley = Fetch($rSmilies))
		$smiliesOrdered[] = $smiley;
}
