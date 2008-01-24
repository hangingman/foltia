#!/usr/bin/perl
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
#
# envpolicyupdate.pl
#
# 環境ポリシー利用時に.htpasswdを再編する。
#
#
# DCC-JPL Japan/foltia project
#
#

use DBI;
use DBD::Pg;

$path = $0;
$path =~ s/envpolicyupdate.pl$//i;
if ($pwd  ne "./"){
push( @INC, "$path");
}

require "foltialib.pl";

# 環境ポリシーを使っているかPHPコンフィグファイル解析
$returnparam = getphpstyleconfig("useenvironmentpolicy");
eval "$returnparam\n";

if ($useenvironmentpolicy == 1){
$returnparam = getphpstyleconfig("environmentpolicytoken");
eval "$returnparam\n";

	my $data_source = sprintf("dbi:%s:dbname=%s;host=%s;port=%d",
		$DBDriv,$DBName,$DBHost,$DBPort);
	 $dbh = DBI->connect($data_source,$DBUser,$DBPass) ||die $DBI::error;;

$DBQuery =  "SELECT userclass,name,passwd1 FROM foltia_envpolicy ";

 $envph = $dbh->prepare($DBQuery);
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