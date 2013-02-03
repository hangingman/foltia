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

# deletemovie.pl
#
# ファイル名を受け取り削除処理をする.とりあえずは./mita/へ移動
#
use DBI;
use DBD::SQLite;

$path = $0;
$path =~ s/deletemovie.pl$//i;
if ($path ne "./") {
  push( @INC, "$path");
}

require "foltialib.pl";

#引き数がアルか?
$fname = $ARGV[0] ;
if ($fname eq "" ) {
  #引き数なし出実行されたら、終了
  print "usage;deletemovie.pl <FILENAME>\n";
  exit;
}

#ファイル名正当性チェック
if ($fname =~ /.m2p$|.m2t$|.MP4$|.aac$/) {

} else {
  #	print "deletemovie invalid filetype.\n";
  &writelog("deletemovie invalid filetype:$fname.");
  exit (1);
}

#DB初期化
$dbh = DBI->connect($DSN,$DBUser,$DBPass) ||die $DBI::error;;

#ファイル存在チェック
my $tid = &mp4filename2tid($fname);
my $mp4dirname = &makemp4dir($tid);
if (-e "$recfolderpath/$fname") {
  $filemovepath = $recfolderpath;
} elsif (-e "$mp4dirname/$fname") {
  $filemovepath = $mp4dirname;
} else {
  #	print "deletemovie file not found.$recfolderpath/$fname\n";
  &writelog("deletemovie file not found:$fname.");
  exit (1);
}

#既読削除処理 
if ($rapidfiledelete  > 0) {	#./mita/へ移動
  system ("mv $filemovepath/$fname $recfolderpath/mita/");
  &writelog("deletemovie mv filemovepath/$fname $recfolderpath/mita/.");
} else {			#即時削除
  system ("rm $filemovepath/$fname ");
  &writelog("deletemovie rm $filemovepath/$fname ");
}



