<?php

function getRefreshActionLink() {
	$args = "ajax=1";

	if (isset($_GET["from"]))
		$args .= "&from=".$_GET["from"];

	return actionLink((isset($_GET["page"]) ? $_GET['page'] : 0), (isset($_GET['id']) ? $_GET["id"] : 0), $args);
}

function printRefreshCode() {
	if (Settings::get("ajax"))
		write(
	"
		<script type=\"text/javascript\">
			refreshUrl = ".json_encode(getRefreshActionLink()).";
		</script>
	");
}

function urlNamify($urlname) {
	$urlname = strtolower($urlname);
	$urlname = str_replace("&", "and", $urlname);
	$urlname = preg_replace("/[^a-zA-Z0-9]/", "-", $urlname);
	$urlname = preg_replace("/-+/", "-", $urlname);
	$urlname = preg_replace("/^-/", "", $urlname);
	$urlname = preg_replace("/-$/", "", $urlname);
	return $urlname;
}

$urlNameCache = [];
function setUrlName($action, $id, $urlname) {
	global $urlNameCache;
	$urlNameCache[$action."_".$id] = $urlname;
}

if (isset($urlRewriting))
	include("urlrewriting.php");
else {
	function actionLink($action, $id="", $args="", $urlname="") {
		global $boardroot, $mainPage;
		if ($boardroot == "")
			$boardroot = "./";

		$bucket = "linkMangler"; include('lib/pluginloader.php');

		$res = "";

		if ($action != $mainPage)
			$res .= "&page=$action";

		if ($id != "")
			$res .= "&id=".urlencode($id);
		if ($args)
			$res .= "&$args";

		if (strpos($res, "&amp")) {
			debug_print_backtrace();
			Kill("Found &amp;amp; in link");
		}

		if ($res == "")
			return $boardroot;
		else
			return $boardroot."?".substr($res, 1);
	}
}

function actionLinkTag($text, $action, $id=0, $args="", $urlname="") {
	return '<a href="'.htmlentities(actionLink($action, $id, $args, $urlname)).'">'.$text.'</a>';
}
function actionLinkTagItem($text, $action, $id=0, $args="", $urlname="") {
	return '<li><a href="'.htmlentities(actionLink($action, $id, $args, $urlname)).'">'.$text.'</a></li>';
}

function actionLinkTagConfirm($text, $prompt, $action, $id=0, $args="") {
	return '<a onclick="return confirm(\''.$prompt.'\'); " href="'.htmlentities(actionLink($action, $id, $args)).'">'.$text.'</a>';
}
function actionLinkTagItemConfirm($text, $prompt, $action, $id=0, $args="") {
	return '<li><a onclick="return confirm(\''.$prompt.'\'); " href="'.htmlentities(actionLink($action, $id, $args)).'">'.$text.'</a></li>';
}

function redirectAction($action, $id=0, $args="", $urlname="") {
	redirect(actionLink($action, $id, $args, $urlname));
}

function redirect($url) {
	header("Location: ".$url);
	die();
}

function resourceLink($what) {
	global $boardroot;
	return "$boardroot$what";
}

function themeResourceLink($what) {
	global $theme, $boardroot;
	return $boardroot."themes/$theme/$what";
}

function getMinipicTag($user) {
	global $dataUrl;
	$minipic = "";
	if ($user["minipic"] == "#INTERNAL#")
		$minipic = "<img src=\"${dataUrl}minipics/${user["id"]}\" alt=\"\" class=\"minipic\">&nbsp;";
	else if ($user["minipic"])
		$minipic = "<img src=\"".$user['minipic']."\" alt=\"\" class=\"minipic\">&nbsp;";
	return $minipic;
}

$powerlevels = [-1 => "banned", 0 => "", 1 => "local mod", 2 => "full mod", 3 => "admin", 4 => "root", 5 => "system"];

function userLink($user, $showMinipic = false, $customID = false) {
	global $powerlevels;

	$bucket = "userMangler"; include("./lib/pluginloader.php");

	$fpow = $user['powerlevel'];
	$fcolor = $user['colorset'];
	$fname = ($user['displayname'] ? $user['displayname'] : $user['name']);
	$fname = htmlspecialchars($fname);
	$fname = str_replace(" ", "&nbsp;", $fname);
	$pronouns = explode("/", $user['pronouns']);

	$minipic = "";
	if ($showMinipic || Settings::get("alwaysMinipic"))
		$minipic = getMinipicTag($user);
	{
	}

	$fname = $minipic.$fname;

	if (!Settings::get("showGender"))
		$fcolor = 2;

	if ($fpow < 0) $fpow = -1;
	$classing = " class=\"nc" . $fcolor . (($fpow < 0) ? "x" : $fpow)."\"";

	if ($customID)
		$classing .= " id=\"$customID\"";

	$bucket = "userLink"; include('lib/pluginloader.php');
	if ($user["powerlevel"])
		$plstring = ", ".$powerlevels[$user['powerlevel']];
	else
		$plstring = "";

	$pronounStr = "";
	if (count($pronouns) == 5) {
		$pronounStr = ", " . $pronouns[0] . "/" . $pronouns[1];
	}

	$title = "#".$user["id"].": ".htmlspecialchars($user['name']) . " (".$plstring."".$pronounStr.")";
	$userlink = actionLinkTag("<span$classing title=\"$title\">$fname</span>", "profile", $user["id"], "", $user["name"]);
	return $userlink;
}

function userLinkById($id) {
	global $userlinkCache;

	if (!isset($userlinkCache[$id])) {
		$rUser = Query("SELECT u.(_userfields) FROM {users} u WHERE u.id={0}", $id);
		if (NumRows($rUser))
			$userlinkCache[$id] = getDataPrefix(Fetch($rUser), "u_");
		else
			$userlinkCache[$id] = ['id' => 0, 'name' => "Unknown User", 'sex' => 0, 'powerlevel' => -1];
	}
	return UserLink($userlinkCache[$id]);
}

function makeThreadLink($thread) {
	$tags = ParseThreadTags($thread["title"]);
	setUrlName("thread", $thread["id"], $tags[0]);
	$link = actionLinkTag($tags[0], "thread", $thread["id"], "", $tags[0]);
	$tags = $tags[1];

	if (Settings::get("tagsDirection") === 'Left')
		return $tags." ".$link;
	else
		return $link." ".$tags;

}
function makeFromUrl($url, $from) {
	if ($from == 0) {
		//This is full of hax.
		$url = str_replace("&amp;from=", "", $url);
		$url = str_replace("&from=", "", $url);
		$url = str_replace("?from=", "", $url);
		if (endsWith($url, "?"))
			$url = substr($url, 0, strlen($url)-1);
		return $url;
	}
	else return $url.$from;
}

function pageLinks($url, $epp, $from, $total) {
	$url = htmlspecialchars($url);

	if ($from < 0) $from = 0;
	if ($from > $total-1) $from = $total-1;
	$from -= $from % $epp;

	$numPages = (int)ceil($total / $epp);
	$page = (int)ceil($from / $epp) + 1;

	$first = ($from > 0) ? "<a class=\"pagelink\" href=\"".makeFromUrl($url, 0)."\">&#x00AB;</a> " : "";
	$prev = $from - $epp;
	if ($prev < 0) $prev = 0;
	$prev = ($from > 0) ? "<a class=\"pagelink\"  href=\"".makeFromUrl($url, $prev)."\">&#x2039;</a> " : "";
	$next = $from + $epp;
	$last = ($numPages * $epp) - $epp;
	if ($next > $last) $next = $last;
	$next = ($from < $total - $epp) ? " <a class=\"pagelink\"  href=\"".makeFromUrl($url, $next)."\">&#x203A;</a>" : "";
	$last = ($from < $total - $epp) ? " <a class=\"pagelink\"  href=\"".makeFromUrl($url, $last)."\">&#x00BB;</a>" : "";

	$pageLinks = [];
	for($p = $page - 5; $p < $page + 5; $p++) {
		if ($p < 1 || $p > $numPages)
			continue;
		if ($p == $page || ($from == 0 && $p == 1))
			$pageLinks[] = "<span class=\"pagelink\">$p</span>";
		else
			$pageLinks[] = "<a class=\"pagelink\"  href=\"".makeFromUrl($url, (($p-1) * $epp))."\">".$p."</a>";
	}

	return $first.$prev.$next.$last;
}

function pageLinksInverted($url, $epp, $from, $total) {
	$url = htmlspecialchars($url);

	if ($from < 0) $from = 0;
	if ($from > $total-1) $from = $total-1;
	$from -= $from % $epp;

	$numPages = (int)ceil($total / $epp);
	$page = (int)ceil($from / $epp) + 1;

	$first = ($from > 0) ? "<a class=\"pagelink\" href=\"".makeFromUrl($url, 0)."\">&#x00BB;</a> " : "";
	$prev = $from - $epp;
	if ($prev < 0) $prev = 0;
	$prev = ($from > 0) ? "<a class=\"pagelink\"  href=\"".makeFromUrl($url, $prev)."\">&#x203A;</a> " : "";
	$next = $from + $epp;
	$last = ($numPages * $epp) - $epp;
	if ($next > $last) $next = $last;
	$next = ($from < $total - $epp) ? " <a class=\"pagelink\"  href=\"".makeFromUrl($url, $next)."\">&#x2039;</a>" : "";
	$last = ($from < $total - $epp) ? " <a class=\"pagelink\"  href=\"".makeFromUrl($url, $last)."\">&#x00AB;</a>" : "";

	$pageLinks = [];
	for($p = $page + 5; $p >= $page - 5; $p--) {
		if ($p < 1 || $p > $numPages)
			continue;
		if ($p == $page || ($from == 0 && $p == 1))
			$pageLinks[] = "<span class=\"pagelink\">".($numPages+1-$p)."</span>";
		else
			$pageLinks[] = "<a class=\"pagelink\"  href=\"".makeFromUrl($url, (($p-1) * $epp))."\">".($numPages+1-$p)."</a>";
	}

	return $last.$next.implode("", $pageLinks).$prev.$first;
}


function absoluteActionLink($action, $id=0, $args="") {
	$https = isHttps();
	return ($https?"https":"http") . "://" . $_SERVER['SERVER_NAME'].actionLink($action, $id, $args);
}

function getRequestedURL() {
	return $_SERVER['REQUEST_URI'];
}

function getServerURL() {
	return getServerURLNoSlash()."/";
}

function getServerURLNoSlash() {
	global $boardroot;
	$https = isHttps();
	$stdport = $https?443:80;
	$port = "";
	if ($stdport != $_SERVER["SERVER_PORT"] && $_SERVER["SERVER_PORT"])
		$port = ":".$_SERVER["SERVER_PORT"];
	return ($https?"https":"http") . "://" . $_SERVER['HTTP_HOST'] . $port . substr($boardroot, 0, strlen($boardroot)-1);
}

function getFullRequestedURL() {
	$https = isHttps();
	$stdport = $https?443:80;
	$port = "";
	if ($stdport != $_SERVER["SERVER_PORT"] && $_SERVER["SERVER_PORT"])
		$port = ":".$_SERVER["SERVER_PORT"];
	return ($https?"https":"http") . "://" . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'];
}

function isHttps() {
	return isset($_SERVER['HTTPS']) || $_SERVER["SERVER_PORT"] == 443;
}

function getFullURL() {
	return getFullRequestedURL();
}
