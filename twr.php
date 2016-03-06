<?php

if(!isset($_GET)){$_GET = &$HTTP_GET_VARS;}
if(!isset($_POST)){$_POST = &$HTTP_POST_VARS;}
if(isset($_GET)){extract($_GET);}
if(isset($_POST)){extract($_POST);}

# Remote File (rf) Manipulator
# Service ~ Twitter (Recent Tweets)
# Author ~ Matt Hill (http://sgnls.net)

# Copyright 2008-2015. You may use and distribute this code as
# long as credit is given, and any amendments do not restrict
# it's continued 'free' distribution and informative value.

# location of rf

$twitter_usr = "celestial_sgnls";
$twitter_limit_initial = "10";

$twitter_limit = $twitter_limit_initial +1;

if(!$twitter_usr){
	$twitter_usr = "celestial_sgnls";
	$twitter_url = "https://twitter.com/" . $twitter_usr;
}
else{
	$twitter_url = "https://twitter.com/" . $twitter_usr;
}

$opts = array('http' => array('header' => 'Accept-Charset: UTF-8, *;q=0'));
$context = stream_context_create($opts);

# $twitter_data = file_get_contents($twitter_url,false, $context);

$twitter_data = file_get_contents($twitter_url,true);

$twitter_data_utf8 = mb_convert_encoding($twitter_data, 'HTML-ENTITIES', "UTF-8");

	$destroy = explode("<div class=\"content\">",$twitter_data_utf8,$twitter_limit); # dictate result returns
	$split = array_slice($destroy,1,$twitter_limit); # limit results > array_slice(value, offset, limit)

		echo "<ol id=\"twitter\">\n";

	foreach($split as $data){
  	
		$datad = preg_match("#\<p class=\"TweetTextSize (.+?)\" lang=\"(.+?)\" data-aria-label-part=\"0\"\>(.+?)\<\/p\>#s", $data, $all_matches);

		$got_data = $all_matches[3];

#		$replacers = array(
#			"#\<span(.+?)\<\/span\>#s" => "",
#			"#\<a href(.+?)\<\/a\>#s" => "",
#			"#\<img(.+?)\/\>#s" => "");
#		$cleaner = preg_replace_callback(function($matches) use (&$replacers){return array_shift($replacers);},"",$got_data);
		$find = array(
			"<span class=\"tco-ellipsis\">",
			"<span class=\"invisible\">http://www.</span>",
			" class=\"twitter-timeline-link\" target=\"_blank\" ",
			" rel=\"nofollow\" dir=\"ltr\" ",
			"data-expanded-url",
			"title",
			"\" >",
			"</span>",
			"<span class=\"invisible\">&nbsp;",
			"</span><span class=\"js-display-url\">",
			" data-query-source=\"hashtag_click\" class=\"twitter-hashtag pretty-link js-nav\" dir=\"ltr\" ",
			" class=\"twitter-atreply pretty-link\" dir=\"ltr\" ",
			"</span><span class=\"invisible\">",
			"<s>@</s>",
			"<s>#</s>",
			"&hellip;",
			" class=\"twitter-timeline-link u-hidden\" data-pre-embedded=\"true\" dir=\"ltr\"",
			" class=\"twitter-atreply pretty-link js-nav\" dir=\"ltr\"",
			"<a href=\"/");
		$repl = array(
			"","",
			"","",
			" data-expanded-url",
			" title","\">",
			"","",
			"","",
			"","",
			"@","#",
			"","",
			"","<a href=\"http://twitter.com/");

		$clean = strtr($got_data, array_combine($find, $repl));

		$cleaner = preg_replace("#\<a href=\"http://t.co/(.+?)http://(.+?)\" title=\"#s","<a href=\"",$clean);

		$avatar = preg_match("#\<img class=\"ProfileAvatar-image \" src=\"(.+?)\" (.+?)\>#s", $twitter_data_utf8, $avatar_matches);
	
		$av = $avatar_matches[1];

		echo "<li>$cleaner</li>\n\n";
	
	}

	echo "</ol>\n";
?>
