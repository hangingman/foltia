#!/usr/bin/perl
#usage ipodtranscode.pl 
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
# iPod MPEG4/H.264トラコン
# ffmpegを呼び出して変換
#
# DCC-JPL Japan/foltia project
#

use DBI;

use DBD::SQLite;
use Jcode;

$path = $0;
$path =~ s/ipodtranscode.pl$//i;
if ($path ne "./"){
push( @INC, "$path");
}
require "foltialib.pl";


# 二重起動の確認!
$processes =  &processfind("ipodtranscode.pl");
#$processes = $processes +  &processfind("ffmpeg");

if ($processes > 1 ){
&writelog("ipodtranscode processes exist. exit:");
exit;
}else{
#&writelog("ipodtranscode.pl  Normal launch.");
}

#DB初期化
$dbh = DBI->connect($DSN,$DBUser,$DBPass) ||die $DBI::error;;

# タイトル取得
#トラコンフラグがたっていてステータス50以上150未満のファイルを古い順にひとつ探す
# 数数える
#$DBQuery =  "SELECT count(*) FROM foltia_subtitle, foltia_program, foltia_m2pfiles 
#WHERE filestatus >= $FILESTATUSRECEND AND filestatus < $FILESTATUSTRANSCODECOMPLETE  AND foltia_program.tid = foltia_subtitle.TID AND foltia_program.PSP = 1  AND foltia_m2pfiles.m2pfilename = foltia_subtitle.m2pfilename  ";
#$sth = $dbh->prepare($DBQuery);
#$sth->execute();
#@titlecount= $sth->fetchrow_array;
&writelog("ipodtranscode starting up.");

$counttranscodefiles = &counttranscodefiles();
if ($counttranscodefiles == 0){
	&writelog("ipodtranscode No MPEG2 files to transcode.");
	exit;
}
sleep 30;

while ($counttranscodefiles >= 1){
    $sth = $dbh->prepare($stmt{'ipodtranscode.1'});
    $sth->execute($FILESTATUSRECEND, $FILESTATUSTRANSCODECOMPLETE, );
@dbparam = $sth->fetchrow_array;
#print "$dbparam[0],$dbparam[1],$dbparam[2],$dbparam[3],$dbparam[4],$dbparam[5]\n";
#&writelog("ipodtranscode DEBUG $DBQuery");
&writelog("ipodtranscode DEBUG $dbparam[0],$dbparam[1],$dbparam[2],$dbparam[3],$dbparam[4],$dbparam[5]");
$pid = $dbparam[0];
$tid = $dbparam[1];
$inputmpeg2 = $recfolderpath."/".$dbparam[2]; # path付き
$mpeg2filename = $dbparam[2]; # pathなし
$filestatus = $dbparam[3];
$aspect = $dbparam[4];# 16,1 (超額縁),4,3
$countno = $dbparam[5];
$mp4filenamestring = &mp4filenamestringbuild($pid);

if (-e $inputmpeg2){#MPEG2ファイルが存在していれば

&writelog("ipodtranscode DEBUG mp4filenamestring $mp4filenamestring");
#展開ディレクトリ作成
$pspdirname = &makemp4dir($tid);
$mp4outdir = $pspdirname ;
# 実際のトラコン
# タイトル取得
if ($pid ne ""){
	$sth = $dbh->prepare($stmt{'ipodtranscode.2'});
	$sth->execute($pid);
@programtitle = $sth->fetchrow_array;
$programtitle[0] =~ s/\"/\\"/gi;
$programtitle[2] =~ s/\"/\\"/gi;

	if ($pid > 0){
		if ($programtitle[1] ne ""){
			$movietitle = " -title \"$programtitle[0] 第$programtitle[1]話 $programtitle[2]\" ";
			$movietitleeuc = " -t \"$programtitle[0] 第$programtitle[1]話 $programtitle[2]\" ";
		}else{
			$movietitle = " -title \"$programtitle[0] $programtitle[2]\" ";
			$movietitleeuc = " -t \"$programtitle[0] $programtitle[2]\" ";
		}
	}elsif($pid < 0){
	#EPG
		$movietitle = " -title \"$programtitle[2]\" ";
		$movietitleeuc = " -t \"$programtitle[2]\" ";
	}else{# 0
	#空白
	$movietitle = "";
	$movietitleeuc = "";
	}
#Jcode::convert(\$movietitle,'utf8');# Title入れるとiTunes7.0.2がクラッシュする
	$movietitle = "";
	$movietitleeuc = "";

}

if ($filestatus <= $FILESTATUSRECEND){
}

if ($filestatus <= $FILESTATUSWAITINGCAPTURE){
#なにもしない
}

if ($filestatus <= $FILESTATUSCAPTURE){
#unlink
# Starlight breaker向けキャプチャ画像作成
if (-e "$toolpath/perl/captureimagemaker.pl"){
	&writelog("ipodtranscode Call captureimagemaker $mpeg2filename");
&changefilestatus($pid,$FILESTATUSCAPTURE);
	system ("$toolpath/perl/captureimagemaker.pl $mpeg2filename");
&changefilestatus($pid,$FILESTATUSCAPEND);
}
}

if ($filestatus <= $FILESTATUSCAPEND){
# サムネイル作る
&makethumbnail();
&changefilestatus($pid,$FILESTATUSTHMCREATE);
}

if ($filestatus <= $FILESTATUSWAITINGTRANSCODE){
}

$filenamebody = $inputmpeg2 ;
$filenamebody =~ s/.m2t$|.ts$|.m2p$|.mpg$|.aac$//gi;

#デジタルかアナログか
if ($inputmpeg2 =~ /m2t$|ts$|aac$/i){

if ($filestatus <= $FILESTATUSTRANSCODETSSPLITTING){
		unlink("${filenamebody}_tss.m2t");
		unlink("${filenamebody}_HD.m2t");
}
if ($filestatus <= $FILESTATUSTRANSCODEFFMPEG){
	unlink("$filenamebody.264");
	# H.264出力
	$trcnmpegfile = $inputmpeg2 ;
	# アスペクト比
	if ($aspect == 1){#超額縁
	$cropopt = " -croptop 150 -cropbottom 150 -cropleft 200 -cropright 200 ";
	}elsif($aspect == 4){#SD 
	$cropopt = " -croptop 6 -cropbottom 6 -cropleft 8 -cropright 8 ";
	}else{#16:9
	$cropopt = " -croptop 6 -cropbottom 6 -cropleft 8 -cropright 8 ";
	}
	# クオリティごとに
	if (($trconqty eq "")||($trconqty == 1)){
	$ffmpegencopt = " -threads 0 -s 360x202 -deinterlace -r 24.00 -vcodec libx264 -g 300 -b 330000 -level 13 -loop 1 -sc_threshold 60 -partp4x4 1 -rc_eq 'blurCplx^(1-qComp)' -refs 3 -maxrate 700000 -async 50 -f h264 $filenamebody.264";
	}elsif($trconqty == 2){
#	$ffmpegencopt = " -s 480x272 -deinterlace -r 29.97 -vcodec libx264 -g 300 -b 400000 -level 13 -loop 1 -sc_threshold 60 -partp4x4 1 -rc_eq 'blurCplx^(1-qComp)' -refs 3 -maxrate 700000 -async 50 -f h264 $filenamebody.264";
# for ffmpeg 0.5 or later
	$ffmpegencopt = " -threads 0  -s 480x272 -deinterlace -r 29.97 -vcodec libx264 -vpre default   -g 300 -b 400000 -level 13 -sc_threshold 60 -rc_eq 'blurCplx^(1-qComp)' -refs 3 -maxrate 700000 -async 50 -f h264 $filenamebody.264";
	}elsif($trconqty == 3){#640x352
#	$ffmpegencopt = " -s 640x352 -deinterlace -r 29.97 -vcodec libx264 -g 100 -b 600000 -level 13 -loop 1 -sc_threshold 60 -partp4x4 1 -rc_eq 'blurCplx^(1-qComp)' -refs 3 -maxrate 700000 -async 50 -f h264 $filenamebody.264";
# for ffmpeg 0.5 or later
	$ffmpegencopt = " -threads 0  -s 640x352 -deinterlace -r 29.97 -vcodec libx264 -vpre default   -g 100 -b 600000 -level 13 -sc_threshold 60 -rc_eq 'blurCplx^(1-qComp)' -refs 3 -maxrate 700000 -async 50 -f h264 $filenamebody.264";
	}
	&changefilestatus($pid,$FILESTATUSTRANSCODEFFMPEG);
#	&writelog("ipodtranscode ffmpeg $filenamebody.264");
#	system ("ffmpeg -y -i $trcnmpegfile $cropopt $ffmpegencopt");
#まずTsSplitする →ワンセグをソースにしてしまわないように
	if (! -e "$filenamebody.264"){
		&changefilestatus($pid,$FILESTATUSTRANSCODETSSPLITTING);
		unlink("${filenamebody}_tss.m2t");
		unlink("${filenamebody}_HD.m2t");
		if (-e "$toolpath/perl/tool/tss.py"){
		&writelog("ipodtranscode tss $inputmpeg2");
		system("$toolpath/perl/tool/tss.py $inputmpeg2");
		}else{
		# TsSplit
#		&writelog("ipodtranscode TsSplitter $inputmpeg2");
#		system("wine $toolpath/perl/tool/TsSplitter.exe  -EIT -ECM  -EMM -SD -1SEG -WAIT2 $inputmpeg2");
		}
		if(-e "${filenamebody}_tss.m2t"){
		$trcnmpegfile = "${filenamebody}_tss.m2t";
		}elsif (-e "${filenamebody}_HD.m2t"){
		$trcnmpegfile = "${filenamebody}_HD.m2t";
		}else{
		&writelog("ipodtranscode ERR NOT Exist ${filenamebody}_HD.m2t");
		$trcnmpegfile = $inputmpeg2 ;
		}
		#Splitファイルの確認
		$trcnmpegfile = &validationsplitfile($inputmpeg2,$trcnmpegfile);
		#tss.pyに失敗してたなら強制的にWINEでTsSplit.exe
		if($trcnmpegfile eq $inputmpeg2){
		
		# TsSplit
		&writelog("ipodtranscode WINE TsSplitter.exe $inputmpeg2");
		system("wine $toolpath/perl/tool/TsSplitter.exe -EIT -ECM  -EMM -SD -1SEG -WAIT2 $inputmpeg2");
		if (-e "${filenamebody}_HD.m2t"){
			$trcnmpegfile = "${filenamebody}_HD.m2t";
			#Splitファイルの確認
			$trcnmpegfile = &validationsplitfile($inputmpeg2,$trcnmpegfile);
#			if($trcnmpegfile ne $inputmpeg2){
#			&changefilestatus($pid,$FILESTATUSTRANSCODEFFMPEG);
#			&writelog("ipodtranscode ffmpeg retry ; WINE TsSplitter.exe $trcnmpegfile");
#			system ("ffmpeg -y -i $trcnmpegfile $cropopt $ffmpegencopt");
#			}else{
#			&writelog("ipodtranscode WINE TsSplit.exe fail");
#			}
		}else{
		&writelog("ipodtranscode WINE TsSplitter.exe ;Not exist ${filenamebody}_HD.m2t");
		}#endif -e ${filenamebody}_HD.m2t
		
		}#endif $trcnmpegfile eq $inputmpeg2
		
		
		#再ffmpeg
		&changefilestatus($pid,$FILESTATUSTRANSCODEFFMPEG);
		&writelog("ipodtranscode ffmpeg retry $filenamebody.264");
		system ("ffmpeg -y -i $trcnmpegfile $cropopt $ffmpegencopt");
	}
	#もしエラーになったらcropやめる
	if (! -e "$filenamebody.264"){
		#再ffmpeg
		&changefilestatus($pid,$FILESTATUSTRANSCODEFFMPEG);
		&writelog("ipodtranscode ffmpeg retry no crop $filenamebody.264");
		system ("ffmpeg -y -i $trcnmpegfile $ffmpegencopt");
	}
	#強制的にWINEでTsSplit.exe
	if (! -e "$filenamebody.264"){
	}
	#それでもエラーならsplitしてないファイルをターゲットに
	if (! -e "$filenamebody.264"){
		#再ffmpeg
		&changefilestatus($pid,$FILESTATUSTRANSCODEFFMPEG);
		&writelog("ipodtranscode ffmpeg retry No splited originalTS file $filenamebody.264");
		system ("ffmpeg -y -i $inputmpeg2 $ffmpegencopt");
	}
}
if ($filestatus <= $FILESTATUSTRANSCODEWAVE){
	# WAVE出力
	unlink("${filenamebody}.wav");
	&changefilestatus($pid,$FILESTATUSTRANSCODEWAVE);
	&writelog("ipodtranscode mplayer $filenamebody.wav");
	system ("mplayer $trcnmpegfile -vc null -vo null -ao pcm:file=$filenamebody.wav:fast");

}
if ($filestatus <= $FILESTATUSTRANSCODEAAC){
	# AAC変換
	unlink("${filenamebody}.aac");
	&changefilestatus($pid,$FILESTATUSTRANSCODEAAC);
	if (-e "$toolpath/perl/tool/neroAacEnc"){
		if (-e "$filenamebody.wav"){
	&writelog("ipodtranscode neroAacEnc $filenamebody.wav");
	system ("$toolpath/perl/tool/neroAacEnc -br 128000  -if $filenamebody.wav  -of $filenamebody.aac");
		}else{
		&writelog("ipodtranscode ERR Not Found $filenamebody.wav");
		}
	}else{
	#print "DEBUG $toolpath/perl/tool/neroAacEnc\n\n";
	&writelog("ipodtranscode faac $filenamebody.wav");
	system ("faac -b 128  -o $filenamebody.aac $filenamebody.wav ");
	}

}
if ($filestatus <= $FILESTATUSTRANSCODEMP4BOX){

unlink("${filenamebody}.base.mp4");

#デジタルラジオなら
if ($inputmpeg2 =~ /aac$/i){
	if (-e "$toolpath/perl/tool/MP4Box"){
		&writelog("ipodtranscode MP4Box $filenamebody");
		system ("cd $recfolderpath ;$toolpath/perl/tool/MP4Box -add $filenamebody.aac  -new $filenamebody.base.mp4");
	$exit_value = $? >> 8;
	$signal_num = $? & 127;
	$dumped_core = $? & 128;
	&writelog("ipodtranscode DEBUG MP4Box -add $filenamebody.aac  -new $filenamebody.base.mp4:$exit_value:$signal_num:$dumped_core");
	}else{
		&writelog("ipodtranscode WARN; Pls. install $toolpath/perl/tool/MP4Box");
	}
}else{
	# MP4ビルド
	if (-e "$toolpath/perl/tool/MP4Box"){
		&changefilestatus($pid,$FILESTATUSTRANSCODEMP4BOX);
		&writelog("ipodtranscode MP4Box $filenamebody");
		system ("cd $recfolderpath ;$toolpath/perl/tool/MP4Box -fps 29.97 -add $filenamebody.264 -new $filenamebody.base.mp4");
	$exit_value = $? >> 8;
	$signal_num = $? & 127;
	$dumped_core = $? & 128;
	&writelog("ipodtranscode DEBUG MP4Box -fps 29.97 -add $filenamebody.264 -new $filenamebody.base.mp4:$exit_value:$signal_num:$dumped_core");
		if (-e "$filenamebody.base.mp4"){
		system ("cd $recfolderpath ;$toolpath/perl/tool/MP4Box -add $filenamebody.aac $filenamebody.base.mp4");
	$exit_value = $? >> 8;
	$signal_num = $? & 127;
	$dumped_core = $? & 128; 
	&writelog("ipodtranscode DEBUG MP4Box -add $filenamebody.aac:$exit_value:$signal_num:$dumped_core");
		}else{
		$filelist = `ls -lhtr $recfolderpath/${filenamebody}*`;
		$debugenv = `env`;
		&writelog("ipodtranscode ERR File not exist.$debugenv.$filelist ;$filenamebody.base.mp4;$filelist;cd $recfolderpath ;$toolpath/perl/tool/MP4Box -fps 29.97 -add $filenamebody.264 -new $filenamebody.base.mp4");
		}
	}else{
		&writelog("ipodtranscode WARN; Pls. install $toolpath/perl/tool/MP4Box");
	}
unlink("$filenamebody.aac");
}#endif #デジタルラジオなら
	
#}

#if ($filestatus <= $FILESTATUSTRANSCODEATOM){
	if (-e "$toolpath/perl/tool/MP4Box"){
		# iPodヘッダ付加
#		&changefilestatus($pid,$FILESTATUSTRANSCODEATOM);
		&writelog("ipodtranscode ATOM $filenamebody");
		#system ("/usr/local/bin/ffmpeg -y -i $filenamebody.base.mp4 -vcodec copy -acodec copy -f ipod ${mp4outdir}MAQ${mp4filenamestring}.MP4");
#		system ("cd $recfolderpath ; MP4Box -ipod $filenamebody.base.mp4");
		system ("cd $recfolderpath ; $toolpath/perl/tool/MP4Box -ipod $filenamebody.base.mp4");
	$exit_value = $? >> 8;
	$signal_num = $? & 127;
	$dumped_core = $? & 128;
	&writelog("ipodtranscode DEBUG MP4Box -ipod $filenamebody.base.mp4:$exit_value:$signal_num:$dumped_core");
		if (-e "$filenamebody.base.mp4"){
		unlink("${mp4outdir}MAQ${mp4filenamestring}.MP4");
		system("mv $filenamebody.base.mp4 ${mp4outdir}MAQ${mp4filenamestring}.MP4");
		&writelog("ipodtranscode mv $filenamebody.base.mp4 ${mp4outdir}MAQ${mp4filenamestring}.MP4");
		}else{
		&writelog("ipodtranscode ERR $filenamebody.base.mp4 Not found.");
		}
	# ipodtranscode mv /home/foltia/php/tv/1329-21-20080829-0017.base.mp4 /home/foltia/php/tv/1329.localized/mp4/MAQ-/home/foltia/php/tv/1329-21-20080829-0017.MP4
	}else{
		&writelog("ipodtranscode WARN; Pls. install $toolpath/perl/tool/MP4Box");
	}
}
if ($filestatus <= $FILESTATUSTRANSCODECOMPLETE){
	if (-e "${mp4outdir}MAQ${mp4filenamestring}.MP4"){
	# 中間ファイル消す
	&changefilestatus($pid,$FILESTATUSTRANSCODECOMPLETE);
	&updatemp4file();
	}else{
		&writelog("ipodtranscode ERR ; Fail.Giving up!  MAQ${mp4filenamestring}.MP4");
		&changefilestatus($pid,999);
	}
	unlink("${filenamebody}_HD.m2t");
# ConfigによってTSファイルは常にsplitした状態にするかどうか選択
# B25失敗したときにここが走るとファイルぶっ壊れるので検証を入れる
#
#	if (-e "${filenamebody}_tss.m2t"){
#		unlink("${filenamebody}.m2t");
#		unless (rename "${filenamebody}_tss.m2t", "${filenamebody}.m2t") {
#		&writelog("ipodtranscode WARNING RENAME FAILED ${filenamebody}_tss.m2t ${filenamebody}.m2t");
#		}else{
#		
#		}
#	}
	unlink("${filenamebody}_tss.m2t");
	unlink("$filenamebody.264");
	unlink("$filenamebody.wav");
	unlink("$filenamebody.base.mp4");

}

}else{ #デジタルかアナログか
	#print "MPEG2\n";
	# アスペクト比
	if ($aspect == 16){
	$cropopt = " -croptop 70 -cropbottom 60 -cropleft  8 -cropright 14 -aspect 16:9 ";
	}else{
	$cropopt = " -croptop 8 -cropbottom 8 -cropleft  8 -cropright 14 ";
	}
# クオリティごとに
if (($trconqty eq "")||($trconqty == 1)){
#$encodeoption = "-y -i $inputmpeg2 -vcodec xvid $cropopt -s 320x240 -b 300 -bt 128 -r 14.985 -bufsize 192 -maxrate 512 -minrate 0 -deinterlace -acodec aac -ab 128 -ar 24000 -ac 2 $movietitle ${mp4outdir}M4V${mp4filenamestring}.MP4";
$mp4file = "${mp4outdir}M4V${mp4filenamestring}.MP4";
$encodeoption = "-y -i $inputmpeg2 vcodec libxvid $cropopt -s 320x240 -b 300 -bt 128 -r 14.985 -deinterlace -acodec libfaac -f ipod  ${mp4outdir}M4V${mp4filenamestring}.MP4";
#time ffmpeg -y  -i /home/foltia/php/tv/trcntest/nanoha-As-op.mpg -vcodec libxvid -croptop 8 -cropbottom 8 -cropleft  8 -cropright 14 -s 320x240 -b 300 -bt 128 -r 14.985 -deinterlace -acodec libfaac -f ipod M4V-Nanoha-As-OP.MP4
# 32sec
# 2.1MB
}elsif($trconqty == 2){ 
#$encodeoption = "-y -i $inputmpeg2  -target ipod -profile 51 -level 30 $cropopt -s 320x240 -b 300 -r 24 -acodec aac -ar 32000 -ac 2 $movietitle ${mp4outdir}MAQ${mp4filenamestring}.MP4";
$mp4file = "${mp4outdir}MAQ${mp4filenamestring}.MP4";
$encodeoption = "-y -i $inputmpeg2 -vcodec libx264 -croptop 8 $cropopt -s 320x240 -b 300 -bt 128 -r 24 -deinterlace -acodec libfaac -f ipod  ${mp4outdir}MAQ${mp4filenamestring}.MP4";
#time ffmpeg -y  -i /home/foltia/php/tv/trcntest/nanoha-As-op.mpg -vcodec libx264 -croptop 8 -cropbottom 8 -cropleft  8 -cropright 14 -s 320x240 -b 300 -bt 128 -r 24 -deinterlace -acodec libfaac -f ipod MAQ-Nanoha-As-OP.MP4
# 2min22sec
# 6.4MB
}elsif($trconqty == 3){ 
#$encodeoption = "-y -i $inputmpeg2  -target ipod -profile 51 -level 30 $cropopt  -acodec aac -ab 96 -vcodec h264  -maxrate 700 -minrate 0 -deinterlace -b 300 -ar 32000 -mbd 2 -coder 1 -cmp 2 -subcmp 2 -s 320x240 -r 30000/1001  -flags loop -trellis 2 -partitions parti4x4+parti8x8+partp4x4+partp8x8+partb8x8 $movietitle ${mp4outdir}MAQ${mp4filenamestring}.MP4";
$mp4file = "${mp4outdir}MAQ${mp4filenamestring}.MP4";
$encodeoption = "-y -i $inputmpeg2  -vcodec libx264 $cropopt -s 320x240 -b 380 -bt 128 -r 29.97 -deinterlace -acodec libfaac -f ipod  ${mp4outdir}MAQ${mp4filenamestring}.MP4";
#time ffmpeg -y  -i /home/foltia/php/tv/trcntest/nanoha-As-op.mpg -vcodec libx264 -croptop 8 -cropbottom 8 -cropleft  8 -cropright 14 -s 320x240 -b 380 -bt 128 -r 29.97 -deinterlace -acodec libfaac -f ipod MAQ-Nanoha-As-OP.MP4
#  2m53.912s
# 7MB
}elsif($trconqty == 4){
#$encodeoption = "-y -i $inputmpeg2  -target ipod -profile 51 -level 30 $cropopt -s 480x360 -b 400 -r 24 -acodec aac -ar 32000 -ac 2 $movietitle ${mp4outdir}MAQ${mp4filenamestring}.MP4";
$mp4file = "${mp4outdir}MAQ${mp4filenamestring}.MP4";
$encodeoption = "-y -i $inputmpeg2 -vcodec libx264 $cropopt -s 640x480 -b 500 -maxrate 700 -bt 128 -r 29.97 -deinterlace -acodec libfaac -f ipod ${mp4outdir}MAQ${mp4filenamestring}.MP4";
#time ffmpeg -y  -i /home/foltia/php/tv/trcntest/nanoha-As-op.mpg -vcodec libx264 -croptop 8 -cropbottom 8 -cropleft  8 -cropright 14 -s 640x480 -b 500  -maxrate 700 -bt 128 -r 29.97 -deinterlace -acodec libfaac -f ipod MAQ-Nanoha-As-OP.MP4
# 11m0.294s
# 20MB
}elsif($trconqty == 5){ 
#$encodeoption = "-y -i $inputmpeg2  -target ipod -profile 51 -level 30 $cropopt  -acodec aac -ab 96 -vcodec h264  -maxrate 700 -minrate 0 -deinterlace -b 400 -ar 32000 -mbd 2 -coder 1 -cmp 2 -subcmp 2 -s 480x360 -r 30000/1001  -flags loop -trellis 2 -partitions parti4x4+parti8x8+partp4x4+partp8x8+partb8x8 $movietitle ${mp4outdir}MAQ${mp4filenamestring}.MP4";
$mp4file = "${mp4outdir}MAQ${mp4filenamestring}.MP4";
$encodeoption = "-y -i $inputmpeg2 -vcodec libx264 -croptop 8 $cropopt -s 640x480 -b 500  -maxrate 700 -bt 128 -r 29.97 -deinterlace -flags loop -trellis 2 -partitions parti4x4+parti8x8+partp4x4+partp8x8+partb8x8 -acodec libfaac -f ipod ${mp4outdir}MAQ${mp4filenamestring}.MP4";
#time ffmpeg -y  -i /home/foltia/php/tv/trcntest/nanoha-As-op.mpg -vcodec libx264 -croptop 8 -cropbottom 8 -cropleft  8 -cropright 14 -s 640x480 -b 500  -maxrate 700 -bt 128 -r 29.97 -deinterlace -flags loop -trellis 2 -partitions parti4x4+parti8x8+partp4x4+partp8x8+partb8x8  -acodec libfaac -f ipod MAQ-Nanoha-As-OP.MP4
#  14m14.033s
# 18MB
}

$encodeoptionlog = $encodeoption;
Jcode::convert(\$encodeoptionlog,'euc');

&writelog("ipodtranscode START QTY=$trconqty $encodeoptionlog");
#print "ffmpeg $encodeoptionlog \n";
&changefilestatus($pid,$FILESTATUSTRANSCODEFFMPEG);
system ("ffmpeg  $encodeoption ");
&writelog("ipodtranscode FFEND $inputmpeg2");
&changefilestatus($pid,$FILESTATUSTRANSCODECOMPLETE);
#もう要らなくなった #2008/11/14 
#&writelog("ipodtranscode mp4psp -p $mp4file $movietitleeuc");
#system("/usr/local/bin/mp4psp -p $mp4file '$movietitleeuc' ");
#&writelog("ipodtranscode mp4psp COMPLETE  $mp4file ");

&updatemp4file();
}#endif #デジタルかアナログか

$counttranscodefiles = &counttranscodefiles();
############################
#一回で終らせるように
#exit;


}else{#ファイルがなければ
&writelog("ipodtranscode NO $inputmpeg2 file.Skip.");
}#end if

}# end while
#残りファイルがゼロなら
&writelog("ipodtranscode ALL COMPLETE");
exit;


#-----------------------------------------------------------------------
sub mp4filenamestringbuild(){
#ファイル名決定
#1329-19-20080814-2337.m2t
my @mpegfilename = split(/\./,$dbparam[2]) ;
my $pspfilname = "-".$mpegfilename[0] ;
return("$pspfilname");
}#end sub mp4filenamestringbuild


sub makethumbnail(){
#サムネール
my $outputfilename = $inputmpeg2 ;#フルパス
my $thmfilename = "MAQ${mp4filenamestring}.THM";
&writelog("ipodtranscode DEBUG thmfilename $thmfilename");

#system ("mplayer -ss 00:01:20 -vo jpeg:outdir=$pspdirname -ao null -sstep 1 -frames 3  -v 3 $outputfilename");
#
#&writelog("ipodtranscode DEBUG mplayer -ss 00:01:20 -vo jpeg:outdir=$pspdirname -ao null -sstep 1 -frames 3  -v 3 $outputfilename");
if($outputfilename =~ /.m2t$/){
#ハイビジョンTS
system ("mplayer -ss 00:01:20 -vo jpeg:outdir=$pspdirname -ao null -vf framestep=300step,scale=160:90,expand=160:120 -frames 1 $outputfilename");
&writelog("ipodtranscode DEBUG mplayer -ss 00:01:20 -vo jpeg:outdir=$pspdirname -ao null -vf framestep=300step,scale=160:90,expand=160:120 -frames 1 $outputfilename");
}else{
#アナログ
system ("mplayer -ss 00:01:20 -vo jpeg:outdir=$pspdirname -ao null -vf framestep=300step,scale=165:126,crop=160:120 -frames 1 $outputfilename");
&writelog("ipodtranscode DEBUG mplayer -ss 00:01:20 -vo jpeg:outdir=$pspdirname -ao null -vf framestep=300step,scale=165:126,crop=160:120 -frames 1 $outputfilename");
}
#if (-e "$pspdirname/$thmfilename"){
#	$timestamp = strftime("%Y%m%d-%H%M%S", localtime);
#chomp $timestamp;
#	system("convert -crop 160x120+1+3 -resize 165x126\! $pspdirname/00000002.jpg $pspdirname/$thmfilename".$timestamp.".THM");
#}else{
#	system("convert -crop 160x120+1+3 -resize 165x126\! $pspdirname/00000002.jpg $pspdirname/$thmfilename");
#}
#&writelog("ipodtranscode DEBUG convert -crop 160x120+1+3 -resize 165x126\! $pspdirname/00000002.jpg $pspdirname/$thmfilename");

#system("rm -rf $pspdirname/0000000*.jpg ");
#&writelog("ipodtranscode DEBUG rm -rf $pspdirname/0000000*.jpg");
system("mv $pspdirname/00000001.jpg $pspdirname/$thmfilename");

}#endsub makethumbnail

sub updatemp4file(){
my $mp4filename = "MAQ${mp4filenamestring}.MP4";

if (-e "${mp4outdir}MAQ${mp4filenamestring}.MP4"){
# MP4ファイル名をPIDレコードに書き込み
	$sth = $dbh->prepare($stmt{'ipodtranscode.updatemp4file.1'});
	$sth->execute($mp4filename, $pid);
	&writelog("ipodtranscode UPDATEsubtitleDB $stmt{'ipodtranscode.updatemp4file.1'}");

# MP4ファイル名をfoltia_mp4files挿入
	$sth = $dbh->prepare($stmt{'ipodtranscode.updatemp4file.2'});
	$sth->execute($tid, $mp4filename);
	&writelog("ipodtranscode UPDATEmp4DB $stmt{'ipodtranscode.updatemp4file.2'}");

&changefilestatus($pid,$FILESTATUSALLCOMPLETE);
}else{
&writelog("ipodtranscode ERR MP4 NOT EXIST $pid/$mp4filename");
}


}#updatemp4file

sub counttranscodefiles(){
    $sth = $dbh->prepare($stmt{'ipodtranscode.counttranscodefiles.1'});
    $sth->execute($FILESTATUSRECEND, $FILESTATUSTRANSCODECOMPLETE);
my @titlecount= $sth->fetchrow_array;

return ($titlecount[0]);


}#end sub counttranscodefiles


sub validationsplitfile{
my $inputmpeg2 = $_[0];
my $trcnmpegfile = $_[1];

		#Split結果確認
		my $filesizeoriginal = -s $inputmpeg2 ;
		my $filesizesplit = -s $trcnmpegfile;
		my $validation = 0;
		if ($filesizesplit  > 0){
			$validation = $filesizeoriginal / $filesizesplit   ;
			if ($validation > 2 ){
				#print "Fail split may be fail.\n";
				&writelog("ipodtranscode ERR File split may be fail: $filesizeoriginal:$filesizesplit");
				$trcnmpegfile = $inputmpeg2 ;
				unlink("${filenamebody}_tss.m2t");
				unlink("${filenamebody}_HD.m2tt");
				return ($trcnmpegfile);
			}else{
				#print "Fail split may be good.\n";
				return ($trcnmpegfile);
			}
		}else{
		#Fail
		&writelog("ipodtranscode ERR File split may be fail: $filesizeoriginal:$filesizesplit");
		$trcnmpegfile = $inputmpeg2 ;
		unlink("${filenamebody}_tss.m2t");
		unlink("${filenamebody}_HD.m2tt");
		return ($trcnmpegfile);
		}
}#end sub validationsplitfile

