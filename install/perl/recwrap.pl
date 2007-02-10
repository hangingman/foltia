#!/usr/bin/perl
#usage recwrap.pl  ch length(sec) [bitrate(5)] [TID] [NO]  [PID]
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
#
#レコーディングラッパ
#atから呼び出され、tvrecordingを呼び出し録画
#そのあとMPEG4トラコンを呼び出す
#
# DCC-JPL Japan/foltia project
#

use DBI;
use DBD::Pg;
use Schedule::At;
use Time::Local;
use Jcode;

$path = $0;
$path =~ s/recwrap.pl$//i;
if ($pwd  ne "./"){
push( @INC, "$path");
}

require "foltialib.pl";
#引き数がアルか?
$recch = $ARGV[0] ;
if ($recch eq "" ){
	#引き数なしで実行されたら、終了
	print "usage recwrap.pl  ch length(sec) [bitrate(5)] [TID] [NO] [PID]\n";
	exit;
}

$recch = $ARGV[0] ;
$reclength = $ARGV[1] ;
$bitrate  = $ARGV[2] ;
$tid  = $ARGV[3] ;
$countno  = $ARGV[4] ;
$pid  = $ARGV[5] ;

$outputfile = `date  +%Y%m%d-%H%M --date "1 min "`;
chomp($outputfile);
if ($tid == 0){
		$outputfilename = "0--".$outputfile."-".$recch.".m2p";
		$mp4newstylefilename = "-0--".$outputfile."-".$recch;
}else{
	if ($countno == 0){
		$outputfilename = $tid ."--".$outputfile.".m2p";
		$mp4newstylefilename = "-" . $tid ."--".$outputfile;
	}else{
		$outputfilename = $tid ."-".$countno."-".$outputfile.".m2p";
		$mp4newstylefilename = "-" . $tid ."-".$countno."-".$outputfile;
	}
}
#リモコン操作
# $haveirdaunit = 1;リモコンつないでるかどうか確認
if ($haveirdaunit == 1){
# 録画チャンネルが0なら
	if ($recch == 0){
# &つけて非同期でchangestbch.pl呼び出し
	&writelog("recwrap Call Change STB CH :$pid");
	system ("$toolpath/perl/changestbch.pl $pid &");
	}#end if
}#end if

if($recch == -10){
#非受信局なら
	&writelog("recwrap Not recordable channel;exit:PID $pid");
	exit;
	}#end if

&writelog("recwrap RECSTART $recch $reclength 0 $outputfilename $bitrate $tid $countno $pid");
#録画
#system("$toolpath/perl/tvrecording.pl $recch $reclength 0 $outputfile $bitrate $tid $countno");
$starttime = (`date +%s`);

$oserr = system("$toolpath/perl/tvrecording.pl $recch $reclength 0 $outputfilename $bitrate $tid $countno");
$oserr = $oserr / 256;
if ($oserr == 1){
	&writelog("recwrap ABORT recfile exist. [$outputfilename] $recch $reclength 0 0 $bitrate $tid $countno $pid");
	exit;
}
#デバイスビジーで即死してないか検出
$now = (`date +%s`);
	if ($now < $starttime + 100){ #録画プロセス起動してから100秒以内に戻ってきてたら
		while($now < $starttime + 100){
		&writelog("recwrap retry recording $now $starttime");
$starttime = (`date +%s`);
$oserr = system("$toolpath/perl/tvrecording.pl $recch $reclength N $outputfilename $bitrate $tid $countno");
$now = (`date +%s`);
$oserr = $oserr / 256;
			if ($oserr == 1){
				&writelog("recwrap ABORT recfile exist. in resume process.[$outputfilename] $recch $reclength 0 0 $bitrate $tid $countno $pid");
				exit;
			}# if
		}# while
	} # if 


	&writelog("recwrap RECEND [$outputfilename] $recch $reclength 0 0 $bitrate $tid $countno $pid");

#DB初期化
	my $data_source = sprintf("dbi:%s:dbname=%s;host=%s;port=%d",
		$DBDriv,$DBName,$DBHost,$DBPort);
	 $dbh = DBI->connect($data_source,$DBUser,$DBPass) ||die $DBI::error;;

# m2pファイル名をPIDレコードに書き込み
	$DBQuery =  "UPDATE  foltia_subtitle  SET 
	m2pfilename 	 = '$outputfilename' 
	WHERE pid =  '$pid' ";
	 $sth = $dbh->prepare($DBQuery);
	$sth->execute();
&writelog("recwrap UPDATEDB  $DBQuery");

# m2pファイル名をPIDレコードに書き込み
	$DBQuery =  "insert into  foltia_m2pfiles values ('$outputfilename')";
	 $sth = $dbh->prepare($DBQuery);
	$sth->execute();
&writelog("recwrap UPDATEDB  $DBQuery");

# Starlight breaker向けキャプチャ画像作成
if (-e "$toolpath/perl/captureimagemaker.pl"){
	&writelog("recwrap Call captureimagemaker $outputfilename");
	system ("$toolpath/perl/captureimagemaker.pl $outputfilename");
}



# PSP ------------------------------------------------------
#PSPトラコン必要かどうか
$DBQuery =  "SELECT psp,aspect,title FROM  foltia_program WHERE tid = '$tid' ";
	 $sth = $dbh->prepare($DBQuery);
	$sth->execute();
 @psptrcn= $sth->fetchrow_array;
 if ($psptrcn[0]  == 1 ){#トラコン番組


#PSPムービーディレクトリがアルかどうか
 
#TIDが100以上の3桁の場合はそのまま
my $pspfilnamehd = "";

	$pspfilnamehd = $tid;
$pspdirname = "$tid.localized/";
$pspdirname = $recfolderpath."/".$pspdirname;

#なければ作る
unless (-e $pspdirname ){
	system("$toolpath/perl/mklocalizeddir.pl $tid");
	#&writelog("recwrap mkdir $pspdirname");
}
$pspdirname = "$tid.localized/mp4/";
$pspdirname = $recfolderpath."/".$pspdirname;
#なければ作る
unless (-e $pspdirname ){
	mkdir $pspdirname ,0777;
	#&writelog("recwrap mkdir $pspdirname");
}

#ファイル名決定
if ($mp4filenamestyle == 1){# 1;よりわかりやすいファイル名
 $pspfilname = $mp4newstylefilename ;
 
}else{##0:PSP ファームウェアver.2.80より前と互換性を持つファイル名
#・フォルダ名[100MNV01]の100の部分は変更可(100〜999)。
# MP_ROOT ━ 100MNV01 ┳ M4V00001.MP4（動画）
#┃         　        ┗ M4V00001.THM（サムネイル）※必須ではない

#ファイル名決定
#ファイル名決定 #新アルゴリズム
#TID 0000-3599まで[3桁]
#話数 00-999まで[2桁]

my $pspfilnameft = "";
my $pspfilnameyearhd = "";
my $pspfilnameyearft = "";

$btid = $tid % 3600;
# print "$btid\n";

if($btid >= 0 && $btid < 1000){

	$pspfilnamehd = sprintf("%03d",$btid);

}elsif ($btid >= 1000 && $btid < 3600){
	$pspfilnameyearhd = substr($btid, 0, 2);
	$pspfilnameyearhd =~ s/10/A/;
	$pspfilnameyearhd =~ s/11/B/;
	$pspfilnameyearhd =~ s/12/C/;
	$pspfilnameyearhd =~ s/13/D/;
	$pspfilnameyearhd =~ s/14/E/;
	$pspfilnameyearhd =~ s/15/F/;
	$pspfilnameyearhd =~ s/16/G/;
	$pspfilnameyearhd =~ s/17/H/;
	$pspfilnameyearhd =~ s/18/I/;
	$pspfilnameyearhd =~ s/19/J/;
	$pspfilnameyearhd =~ s/20/K/;
	$pspfilnameyearhd =~ s/21/L/;
	$pspfilnameyearhd =~ s/22/M/;
	$pspfilnameyearhd =~ s/23/N/;
	$pspfilnameyearhd =~ s/24/O/;
	$pspfilnameyearhd =~ s/25/P/;
	$pspfilnameyearhd =~ s/26/Q/;
	$pspfilnameyearhd =~ s/27/R/;
	$pspfilnameyearhd =~ s/28/S/;
	$pspfilnameyearhd =~ s/29/T/;
	$pspfilnameyearhd =~ s/30/U/;
	$pspfilnameyearhd =~ s/31/V/;
	$pspfilnameyearhd =~ s/32/W/;
	$pspfilnameyearhd =~ s/33/X/;
	$pspfilnameyearhd =~ s/34/Y/;
	$pspfilnameyearhd =~ s/35/Z/;
	
$pspfilnameyearft = substr($btid, 2, 2);
$pspfilnameyearft = sprintf("%02d",$pspfilnameyearft);
$pspfilnamehd = $pspfilnameyearhd . $pspfilnameyearft;

}

# 話数
if (0 < $countno && $countno < 100 ){
# 2桁
	$pspfilnameft = sprintf("%02d",$countno);
}elsif(100 <= $countno && $countno < 1000 ){
# 3桁
	$pspfilnameft = sprintf("%03d",$countno); # 話数3桁
	$pspfilnamehd = substr($pspfilnamehd, 0, 2); # TID 二桁　後ろ1バイト落とし
}elsif(1000 <= $countno && $countno < 10000 ){
# 4桁
	$pspfilnameft = sprintf("%04d",$countno); # 話数4桁
	$pspfilnamehd = substr($pspfilnamehd, 0, 1); # TID 1桁　後ろ2バイト落とし


}elsif($countno == 0){
#タイムスタンプが最新のMP4ファイル名取得
my $newestmp4filename = `cd $pspdirname ; ls -t *.MP4 | head -1`;
 if ($newestmp4filename =~ /M4V$tid/){
	$nowcountno = $' ;
		$nowcountno++;
		$pspfilnameft = sprintf("%02d",$nowcountno);
	while (-e "$pspdirname/M4V".$pspfilnamehd.$pspfilnameft.".MP4"){
		$nowcountno++;
		$pspfilnameft = sprintf("%02d",$nowcountno);	
	print "File exist:$nowcountno\n";
	}
#print "NeXT\n";
}else{
# 0の場合　週番号を100から引いたもの
# week number of year with Monday as first day of week (01..53)
#だったけど常に0に
#	my $weeno = `date "+%V"`;
#	$weeno = 100 - $weeno ;
#	$pspfilnameft = sprintf("%02d",$weeno);
	$pspfilnameft = sprintf("%02d",0);
#print "WEEKNO\n";
}

}

my $pspfilname = $pspfilnamehd.$pspfilnameft  ;
# print "$pspfilname($pspfilnamehd/$pspfilnameft)\n";
}# endif MP4ファイル名が新styleなら
#2006/12/03_10:30:24 recwrap TRCNSTART vfr4psp.sh /home/foltia/php/tv/591-87-20061203-1000.m2p -591-87-20061203-1000 /home/foltia/php/tv/591.localized/mp4/ 3

if (($trconqty eq "")||($trconqty == 0 )){
	&writelog("recwrap TRCNSTART vfr4psp.sh $recfolderpath/$outputfilename $pspfilname $pspdirname $psptrcn[1]");
	system("$toolpath/perl/transcode/vfr4psp.sh $recfolderpath/$outputfilename $pspfilname $pspdirname $psptrcn[1]");
	&writelog("recwrap TRCNEND  vfr4psp.sh $recfolderpath/$outputfilename $pspfilname $pspdirname $psptrcn[1]");
	#最適化
	$DBQuery =  "SELECT subtitle  FROM  foltia_subtitle WHERE tid = '$tid' AND countno = '$countno' ";
		 $sth = $dbh->prepare($DBQuery);
		$sth->execute();
	 @programtitle = $sth->fetchrow_array;
	if ( $countno == "0" ){
		$pspcountno = "";
	}else{
		$pspcountno = $countno ;
	}
	&writelog("recwrap OPTIMIZE  mp4psp -p $pspdirname/M4V$pspfilname.MP4   -t  '$psptrcn[2] $pspcountno $programtitle[0]' ");
	Jcode::convert(\$programtitle[0],'euc');
	system ("/usr/local/bin/mp4psp -p $pspdirname/M4V$pspfilname.MP4   -t  '$psptrcn[2] $pspcountno $programtitle[0]'") ;
$mp4filename = "M4V${pspfilname}.MP4";
$thmfilename = "M4V${pspfilname}.THM";
}else{# #2006/12/6 新エンコーダ

	&writelog("recwrap TRCNSTART ipodtranscode.pl $recfolderpath/$outputfilename $pspfilname $pspdirname $pid $psptrcn[1]");
	system("$toolpath/perl/ipodtranscode.pl $recfolderpath/$outputfilename $pspfilname $pspdirname $pid $psptrcn[1]");
	&writelog("recwrap TRCNEND  ipodtranscode.pl $recfolderpath/$outputfilename $pspfilname $pspdirname $psptrcn[1]");

	if($trconqty >= 2){#H.264/AVCなら
	$mp4filename = "MAQ${pspfilname}.MP4";
	$thmfilename = "MAQ${pspfilname}.THM";
	}else{
	$mp4filename = "M4V${pspfilname}.MP4";
	$thmfilename = "M4V${pspfilname}.THM";
	}
}

#サムネール

# mplayer -ss 00:01:20 -vo jpeg:outdir=/home/foltia/php/tv/443MNV01 -ao null -sstep 1 -frames 3  -v 3 /home/foltia/php/tv/443-07-20050218-0030.m2p
#2005/02/22_18:30:05 recwrap TRCNSTART vfr4psp.sh /home/foltia/php/tv/447-21-20050222-1800.m2p 44721 /home/foltia/php/tv/447MNV01 3
&writelog("recwrap THAMJ  mplayer -ss 00:01:20 -vo jpeg:outdir=$pspdirname -ao null -sstep 1 -frames 3  -v 3 $recfolderpath/$outputfilename ");
system ("mplayer -ss 00:01:20 -vo jpeg:outdir=$pspdirname -ao null -sstep 1 -frames 3  -v 3 $recfolderpath/$outputfilename");
&writelog("recwrap THAMI  convert -crop 160x120+1+3 -resize 165x126\! $pspdirname/00000002.jpg $pspdirname/M4V$pspdirname.THM ");

if (-e "$pspdirname/$thmfilename"){
$timestamp =`date "+%Y%m%d-%H%M%S"`;
chomp $timestamp;
	system("convert -crop 160x120+1+3 -resize 165x126\! $pspdirname/00000002.jpg $pspdirname/$thmfilename".$timestamp.".THM");

}else{
	system("convert -crop 160x120+1+3 -resize 165x126\! $pspdirname/00000002.jpg $pspdirname/$thmfilename");
}
# rm -rf 00000001.jpg      
# convert -resize 160x120\! 00000002.jpg M4V44307.THM
# rm -rf 00000002.jpg  
system("rm -rf $pspdirname/0000000*.jpg ");




# MP4ファイル名をPIDレコードに書き込み
	$DBQuery =  "UPDATE  foltia_subtitle  SET 
	PSPfilename 	 = '$mp4filename' 
	WHERE pid =  '$pid' ";
	 $sth = $dbh->prepare($DBQuery);
	$sth->execute();
&writelog("recwrap UPDATEsubtitleDB  $DBQuery");

# MP4ファイル名をfoltia_mp4files挿入
	$DBQuery =  "insert into  foltia_mp4files values ('$tid','$mp4filename') ";
	 $sth = $dbh->prepare($DBQuery);
	$sth->execute();
&writelog("recwrap UPDATEmp4DB  $DBQuery");

}#PSPトラコンあり




