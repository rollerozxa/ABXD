<?php

/*
//Improved permissions system ~Nina
$groups = array();
$rGroups = query("SELECT * FROM {usergroups}");
while ($group = fetch($rGroups)) {
	$groups[] = $group;
	$groups[$grup['id']]['permissions'] = unserialize($group['permissions']);
}

//Do nothing for guests.
if (isset($loguserid) && isset($loguser['group'])) {
	$rPermissions = query("SELECT * FROM {userpermissions} WHERE uid={0}", $loguserid);
	$permissions = fetch($rPermissions);
	$permissions['permissions'] = unserialize($permissions['permissions']);
	if (is_array($groups[$loguser['group']]['permissions']))
		$loguser['permissions'] = array_merge($groups[$loguser['group']]['permissions'], $permissions); //$permissions overrides the group permissions here.
	if ($loguser['powerlevel'] == 4) $loguser['group'] == "root"; //Just in case.
}

//Returns false for guests no matter what. Returns if the user is allowed to do something otherwise.
//Additionally always returns true if the user's powerlevel is root.
function checkAllowed($p) {
	global $loguser, $loguserid;
	if (!$loguserid) return false;
	elseif ($loguser['group'] == "root" || $loguser['powerlevel'] == 4) return true;
	elseif (strpos('.', $p)) {
		$nodes = explode(".", $p);
		$r = $loguser['permissions'];
		foreach ($nodes as $n)
			$r = $r[$node];
		return $r;
	}
	else return $loguser['permissions'][$p];
}

*/


//Functions from old permissions system.
//I'm putting them here so we know what we have to rewrite/nuke ~Dirbaio


function CanMod($userid, $fid) {
	global $loguser;
	// Private messages. You cannot moderate them
	if (!$fid)
		return false;
	if ($loguser['powerlevel'] > 1)
		return true;
	if ($loguser['powerlevel'] == 1) {
		$rMods = Query("select * from {forummods} where forum={0} and user={1}", $fid, $userid);
		if (NumRows($rMods))
			return true;
	}
	return false;
}


function AssertForbidden($to, $specifically = 0) {
	global $loguser, $forbidden;
	if (!isset($forbidden))
		$forbidden = explode(" ", $loguser['forbiddens']);
	$caught = 0;
	if (in_array($to, $forbidden))
		$caught = 1;
	else {
		$specific = $to."[".$specifically."]";
		if (in_array($specific, $forbidden))
			$caught = 2;
	}

	if ($caught) {
		$not = "You are not allowed to {0}.";
		$messages = [
			"addRanks" => "add new ranks",
			"blockLayouts" => "block layouts",
			"deleteComments" => "delete usercomments",
			"editCats" => "edit the forum categories",
			"editForum" => "edit the forum list",
			"editIPBans" => "edit the IP ban list",
			"editMods" => "edit Local Moderator assignments",
			"editMoods" => "edit your mood avatars",
			"editPoRA" => "edit the PoRA box",
			"editPost" => "edit posts",
			"editProfile" => "edit your profile",
			"editSettings" => "edit the board settings",
			"editSmilies" => "edit the smiley list",
			"editThread" => "edit threads",
			"editUser" => "edit users",
			"haveCookie" => "have a cookie",
			"listPosts" => "see all posts by a given user",
			"makeComments" => "post usercomments",
			"makeReply" => "reply to threads",
			"makeThread" => "start new threads",
			"optimize" => "optimize the tables",
			"purgeRevs" => "purge old revisions",
			"recalculate" => "recalculate the board counters",
			"search" => "use the search function",
			"sendPM" => "send private messages",
			"snoopPM" => "view other users' private messages",
			"useUploader" => "upload files",
			"viewAdminRoom" => "see the admin room",
			"viewAvatars" => "see the avatar library",
			"viewCalendar" => "see the calendar",
			"viewForum" => "view fora",
			"viewLKB" => "see the Last Known Browser table",
			"viewMembers" => "see the memberlist",
			"viewOnline" => "see who's online",
			"viewPM" => "view private messages",
			"viewProfile" => "view user profiles",
			"viewRanks" => "see the rank lists",
			"viewRecords" => "see the top scores and DB usage",
			"viewThread" => "read threads",
			"viewUploader" => "see the uploader",
			"vote" => "vote",
		];
		$messages2 = [
			"viewForum" => "see this forum",
			"viewThread" => "read this thread",
			"makeReply" => "reply in this thread",
			"editUser" => "edit this user",
		];
		$bucket = "forbiddens"; include("./lib/pluginloader.php");
		if ($caught == 2 && array_key_exists($to, $messages2))
			Kill(format($not, $messages2[$to]), "Permission denied.");
		Kill(format($not, $messages[$to]), "Permission denied.");
	}
}

function IsAllowed($to, $specifically = 0) {
	global $loguser, $forbidden;
	if (!isset($forbidden))
		$forbidden = explode(" ", $loguser['forbiddens']);
	if (in_array($to, $forbidden))
		return FALSE;
	else {
		$specific = $to."[".$specifically."]";
		if (in_array($specific, $forbidden))
			return FALSE;
	}
	return TRUE;
}

