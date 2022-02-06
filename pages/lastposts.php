<?php

$title = "Last posts";

$crumbs = new PipeMenu();
$crumbs->add(new PipeMenuLinkEntry("Last posts", "lastposts"));
makeBreadcrumbs($crumbs);

doLastPosts(false, 100);
