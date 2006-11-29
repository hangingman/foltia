#!/usr/bin/perl
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
#
#deletemovie.pl
#
#ファイル名を受け取り、削除処理をする
#とりあえずは./mita/へ移動
#
#
# DCC-JPL Japan/foltia project
#
#

$path = $0;
$path =~ s/deletemovie.pl$//i;
if ($pwd  ne "./"){
push( @INC, "$path");
}

require "foltialib.pl";

#引き数がアルか?
$fname = $ARGV[0] ;
if ($fname eq "" ){
	#引き数なし出実行されたら、終了
	print "usage;deletemovie.pl <FILENAME>\n";
	exit;
}

#ファイル名正当性チェック
if ($fname =~ /.m2p\z/){

}else{
#	print "deletemovie invalid filetype.\n";
	&writelog("deletemovie invalid filetype:$fname.");
	exit (1);
}

#ファイル存在チェック

if (-e "$recfolderpath/$fname"){

}else{
#	print "deletemovie file not found.$recfolderpath/$fname\n";
	&writelog("deletemovie file not found:$fname.");
	exit (1);
}

#既読削除処理 
if ($rapidfiledelete  > 0){ #./mita/へ移動
	system ("mv $recfolderpath/$fname $recfolderpath/mita/");
	&writelog("deletemovie mv $recfolderpath/$fname $recfolderpath/mita/.");
}else{ #即時削除
	system ("rm $recfolderpath/$fname ");
	&writelog("deletemovie rm $recfolderpath/$fname ");


}



