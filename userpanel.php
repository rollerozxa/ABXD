<?php
$userMenu = new PipeMenu();

if ($loguserid) {
	$userMenu->add(new PipeMenuHtmlEntry(userLink($loguser)));

	if (isAllowed("editProfile"))
		$userMenu->add(new PipeMenuLinkEntry("Edit profile", "editprofile", "", "", "pencil"));
	if (isAllowed("viewPM"))
		$userMenu->add(new PipeMenuLinkEntry("Private messages", "private", "", "", "envelope"));
	if (isAllowed("editMoods"))
		$userMenu->add(new PipeMenuLinkEntry("Mood avatars", "editavatars", "", "", "picture"));

	$bucket = "bottomMenu"; include("./lib/pluginloader.php");

	if (!isset($_POST['id']) && isset($_GET['id']))
		$_POST['id'] = (int)$_GET['id'];

	if (isset($user_panel))
		echo $user_panel;

	$userMenu->add(new PipeMenuLinkEntry("Log out", "", "", "", "signout", "document.forms[0].submit(); return false;"));
} else {
	$userMenu->add(new PipeMenuLinkEntry("Register", "register", "", "", "user"));
	$userMenu->add(new PipeMenuLinkEntry("Log in", "login", "", "", "signin"));
}

$layout_userpanel = $userMenu;
