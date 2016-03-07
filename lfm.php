<?php

if(!isset($_GET)){$_GET = &$HTTP_GET_VARS;}
if(!isset($_POST)){$_POST = &$HTTP_POST_VARS;}
if(isset($_GET)){extract($_GET);}
if(isset($_POST)){extract($_POST);}

# Remote File (rf) Manipulator
# Service ~ last.fm (Recent Tracks)
# http://sgnls.net

# Copyright 2016. You may use and distribute this code as
# long as credit is given, and any amendments do not restrict
# it's continued 'free' distribution and informative value.

$lfm_usr = sgnls;
$lfm_limit = 8;

# replacement array
$hunt = array("+","%27"," live "," The "," Is ","%3F","/music/"," Of ","/serve/34s","/serve/64s","an hour"," 2015"," minutes ago","T%C3%BDr");
$swap = array(" ","'"," Live "," the "," is ","?",""," of ","/serve/126s","/serve/126s","An hour",", 2015","m","Týr");

# location of remote file
if($lfm_usr == ""){
	$lfm_usr = "sgnls";
}
else{
	$lfm_urlb = "http://last.fm/user/" . $lfm_usr;
	# $lfm_url_30 = "http://last.fm/user/" . $lfm_usr . "/library/artists?date_preset=LAST_30_DAYS";
	# $lfm_url_90 = "http://last.fm/user/" . $lfm_usr . "/library/artists?date_preset=LAST_90_DAYS";
	# $lfm_url_365 = "http://last.fm/user/" . $lfm_usr . "/library/artists?date_preset=LAST_365_DAYS";
	# $lfm_url_all = "http://last.fm/user/" . $lfm_usr . "/library/artists?date_preset=";
}

# configure limits
$results_limit = $lfm_limit + 3;
$results_limit_30 = $lfm_limit + 1;
$results_limit_90 = $lfm_limit + 1;
$results_limit_365 = $lfm_limit + 1;
$results_limit_all = $lfm_limit + 1;

# get data
$cnxt = stream_context_create(array('http' => array('header'=>'Connection: close\r\n')));
$lfm_rs = file_get_contents($lfm_urlb,false,$cnxt);

	# debug
	if(isset($debug)){
		echo $lfm_rs;
	}

	echo "<table cellpadding=\"0\" cellspacing=\"0\" id=\"lfm\">\n\n";

	# grab first result, limit to 1
	$destroy_first = explode("</tr>",$lfm_rs,2); # dictate result returns
	$split_first = array_slice($destroy_first,1,1); # limit results > array_slice(value, offset, limit)

	foreach($split_first as $data_first){

		# extract URL (contains all info)
  		$get_url = preg_match("#\<(.+?) data-track-url=\"(.+?)\"(.+?)>#s", $data_first, $got_url);
		$url = $got_url[2];

		# format URL
		$destroyurl = explode("/_/",$url,3); # dictate result returns
		$artist = $destroyurl[0];
		$song = $destroyurl[1];
		$clean_artist = str_replace($hunt,$swap,$artist);
		$clean_song = str_replace($hunt,$swap,$song);

		# get data etc
		$data_url = "http://last.fm$url";
		$data_etc = file_get_contents($data_url,false,$cnxt);

		# grab image from URL
  		$get_cover_img = preg_match("#\<img src=\"(.+?)\"(.+?)alt=\"(.+?)\"(.+?)class=\"cover-art\"\/\>#s", $data_etc, $got_cover);
		$cover = $got_cover[1];
	
		# grab album from URL
		$get_album = preg_match("#\<h3 class=\"metadata-display\"\>\<a href=\"(.+?)\">(.+?)\<\/a\>\<\/h3\>#s", $data_etc, $got_album); 
		$album = $got_album[2];
		$album_raw = str_replace(" ","+",$album);
		$album_url = "http://last.fm$artist/$album_raw";		

		# get timestamp
  		$get_timestamp = preg_match("#\<td class=\"chartlist-timestamp\"\>(.+?)\<span (.+?)=\"(.+?)\"\>(.+?)\<\/span\>(.+?)\<\/td>#s", $data_first, $got_timestamp);
		$date = $got_timestamp[4];
		$clean_date = str_replace($hunt,$swap,$date);
			if($clean_date == "chartlist-now-scrobbling" || $clean_date == "Scrobbling now"){
				$clean_date = "Now Playing...";			
			}

		# print values
		echo "<tr><td style=\"background-image: url('$cover'); background-size: 34px 34px; background-position: 0 7px; background-repeat: no-repeat;\">$clean_artist - <a href=\"http://last.fm$url\">'$clean_song'</a><br /> <i>From <a href=\"$album_url\">'$album'</a></i> <br /><span class=\"when\">$clean_date</span></tr>\n";

	}

	# grab additional results, offset by 1, limit anything
	$destroy_rest = explode("</tr>",$lfm_rs,$results_limit); # dictate result returns
	$split_rest = array_slice($destroy_rest,4,$results_limit); # limit results > array_slice(value, offset, limit)

	foreach($split_rest as $data_rest){

		# extract URL (contains all info)
  		$get_url = preg_match("#\<a(.+?)class=\"(.+?)chartlist-cover-link(.+?)js-link-block-cover-link(.+?)\"(.+?)href=\"(.+?)\"(.+?)\>#s", $data_rest, $got_url);

		$url = $got_url[6];

		# format URL
		$destroyurl = explode("/_/",$url,3); # dictate result returns
		$artist = $destroyurl[0];
		$song = $destroyurl[1];
		$clean_artist = str_replace($hunt,$swap,$artist);
		$clean_song = str_replace($hunt,$swap,$song);

		# get data etc
		$data_url = "http://last.fm$url";
		$data_etc = file_get_contents($data_url,false,$cnxt);

		# grab image from URL
  		$get_cover_img = preg_match("#\<img src=\"(.+?)\"(.+?)alt=\"(.+?)\"(.+?)class=\"cover-art\"\/\>#s", $data_etc, $got_cover);
		$cover = $got_cover[1];

		# grab album from URL
		$get_album = preg_match("#\<h3 class=\"metadata-display\"\>\<a href=\"(.+?)\">(.+?)\<\/a\>\<\/h3\>#s", $data_etc, $got_album); 
		$album = $got_album[2];
		$album_raw = str_replace(" ","+",$album);
		$album_url = "http://last.fm$artist/$album_raw";

		# get timestamp
  		$get_timestamp = preg_match("#\<td class=\"chartlist-timestamp\"\>(.+?)\<span (.+?)=\"(.+?)\"\>(.+?)\<\/span\>(.+?)\<\/td>#s", $data_rest, $got_timestamp);
		$date = $got_timestamp[4];
		$clean_date = str_replace($hunt,$swap,$date);

		# print values
		echo "<tr><td style=\"background-image: url('$cover'); background-size: 34px 34px; background-position: 0 7px; background-repeat: no-repeat;\">$clean_artist - <a href=\"http://last.fm$url\">'$clean_song'</a><br /> <i>From <a href=\"$album_url\">'$album'</a></i> <br /><span class=\"when\">$clean_date</span></tr>\n";
	
	}

	echo "\n</table>\n\n";
?>
