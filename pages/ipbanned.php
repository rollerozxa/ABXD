<table class="outline margin center" style="width: 60%; overflow: auto; margin: auto; margin-top: 40px; margin-bottom: 40px;">
<tr><td class="cell0" style="padding:60px">
<?php
$ipban['date'] = (int) $ipban['date'];
if ($ipban['date'])
	print format("You have been banned from this board until {0}. That's {1} left.",
				gmdate("M jS Y, G:i:s",$ipban['date']),
				TimeUnits($ipban['date']-time()));
else
	print "You have been <strong>permanently</strong> banned from this board";
print "<br>";
print "Attempting to get around this in any way will result in worse things.";
print "<br>";
print "<br>";
print "<b>Reason</b>: ".htmlspecialchars($ipban['reason']);
print "<br>";
$bucket = "ipbanned"; include('lib/pluginloader.php');
?>

</td></tr></table>