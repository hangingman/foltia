#!/usr/bin/perl
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
#usage ;mklocalizeddir.pl [TID]
# Mac OS X Localizedフォーマットに準拠した構造の録画ディレクトリを作る。
# 参考:[Mac OS X 10.2のローカライズ機能] http://msyk.net/macos/jaguar-localize/
#
# DCC-JPL Japan/foltia project
#
#


use Jcode;
use DBI;
use DBD::Pg;
use DBD::SQLite;

$path = $0;
$path =~ s/mklocalizeddir.pl$//i;
if ($path ne "./"){
push( @INC, "$path");
}
require "foltialib.pl";

#引き数がアルか?
$tid =  $ARGV[0] ;
if ($tid eq "" ){
	#引き数なし出実行されたら、終了
	print "usage mklocalizeddir.pl [TID]\n";
	exit;
}


#そのディレクトリがなければ
if (-e "$recfolderpath/$tid.localized"){

}else{


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

