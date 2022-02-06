<?php

$title = "Plugin Manager";

AssertForbidden("managePlugins");

if ($loguser['powerlevel'] < 3)
	Kill("You must be an administrator to manage plugins.");

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Admin", "admin"));
$crumbs->add(new PipeMenuLinkEntry("Plugin manager", "pluginmanager"));
makeBreadcrumbs($crumbs);

if ($_GET["action"] == "enable") {
	if ($_GET["key"] != $loguser['token'])
		Kill("No.");

	Query("insert into {enabledplugins} values ({0})", $_GET["id"]);
	logAction("enableplugin", ['text' => $_GET["id"]]);
	Upgrade();
	redirectAction("pluginmanager");
}
if ($_GET["action"] == "disable") {
	if ($_GET["key"] != $loguser['token'])
		Kill("No.");

	Query("delete from {enabledplugins} where plugin={0}", $_GET["id"]);
	logAction("disableplugin", ['text' => $_GET["id"]]);
	redirectAction("pluginmanager");
}


$pluginsDb = [];

$pluginList = query("SELECT * FROM {enabledplugins}");
while($plugin = fetch($pluginList))
	$pluginsDb[$plugin["plugin"]] = true;

$cell = 0;
$pluginsDir = @opendir("plugins");

$enabledplugins = [];
$disabledplugins = [];
$pluginDatas = [];

if ($pluginsDir !== FALSE) {
	while(($plugin = readdir($pluginsDir)) !== FALSE) {
		if ($plugin == "." || $plugin == "..") continue;
		if (is_dir("./plugins/".$plugin)) {
			try
			{
				$plugindata = getPluginData($plugin, false);
			}
			catch(BadPluginException $e) {
				continue;
			}

			$pluginDatas[$plugin] = $plugindata;
			if (isset($pluginsDb[$plugin]))
				$enabledplugins[$plugin] = $plugindata["name"];
			else
				$disabledplugins[$plugin] = $plugindata["name"];
		}
	}

}

asort($enabledplugins);
asort($disabledplugins);

print '<table class="outline margin width50">';
print '<tr class="header0"><th colspan="2">Enabled plugins</th></tr>';
foreach($enabledplugins as $plugin => $pluginname)
	listPlugin($plugin, $pluginDatas[$plugin]);
print '<tr class="header0"><th colspan="2">Disabled plugins</th></tr>';
foreach($disabledplugins as $plugin => $pluginname)
	listPlugin($plugin, $pluginDatas[$plugin]);

print '</table>';

function listPlugin($plugin, $plugindata) {
	global $cell, $plugins, $loguser, $pluginsDb;

	print '<tr class="cell'.$cell.'"><td>';
	print "<b>".$plugindata["name"]."</b><br>";
	if ($plugindata["author"])
		$author = '<br>Made by: '.$plugindata["author"];
	print '<span style="display:block;margin-left:30px;">'.$plugindata["description"].$author.'</span>';
	print '</td><td>';

	print '<ul class="pipemenu">';

	$text = "Enable";
	$act = "enable";
	if (isset($pluginsDb[$plugin])) {
		$text = "Disable";
		$act = "disable";
	}
	print actionLinkTagItem($text, "pluginmanager", $plugin, "action=".$act."&key=".$loguser['token']);

	if (in_array("settingsfile", $plugindata["buckets"])) {
		if (isset($plugins[$plugin]))
			print actionLinkTagItem("Settings&hellip;", "editsettings", $plugin);
	}
	print '</ul>';
	print '</td></tr>';

	$cell++;
	$cell %= 2;
}
