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
# epgimport.pl
#
# EPG番組表取得
# tsを取得してepgdump経由でepgテーブルにインポートします。
# 内部でxmltv2foltia.plを呼んで実際の追加処理を行います。
#
# usage 
# epgimport.pl [long]  #longがつくと一週間分
# epgimport.pl [stationid]  #放送局ID指定でそのチャンネルだけ短時間で取得
#
use DBI;
use DBD::SQLite;
use Jcode;

$path = $0;
$path =~ s/epgimport.pl$//i;
if ($path ne "./") {
  push( @INC, "$path");
}

require "foltialib.pl";

my $ontvcode = "";
my $channel = "";
my @date = ();
my $recpt1path  = $foltia_recpt1path;
my $epgdumppath = $foltia_epgdumppath;
my $xmloutpath  = "/tmp";
my %stations;
my $uset = "";
my $usebs = "";
my $usecs = "";
my $stationid = "" ;
my $rectime = 0;
my $bsrectime = 0;
my $cs1rectime = 0;
my $cs2rectime = 0;


#引き数がアルか?
if ( $ARGV[0] eq "long" ) {
  #長期番組表取得
  $rectime = 60;
  $bsrectime = 120;
  $cs1rectime = 60;
  $cs2rectime = 60;
} elsif ( $ARGV[0] > 0 ) {
  $stationid = $ARGV[0]; 
  $rectime = 3;
  $bsrectime = 36;
  $cs1rectime = 15;
  $cs2rectime = 5;
} else {
  #短期番組表取得
  $rectime = 3;
  $bsrectime = 36;
  $cs1rectime = 15;
  $cs2rectime = 5;
}
#データ量比較
#3秒   16350 Aug 10 16:21 __27-epg-short.xml
#12秒  56374 Aug 10 16:21 __27-epg-long.xml
#60秒 127735 Aug 10 16:23 __27-epg-velylong.xml

#重複起動確認
$processes =  &processfind("epgimport.pl");
if ($processes > 1 ) {
  &writelog("epgimport processes exist. exit:");
  exit;
}

$dbh = DBI->connect($DSN,$DBUser,$DBPass) ||die $DBI::error;;

#局指定があるなら、単一放送局指定モード
if ($stationid > 0) {
  $sth = $dbh->prepare($stmt{'epgimport.1'});
  $sth->execute($stationid);
  @data = $sth->fetchrow_array();
  unless($data[0] == 1){	#局の数が1でなければ異常終了
    &writelog("epgimport ERROR Invalid station id ($stationid).");
    exit 1;
  } else {
    $sth = $dbh->prepare($stmt{'epgimport.2'});
    $sth->execute($stationid);
    @data = $sth->fetchrow_array();
    $channel = $data[0];
    $ontvcode = $data[1];
    if ($channel > 0) {
      &writelog("epgimport DEBUG Single station mode (ch:$channel / $ontvcode).");
    } else {			#ラジオ局などの場合
      &writelog("epgimport ABORT SID $stationid is not Digital TV ch.");
      exit;
    }				#endif ラジオ局かどうか
  }				#end unless($data[0] == 1
}				#endif $stationid > 0

#地デジ----------------------------------------
#受信局確認
if ($channel >= 13 && $channel <= 62) {	#局指定があるなら
  $stations{$channel} = $ontvcode;
  $uset = 1;
} elsif ($channel >= 100) {
  $uset = 0;			#地デジ範囲外の局
} else {
  $sth = $dbh->prepare($stmt{'epgimport.3'});
  $sth->execute();
	
  while (@data = $sth->fetchrow_array()) {
    $stations{$data[0]} = $data[1];
  }				#end while 
  $uset = 1;
}				#end if

if ($uset == 1) {
  foreach $channel ( keys %stations ) {
    $ontvcode = $stations{$channel};
    #print "$ontvcode $digitalch\n";
    &chkrecordingschedule;
    #print "$recpt1path $channel $rectime $recfolderpath/__$channel.m2t\n";
    $oserr = `$recpt1path $channel $rectime $recfolderpath/__$channel.m2t`;
    #print "$epgdumppath/epgdump $ontvcode $recfolderpath/__$channel.m2t $xmloutpath/__$channel-epg.xml\n";
    $oserr = `$epgdumppath/epgdump $ontvcode $recfolderpath/__$channel.m2t $xmloutpath/__$channel-epg.xml`;
    #print "cat $xmloutpath/__$channel-epg.xml | $toolpath/perl/xmltv2foltia.pl\n";
    $oserr = `cat $xmloutpath/__$channel-epg.xml | $toolpath/perl/xmltv2foltia.pl`;
    unlink "$recfolderpath/__$channel.m2t";
    unlink "$xmloutpath/__$channel-epg.xml";
  }				#end foreach
}				#endif

#BS----------------------------------------
#受信局確認
if ($channel >= 100 && $channel <= 222 ) { #局指定があるなら
  $usebs = 1;
} elsif ($channel >= 13 && $channel <= 62) {
  $usebs = 0;			#地デジ局指定の場合、スキップ。
} elsif ($channel >= 223) {
  $usebs = 0;			#CS局指定の場合もスキップ
} else {
  $sth = $dbh->prepare($stmt{'epgimport.4'});
  $sth->execute();
  @data = $sth->fetchrow_array();
  if ($data[0] > 0 ) {
    $usebs = 1;
  }
}				#end if

if ($usebs == 1) {
  #$ontvcode = $stations{$channel};
  $channel = 211;
  #print "$ontvcode $digitalch\n";
  &chkrecordingschedule;
  #print "$recpt1path $channel $bsrectime $recfolderpath/__$channel.m2t\n";
  $oserr = `$recpt1path $channel $bsrectime $recfolderpath/__$channel.m2t`;
  #print "$epgdumppath/epgdump /BS $recfolderpath/__$channel.m2t $xmloutpath/__$channel-epg.xml\n";
  $oserr = `$epgdumppath/epgdump /BS $recfolderpath/__$channel.m2t $xmloutpath/__$channel-epg.xml`;
  #print "cat $xmloutpath/__$channel-epg.xml | $toolpath/perl/xmltv2foltia.pl\n";
  $oserr = `cat $xmloutpath/__$channel-epg.xml | $toolpath/perl/xmltv2foltia.pl`;
  unlink "$recfolderpath/__$channel.m2t";
  unlink "$xmloutpath/__$channel-epg.xml";
} else {
  &writelog("epgimport DEBUG Skip BS.$channel:$usebs");
}



#CS----------------------------------------
#if ( $ARGV[0] eq "long" ){ #短時間録画なら異常に重くはならないことを発見した
#受信局確認
if ($channel >= 223  ) {	#局指定があるなら
  $usecs = 1;
} else {
  $sth = $dbh->prepare($stmt{'epgimport.5'});
  $sth->execute();
  @data = $sth->fetchrow_array();
  if ($data[0] > 0 ) {
    $usecs = 1;
  }
}				#end if

if ($usecs == 1) {
  #一気に録画して
  $channela = "CS8";
  #print "$ontvcode $digitalch\n";
  &chkrecordingschedule;
  #print "$recpt1path $channela $bsrectime $recfolderpath/__$channela.m2t\n";
  $oserr = `$recpt1path $channela $cs1rectime $recfolderpath/__$channela.m2t`;

  $channelb = "CS24";
  &chkrecordingschedule;
  #print "$recpt1path $channelb $bsrectime $recfolderpath/__$channelb.m2t\n";
  $oserr = `$recpt1path $channelb $cs2rectime $recfolderpath/__$channelb.m2t`;

  #時間のかかるepgdumpまとめてあとまわし
  #print "nice -n 19 $epgdumppath/epgdump /CS $recfolderpath/__$channela.m2t $xmloutpath/__$channela-epg.xml\n";
  $oserr = `$epgdumppath/epgdump /CS $recfolderpath/__$channela.m2t $xmloutpath/__$channela-epg.xml`;
  #print "cat $xmloutpath/__$channela-epg.xml | $toolpath/perl/xmltv2foltia.pl\n";
  $oserr = `cat $xmloutpath/__$channela-epg.xml | $toolpath/perl/xmltv2foltia.pl`;
  unlink "$recfolderpath/__$channela.m2t";
  unlink "$xmloutpath/__$channela-epg.xml";

  #print "nice -n 19 $epgdumppath/epgdump /CS $recfolderpath/__$channelb.m2t $xmloutpath/__$channelb-epg.xml\n";
  $oserr = `$epgdumppath/epgdump /CS $recfolderpath/__$channelb.m2t $xmloutpath/__$channelb-epg.xml`;
  #print "cat $xmloutpath/__$channelb-epg.xml | $toolpath/perl/xmltv2foltia.pl\n";
  $oserr = `cat $xmloutpath/__$channelb-epg.xml | $toolpath/perl/xmltv2foltia.pl`;
  unlink "$recfolderpath/__$channelb.m2t";
  unlink "$xmloutpath/__$channelb-epg.xml";
} else {
  &writelog("epgimport DEBUG Skip CS.");
}		      #endif use 
#}else{
#	if ($channel >= 223  ){#局指定があるなら
#		&writelog("epgimport ERROR CS Station No. was ignored. CS EPG get long mode only.");
#	}
#}#end if long


sub chkrecordingschedule{
  #放送予定まで近くなったら、チューナー使いつづけないようにEPG取得中断
  my $now = time() ;
  my $fiveminitsafter = time() + 60 * 4;
  my $rows = -2;
  $now = &epoch2foldate($now);
  $fiveminitsafter = &epoch2foldate($fiveminitsafter);

  #録画予定取得
  $sth = $dbh->prepare($stmt{'epgimport.6'});
  $sth->execute($now,$fiveminitsafter,$now,$fiveminitsafter);

  while (@data = $sth->fetchrow_array()) {
    #
  } #end while 

  $rows = $sth->rows;

  if ($rows > 0 ) {
    &writelog("epgimport ABORT The recording schedule had approached.");
    exit ;
  } else {
    &writelog("epgimport DEBUG Near rec program is $rows.:$now:$fiveminitsafter");
  }				#end if 
}				#endsub chkrecordingschedule

