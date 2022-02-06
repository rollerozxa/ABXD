<?php

if ($loguser['powerlevel'] < 3)
	Kill("You're not an administrator. There is nothing for you here.");

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Admin", "admin"));
$crumbs->add(new PipeMenuLinkEntry("Migrate genders", "migrategenders"));
makeBreadcrumbs($crumbs);

$rField = query("SHOW COLUMNS IN users WHERE field=\"sex\"");
if (numRows($rField) > 0) {
	print("`sex` field exists.<br>");
} else {
	Kill("The database does not have a `sex` field. You don't need to run this script.", "Already done");
}

$sexToGender = [
	0 => "Male",
	1 => "Female",
	2 => "N/A"
];

foreach ($sexToGender as $sex => $gender) {
	printf("Setting gender to %s for users with sex=%d&hellip;<br>", $gender, $sex);
	query("update users set gender=\"" . $gender . "\" where sex=" . $sex);
}

print("Setting user name colors&hellip;<br>");
query("UPDATE users SET colorset=sex");

print("Dropping 'sex' column&hellip;<br>");
query('alter table users drop column sex');
