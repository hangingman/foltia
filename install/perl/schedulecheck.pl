#!/usr/bin/perl
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
# Copyright (C) 2013 DCC-JPL Japan/foltia project

# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

#
# schedulecheck.pl
#
# DBの予約から定期的に予約キューを作り出します
#
use DBI;
use DBD::SQLite;
use Schedule::At;
use Time::Local;

$path = $0;
$path =~ s/schedulecheck.pl$//i;
if ($path ne "./") {
  push( @INC, "$path");
}

require "foltialib.pl";

#XMLゲット&更新
system("$toolpath/perl/getxml2db.pl");

#予約番組探し
$now = &epoch2foldate(time());
$now = &epoch2foldate($now);
$checkrangetime = $now   + 15*60; #15分後まで
$checkrangetime =  &epoch2foldate($checkrangetime);

$dbh = DBI->connect($DSN,$DBUser,$DBPass) ||die $DBI::error;;

$sth = $dbh->prepare($stmt{'schedulecheck.1'});
$sth->execute();
@titlecount= $sth->fetchrow_array;

if ($titlecount[0]  == 0 ) {
  exit;
} else {
  $sth = $dbh->prepare($stmt{'schedulecheck.2'});
  $sth->execute();
  while (($tid,$stationid  ) = $sth->fetchrow_array()) {
    #キュー再投入
    system ("$toolpath/perl/addatq.pl $tid $stationid  ");
    &writelog("schedulecheck  $toolpath/perl/addatq.pl $tid $stationid ");

  }				#while

  #EPG更新
  system("$toolpath/perl/epgimport.pl");
}
