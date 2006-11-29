#!/usr/bin/perl
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
#addpidatq.pl
#
#PID受け取りatqに入れる。folprep.plからキュー再入力のために使われる
#
# DCC-JPL Japan/foltia project
#
#

use DBI;
use DBD::Pg;
use Schedule::At;
use Time::Local;

$path = $0;
$path =~ s/addpidatq.pl$//i;
if ($pwd  ne "./"){
push( @INC, "$path");
}

require "foltialib.pl";


#引き数がアルか?
$pid = $ARGV[0] ;
if ($pid eq "" ){
	#引き数なし出実行されたら、終了
	print "usage;addpidatq.pl <PID>\n";
	exit;
}


#DB検索(PID)
	my $data_source = sprintf("dbi:%s:dbname=%s;host=%s;port=%d",
		$DBDriv,$DBName,$DBHost,$DBPort);
	 $dbh = DBI->connect($data_source,$DBUser,$DBPass) ||die $DBI::error;;

$DBQuery =  "SELECT count(*) FROM  foltia_subtitle WHERE pid = '$pid' ";
	 $sth = $dbh->prepare($DBQuery);
	$sth->execute();
 @titlecount= $sth->fetchrow_array;
 
 if ($titlecount[0]  == 1 ){

$DBQuery =  "SELECT bitrate FROM  foltia_tvrecord , foltia_subtitle  WHERE foltia_tvrecord.tid = foltia_subtitle.tid AND pid='$pid' ";
 $sth = $dbh->prepare($DBQuery);
$sth->execute();
 @titlecount= $sth->fetchrow_array;
$bitrate = $titlecount[0];#ビットレート取得

#PID抽出
$now = &epoch2foldate(`date +%s`);

$DBQuery =  "SELECT stationrecch FROM foltia_station,foltia_subtitle WHERE foltia_subtitle.pid = '$pid'  AND  foltia_subtitle.stationid =  foltia_station.stationid ";


#stationIDからrecch
 $stationh = $dbh->prepare($DBQuery);
	$stationh->execute();
@stationl =  $stationh->fetchrow_array;
$recch = $stationl[0];

$DBQuery =  "SELECT  * FROM  foltia_subtitle WHERE pid='$pid' ";
 $sth = $dbh->prepare($DBQuery);
$sth->execute();
($pid ,
$tid ,
$stationid ,
$countno,
$subtitle,
$startdatetime,
$enddatetime,
$startoffset ,
$lengthmin,
$atid ) = $sth->fetchrow_array();
# print "$pid ,$tid ,$stationid ,$countno,$subtitle,$startdatetime,$enddatetime,$startoffset ,$lengthmin,$atid \n";

if($now< $startdatetime){#放送が未来の日付なら
#もし新開始時刻が15分移譲先なら再キュー
$startafter = &calclength($now,$startdatetime);
&writelog("addpidatq DEBUG \$startafter $startafter \$now $now \$startdatetime $startdatetime");

if ($startafter > 14 ){

#キュー削除
 Schedule::At::remove ( TAG => "$pid"."_X");
	&writelog("addpidatq remove que $pid");


#キュー入れ
	#プロセス起動時刻は番組開始時刻の-5分
$atdateparam = &calcatqparam(300);
	Schedule::At::add (TIME => "$atdateparam", COMMAND => "$toolpath/perl/folprep.pl $pid" , TAG => "$pid"."_X");
	&writelog("addpidatq TIME $atdateparam   COMMAND $toolpath/perl/folprep.pl $pid ");
}else{
$atdateparam = &calcatqparam(60);
$reclength = $lengthmin * 60;

#キュー削除
 Schedule::At::remove ( TAG => "$pid"."_R");
	&writelog("addpidatq remove que $pid");

if ($countno eq ""){
	$countno = "0";
}

Schedule::At::add (TIME => "$atdateparam", COMMAND => "$toolpath/perl/recwrap.pl $recch $reclength  $bitrate $tid $countno $pid" , TAG => "$pid"."_R");
	&writelog("addpidatq TIME $atdateparam   COMMAND $toolpath/perl/recwrap.pl $recch $reclength  $bitrate $tid $countno $pid");

}#end #もし新開始時刻が15分移譲先なら再キュー

}else{
&writelog("addpidatq drop:expire  $pid  $startafter  $now  $startdatetime");
}#放送が未来の日付なら

}else{
print "error record TID=$tid SID=$station $titlecount[0] match:$DBQuery\n";
&writelog("addpidatq error record TID=$tid SID=$station $titlecount[0] match:$DBQuery");

}#end if ($titlecount[0]  == 1 ){


