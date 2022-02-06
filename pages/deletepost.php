<?php

$ajaxPage = 1;

if (!isset($_POST['id']))
	Kill("Post ID unspecified.");
if ($_POST['key'] != $loguser['token'])
	Kill("No.");

$pid = (int)$_POST['id'];

$rPost = Query("SELECT * FROM {posts} WHERE id={0}", $pid);

if (NumRows($rPost))
	$post = Fetch($rPost);
else
	Kill("Unknown post ID.");

$tid = $post['thread'];

$rThread = Query("select * from {threads} where id={0}", $tid);
if (NumRows($rThread))
	$thread = Fetch($rThread);
else
	Kill("Unknown thread ID.");

$rFora = Query("select * from {forums} where id={0}", $thread['forum']);
if (NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill("Unknown forum ID.");

$fid = $forum['id'];

if (!CanMod($loguserid,$fid))
	Kill("You're not allowed to delete posts.");

$del = (int)$_POST['delete'];

if ($del == 1) {
	Query("update {posts} set deleted=1,deletedby={0},reason={1} where id={2} limit 1", $loguserid, $_POST['reason'], $pid);
	logAction('deletepost', ['forum' => $fid, 'thread' => $tid, 'user2' => $post["user"], 'post' => $pid]);
} else if ($del == 2) {
	Query("update {posts} set deleted=0 where id={0} limit 1", $pid);
	logAction('undeletepost', ['forum' => $fid, 'thread' => $tid, 'user2' => $post["user"], 'post' => $pid]);
}
