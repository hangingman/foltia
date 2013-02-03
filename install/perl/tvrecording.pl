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
# tvrecording.pl
# record-v4l2.plを呼びだす録画モジュール。
#
# usage tvrecording.pl ch length(sec) [clip No(000-)] [filename] [bitrate(5)] [TID] [NO] [/dev/video0]
# 引数
# ch :録画チャンネル　0だとS入力、-1だとコンポジット入力 [必須項目]
# length(sec) :録画秒数 [必須項目]
# [sleeptype] :0かN Nならスリープなしで録画
# [filename] :出力ファイル名
# [bitrate(5)] :ビットレート　Mbps単位で指定
# [TID] :しょぼかるタイトルID
# [NO] :その番組の放送話数
# [/dev/video0]:キャプチャデバイス
#
$path = $0;
$path =~ s/tvrecording.pl$//i;
if ($path ne "./") {
  push( @INC, "$path");
}

#
#キャプチャカード枚数検出
#cat /proc/interrupts  | grep ivtv |wc -l
# 11:    1054118          XT-PIC  uhci_hcd, eth0, ivtv0, ivtv1, ivtv2
#失敗

#tvConfig.pl -------------------------------
$extendrecendsec = 10;		#recording end second. 
#$startupsleeptime = 52;	#process wait(MAX60sec)
$startupsleeptime = 37;		#process wait(MAX60sec)


#-------------------------------

require 'foltialib.pl';

&writelog("tvrecording:  DEBUG $ARGV[0] $ARGV[1] $ARGV[2] $ARGV[3] $ARGV[4] $ARGV[5] $ARGV[6]  $ARGV[7] ");

sub getChCallsign {
  if ($ARGV[5]  ne "") {
    $recchname = $ARGV[5] ;
  } else {
    $recchname = $recch."ch";
  }

}				#endsub getChCallsign

sub getRecPath{			#capture directory define
  $recfolderpath = '/home/foltia/php/tv';		
}		      #end sub getRecPath
#
# -- ここからメイン ----------------------------
#準備
&prepare;
#もし録画が走ってたら、止める
$reclengthsec = &chkrecprocess();
&setbitrate;
&chkextinput;

$reclengthsec = $reclengthsec + $extendrecendsec ;

&callrecordv4l;

&writelog("tvrecording:$recch:$reclengthsec:$outputfile:$recdevice:$capturedeviceinputnum:$ivtvrecch:$stdbitrate:$peakbitrate");

# -- これ以下サブルーチン ----------------------------
sub chkextinput{

  if ($recch == 0) {
    if ($svideoinputnum > -1 && $svideoinputnum < 30) {
      $capturedeviceinputnum = $svideoinputnum ;
    } else {
      $capturedeviceinputnum = 7 ;
    }
    $capturedeviceinputName = "S-Video 1";
    $ivtvrecch = '';
  } elsif ($recch == -1) {
    if ($comvideoinputnum > -1 && $comvideoinputnum < 30) {
      $capturedeviceinputnum = $comvideoinputnum;
    } else {
      $capturedeviceinputnum = 8;
    }
    $capturedeviceinputName = "Composite 1";
    $ivtvrecch = '';
  } else {
    if ($tunerinputnum > -1 && $tunerinputnum < 30) {
      $capturedeviceinputnum = $tunerinputnum ;
    } else {
      $capturedeviceinputnum = 6 ;
    }
    $capturedeviceinputName = "Tuner 1";
    $ivtvrecch = $recch;
  }
  # 1-12chはntsc-bcast-jp
  if ($recch > 12) {
    if ($uhfbandtype == 1) {
      $frequencyTable = "ntsc-cable-jp";
    } else {
      $frequencyTable = "ntsc-bcast-jp";
    }
  } else {
    $frequencyTable = "ntsc-bcast-jp";
  }				#if
  &writelog ("tvrecording DEBUG $frequencyTable $recch");

}				#chkextinput



sub chkrecprocessOLD{
  #もし録画が走ってたら、止める
  my $mencoderkillcmd = "";

  $mencoderkillcmd =`/usr/sbin/lsof -Fp $recdevice`;
  $mencoderkillcmd =~ s/p//;

  if ($mencoderkillcmd != "") {
    #kill process
    $mencoderkillcmd  = "kill ".$mencoderkillcmd;
    system ($mencoderkillcmd);
    chomp($mencoderkillcmd);
    &writelog ("tvrecording Killed current recording process. process:$mencoderkillcmd");
    sleep(1);
    my $videodevice =`/usr/sbin/lsof $recdevice`;

    while ($videodevice =~ /tvrecording/) {

      $videodevice =`/usr/sbin/lsof $recdevice`;
      sleep(1);
      $sleepcounter++;
      $reclengthsec = $reclengthsec - $sleepcounter;
      &writelog ("tvrecording videodevice wait:$sleepcounter");
    }
    $sleepcounter = 0;		
  }				#if ($mencoderkillcmd != "")

  return $reclengthsec;

}				#end chkrecprocess

sub chkrecprocess{
  my $mencoderkillcmd = "";
  my $j = $recunits -1;
  my $i = 0;
  my $testrecdevice = "";
  my @usedevices  ;
  my @unusedevices;
  my $n = 0;
  $recdevice = "";
  if ($ARGV[7]  ne "") {
    $recdevice =  $ARGV[7] ;
  }

  #for ($i = $j ;$i >= 0 ; $i--){
  for ($i = 0 ;$i <= $j ; $i++) {
    #print "$i,$j\n";
    $testrecdevice = "/dev/video$i";
    $mencoderkillcmd =`/usr/sbin/lsof -Fp $testrecdevice`;
    $mencoderkillcmd =~ s/p//;
    if ($mencoderkillcmd != "") {
      push (@usedevices ,  $testrecdevice);
      &writelog ("tvrecording now using:$testrecdevice");
    } else {
      push (@unusedevices ,  $testrecdevice);
      &writelog ("tvrecording unused:$testrecdevice");
    }				#if
  }				#for

  $i = 0;			#初期化
  $n = @unusedevices;
  #デバイス指定があるか?
  if ($recdevice  ne "") {	#指定があったら
    #そこが使われているかチェック
    $mencoderkillcmd =`/usr/sbin/lsof -Fp $recdevice`;
    $mencoderkillcmd =~ s/p//;
    if ($mencoderkillcmd != "") { #使われてたら無条件に落とす
      $mencoderkillcmd  = "kill ".$mencoderkillcmd;
      system ($mencoderkillcmd);
      chomp($mencoderkillcmd);
      &writelog ("tvrecording Killed current recording process. $recdevice:$mencoderkillcmd");
      sleep(1);
    }
  } else {
    #地上波or 指定なしなら
    if (($n == 0) and ($recch > 0)) { #空きデバイスがなくて、地上波なら	
      $mencoderkillcmd =`/usr/sbin/lsof -Fp /dev/video$i`; #→$i
      $mencoderkillcmd =~ s/p//;
      if ($mencoderkillcmd != "") { #使われてたら最高位/dev/video$j を無条件に落とす →最低位$i
	$mencoderkillcmd  = "kill ".$mencoderkillcmd;
	system ($mencoderkillcmd);
	chomp($mencoderkillcmd);
	&writelog ("tvrecording Killed current recording process. /dev/video$i:$mencoderkillcmd");
	sleep(1);
      }
      $recdevice = "/dev/video$i"; #→最低位$i
      &writelog ("tvrecording select device:$recdevice");

    } elsif ($recch <= 0) {	# 外部入力なら
      #外部入力だけどデバイス指定されていないときも
      #落とす
      $mencoderkillcmd =`/usr/sbin/lsof -Fp /dev/video$j`; #
      $mencoderkillcmd =~ s/p//;
      if ($mencoderkillcmd != "") { #使われてたら最高位/dev/video$j を無条件に落とす
	$mencoderkillcmd  = "kill ".$mencoderkillcmd;
	system ($mencoderkillcmd);
	chomp($mencoderkillcmd);
	&writelog ("tvrecording Killed current recording process. /dev/video$j:$mencoderkillcmd");
	sleep(1);
      }
      $recdevice = "/dev/video$j"; #　外部入力は最高位デバイス
    } else {
      #空きを使う
      $recdevice = shift(@unusedevices );
    }				#endif 空きデバイスなければ

  }				#end if 指定あるか

  #ここには落ちてこないはずなのに?
  if ($recdevice eq "") {
    $recdevice = "/dev/video0";
    &writelog ( "Rec Device un defined. / $recch ");
  }
  return $reclengthsec;

}				#end chkrecprocessNew



sub prepare{

  #引数エラー処理
  $recch = $ARGV[0] ;
  $reclengthsec = $ARGV[1];
  if (($recch eq "" )|| ($reclengthsec eq "")) {
    print "usage tvrecording.pl ch length(sec) [clip No(000-)] [filename] [bitrate(5)] [TID] [NO] [/dev/video0]\n";
    exit;
  }
  #1分前にプロセス起動するから指定時間スリープ
  #srand(time ^ ($$ + ($$ << 15)));
  #my $useconds  = int(rand(12000000));
  #my $intval = int ($useconds  / 1000000);
  #my $startupsleeptimemicro = ($startupsleeptime * 1000000) - $useconds;
  #$reclengthsec = $reclengthsec + $intval + 1;
  #&writelog("tvrecording:  DEBUG SLEEP $startupsleeptime:$useconds:$intval:$startupsleeptimemicro");
  #	usleep ( $startupsleeptimemicro );

  # $recch でウェイト調整入れましょう
  #52
  #my $intval = $recch % 50; # 0〜49
  #my $startupsleep = $startupsleeptime - $intval; #  3〜52 (VHF 40-51)
  #37
  my $intval = $recch % 35;			# 0〜34
  my $startupsleep = $startupsleeptime - $intval; #  3-37 (VHF 25-36,tvk 30)
  $reclengthsec = $reclengthsec + (60 - $startupsleep) + 1; #

  if ( $ARGV[2] ne "N") {
    &writelog("tvrecording: DEBUG SLEEP $startupsleeptime:$intval:$startupsleep:$reclengthsec");
    sleep ( $startupsleep);
  } else {
    &writelog("tvrecording: DEBUG RAPID START");

  }
  if ($recunits > 1) {
    my $deviceno = $recunits - 1; #3枚差しのとき/dev/video2から使う
    $recdevice = "/dev/video$deviceno";
    $recch = $ARGV[0] ;
  } else {
    #1枚差し
    $recdevice = "/dev/video0";
    $recch = $ARGV[0] ;
  }

  &getChCallsign();
  #&getRecPath;

  $outputpath = "$recfolderpath"."/";

  if ($ARGV[6] eq "0") {
    $outputfile = $outputpath.$ARGV[5]."--";
  } else {
    $outputfile = $outputpath.$ARGV[5]."-".$ARGV[6]."-";
  }
  #2番目以降のクリップでファイル名指定があったら
  if ($ARGV[3]  ne "") {
    #		if ($ARGV[3] =~ /[0-9]{8}-[0-9]{4}/){
    #		$outputfile .= "$ARGV[3]";
    #		}else{
    #		$outputfile .= strftime("%Y%m%d-%H%M", localtime(time + 60));
    #		}
    $outputfile = $ARGV[3];
    $outputfile = &filenameinjectioncheck($outputfile);
    $outputfilewithoutpath = $outputfile ;
    $outputfile = $outputpath.$outputfile ;
    #		$outputfile .= "$ARGV[3]";		
    #		$outputfile .= strftime("%Y%m%d-%H%M", localtime(time + 60));
    &writelog("tvrecording:  DEBUG ARGV[2] ne null  \$outputfile $outputfile ");
  } else {
    $outputfile .= strftime("%Y%m%d-%H%M", localtime(time + 60));
    chomp($outputfile);
    $outputfile .= ".m2p";
    $outputfilewithoutpath = $outputfile ;
    &writelog("tvrecording:  DEBUG ARGV[2] is null  \$outputfile $outputfile ");
  }


  @wday_name = ("Sun","Mon","Tue","Wed","Thu","Fri","Sat");
  $sleepcounter = 0;
  $cmd="";

  #二重録りなど既に同名ファイルがあったら中断
  if ( -e "$outputfile" ) {
    if ( -s "$outputfile" ) {
      &writelog("tvrecording :ABORT :recfile $outputfile exist.");
      exit 1;
    }
  }

}				#end prepare

sub setbitrate{
  $bitrate = $ARGV[4] ;
  $bitrate = $bitrate * 1024*1024; #Mbps -> bps
  $peakbitrate = $bitrate + 350000;
  $recordbitrate = "  --bitrate $bitrate --peakbitrate $peakbitrate ";
  $stdbitrate = "$bitrate";
  $peakbitrate = "$peakbitrate";
}				#end setbitrate


sub callrecordv4l{

  #$frequency = `ivtv-tune -d $recdevice -t $frequencyTable -c $ivtvrecch | awk '{print $2}'|tr -d .`;
  my $ivtvtuneftype = '';
  if ($frequencyTable eq "ntsc-cable-jp") {
    $ivtvtuneftype = 'japan-cable';
  } else {
    $ivtvtuneftype = 'japan-bcast';
  }
  #print "ivtv-tune -d $recdevice -t $ivtvtuneftype -c $ivtvrecch\n";
  &writelog("tvrecording DEBUG ivtv-tune -d $recdevice -t $ivtvtuneftype -c $ivtvrecch");
  &writelog("tvrecording DEBUG $ENV{PATH}");

  $frequency = `env PATH=PATH=/usr/kerberos/bin:/usr/lib/ccache:/usr/local/bin:/bin:/usr/bin:/home/foltia/bin ivtv-tune -d $recdevice -t $ivtvtuneftype -c $ivtvrecch`;
  &writelog("tvrecording DEBUG frequency:$frequency");
  @frequency = split(/\s/,$frequency);
  $frequency[1] =~ s/\.//gi;
  $frequency = $frequency[1] ;
  &writelog("tvrecording DEBUG frequency:$frequency");

  my $recordv4lcallstring = "$toolpath/perl/record-v4l2.pl --frequency $frequency --duration $reclengthsec --input $recdevice --directory $recfolderpath --inputnum $capturedeviceinputnum --inputname '$capturedeviceinputName' --freqtable $frequencyTable --bitrate $stdbitrate --peakbitrate $peakbitrate --output $outputfilewithoutpath ";

  &writelog("tvrecording $recordv4lcallstring");
  &writelog("tvrecording DEBUG $ENV{HOME}/.ivtvrc");
  $oserr = `env HOME=$toolpath $recordv4lcallstring`;
  &writelog("tvrecording DEBUG $oserr");

}				#end callrecordv4l

