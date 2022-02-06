<?php

if (Settings::get("mailResetSender") == "")
	Kill("No sender specified for reset emails. Please check the board settings.");

if (isset($_GET['key']) && isset($_GET['id'])) {
	$user = Query("select pss from {users} where id = {0}", (int)$_GET['id']);
	if (NumRows($user) == 0)
		Kill("This old key cannot be used.", "Invalid key");

	$user = Fetch($user);

	$sha = doHash($_GET['key'].$salt.$user["pss"]);

	$user = Query("select id, name, password, pss from {users} where id = {0} and lostkey = {1} and lostkeytimer > {2}", (int)$_GET['id'], $sha, (time() - (60*60)));

	if (NumRows($user) == 0)
		Kill("This old key cannot be used.", "Invalid key");
	else
		$user = Fetch($user);

	$newsalt = Shake();
	$newPass = randomString(8);
	$sha = doHash($newPass.$salt.$newsalt);

	logAction('lostpass2', ['user' => $user["id"]]);

	Query("update {users} set lostkey = '', password = {0}, pss = {2} where id = {1}", $sha, (int)$_GET['id'], $newsalt);
	Kill(format("Your password has been reset to <strong>{0}</strong>. You can use this password to log in to the board. We suggest you change it as soon as possible.", $newPass), "Password reset");
} else if ($_POST['action'] == "Send reset email") {
	if ($_POST['mail'] != $_POST['mail2'])
		Kill("The e-mail addresses you entered don't match, try again.");

	$user = Query("select id, name, password, email, lostkeytimer, pss from {users} where name = {0} and email = {1}", $_POST['name'], $_POST['mail']);
	if (NumRows($user) != 0) {
		//Do not disclose info about user e-mail.
		$user = Fetch($user);
		if ($user['lostkeytimer'] > time() - (60*60)) //wait an hour between attempts
			Kill("To prevent abuse, this function can only be used once an hour.", "Slow down!");

		//Make a RANDOM reset key.
		$resetKey = Shake();

		$hashedResetKey = doHash($resetKey.$salt.$user["pss"]);

		$from = Settings::get("mailResetSender");
		$to = $user['email'];
		$subject = format("Password reset for {0}", $user['name']);
		$message = format("A password reset was requested for your user account on {0}.", Settings::get("boardname"))."\nIf you did not submit this request, this message can be ignored.\n\nTo reset your password, visit the following URL:\n\n".absoluteActionLink("lostpass", $user['id'], "key=$resetKey")."\n\nThis link can be used once.";

		$headers = "From: ".$from."\r\n"."Reply-To: ".$from."\r\n"."X-Mailer: PHP";

		mail($to, $subject, wordwrap($message, 70), $headers);
		logAction('lostpass', ['user2' => $user["id"]]);

		Query("update {users} set lostkey = {0}, lostkeytimer = {1} where id = {2}", $hashedResetKey, time(), $user['id']);
	}
	Kill("Check your email in a moment and follow the link found therein.", "Reset email sent");
} else {
	write("
	<form action=\"".actionLink("lostpass")."\" method=\"post\">
		<table class=\"outline margin width50\">
			<tr class=\"header0\">
				<th colspan=\"2\">
					Lost password
				</th>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"un\">User name</label>
				</td>
				<td class=\"cell0\">
					<input type=\"text\" id=\"un\" name=\"name\" style=\"width: 98%;\" maxlength=\"25\">
				</td>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"em\">Email address</label>
				</td>
				<td class=\"cell1\">
					<input type=\"email\" id=\"em\" name=\"mail\" style=\"width: 98%;\" maxlength=\"60\">
				</td>
			</tr>
			<tr>
				<td class=\"cell2\">
					<label for=\"em\">Retype email address</label>
				</td>
				<td class=\"cell1\">
					<input type=\"email\" id=\"em\" name=\"mail2\" style=\"width: 98%;\" maxlength=\"60\">
				</td>
			</tr>
			<tr class=\"cell2\">
				<td></td>
				<td>
					<input type=\"submit\" name=\"action\" value=\"Send reset email\">
				</td>
			</tr>
			<tr>
				<td class=\"cell1 smallFonts\" colspan=\"2\">
					If you did not specify an email address in your profile, you are <em>not</em> out of luck. The old method of contacting an administrator from outside the board is still an option.
				</td>
			</tr>
		</table>
	</form>
");

}

function randomString($len, $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789") {
   $s = "";
   for ($i = 0; $i < $len; $i++)
   {
	   $p = rand(0, strlen($chars)-1);
	   $s .= $chars[$p];
   }
   return $s;
}
