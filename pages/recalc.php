<?php
//  AcmlmBoard XD - Report/content mismatch fixing utility
//  Access: staff

AssertForbidden("recalculate");

if ($loguser['powerlevel'] < 1)
	Kill("Staff only, please.");

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Admin", "admin"));
$crumbs->add(new PipeMenuLinkEntry("Recalculate statistics", "recalc"));
makeBreadcrumbs($crumbs);

function startFix() {
	global $fixtime, $aff;
	$aff = -1;
	$fixtime = usectime();
}

function reportFix($what, $aff = -1) {
	global $fixtime, $aff;

	if ($aff == -1)
		$aff = affectedRows();
	echo $what, " ", format("{0} rows affected.", $aff), " time: ", sprintf('%1.3f', usectime()-$fixtime), "<br>";
}

$debugQueries = false;

startFix();
query("UPDATE {users} u SET posts =
			(SELECT COUNT(*) FROM {posts} p WHERE p.user = u.id)
		");
reportFix("Counting user's posts&hellip;");

startFix();
query("UPDATE {threads} t SET replies =
			(SELECT COUNT(*) FROM {posts} p WHERE p.thread = t.id) - 1
		");
reportFix("Counting thread replies&hellip;");

startFix();
query("UPDATE {forums} f SET numthreads =
			(SELECT COUNT(*) FROM {threads} t WHERE t.forum = f.id)
		");
reportFix("Counting forum threads&hellip;");

startFix();
query("UPDATE {forums} f SET numposts =
			(SELECT SUM(replies+1) FROM {threads} t WHERE t.forum = f.id)
		");
reportFix("Counting forum posts&hellip;");

startFix();
//For some reason, this beautiful query will set MySQL to use 100% CPU and never finishes.
/*query("UPDATE {threads} t SET
			lastpostid = (SELECT p.id FROM {posts} p WHERE p.thread = t.id ORDER BY date DESC LIMIT 0,1),
			lastposter = (SELECT p.user FROM {posts} p WHERE p.thread = t.id ORDER BY date DESC LIMIT 0,1),
			lastpostdate = (SELECT p.date FROM {posts} p WHERE p.thread = t.id ORDER BY date DESC LIMIT 0,1)
		");*/

$aff = 0;
$rForum = Query("select * from {forums}");
while($forum = Fetch($rForum)) {
	$rThread = Query("select * from {threads} where forum = {0} order by lastpostdate desc", $forum['id']);
	$first = 1;
	while($thread = Fetch($rThread)) {
		$lastPost = Fetch(Query("select * from {posts} where thread = {0} order by date desc limit 0,1", $thread['id']));
		$firstPost = Fetch(Query("select * from {posts} where thread = {0} order by date asc limit 0,1", $thread['id']));
		Query("update {threads} set lastpostid = {0}, lastposter = {1}, lastpostdate = {2}, date = {3}, firstpostid={4}, user={5} where id = {6}",
			(int)$lastPost['id'], (int)$lastPost['user'], (int)$lastPost['date'], (int)$firstPost['date'], (int)$firstPost['id'], (int)$firstPost['user'], $thread['id']);

		$aff += affectedRows();
		if ($first) {
			Query("update {forums} set lastpostid = {0}, lastpostuser = {1}, lastpostdate = {2} where id = {3}", (int)$lastPost['id'], (int)$lastPost['user'], (int)$lastPost['date'], $forum['id']);
			$aff += affectedRows();
		}
		$first = 0;
	}
}
reportFix("Updating threads dates and post IDs&hellip;");

$bucket = "recalc"; include("./lib/pluginloader.php");
print "<br>All done!<br>";
