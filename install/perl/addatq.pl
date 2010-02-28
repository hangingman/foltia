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
use DBD::SQLite;
use Schedule::At;
use Time::Local;

$path = $0;
$path =~ s/addatq.pl$//i;
if ($path ne "./"){
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
$dbh = DBI->connect($DSN,$DBUser,$DBPass) ||die $DBI::error;;

if ($station == 0){
    $sth = $dbh->prepare($stmt{'addatq.1'});
    $sth->execute($tid);
}else{
    $sth = $dbh->prepare($stmt{'addatq.2'});
    $sth->execute($tid, $station);
}
 @titlecount = $sth->fetchrow_array;
#件数数える

#2以上だったら
if ($titlecount[0]  >= 2){
    #全局録りが含まれているか調べる
    $kth = $dbh->prepare($stmt{'addatq.3'});
    $kth->execute($tid);
 	@reservecounts = $kth->fetchrow_array;

	if($reservecounts[0] >= 1 ){#含まれていたら
		if($tid == 0){
		#今回の引き数がSID 0だったら
	    #全局録りだけ予約
#		&writelog("addatq  DEBUG; ALL STATION RESERVE. TID=$tid SID=$station $titlecount[0] match:$stmt{'addatq.3'}");
		&addcue;
		}else{
		#ほかの全局録画addatqが予約入れてくれるからなにもしない
#		&writelog("addatq  DEBUG; SKIP OPERSTION. TID=$tid SID=$station $titlecount[0] match:$stmt{'addatq.3'}");
		exit;
  		}#end if ふくまれていたら
	}#endif 2つ以上	
}elsif($titlecount[0]  == 1){
		&addcue;
}else{
    &writelog("addatq  error; reserve impossible . TID=$tid SID=$station $titlecount[0] match:$stmt{'addatq.3'}");
}

#旧処理
# if ($titlecount[0]  == 1 ){
# 	& addcue;
# }else{
#&writelog("addatq  error record TID=$tid SID=$station $titlecount[0] match:$stmt{'addatq.3'}");
#}

sub addcue{

if ($station == 0){
	$sth = $dbh->prepare($stmt{'addatq.addcue.1'});
	$sth->execute($tid);
}else{
	$sth = $dbh->prepare($stmt{'addatq.addcue.2'});
	$sth->execute($tid, $station);
}
 @titlecount= $sth->fetchrow_array;
$bitrate = $titlecount[2];#ビットレート取得

#PID抽出
    $now = &epoch2foldate(time());
    $twodaysafter = &epoch2foldate(time() + (60 * 60 * 24 * 2));
#キュー入れは直近2日後まで
if ($station == 0 ){
	$sth = $dbh->prepare($stmt{'addatq.addcue.3'});
	$sth->execute($tid, $now, $twodaysafter);
}else{
#stationIDからrecch
	$stationh = $dbh->prepare($stmt{'addatq.addcue.4'});
	$stationh->execute($station);
@stationl =  $stationh->fetchrow_array;
$recch = $stationl[1];

	$sth = $dbh->prepare($stmt{'addatq.addcue.5'});
	$sth->execute($tid, $station, $now, $twodaysafter);
    }
 
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
	    $stationh = $dbh->prepare($stmt{'addatq.addcue.6'});
	    $stationh->execute($stationid);
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
