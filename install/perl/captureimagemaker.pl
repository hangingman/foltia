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
# captureimagemaker.pl
# usage captureimagemaker.pl  MPEG2filename
#
# キャプチャ画像作成モジュール
# recwrap.plから呼び出される。
#

$path = $0;
$path =~ s/captureimagemaker.pl$//i;
if ($path ne "./") {
  push( @INC, "$path");
}

require "foltialib.pl";

#$tid = $ARGV[0] ;
$filename = $ARGV[0] ;

# filenameの妥当性をチェック
@filenametmp = split(/\./,$filename);
@filename = split(/-/,$filenametmp[0]);
$tid = $filename[0];

# tidが数字のみかチェック
$tid =~ s/[^0-9]//ig;
#print "$tid\n";

if ($tid eq "" ) {
  #引き数なし出実行されたら、終了
  print "usage captureimagemaker.pl  MPEG2filename\n";
  exit;
}

if ($tid >= 0) {
  #	print "TID is valid\n";
} else {
  &writelog("captureimagemaker TID invalid");
  exit;
}


$countno = $filename[1];
$countno =~ s/[^0-9]//ig;
#if ($countno eq "" ){
#$countno = "x";
#}
#	print "CNTNO:$countno\n";

$date = $filename[2];
$date =~ s/[^0-9]//ig;
if ($date eq "" ) {
  $date = strftime("%Y%m%d", localtime);
}
#	print "DATE:$date\n";


$time = $filename[3];
$time = substr($time, 0, 4);
$time =~ s/[^0-9]//ig;
if ($time eq "" ) {
  $time =  strftime("%H%M", localtime);
}
#	print "TIME:$time\n";

#　録画ファイルがアルかチェック
if (-e "$recfolderpath/$filename") {
  #	print "EXIST $recfolderpath/$filename\n";
} else {
  #	print "NO $recfolderpath/$filename\n";
  &writelog("captureimagemaker notexist $recfolderpath/$filename");

  exit;
}

# 展開先ディレクトリがあるか確認

$capimgdirname = "$tid.localized/";
$capimgdirname = $recfolderpath."/".$capimgdirname;
#なければ作る
unless (-e $capimgdirname ){
  system("$toolpath/perl/mklocalizeddir.pl $tid");
  &writelog("captureimagemaker mkdir $capimgdirname");
}
$capimgdirname = "$tid.localized/img";
$capimgdirname = $recfolderpath."/".$capimgdirname;
#なければ作る
unless (-e $capimgdirname ){
  mkdir $capimgdirname ,0777;
  &writelog("captureimagemaker mkdir $capimgdirname");
}


# キャプチャ入れるディレクトリ作成 
# $captureimgdir = "$tid"."-"."$countno"."-"."$date"."-"."$time";
$captureimgdir = $filename;
$captureimgdir =~ s/\.m2p$|\.m2t$//; 

unless (-e "$capimgdirname/$captureimgdir"){
  mkdir "$capimgdirname/$captureimgdir" ,0777;
  &writelog("captureimagemaker mkdir $capimgdirname/$captureimgdir");

}

# 変換
#system ("mplayer -ss 00:00:10 -vo jpeg:outdir=$capimgdirname/$captureimgdir/ -vf crop=702:468:6:6,scale=160:120,pp=lb -ao null -sstep 14 -v 3 $recfolderpath/$filename");

#system ("mplayer -ss 00:00:10 -vo jpeg:outdir=$capimgdirname/$captureimgdir/ -vf crop=702:468:6:6,scale=160:120 -ao null -sstep 14 -v 3 $recfolderpath/$filename");

#　ETVとか黒線入るから左右、もうすこしづつ切ろう。
#system ("mplayer -ss 00:00:10 -vo jpeg:outdir=$capimgdirname/$captureimgdir/ -vf crop=690:460:12:10,scale=160:120 -ao null -sstep 14 -v 3 $recfolderpath/$filename");

#　10秒ごとに
if ($filename =~ /m2t$/) {
  &writelog("captureimagemaker DEBUG mplayer -ss 00:00:10 -vo jpeg:outdir=$capimgdirname/$captureimgdir/ -vf scale=192:108 -ao null -sstep 9  $recfolderpath/$filename");
  system ("mplayer -ss 00:00:10 -vo jpeg:outdir=$capimgdirname/$captureimgdir/ -vf scale=192:108 -ao null -sstep 9  $recfolderpath/$filename");
  if (-e "$capimgdirname/$captureimgdir/00000001.jpg" ) { #$capimgdirname/$captureimgdir/があったらなにもしない	
  } else {			#空っぽなら再試行
    &writelog("captureimagemaker DEBUG RETRY mplayer -ss 00:00:10 -vo jpeg:outdir=$capimgdirname/$captureimgdir/ -vf framestep=300step,scale=192:108 -ao null $recfolderpath/$filename");
    system ("mplayer -ss 00:00:10 -vo jpeg:outdir=$capimgdirname/$captureimgdir/ -vf framestep=300step,scale=192:108 -ao null $recfolderpath/$filename");
  }
	
} else {
  &writelog("captureimagemaker DEBUG mplayer -ss 00:00:10 -vo jpeg:outdir=$capimgdirname/$captureimgdir/ -vf crop=690:460:12:10,scale=160:120 -ao null -sstep 9 -v 3 $recfolderpath/$filename");
  system ("mplayer -ss 00:00:10 -vo jpeg:outdir=$capimgdirname/$captureimgdir/ -vf crop=690:460:12:10,scale=160:120 -ao null -sstep 9 $recfolderpath/$filename");
  if (-e "$capimgdirname/$captureimgdir/00000001.jpg" ) { #$capimgdirname/
  } else {
    system ("mplayer -ss 00:00:10 -vo jpeg:outdir=$capimgdirname/$captureimgdir/ -vf framestep=300step,crop=690:460:12:10,scale=160:120 -ao null $recfolderpath/$filename");
  }
}

