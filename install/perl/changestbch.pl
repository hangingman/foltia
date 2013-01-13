#!/usr/bin/perl
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
#changestbch.pl
#
# リモコンユニットを操作して外部チューナの信号を切り替える。
#対応ユニット
# Tira-2.1: Remote Control Receiver/Transmitter
#http://www.home-electro.com/tira2.php
#
#usage :changestbch.pl  [PID]
#引数
#[PID]番組プログラムID
#
# チャンネル切り替えの流れ
# changestbch.pl :局から送出信号を調べて transfer.pl にチャンネル変更引き数を渡す。
# ↓
# transfer.pl 指定ファイルを送出 <http://www.geocities.jp/coffee_style/Tira-2-0.html>
#
#
# DCC-JPL Japan/foltia project
#

use DBI;

use DBD::SQLite;

$path = $0;
$path =~ s/changestbch.pl$//i;
if ($path ne "./"){
push( @INC, "$path");
}
require 'foltialib.pl';


#	&writelog("changestbch DEBUG START");


#引き数がアルか?
$pid = $ARGV[0] ;
if ($pid eq "" ){
	#引き数なし出実行されたら、終了
	print "usage :changestbch.pl  [PID]\n";
	&writelog("changestbch ERR PID null");
	exit;
}

# $haveirdaunit = 1;リモコンつないでるかどうか
if ($haveirdaunit == 1){
#デバイス見えるかどうか
if (-e "/dev/ttyUSB0"){

# pidから局(送出コマンド)調べる
#DB初期化
	$dbh = DBI->connect($DSN,$DBUser,$DBPass) ||die $DBI::error;;

	$sth = $dbh->prepare($stmt{'changestbch.1'});
	$sth->execute($pid);
 @chstatus = $sth->fetchrow_array;
 	$tunertype = $chstatus[0];
	$tunercmd =  $chstatus[1];
	$recch =  $chstatus[2];
	$stationid =  $chstatus[3];	
$cmdjoined = "$tunertype"."$tunercmd";

&writelog("changestbch DEBUG  $cmdjoined :$recch:$stationid");

$length = length($cmdjoined);
$sendcmdfile = "";
for ($i=0 ; $i < $length ; $i++ ){
	$cmdtxt = substr($cmdjoined,$i,1);
#	print "$cmdtxt\n";
	$sendcmdfile .= " $toolpath/perl/irda/$cmdtxt".".dat ";
}#for

#if (-e "$toolpath/perl/irda/$sendcmdfile"){
	system("$toolpath/perl/irda/transfer.pl $sendcmdfile");
&writelog("changestbch DEBUG  $toolpath/perl/irda/transfer.pl $toolpath/perl/irda/$sendcmdfile");
#}else{
#	&writelog("changestbch ERR cmd file not found:$toolpath/perl/irda/$sendcmdfile");
#}#if -e



#BS-hi b x103 || b 3
#キッズステーション c x264 || c 2 

#コマンドから実行するコマンド組み立て
}else{
#デバイス見えない
		&writelog("changestbch ERR Tira2 Not found.");
}#end if (-e "/dev/ttyUSB0")

}#endif if ($haveirdaunit == 1


