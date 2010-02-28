#!/usr/bin/perl
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
#schedulecheck.pl
#
#DBの予約から定期的に予約キューを作り出します
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
$path =~ s/schedulecheck.pl$//i;
if ($path ne "./"){
push( @INC, "$path");
}

require "foltialib.pl";

#XMLゲット&更新
system("$toolpath/perl/getxml2db.pl");

#予約番組探し
$now = &epoch2foldate(time());
$now = &epoch2foldate($now);
$checkrangetime = $now   + 15*60;#15分後まで
$checkrangetime =  &epoch2foldate($checkrangetime);

$dbh = DBI->connect($DSN,$DBUser,$DBPass) ||die $DBI::error;;

$sth = $dbh->prepare($stmt{'schedulecheck.1'});
	$sth->execute();
 @titlecount= $sth->fetchrow_array;

 if ($titlecount[0]  == 0 ){
exit;
}else{
    $sth = $dbh->prepare($stmt{'schedulecheck.2'});
	$sth->execute();
while (($tid,$stationid  ) = $sth->fetchrow_array()) {
#キュー再投入
system ("$toolpath/perl/addatq.pl $tid $stationid  ");
&writelog("schedulecheck  $toolpath/perl/addatq.pl $tid $stationid ");

}#while


}
