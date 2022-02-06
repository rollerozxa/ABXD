<?php

$hugeInt = "bigint(20) NOT NULL DEFAULT '0'";
$genericInt = "int(11) NOT NULL DEFAULT '0'";
$smallerInt = "int(8) NOT NULL DEFAULT '0'";
$bool = "tinyint(1) NOT NULL DEFAULT '0'";
$notNull = " NOT NULL DEFAULT ''";
$text = "text DEFAULT ''"; //NOT NULL breaks in certain versions/settings.
$postText = "mediumtext DEFAULT ''";
$var128 = "varchar(128)".$notNull;
$var256 = "varchar(256)".$notNull;
$var1024 = "varchar(1024)".$notNull;
$AI = "int(11) NOT NULL AUTO_INCREMENT";
$keyID = "primary key (`id`)";

$tables = [
	"badges" => [
		"fields" => [
			"owner" => $genericInt,
			"name" => $var256,
			"color" => $smallerInt,
		],
		"special" => "unique key `steenkinbadger` (`owner`,`name`)"
	],
	"settings" => [
		"fields" => [
			"plugin" => $var128,
			"name" => $var128,
			"value" => $text,
		],
		"special" => "unique key `mainkey` (`plugin`,`name`)"
	],

	//Weird column names: An entry means that "blockee" has blocked the layout of "user"
	"blockedlayouts" => [
		"fields" => [
			"user" => $genericInt,
			"blockee" => $genericInt,
		],
		"special" => "key `mainkey` (`blockee`, `user`)"
	],
	"categories" => [
		"fields" => [
			"id" => $AI,
			"name" => $var256,
			"corder" => $smallerInt,
		],
		"special" => $keyID
	],
	"enabledplugins" => [
		"fields" => [
			"plugin" => $var256,
		],
		"special" => "unique key `plugin` (`plugin`)"
	],
	"forummods" => [
		"fields" => [
			"forum" => $genericInt,
			"user" => $genericInt,
		],
		"special" => "key `mainkey` (`forum`, `user`)"
	],
	"forums" => [
		"fields" => [
			"id" => $AI,
			"title" => $var256,
			"description" => $text,
			"catid" => $smallerInt,
			"minpower" => $smallerInt,
			"minpowerthread" => $smallerInt,
			"minpowerreply" => $smallerInt,
			"numthreads" => $genericInt,
			"numposts" => $genericInt,
			"lastpostdate" => $genericInt,
			"lastpostuser" => $genericInt,
			"lastpostid" => $genericInt,
			"hidden" => $bool,
			"forder" => $smallerInt,
		],
		"special" => $keyID.", key `catid` (`catid`)"
	],
	"guests" => [
		"fields" => [
			"id" => $AI,
			"ip" => "varchar(45)".$notNull,
			"date" => $genericInt,
			"lasturl" => "varchar(100)".$notNull,
			"lastforum" => $genericInt,
			"useragent" => "varchar(100)".$notNull,
			"bot" => $bool,
		],
		"special" => $keyID.", key `ip` (`ip`), key `bot` (`bot`)"
	],
	"ignoredforums" => [
		"fields" => [
			"uid" => $genericInt,
			"fid" => $genericInt,
		],
		"special" => "key `mainkey` (`uid`, `fid`)"
	],
	"ip2c" => [
		"fields" => [
			"ip_from" => "bigint(12) NOT NULL DEFAULT '0'",
			"ip_to" => "bigint(12) NOT NULL DEFAULT '0'",
			"cc" => "varchar(2) DEFAULT ''",
		],
		"special" => "key `ip_from` (`ip_from`)"
	],
	"ipbans" => [
		"fields" => [
			"ip" => "varchar(45)".$notNull,
			"reason" => "varchar(100)".$notNull,
			"date" => $genericInt,
			"whitelisted" => $bool,
		],
		"special" => "unique key `ip` (`ip`), key `date` (`date`)"
	],
	"misc" => [
		"fields" => [
			"version" => $genericInt,
			"views" => $genericInt,
			"hotcount" => $genericInt,
			"maxusers" => $genericInt,
			"maxusersdate" => $genericInt,
			"maxuserstext" => $text,
			"maxpostsday" => $genericInt,
			"maxpostsdaydate" => $genericInt,
			"maxpostshour" => $genericInt,
			"maxpostshourdate" => $genericInt,
			"milestone" => $text,
		],
	],
	"moodavatars" => [
		"fields" => [
			"id" => $AI,
			"uid" => $genericInt,
			"mid" => $genericInt,
			"name" => $var256,
		],
		"special" => $keyID. ", key `mainkey` (`uid`, `mid`)"
	],
	"pmsgs" => [
		"fields" => [
			"id" => $AI,
			"userto" => $genericInt,
			"userfrom" => $genericInt,
			"date" => $genericInt,
			"ip" => "varchar(45)".$notNull,
			"msgread" => $bool,
			"deleted" => "tinyint(4) NOT NULL DEFAULT '0'",
			"drafting" => $bool,
		],
		"special" => $keyID.", key `userto` (`userto`), key `userfrom` (`userfrom`), key `msgread` (`msgread`), key `date` (`date`)"
	],
	"pmsgs_text" => [
		"fields" => [
			"pid" => $genericInt,
			"title" => $var256,
			"text" => $postText,
		],
		"special" => "primary key (`pid`)"
	],
	"poll" => [
		"fields" => [
			"id" => $AI,
			"question" => $var256,
			"briefing" => $text,
			"closed" => $bool,
			"doublevote" => $bool,
		],
		"special" => $keyID
	],
	"pollvotes" => [
		"fields" => [
			"user" => $genericInt,
			"choiceid" => $genericInt,
			"poll" => $genericInt,
		],
		"special" => "key `lol` (`user`, `choiceid`), key `poll` (`poll`)"
	],
	"poll_choices" => [
		"fields" => [
			"id" => $AI,
			"poll" => $genericInt,
			"choice" => $var256,
			"color" => "varchar(25)".$notNull,
		],
		"special" => $keyID.", key `poll` (`poll`)"
	],
	"posts" => [
		"fields" => [
			"id" => $AI,
			"thread" => $genericInt,
			"user" => $genericInt,
			"date" => $genericInt,
			"ip" => "varchar(45)".$notNull,
			"num" => $genericInt,
			"deleted" => $bool,
			"deletedby" => $genericInt,
			"reason" => "varchar(300)".$notNull,
			"options" => "tinyint(4) NOT NULL DEFAULT '0'",
			"mood" => $genericInt,
			"currentrevision" => $genericInt,
		],
		"special" => $keyID.", key `thread` (`thread`), key `date` (`date`), key `user` (`user`), key `ip` (`ip`), key `id` (`id`, `currentrevision`), key `deletedby` (`deletedby`)"
	],
	"posts_text" => [
		"fields" => [
			"pid" => $genericInt,
			"text" => $postText,
			"revision" => $genericInt,
			"user" => $genericInt,
			"date" => $genericInt,
		],
		"special" => "fulltext key `text` (`text`), key `pidrevision` (`pid`, `revision`), key `user` (`user`)"
	],
	"proxybans" => [
		"fields" => [
			"id" => $AI,
			"ip" => "varchar(45)".$notNull,
		],
		"special" => $keyID.", unique key `ip` (`ip`)"
	],
	"queryerrors" => [
		"fields" => [
			"id" => $AI,
			"user" => $genericInt,
			"ip" => "varchar(50)".$notNull,
			"time" => $genericInt,
			"query" => $text,
			"get" => $text,
			"post" => $text,
			"cookie" => $text,
			"error" => $text
		],
		"special" => $keyID
	],
	"log" => [
		"fields" => [
			"user" => $genericInt,
			"date" => $genericInt,
			"type" => "varchar(16)".$notNull,
			"user2" => $genericInt,
			"thread" => $genericInt,
			"post" => $genericInt,
			"forum" => $genericInt,
			"forum2" => $genericInt,
			"pm" => $genericInt,
			"text" => "varchar(1024)".$notNull,
			"ip" => "varchar(50)".$notNull,
		],
	],
	"sessions" => [
		"fields" => [
			"id" => $var256,
			"user" => $genericInt,
			"expiration" => $genericInt,
			"autoexpire" => $bool,
			"iplock" => $bool,
			"iplockaddr" => $var128,
			"lastip" => $var128,
			"lasturl" => $var128,
			"lasttime" => $genericInt,
		],
		"special" => $keyID.", key `user` (`user`), key `expiration` (`expiration`)"
	],
	"smilies" => [
		"fields" => [
			"id" => $AI,
			"code" => "varchar(32)".$notNull,
			"image" => "varchar(32)".$notNull,
		],
		"special" => $keyID
	],
	"threads" => [
		"fields" => [
			"id" => $AI,
			"forum" => $genericInt,
			"user" => $genericInt,
			"date" => $genericInt,
			"firstpostid" => $genericInt,
			"views" => $genericInt,
			"title" => "varchar(100)".$notNull,
			"replies" => $genericInt,
			"lastpostdate" => $genericInt,
			"lastposter" => $genericInt,
			"lastpostid" => $genericInt,
			"closed" => $bool,
			"sticky" => $bool,
			"poll" => $genericInt,
		],
		"special" => $keyID.", key `forum` (`forum`), key `user` (`user`), key `sticky` (`sticky`), key `lastpostdate` (`lastpostdate`), key `date` (`date`), fulltext key `title` (`title`)"
	],
	"threadsread" => [
		"fields" => [
			"id" => $genericInt,
			"thread" => $genericInt,
			"date" => $genericInt,
		],
		"special" => "primary key (`id`, `thread`)"
	],
	// cid = user who commented
	// uid = user whose profile received the comment
	"usercomments" => [
		"fields" => [
			"id" => $AI,
			"uid" => $genericInt,
			"cid" => $genericInt,
			"text" => $text,
			"date" => $genericInt,
		],
		"special" => $keyID.", key `uid` (`uid`), key `date` (`date`)"
	],
	"users" => [
		"fields" => [
			"id" => $AI,
			"name" => "varchar(32)".$notNull,
			"displayname" => "varchar(32)".$notNull,
			"password" => $var256,
			"pss" => "varchar(16)".$notNull,
			"powerlevel" => $smallerInt,
			"posts" => $genericInt,
			"regdate" => $genericInt,
			"minipic" => $var128,
			"picture" => $var128,
			"title" => $var256,
			"postheader" => $text,
			"signature" => $text,
			"bio" => $text,
//			"sex" => "tinyint(2) NOT NULL DEFAULT '2'",
			"gender" => "varchar(32) default 'N/A'",
			'pronouns' => "varchar(32) not null default ''",
			'colorset' => "int(8) not null default 2",
			"rankset" => $var128,
			"realname" => "varchar(60)".$notNull,
			"lastknownbrowser" => $text,
			"location" => $var128,
			"birthday" => $genericInt,
			"email" => "varchar(60)".$notNull,
			"homepageurl" => "varchar(80)".$notNull,
			"homepagename" => "varchar(100)".$notNull,
			"lastposttime" => $genericInt,
			"lastactivity" => $genericInt,
			"lastip" => "varchar(50)".$notNull,
			"lasturl" => $var128,
			"lastforum" => $genericInt,
			"postsperpage" => "int(8) NOT NULL DEFAULT '20'",
			"threadsperpage" => "int(8) NOT NULL DEFAULT '50'",
			"timezone" => "float NOT NULL DEFAULT '0'",
			"theme" => "varchar(64)".$notNull,
			"signsep" => $bool,
			"dateformat" => "varchar(20) NOT NULL DEFAULT 'm-d-y'",
			"timeformat" => "varchar(20) NOT NULL DEFAULT 'h:i a'",
			"fontsize" => "int(8) NOT NULL DEFAULT '80'",
			"blocklayouts" => $bool,
			"globalblock" => $bool,
			"usebanners" => "tinyint(1) NOT NULL DEFAULT '1'",
			"showemail" => $bool,
			"newcomments" => $bool,
			"tempbantime" => $hugeInt,
			"tempbanpl" => $smallerInt,
			"forbiddens" => $var1024,
			"pluginsettings" => $text,
			"lostkey" => $var128,
			"lostkeytimer" => $genericInt,
			"loggedin" => $bool,
			"convertpassword" => $var256,
			"convertpasswordsalt" => $var256,
			"convertpasswordtype" => $var256,
		],
		"special" => $keyID.", key `posts` (`posts`), key `name` (`name`), key `lastforum` (`lastforum`), key `lastposttime` (`lastposttime`), key `lastactivity` (`lastactivity`)"
	],
	"uservotes" => [
		"fields" => [
			"uid" => $genericInt,
			"voter" => $genericInt,
			"up" => $bool,
		],
		"special" => "primary key (`uid`, `voter`)"
	],
];
?>
