<?php
setlocale(LC_ALL, "en_US");

$birthdayExample = "June 26, 1983";

$dateformats = ["", "m-d-y", "d-m-y", "y-m-d", "Y-m-d", "m/d/Y", "d.m.y", "M j Y", "D jS M Y"];
$timeformats = ["", "h:i A", "h:i:s A", "H:i", "H:i:s"];

$months = [
	"",
	"January",
	"February",
	"March",
	"April",
	"May",
	"June",
	"July",
	"August",
	"September",
	"October",
	"November",
	"December",
];

$days = [
	"",
	"Sunday",
	"Monday",
	"Tuesday",
	"Wednesday",
	"Thursday",
	"Friday",
	"Saturday",
];

function Plural($i, $s) {
	if ($i == 1) //For 1, just return that.
		return $i." ".$s;

	if (substr($s,-1) == "y") //Grammar Nazi strikes back!
		$s = substr($s, 0, strlen($s)-1)."ies"; //query -> queries
	else if (substr($s,-3) == "tch") //Grammar Nazi strikes back again!
		$s = $s."es"; //match -> matches
	else
		$s .= "s"; //record -> records

	return $i." ".$s;
}

function stringtotimestamp($str) {
	global $months;
	$parts = explode(" ", $str);
	$day = (int)$parts[1];
	$month = $parts[0];
	$month = str_replace(",", "", $month);
	$year = (int)$parts[2];
	for($m = 1; $m <= 12; $m++) {
		if (strcasecmp($month, $months[$m]) == 0) {
			$month = $m;
			break;
		}
	}
	if ((int)$month != $month)
		return 0;
	return mktime(12,0,0, $month, $day, $year);
}

function timestamptostring($t) {
	if ($t == 0)
		return "";
	return strftime("%B %#d, %Y", $t);
}
