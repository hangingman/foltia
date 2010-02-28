#!/usr/bin/perl
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
#digitaltvrecording.pl
# friioをはじめとするデジタル録画プログラムを呼びだす録画モジュール。
#
#usage digitaltvrecording.pl bandtype ch length(sec) [stationid] [sleeptype] [filename] [TID] [NO] [unittype]
#引数
#bandtype : 0:地デジ 1:BSデジタル 2:CSデジタル
#ch :録画チャンネル (地デジはそのまま渡す、BS/CSデジタルは基本的にチャンネル BS1/BS2など同じ数時に)
#length(sec) :録画秒数 [必須項目]
#[stationid] :foltia stationid
#[sleeptype] :0かN Nならスリープなしで録画
#[filename] :出力ファイル名
#[TID] :しょぼかるタイトルID
#[NO] :その番組の放送話数
#[unittype] :friioかfriioBSかユニデンチューナかHDUSかなど(未使用)
#
# DCC-JPL Japan/foltia project
#
#

$path = $0;
$path =~ s/digitaltvrecording.pl$//i;
if ($path ne "./"){
push( @INC, "$path");
}

#tvConfig.pl -------------------------------
$extendrecendsec = 10;							#recording end second. 
#$startupsleeptime = 52;					#process wait(MAX60sec)
$startupsleeptime = 27;					#process wait(MAX60sec)
#-------------------------------

require 'foltialib.pl';

 &writelog("digitaltvrecording: DEBUG $ARGV[0] $ARGV[1] $ARGV[2] $ARGV[3] $ARGV[4] $ARGV[5] $ARGV[6] $ARGV[7] $ARGV[8]");


#準備
&prepare;
#もし録画が走ってたら、止める
#$reclengthsec = &chkrecprocess();
#&setbitrate;
#&chkextinput;
#$reclengthsec = $reclengthsec + $extendrecendsec ;

&calldigitalrecorder;

&writelog("digitaldigitaltvrecording:RECEND:$bandtype $recch $lengthsec $stationid $sleeptype $filename $tid $countno $unittype
");

# -- これ以下サブルーチン ----------------------------


sub prepare{

#引数エラー処理
$bandtype = $ARGV[0] ;
$recch = $ARGV[1] ;
$lengthsec = $ARGV[2] ;
$stationid = $ARGV[3] ;
$sleeptype = $ARGV[4] ;
$filename = $ARGV[5] ;
$tid = $ARGV[6] ;
$countno = $ARGV[7] ;
$unittype = $ARGV[8] ; 

if (($bandtype eq "" )|| ($recch eq "")|| ($lengthsec eq "")){
	print "usage digitaltvrecording.pl bandtype ch length(sec) [stationid] [sleeptype] [filename] [TID] [NO] [unittype]\n";
	exit;
}

my $intval = $recch % 10; # 0〜9 sec
my $startupsleep = $startupsleeptime - $intval; #  18〜27 sec
$reclengthsec = $lengthsec + (60 - $startupsleep) + 1; #

if ( $sleeptype ne "N"){
	&writelog("digitaltvrecording: DEBUG SLEEP $startupsleeptime:$intval:$startupsleep:$reclengthsec");
	sleep ( $startupsleep);
	#2008/08/12_06:39:00 digitaltvrecording: DEBUG SLEEP 17:23:-6:367
}else{
	&writelog("digitaltvrecording: DEBUG RAPID START");
}
## recfriio このへんどうなってるの?
#if ($recunits > 1){
#my $deviceno = $recunits - 1;#3枚差しのとき/dev/video2から使う
#	$recdevice = "/dev/video$deviceno";
#	$recch = $ARGV[0] ;
#}else{
##1枚差し
#	$recdevice = "/dev/video0";
#	$recch = $ARGV[0] ;
#}

$outputpath = "$recfolderpath"."/";

if ($countno eq "0"){
	$outputfile = $outputpath.$tid."--";
}else{
	$outputfile = $outputpath.$tid."-".$countno."-";
}
#2番目以降のクリップでファイル名指定があったら
	if ($filename  ne ""){

		$outputfile = $filename ;
		$outputfile = &filenameinjectioncheck($outputfile);
		$outputfilewithoutpath = $outputfile ;
		$outputfile = $outputpath.$outputfile ;
		&writelog("digitaltvrecording: DEBUG FILENAME ne null \$outputfile $outputfile ");
	}else{
	$outputfile .= strftime("%Y%m%d-%H%M", localtime(time + 60));
		chomp($outputfile);
		$outputfile .= ".m2t";
		$outputfilewithoutpath = $outputfile ;
		&writelog("digitaltvrecording:  DEBUG FILENAME is null \$outputfile $outputfile ");
	}


@wday_name = ("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
$sleepcounter = 0;
$cmd="";

#二重録りなど既に同名ファイルがあったら中断
if ( -e "$outputfile" ){
	if ( -s "$outputfile" ){
	&writelog("digitaltvrecording :ABORT :recfile $outputfile exist.");
	exit 1;
	}
}

}#end prepare

sub calldigitalrecorderOld{
#
#
#いまんところ白friioと黒friioのみ
#
#
my $oserr = 0;

if ($bandtype == 0){
# 地デジ friio
# b25,recfriioがあるか確認
	if ((-e "$toolpath/perl/tool/b25") && (-e "$toolpath/perl/tool/recfriio")){
	my $friiofifo = "$outputpath"."fifo-friio-"."$outputfilewithoutpath";
	my $b25fifo = "$outputpath"."fifo-b25-"."$outputfilewithoutpath";
	
		if ((-e "$friiofifo") || (-e "$b25fifo")){
			&writelog("digitaltvrecording :ABORT :fifo is exist. It may be overwrite recording.");
			exit 1;
		}else{ 
		system ("mkfifo $friiofifo $b25fifo");
		# mkfifo fifo-friio-9999-01-20080810.m2t  fifo-b25-9999-01-20080810.m2t 
		&writelog("digitaltvrecording DEBUG mkfifo $friiofifo $b25fifo: $?.");
	# /home/foltia/perl/recfriio 27 30 ./fifo-friio-9999-01-20080810.m2t & /home/foltia/perl/b25 ./fifo-friio-9999-01-20080810.m2t  ./fifo-b25-9999-01-20080810.m2t & dd if=./fifo-b25-9999-01-20080810.m2t  of=/home/foltia/php/tv/9999-01-20080810.m2t bs=1M skip=10
#		system("$toolpath/perl/tool/recfriio $recch $reclengthsec $friiofifo & ");
#		system("$toolpath/perl/tool/b25 $friiofifo $b25fifo &");
#		system("dd if=$b25fifo  of=$outputfile bs=1M skip=10");
		&writelog("digitaltvrecording DEBUG $toolpath/perl/tool/recfriio $recch $reclengthsec $friiofifo & dd if=$friiofifo  of=$b25fifo bs=1M skip=10 & $toolpath/perl/tool/b25 $b25fifo $outputfile: $?.");
		system("dd if=$friiofifo  of=$b25fifo bs=1M skip=10 & $toolpath/perl/tool/b25 $b25fifo $outputfile &");
		$oserr = system("$toolpath/perl/tool/recfriio $recch $reclengthsec $friiofifo  ");
		$oserr = $oserr >> 8;
		system ("rm -rf $friiofifo $b25fifo");
		&writelog("digitaltvrecording DEBUG rm -rf $friiofifo $b25fifo: $?.");
			if ($oserr > 0){
			# 		print "RECFRIIO RETURNS:$oserr\n";
			&writelog("digitaltvrecording :ERROR :friio is BUSY.");
			# kill dd
			$ddpid = `ps a | grep $friiofifo | grep -v grep`;
			@ddpid = split(/ /,$ddpid);
			$ddpid = $ddpid[0];
			chomp($ddpid);
			$killcmd = "kill ".$ddpid;
			system($killcmd);
			&writelog("digitaltvrecording :DEBUG dd killed:$killcmd");

			#kill b25
			$b25pid = `ps a | grep $b25fifo |   grep -v grep`;
			@b25pid = split(/ /,$b25pid);
			$b25pid = $b25pid[0];
			chomp($b25pid);
			$killcmd = "kill ".$b25pid;
			system($killcmd);
			&writelog("digitaltvrecording :DEBUG b25 killed:$killcmd");

			system ("rm -rf $outputfile");

			exit 2;
			}
		}
	}else{ # エラー b25とrecfriioがありません
		&writelog("digitaltvrecording :ERROR :recfriio or b25 not found. You must install $toolpath/perl/tool/b25 and $toolpath/perl/tool/recfriio.");
	exit 1;
	}

}elsif($bandtype == 1){

# BS/CS friio
# b25,recfriioがあるか確認
	if ((-e "$toolpath/perl/tool/b25") && (-e "$toolpath/perl/tool/recfriiobs")){
	my $friiofifo = "$outputpath"."fifo-friioBS-"."$outputfilewithoutpath";
	my $b25fifo = "$outputpath"."fifo-b25-"."$outputfilewithoutpath";
	
		if ((-e "$friiofifo") || (-e "$b25fifo")){
			&writelog("digitaltvrecording :ABORT :fifo is exist. It may be overwrite recording.");
			exit 1;
		}else{ 
		system ("mkfifo $friiofifo $b25fifo");
		&writelog("digitaltvrecording DEBUG mkfifo $friiofifo $b25fifo: $?.");
		#recfriiobs用チャンネルリマップ
		if ($recch == 101) {
			$bssplitflag = $recch;
			$recch = 22;#22 : NHK BS1/BS2 
		}elsif($recch == 102){
			$bssplitflag = $recch;
			$recch = 22;#22 : NHK BS1/BS2 
		}elsif($recch == 103){
			$recch = 23;#23 : NHK hi  
		}elsif($recch == 141){
			$recch = 20;# 20 : BS-NTV  
		}elsif($recch == 151){
			$recch = 13;#13 : BS-Asahi 
		}elsif($recch == 161){
			$recch = 14;#14 : BS-i  
		}elsif($recch == 171){
			$recch = 16;#16 : BS-Japan 
		}elsif($recch == 181){
			$recch = 21;#21 : BS-Fuji 
		}elsif($recch == 191){
			$recch = 15;#15 : WOWOW 
		}elsif($recch == 192){
			$recch = 15;#15 : WOWOW 
		}elsif($recch == 193){
			$recch = 15;#15 : WOWOW 
		}elsif($recch == 211){
			$recch = 17;#17 : BS11  
		}else{
			$recch = 19;#19 : TwellV 
		}
		&writelog("digitaltvrecording DEBUG $toolpath/perl/tool/recfriiobs $recch $reclengthsec $friiofifo & dd if=$friiofifo  of=$b25fifo bs=1M skip=10 & $toolpath/perl/tool/b25 $b25fifo $outputfile : $?.");
		system("dd if=$friiofifo  of=$b25fifo bs=1M skip=10 & $toolpath/perl/tool/b25 $b25fifo $outputfile  &");
		$oserr = system("$toolpath/perl/tool/recfriiobs $recch $reclengthsec $friiofifo  ");
		$oserr = $oserr >> 8;

		system ("rm -rf $friiofifo $b25fifo");
		&writelog("digitaltvrecording DEBUG rm -rf $friiofifo $b25fifo: $?.");
			if ($oserr > 0){
			# 		print "RECFRIIO RETURNS:$oserr\n";
			&writelog("digitaltvrecording :ERROR :friioBS is BUSY.");
			# kill dd
			$ddpid = `ps a | grep $friiofifo | grep -v grep`;
			@ddpid = split(/ /,$ddpid);
			$ddpid = $ddpid[0];
			chomp($ddpid);
			$killcmd = "kill ".$ddpid;
			system($killcmd);
			&writelog("digitaltvrecording :DEBUG dd killed:$killcmd");

			#kill b25
			$b25pid = `ps a | grep $b25fifo |   grep -v grep`;
			@b25pid = split(/ /,$b25pid);
			$b25pid = $b25pid[0];
			chomp($b25pid);
			$killcmd = "kill ".$b25pid;
			system($killcmd);
			&writelog("digitaltvrecording :DEBUG b25 killed:$killcmd");

			system ("rm -rf $outputfile");

			exit 2;
			}
		
		#BS1/BS2などのスプリットを
		if ($bssplitflag == 101){
			if (-e "$toolpath/perl/tool/TsSplitter.exe"){
			# BS1		
			system("wine $toolpath/perl/tool/TsSplitter.exe  -EIT -ECM  -EMM  -OUT \"$outputpath\" -HD  -SD2 -SD3 -1SEG  -LOGFILE -WAIT2 $outputfile");
			$splitfile = $outputfile;
			$splitfile =~ s/\.m2t$/_SD1.m2t/;
				if (-e "$splitfile"){
				system("rm -rf $outputfile ; mv $splitfile $outputfile");
				&writelog("digitaltvrecording DEBUG rm -rf $outputfile ; mv $splitfile $outputfile: $?.");
				}else{
				&writelog("digitaltvrecording ERROR File not found:$splitfile.");
				}
			}else{
			&writelog("digitaltvrecording ERROR $toolpath/perl/tool/TsSplitter.exe not found.");
			}
		}elsif($bssplitflag == 102){
			if (-e "$toolpath/perl/tool/TsSplitter.exe"){
			# BS2		
			system("wine $toolpath/perl/tool/TsSplitter.exe  -EIT -ECM  -EMM  -OUT \"$outputpath\" -HD  -SD1 -SD3 -1SEG  -LOGFILE -WAIT2 $outputfile");
			$splitfile = $outputfile;
			$splitfile =~ s/\.m2t$/_SD2.m2t/;
				if (-e "$splitfile"){
				system("rm -rf $outputfile ; mv $splitfile $outputfile");
				&writelog("digitaltvrecording DEBUG rm -rf $outputfile ; mv $splitfile $outputfile: $?.");
				}else{
				&writelog("digitaltvrecording ERROR File not found:$splitfile.");
				}
			}else{
			&writelog("digitaltvrecording ERROR $toolpath/perl/tool/TsSplitter.exe not found.");
			}
		}else{
			&writelog("digitaltvrecording DEBUG not split TS.$bssplitflag");
		}# endif #BS1/BS2などのスプリットを
		
		}
	}else{ # エラー b25とrecfriioがありません
		&writelog("digitaltvrecording :ERROR :recfriiobs or b25 not found. You must install $toolpath/perl/tool/b25 and $toolpath/perl/tool/recfriiobs.");
	exit 1;
	}
}elsif($bandtype == 2){
}else{
	&writelog("digitaltvrecording :ERROR :Unsupported and type (digital CS).");
	exit 3;
}



}#end calldigitalrecorderOld
#------------------------------------------------------------------------------------
#
sub calldigitalrecorder{
#
#白friioと黒friio、PT1対応
#2008/10/23 recfriio4仕様に変更 
#
my $oserr = 0;
my $originalrecch = $recch;
my $errorflag = 0;
if ($bandtype == 0){
# 地デジ friio
}elsif($bandtype == 1){
# BS/CS friio
		#recfriiobs用チャンネルリマップ
		if ($recch == 101) {
			$bssplitflag = $recch;
			$recch = "b10";#22 : NHK BS1/BS2 
		}elsif($recch == 102){
			$bssplitflag = $recch;
			$recch = "b10";#22 : NHK BS1/BS2 
		}elsif($recch == 103){
			$recch = "b11";#23 : NHK hi  
		}elsif($recch == 141){
			$recch = "b8";# 20 : BS-NTV  
		}elsif($recch == 151){
			$recch = "b1";#13 : BS-Asahi 
		}elsif($recch == 161){
			$recch = "b2";#14 : BS-i  
		}elsif($recch == 171){
			$recch = "b4";#16 : BS-Japan 
		}elsif($recch == 181){
			$recch = "b9";#21 : BS-Fuji 
		}elsif($recch == 191){
			$recch = "b3";#15 : WOWOW 
		}elsif($recch == 192){
			$recch = "b3";#15 : WOWOW 
		}elsif($recch == 193){
			$recch = "b3";#15 : WOWOW 
		}elsif($recch == 211){
			$recch = "b5";#17 : BS11  
		}else{
			$recch = "b7";#19 : TwellV 
		}
		# b6 # Star Channel

}elsif($bandtype == 2){
# 110度CSよくわかんない
}else{
	&writelog("digitaltvrecording :ERROR :Unsupported and type (digital CS).");
	exit 3;
}

# PT1
# b25,recpt1があるか確認
	if  (-e "$toolpath/perl/tool/recpt1"){
		&writelog("digitaltvrecording DEBUG recpt1 --b25  $originalrecch $reclengthsec $outputfile  ");
		$oserr = system("$toolpath/perl/tool/recpt1 --b25  $originalrecch $reclengthsec $outputfile  ");
		$oserr = $oserr >> 8;
			if ($oserr > 0){
			&writelog("digitaltvrecording :ERROR :PT1 is BUSY.$oserr");
			$errorflag = 2;
			}
	}else{ # エラー recpt1がありません
		&writelog("digitaltvrecording :ERROR :recpt1  not found. You must install $toolpath/tool/b25 and $toolpath/tool/recpt1.");
	$errorflag = 1;
	}
# friio
if ($errorflag >= 1 ){
# b25,recfriioがあるか確認
	if  (-e "$toolpath/perl/tool/recfriio"){
	
	if (! -e "$toolpath/perl/tool/friiodetect"){
		system("touch $toolpath/perl/tool/friiodetect");
		system("chown foltia:foltia $toolpath/perl/tool/friiodetect");
		system("chmod 775 $toolpath/perl/tool/friiodetect");
		&writelog("digitaltvrecording :DEBUG make lock file.$toolpath/perl/tool/friiodetect");
	}
		&writelog("digitaltvrecording DEBUG recfriio --b25 --lockfile $toolpath/perl/tool/friiodetect $recch $reclengthsec $outputfile  ");
		$oserr = system("$toolpath/perl/tool/recfriio --b25 --lockfile $toolpath/perl/tool/friiodetect $recch $reclengthsec $outputfile  ");
		$oserr = $oserr >> 8;
			if ($oserr > 0){
			&writelog("digitaltvrecording :ERROR :friio is BUSY.$oserr");
			exit 2;
			}
	}else{ # エラー recfriioがありません
		&writelog("digitaltvrecording :ERROR :recfriio  not found. You must install $toolpath/perl/tool/b25 and $toolpath/perl/tool/recfriio.");
	exit 1;
	}
}#end if errorflag

#BS1/BS2などのスプリットを
if ($bssplitflag == 101){
	if (-e "$toolpath/perl/tool/TsSplitter.exe"){
	# BS1		
	system("wine $toolpath/perl/tool/TsSplitter.exe  -EIT -ECM  -EMM  -OUT \"$outputpath\" -HD  -SD2 -SD3 -1SEG  -LOGFILE -WAIT2 $outputfile");
	$splitfile = $outputfile;
	$splitfile =~ s/\.m2t$/_SD1.m2t/;
		if (-e "$splitfile"){
		system("rm -rf $outputfile ; mv $splitfile $outputfile");
		&writelog("digitaltvrecording DEBUG rm -rf $outputfile ; mv $splitfile $outputfile: $?.");
		}else{
		&writelog("digitaltvrecording ERROR File not found:$splitfile.");
		}
	}else{
	&writelog("digitaltvrecording ERROR $toolpath/perl/tool/TsSplitter.exe not found.");
	}
}elsif($bssplitflag == 102){
	if (-e "$toolpath/perl/tool/TsSplitter.exe"){
	# BS2		
	system("wine $toolpath/perl/tool/TsSplitter.exe  -EIT -ECM  -EMM  -OUT \"$outputpath\" -HD  -SD1 -SD3 -1SEG  -LOGFILE -WAIT2 $outputfile");
	$splitfile = $outputfile;
	$splitfile =~ s/\.m2t$/_SD2.m2t/;
		if (-e "$splitfile"){
		system("rm -rf $outputfile ; mv $splitfile $outputfile");
		&writelog("digitaltvrecording DEBUG rm -rf $outputfile ; mv $splitfile $outputfile: $?.");
		}else{
		&writelog("digitaltvrecording ERROR File not found:$splitfile.");
		}
	}else{
	&writelog("digitaltvrecording ERROR $toolpath/perl/tool/TsSplitter.exe not found.");
	}
}else{
	&writelog("digitaltvrecording DEBUG not split TS.$bssplitflag");
}# endif #BS1/BS2などのスプリットを

}#end calldigitalrecorder


