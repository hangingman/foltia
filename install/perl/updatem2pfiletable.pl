#!/usr/bin/perl
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
# usage :updatem2pfiletable.pl
#
# m2pfileのリストをテーブルに入れる
# 全deleteして更新する
# おもにメンテナンス用
# または一日一回ぐらいcronで実行させてもいいかも
#
# DCC-JPL Japan/foltia project
#

use DBI;
use DBD::Pg;

$path = $0;
$path =~ s/updatem2pfiletable.pl$//i;
if ($pwd  ne "./"){
push( @INC, "$path");
}

require "foltialib.pl";
	my $data_source = sprintf("dbi:%s:dbname=%s;host=%s;port=%d",
		$DBDriv,$DBName,$DBHost,$DBPort);
	 $dbh = DBI->connect($data_source,$DBUser,$DBPass) ||die $DBI::error;;

$dbh->{AutoCommit} = 0;
#　ひとまず消す
$query =  "DELETE  FROM  foltia_m2pfiles  ";
	 $sth = $dbh->prepare($query);
	$sth->execute();

while ($file = glob("$recfolderpath/*.m2p")) {
$file =~ s/$recfolderpath\///;
$query =  "insert into  foltia_m2pfiles values ('$file')";
$oserr = $dbh->do($query);
# print "$file\n";
}#while
$oserr = $dbh->commit;

# foltia_mp4files
#　ひとまず消す
$query =  "DELETE  FROM  foltia_mp4files  ";
	 $sth = $dbh->prepare($query);
	$sth->execute();

@mp4filelist = `find ${recfolderpath}/ | grep MP4`;#by foltia dev ticket #5 http://www.dcc-jpl.com/foltia/ticket/5

foreach (@mp4filelist) {
chomp();
s/$recfolderpath\///;
@fileline = split (/\//);
$filetid = $fileline[0];
$filetid =~ s/[^0-9]//g;

$query =  "insert into  foltia_mp4files values ('$filetid','$fileline[2]')";
$oserr = $dbh->do($query);
#print "$filetid;$fileline[2];$query\n"
# http://www.atmarkit.co.jp/fnetwork/rensai/sql03/sql1.html

}
$oserr = $dbh->commit;

