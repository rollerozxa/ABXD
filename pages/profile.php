<?php
//  AcmlmBoard XD - User profile page
//  Access: all

AssertForbidden("viewProfile");

if (isset($_POST['id']))
	$_GET['id'] = $_POST['id'];

if (!isset($_GET['id']))
	Kill("User ID unspecified.");

$id = (int)$_GET['id'];

$rUser = Query("select * from {users} where id={0}", $id);
if (NumRows($rUser))
	$user = Fetch($rUser);
else
	Kill("Unknown user ID.");

if ($id == $loguserid) {
	Query("update {users} set newcomments = 0 where id={0}", $loguserid);
	$loguser['newcomments'] = false;
}

if ($loguserid && (isset($_REQUEST['token']) && $_REQUEST['token'] == $loguser['token'])) {
	if (isset($_GET['block'])) {
		AssertForbidden("blockLayouts");
		$block = (int)$_GET['block'];
		$rBlock = Query("select * from {blockedlayouts} where user={0} and blockee={1}", $id, $loguserid);
		$isBlocked = NumRows($rBlock);
		if ($block && !$isBlocked && $loguserid != $id)
			$rBlock = Query("insert into {blockedlayouts} (user, blockee) values ({0}, {1})", $id, $loguserid);
		elseif (!$block && $isBlocked)
			$rBlock = Query("delete from {blockedlayouts} where user={0} and blockee={1} limit 1", $id, $loguserid);
		die(header("Location: ".actionLink("profile", $id)));
	}
}

$daysKnown = (time()-$user['regdate'])/86400;
$posts = FetchResult("select count(*) from {posts} where user={0}", $id);
$threads = FetchResult("select count(*) from {threads} where user={0}", $id);
$averagePosts = sprintf("%1.02f", $user['posts'] / $daysKnown);
$averageThreads = sprintf("%1.02f", $threads / $daysKnown);

$minipic = getMinipicTag($user);

if ($user['rankset']) {
	$currentRank = GetRank($user["rankset"], $user["posts"]);
	$toNextRank = GetToNextRank($user["rankset"], $user["posts"]);
	if ($toNextRank)
		$toNextRank = Plural($toNextRank, "post");
}
if ($user['title'])
	$title = str_replace("<br>", " &bull; ", strip_tags(CleanUpPost($user['title'], "", true), "<b><strong><i><em><span><s><del><img><a><br><br><small>"));

if ($user['homepageurl']) {
	$nofollow = "";
	if (Settings::get("nofollow"))
		$nofollow = "rel=\"nofollow\"";

	if ($user['homepagename'])
		$homepage = "<a $nofollow target=\"_blank\" href=\"".htmlspecialchars($user['homepageurl'])."\">".htmlspecialchars($user['homepagename'])."</a> - ".htmlspecialchars($user['homepageurl']);
	else
		$homepage = "<a $nofollow target=\"_blank\" href=\"".htmlspecialchars($user['homepageurl'])."\">".htmlspecialchars($user['url'])."</a>";
}

$emailField = "Private";
if ($user['email'] == "")
	$emailField = "None given";
elseif ($user['showemail'])
	$emailField = "<span id=\"emailField\">Public <button style=\"font-size: 0.7em;\" onclick=\"loadEmail($id)\">Show</button></span>";

if ($user['tempbantime']) {
	write(
"
	<div class=\"outline margin cell1 smallFonts\">
		This user has been temporarily banned until {0} (GMT). That's {1} left.
	</div>
",	gmdate("M jS Y, G:i:s",$user['tempbantime']), TimeUnits($user['tempbantime'] - time())
	);
}


$profileParts = [];

$foo = [];
$foo["Name"] = $minipic . htmlspecialchars($user['displayname'] ? $user['displayname']." (".$user['name'].")" : $user['name']);
$foo["Power"] = getPowerlevelName($user['powerlevel']);
if (isset($title))
	$foo["Title"] = $title;
if (isset($currentRank))
	$foo["Rank"] = $currentRank;
if (isset($toNextRank))
	$foo["To next rank"] = $toNextRank;
$foo["Total posts"] = format("{0} ({1} per day)", $posts, $averagePosts);
$foo["Total threads"] = format("{0} ({1} per day)", $threads, $averageThreads);
$foo["Registered on"] = format("{0} ({1} ago)", formatdate($user['regdate']), TimeUnits($daysKnown*86400));

$lastPost = Fetch(Query("
	SELECT
		p.id as pid, p.date as date,
		{threads}.title AS ttit, {threads}.id AS tid,
		{forums}.title AS ftit, {forums}.id AS fid, {forums}.minpower
	FROM {posts} p
		LEFT JOIN {users} u on u.id = p.user
		LEFT JOIN {threads} on {threads}.id = p.thread
		LEFT JOIN {forums} on {threads}.forum = {forums}.id
	WHERE p.user={0}
	ORDER BY p.date DESC
	LIMIT 0, 1", $user["id"]));

if ($lastPost) {
	$thread = [];
	$thread["title"] = $lastPost["ttit"];
	$thread["id"] = $lastPost["tid"];

	$realpl = $loguser["powerlevel"];
	if ($realpl < 0) $realpl = 0;
	if ($lastPost["minpower"] > $realpl)
		$place = "a restricted forum.";
	else {
		$pid = $lastPost["pid"];
		$place = makeThreadLink($thread)." (".actionLinkTag($lastPost["ftit"], "forum", $lastPost["fid"], "", $lastPost["ftit"]).")";
		$place .= " &raquo; ".actionLinkTag($pid, "post", $pid);
	}
	$foo["Last post"] = format("{0} ({1} ago)", formatdate($lastPost["date"]), TimeUnits(time() - $lastPost["date"])) .
								"<br>in ".$place;
}
else
	$foo["Last post"] = "Never";

$foo["Last view"] = format("{0} ({1} ago)", formatdate($user['lastactivity']), TimeUnits(time() - $user['lastactivity']));
$foo["Browser"] = $user['lastknownbrowser'];
if ($loguser['powerlevel'] > 0)
	$foo["Last known IP"] = formatIP($user['lastip']);
$profileParts["General information"] = $foo;

$foo = [];
$foo["Email address"] = $emailField;
if (isset($homepage))
	$foo["Homepage"] = $homepage;
$profileParts["Contact information"] = $foo;

$foo = [];
$infofile = "themes/".$user['theme']."/themeinfo.txt";

$themeinfo = file_get_contents($infofile);
$themeinfo = explode("\n", $themeinfo, 2);

if (file_exists($infofile)) {
	$themename = trim($themeinfo[0]);
	$themeauthor = trim($themeinfo[1]);
} else {
	$themename = $user['theme'];
	$themeauthor = "";
}
$foo["Theme"] = $themename;
$foo["Items per page"] = Plural($user['postsperpage'], "post") . ", " . Plural($user['threadsperpage'], "thread");
$profileParts["Presentation"] = $foo;

$foo = [];
if ($user['realname'])
	$foo["Real name"] = htmlspecialchars($user['realname']);
if ($user['location'])
	$foo["Location"] = htmlspecialchars($user['location']);
if ($user['birthday'])
	$foo["Birthday"] = formatBirthday($user['birthday']);
if ($user['gender'] != "N/A") {
	$foo["Gender"] = htmlspecialchars($user['gender']);
}
if ($user['pronouns'] != "") {
	$pronouns = explode("/", $user['pronouns']);
	$foo["Pronouns"] = $pronouns[0] . "/" . $pronouns[1];
	if (!array_key_exists($user['pronouns'], $defaultPronouns)) {
		$foo["Pronouns"] .= format(
			'<div class="spoiler" style="display: inline;">
				<button class="spoilerbutton named">{0}</button>
				<div class="spoiled hidden">
					{6} went to the park.<br>
					I went with {2}.<br>
					{6} brought {3} frisbee.<br>
					At least I think it was {4}.<br>
					{6} threw it to {5}.
				</div>
			</div>',
			"Show usage", $pronouns[0], $pronouns[1], $pronouns[2], $pronouns[3], $pronouns[4], ucfirst($pronouns[0])
		);
	}
}

if (count($foo))
	$profileParts["Personal information"] = $foo;

if ($user['bio'])
	$profileParts["Bio"] = ["" => CleanUpPost($user['bio'], $user['displayname'] ? $user['displayname'] : $user['name'])];

$badgersR = Query("select * from {badges} where owner={0} order by color", $id);
if (NumRows($badgersR)) {
	$badgers = "";
	$colors = ["bronze", "silver", "gold", "platinum"];
	while($badger = Fetch($badgersR))
		$badgers .= Format("<span class=\"badge {0}\">{1}</span> ", $colors[$badger['color']], $badger['name']);
	$profileParts['General information']['Badges'] = $badgers;
}

$bucket = "profileTable"; include("./lib/pluginloader.php");

echo "
	<table>
		<tr>
			<td style=\"width: 60%; border: 0px none; vertical-align: top; padding-right: 1em; padding-bottom: 1em;\">";

echo "<table class=\"outline margin\">";

$cc = 0;
foreach($profileParts as $partName => $fields) {
	write("
					<tr class=\"header0\">
						<th colspan=\"2\">{0}</th>
					</tr>
", $partName);
	foreach($fields as $label => $value) {
		$cc = ($cc + 1) % 2;
		if ($label)
			write("
								<tr>
									<td class=\"cell2\">{0}</td>
									<td class=\"cell{2}\">{1}</td>
								</tr>
	", str_replace(" ", "&nbsp;", $label), $value, $cc);
		else
			write("
								<tr>
									<td colspan=\"2\" class=\"cell{2}\">{1}</td>
								</tr>
	", str_replace(" ", "&nbsp;", $label), $value, $cc);
	}
}

write("
				</table>
");

$bucket = "profileLeft"; include("./lib/pluginloader.php");

write("
			</td>
			<td style=\"vertical-align: top; border: 0px none;\">
");

include("usercomments.php");

print "
			</td>
		</tr>
	</table>";

$previewPost['text'] = Settings::get("profilePreviewText");

$previewPost['num'] = "_";
$previewPost['id'] = "_";

foreach($user as $key => $value)
	$previewPost["u_".$key] = $value;

MakePost($previewPost, POST_SAMPLE);


$links = new PipeMenu();

if (IsAllowed("editProfile") && $loguserid == $id)
	$links -> add(new PipeMenuLinkEntry("Edit my profile", "editprofile", "", "", "pencil"));
else if (IsAllowed("editUser") && $loguser['powerlevel'] > 2)
	$links -> add(new PipeMenuLinkEntry("Edit user", "editprofile", $id, "", "pencil"));

if (IsAllowed("snoopPM") && $loguser['powerlevel'] > 2)
	$links -> add(new PipeMenuLinkEntry("Show PMs", "private", $id, "", "eye-open"));

if ($loguserid && IsAllowed("sendPM"))
	$links -> add(new PipeMenuLinkEntry("Send PM", "sendprivate", "", "uid=".$id, "envelope"));
if (IsAllowed("listPosts"))
		$links -> add(new PipeMenuLinkEntry("Show posts", "listposts", $id, "", "copy"));
if (IsAllowed("listThreads"))
		$links -> add(new PipeMenuLinkEntry("Show threads", "listthreads", $id, "", "list"));


if (IsAllowed("blockLayouts") && $loguserid) {
	$rBlock = Query("select * from {blockedlayouts} where user={0} and blockee={1}", $id, $loguserid);
	$isBlocked = NumRows($rBlock);
	if ($isBlocked)
		$links -> add(new PipeMenuLinkEntry("Unblock layout", "profile", $id, "block=0&token={$loguser['token']}", "ban-circle"));
	else if ($id != $loguserid)
		$links -> add(new PipeMenuLinkEntry("Block layout", "profile", $id, "block=1&token={$loguser['token']}", "ban-circle"));
}

makeLinks($links);

$uname = $user["name"];
if ($user["displayname"])
	$uname = $user["displayname"];

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Member list", "memberlist"));
$crumbs->add(new PipeMenuHtmlEntry(userLink($user)));
makeBreadcrumbs($crumbs);

$title = format("Profile for {0}", htmlspecialchars($uname));
