<?php

//Category/forum editor -- By Nikolaj
//Secured and improved by Dirbaio

$title = "Edit forums";

if ($loguser['powerlevel'] < 3) Kill("You're not allowed to access the forum editor.");

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Admin", "admin"));
$crumbs->add(new PipeMenuLinkEntry("Edit forum list", "editfora"));
makeBreadcrumbs($crumbs);

/**
	Okay. Much like the category editor, now the action is specified by $_POST["action"].

	Possible actions are:
	- updateforum: Updates the settings of a forum in the DB.
	- addforum: Adds a new forum to the DB.
	- deleteforum: Deletes a forum from the DB. Also, depending on $_GET["threads"]: (NOT YET)
		- "delete": DELETES all threads and posts in the DB.
		- "trash": TRASHES all the threads (move to trash and close)
		- "move": MOVES the threads to forum ID $_POST["threadsmove"]
		- "leave": LEAVES all the threads untouched in the DB (like the old forum editor. Not recommended. Will cause "invisible posts" that will still count towards user's postcounts)

	- forumtable: Returns the forum table for the left panel.
	- editforum: Returns the HTML code for the forum settings in right panel.
		- editforumnew: Returns the forum edit box to create a new forum. This way the huge HTML won't be duplicated in the code.
		- editforum: Returns the forum edit box to edit a forum.

**/


//Make actions be requested by GET also. Makes AJAX stuff easier in some cases. And manual debugging too :)
if (!isset($_POST["action"]))
	$_POST["action"] = $_GET["action"];

$noFooter = true;

function recursionCheck($fid, $cid) {
	if ($cid >= 0) return;

	$check = [];
	for (;;) {
		$check[] = -$cid;
		if ($check[0] == $fid)
			dieAjax('Endless recursion detected; choose another parent for this forum.');

		$cid = FetchResult("SELECT catid FROM {forums} WHERE id={0}", $cid);
		if ($cid >= 0) break;
	}
}

switch($_POST['action']) {
	case 'updateforum':

		//Check for the key
		if (isset($_POST['action']) && $loguser['token'] != $_POST['key'])
			Kill("No.");

		//Get new forum data
		$id = (int)$_POST['id'];
		$title = $_POST['title'];
		if ($title == "") dieAjax("Title can't be empty.");
		$description = $_POST['description'];
		$category = ($_POST['ptype'] == 0) ? (int)$_POST['category'] : -(int)$_POST['pforum'];
		$forder = (int)$_POST['forder'];
		$minpower = (int)$_POST['minpower'];
		$minpowerthread = (int)$_POST['minpowerthread'];
		$minpowerreply = (int)$_POST['minpowerreply'];

		//Send it to the DB
		Query("UPDATE {forums} SET title = {0}, description = {1}, catid = {2}, forder = {3}, minpower = {4}, minpowerthread = {5}, minpowerreply = {6} WHERE id = {7}", $title, $description, $category, $forder, $minpower, $minpowerthread, $minpowerreply, $id);
		dieAjax("Ok");

		break;
	case 'updatecategory':

		//Check for the key
		if (isset($_POST['action']) && $loguser['token'] != $_POST['key'])
			Kill("No.");

		//Get new cat data
		$id = (int)$_POST['id'];
		$name = $_POST['name'];
		if ($name == "") dieAjax("Name can't be empty.");
		$corder = (int)$_POST['corder'];

		//Send it to the DB
		Query("UPDATE {categories} SET name = {0}, corder = {1} WHERE id = {2}", $name, $corder, $id);
		dieAjax("Ok");

		break;

	case 'addforum':
		//Check for the key
		if (isset($_POST['action']) && $loguser['token'] != $_POST['key'])
			Kill("No.");

		//Get new forum data
		$title = $_POST['title'];
		if ($title == "") dieAjax("Title can't be empty.");
		$description = $_POST['description'];
		$category = ($_POST['ptype'] == 0) ? (int)$_POST['category'] : -(int)$_POST['pforum'];
		$forder = (int)$_POST['forder'];
		$minpower = (int)$_POST['minpower'];
		$minpowerthread = (int)$_POST['minpowerthread'];
		$minpowerreply = (int)$_POST['minpowerreply'];

		//Figure out the new forum ID.
		//I think it'd be better to use InsertId, but...
		$newID = FetchResult("SELECT id+1 FROM {forums} WHERE (SELECT COUNT(*) FROM {forums} f2 WHERE f2.id={forums}.id+1)=0 ORDER BY id ASC LIMIT 1");
		if ($newID < 1) $newID = 1;

		//Add the actual forum
		Query("INSERT INTO {forums} (`id`, `title`, `description`, `catid`, `forder`, `minpower`, `minpowerthread`, `minpowerreply`) VALUES ({0}, {1}, {2}, {3}, {4}, {5}, {6}, {7})", $newID, $title, $description, $category, $forder, $minpower, $minpowerthread, $minpowerreply);

		dieAjax("Ok");

	case 'addcategory':

		//Check for the key
		if (isset($_POST['action']) && $loguser['token'] != $_POST['key'])
			Kill("No.");

		//Get new cat data
		$id = (int)$_POST['id'];
		$name = $_POST['name'];
		if ($name == "") dieAjax("Name can't be empty.");
		$corder = (int)$_POST['corder'];

		Query("INSERT INTO {categories} (`name`, `corder`) VALUES ({0}, {1})", $name, $corder);

		dieAjax("Ok");

		break;
	case 'deleteforum':
		//TODO: Move and delete threads mode.

		//Check for the key
		if (isset($_POST['action']) && $loguser['token'] != $_POST['key'])
			Kill("No.");

		//Get Forum ID
		$id = (int)$_POST['id'];

		//Check that forum exists
		$rForum = Query("SELECT * FROM {forums} WHERE id={0}", $id);
		if (!NumRows($rForum))
			dieAjax("No such forum.");

		//Check that forum has threads.
		$forum = Fetch($rForum);
		if ($forum['numthreads'] > 0)
			dieAjax("Forum has threads. Move those first.");

		//Delete
		Query("DELETE FROM `{forums}` WHERE `id` = {0}", $id);
		dieAjax("Ok");
	case 'deletecategory':
		//TODO: Do something with the forums left in it?

		//Check for the key
		if (isset($_POST['action']) && $loguser['token'] != $_POST['key'])
			Kill("No.");

		//Get Cat ID
		$id = (int)$_POST['id'];

		//Check that forum exists
		$rCat = Query("SELECT * FROM {categories} WHERE id={0}", $id);
		if (!NumRows($rCat))
			dieAjax("No such category.");

		//Delete
		Query("DELETE FROM `{categories}` WHERE `id` = {0}", $id);
		dieAjax("Ok");

	case 'forumtable':
		writeForumTableContents();
		dieAjax("");
		break;

	case 'editforumnew':
	case 'editforum':

		//Get forum ID
		$fid = (int)$_GET["fid"];
		if ($_POST['action'] == 'editforumnew')
			$fid = -1;

		WriteForumEditContents($fid);
		dieAjax("");
		break;

	case 'editcategorynew':
	case 'editcategory':

		//Get cat ID
		$cid = (int)$_GET["cid"];
		if ($_POST['action'] == 'editcategorynew')
			$cid = -1;

		WriteCategoryEditContents($cid);
		dieAjax("");
		break;

	case 'deletemod':
		if (!isset($_GET['fid']))
			Kill("Forum ID unspecified.");
		if (!isset($_GET['mid']))
			Kill("Mod ID unspecified.");

		$fid = (int)$_GET['fid'];
		$mid = (int)$_GET['mid'];

		query("delete from {forummods} where forum={0} and user={1}", $fid, $mid);
		dieAjax("Ok");
		break;
	case 'addmod':
		if (!isset($_GET['fid']))
			Kill("Forum ID unspecified.");
		if (!isset($_GET['mid']))
			Kill("Mod ID unspecified.");

		$fid = (int)$_GET['fid'];
		$mid = (int)$_GET['mid'];

		$rUser = Fetch(Query("SELECT powerlevel FROM {users} WHERE id={0}", $mid));
		if (!$rUser || $rUser["powerlevel"] != 1)
			dieAjax("Invalid user ID: $mid");

		$rMod = Query("insert into {forummods} (forum, user) values ({0}, {1})", $fid, $mid);
		dieAjax("Ok");
		break;

	case 'deleteprivuser':
		if (!isset($_GET['fid']))
			Kill("Forum ID unspecified.");
		if (!isset($_GET['uid']))
			Kill("User ID unspecified.");

		$fid = (int)$_GET['fid'];
		$uid = (int)$_GET['uid'];

		$allowedusers = FetchResult("SELECT allowedusers FROM {forums} WHERE id={0}", $fid);
		if (strlen($allowedusers) < 3) $allowed = [];
		else $allowed = explode('|', substr($allowedusers,1,-1));

		foreach ($allowed as $k=>$id) {
			if ($uid == $id) {
				unset($allowed[$k]);
				break;
			}
		}
		Query("UPDATE {forums} SET allowedusers={0} WHERE id={1}", '|'.implode('|', $allowed).'|', $fid);

		dieAjax("Ok");
		break;
	case 'addprivuser':
		if (!isset($_GET['fid']))
			Kill("Forum ID unspecified.");
		if (!isset($_GET['name']))
			Kill("User name unspecified.");

		$fid = (int)$_GET['fid'];
		$name = $_GET['name'];

		$uid = FetchResult("SELECT id FROM {users} WHERE name={0} OR displayname={0}", $name);
		if ($uid < 1) dieAjax('Unknown user name.');

		$allowedusers = FetchResult("SELECT allowedusers FROM {forums} WHERE id={0}", $fid);
		if (strlen($allowedusers) < 3) $allowed = [];
		else $allowed = explode('|', substr($allowedusers,1,-1));

		$alreadyin = false;
		foreach ($allowed as $id) {
			if ($uid == $id) {
				$alreadyin = true;
				break;
			}
		}
		if (!$alreadyin)
			$allowed[] = $uid;
		Query("UPDATE {forums} SET allowedusers={0} WHERE id={1}", '|'.implode('|', $allowed).'|', $fid);

		dieAjax("Ok");
		break;

	case '': //No action, do main code
		break;

	default: //Unrecognized action
		dieAjax(format("Unknown action: {0}", $_POST["action"]));
}



//Main code.

print '<script src="'.resourceLink('js/editfora.js').'" type="text/javascript"></script>';

Write('
<div id="editcontent" style="float: right; width: 45%;">
	&nbsp;
</div>
<div id="flist">
');

WriteForumTableContents();

Write('
</div>');




//Helper functions

function cell() {
	global $cell;
	$cell = ($cell == 1 ? 0 : 1);
	return $cell;
}

// $fid == -1 means that a new forum should be made :)
function WriteForumEditContents($fid) {
	global $loguser;

	//Get all categories.
	$rCats = Query("SELECT * FROM {categories} ORDER BY corder, id");

	$cats = [];
	while ($cat = Fetch($rCats))
		$cats[$cat['id']] = $cat;

	$rFora = Query("SELECT * FROM {forums} ORDER BY forder, id");

	$fora = [];
	while ($forum = Fetch($rFora))
		$fora[$forum['id']] = $forum;

	if (count($cats) == 0)
		$cats[0] = "No categories";

	if ($fid != -1) {
		$rForum = Query("SELECT * FROM {forums} WHERE id={0}", $fid);
		if (!NumRows($rForum)) {
			Kill("Forum not found.");
		}
		$forum = Fetch($rForum);

		$title = htmlspecialchars($forum['title']);
		$description = htmlspecialchars($forum['description']);
		$catselect = MakeCatSelect('cat', $cats, $fora, $forum['catid'], $forum['id']);
		$minpower = PowerSelect('minpower', $forum['minpower']);
		$minpowerthread = PowerSelect("minpowerthread", $forum['minpowerthread']);
		$minpowerreply = PowerSelect('minpowerreply', $forum['minpowerreply']);
		$forder = $forum['forder'];
		$func = "changeForumInfo";
		$button = "Save";
		$boxtitle = "Edit Forum";
		$delbutton = "
			<button onclick='showDeleteForum(); return false;'>
				Delete
			</button>";

		$localmods = "";

		$rMods = query("SELECT u.(_userfields)
						FROM {forummods} m
						LEFT JOIN {users} u ON u.id = m.user
						WHERE m.forum={0}
						ORDER BY m.user", $fid);

		$addedMods = [];

		if (!numRows($rMods))
			$localmods .= "(No local moderators assigned to this forum)<br><br>";
		else {
			$localmods .= "<ul>";
			while($mod = fetch($rMods)) {
				$mod = getDataPrefix($mod, "u_");
				$localmods .= "<li>".UserLink($mod);
				$mid = $mod["id"];
				$addedMods[$mid] = 1;
				$localmods .= " <sup><a href=\"\" onclick=\"deleteMod($mid); return false;\">&#x2718;</a></li>";
			}
			$localmods .= "</ul>";
		}

		$rMods = query("SELECT u.(_userfields)
						FROM {users} u
						WHERE u.powerlevel = 1
						ORDER BY u.id");
		$canAddMods = false;
		$addmod = "Add a mod: ";
		$addmod .= "<select name=\"addmod\" id=\"addmod\">";

		while($mod = fetch($rMods)) {
			$mod = getDataPrefix($mod, "u_");
			if (isset($addedMods[$mod["id"]])) continue;
			$canAddMods = true;
			$mid = $mod["id"];
			$mname = $mod["displayname"];
			if (!$mname)
				$mname = $mod["name"];
			$addmod .= "<option value=\"$mid\">$mname ($mid)</option>";
		}

		$addmod .= "</select>";
		$addmod .= "<button type=\"button\" onclick=\"addMod(); return false;\">Add</button>";
		if (!$canAddMods)
			$addmod = "<br>No moderators available for adding.<br>To add a mod, set his powerlevel to Local Mod first.";

		$localmods .= $addmod;


	}
	else {
		$title = "New Forum";
		$description = "Description goes here. <strong>HTML allowed.</strong>";
		$catselect = MakeCatSelect('cat', $cats, $fora, 1, -1);
		$minpower = PowerSelect('minpower', 0);
		$minpowerthread = PowerSelect("minpowerthread", 0);
		$minpowerreply = PowerSelect('minpowerreply', 0);
		$forder = 0;
		$func = "addForum";
		$button = "Add";
		$boxtitle = "New Forum";
		$delbutton = "";
		$localmods = "(Create the forum before managing mods)";
		$privusers = '<small>(create the forum before adding users here)</small>';
	}

	echo "
	<form method=\"post\" id=\"forumform\" action=\"".actionLink("editfora")."\">
	<input type=\"hidden\" name=\"key\" value=\"".$loguser['token']."\">
	<input type=\"hidden\" name=\"id\" value=\"$fid\">
	<table class=\"outline margin\">
		<tr class=\"header1\">
			<th colspan=\"2\">
				$boxtitle
			</th>
		</tr>
		<tr class=\"cell1\">
			<td style=\"width: 25%;\">
				Title
			</td>
			<td>
				<input type=\"text\" style=\"width: 98%;\" name=\"title\" value=\"$title\">
			</td>
		</tr>
		<tr class=\"cell0\">

			<td>
				Description
			</td>
			<td>
				<input type=\"text\" style=\"width: 98%;\" name=\"description\" value=\"$description\">
			</td>
		</tr>
		<tr class=\"cell1\">
			<td>
				Parent
			</td>
			<td>
				$catselect
			</td>
		</tr>
		<tr class=\"cell0\">
			<td>
				Listing order
			</td>
			<td>
				<input type=\"text\" size=\"2\" name=\"forder\" value=\"$forder\">
				<img src=\"\" title=\"Everything is sorted by listing order first, then by ID. If everything has its listing order set to 0, they will therefore be sorted by ID only.\" alt=\"[?]\">
			</td>
		</tr>
		<tr class=\"cell1\">
			<td>
				Powerlevel required
			</td>
			<td>

				$minpower
				to view
				<br>
				$minpowerthread
				to post threads
				<br>
				$minpowerreply
				to reply
			</td>
		</tr>
		<tr class=\"cell0\">
			<td>
				Local moderators
			</td>
			<td>
				$localmods
			</td>
		</tr>

		<tr class=\"cell2\">
			<td>
				&nbsp;
			</td>
			<td>
				<button onclick=\"$func(); return false;\">
					$button
				</button>
				$delbutton
			</td>
		</tr>
	</table></form>

	<form method=\"post\" id=\"deleteform\" action=\"".actionLink("editfora")."\">
	<input type=\"hidden\" name=\"key\" value=\"".$loguser['token']."\">
	<input type=\"hidden\" name=\"id\" value=\"$fid\">
	<div id=\"deleteforum\" style=\"display:none\">
		<table class=\"outline margin\">
			<tr class=\"header1\">
				<th>
					Delete forum
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					Instead of deleting a forum, you might want to consider archiving it: Change its name or description to say so, and raise the minimum powerlevel to reply and create threads so it's effectively closed.<br><br>
					If you still want to delete it, click below:<br>
					<button onclick=\"deleteForum('delete'); return false;\">
						Delete forum
					</button>
				</td>
			</tr>
		</table>
	</div>
	</form>";

//	, $title, $description, $catselect, $minpower, $minpowerthread, $minpowerreply, $fid, $forder, $loguser['token'], $func, $button, $boxtitle, $delbutton);
}
// $fid == -1 means that a new forum should be made :)
function WriteCategoryEditContents($cid) {
	global $loguser;

	//Get all categories.
	$rCats = Query("SELECT * FROM {categories}");

	$cats = [];
	while ($cat = Fetch($rCats))
		$cats[$cat['id']] = $cat;

	if (count($cats) == 0)
		$cats[0] = "No categories";

	if ($cid != -1) {
		$rCategory = Query("SELECT * FROM {categories} WHERE id={0}", $cid);
		if (!NumRows($rCategory)) {
			Kill("Category not found.");
		}
		$cat = Fetch($rCategory);

		$name = htmlspecialchars($cat['name']);
		$corder = $cat['corder'];

		$func = "changeCategoryInfo";
		$button = "Save";
		$boxtitle = "Edit Category";
		$delbutton = "
			<button onclick='showDeleteForum(); return false;'>
				Delete
			</button>";
	}
	else {
		$title = "New Category";
		$corder = 0;
		$func = "addCategory";
		$button = "Add";
		$boxtitle = "New Category";
		$delbutton = "";
	}

	echo "<form method=\"post\" id=\"forumform\" action=\"".actionLink("editfora")."\">
	<input type=\"hidden\" name=\"key\" value=\"".$loguser["token"]."\">
	<input type=\"hidden\" name=\"id\" value=\"$cid\">
	<table class=\"outline margin\">
		<tr class=\"header1\">
			<th colspan=\"2\">
				$boxtitle
			</th>
		</tr>
		<tr class=\"cell1\">
			<td style=\"width: 25%;\">
				Name
			</td>
			<td>
				<input type=\"text\" style=\"width: 98%;\" name=\"name\" value=\"$name\">
			</td>
		</tr>
		<tr class=\"cell0\">
			<td>
				Listing order
			</td>
			<td>
				<input type=\"text\" size=\"2\" name=\"corder\" value=\"$corder\">
				<img src=\"\" title=\"Everything is sorted by listing order first, then by ID. If everything has its listing order set to 0, they will therefore be sorted by ID only.\" alt=\"[?]\">
			</td>
		</tr>
		<tr class=\"cell2\">
			<td>
				&nbsp;
			</td>
			<td>
				<button onclick=\"$func(); return false;\">
					$button
				</button>
				$delbutton
			</td>
		</tr>
	</table></form>

	<form method=\"post\" id=\"deleteform\" action=\"".actionLink("editfora")."\">
	<input type=\"hidden\" name=\"key\" value=\"".$loguser["token"]."\">
	<input type=\"hidden\" name=\"id\" value=\"$cid\">
	<div id=\"deleteforum\" style=\"display:none\">
		<table class=\"outline margin\">
			<tr class=\"header1\">

				<th>
					Delete category
				</th>
			</tr>
			<tr class=\"cell0\">
				<td>
					Be careful when deleting categories. Make sure there are no forums in the category before deleting it.
					<br><br>
					If you still want to delete it, click below:
					<br>
					<button onclick=\"deleteCategory('delete'); return false;\">
						Delete category
					</button>
				</td>
			</tr>
		</table>
	</div>
	</form>";
}


function writeForums($cats, $cid, $level) {
	$cat = $cats[$cid];

	if (isset($cat['forums'])) //<Kawa> empty categories look BAD.
	{
		foreach ($cat['forums'] as $cf) {
			if ($cf['id'] == 1337) // HAX
			{
				print '
	<tr class="cell'.cell().'" style="cursor: hand;">
		<td style="padding-left: '.(24*$level).'px;'.$sel.'">
			<span style="opacity:0.5;">'.$cf['title'].'<br>
			<small>(fake forum)</small></span>
		</td>
	</tr>';

				continue;
			}

			$sel = $_GET['s'] == $cf['id'] ? ' outline: 1px solid #888;"' : '';
			print '
	<tr class="cell'.cell().'" style="cursor: hand;">
		<td style="cursor: pointer; padding-left: '.(24*$level).'px;'.$sel.'" class="f" onmousedown="pickForum('.$cf['id'].');" id="forum'.$cf['id'].'">
			'.$cf['title'].'<br>
			<small style="opacity: 0.75;">'.$cf['description'].'</small>
		</td>
	</tr>';

			if (isset($cats[-$cf['id']]))
				writeForums($cats, -$cf['id'], $level+1);
		}
	}
	else {
			print '
	<tr class="cell'.cell().'" style="cursor: hand;">
		<td style="padding-left: 24px;" class="f">
			No forums in this category.
		</td>
	</tr>';
	}
}

function WriteForumTableContents() {
	$cats = [];
	$rCats = Query("SELECT * FROM {categories} ORDER BY corder, id");
	$forums = [];
	if (NumRows($rCats)) {
		while ($cat = Fetch($rCats)) {
			$cats[$cat['id']] = $cat;
		}
		$rForums = Query("SELECT * FROM {forums} ORDER BY forder, id");
		$forums = [];
		if (NumRows($rForums)) {
			while ($forum = Fetch($rForums)) {
				$forums[$forum['id']] = $forum;
			}
		}
	}
	$hint = '';//$cats ? "Hint: Click a forum or category to select it." : '';
	$newforum = $cats ? '<button onclick="newForum();">Add Forum</button>' : '';

	$buttons = '
	<tr class="cell2">
		<td>
			<span style="float: right;">' . $newforum .
				'<button onclick="newCategory();">Add Category</button>
			</span>' . $hint . '
		</td>
	</tr>';

	print '
	<table class="outline margin" style="width: 45%;">
	<tr class="header1">
		<th>
			Edit forum list
		</th>
	</tr>';
	print $buttons;
	foreach ($forums as $forum) {
		$cats[$forum['catid']]['forums'][$forum['id']] = $forum;
	}

	echo '<tr class="header0"><th>Main forums</th></tr>';

	foreach ($cats as $cid=>$cat) {
		if ($cid < 0) continue;

		$cname = $cat['name'];

		print '
	<tbody id="cat'.$cat['id'].'" class="c">
		<tr class="cell'.cell().'">
			<td class="c" style="cursor: pointer;" onmousedown="pickCategory('.$cat['id'].');">
				<strong>'.htmlspecialchars($cname).'</strong>
			</td>
		</tr>';

		writeForums($cats, $cid, 1);

		print "</tbody>";
	}

	if ($forums) {
	print $buttons;
	}
	print '</table>';
}

function mcs_forumBlock($fora, $catid, $selID, $indent, $fid) {
	$ret = '';

	foreach ($fora as $forum) {
		if ($forum['catid'] != $catid)
			continue;
		if ($forum['id'] == $fid)
			continue;
		if ($forum['id'] == 1337)	// HAX
			continue;

		$ret .=
'				<option value="'.$forum['id'].'"'.($forum['id'] == -$selID ? ' selected="selected"':'').'>'
	.str_repeat('&nbsp; &nbsp; ', $indent).htmlspecialchars($forum['title'])
	.'</option>
';
		$ret .= mcs_forumBlock($fora, -$forum['id'], $selID, $indent+1, $fid);
	}

	return $ret;
}

function MakeCatSelect($i, $o, $fora, $v, $fid) {
	$r = '
			<label><input type="radio" name="ptype" value="0"'.($v>=0 ? ' checked="checked"':'').'>Category:</label>
			<select name="category">';
	foreach ($o as $opt) {
		$r .= '
				<option value="'.$opt['id'].'"'.($v == $opt['id'] ? ' selected="selected"' : '').'>
					'.htmlspecialchars($opt['name']).'
				</option>';
	}
	$r .= '
			</select>';

	$r .= '
			<br>
			<label><input type="radio" name="ptype" value="1"'.($v<0 ? ' checked="checked"':'').'>Forum:</label>
			<select name="pforum">';

	foreach ($o as $cid=>$cat) {
		$cname = $cat['name'];

		$fb = mcs_forumBlock($fora, $cid, $v, 0, $fid);
		if (!$fb) continue;

		$r .=
'			<optgroup label="'.htmlspecialchars($cname).'">
'.$fb.
'			</optgroup>
';
	}

	$r .= '
			</select>';

	return $r;
}
function PowerSelect($id, $s) {
	$r = Format('
				<select name="{0}">
	', $id);
	if ($s < 0) $s = 0;
	else if ($s > 3) $s = 3;
	$powers = [0=>"Regular", 1=>"Local mod", 2=>"Full mod", 3=>"Admin"];
	foreach ($powers as $k => $v) {
		$r .= Format('
					<option value="{0}"{2}>{1}</option>
		', $k, $v, ($k == $s ? ' selected="selected"' : ''));
	}
	$r .= '
				</select>';
	return $r;
}
