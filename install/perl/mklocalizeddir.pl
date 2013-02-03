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
# mklocalizeddir.pl
# usage ;mklocalizeddir.pl [TID]
# Mac OS X Localizedフォーマットに準拠した構造の録画ディレクトリを作る。
# 参考:[Mac OS X 10.2のローカライズ機能] http://msyk.net/macos/jaguar-localize/
#
use Jcode;
use DBI;
use DBD::SQLite;

$path = $0;
$path =~ s/mklocalizeddir.pl$//i;
if ($path ne "./") {
  push( @INC, "$path");
}
require "foltialib.pl";

#引き数がアルか?
$tid =  $ARGV[0] ;
if ($tid eq "" ) {
  #引き数なし出実行されたら、終了
  print "usage mklocalizeddir.pl [TID]\n";
  exit;
}

#そのディレクトリがなければ
if (-e "$recfolderpath/$tid.localized") {

} else {
  #.localized用文字列取得

  #接続
  $dbh = DBI->connect($DSN,$DBUser,$DBPass) ||die $DBI::error;;

  #検索
  $sth = $dbh->prepare($stmt{'mklocalizeddir.1'});
  $sth->execute($tid);
  @subticount= $sth->fetchrow_array;
  $title = $subticount[0] ;
  $titleeuc = $title ;
  Jcode::convert(\$title , 'utf8', 'euc', "z");

  mkdir ("$recfolderpath/$tid.localized",0755);
  mkdir ("$recfolderpath/$tid.localized/.localized",0755);
  mkdir ("$recfolderpath/$tid.localized/mp4",0755);
  mkdir ("$recfolderpath/$tid.localized/m2p",0755);
  open (JASTRING,">$recfolderpath/$tid.localized/.localized/ja.strings")  || die "Cannot write ja.strings.\n";
  print JASTRING "\"$tid\"=\"$title\";\n";
  close(JASTRING);

  &writelog("mklocalizeddir $tid $titleeuc");

}#unless 引き数がアルか?

