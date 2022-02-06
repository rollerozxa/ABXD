<?php

if ($loguser['powerlevel'] < 3)
	Kill("Access denied.");

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Admin", "admin"));
$crumbs->add(new PipeMenuLinkEntry("Log", "log"));
makeBreadcrumbs($crumbs);

doLogList("1");
