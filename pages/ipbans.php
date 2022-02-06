<?php
//  AcmlmBoard XD - IP ban management tool
//  Access: administrators only

$title = "IP bans";

AssertForbidden("editIPBans");

if ($loguser['powerlevel'] < 3)
	Kill("Only administrators get to manage IP bans.");

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Admin", "admin"));
$crumbs->add(new PipeMenuLinkEntry("IP bans", "ipbans"));
makeBreadcrumbs($crumbs);

if (isset($_POST['actionadd'])) {
	//This doesn't allow you to ban IP ranges...
	//if (!filter_var($_POST['ip'], FILTER_VALIDATE_IP))
	//	Alert("Invalid IP");
	//else
	if (isIPBanned($_POST['ip']))
		Alert("Already banned IP!");
	else {
		$whitelist = $_POST['whitelisted'] ? 'TRUE' : 'FALSE';
		$rIPBan = Query("insert into {ipbans} (ip, reason, date, whitelisted) values ({0}, {1}, {2}, $whitelist)", $_POST['ip'], $_POST['reason'], ((int)$_POST['days'] > 0 ? time() + ((int)$_POST['days'] * 86400) : 0));
		Alert("Added.", "Notice");
	}
}
elseif ($_GET['action'] == "delete") {
	$rIPBan = Query("delete from {ipbans} where ip={0} limit 1", $_GET['ip']);
	Alert("Removed.", "Notice");
}

$rIPBan = Query("select * from {ipbans} order by date desc");

$banList = "";
while($ipban = Fetch($rIPBan)) {
	$cellClass = ($cellClass+1) % 2;
	if ($ipban['date'])
		$date = formatdate($ipban['date'])." (".TimeUnits($ipban['date']-time())." left)";
	else
		$date = "Permanent";
	$banList .= "
	<tr class=\"cell$cellClass\">
		<td>".htmlspecialchars($ipban['ip'])."</td>
		<td>".htmlspecialchars($ipban['reason'])."</td>
		<td>$date</td>
		<td>".($ipban['whitelisted'] ? "Yes" : "No")."
		<td><a href=\"".actionLink("ipbans", "", "ip=".htmlspecialchars($ipban['ip'])."&action=delete")."\">&#x2718;</a></td>
	</tr>";
}

print "
<table class=\"outline margin width50\">
	<tr class=\"header1\">
		<th>IP</th>
		<th>Reason</th>
		<th>Date</th>
		<th>Whitelisted</th>
		<th>&nbsp;</th>
	</tr>
	$banList
</table>

<form action=\"".actionLink("ipbans")."\" method=\"post\">
	<table class=\"outline margin width50\">
		<tr class=\"header1\">
			<th colspan=\"2\">
				Add
			</th>
		</tr>
		<tr>
			<td class=\"cell2\">
				IP
			</td>
			<td class=\"cell0\">
				<input type=\"text\" name=\"ip\" style=\"width: 98%;\" maxlength=\"45\">
			</td>
		</tr>
		<tr>
			<td class=\"cell2\">
				Reason
			</td>
			<td class=\"cell1\">
				<input type=\"text\" name=\"reason\" style=\"width: 98%;\" maxlength=\"100\">
			</td>
		</tr>
		<tr>
			<td class=\"cell2\">
				For
			</td>
			<td class=\"cell1\">
				<input type=\"text\" name=\"days\" size=\"13\" maxlength=\"13\"> days
			</td>
		</tr>
		<tr>
			<td class=\"cell2\">
				Whitelisted
			</td>
			<td class=\"cell1\">
				<input type=\"checkbox\" name=\"whitelisted\" size=\"13\" maxlength=\"13\">
			</td>
		</tr>
		<tr class=\"cell2\">
			<td></td>
			<td>
				<input type=\"submit\" name=\"actionadd\" value=\"Add\">
			</td>
		</tr>
	</table>
</form>";
