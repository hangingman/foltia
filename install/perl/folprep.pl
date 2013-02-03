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
# folprep.pl
#
# atから呼ばれて、目的番組がずれていないか確認します
# 新しい放映時刻が15分以上先なら再度folprepのキューを入れます
# 放映時刻が15分以内なら放映時刻に録画キューを入れます
#
# 引数:PID
#
use DBI;
use Schedule::At;
use Time::Local;

$path = $0;
$path =~ s/folprep.pl$//i;
if ($path ne "./") {
  push( @INC, "$path");
}

require "foltialib.pl";


#PID探し
my $pid = $ARGV[0];

#引き数がアルか?
if ($pid eq "" ) {
  #引き数なし出実行されたら、終了
  print "usage;folprep.pl <PID>\n";
  exit;
}

my $stationid = "";
if ($pid <= 0) {		#EPG録画/キーワード録画
  #EPG更新 & DB更新
  $dbh = DBI->connect($DSN,$DBUser,$DBPass) ||die $DBI::error;;
  $stationid = &pid2sid($pid);
  &writelog("folprep DEBUG epgimport.pl $stationid");
  system("$toolpath/perl/epgimport.pl $stationid");
} else {			#しょぼかる録画
				#XMLゲット & DB更新
  &writelog("folprep DEBUG getxml2db.pl");
  system("$toolpath/perl/getxml2db.pl");
}

#キュー再投入
&writelog("folprep  $toolpath/perl/addpidatq.pl $pid");
system("$toolpath/perl/addpidatq.pl $pid");

