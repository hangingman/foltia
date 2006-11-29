#!/usr/bin/perl
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
#folprep.pl
#
#atから呼ばれて、目的番組がずれていないか確認します
#新しい放映時刻が15分以上先なら再度folprepのキューを入れます
#放映時刻が15分以内なら放映時刻に録画キューを入れます
#
#引数:PID
#
# DCC-JPL Japan/foltia project
#
#
use DBI;
use DBD::Pg;
use Schedule::At;
use Time::Local;


$path = $0;
$path =~ s/folprep.pl$//i;
if ($pwd  ne "./"){
push( @INC, "$path");
}

require "foltialib.pl";

#XMLゲット & DB更新
system("$toolpath/perl/getxml2db.pl");

#引き数がアルか?
$pid = $ARGV[0] ;
if ($pid eq "" ){
	#引き数なし出実行されたら、終了
	print "usage;folprep.pl <PID>\n";
	exit;
}

#PID探し
$pid = $ARGV[0];

#キュー再投入
	&writelog("folprep  $toolpath/perl/addpidatq.pl $pid");
system("$toolpath/perl/addpidatq.pl $pid");

