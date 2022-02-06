<?php

// parent=0: index listing
function makeForumListing($parent, $page=0) {
	global $loguserid, $loguser;

	$pl = $loguser['powerlevel'];
	if ($pl < 0) $pl = 0;

	$lastCatID = -1;
	$firstCat = true;
	$rFora = Query("	SELECT f.*,
							c.name cname,
							".($loguserid ? "(NOT ISNULL(i.fid))" : "0")." ignored,
							(SELECT COUNT(*) FROM {threads} t".($loguserid ? " LEFT JOIN {threadsread} tr ON tr.thread=t.id AND tr.id={0}" : "")."
								WHERE t.forum=f.id AND t.lastpostdate>".($loguserid ? "IFNULL(tr.date,0)" : time()-900).") numnew,
							lu.(_userfields)
						FROM {forums} f
							LEFT JOIN {categories} c ON c.id=f.catid
							".($loguserid ? "LEFT JOIN {ignoredforums} i ON i.fid=f.id AND i.uid={0}" : "")."
							LEFT JOIN {users} lu ON lu.id=f.lastpostuser
						WHERE ".forumAccessControlSQL().' AND '.($parent==0 ? 'f.catid>0 ' : 'f.catid={1}').(($pl < 1) ? " AND f.hidden=0" : '')."
						ORDER BY c.corder, c.id, f.forder, f.id",
						$loguserid, -$parent, $page);
	if (!NumRows($rFora))
		return;

	$rSubfora = Query("	SELECT f.*,
							".($loguserid ? "(NOT ISNULL(i.fid))" : "0")." ignored,
							(SELECT COUNT(*) FROM {threads} t".($loguserid ? " LEFT JOIN {threadsread} tr ON tr.thread=t.id AND tr.id={0}" : "")."
								WHERE t.forum=f.id AND t.lastpostdate>".($loguserid ? "IFNULL(tr.date,0)" : time()-900).") numnew
						FROM {forums} f
							".($loguserid ? "LEFT JOIN {ignoredforums} i ON i.fid=f.id AND i.uid={0}" : "")."
						WHERE ".forumAccessControlSQL().' AND '.($parent==0 ? 'f.catid<0' : 'f.catid!={1}').(($pl < 1) ? " AND f.hidden=0" : '')."
						ORDER BY f.forder, f.id",
						$loguserid, -$parent);
	$subfora = [];
	while ($sf = Fetch($rSubfora))
		$subfora[-$sf['catid']][] = $sf;

	$rMods = Query("SELECT m.forum, u.(_userfields) FROM {forummods} m LEFT JOIN {users} u ON m.user=u.id");
	$mods = [];
	while($mod = Fetch($rMods))
		$mods[$mod['forum']][] = getDataPrefix($mod, "u_");

	$theList = "";
	while($forum = Fetch($rFora)) {
		$skipThisOne = false;
		$bucket = "forumListMangler"; include("./lib/pluginloader.php");
		if ($skipThisOne)
			continue;

		if ($firstCat || $forum['catid'] != $lastCatID ) {
			$theList .= format(
"
		".($firstCat ? '':'</tbody></table>')."
	<table class=\"outline margin\">
		<tbody>
			<tr class=\"header1\">
				<th style=\"width:32px;\"></th>
				<th>{0}</th>
				<th style=\"width:100px;\">Threads</th>
				<th style=\"width:100px;\">Posts</th>
				<th style=\"width:150px\">Last post</th>
			</tr>
		</tbody>
		<tbody id=\"cat_{1}\">
", ($parent==0)?$forum['cname']:'Subforums', $forum['catid']);

			$lastCatID = $forum['catid'];
			$firstCat = false;
		}

		$newstuff = 0;
		$NewIcon = "";
		$localMods = "";
		$subforaList = '';

		$newstuff = $forum['ignored'] ? 0 : $forum['numnew'];
		$ignoreClass = $forum['ignored'] ? " class=\"ignored\"" : "";

		if ($newstuff > 0)
			$NewIcon = "<img src=\"".resourceLink("img/status/new.png")."\" alt=\"New\"><br>".gfxnumber($newstuff);

		if (isset($mods[$forum['id']]))
			foreach($mods[$forum['id']] as $user)
				$localMods .= UserLink($user). ", ";

		if ($localMods)
			$localMods = "<br><small>Moderated by: ".substr($localMods,0,-2)."</small>";

		if (isset($subfora[$forum['id']])) {
			foreach ($subfora[$forum['id']] as $subforum) {
				$link = actionLinkTag($subforum['title'], 'forum', $subforum['id']);

				if ($subforum['ignored'])
					$link = '<span class="ignored">'.$link.'</span>';
				else if ($subforum['numnew'] > 0)
					$link = '<img src="'.resourceLink('img/status/new.png').'" alt="New"/> '.$link;

				$subforaList .= $link.', ';
			}
		}

		if ($subforaList)
			$subforaList = "<br><small>Subforums: ".substr($subforaList,0,-2)."</small>";

		if ($forum['lastpostdate']) {
			$user = getDataPrefix($forum, "lu_");

			$lastLink = "";
			if ($forum['lastpostid'])
				$lastLink = actionLinkTag("&raquo;", "post", $forum['lastpostid']);
			$lastLink = format("<span class=\"nom\">{0}<br>by </span>{1} {2}", formatdate($forum['lastpostdate']), UserLink($user), $lastLink);
		}
		else
			$lastLink = "----";

		$theList .=
"
		<tr class=\"cell1\">
			<td class=\"cell2 threadIcon newMarker\">
				$NewIcon
			</td>
			<td>
				<h4 $ignoreClass>".
					actionLinkTag($forum['title'], "forum",  $forum['id']) . "
				</h4>
				<span $ignoreClass>
					{$forum['description']}
					$localMods
					$subforaList
				</span>
			</td>
			<td class=\"center cell2\">
				{$forum['numthreads']}
			</td>
			<td class=\"center cell2\">
				{$forum['numposts']}
			</td>
			<td class=\"smallFonts center\">
				$lastLink
			</td>
		</tr>";
	}

	write(
"
	{0}
	</tbody>
</table>
",	$theList);
}


function listThread($thread, $cellClass, $dostickies = true, $showforum = false) {
	global $haveStickies, $loguserid, $loguser, $misc;

	$forumList = "";

	$starter = getDataPrefix($thread, "su_");
	$last = getDataPrefix($thread, "lu_");

	$threadlink = makeThreadLink($thread);


	$NewIcon = "";
	$newstuff = 0;
	if ($thread['closed'])
		$NewIcon = "off";
	if ($thread['replies'] >= $misc['hotcount'])
		$NewIcon .= "hot";
	if ((!$loguserid && $thread['lastpostdate'] > time() - 900) ||
		($loguserid && $thread['lastpostdate'] > $thread['readdate']) &&
		!$isIgnored) {
		$NewIcon .= "new";
		$newstuff++;
	}
	else if (!$thread['closed'] && !$thread['sticky'] && Settings::get("oldThreadThreshold") > 0 && $thread['lastpostdate'] < time() - (2592000 * Settings::get("oldThreadThreshold")))
		$NewIcon = "old";

	if ($NewIcon)
		$NewIcon = "<img src=\"".resourceLink("img/status/".$NewIcon.".png")."\" alt=\"\"/>";

	if ($thread['sticky'] == 0 && $haveStickies == 1 && $dostickies) {
		$haveStickies = 2;
		$forumList .= "<tr class=\"header1\"><th colspan=\"7\" style=\"height: 8px;\"></th></tr>";
	}
	if ($thread['sticky'] && $haveStickies == 0) $haveStickies = 1;

	$poll = ($thread['poll'] ? "<img src=\"".resourceLink("img/poll.png")."\" alt=\"Poll\"/> " : "");


	$n = 4;
	$total = $thread['replies'];

	$ppp = $loguser['postsperpage'];
	if (!$ppp) $ppp = 20;

	$numpages = floor($total / $ppp);
	$pl = "";
	if ($numpages <= $n * 2) {
		for($i = 1; $i <= $numpages; $i++)
			$pl .= " ".actionLinkTag($i+1, "thread", $thread['id'], "from=".($i * $ppp));
	}
	else {
		for($i = 1; $i < $n; $i++)
		$pl .= " ".actionLinkTag($i+1, "thread", $thread['id'], "from=".($i * $ppp));
		$pl .= " &hellip; ";
		for($i = $numpages - $n + 1; $i <= $numpages; $i++)
			$pl .= " ".actionLinkTag($i+1, "thread", $thread['id'], "from=".($i * $ppp));
	}
	if ($pl)
		$pl = " <span class=\"smallFonts\">[".
			actionLinkTag(1, "thread", $thread['id']). $pl . "]</span>";

	$lastLink = "";
	if ($thread['lastpostid'])
		$lastLink = " ".actionLinkTag("&raquo;", "post", $thread['lastpostid']);


	$forumcell = "";
	if ($showforum) {
		$forumcell = "<td class=\"center\">".actionLinkTag(htmlspecialchars($thread["f_title"]), "forum", $thread["f_id"], "", $thread["f_title"])."</td>";
	}
	$forumList .= "
	<tr class=\"cell$cellClass\">
		<td class=\"cell2 threadIcon\"> $NewIcon</td>
		<td style=\"border-left: 0px none;\">
			$poll
			$threadlink
			$pl
		</td>
		$forumcell
		<td class=\"center\">
			".UserLink($starter)."
		</td>
		<td class=\"center\">
			{$thread['replies']}
		</td>
		<td class=\"center\">
			{$thread['views']}
		</td>
		<td class=\"smallFonts center\">
			".formatdate($thread['lastpostdate'])."<br>
			by ".UserLink($last)." {$lastLink}</td>
	</tr>";

	return $forumList;
}
