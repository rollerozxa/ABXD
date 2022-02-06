<?php

$settings = [
	"boardname" => [
		"type" => "text",
		"default" => "AcmlmBoard XD",
		"name" => "Board name"
	],
	"metaDescription" => [
		"type" => "text",
		"default" => "AcmlmBoard XD",
		"name" => "Meta description"
	],
	"metaTags" => [
		"type" => "text",
		"default" => "AcmlmBoard XD abxd",
		"name" => "Meta tags"
	],
	"dateformat" => [
		"type" => "text",
		"default" => "m-d-y, h:i a",
		"name" => "Date format"
	],
	"customTitleThreshold" => [
		"type" => "integer",
		"default" => "100",
		"name" => "Custom Title Threshold"
	],
	"oldThreadThreshold" => [
		"type" => "integer",
		"default" => "3",
		"name" => "Old Thread Threshold months"
	],
	"viewcountInterval" => [
		"type" => "integer",
		"default" => "10000",
		"name" => "Viewcount Report Interval"
	],
	"ajax" => [
		"type" => "boolean",
		"default" => "1",
		"name" => "Enable AJAX"
	],
	"guestLayouts" => [
		"type" => "boolean",
		"default" => "0",
		"name" => "Show post layouts to guests"
	],
	"breadcrumbsMainName" => [
		"type" => "text",
		"default" => "Main",
		"name" => "Text in breadcrumbs 'main' link",
	],
	"menuMainName" => [
		"type" => "text",
		"default" => "Main",
		"name" => "Text in menu 'main' link",
	],
	"mailResetSender" => [
		"type" => "text",
		"default" => "",
		"name" => "Password Reset e-mail Sender",
		"help" => "Email address used to send the pasword reset e-mails. If left blank, the password reset feature is disabled.",
	],
	"defaultTheme" => [
		"type" => "theme",
		"default" => "abxd30",
		"name" => "Default Board Theme",
	],
	"defaultLayout" => [
		"type" => "layout",
		"default" => "abxd",
		"name" => "Board layout",
	],
	"showGender" => [
		"type" => "boolean",
		"default" => "1",
		"name" => "Color usernames based on gender"
	],
	"defaultLanguage" => [
		"type" => "language",
		"default" => "en_US",
		"name" => "Board language",
	],
	"floodProtectionInterval" => [
		"type" => "integer",
		"default" => "10",
		"name" => "Minimum time between user posts"
	],
	"nofollow" => [
		"type" => "boolean",
		"default" => "0",
		"name" => "Add rel=nofollow to all user-posted links"
	],
	"tagsDirection" => [
		"type" => "options",
		"options" => ['Left' => 'Left', 'Right' => 'Right'],
		"default" => 'Right',
		"name" => "Direction of thread tags.",
	],
	"alwaysMinipic" => [
		"type" => "boolean",
		"default" => "0",
		"name" => "Show Minipics everywhere",
	],
	"showExtraSidebar" => [
		"type" => "boolean",
		"default" => "1",
		"name" => "Show extra info in post sidebar",
	],
	"showPoRA" => [
		"type" => "boolean",
		"default" => "1",
		"name" => "Show Points of Required Attention",
	],
	"PoRATitle" => [
		"type" => "text",
		"default" => "Points of Required Attention&trade;",
		"name" => "PoRA title",
	],
	"PoRAText" => [
		"type" => "texthtml",
		"default" => "Welcome to your new ABXD Board!<br>The first person to register gets root/owner access. For this reason, avoid showing people the URL of your site before it is set up.<br>Then, when you have registered, you can edit the board settings, forum list, this very message, and other stuff from the admin panel.<br>Enjoy ABXD!",
		"name" => "PoRA text",
	],

	"profilePreviewText" => [
		"type" => "textbbcode",
		"default" => "This is a sample post. You [b]probably[/b] [i]already[/i] [u]know[/u] what this is for.

[quote=Goomba][quote=Mario]Woohoo! [url=http://www.mariowiki.com/Super_Mushroom]That's what I needed![/url][/quote]Oh, nooo! *stomp*[/quote]

Well, what more could you [url=http://en.wikipedia.org]want to know[/url]? Perhaps how to do the classic infinite loop?
[code]while(true){
printf(\"Hello World!
\");
}[/code]",
		"name" => "Post Preview text"
	],

	"trashForum" => [
		"type" => "forum",
		"default" => "1",
		"name" => "Trash forum",
	],
	"hiddenTrashForum" => [
		"type" => "forum",
		"default" => "1",
		"name" => "Forum for deleted threads",
	],
];
