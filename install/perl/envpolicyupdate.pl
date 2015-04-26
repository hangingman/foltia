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
# envpolicyupdate.pl
#
# 環境ポリシー利用時に.htpasswdを再編する。
#
use DBI;
use DBD::SQLite;

$path = $0;
$path =~ s/envpolicyupdate.pl$//i;
if ($path ne "./"){
push( @INC, "$path");
}

require "foltialib.pl";

# 環境ポリシーを使っているかPHPコンフィグファイル解析
$returnparam = getphpstyleconfig("useenvironmentpolicy");
eval "$returnparam\n";

if ($useenvironmentpolicy == 1){
$returnparam = getphpstyleconfig("environmentpolicytoken");
eval "$returnparam\n";

    $dbh = DBI->connect($DSN,$DBUser,$DBPass) ||die $DBI::error;;

    $envph = $dbh->prepare($stmt{'envpolicyupdate.1'});
	$envph->execute();

#なければつくる
unless (-e "$toolpath/.htpasswd"){
	$oserr = `touch $toolpath/.htpasswd`;
}else{
	$oserr = `mv $toolpath/.htpasswd $toolpath/htpasswd_foltia_old`;
	$oserr = `touch $toolpath/.htpasswd`;
}

while (@ref = $envph->fetchrow_array ){

if ($ref[0] == 0){
#ユーザクラス
#0:特権管理者
#1:管理者:予約削除、ファイル削除が出来る
#2:利用者:EPG追加、予約追加が出来る
#3:ビュアー:ファイルダウンロードが出来る
#4:ゲスト:インターフェイスが見れる

	$htpasswd = "$ref[2]";
}else{
	$htpasswd = "$ref[2]"."$environmentpolicytoken";
}

$oserr = `htpasswd -b $toolpath/.htpasswd $ref[1] $htpasswd`;


}#end while
&writelog("envpolicyupdate htpasswd updated.");

}#endif 
