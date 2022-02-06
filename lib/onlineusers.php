<?php


function OnlineUsers($forum = 0, $update = true) {
	global $loguserid;
	$forumClause = "";
	$browseLocation = "online";

	if ($update) {
		if ($loguserid)
			Query("UPDATE {users} SET lastforum={0} WHERE id={1}", $forum, $loguserid);
		else
			Query("UPDATE {guests} SET lastforum={0} WHERE ip={1}", $forum, $_SERVER['REMOTE_ADDR']);
	}

	if ($forum) {
		$forumClause = " and lastforum={1}";
		$forumName = FetchResult("SELECT title FROM {forums} WHERE id={0}", $forum);
		$browseLocation = format("browsing {0}", $forumName);
	}

	$rOnlineUsers = Query("select u.(_userfields) from {users} u where (lastactivity > {0} or lastposttime > {0}) and loggedin = 1 ".$forumClause." order by name", time()-300, $forum);
	$onlineUserCt = 0;
	$onlineUsers = "";
	while($user = Fetch($rOnlineUsers)) {
		$user = getDataPrefix($user, "u_");
		$userLink = UserLink($user, true);
		$onlineUsers .= ($onlineUserCt ? ", " : "").$userLink;
		$onlineUserCt++;
	}
	//$onlineUsers = $onlineUserCt." "user".(($onlineUserCt > 1 || $onlineUserCt == 0) ? "s" : "")." ".$browseLocation.($onlineUserCt ? ": " : ".").$onlineUsers;
	$onlineUsers = Plural($onlineUserCt, "user")." ".$browseLocation.($onlineUserCt ? ": " : ".").$onlineUsers;

	$data = Fetch(Query("select
		(select count(*) from {guests} where bot=0 and date > {0} $forumClause) as guests,
		(select count(*) from {guests} where bot=1 and date > {0} $forumClause) as bots
		", (time() - 300), $forum));
	$guests = $data["guests"];
	$bots = $data["bots"];

	if ($guests)
		$onlineUsers .= " | ".Plural($guests,"guest");
	if ($bots)
		$onlineUsers .= " | ".Plural($bots,"bot");

//	$onlineUsers = "<div style=\"display: inline-block; height: 16px; overflow: hidden; padding: 0px; line-height: 16px;\">".$onlineUsers."</div>";
	return $onlineUsers;
}

function getOnlineUsersText() {
	global $OnlineUsersFid;

	$refreshCode = "";

	if (!isset($OnlineUsersFid))
		$OnlineUsersFid = 0;

	$onlineUsers = OnlineUsers($OnlineUsersFid);

	return "<span style=\"line-height:18px;\" id=\"onlineUsers\">$onlineUsers</span>$refreshCode";
}
