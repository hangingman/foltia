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
# updatem2pfiletable.pl
#
# m2pfileのリストをテーブルに入れる.その後全deleteして更新する
# おもにメンテナンス用.または一日一回ぐらいcronで実行させてもいいかも
#
use DBI;
use DBD::SQLite;

$path = $0;
$path =~ s/updatem2pfiletable.pl$//i;
if ($path ne "./") {
  push( @INC, "$path");
}

require "foltialib.pl";
$dbh = DBI->connect($DSN,$DBUser,$DBPass) ||die $DBI::error;;

$dbh->{AutoCommit} = 0;
#　ひとまず消す
$sth = $dbh->prepare($stmt{'updatem2pfiletable.1'});
$sth->execute();
while ($file = glob("$recfolderpath/*.m2?")) {
  $file =~ s/$recfolderpath\///;
  $sth = $dbh->prepare($stmt{'updatem2pfiletable.2'});
  $sth->execute($file);
  # print "$file\n";
} #while
while ($file = glob("$recfolderpath/*.aac")) {
  $file =~ s/$recfolderpath\///;
  $sth = $dbh->prepare($stmt{'updatem2pfiletable.2'});
  $sth->execute($file);
  # print "$file\n";
} #while

$oserr = $dbh->commit;

# foltia_mp4files
@mp4filelist = `find ${recfolderpath}/ | grep MP4`; #by foltia dev ticket #5 http://www.dcc-jpl.com/foltia/ticket/5

#　ひとまず消す
$sth = $dbh->prepare($stmt{'updatem2pfiletable.3'});
$sth->execute();

foreach (@mp4filelist) {
  chomp();
  s/$recfolderpath\///;
  @fileline = split (/\//);
  $filetid = $fileline[0];
  $filetid =~ s/[^0-9]//g;
  if (($filetid ne "" )&& ($fileline[2] ne "" )) {
    $sth = $dbh->prepare($stmt{'updatem2pfiletable.4'});
    $oserr = $sth->execute($filetid, $fileline[2]);
    #print "$filetid;$fileline[2];$query\n"
    # http://www.atmarkit.co.jp/fnetwork/rensai/sql03/sql1.html
  } #end if
}   # end foreach
$oserr = $dbh->commit;
