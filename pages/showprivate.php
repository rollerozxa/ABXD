<?php
//  AcmlmBoard XD - Private message display page
//  Access: user, specifically the sender or reciever.

$title = "Private messages";

AssertForbidden("viewPM");

if (!$loguserid)
	Kill("You must be logged in to view your private messages.");

if (!isset($_GET['id']) && !isset($_POST['id']))
	Kill("No PM specified.");

$id = (int)(isset($_GET['id']) ? $_GET['id'] : $_POST['id']);
$pmid = $id;

if (isset($_GET['snooping'])) {
	if ($loguser['powerlevel'] > 2)
		$rPM = Query("select * from {pmsgs} left join {pmsgs_text} on pid = {pmsgs}.id where {pmsgs}.id = {0}", $id);
	else
		Kill("No snooping for you.");
}
else
	$rPM = Query("select * from {pmsgs} left join {pmsgs_text} on pid = {pmsgs}.id where (userto = {1} or userfrom = {1}) and {pmsgs}.id = {0}", $id, $loguserid);

if (NumRows($rPM))
	$pm = Fetch($rPM);
else
	Kill("Unknown PM");

if ($pm['drafting'] && $pm['userfrom'] != $loguserid)
	Kill("Unknown PM"); //could say "PM is addresssed to you, but is being drafted", but what they hey?

$rUser = Query("select * from {users} where id = {0}", $pm['userfrom']);
if (NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill("Unknown user.");

$links = new PipeMenu();
if (!isset($_GET['snooping']) && $pm['userto'] == $loguserid) {
	$qPM = "update {pmsgs} set msgread=1 where id={0}";
	$rPM = Query($qPM, $pm['id']);
	$links->add(new PipeMenuLinkEntry("Send reply", "sendprivate", "", "pid=".$pm['id'], "reply"));
}
else if (!isset($_GET['snooping']) && $pm['drafting']) {
	if ($pm['userfrom'] != $loguserid)
		Kill("This PM is still being drafted.");
	else
		$draftEditor = true;
}
else if (isset($_GET['snooping']))
	Alert("You are snooping.");

$pmtitle = htmlspecialchars($pm['title']); //sender's custom title overwrites this below, so save it here

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Member list", "memberlist"));
$crumbs->add(new PipeMenuHtmlEntry(userLinkById($pm["userto"])));
$crumbs->add(new PipeMenuLinkEntry("Private messages", "private", $pm["userto"]==$loguserid?"":$pm["userto"]));
$crumbs->add(new PipeMenuTextEntry($pmtitle));
makeBreadcrumbs($crumbs);
makeLinks($links);

$pm['num'] = "preview";
$pm['posts'] = $user['posts'];
$pm['id'] = "_";

foreach($user as $key => $value)
	$pm["u_".$key] = $value;

if ($draftEditor) {
	write(
"
	<script type=\"text/javascript\">
			window.addEventListener(\"load\",  hookUpControls, false);
	</script>
");


	$rUser = Query("select name from {users} where id={0}", $pm['userto']);
	if (!NumRows($rUser)) {
		if ($_POST['action'] == "Send")
			Kill("Unknown user.");
	}
	$user = Fetch($rUser);

	if ($_POST['action'] == "Preview") {
		$pm['text'] = $_POST['text'];
		$pmtitle = $_POST['title'];
	}

	if ($_POST['action'] == "Discard Draft") {
		Query("delete from {pmsgs} where id = {0}", $pmid);
		Query("delete from {pmsgs_text} where pid = {0}", $pmid);

		redirectAction("private");
		exit();
	}

	if (substr($pm['text'], 0, 17) == "<!-- ###MULTIREP:") {
		$to = substr($pm['text'], 17, strpos($pm['text'], "### -->") - 18);
		$pm['text'] = substr($pm['text'], strpos($pm['text'], "### -->") + 7);
	}

	if ($_POST['action'] == "Send" || $_POST['action'] == "Update Draft") {
		$recipIDs = [];
		if ($_POST['to']) {
			$firstTo = -1;
			$recipients = explode(";", $_POST['to']);
			foreach($recipients as $to) {
				$to = trim(htmlentities($to));
				if ($to == "")
					continue;
				$rUser = Query("select id from {users} where name={0} or displayname={0}", $to);
				if (NumRows($rUser)) {
					$user = Fetch($rUser);
					$id = $user['id'];
					if ($firstTo == -1)
						$firstTo = $id;
					if ($id == $loguserid)
						$errors .= "You can't send private messages to yourself.<br>";
					else if (!in_array($id, $recipIDs))
						$recipIDs[] = $id;
				}
				$maxRecips = [-1 => 1, 3, 3, 3, 10, 100, 1];
				$maxRecips = $maxRecips[$loguser['powerlevel']];
				//$maxRecips = ($loguser['powerlevel'] > 1) ? 5 : 1;
				if (count($recipIDs) > $maxRecips)
					$errors .= "Too many recipients.";
				else
					$errors .= format("Unknown user \"{0}\"", $to)."<br>";
			}
			if ($errors != "") {
				Alert($errors);
				$_POST['action'] = "";
			}
		}
		else {
			if ($_POST['action'] == "Send")
				Alert("Enter a recipient and try again.", "Your PM has no recipient.");
			$_POST['action'] = "";
		}

		if ($_POST['title']) {
			$_POST['title'] = $_POST['title'];

			if ($_POST['text']) {
				if ($_POST['action'] == "Update Draft") {
					$post = $pm['text'];
					$post = preg_replace("'/me '","[b]* ".$loguser['name']."[/b] ", $post); //to prevent identity confusion
						$post = "<!-- ###MULTIREP:".$_POST['to']." ### -->".$post;

					$rPMT = Query("update {pmsgs_text} set title = {0}, text = {1} where pid = {2}", $_POST['title'], $post, $pmid);
					$rPM = Query("update {pmsgs} set userto = {0} where id = {1}", $firstTo, $pmid);

					redirectAction("private", "", "show=2");
				}
				else {
					$post = $pm['text'];
					$post = preg_replace("'/me '","[b]* ".$loguser['name']."[/b] ", $post); //to prevent identity confusion

					$rPMT = Query("update {pmsgs_text} set title = {0}, text = {1} where pid = {2}", $_POST['title'], $post, $pmid);
					$rPM = Query("update {pmsgs} set drafting = 0 where id = {0}", $pmid);

					foreach($recipIDs as $recipient) {
						if ($recipient == $firstTo)
							continue;

						$rPM = Query("insert into {pmsgs} (userto, userfrom, date, ip, msgread) values ({0}, {1}, {2}, {3}, 0)", $recipient, $loguserid, time(), $_SERVER['REMOTE_ADDR']);
						$pid = insertId();

						$rPMT = Query("insert into {pmsgs_text} (pid,title,text) values ({0}, {1}, {2})", $pid, $_POST['title'], $post);
					}

				redirectAction("private", "", "show=1");
					exit();
				}
			}
			else
				Alert("Enter a message and try again.", "Your PM is empty.");
		}
		else
			Alert("Enter a title and try again.", "Your PM is untitled.");
	}

	$prefill = $pm['text'];
	$trefill = $pmtitle;

	MakePost($pm, POST_PM);

	$form = "
		<form action=\"".actionLink("showprivate")."\" method=\"post\">
			<table class=\"outline margin width100\">
				<tr class=\"header1\">
					<th colspan=\"2\">
						Edit Draft
					</th>
				</tr>
				<tr class=\"cell0\">
					<td>
						To
					</td>
					<td>
						<input type=\"text\" name=\"to\" style=\"width: 98%;\" maxlength=\"1024\" value=\"$to\">
					</td>
				</tr>
				<tr class=\"cell1\">
					<td>
						Title
					</td>
					<td>
						<input type=\"text\" name=\"title\" style=\"width: 98%;\" maxlength=\"60\" value=\"$trefill\">
					</td>
				<tr class=\"cell0\">
					<td colspan=\"2\">
						<textarea id=\"text\" name=\"text\" rows=\"16\" style=\"width: 98%;\">$prefill</textarea>
					</td>
				</tr>
				<tr class=\"cell2\">
					<td></td>
					<td>
						<input type=\"submit\" name=\"action\" value=\"Send\">
						<input type=\"submit\" name=\"action\" value=\"Preview\">
						<input type=\"submit\" name=\"action\" value=\"Update Draft\">
						<input type=\"submit\" name=\"action\" value=\"Discard Draft\">
						<input type=\"hidden\" name=\"id\" value=\"$pmid\">
					</td>
				</tr>
			</table>";
	doPostForm($form);
} else {
	MakePost($pm, POST_PM);
}
