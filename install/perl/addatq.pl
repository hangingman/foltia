#!/usr/bin/perl
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
#addatq.pl
#
#TIDと局IDを受け取りatqに入れる
# addatq.pl <TID> <StationID> [DELETE]
# DELETEフラグがつくと削除のみ行う
#
# DCC-JPL Japan/foltia project
#
#

use DBI;
use DBD::Pg;
use Schedule::At;
use Time::Local;

$path = $0;
$path =~ s/addatq.pl$//i;
if ($pwd  ne "./"){
push( @INC, "$path");
}

require "foltialib.pl";

#引き数がアルか?
$tid = $ARGV[0] ;
$station = $ARGV[1];

if (($tid eq "" )|| ($station eq "")){
	#引き数なし出実行されたら、終了
	print "usage;addatq.pl <TID> <StationID> [DELETE]\n";
	exit;
}

#DB検索(TIDとStationIDからPIDへ)
 $data_source = sprintf("dbi:%s:dbname=%s;host=%s;port=%d",
		$DBDriv,$DBName,$DBHost,$DBPort);
	 $dbh = DBI->connect($data_source,$DBUser,$DBPass) ||die $DBI::error;;

if ($station == 0){
	$DBQuery =  "SELECT count(*) FROM  foltia_tvrecord WHERE tid = '$tid'  ";
}else{
	$DBQuery =  "SELECT count(*) FROM  foltia_tvrecord WHERE tid = '$tid' AND stationid  = '$station' ";
}
	 $sth = $dbh->prepare($DBQuery);
	$sth->execute();
 @titlecount = $sth->fetchrow_array;
#件数数える

#2以上だったら
if ($titlecount[0]  >= 2){
	#全曲取りが含まれているか調べる
	$DBQuery =  "SELECT count(*) FROM  foltia_tvrecord WHERE tid = '$tid'  AND  stationid  ='0' ";
	$kth = $dbh->prepare($DBQuery);
	$kth->execute();
 	@reservecounts = $kth->fetchrow_array;

	if($reservecounts[0] >= 1 ){#含まれていたら
		if($tid == 0){
		#今回の引き数がSID 0だったら
		#全局取りだけ予約
#		&writelog("addatq  DEBUG; ALL STATION RESERVE. TID=$tid SID=$station $titlecount[0] match:$DBQuery");
		&addcue;
		}else{
		#ほかの全局録画addatqが予約入れてくれるからなにもしない
#		&writelog("addatq  DEBUG; SKIP OPERSTION. TID=$tid SID=$station $titlecount[0] match:$DBQuery");
		exit;
  		}#end if ふくまれていたら
	}#endif 2つ以上	
}elsif($titlecount[0]  == 1){
		&addcue;
}else{
&writelog("addatq  error; reserve impossible . TID=$tid SID=$station $titlecount[0] match:$DBQuery");
}

#旧処理
# if ($titlecount[0]  == 1 ){
# 	& addcue;
# }else{
#&writelog("addatq  error record TID=$tid SID=$station $titlecount[0] match:$DBQuery");
#}

sub addcue{

if ($station == 0){
	$DBQuery =  "SELECT * FROM  foltia_tvrecord WHERE tid = '$tid'  ";
}else{
	$DBQuery =  "SELECT * FROM  foltia_tvrecord WHERE tid = '$tid' AND stationid  = '$station' ";
}
 $sth = $dbh->prepare($DBQuery);
$sth->execute();
 @titlecount= $sth->fetchrow_array;
$bitrate = $titlecount[2];#ビットレート取得

#PID抽出
$now = &epoch2foldate(`date +%s`);
$twodaysafter = &epoch2foldate(`date +%s` + (60 * 60 * 24 * 2));
#キュー入れは直近2日後まで
if ($station == 0 ){
	$DBQuery =  "
SELECT * from foltia_subtitle WHERE tid = '$tid'  AND startdatetime >  '$now'  AND startdatetime < '$twodaysafter' ";
}else{
	$DBQuery =  "
SELECT * from foltia_subtitle WHERE tid = '$tid' AND stationid  = '$station'  AND startdatetime >  '$now'  AND startdatetime < '$twodaysafter' ";
#stationIDからrecch
$getrecchquery="SELECT stationid , stationrecch  FROM foltia_station where stationid  = '$station' ";
 $stationh = $dbh->prepare($getrecchquery);
	$stationh->execute();
@stationl =  $stationh->fetchrow_array;
$recch = $stationl[1];
}

 $sth = $dbh->prepare($DBQuery);
	$sth->execute();
 
while (($pid ,
$tid ,
$stationid ,
$countno,
$subtitle,
$startdatetime,
$enddatetime,
$startoffset ,
$lengthmin,
$atid ) = $sth->fetchrow_array()) {

if ($station == 0 ){
#stationIDからrecch
$getrecchquery="SELECT stationid , stationrecch  FROM foltia_station where stationid  = '$stationid' ";
 $stationh = $dbh->prepare($getrecchquery);
	$stationh->execute();
@stationl =  $stationh->fetchrow_array;
$recch = $stationl[1];
}
#キュー入れ
	#プロセス起動時刻は番組開始時刻の-1分
$atdateparam = &calcatqparam(300);
$reclength = $lengthmin * 60;
#&writelog("TIME $atdateparam COMMAND $toolpath/perl/tvrecording.pl $recch $reclength 0 0 $bitrate $tid $countno");
#キュー削除
 Schedule::At::remove ( TAG => "$pid"."_X");
	&writelog("addatq remove $pid");
if ( $ARGV[2] eq "DELETE"){
	&writelog("addatq remove  only $pid");
}else{
	Schedule::At::add (TIME => "$atdateparam", COMMAND => "$toolpath/perl/folprep.pl $pid" , TAG => "$pid"."_X");
	&writelog("addatq TIME $atdateparam   COMMAND $toolpath/perl/folprep.pl $pid ");
}
##processcheckdate 
#&writelog("addatq TIME $atdateparam COMMAND $toolpath/perl/schedulecheck.pl");
}#while



}#endsub
