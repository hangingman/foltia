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
# digitaltvrecording.pl
# PT1,PT2,friioをはじめとするデジタル録画プログラムを呼びだす録画モジュール。
#
# usage digitaltvrecording.pl bandtype ch length(sec) [stationid] [sleeptype] [filename] [TID] [NO] [unittype]
# 引数
# bandtype : 0:地デジ 1:BSデジタル 2:CSデジタル
# ch :録画チャンネル (地デジはそのまま渡す、BS/CSデジタルは基本的にチャンネル BS1/BS2など同じ数時に)
# length(sec) :録画秒数 [必須項目]
# [stationid] :foltia stationid
# [sleeptype] :0かN Nならスリープなしで録画
# [filename] :出力ファイル名
# [TID] :しょぼかるタイトルID
# [NO] :その番組の放送話数
# [unittype] :friioかfriioBSかユニデンチューナかHDUSかなど(未使用)

$path = $0;
$path =~ s/digitaltvrecording.pl$//i;
if ($path ne "./") {
  push( @INC, "$path");
}

#tvConfig.pl -------------------------------
$extendrecendsec = 10;		#recording end second. 
#$startupsleeptime = 52;        #process wait(MAX60sec)
$startupsleeptime = 32;		#process wait(MAX60sec)
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

  if (($bandtype eq "" )|| ($recch eq "")|| ($lengthsec eq "")) {
    print "usage digitaltvrecording.pl bandtype ch length(sec) [stationid] [sleeptype] [filename] [TID] [NO] [unittype]\n";
    exit;
  }

  my $intval = $recch % 10;			       # 0〜9 sec
  my $startupsleep = $startupsleeptime - $intval;      #  18〜27 sec
  $reclengthsec = $lengthsec + (60 - $startupsleep) + 1; #

  if ( $sleeptype ne "N") {
    &writelog("digitaltvrecording: DEBUG SLEEP $startupsleeptime:$intval:$startupsleep:$reclengthsec");
    sleep ( $startupsleep);
    #2008/08/12_06:39:00 digitaltvrecording: DEBUG SLEEP 17:23:-6:367
  } else {
    &writelog("digitaltvrecording: DEBUG RAPID START");
  }

  $outputpath = "$recfolderpath"."/";

  if ($countno eq "0") {
    $outputfile = $outputpath.$tid."--";
  } else {
    $outputfile = $outputpath.$tid."-".$countno."-";
  }
  #2番目以降のクリップでファイル名指定があったら
  if ($filename  ne "") {

    $outputfile = $filename ;
    $outputfile = &filenameinjectioncheck($outputfile);
    $outputfilewithoutpath = $outputfile ;
    $outputfile = $outputpath.$outputfile ;
    &writelog("digitaltvrecording: DEBUG FILENAME ne null \$outputfile $outputfile ");
  } else {
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
  if ( -e "$outputfile" ) {
    if ( -s "$outputfile" ) {
      &writelog("digitaltvrecording :ABORT :recfile $outputfile exist.");
      exit 1;
    }
  }

}				#end prepare

#------------------------------------------------------------------------------------
#
sub calldigitalrecorder{
  #
  #白friioと黒friio、PT1対応
  #2008/10/23 recfriio4仕様に変更 
  #
  my $oserr = 0;
  my $originalrecch = $recch;
  my $pt1recch =  $recch;
  my $errorflag = 0;
  if ($bandtype == 0) {
    # 地デジ friio

  } elsif ($bandtype == 1) {
    # BS/CS friio
    #recfriiobs用チャンネルリマップ
    if ($recch == 101) {
      $bssplitflag = $recch;
      $recch = "b10";		#22 : NHK BS1/BS2 
    } elsif ($recch == 102) {
      $bssplitflag = $recch;
      $recch = "b10";		#22 : NHK BS1/BS2 
    } elsif ($recch == 103) {
      $recch = "b11";		#23 : NHK hi  
    } elsif ($recch == 141) {
      $recch = "b8";		# 20 : BS-NTV  
    } elsif ($recch == 151) {
      $recch = "b1";		#13 : BS-Asahi 
    } elsif ($recch == 161) {
      $recch = "b2";		#14 : BS-i  
    } elsif ($recch == 171) {
      $recch = "b4";		#16 : BS-Japan 
    } elsif ($recch == 181) {
      $recch = "b9";		#21 : BS-Fuji 
    } elsif ($recch == 191) {
      $recch = "b3";		#15 : WOWOW 
    } elsif ($recch == 192) {
      $recch = "b3";		#15 : WOWOW 
    } elsif ($recch == 193) {
      $recch = "b3";		#15 : WOWOW 
    } elsif ($recch == 200) {
      $recch = "b6";		# b6 # Star Channel
    } elsif ($recch == 211) {
      $recch = "b5";		#17 : BS11  
    } else {
      $recch = "b7";		#19 : TwellV 
    }
    #PT1はそのまま通る

  } elsif ($bandtype == 2) {
    # recpt1でのみ動作確認
    if ($recch == 335) {
      $pt1recch = "CS8";	#335ch：キッズステーション HD
    } elsif ($recch == 237) {
      $pt1recch = "CS2";	#237ch：スター・チャンネル プラス
    } elsif ($recch == 239) {
      $pt1recch = "CS2";	#239ch：日本映画専門チャンネルHD
    } elsif ($recch == 306) {
      $pt1recch = "CS2";	#306ch：フジテレビCSHD
    } elsif ($recch == 100) {
      $pt1recch = "CS4";	#100ch：e2プロモ
    } elsif ($recch == 256) {
      $pt1recch = "CS4";	#256ch：J sports ESPN
    } elsif ($recch == 312) {
      $pt1recch = "CS4";	#312ch：FOX
    } elsif ($recch == 322) {
      $pt1recch = "CS4";	#322ch：スペースシャワーTV
    } elsif ($recch == 331) {
      $pt1recch = "CS4";	#331ch：カートゥーンネットワーク
    } elsif ($recch == 194) {
      $pt1recch = "CS4";	#194ch：インターローカルTV
    } elsif ($recch == 334) {
      $pt1recch = "CS4";	#334ch：トゥーン・ディズニー
    } elsif ($recch == 221) {
      $pt1recch = "CS6";	#221ch：東映チャンネル 
    } elsif ($recch == 222) {
      $pt1recch = "CS6";	#222ch：衛星劇場
    } elsif ($recch == 223) {
      $pt1recch = "CS6";	#223ch：チャンネルNECO
    } elsif ($recch == 224) {
      $pt1recch = "CS6";	#224ch：洋画★シネフィル・イマジカ
    } elsif ($recch == 292) {
      $pt1recch = "CS6";	#292ch：時代劇専門チャンネル
    } elsif ($recch == 238) {
      $pt1recch = "CS6";	#238ch：スター・チャンネル クラシック
    } elsif ($recch == 310) {
      $pt1recch = "CS6";	#310ch：スーパー！ドラマTV
    } elsif ($recch == 311) {
      $pt1recch = "CS6";	#311ch：AXN
    } elsif ($recch == 343) {
      $pt1recch = "CS6";  #343ch：ナショナルジオグラフィックチャンネル
    } elsif ($recch == 055) {
      $pt1recch = "CS8";	#055ch：ショップ チャンネル
    } elsif ($recch == 228) {
      $pt1recch = "CS10";	#228ch：ザ・シネマ
    } elsif ($recch == 800) {
      $pt1recch = "CS10";	#800ch：スカチャンHD800
    } elsif ($recch == 801) {
      $pt1recch = "CS10";	#801ch：スカチャン801
    } elsif ($recch == 802) {
      $pt1recch = "CS10";	#802ch：スカチャン802
    } elsif ($recch == 260) {
      $pt1recch = "CS12";	#260ch：ザ・ゴルフ・チャンネル
    } elsif ($recch == 303) {
      $pt1recch = "CS12";	#303ch：テレ朝チャンネル
    } elsif ($recch == 323) {
      $pt1recch = "CS12"; #323ch：MTV 324ch：大人の音楽専門TV◆ミュージック・エア
    } elsif ($recch == 352) {
      $pt1recch = "CS12";	#352ch：朝日ニュースター
    } elsif ($recch == 353) {
      $pt1recch = "CS12";	#353ch：BBCワールドニュース
    } elsif ($recch == 354) {
      $pt1recch = "CS12";	#354ch：CNNj
    } elsif ($recch == 361) {
      $pt1recch = "CS12";    #361ch：ジャスト・アイ インフォメーション
    } elsif ($recch == 251) {
      $pt1recch = "CS14";	#251ch：J sports 1
    } elsif ($recch == 252) {
      $pt1recch = "CS14";	#252ch：J sports 2
    } elsif ($recch == 253) {
      $pt1recch = "CS14";	#253ch：J sports Plus
    } elsif ($recch == 254) {
      $pt1recch = "CS14";	#254ch：GAORA
    } elsif ($recch == 255) {
      $pt1recch = "CS14";	#255ch：スカイ・Asports＋
    } elsif ($recch == 305) {
      $pt1recch = "CS16";	#305ch：チャンネル銀河
    } elsif ($recch == 333) {
      $pt1recch = "CS16";	#333ch：アニメシアターX(AT-X)
    } elsif ($recch == 342) {
      $pt1recch = "CS16";	#342ch：ヒストリーチャンネル
    } elsif ($recch == 290) {
      $pt1recch = "CS16";	#290ch：TAKARAZUKA SKYSTAGE
    } elsif ($recch == 803) {
      $pt1recch = "CS16";	#803ch：スカチャン803
    } elsif ($recch == 804) {
      $pt1recch = "CS16";	#804ch：スカチャン804
    } elsif ($recch == 240) {
      $pt1recch = "CS18";	#240ch：ムービープラスHD
    } elsif ($recch == 262) {
      $pt1recch = "CS18";	#262ch：ゴルフネットワーク
    } elsif ($recch == 314) {
      $pt1recch = "CS18";	#314ch：LaLa HDHV
    } elsif ($recch == 258) {
      $pt1recch = "CS20";	#258ch：フジテレビ739
    } elsif ($recch == 302) {
      $pt1recch = "CS20";	#302ch：フジテレビ721
    } elsif ($recch == 332) {
      $pt1recch = "CS20";	#332ch：アニマックス
    } elsif ($recch == 340) {
      $pt1recch = "CS20";	#340ch：ディスカバリーチャンネル
    } elsif ($recch == 341) {
      $pt1recch = "CS20";	#341ch：アニマルプラネット
    } elsif ($recch == 160) {
      $pt1recch = "CS22";	#160ch：C-TBSウェルカムチャンネル
    } elsif ($recch == 161) {
      $pt1recch = "CS22";	#161ch：QVC
    } elsif ($recch == 185) {
      $pt1recch = "CS22";	#185ch：プライム365.TV
    } elsif ($recch == 293) {
      $pt1recch = "CS22";	#293ch：ファミリー劇場
    } elsif ($recch == 301) {
      $pt1recch = "CS22";	#301ch：TBSチャンネル
    } elsif ($recch == 304) {
      $pt1recch = "CS22";	#304ch：ディズニー・チャンネル
    } elsif ($recch == 325) {
      $pt1recch = "CS22";	#325ch：MUSIC ON! TV
      #}elsif($recch == 330){
      #	$pt1recch = "CS22";#330ch：キッズステーション  #HD化により2010/4変更
    } elsif ($recch == 351) {
      $pt1recch = "CS22";	#351ch：TBSニュースバード
    } elsif ($recch == 257) {
      $pt1recch = "CS24";	#ch：日テレG+
    } elsif ($recch == 291) {
      $pt1recch = "CS24";	#ch：fashiontv
    } elsif ($recch == 300) {
      $pt1recch = "CS24";	#ch：日テレプラス
    } elsif ($recch == 320) {
      $pt1recch = "CS24";  #ch：安らぎの音楽と風景／エコミュージックTV
    } elsif ($recch == 321) {
      $pt1recch = "CS24";	#ch：MusicJapan TV
    } elsif ($recch == 350) {
      $pt1recch = "CS24";	#ch：日テレNEWS24
    }				# end if CSリマップ

  } else {
    &writelog("digitaltvrecording :ERROR :Unsupported and type (digital CS).");
    exit 3;
  }

  # PT1
  # b25,recpt1があるか確認
  if (-e "$toolpath/perl/tool/recpt1") {
    if ($bandtype >= 1) {	#BS/CSなら
      &writelog("digitaltvrecording DEBUG recpt1 --b25 --sid $originalrecch  $pt1recch $reclengthsec $outputfile   ");
      $oserr = system("$toolpath/perl/tool/recpt1 --b25 --sid $originalrecch $pt1recch $reclengthsec $outputfile  ");
    } else {			#地デジ
      &writelog("digitaltvrecording DEBUG recpt1 --b25  $originalrecch $reclengthsec $outputfile  ");
      $oserr = system("$toolpath/perl/tool/recpt1 --b25  $originalrecch $reclengthsec $outputfile  ");
    }
    $oserr = $oserr >> 8;
    if ($oserr > 0) {
      &writelog("digitaltvrecording :ERROR :PT1 is BUSY.$oserr");
      $errorflag = 2;
    }
  } else {			# エラー recpt1がありません
    &writelog("digitaltvrecording :ERROR :recpt1  not found. You must install $toolpath/tool/b25 and $toolpath/tool/recpt1.");
    $errorflag = 1;
  }
  # friio
  if ($errorflag >= 1 ) {
    # b25,recfriioがあるか確認
    if (-e "$toolpath/perl/tool/recfriio") {
	
      if (! -e "$toolpath/perl/tool/friiodetect") {
	system("touch $toolpath/perl/tool/friiodetect");
	system("chown foltia:foltia $toolpath/perl/tool/friiodetect");
	system("chmod 775 $toolpath/perl/tool/friiodetect");
	&writelog("digitaltvrecording :DEBUG make lock file.$toolpath/perl/tool/friiodetect");
      }
      &writelog("digitaltvrecording DEBUG recfriio --b25 --lockfile $toolpath/perl/tool/friiodetect $recch $reclengthsec $outputfile  ");
      $oserr = system("$toolpath/perl/tool/recfriio --b25 --lockfile $toolpath/perl/tool/friiodetect $recch $reclengthsec $outputfile  ");
      $oserr = $oserr >> 8;
      if ($oserr > 0) {
	&writelog("digitaltvrecording :ERROR :friio is BUSY.$oserr");
	exit 2;
      }

      #BS1/BS2などのスプリットを
      if ($bssplitflag == 101) {
	if (-e "$toolpath/perl/tool/TsSplitter.exe") {
	  # BS1		
	  system("wine $toolpath/perl/tool/TsSplitter.exe  -EIT -ECM  -EMM  -OUT \"$outputpath\" -HD  -SD2 -SD3 -1SEG  -LOGFILE -WAIT2 $outputfile");
	  $splitfile = $outputfile;
	  $splitfile =~ s/\.m2t$/_SD1.m2t/;
	  if (-e "$splitfile") {
	    system("rm -rf $outputfile ; mv $splitfile $outputfile");
	    &writelog("digitaltvrecording DEBUG rm -rf $outputfile ; mv $splitfile $outputfile: $?.");
	  } else {
	    &writelog("digitaltvrecording ERROR File not found:$splitfile.");
	  }
	} else {
	  &writelog("digitaltvrecording ERROR $toolpath/perl/tool/TsSplitter.exe not found.");
	}
      } elsif ($bssplitflag == 102) {
	if (-e "$toolpath/perl/tool/TsSplitter.exe") {
	  # BS2		
	  system("wine $toolpath/perl/tool/TsSplitter.exe  -EIT -ECM  -EMM  -OUT \"$outputpath\" -HD  -SD1 -SD3 -1SEG  -LOGFILE -WAIT2 $outputfile");
	  $splitfile = $outputfile;
	  $splitfile =~ s/\.m2t$/_SD2.m2t/;
	  if (-e "$splitfile") {
	    system("rm -rf $outputfile ; mv $splitfile $outputfile");
	    &writelog("digitaltvrecording DEBUG rm -rf $outputfile ; mv $splitfile $outputfile: $?.");
	  } else {
	    &writelog("digitaltvrecording ERROR File not found:$splitfile.");
	  }
	} else {
	  &writelog("digitaltvrecording ERROR $toolpath/perl/tool/TsSplitter.exe not found.");
	}
      } else {
	&writelog("digitaltvrecording DEBUG not split TS.$bssplitflag");
      }				# endif #BS1/BS2などのスプリットを

    } else {			# エラー recfriioがありません
      &writelog("digitaltvrecording :ERROR :recfriio  not found. You must install $toolpath/perl/tool/b25 and $toolpath/perl/tool/recfriio:$errorflag");
      #exit 1;
      exit $errorflag;
    }
  }				#end if errorflag
}				#end calldigitalrecorder


