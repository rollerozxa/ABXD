<?php

//Check Stuff
if (!$loguserid)
	Kill("You must be logged in to edit your profile.");

if ($loguser['powerlevel'] < 0)
	Kill("Banned users may not edit their profile.");

if (isset($_POST['action']) && $loguser['token'] != $_POST['key'])
	Kill("No.");

if (isset($_POST['editusermode']) && $_POST['editusermode'] != 0)
	$_GET['id'] = $_POST['userid'];

if ($loguser['powerlevel'] > 2)
	$userid = (isset($_GET['id'])) ? (int)$_GET['id'] : $loguserid;
else
	$userid = $loguserid;

$user = Fetch(Query("select * from {users} where id={0}", $userid));

$editUserMode = isset($_GET['id']) && $loguser['powerlevel'] > 2;

if ($editUserMode && $user['powerlevel'] == 4 && $loguser['powerlevel'] != 4 && $loguserid != $userid)
	Kill("Cannot edit a root user.");

AssertForbidden($editUserMode ? "editUser" : "editProfile");

//Breadcrumbs

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Member list", "memberlist"));
$crumbs->add(new PipeMenuHtmlEntry(userLink($user)));
$crumbs->add(new PipeMenuTextEntry("Edit profile"));
makeBreadcrumbs($crumbs);


loadRanksets();
$ranksets = $ranksetNames;
$ranksets = array_reverse($ranksets);
$ranksets[""] = "None";
$ranksets = array_reverse($ranksets);

foreach($dateformats as $format)
	$datelist[$format] = ($format ? $format.' ('.cdate($format).')':'');
foreach($timeformats as $format)
	$timelist[$format] = ($format ? $format.' ('.cdate($format).')':'');

$powerlevels = [-1 => "-1 - Banned", "0 - Normal user", "1 - Local Mod", "2 - Full Mod", "3 - Admin"];
$colorValues = [];
$dispname = $user['displayname'] ? $user['displayname'] : $user['name'];

for ($i = 0; $i < 3; $i++) {
	$colorValues[] = "<span class=\"nc$i" . $user['powerlevel'] . "\">" . $dispname . "</span>";
}

//Editprofile.php: Welcome to the Hell of Nested Arrays!
$general = [
	"appearance" => [
		"name" => "Appearance",
		"items" => [
			"displayname" => [
				"caption" => "Display name",
				"type" => "text",
				"width" => "98%",
				"length" => 32,
				"hint" => "Leave this empty to use your login name.",
				"callback" => "HandleDisplayname",
			],
			"rankset" => [
				"caption" => "Rankset",
				"type" => "select",
				"options" => $ranksets,
			],
			"title" => [
				"caption" => "Title",
				"type" => "text",
				"width" => "98%",
				"length" => 255,
			],
			"colorset" => [
				"caption" => "Name color",
				"type" => "radiogroup",
				"options" => $colorValues
			]
		],
	],
	"avatar" => [
		"name" => "Avatar",
		"items" => [
			"picture" => [
				"caption" => "Avatar",
				"type" => "displaypic",
				"errorname" => "picture",
				"hint" => format("Maximum size is {0} by {0} pixels.", 100),
			],
			"minipic" => [
				"caption" => "Minipic",
				"type" => "minipic",
				"errorname" => "minipic",
				"hint" => format("Maximum size is {0} by {0} pixels.", 16),
			],
		],
	],
	"presentation" => [
		"name" => "Presentation",
		"items" => [
			"threadsperpage" => [
				"caption" => "Threads per page",
				"type" => "number",
				"min" => 50,
				"max" => 99,
			],
			"postsperpage" => [
				"caption" => "Posts per page",
				"type" => "number",
				"min" => 20,
				"max" => 99,
			],
			"dateformat" => [
				"caption" => "Date format",
				"type" => "datetime",
				"presets" => $datelist,
				"presetname" => "presetdate",
			],
			"timeformat" => [
				"caption" => "Time format",
				"type" => "datetime",
				"presets" => $timelist,
				"presetname" => "presettime",
			],
			"fontsize" => [
				"caption" => "Font scale",
				"type" => "number",
				"min" => 20,
				"max" => 200,
			],
		],
	],
	"options" => [
		"name" => "Options",
		"items" => [
			"blocklayouts" => [
				"caption" => "Block all layouts",
				"type" => "checkbox",
			],
			"usebanners" => [
				"caption" => "Use nice notification banners",
				"type" => "checkbox",
			],
		],
	],
];

$personal = [
	"personal" => [
		"name" => "Personal information",
		"items" => [
			"gender" => [
				"caption" => "Gender",
				"type" => "gender"
			],
			"pronouns" => [
				"caption" => "Pronouns",
				"type" => "pronouns",
				"callback" => "handlePronouns"
			],
			"realname" => [
				"caption" => "Real name",
				"type" => "text",
				"width" => "98%",
				"length" => 60,
			],
			"location" => [
				"caption" => "Location",
				"type" => "text",
				"width" => "98%",
				"length" => 60,
			],
			"birthday" => [
				"caption" => "Birthday",
				"type" => "birthday",
				"width" => "98%",
				"length" => 60,
				"extra" => "<span class=\"smallFonts\">".format("(example: {0})", $birthdayExample)."</span>",
			],
			"bio" => [
				"caption" => "Bio",
				"type" => "textarea",
			],
			"timezone" => [
				"caption" => "Timezone offset",
				"type" => "timezone",
			],
		],
	],
	"contact" => [
		"name" => "Contact information",
		"items" => [
			"homepageurl" => [
				"caption" => "Homepage URL",
				"type" => "text",
				"width" => "98%",
				"length" => 60,
			],
			"homepagename" => [
				"caption" => "Homepage name",
				"type" => "text",
				"width" => "98%",
				"length" => 60,
			],
		],
	],
];

$account = [
	"confirm" => [
		"name" => "Password confirmation",
		"items" => [
			"info" => [
				"caption" => "",
				"type" => "label",
				"value" => "Enter your password in order to edit account settings"
			],
			"currpassword" => [
				"caption" => "Password",
				"type" => "passwordonce",
				"callback" => "",
			],
		],
	],
	"login" => [
		"name" => "Login information",
		"class" => "needpass",
		"items" => [
			"name" => [
				"caption" => "User name",
				"type" => "text",
				"length" => 20,
				"callback" => "HandleUsername",
			],
			"password" => [
				"caption" => "Password",
				"type" => "password",
				"callback" => "HandlePassword",
			],
		],
	],
	"email" => [
		"name" => "Email information",
		"class" => "needpass",
		"items" => [
			"email" => [
				"caption" => "Email address",
				"type" => "text",
				"width" => "50%",
				"length" => 60,
			],
			"showemail" => [
				"caption" => "Make email public",
				"type" => "checkbox",
			],
		],
	],
	"admin" => [
		"name" => "Administrative stuff",
		"class" => "needpass",
		"items" => [
			"powerlevel" => [
				"caption" => "Power level",
				"type" => "select",
				"options" => $powerlevels,
				"callback" => "HandlePowerlevel",
			],
			"globalblock" => [
				"caption" => "Globally block layout",
				"type" => "checkbox",
			],
		],
	],
];

$layout = [
	"postlayout" => [
		"name" => "Post layout",
		"items" => [
			"postheader" => [
				"caption" => "Header",
				"type" => "textarea",
				"rows" => 16,
			],
			"signature" => [
				"caption" => "Footer",
				"type" => "textarea",
				"rows" => 16,
			],
			"signsep" => [
				"caption" => "Show signature separator",
				"type" => "checkbox",
				"negative" => true,
			],
		],
	],
];

//Allow plugins to add their own fields
$bucket = "edituser"; include("lib/pluginloader.php");

//Make some more checks.
if ($user['posts'] < Settings::get("customTitleThreshold") && $user['powerlevel'] < 1 && !$editUserMode)
	unset($general['appearance']['items']['title']);

if (!$editUserMode) {
	$account['login']['items']['name']['type'] = "label";
	$account['login']['items']['name']['value'] = $user["name"];
	unset($account['admin']);
}

if ($loguser['powerlevel'] > 0)
	$general['avatar']['items']['picture']['hint'] = "As a staff member, you can upload pictures of any reasonable size.";

if ($loguser['powerlevel'] == 4 && isset($account['admin']['items']['powerlevel'])) {
	if ($user['powerlevel'] == 4) {
		$account['admin']['items']['powerlevel']['type'] = "label";
		$account['admin']['items']['powerlevel']['value'] = "4 - Root";
	}
	else {
		$account['admin']['items']['powerlevel']['options'][-2] = "-2 - Slowbanned";
		$account['admin']['items']['powerlevel']['options'][4] = "4 - Root";
		$account['admin']['items']['powerlevel']['options'][5] = "5 - System";
		ksort($account['admin']['items']['powerlevel']['options']);
	}
}

// Now that we have everything set up, we can link 'em into a set of tabs.
$tabs = [
	"general" => [
		"name" => "General",
		"page" => $general,
	],
	"personal" => [
		"name" => "Personal",
		"page" => $personal,
	],
	"account" => [
		"name" => "Account settings",
		"page" => $account,
	],
	"postlayout" => [
		"name" => "Post layout",
		"page" => $layout,
	],
	"theme" => [
		"name" => "Theme",
		"width" => "80%",
	],
];

/*
if (isset($_POST['theme']) && $user['id'] == $loguserid) {
	$theme = $_POST['theme'];
	$themeFile = $theme.".css";
	if (!file_exists("css/".$themeFile))
		$themeFile = $theme.".php";
	$logopic = "img/themes/default/logo.png";
	if (file_exists("img/themes/".$theme."/logo.png"))
		$logopic = "img/themes/".$theme."/logo.png";
}*/

/* QUICK-E BAN
 * -----------
 */
$_POST['action'] = (isset($_POST['action']) ? $_POST['action'] : "");
if ($_POST['action'] == "Tempban" && $user['tempbantime'] == 0) {
	if ($loguser['powerlevel'] < 3) Kill('No.');

	if ($user['powerlevel'] == 4) {
		Kill("Trying to ban a root user?");
	}
	$timeStamp = strtotime($_POST['until']);
	if ($timeStamp === FALSE) {
		Alert("Invalid time given. Try again.");
	}
	else {
		SendSystemPM($userid, format("You have been temporarily banned until {0} GMT. If you don't know why this happened, feel free to ask the one most likely to have done this. Calmly, if possible.", gmdate("M jS Y, G:[b][/b]i:[b][/b]s", $timeStamp)), "You have been temporarily banned.");

		Query("update {users} set tempbanpl = {0}, tempbantime = {1}, powerlevel = -1 where id = {2}", $user['powerlevel'], $timeStamp, $userid);
		redirect(format("User has been banned for {0}.", TimeUnits($timeStamp - time())), actionLink("profile", $userid), "that user's profile");
	}
}

/* QUERY PART
 * ----------
 */

$failed = false;

if ($_POST['action'] == "Edit profile") {
	$passwordEntered = false;

	if ($_POST["currpassword"] != "") {
		if (password_verify($_POST['currpassword'], $loguser['password']))
			$passwordEntered = true;
		else {
			Alert("Invalid password");
			$failed = true;
			$selectedTab = "account";
			$tabs["account"]["page"]["confirm"]["items"]["currpassword"]["fail"] = true;
		}
	}

	$query = "UPDATE {$dbpref}users SET ";
	$sets = [];
	$pluginSettings = unserialize($user['pluginsettings']);

	foreach($tabs as $id => &$tab) {
		if (!isset($tab['page'])) continue;
		if ($id == "account" && !$passwordEntered) continue;

		foreach($tab['page'] as $id => &$section) {
			foreach($section['items'] as $field => &$item) {
				if ($item['callback']) {
					$ret = $item['callback']($field, $item);
					if ($ret === true)
						continue;
					else if ($ret != "") {
						Alert($ret, 'Error');
						$failed = true;
						$selectedTab = $id;
						$item["fail"] = true;
					}
				}

				switch($item['type']) {
					case "label":
						break;
					case "color":
						$val = $_POST[$field];
						var_dump($val);
						if (!preg_match("/^#[0-9a-fA-F]*$/", $val))
							$val = "";
						$sets[] = $field." = '".SqlEscape($val)."'";
						break;
					case "text":
					case "textarea":
						$sets[] = $field." = '".SqlEscape($_POST[$field])."'";
					case "password":
						if ($_POST[$field])
							$sets[] = $field." = '".SqlEscape($_POST[$field])."'";
						break;
					case "select":
						$val = $_POST[$field];
						if (array_key_exists($val, $item['options']))
							$sets[] = $field." = '".sqlEscape($val)."'";
						break;
					case "number":
						$num = (int)$_POST[$field];
						if ($num < 1)
							$num = $item['min'];
						elseif ($num > $item['max'])
							$num = $item['max'];
						$sets[] = $field." = ".$num;
						break;
					case "datetime":
						if ($_POST[$item['presetname']] != -1)
							$_POST[$field] = $_POST[$item['presetname']];
						$sets[] = $field." = '".SqlEscape($_POST[$field])."'";
						break;
					case "checkbox":
						$val = (int)($_POST[$field] == "on");
						if ($item['negative'])
							$val = (int)($_POST[$field] != "on");
						$sets[] = $field." = ".$val;
						break;
					case "radiogroup":
						if (array_key_exists($_POST[$field], $item['options']))
							$sets[] = $field." = '".SqlEscape($_POST[$field])."'";
						break;
					case "birthday":
						if ($_POST[$field]) {
							$val = @stringtotimestamp($_POST[$field]);
							if ($val > time())
								$val = 0;
						}
						else
							$val = 0;
						$sets[] = $field." = '".$val."'";
						break;
					case "timezone":
						$val = ((int)$_POST[$field.'H'] * 3600) + ((int)$_POST[$field.'M'] * 60) * ((int)$_POST[$field.'H'] < 0 ? -1 : 1);
						$sets[] = $field." = ".$val;
						break;

					case 'gender':
						$gender = "N/A";
						if ($_POST[$field] == "Custom" && trim($_POST[$field . '_custom']) != "") {
							$gender = $_POST[$field . "_custom"];
						} else if (trim($_POST[$field]) != "") {
							$gender = $_POST[$field];
						}
						$sets[] = $field . " = \"" . sqlEscape($gender) . "\"";
						break;

					case 'pronouns':
						$pronouns = "";
						if (array_key_exists($_POST[$field], $defaultPronouns)) {
							$pronouns = $_POST[$field];
						} else if ($_POST[$field] == "custom") {
							$pronouns =
								$_POST[$field . "_0"] . "/" .
								$_POST[$field . "_1"] . "/" .
								$_POST[$field . "_2"] . "/" .
								$_POST[$field . "_3"] . "/" .
								$_POST[$field . "_4"];
						} else {
							$pronouns = "";
						}
						$sets[] = $field . " = \"" . sqlEscape($pronouns) . "\"";
						break;


					//TODO: These two are copypasta, fixit
					case "displaypic":
						if ($_POST['remove'.$field]) {
							@unlink($dataDir."avatars/$userid");
							$sets[] = $field." = ''";
							break;
						}
						if ($_FILES[$field]['name'] == "" || $_FILES[$field]['error'] == UPLOAD_ERR_NO_FILE)
							break;
						$res = HandlePicture($field, 0, $item['errorname'], $user['powerlevel'] > 0 || $loguser['powerlevel'] > 0);
						if ($res === true)
							$sets[] = $field." = '#INTERNAL#'";
						else
						{
							Alert($res);
							$failed = true;
							$item["fail"] = true;
						}
						break;
					case "minipic":
						if ($_POST['remove'.$field]) {
							@unlink($dataDir."minipic/$userid");
							$sets[] = $field." = ''";
							break;
						}
						if ($_FILES[$field]['name'] == "" || $_FILES[$field]['error'] == UPLOAD_ERR_NO_FILE)
							break;
						$res = HandlePicture($field, 1, $item['errorname']);
						if ($res === true)
							$sets[] = $field." = '#INTERNAL#'";
						else
						{
							Alert($res);
							$failed = true;
							$item["fail"] = true;
						}
						break;
				}
			}
		}
	}

	//Force theme names to be alphanumeric to avoid possible directory traversal exploits ~Dirbaio
	if (preg_match("/^[a-zA-Z0-9_]+$/", $_POST['theme']))
		$sets[] = "theme = '".SqlEscape($_POST['theme'])."'";

	$sets[] = "pluginsettings = '".SqlEscape(serialize($pluginSettings))."'";
	if ((int)$_POST['powerlevel'] != $user['powerlevel']) $sets[] = "tempbantime = 0";

	$query .= join(", ", $sets)." WHERE id = ".$userid;
	if (!$failed) {
		RawQuery($query);
		if ($loguserid == $userid)
			$loguser = Fetch(Query("select * from {users} where id={0}", $loguserid));

		logAction('edituser', ['user2' => $user['id']]);
		redirectAction("profile", $userid);
	}
}

//If failed, get values from $_POST
//Else, get them from $user

foreach($tabs as &$tab) {
	if (!isset($tab['page'])) continue;

	foreach($tab['page'] as &$section) {
		foreach($section['items'] as $field => &$item) {
			if ($item['type'] == "label" || $item['type'] == "password")
				continue;

			if (!$failed) {
				if (!isset($item["value"]))
					$item["value"] = $user[$field];
			}
			else {
				if ($item['type'] == 'checkbox')
					$item['value'] = ($_POST[$field] == 'on') ^ $item['negative'];
				elseif ($item['type'] == 'timezone')
					$item['value'] = ((int)$_POST[$field.'H'] * 3600) + ((int)$_POST[$field.'M'] * 60) * ((int)$_POST[$field.'H'] < 0 ? -1 : 1);
				elseif ($item['type'] == 'birthday')
					$item['value'] = @stringtotimestamp($_POST['birthday']);
				else
					$item['value'] = $_POST[$field];
			}
		}
		unset($item);
	}
	unset($section);
}
unset($tab);

if ($failed)
	$loguser['theme'] = $_POST['theme'];

function handlePronouns($field, $item) {
	global $defaultPronouns;

	if ($_POST[$field] == "custom") {
		for ($i = 0; $i < 5; $i++) {
			if (trim($_POST[$field . "_" . $i]) == "")
				return "You must fill out all the fields for pronouns.";
			else if (!preg_match("/^\p{L}+$/ui", $_POST[$field."_".$i]))
				return "You can't use special characters in your pronouns.";
		}
	} else if ($_POST[$field] != "N/A" && !array_key_exists($_POST[$field], $defaultPronouns)) {
		return "Did you mean to select custom pronouns?";
	}
}

function HandlePicture($field, $type, $errorname, $allowOversize = false) {
	global $userid, $dataDir;
	if ($type == 0) {
		$extensions = [".png",".jpg",".jpeg",".gif"];
		$maxDim = 100;
		$maxSize = 300 * 1024;
	}
	else if ($type == 1) {
		$extensions = [".png", ".gif"];
		$maxDim = 16;
		$maxSize = 100 * 1024;
	}

	$fileName = $_FILES[$field]['name'];
	$fileSize = $_FILES[$field]['size'];
	$tempFile = $_FILES[$field]['tmp_name'];
	list($width, $height, $fileType) = getimagesize($tempFile);

	if ($type == 0 && ($width > 300 || $height > 300))
		return "That avatar is definitely too big. The avatar field is meant for an avatar, not a wallpaper.";

	$extension = strtolower(strrchr($fileName, "."));
	if (!in_array($extension, $extensions))
		return format("Invalid extension used for {0}. Allowed: {1}", $errorname, join(", ", $extensions));

	if ($fileSize > $maxSize && !$allowOversize)
		return format("File size for {0} is too high. The limit is {1} bytes, the uploaded image is {2} bytes.", $errorname, $maxSize, $fileSize)."</li>";

	switch($fileType) {
		case 1:
			$sourceImage = imagecreatefromgif ($tempFile);
			break;
		case 2:
			$sourceImage = imagecreatefromjpeg($tempFile);
			break;
		case 3:
			$sourceImage = imagecreatefrompng($tempFile);
			break;
	}

	$oversize = ($width > $maxDim || $height > $maxDim);
	if ($type == 0) {
		$targetFile = $dataDir."avatars/".$userid;

		if ($allowOversize || !$oversize) {
			//Just copy it over.
			copy($tempFile, $targetFile);
		}
		else {
			//Resample that mother!
			$ratio = $width / $height;
			if ($ratio > 1) {
				$targetImage = imagecreatetruecolor($maxDim, floor($maxDim / $ratio));
				imagecopyresampled($targetImage, $sourceImage, 0,0,0,0, $maxDim, $maxDim / $ratio, $width, $height);
			} else {
				$targetImage = imagecreatetruecolor(floor($maxDim * $ratio), $maxDim);
				imagecopyresampled($targetImage, $sourceImage, 0,0,0,0, $maxDim * $ratio, $maxDim, $width, $height);
			}
			imagepng($targetImage, $targetFile);
			imagedestroy($targetImage);
		}
	}
	elseif ($type == 1) {
		$targetFile = $dataDir."minipics/".$userid;

		if ($oversize) {
			//Don't allow minipics over $maxDim for anypony.
			return format("Dimensions of {0} must be at most {1} by {1} pixels.", $errorname, $maxDim);
		}
		else
			copy($tempFile, $targetFile);
	}
	return true;
}

// Special field-specific callbacks
function HandlePassword($field, $item) {
	global $sets, $salt, $user, $loguser, $loguserid;
	if ($_POST[$field] != "" && $_POST['repeat'.$field] != "" && $_POST['repeat'.$field] !== $_POST[$field]) {
		return "To change your password, you must type it twice without error.";
	}

	if ($_POST[$field] != "" && $_POST['repeat'.$field] == "")
		$_POST[$field] = "";

	if ($_POST[$field]) {
		$newsalt = Shake();
		$sha = doHash($_POST[$field].$salt.$newsalt);
		$sets[] = "pss = '".$newsalt."'";
		$_POST[$field] = password_hash($_POST[$field], PASSWORD_DEFAULT);

		//Now logout all the sessions that aren't this one, for security.
		Query("DELETE FROM {sessions} WHERE id != {0} and user = {1}", doHash($_COOKIE['logsession'].$salt), $user["id"]);
	}

	return false;
}

function HandleDisplayname($field, $item) {
	global $user;
	if (!IsReallyEmpty($_POST[$field]) || $_POST[$field] == $user['name']) {
		// unset the display name if it's really empty or the same as the login name.
		$_POST[$field] = "";
	}
	else {
		$dispCheck = FetchResult("select count(*) from {users} where id != {0} and (name = {1} or displayname = {1})", $user['id'], $_POST[$field]);
		if ($dispCheck) {

			return format("The display name you entered, \"{0}\", is already taken.", SqlEscape($_POST[$field]));
		}
		else if (strpos($_POST[$field], ";") !== false) {
			$user['displayname'] = str_replace(";", "", $_POST[$field]);

			return "The display name you entered cannot contain semicolons.";
		}
		else if ($_POST[$field] !== ($_POST[$field] = preg_replace('/(?! )[\pC\pZ]/u', '', $_POST[$field]))) {

			return "The display name you entered cannot contain control characters.";
		}
	}
}

function HandleUsername($field, $item) {
	global $user;
	if (!IsReallyEmpty($_POST[$field]))
		$_POST[$field] = $user[$field];

	$dispCheck = FetchResult("select count(*) from {users} where id != {0} and (name = {1} or displayname = {1})", $user['id'], $_POST[$field]);
	if ($dispCheck) {

		return format("The login name you entered, \"{0}\", is already taken.", SqlEscape($_POST[$field]));
	}
	else if (strpos($_POST[$field], ";") !== false) {
		$user['name'] = str_replace(";", "", $_POST[$field]);

		return "The login name you entered cannot contain semicolons.";
	}
	else if ($_POST[$field] !== ($_POST[$field] = preg_replace('/(?! )[\pC\pZ]/u', '', $_POST[$field]))) {

		return "The login name you entered cannot contain control characters.";
	}
}

function HandlePowerlevel($field, $item) {
	global $user, $loguserid, $userid;
	$id = $userid;
	if ($user['powerlevel'] != (int)$_POST['powerlevel'] && $id != $loguserid) {
		$newPL = (int)$_POST['powerlevel'];
		$oldPL = $user['powerlevel'];

		if ($newPL == 5)
			; //Do nothing -- System won't pick up the phone.
		else if ($newPL == -1) {
			SendSystemPM($id, "If you don't know why this happened, feel free to ask the one most likely to have done this. Calmly, if possible.", "You have been banned.");
		}
		else if ($newPL == 0) {
			if ($oldPL == -1)
				SendSystemPM($id, "Try not to repeat whatever you did that got you banned.", "You have been unbanned.");
			else if ($oldPL > 0)
				SendSystemPM($id, "Try not to take it personally.", "You have been brought down to normal.");
		}
		else if ($newPL == 4) {
			SendSystemPM($id, "Your profile is now untouchable to anybody but you. You can give root status to anybody else, and can access the RAW UNFILTERED POWERRR of sql.php. Do not abuse this. Your root status can only be removed through sql.php.", "You are now a root user.");
		}
		else {
			if ($oldPL == -1)
				; //Do nothing.
			else if ($oldPL > $newPL)
				SendSystemPM($id, "Try not to take it personally.", "You have been demoted.");
			else if ($oldPL < $newPL)
				SendSystemPM($id, "Congratulations. Don't forget to review the rules regarding your newfound powers.", "You have been promoted.");
		}
	}
}


/* EDITOR PART
 * -----------
 */

//Dirbaio: Rewrote this so that it scans the themes dir.
$dir = "themes/";
$themeList = "";
$themes = [];

// Open a known directory, and proceed to read its contents
if (is_dir($dir)) {
	if ($dh = opendir($dir)) {
		while (($file = readdir($dh)) !== false) {
			if (filetype($dir . $file) != "dir") continue;
			if ($file == ".." || $file == ".") continue;
			$infofile = $dir.$file."/themeinfo.txt";

			if (file_exists($infofile)) {
				$themeinfo = file_get_contents($infofile);
				$themeinfo = explode("\n", $themeinfo, 2);

				$themes[$file]["name"] = trim($themeinfo[0]);
				$themes[$file]["author"] = trim($themeinfo[1]);
			}
			else {
				$themes[$file]["name"] = $file;
				$themes[$file]["author"] = "";
			}
		}
		closedir($dh);
	}
}

asort($themes);

$themeList .= "
	<div style=\"text-align: right;\">
		<input type=\"text\" placeholder=\"Search\" id=\"search\" onkeyup=\"searchThemes(this.value);\">
	</div>";

foreach($themes as $themeKey => $themeData) {
	$themeName = $themeData["name"];
	$themeAuthor = $themeData["author"];

	$qCount = "select count(*) from {users} where theme='".$themeKey."'";
	$numUsers = FetchResult($qCount);

	$preview = "themes/".$themeKey."/preview.png";
	if (!is_file($preview))
		$preview = "img/nopreview.png";
	$preview = resourceLink($preview);

	$preview = "<img src=\"".$preview."\" alt=\"".$themeName."\" style=\"margin-bottom: 0.5em\">";

	if ($themeAuthor)
		$byline = "<br>".nl2br($themeAuthor);
	else
		$byline = "";

	if ($themeKey == $user['theme'])
		$selected = " checked=\"checked\"";
	else
		$selected = "";

	$themeList .= format(
"
	<div style=\"display: inline-block;\" class=\"theme\" title=\"{0}\">
		<input style=\"display: none;\" type=\"radio\" name=\"theme\" value=\"{3}\"{4} id=\"{3}\" onchange=\"ChangeTheme(this.value);\">
		<label style=\"display: inline-block; clear: left; padding: 0.5em; {6} width: 260px; vertical-align: top\" onmousedown=\"void();\" for=\"{3}\">
			{2}<br>
			<strong>{0}</strong>
			{1}<br>
			{5}
		</label>
	</div>
",	$themeName, $byline, $preview, $themeKey, $selected, Plural($numUsers, "user"), "");
}

if ($editUserMode && $user['powerlevel'] < 4 && $user['tempbantime'] == 0)
	write(
"
	<form action=\"".actionLink("editprofile")."\" method=\"post\">
		<table class=\"outline margin width25\" style=\"float: right;\">
			<tr class=\"header0\">
				<th colspan=\"2\">
					Quick-E Ban&trade;
				</th>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"until\">Target time</label>
				</td>
				<td class=\"cell0\">
					<input id=\"until\" name=\"until\" type=\"text\">
				</td>
			</tr>
			<tr>
				<td class=\"cell1\" colspan=\"2\">
					<input type=\"submit\" name=\"action\" value=\"Tempban\">
					<input type=\"hidden\" name=\"userid\" value=\"{0}\">
					<input type=\"hidden\" name=\"editusermode\" value=\"1\">
					<input type=\"hidden\" name=\"key\" value=\"{1}\">
				</td>
			</tr>
		</table>
	</form>
", $userid, $loguser['token']);

if (!isset($selectedTab)) {
	$selectedTab = "general";
	foreach($tabs as $id => $tab) {
		if (isset($_GET[$id])) {
			$selectedTab = $id;
			break;
		}
	}
}

Write("<div class=\"margin width0\" id=\"tabs\">");
foreach($tabs as $id => $tab) {
	$selected = ($selectedTab == $id) ? " selected" : "";
	Write("
	<button id=\"{2}Button\" class=\"tab{1}\" onclick=\"showEditProfilePart('{2}');\">{0}</button>
	", $tab['name'], $selected, $id);
}
Write("
</div>
<form action=\"".actionLink("editprofile")."\" method=\"post\" enctype=\"multipart/form-data\">
");

foreach($tabs as $id => $tab) {
	if (isset($tab['page']))
		BuildPage($tab['page'], $id);
	elseif ($id == "theme")
		Write("
	<table class=\"outline margin width100 eptable\" id=\"{0}\"{1}>
		<tr class=\"header0\"><th>Theme</th></tr>
		<tr class=\"cell0\"><td class=\"themeselector\">{2}</td></tr>
	</table>
",	$id, ($id != $selectedTab) ? " style=\"display: none;\"" : "",
	$themeList);
}

$editUserFields = "";
if ($editUserMode) {
	$editUserFields = format(
"
		<input type=\"hidden\" name=\"editusermode\" value=\"1\">
		<input type=\"hidden\" name=\"userid\" value=\"{0}\">
", $userid);
}

Write(
"
	<div class=\"margin center width50\" id=\"button\">
		{2}
		<input type=\"submit\" id=\"submit\" name=\"action\" value=\"Edit profile\">
		<input type=\"hidden\" name=\"id\" value=\"{0}\">
		<input type=\"hidden\" name=\"key\" value=\"{1}\">
	</div>
</form>
", $id, $loguser['token'], $editUserFields);

function BuildPage($page, $id) {
	global $selectedTab, $loguser, $user, $defaultPronouns, $defaultGenders;

	//TODO: This should be done in JS.
	//So that a user who doesn't have Javascript will see all the tabs.
	$display = ($id != $selectedTab) ? " style=\"display: none;\"" : "";

	$cellClass = 0;
	$output = "<table class=\"outline margin width50 eptable\" id=\"".$id."\"".$display.">\n";
	foreach($page as $pageID => $section) {
		$secClass = $section["class"];
		$output .= "<tr class=\"header0 $secClass\"><th colspan=\"2\">".$section['name']."</th></tr>\n";
		foreach($section['items'] as $field => $item) {
			$output .= "<tr class=\"cell$cellClass $secClass\" >\n";
			$output .= "<td>\n";
			if (isset($item["fail"])) $output .= "[ERROR] ";
			if ($item['type'] != "checkbox")
				$output .= "<label for=\"".$field."\">".$item['caption']."</label>\n";

			if (isset($item['hint']))
				$output .= "<img src=\"\" title=\"".$item['hint']."\" alt=\"[?]\">\n";
			$output .= "</td>\n";
			$output .= "<td>\n";

			if (isset($item['before']))
				$output .= " ".$item['before'];

			// Yes, some cases are missing the break; at the end.
			// This is intentional, but I don't think it's a good idea...
			switch($item['type']) {
				case "label":
					$output .= htmlspecialchars($item['value'])."\n";
					break;
				case "birthday":
					$item['type'] = "text";
					//$item['value'] = gmdate("F j, Y", $item['value']);
					$item['value'] = timestamptostring($item['value']);
				case "password":
					if ($item['type'] == "password")
						$item['extra'] = "/ Repeat: <input type=\"password\" name=\"repeat".$field."\" size=\"".$item['size']."\" maxlength=\"".$item['length']."\">";
				case "passwordonce":
					if (!isset($item['size']))
						$item['size'] = 13;
					if (!isset($item['length']))
						$item['length'] = 32;
					if ($item["type"] == "passwordonce")
						$item["type"] = "password";
				case "color":
				case "text":
					$output .= "<input id=\"".$field."\" name=\"".$field."\" type=\"".$item['type']."\" value=\"".htmlspecialchars($item['value'])."\"";
					if (isset($item['size']))
						$output .= " size=\"".$item['size']."\"";
					if (isset($item['length']))
						$output .= " maxlength=\"".$item['length']."\"";
					if (isset($item['width']))
						$output .= " style=\"width: ".$item['width'].";\"";
					if (isset($item['more']))
						$output .= " ".$item['more'];
					$output .= ">\n";
					break;
				case "textarea":
					if (!isset($item['rows']))
						$item['rows'] = 8;
					$output .= "<textarea id=\"".$field."\" name=\"".$field."\" rows=\"".$item['rows']."\" style=\"width: 98%;\">".htmlspecialchars($item['value'])."</textarea>";
					break;
				case "checkbox":
					$output .= "<label><input id=\"".$field."\" name=\"".$field."\" type=\"checkbox\"";
					if ((isset($item['negative']) && !$item['value']) || (!isset($item['negative']) && $item['value']))
						$output .= " checked=\"checked\"";
					$output .= "> ".$item['caption']."</label>\n";
					break;
				case "select":
					$disabled = isset($item['disabled']) ? $item['disabled'] : false;
					$disabled = $disabled ? "disabled=\"disabled\" " : "";
					$checks = [];
					$checks[$item['value']] = " selected=\"selected\"";
					$options = "";
					foreach($item['options'] as $key => $val)
						$options .= format("<option value=\"{0}\"{1}>{2}</option>", $key, $checks[$key], $val);
					$output .= format("<select id=\"{0}\" name=\"{0}\" size=\"1\" {2}>\n{1}\n</select>\n", $field, $options, $disabled);
					break;
				case "radiogroup":
					$checks = [];
					$checks[$item['value']] = " checked=\"checked\"";
					foreach($item['options'] as $key => $val)
						$output .= format("<label><input type=\"radio\" name=\"{1}\" value=\"{0}\"{2}>{3}</label>", $key, $field, $checks[$key], $val);
					break;
				case "displaypic":
				case "minipic":
					$output .= "<input type=\"file\" id=\"".$field."\" name=\"".$field."\" style=\"width: 98%;\">\n";
					$output .= "<label><input type=\"checkbox\" name=\"remove".$field."\"> Remove</label>\n";
					break;
				case "number":
					//$output .= "<input type=\"number\" id=\"".$field."\" name=\"".$field."\" value=\"".$item['value']."\">";
					$output .= "<input type=\"text\" id=\"".$field."\" name=\"".$field."\" value=\"".$item['value']."\" size=\"6\" maxlength=\"4\">";
					break;
				case "datetime":
					$output .= "<input type=\"text\" id=\"".$field."\" name=\"".$field."\" value=\"".$item['value']."\">\n";
					$output .= "or preset:\n";
					$options = "<option value=\"-1\">[select]</option>";
					foreach($item['presets'] as $key => $val)
						$options .= format("<option value=\"{0}\">{1}</option>", $key, $val);
					$output .= format("<select id=\"{0}\" name=\"{0}\" size=\"1\" >\n{1}\n</select>\n", $item['presetname'], $options);
					break;
				case "timezone":
					$output .= "<input type=\"text\" name=\"".$field."H\" size=\"2\" maxlength=\"3\" value=\"".(int)($item['value']/3600)."\">\n";
					$output .= ":\n";
					$output .= "<input type=\"text\" name=\"".$field."M\" size=\"2\" maxlength=\"3\" value=\"".floor(abs($item['value']/60)%60)."\">";
					break;
				case 'gender':
					foreach ($defaultGenders as $k => $v) {
						$output .= format(
							'<label><input type="radio" name="{0}" value="{1}" {2}/>{3}</label> ',
							$field, $k, trim($item['value']) == $k ? "checked=\"checked\"" : "", $v
						);
					}
					$isDefault = array_key_exists(trim($item['value']), $defaultGenders);
					$output .= format(
						'<br><label><input type="radio" name="{0}" value="Custom" {1}/>Other</label>
						<input type="text" name="{0}_custom" value="{2}" size="32">',
						$field, $isDefault ? "" : "checked=\"checked\" ", $isDefault ? "" : htmlspecialchars($item['value'])
					);
					break;

				case 'pronouns':
					$output .= format(
						'<label><input type="radio" name="{0}" value="N/A" {1}>{2}</label>',
						$field, trim($item['value']) == "" || trim($item['value']) == "N/A" ? "checked=\"checked\"" : "", "N/A"
					);

					foreach ($defaultPronouns as $k => $v) {
						$output .= format(
							'<label><input type="radio" name="{0}" value="{1}" {2}>{3}</label>',
							$field, $k, $item['value'] == $k ? "checked=\"checked\"" : "", $v
						);
					}

					if (trim($item['value']) == "") {
						$isCustom = false;
					} else {
						$isCustom = !array_key_exists($item['value'], $defaultPronouns);
					}
					$pronouns = $isCustom && $item['value'] != "custom" ? explode("/", $item['value']) : [];

					$output .= format(
						'<br><label><input type="radio" name="{0}" value="custom" {1}>{2}</label> ',
						$field, $isCustom ? "checked=\"checked\"" : "", "Custom"
					);
					$example = ["they", "them", "their", "theirs", "themself"];
					foreach ($example as $i => $ex) {
						$output .= format(
							'<input type="text" name="{0}_{1}" size="5" placeholder="{2}" value="{3}">',
							$field, $i, $ex, $_POST[$field . "_" . $i] ? htmlspecialchars($_POST[$field . "_" . $i]) : htmlspecialchars($pronouns[$i])
						);
						if ($i != count($example) - 1)
							$output .= "/";
					}
					break;
			}
			if (isset($item['extra']))
				$output .= " ".$item['extra'];

			$output .= "</td>\n";
			$output .= "</tr>\n";
			$cellClass = ($cellClass + 1) % 2;
		}
	}
	$output .= "</table>";
	Write($output);
}


function IsReallyEmpty($subject) {
	$trimmed = trim(preg_replace("/&.*;/", "", $subject));
	return strlen($trimmed) != 0;
}

?>

<script type="text/javascript">
	var passwordChanged = function() {
		if ($("#currpassword").val() == "")
			$("#passwordhide").html(".needpass {display:none;}");
		else
			$("#passwordhide").html("");
	};

	$(function() {
		$("#currpassword").keyup(passwordChanged);
		passwordChanged();
	});

</script>
<style type="text/css" id="passwordhide">

</style>
