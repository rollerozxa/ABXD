<?php
$navigation = new PipeMenu();

if ($loguser['powerlevel'] >= 3 && isAllowed("viewAdminRoom"))
	$navigation->add(new PipeMenuLinkEntry("Admin", "admin", "", "", "cogs"));

$bucket = "topMenuStart"; include("./lib/pluginloader.php");

$navigation->add(new PipeMenuLinkEntry(Settings::get("menuMainName"), "board", "", "", "home"));
if (isAllowed("viewMembers"))
	$navigation->add(new PipeMenuLinkEntry("Member list", "memberlist", "", "", "group"));
if (isAllowed("viewRanks"))
	$navigation->add(new PipeMenuLinkEntry("Ranks", "ranks", "", "", "trophy"));
if (isAllowed("viewOnline"))
	$navigation->add(new PipeMenuLinkEntry("Online users", "online", "", "", "eye-open"));
if (isAllowed("search"))
	$navigation->add(new PipeMenuLinkEntry("Search", "search", "", "", "search"));

$navigation->add(new PipeMenuLinkEntry("Last posts", "lastposts", "", "", "reorder"));

$bucket = "topMenu"; include("./lib/pluginloader.php");

$layout_navigation = $navigation;
