#!/usr/bin/perl
#usage recwrap.pl ch length(sec) [bitrate(5)] [TID] [NO] [PID] [stationid] [digitalflag] [digitalband] [digitalch] 
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
#
#レコーディングラッパ
#atから呼び出され、tvrecordingを呼び出し録画
#そのあとMPEG4トラコンを呼び出す
#
# DCC-JPL Japan/foltia project
#

use DBI;
use DBD::Pg;
use DBD::SQLite;
use Schedule::At;
use Time::Local;
use Jcode;

$path = $0;
$path =~ s/recwrap.pl$//i;
if ($path ne "./"){
push( @INC, "$path");
}

require "foltialib.pl";
#引き数がアルか?
$recch = $ARGV[0] ;
if ($recch eq "" ){
	#引き数なしで実行されたら、終了
	print "usage recwrap.pl  ch length(sec) [bitrate(5)] [TID] [NO] [PID]\n";
	exit;
}

$recch = $ARGV[0] ;
$reclength = $ARGV[1] ;
$bitrate  = $ARGV[2] ;
$tid  = $ARGV[3] ;
$countno  = $ARGV[4] ;
$pid = $ARGV[5] ;
$stationid = $ARGV[6] ;
$usedigital = $ARGV[7] ;
$digitalstationband = $ARGV[8] ;
$digitalch= $ARGV[9] ;

#DB初期化
$dbh = DBI->connect($DSN,$DBUser,$DBPass) ||die $DBI::error;;


if ($usedigital == 1){
	$extension = ".m2t";#TSの拡張子
}else{
	$extension = ".m2p";#MPEG2の拡張子
}
if ($recch == -2 ){ #ラジオ局
	$extension = ".aac";#MPEG2の拡張子
}

$outputfile = strftime("%Y%m%d-%H%M", localtime(time + 60));
chomp($outputfile);

if ($tid == 0){
		$outputfilename = "0--".$outputfile."-".$recch.$extension;
		$mp4newstylefilename = "-0--".$outputfile."-".$recch;
}else{
	if ($countno == 0){
		$outputfilename = $tid ."--".$outputfile.$extension;
		$mp4newstylefilename = "-" . $tid ."--".$outputfile;
	}else{
		$outputfilename = $tid ."-".$countno."-".$outputfile.$extension;
		$mp4newstylefilename = "-" . $tid ."-".$countno."-".$outputfile;
	}
}

if ($recch == -2 ){ #ラジオ局
# stationIDからradiko識別子を取得
$sth = $dbh->prepare($stmt{'recwrap.8'});
$sth->execute($stationid);
 @stationline= $sth->fetchrow_array;
$radikostationname = $stationline[3];

$oserr = system("$toolpath/perl/digitalradiorecording.pl $radikostationname $reclength $outputfilename");
$oserr = $oserr / 256;
&writelog("recwrap DEBUG radiko rec finished. $oserr");

# aacファイル名をfoltia_subtitlePIDレコードに書き込み
$sth = $dbh->prepare($stmt{'recwrap.1'});
$sth->execute($outputfilename, $pid);
&writelog("recwrap DEBUG UPDATEDB $stmt{'recwrap.1'}");
&changefilestatus($pid,$FILESTATUSTRANSCODEMP4BOX);

# aacファイル名をfoltia_m2pfilesPIDレコードに書き込み
$sth = $dbh->prepare($stmt{'recwrap.2'});
$sth->execute($outputfilename);
&writelog("recwrap DEBUG UPDATEDB $stmt{'recwrap.2'}");


}else{#非ラジオ局なら

if ($usedigital == 1){
#デジタルなら
&writelog("recwrap RECSTART DIGITAL $digitalstationband $digitalch $reclength $stationid 0 $outputfilename $tid $countno friio");
#録画
    $starttime = time();
$oserr = system("$toolpath/perl/digitaltvrecording.pl $digitalstationband $digitalch $reclength $stationid 0 $outputfilename $tid $countno friio");
$oserr = $oserr / 256;

if ($oserr == 1){
	&writelog("recwrap ABORT recfile exist. [$outputfilename] $digitalstationband $digitalch $reclength $stationid 0  $outputfilename $tid $countno");
	exit;
}elsif ($oserr == 2){
	&writelog("recwrap ERR 2:friio busy;retry.");
	&continuousrecordingcheck;#もうすぐ終わる番組をkill
	sleep(2);
	$oserr = system("$toolpath/perl/digitaltvrecording.pl $digitalstationband $digitalch $reclength $stationid N $outputfilename $tid $countno friio");
	$oserr = $oserr / 256;
	if ($oserr == 2){
	&writelog("recwrap ERR 2:friio busy;Giving up digital recording.");
	}
}elsif ($oserr == 3){
&writelog("recwrap ABORT:ERR 3");
exit ;
}
}else{ # NOT $usedigital == 1
#リモコン操作
# $haveirdaunit = 1;リモコンつないでるかどうか確認
if ($haveirdaunit == 1){
# 録画チャンネルが0なら
	if ($recch == 0){
# &つけて非同期でchangestbch.pl呼び出し
	&writelog("recwrap Call Change STB CH :$pid");
	system ("$toolpath/perl/changestbch.pl $pid &");
	}#end if
}#end if

if($recch == -10){
#非受信局なら
	&writelog("recwrap Not recordable channel;exit:PID $pid");
	exit;
	}#end if
# アナログ録画
&writelog("recwrap RECSTART $recch $reclength 0 $outputfilename $bitrate $tid $countno $pid $usedigital $digitalstationband $digitalch");

#録画
#system("$toolpath/perl/tvrecording.pl $recch $reclength 0 $outputfile $bitrate $tid $countno");
    $starttime = time();

$oserr = system("$toolpath/perl/tvrecording.pl $recch $reclength 0 $outputfilename $bitrate $tid $countno");
$oserr = $oserr / 256;
if ($oserr == 1){
	&writelog("recwrap ABORT recfile exist. [$outputfilename] $recch $reclength 0 0 $bitrate $tid $countno $pid");
	exit;
}

}#endif #デジタル優先フラグ

#デバイスビジーで即死してないか検出
$now = time();
	if ($now < $starttime + 100){ #録画プロセス起動してから100秒以内に戻ってきてたら
    $retrycounter = 0;
		while($now < $starttime + 100){
			if($retrycounter >= 5){
				&writelog("recwrap WARNING  Giving up recording.");
				last;
			}
		&writelog("recwrap retry recording $now $starttime");
		#アナログ録画
	$starttime = time();
if($outputfilename =~ /.m2t$/){
	$outputfilename =~ s/.m2t$/.m2p/;
}
$oserr = system("$toolpath/perl/tvrecording.pl $recch $reclength N $outputfilename $bitrate $tid $countno");
	$now = time();
$oserr = $oserr / 256;
			if ($oserr == 1){
				&writelog("recwrap ABORT recfile exist. in resume process.[$outputfilename] $recch $reclength 0 0 $bitrate $tid $countno $pid");
				exit;
			}# if
		$retrycounter++;
		}# while
	} # if 

	&writelog("recwrap RECEND [$outputfilename] $recch $reclength 0 0 $bitrate $tid $countno $pid");


# m2pファイル名をPIDレコードに書き込み
$sth = $dbh->prepare($stmt{'recwrap.1'});
$sth->execute($outputfilename, $pid);
&writelog("recwrap DEBUG UPDATEDB $stmt{'recwrap.1'}");
&changefilestatus($pid,$FILESTATUSRECEND);

# m2pファイル名をPIDレコードに書き込み
$sth = $dbh->prepare($stmt{'recwrap.2'});
$sth->execute($outputfilename);
&writelog("recwrap DEBUG UPDATEDB $stmt{'recwrap.2'}");

# Starlight breaker向けキャプチャ画像作成
if (-e "$toolpath/perl/captureimagemaker.pl"){
	&writelog("recwrap Call captureimagemaker $outputfilename");
&changefilestatus($pid,$FILESTATUSCAPTURE);
	system ("$toolpath/perl/captureimagemaker.pl $outputfilename");
&changefilestatus($pid,$FILESTATUSCAPEND);
}
}#非ラジオ局

# MPEG4 ------------------------------------------------------
#MPEG4トラコン必要かどうか
$sth = $dbh->prepare($stmt{'recwrap.3'});
$sth->execute($tid);
 @psptrcn= $sth->fetchrow_array;
if ($psptrcn[0]  == 1 ){#トラコン番組
	&writelog("recwrap Launch ipodtranscode.pl");
	exec ("$toolpath/perl/ipodtranscode.pl");
	exit;
}#PSPトラコンあり

sub continuousrecordingcheck(){
    my $now = time() + 60 * 2;
&writelog("recwrap DEBUG continuousrecordingcheck() now $now");
my @processes =`ps ax | grep -e recpt1 -e recfriio`; #foltiaBBS もうすぐ終了する番組のプロセスをkill 投稿日 2010年08月05日03時19分33秒 投稿者 Nis 

my $psline = "";
my @processline = "";
my $pid = "";
my @pid;
my $sth;
foreach (@processes){
	if (/recpt1|friiodetect/) {
		if (/^.[0-9]*\s/){
			push(@pid, $&);
		}#if
	}#if
}#foreach

if (@pid > 0){
my @filenameparts;
my $tid = "";
my $startdate = "";
my $starttime = "";
my $startdatetime = "";
my @recfile;
my $endtime = "";
my $endtimeepoch = "";
foreach $pid (@pid){
#print "DEBUG  PID $pid\n";
&writelog("recwrap DEBUG continuousrecordingcheck() PID $pid");

	my @lsofoutput = `/usr/sbin/lsof -p $pid`;
	my $filename = "";
	#print "recfolferpath $recfolderpath\n";
	foreach (@lsofoutput){
		if (/m2t/){
		@processline = split(/\s+/,$_);
		$filename = $processline[8];
		$filename =~ s/$recfolderpath\///;
		&writelog("recwrap DEBUG continuousrecordingcheck()  FILENAME $filename");
		# 1520-9-20081201-0230.m2t
		@filenameparts = split(/-/,$filename);
		$tid = $filenameparts[0];
		$startdate = $filenameparts[2];
		$starttime = $filenameparts[3];
		@filenameparts = split(/\./,$starttime);
		$startdatetime = $startdate.$filenameparts[0];
		#DBから録画中番組のデータ探す
		    &writelog("recwrap DEBUG continuousrecordingcheck() $stmt{'recwrap.7'}");
		    $sth = $dbh->prepare($stmt{'recwrap.7'});
	&writelog("recwrap DEBUG continuousrecordingcheck() prepare");
		    $sth->execute($tid, $startdatetime);
	&writelog("recwrap DEBUG continuousrecordingcheck() execute");
	@recfile = $sth->fetchrow_array;
	&writelog("recwrap DEBUG continuousrecordingcheck() @recfile  $recfile[0] $recfile[1] $recfile[2] $recfile[3] $recfile[4] $recfile[5] $recfile[6] $recfile[7] $recfile[8] $recfile[9] ");
	#終了時刻
	$endtime = $recfile[4];
	$endtimeepoch = &foldate2epoch($endtime);
	&writelog("recwrap DEBUG continuousrecordingcheck() $recfile[0] $recfile[1] $recfile[2] $recfile[3] $recfile[4] $recfile[5] endtimeepoch $endtimeepoch");
	if ($endtimeepoch < $now){#まもなく終わる番組なら
		#kill
		system("kill $pid");
		&writelog("recwrap recording process killed $pid/$endtimeepoch/$now");
	}
		}#endif m2t
	}#foreach lsofoutput
}#foreach
}else{
#print "DEBUG fecfriio NO PID\n";
&writelog("recwrap No recording process killed.");
}
}#endsub



