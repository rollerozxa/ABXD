<?php
//  AcmlmBoard XD - Member list page
//  Access: all


$title = "Member list";

AssertForbidden("viewMembers");



function PageLinks2($url, $epp, $from, $total) {
	if ($total < 1) return '';

	$numPages = ceil($total / $epp);
	$page = ceil($from / $epp) + 1;

	$first = ($from) ? "<a class=\"pagelink\" href=\"".$url."0)\">&#x00AB;</a> " : "";
	$prev = ($from) ? "<a class=\"pagelink\" href=\"".$url.($from - $epp).")\">&#x2039;</a> " : "";
	$next = ($from < $total - $epp) ? " <a class=\"pagelink\" href=\"".$url.($from + $epp).")\">&#x203A;</a>" : "";
	$last = ($from < $total - $epp) ? " <a class=\"pagelink\" href=\"".$url.(($numPages * $epp) - $epp).")\">&#x00BB;</a>" : "";

	$pageLinks = [];
	for($p = $page - 5; $p < $page + 10; $p++) {
		if ($p < 1 || $p > $numPages)
			continue;
		if ($p == $page || ($from == 0 && $p == 1))
			$pageLinks[] = $p;
		else
			$pageLinks[] = "<a class=\"pagelink\" href=\"".$url.(($p-1) * $epp).")\">".$p."</a>";
	}

	return $first.$prev.join(" ", array_slice($pageLinks, 0, 11)).$next.$last;
}


if ($_GET['listing']) {
	$tpp = $loguser['threadsperpage'];
	if ($tpp<1) $tpp=50;

	if (isset($_GET['from']))
		$from = (int)$_GET['from'];
	else
		$from = 0;

	if (isset($dir)) unset($dir);
	if (isset($_GET['dir'])) {
		$dir = $_GET['dir'];
		if ($dir != "asc" && $dir != "desc")
			unset($dir);
	}

	$sort = $_GET['sort'];
	if (!in_array($sort, ['', 'id', 'name', 'reg']))
		unset($sort);

	if (isset($_GET['pow']) && $_GET['pow'] != "")
		$pow = (int)$_GET['pow'];

	$order = "";
	$where = "";

	switch($sort) {
		case "id": $order = "id ".(isset($dir) ? $dir : "asc"); break;
		case "name": $order = "name ".(isset($dir) ? $dir : "asc"); break;
		case "reg": $order = "regdate ".(isset($dir) ? $dir : "desc"); break;
		default: $order="posts ".(isset($dir) ? $dir : "desc");
	}

	$where = "1";
	if (isset($pow))
		$where.= " and powerlevel={2}";

	$query = $_GET['query'];

	if ($query != "") {
			$where.= " and name like {3} or displayname like {3}";
	}

	if (!(isset($pow) && $pow == 5))
		$where.= " and powerlevel < 5";

	$numUsers = FetchResult("select count(*) from {users} where ".$where, null, null, $pow, "%{$query}%");
	$rUsers = Query("select * from {users} where ".$where." order by ".$order.", name asc limit {0u},{1u}", $from, $tpp, $pow, "%{$query}%");

	$pagelinks = PageLinks2("javascript:refreshMemberlist(", $tpp, $from, $numUsers);

	$ajaxPage = true;

	echo "	<table class=\"outline margin\">";

	if ($numUsers) {
		if ($numUsers == 1)
			$nu = "1 user found.";
		else
			$nu = format("{0} users found.", $numUsers);

		echo "
			<tr class=\"cell1\">
				<td colspan=\"2\">
				</td>
				<td colspan=\"6\">
					$nu
				</td>
			</tr>";
	}
	if ($pagelinks) {
		echo "
			<tr class=\"cell2\">
				<td colspan=\"2\">
					Page
				</td>
				<td colspan=\"6\">
					$pagelinks
				</td>
			</tr>";
	}

	$memberList = "";
	if ($numUsers) {
		while($user = Fetch($rUsers)) {
			$daysKnown = (time()-$user['regdate'])/86400;
			$user['average'] = sprintf("%1.02f", $user['posts'] / $daysKnown);

			$userPic = "";

			if ($user["picture"] == "#INTERNAL#")
				$userPic = "<img src=\"${dataUrl}avatars/".$user['id']."\" alt=\"\" style=\"max-width: 60px;max-height:60px;\">";
			else if ($user["picture"])
				$userPic = "<img src=\"".htmlspecialchars($user["picture"])."\" alt=\"\" style=\"max-width: 60px;max-height:60px;\">";

			$cellClass = ($cellClass+1) % 2;
			$memberList .= format(
	"
			<tr class=\"cell{0}\">
				<td>{1}</td>
				<td class=\"center\">{2}</td>
				<td>{3}</td>
				<td>{4}</td>
				<td>{5}</td>
				<td>{6}</td>
				<td>{7}</td>
			</tr>
	",	$cellClass, $user['id'], $userPic, UserLink($user), $user['posts'],
		$user['average'],
		($user['birthday'] ? cdate("M jS", $user['birthday']) : "&nbsp;"),
		cdate("M jS Y", $user['regdate'])
		);
		}
	}
	else {
		$memberList = "
			<tr class=\"cell0\">
				<td colspan=\"8\">
					Nothing matched your search.
				</td>
			</tr>";
	}

	echo "
			<tr class=\"header1\">
				<th style=\"width: 30px; \">#</th>
				<th style=\"width: 62px; \">Picture</th>
				<th>Name</th>
				<th style=\"width: 50px; \">Posts</th>
				<th style=\"width: 50px; \">Average</th>
				<th style=\"width: 80px; \">Birthday</th>
				<th style=\"width: 130px; \">Registered on</th>
			</tr>
			$memberList";

	if ($pagelinks) {
		echo "
			<tr class=\"cell2\">
				<td colspan=\"2\">
					Page
				</td>
				<td colspan=\"6\">
					$pagelinks
				</td>
			</tr>";
	}

	echo "</table>";

	die();
}

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Member list", "memberlist"));
makeBreadcrumbs($crumbs);

if (!$isBot) {
	echo "
	<script type=\"text/javascript\" src=\"".resourceLink("js/memberlist.js")."\"></script>
	<table>
	<tr>
	<td id=\"userFilter\" style=\"margin-bottom: 1em; margin-left: auto; margin-right: auto; padding: 1em; padding-bottom: 0.5em; padding-top: 0.5em;\">
		<label>
		Sort by:
		".makeSelect("orderBy", [
			"" => "Post count",
			"id" => "ID",
			"name" => "Name",
			"reg" => "Registration date"
		])." &nbsp;
		</label>
		<label>
		Order:
		".makeSelect("order", [
			"desc" => "Descending",
			"asc" => "Ascending",
		])." &nbsp;
		</label>
		<label>
		Power:
		".makeSelect("power", [
			"" => "(any)",
			-1 => "Banned",
			0 => "Normal",
			1 => "Local Mod",
			2 => "Full Mod",
			3 => "Admin",
			4 => "Root",
			5 => "System"
		])."
		</label>
	</td>
	<td style=\"text-align: right;\">
			<form action=\"javascript:refreshMemberlist();\">
				<div style=\"display:inline-block\">
					<input type=\"text\" name=\"query\" id=\"query\" placeholder=\"Search\">
					<button id=\"submitQuery\"><i class=\"icon-search\"></i></button>
				</div>
			</form>
	</td></tr></table>";
}

echo "
	<div id=\"memberlist\">
		<div class=\"center\" style=\"padding: 2em;\">
			Loading memberlist...
		</div>
	</div>";


//We do not need a default index.
//All options are translatable too, so no need for ) in the array.
//Name is the same as ID.

function makeSelect($name, $options) {
	$result = "<select name=\"".$name."\" id=\"".$name."\">";

	$i = 0;
	foreach ($options as $key => $value) {
		$result .= "\n\t<option".($i = 0 ? " selected=\"selected\"" : "")." value=\"".$key."\">".$value."</option>";
	}

	$result .= "\n</select>";

	return $result;
}
