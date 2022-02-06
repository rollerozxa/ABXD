<?php
//  AcmlmBoard XD - Thread submission/preview page
//  Access: users

$title = "New thread";

AssertForbidden("makeThread");

if (!$loguserid) //Not logged in?
	Kill("You must be logged in to post.");

if (isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if (!isset($_GET['id']))
	Kill("Forum ID unspecified.");

$fid = (int)$_GET['id'];

if ($loguser['powerlevel'] < 0)
	Kill("You're banned.");

$rFora = Query("select * from {forums} where id={0}", $fid);
if (NumRows($rFora))
	$forum = Fetch($rFora);
else
	Kill("Unknown forum ID.");

if ($forum['locked'])
	Kill("This forum is locked.");

if ($forum['minpowerthread'] > $loguser['powerlevel'])
	Kill("You are not allowed to post threads in this forum.");

if (!isset($_POST['poll']) || isset($_GET['poll']))
	$_POST['poll'] = $_GET['poll'];


$OnlineUsersFid = $fid;

$crumbs = new PipeMenu();
makeForumCrumbs($crumbs, $forum);
$crumbs->add(new PipeMenuTextEntry("New thread"));
makeBreadcrumbs($crumbs);


if (isset($_POST['actionpreview'])) {
	if ($_POST['poll']) {
		$options = [];
		$noColors = 0;
		$defaultColors = [
			"#000000","#0000B6","#00B600","#00B6B6","#B60000","#B600B6","#B66700","#B6B6B6",
			"#676767","#6767FF","#67FF67","#67FFFF","#FF6767","#FF67FF","#FFFF67","#FFFFFF",];
		for($i = 0; $i < $_POST['pollOptions']; $i++) {
			$options[] = ["choice"=>$_POST['pollOption'.$i], "color"=>$_POST['pollColor'.$i]];
		}
		$totalVotes = count($options);
		foreach($options as $option) {
			if ($option['color'] == "")
				$option['color'] = $defaultColors[($pops + 9) % 16];

			$votes = 1;

			$cellClass = ($cellClass+1) % 2;
			$label = format("{1}", $pc[$pops], htmlspecialchars($option['choice']));

			$bar = "";
			if ($totalVotes > 0) {
				$width = 100 * ($votes / $totalVotes);
				$alt = format("{0}&nbsp;of&nbsp;{1},&nbsp;{2}%", $votes, $totalVotes, $width);
				$bar = format("<div class=\"pollbar\" style=\"background: {0}; width: {1}%;\" title=\"{2}\">&nbsp;{3}</div>", $option['color'], $width, $alt, $votes);
				if ($width == 0)
					$bar = "&nbsp;".$votes;
			}

			$pollLines .= format(
"
	<tr class=\"cell{0}\">
		<td>
			{1}
		</td>
		<td class=\"width75\">
			<div class=\"pollbarContainer\">
				{2}
			</div>
		</td>
	</tr>
", $cellClass, $label, $bar);
			$pops++;
		}
		write(
"
	<table class=\"outline margin\">
		<tr class=\"header0\">
			<th colspan=\"2\">
				Poll
			</th>
		</tr>
		<tr class=\"cell0\">
			<td colspan=\"2\">
				{1}
			</td>
		</tr>
		{2}
	</table>
", $cellClass, htmlspecialchars($_POST['pollQuestion']), $pollLines);
	}

	$previewPost['text'] = $_POST["text"];
	$previewPost['num'] = $loguser['posts']+1;
	$previewPost['posts'] = $loguser['posts']+1;
	$previewPost['id'] = "_";
	$previewPost['options'] = 0;
	if ($_POST['nopl']) $previewPost['options'] |= 1;
	if ($_POST['nosm']) $previewPost['options'] |= 2;
	$previewPost['mood'] = (int)$_POST['mood'];

	foreach($loguser as $key => $value)
		$previewPost["u_".$key] = $value;

	MakePost($previewPost, POST_SAMPLE, ['forcepostnum'=>1, 'metatext'=>"Preview"]);
}
else if (isset($_POST['actionpost'])) {
	$titletags = parseThreadTags($_POST['title']);
	$trimmedTitle = trim(str_replace('&nbsp;', ' ', $titletags[0]));

	//Now check if the thread is acceptable.
	$rejected = false;

	if (!$_POST['text']) {
		Alert("Enter a message and try again.", "Your post is empty.");
		$rejected = true;
	}
	else if (!$trimmedTitle) {
		Alert("Enter a thread title and try again.", "Your thread is unnamed.");
		$rejected = true;
	}
	else if ($_POST['poll']) {
		$optionCount = 0;
		for($pops = 0; $pops < $_POST['pollOptions']; $pops++)
			if ($_POST['pollOption'.$pops])
				$optionCount++;

		if ($optionCount < 2) {
			Alert("You need to enter at least two options to make a poll.", "Invalid poll.");
			$rejected = true;
		}

		if (!$rejected && !$_POST["pollQuestion"]) {
			Alert("You need to enter a poll question to make a poll.", "Invalid poll.");
			$rejected = true;
		}
	}
	else {
		$lastPost = time() - $loguser['lastposttime'];
		if ($lastPost < Settings::get("floodProtectionInterval")) {
			//Check for last thread the user posted.
			$lastThread = Fetch(Query("SELECT * FROM {threads} WHERE user={0} ORDER BY id DESC LIMIT 1", $loguserid));

			//If it looks similar to this one, assume the user has double-clicked the button.
			if ($lastThread["forum"] == $fid && $lastThread["title"] == $_POST["title"])
				redirectAction("thread", $lastThread["id"]);

			$rejected = true;
			Alert("You're going too damn fast! Slow down a little.", "Hold your horses.");
		}
	}

	if (!$rejected) {
		$bucket = "checkPost"; include("./lib/pluginloader.php");
	}

	if (!$rejected) {
		$post = $_POST['text'];

		$options = 0;
		if ($_POST['nopl']) $options |= 1;
		if ($_POST['nosm']) $options |= 2;

		$closed = 0;
		$sticky = 0;
		if (CanMod($loguserid, $forum['id'])) {
			$closed = ($_POST['lock'] == 'on') ? '1':'0';
			$sticky = ($_POST['stick'] == 'on') ? '1':'0';
		}

		if ($_POST['poll']) {
			$doubleVote = ($_POST['multivote']) ? 1 : 0;
			$rPoll = Query("insert into {poll} (question, doublevote) values ({0}, {1})", $_POST['pollQuestion'], $doubleVote);
			$pod = InsertId();
			for($pops = 0; $pops < $_POST['pollOptions']; $pops++) {
				if ($_POST['pollOption'.$pops]) {
					$pollColor = filterPollColors($_POST['pollColor'.$pops]);
					$rPollOption = Query("insert into {poll_choices} (poll, choice, color) values ({0}, {1}, {2})", $pod, $_POST['pollOption'.$pops], $pollColor);
				}
			}
		}
		else
			$pod = 0;

		$now = time();

		$rThreads = Query("insert into {threads} (forum, user, title, lastpostdate, lastposter, closed, sticky, poll)
										  values ({0},   {1},  {2},   {3},          {1},        {4},   {5},     {6})",
										    $fid, $loguserid, $_POST['title'], time(), $closed, $sticky, $pod);
		$tid = InsertId();

		$rUsers = Query("update {users} set posts={0}, lastposttime={1} where id={2} limit 1", ($loguser['posts']+1), time(), $loguserid);

		$rPosts = Query("insert into {posts} (thread, user, date, ip, num, options, mood)
									  values ({0},{1},{2},{3},{4}, {5}, {6})", $tid, $loguserid, $now, $_SERVER['REMOTE_ADDR'], ($loguser['posts']+1), $options, (int)$_POST['mood']);
		$pid = InsertId();

		$rPostsText = Query("insert into {posts_text} (pid,text) values ({0},{1})", $pid, $post);

		$rFora = Query("update {forums} set numthreads=numthreads+1, numposts=numposts+1, lastpostdate={0}, lastpostuser={1}, lastpostid={2} where id={3} limit 1", time(), $loguserid, $pid, $fid);

		Query("update {threads} set firstpostid = {0}, lastpostid = {0}, date = {1} where id = {2}", $pid, $now, $tid);

		logAction('newthread', ['forum' => $fid, 'thread' => $tid]);

		//newthread bucket
		$postingAsUser = $loguser;
		$thread["title"] = $_POST['title'];
		$thread["id"] = $tid;
		$bucket = "newthread"; include("lib/pluginloader.php");

		redirectAction("thread", $tid);
	}
}

// Let the user try again.
$prefill = htmlspecialchars($_POST['text']);
$trefill = htmlspecialchars($_POST['title']);

function getCheck($name) {
	if (isset($_POST[$name]) && $_POST[$name])
		return "checked=\"checked\"";
	else return "";
}

if ($_POST["addpoll"])
	$_POST["poll"] = 1;

if ($_POST["deletepoll"])
	$_POST["poll"] = 0;

if ($_POST['poll']) {
	$first = true;
	$pollOptions = "";
	for($pops = 0; $pops < $_POST['pollOptions']; $pops++) {
		$cellClass = ($cellClass+1) % 2;
		$fixed = htmlspecialchars($_POST['pollOption'.$pops]);
		$pollOptions .= format(
"
						<tr class=\"cell{0}\">
							<td>
								<label for=\"p{1}\">Option {2}</label>
							</td>
							<td>
								<input type=\"text\" id=\"p{1}\" name=\"pollOption{1}\" value=\"{3}\" style=\"width: 50%;\" maxlength=\"40\" >&nbsp;
								<label>
									Color&nbsp;
									<input type=\"text\" name=\"pollColor{1}\" value=\"{4}\" size=\"10\" maxlength=\"7\" class=\"color {hash:true,required:false,pickerFaceColor:'black',pickerFace:3,pickerBorder:0,pickerInsetColor:'black',pickerPosition:'left',pickerMode:'HVS'}\">
								</label>
								{5}
							</td>
						</tr>
",	$cellClass, $pops, $pops + 1, $fixed,
	filterPollColors($_POST['pollColor'.$pops]), ($first ? "&nbsp;(#rrggbb)" : ""));
		$first = false;
	}

	$multivote = "<label><input type=\"checkbox\" ".($_POST['multivote'] ? "checked=\"checked\"" : " name=\"multivote\">&nbsp;Multivote</label>");

	$pollSettings = "
		<tr class=\"cell0\">
			<td>
				<label for=\"pq\">
					Poll question
				</label>
			</td>
			<td>
				<input type=\"text\" id=\"pq\" name=\"pollQuestion\" value=\"".htmlspecialchars($_POST['pollQuestion'])."\" style=\"width: 98%;\" maxlength=\"100\">
			</td>
		</tr>
		<tr class=\"cell1\">
			<td>
				<label for=\"pn\">
					Number of options
				</label>
			</td>
			<td>
				<input type=\"text\" id=\"pn\" name=\"pollOptions\" value=\"".htmlspecialchars($_POST['pollOptions'])."\" size=\"2\" maxlength=\"2\">
				<input type=\"submit\" name=\"actionsetpoll\" value=\"Set\">
			</td>
		</tr>
		<tr class=\"cell0\">
			<td>
			</td>
			<td>
				$multivote
			</td>
		</tr>
		$pollOptions";
	$pollSettings .= "<tr class=\"cell1\"><td></td><td><input type=\"submit\" name=\"deletepoll\" value=\"Delete poll\"></td></tr>";

}
else
	$pollSettings = "<tr class=\"cell1\"><td></td><td><input type=\"submit\" name=\"addpoll\" value=\"Add poll\"></td></tr>";

$pollSettings = "
	<tr class=\"cell0\"><td colspan=\"2\"></td></tr>
	$pollSettings
	<tr class=\"cell0\"><td  colspan=\"2\"></td></tr>";


if ($_POST['mood'])
	$moodSelects[(int)$_POST['mood']] = "selected=\"selected\" ";
$moodOptions = "<option ".$moodSelects[0]."value=\"0\">[Default avatar]</option>\n";
$rMoods = Query("select mid, name from {moodavatars} where uid={0} order by mid asc", $loguserid);
while($mood = Fetch($rMoods))
	$moodOptions .= format(
"
	<option {0} value=\"{1}\">{2}</option>
",	$moodSelects[$mood['mid']], $mood['mid'], htmlspecialchars($mood['name']));

if (CanMod($loguserid, $forum['id'])) {
	$mod = "\n\n<!-- Mod options -->\n";
	$mod .= "<label><input type=\"checkbox\" ".getCheck("lock")." name=\"lock\">&nbsp;Close thread</label>\n";
	$mod .= "<label><input type=\"checkbox\" ".getCheck("stick")." name=\"stick\">&nbsp;Sticky</label>\n";
}

if (!$_POST['poll'] || $_POST['pollOptions'])
	$postButton = "<input type=\"submit\" name=\"actionpost\" value=\"Post\"> ";


echo "<script src=\"".resourceLink("js/threadtagging.js")."\"></script>";

echo "
	<script type=\"text/javascript\">
			window.addEventListener(\"load\",  hookUpControls, false);
	</script>";


$form = "
		<form name=\"postform\" action=\"".actionLink("newthread", $fid)."\" method=\"post\">
			<table class=\"outline margin width100\">
				<tr class=\"header1\">
					<th colspan=\"2\">
						New thread
					</th>
				</tr>
				<tr class=\"cell0\">
					<td>
						<label for=\"tit\">
							Title
						</label>
					</td>
					<td id=\"threadTitleContainer\">
						<input type=\"text\" id=\"tit\" name=\"title\" style=\"width: 98%;\" maxlength=\"60\" value=\"$trefill\">
					</td>
				</tr>
				$pollSettings
				<tr class=\"cell0\">
					<td colspan=\"2\">
						<textarea id=\"text\" name=\"text\" rows=\"16\" style=\"width: 98%;\">$prefill</textarea>
					</td>
				</tr>
				<tr class=\"cell2\">
					<td></td>
					<td>
						$postButton
						<input type=\"submit\" name=\"actionpreview\" value=\"Preview\">
						<select size=\"1\" name=\"mood\">
							$moodOptions
						</select>
						<label>
							<input type=\"checkbox\" name=\"nopl\" ".getCheck("nopl").">&nbsp;Disable post layout
						</label>
						<label>
							<input type=\"checkbox\" name=\"nosm\" ".getCheck("nosm").">&nbsp;Disable smilies
						</label>
						<input type=\"hidden\" name=\"id\" value=\"$fid\">
						<input type=\"hidden\" name=\"poll\" value=\"".htmlspecialchars($_POST['poll'])."\">
						$mod
					</td>
				</tr>
			</table>
		</form>";

doPostForm($form);
