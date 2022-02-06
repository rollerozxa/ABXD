<?php
//  AcmlmBoard XD - User account registration page
//  Access: any, but meant for guests.

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Register", "register"));
makeBreadcrumbs($crumbs);

$title = "Register";

if (isset($_POST['name'])) {
	$name = trim($_POST['name']);
	$cname = str_replace(" ","", strtolower($name));

	$rUsers = Query("select name, displayname from {users}");
	while($user = Fetch($rUsers)) {
		$uname = trim(str_replace(" ", "", strtolower($user['name'])));
		if ($uname == $cname)
			break;
		$uname = trim(str_replace(" ", "", strtolower($user['displayname'])));
		if ($uname == $cname)
			break;
	}

	$ipKnown = FetchResult("select COUNT(*) from {users} where lastip={0}", $_SERVER['REMOTE_ADDR']);

	//This makes testing faster.
	if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1" || $_SERVER['REMOTE_ADDR'] == "::1")
		$ipKnown = 0;

	if ($uname == $cname)
		$err = "This user name is already taken. Please choose another.";
	else if ($name == "" || $cname == "")
		$err = "The user name must not be empty. Please choose one.";
	else if (strpos($name, ";") !== false)
		$err = "The user name cannot contain semicolons.";
	elseif ($ipKnown >= 3)
		$err = "Another user is already using this IP address.";
	else if ($_POST['pass'] !== $_POST['pass2'])
		$err = "The passwords you entered don't match.";

	if ($err) {
		Alert($err);
	}
	else {
		$newsalt = Shake();
		$password = password_hash($_POST['pass'], PASSWORD_DEFAULT);

		$rUsers = Query("insert into {users} (name, password, pss, regdate, lastactivity, lastip, email, theme) values ({0}, {1}, {2}, {3}, {3}, {4}, {5}, {6})", $_POST['name'], $password, $newsalt, time(), $_SERVER['REMOTE_ADDR'], $_POST['email'], Settings::get("defaultTheme"));

		$uid = insertId();

		if ($uid == 1)
			Query("update {users} set powerlevel = 4 where id = 1");

		logAction('register', ['user' => $uid]);

		$user = Fetch(Query("select * from {users} where id={0}", $uid));
		$user["rawpass"] = $_POST["pass"];

		$bucket = "newuser"; include("lib/pluginloader.php");

		$sessionID = Shake();
		setcookie("logsession", $sessionID, 0, $boardroot, "", false, true);
		Query("INSERT INTO {sessions} (id, user, autoexpire) VALUES ({0}, {1}, {2})", doHash($sessionID.$salt), $user["id"], 0);
		redirectAction("board");
	}
}


$name = "";
if (isset($_POST["name"]))
	$name = htmlspecialchars($_POST["name"]);
$email = "";
if (isset($_POST["email"]))
	$email = htmlspecialchars($_POST["email"]);
echo "
<form action=\"".actionLink("register")."\" method=\"post\">
	<table class=\"outline margin width50\">
		<tr class=\"header0\">
			<th colspan=\"2\">
				Register
			</th>
		</tr>
		<tr>
			<td class=\"cell2\">
				<label for=\"un\">User name</label>
			</td>
			<td class=\"cell0\">
				<input type=\"text\" id=\"un\" name=\"name\" value=\"$name\" maxlength=\"20\" style=\"width: 98%;\"  class=\"required\">
			</td>
		</tr>
		<tr>
			<td class=\"cell2\">
				<label for=\"pw\">Password</label>
			</td>
			<td class=\"cell1\">
				<input type=\"password\" id=\"pw\" name=\"pass\" size=\"13\" maxlength=\"32\" class=\"required\"> / Repeat: <input type=\"password\" id=\"pw2\" name=\"pass2\" size=\"13\" maxlength=\"32\" class=\"required\">
			</td>
		</tr>
		<tr>
			<td class=\"cell2\">
				<label for=\"email\">Email address</label>
			</td>
			<td class=\"cell0\">
				<input type=\"email\" id=\"email\" name=\"email\" value=\"$email\" style=\"width: 98%;\" maxlength=\"60\">
			</td>
		</tr>";

echo "
		<tr class=\"cell2\">
			<td></td>
			<td>
				<input type=\"submit\" name=\"action\" value=\"Register\"/>
			</td>
		</tr>
		<tr>
			<td colspan=\"2\" class=\"cell0 smallFonts\">
				Specifying an email address is not exactly a hard requirement, but it will allow you to reset your password should you forget it. By default, your email is not shown.
			</td>
		</tr>
	</table>
</form>";

function MakeOptions($fieldName, $checkedIndex, $choicesList) {
	$result = "";
	$checks[$checkedIndex] = " checked=\"checked\"";
	foreach($choicesList as $key=>$val)
		$result .= format("
					<label>
						<input type=\"radio\" name=\"{1}\" value=\"{0}\"{2}>
						{3}
					</label>", $key, $fieldName, $checks[$key], $val);
	return $result;
}
