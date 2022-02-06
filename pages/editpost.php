<?php
//  AcmlmBoard XD - Post editing page
//  Access: users

$title = "Edit post";

if (!$loguserid)
	Kill("You must be logged in to edit your posts.");

if ($loguser['powerlevel'] < 0)
	Kill("Banned users can't edit their posts.");

if (isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if (!isset($_GET['id']))
	Kill("Post ID unspecified.");

$pid = (int)$_GET['id'];
AssertForbidden("editPost", $pid);

$rPost = Query("
	SELECT
		{posts}.*,
		{posts_text}.text
	FROM {posts}
		LEFT JOIN {posts_text} ON {posts_text}.pid = {posts}.id AND {posts_text}.revision = {posts}.currentrevision
	WHERE id={0}", $pid);

if (NumRows($rPost)) {
	$post = Fetch($rPost);
	$tid = $post['thread'];
}
else
	Kill("Unknown post ID.");

$rUser = Query("select * from {users} where id={0}", $post['user']);
if (NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill("Unknown user ID.");

$rThread = Query("select * from {threads} where id={0}", $tid);
if (NumRows($rThread))
	$thread = Fetch($rThread);
else
	Kill("Unknown thread ID.");
AssertForbidden("viewThread", $tid);

$rFora = Query("select * from {forums} where id={0}", $thread['forum']);
if (NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill("Unknown forum ID.");

if ($loguser['powerlevel'] < $forum['minpower'])
	Kill("You are not allowed to browse this forum.");
$fid = $forum['id'];
AssertForbidden("viewForum", $fid);

//-- Mark as New if last post is edited --
$wasLastPost = ($thread['lastpostdate'] == $post['date']);

$fid = $thread['forum'];

if ($post['deleted'])
	Kill("This post has been deleted."));

if (!CanMod($loguserid, $fid) && $post['user'] != $loguserid)
	Kill("You are not allowed to edit posts.");

if ($thread['closed'] && !CanMod($loguserid, $fid))
	Kill("This thread is closed.");

$tags = ParseThreadTags($thread['title']);
setUrlName("thread", $thread["id"], $thread["title"]);

$crumbs = new PipeMenu();
makeForumCrumbs($crumbs, $forum);
$crumbs->add(new PipeMenuHtmlEntry(makeThreadLink($thread)));
$crumbs->add(new PipeMenuTextEntry("Edit post"));
makeBreadcrumbs($crumbs);

write("
	<script type=\"text/javascript\">
			window.addEventListener(\"load\",  hookUpControls, false);
	</script>
");

if (isset($_POST['actionpreview'])) {
	$previewPost['text'] = $_POST["text"];
	$previewPost['num'] = $post['num'];
	$previewPost['id'] = "_";
	$previewPost['options'] = 0;
	if ($_POST['nopl']) $previewPost['options'] |= 1;
	if ($_POST['nosm']) $previewPost['options'] |= 2;
	$previewPost['mood'] = (int)$_POST['mood'];
	foreach($user as $key => $value)
		$previewPost["u_".$key] = $value;
	MakePost($previewPost, POST_SAMPLE, ['forcepostnum'=>1, 'metatext'=>"Preview"]);
}
else if (isset($_POST['actionpost'])) {
	if ($_POST['key'] != $loguser['token']) Kill("No.");

	$rejected = false;

	if (!$_POST['text']) {
		Alert("Enter a message and try again.", "Your post is empty.");
		$rejected = true;
	}

	if (!$rejected) {
		$bucket = "checkPost"; include("./lib/pluginloader.php");
	}

	if (!$rejected) {
		$options = 0;
		if ($_POST['nopl']) $options |= 1;
		if ($_POST['nosm']) $options |= 2;

		$now = time();
		$rev = fetchResult("select max(revision) from {posts_text} where pid={0}", $pid);
		$rev++;
		$rPostsText = Query("insert into {posts_text} (pid,text,revision,user,date) values ({0}, {1}, {2}, {3}, {4})",
							$pid, $_POST["text"], $rev, $loguserid, $now);

		$rPosts = Query("update {posts} set options={0}, mood={1}, currentrevision = currentrevision + 1 where id={2} limit 1",
						$options, (int)$_POST['mood'], $pid);

		//Update thread lastpostdate if we edited the last post
		if ($wasLastPost)
			Query("update {threads} set lastpostdate={0} WHERE id={1} limit 1", $now, $thread['id']);

		logAction('editpost', ['forum' => $fid, 'thread' => $tid, 'user2' => $post["user"], 'post' => $pid]);

		redirectAction("post", $pid);
	}
}

if (isset($_POST['actionpreview']) || isset($_POST['actionpost'])) {
	$prefill = $_POST['text'];
	if ($_POST['nopl']) $nopl = "checked=\"checked\"";
	if ($_POST['nosm']) $nosm = "checked=\"checked\"";
} else {
	$prefill = $post['text'];
	if ($post['options'] & 1) $nopl = "checked=\"checked\"";
	if ($post['options'] & 2) $nosm = "checked=\"checked\"";
	$_POST['mood'] = $post['mood'];
}

if ($_POST['mood'])
	$moodSelects[(int)$_POST['mood']] = "selected=\"selected\" ";
$moodOptions = Format("<option {0}value=\"0\">[Default avatar]</option>\n", $moodSelects[0]);
$rMoods = Query("select mid, name from {moodavatars} where uid={0} order by mid asc", $post['user']);
while($mood = Fetch($rMoods))
	$moodOptions .= Format("<option {0}value=\"{1}\">{2}</option>\n", $moodSelects[$mood['mid']], $mood['mid'], htmlspecialchars($mood['name']));

$form = "
	<form name=\"postform\" action=\"".actionLink("editpost")."\" method=\"post\">
		<table class=\"outline margin width100\">
			<tr class=\"header1\">
				<th colspan=\"2\">
					Edit Post
				</th>
			</tr>
			<tr class=\"cell0\">
				<td colspan=\"2\">
					<textarea id=\"text\" name=\"text\" rows=\"16\" style=\"width: 98%;\">".htmlspecialchars($prefill)."</textarea>
				</td>
			</tr>
			<tr class=\"cell2\">
				<td></td>
				<td>
					<input type=\"submit\" name=\"actionpost\" value=\"Edit\">
					<input type=\"submit\" name=\"actionpreview\" value=\"Preview\">
					<select size=\"1\" name=\"mood\">
						$moodOptions
					</select>
					<label>
						<input type=\"checkbox\" name=\"nopl\" $pid>&nbsp;Disable post layout
					</label>
					<label>
						<input type=\"checkbox\" name=\"nosm\" $nosm>&nbsp;Disable smilies
					</label>
					<input type=\"hidden\" name=\"id\" value=\"$pid\">
					<input type=\"hidden\" name=\"key\" value=\"".$loguser['token']."\">
				</td>
			</tr>
		</table>
	</form>";

doPostForm($form);

doThreadPreview($tid);
