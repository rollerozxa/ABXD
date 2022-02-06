<?php
//  AcmlmBoard XD - Administration hub page
//  Access: administrators


AssertForbidden("viewAdminRoom");

if ($loguser['powerlevel'] < 3)
	Kill("You're not an administrator. There is nothing for you here.");

$title = "Administration";

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Admin", "admin"));
makeBreadcrumbs($crumbs);

$cell2 = 1;
function cell2($content) {
	global $cell2;
	$cell2 = ($cell2 == 1 ? 0 : 1);
	Write("
		<tr class=\"cell{0}\">
			<td>
				{1}
			</td>
		</tr>
	", $cell2, $content);
}

Write("
	<table class=\"outline margin width50 floatright\">
		<tr class=\"header1\">
			<th colspan=\"2\">
				Information
			</th>
		</tr>
");
cell2(Format("

				Last viewcount milestone
			</td>
			<td style=\"width: 60%;\">
				{0}
			",	$misc['milestone']));

$bucket = "adminright"; include("./lib/pluginloader.php");

write(
"
	</table>
");

$cell2 = 1;
Write("
	<table class=\"outline margin width25\">
		<tr class=\"header1\">
			<th>
				Admin tools
			</th>
		</tr>
");
cell2(actionLinkTag("Recalculate statistics", "recalc"));
cell2(actionLinkTag("Last Known Browsers", "lastknownbrowsers"));
cell2(actionLinkTag("Manage IP bans", "ipbans"));
cell2(actionLinkTag("Manage forum list", "editfora"));
cell2(actionLinkTag("Manage plugins", "pluginmanager"));
cell2(actionLinkTag("Edit settings", "editsettings"));
cell2(actionLinkTag("Edit smilies", "editsmilies"));
cell2(actionLinkTag("Optimize tables", "optimize"));
cell2(actionLinkTag("View log", "log"));
cell2(actionLinkTag("Update table structure", "updateschema"));
cell2(actionLinkTag("Migrate genders", "migrategenders"));

$bucket = "adminleft"; include("./lib/pluginloader.php");

write(
"
	</table>
");
