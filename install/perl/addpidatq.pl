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
# addpidatq.pl
#
# PID受け取りatqに入れる。folprep.plからキュー再入力のために使われる
#

use DBI;
use DBD::SQLite;
use Schedule::At;
use Time::Local;

$path = $0;
$path =~ s/addpidatq.pl$//i;
if ($path ne "./") {
  push( @INC, "$path");
}

require "foltialib.pl";


#引き数がアルか?
$pid = $ARGV[0] ;
if ($pid eq "" ) {
  #引き数なし出実行されたら、終了
  print "usage;addpidatq.pl <PID>\n";
  exit;
}


#DB検索(PID)
$dbh = DBI->connect($DSN,$DBUser,$DBPass) ||die $DBI::error;;

$sth = $dbh->prepare($stmt{'addpidatq.1'});
$sth->execute($pid);
@titlecount= $sth->fetchrow_array;
 
if ($titlecount[0]  == 1 ) {
  $sth = $dbh->prepare($stmt{'addpidatq.2'});
  $sth->execute($pid);
  @titlecount= $sth->fetchrow_array;
  $bitrate = $titlecount[0];	#ビットレート取得
  if ($titlecount[1] >= 1) {
    $usedigital = $titlecount[1]; #デジタル優先フラグ
  } else {
    $usedigital = 0;
  }

  #PID抽出
  $now = &epoch2foldate(time());

  #stationIDからrecch
  $stationh = $dbh->prepare($stmt{'addpidatq.3'});
  $stationh->execute($pid);
  @stationl =  $stationh->fetchrow_array();
  $recch = $stationl[0];
  if ($recch eq "") {
    &writelog("addpidatq ERROR recch is NULL:$stmt{'addpidatq.3'}.");
    exit 1;
  }
  if ($stationl[1] => 1) {
    $digitalch = $stationl[1];
  } else {
    $digitalch = 0;
  }
  if ($stationl[2] => 1) {
    $digitalstationband = $stationl[2];
  } else {
    $digitalstationband = 0;
  }
  $sth = $dbh->prepare($stmt{'addpidatq.4'});
  $sth->execute($pid);
  ($pid ,
   $tid ,
   $stationid ,
   $countno,
   $subtitle,
   $startdatetime,
   $enddatetime,
   $startoffset ,
   $lengthmin,
   $atid ) = $sth->fetchrow_array();
  # print "$pid ,$tid ,$stationid ,$countno,$subtitle,$startdatetime,$enddatetime,$startoffset ,$lengthmin,$atid \n";

  if ($now< $startdatetime) {  #放送が未来の日付なら
    #もし新開始時刻が15分移譲先なら再キュー
    $startafter = &calclength($now,$startdatetime);
    &writelog("addpidatq DEBUG \$startafter $startafter \$now $now \$startdatetime $startdatetime");

    if ($startafter > 14 ) {

      #キュー削除
      Schedule::At::remove ( TAG => "$pid"."_X");
      &writelog("addpidatq remove que $pid");


      #キュー入れ
      #プロセス起動時刻は番組開始時刻の-5分
      $atdateparam = &calcatqparam(300);
      Schedule::At::add (TIME => "$atdateparam", COMMAND => "$toolpath/perl/folprep.pl $pid" , TAG => "$pid"."_X");
      &writelog("addpidatq TIME $atdateparam   COMMAND $toolpath/perl/folprep.pl $pid ");
    } else {
      $atdateparam = &calcatqparam(60);
      $reclength = $lengthmin * 60;

      #キュー削除
      Schedule::At::remove ( TAG => "$pid"."_R");
      &writelog("addpidatq remove que $pid");

      if ($countno eq "") {
	$countno = "0";
      }

      Schedule::At::add (TIME => "$atdateparam", COMMAND => "$toolpath/perl/recwrap.pl $recch $reclength $bitrate $tid $countno $pid $stationid $usedigital $digitalstationband $digitalch" , TAG => "$pid"."_R");
      &writelog("addpidatq TIME $atdateparam   COMMAND $toolpath/perl/recwrap.pl $recch $reclength $bitrate $tid $countno $pid $stationid $usedigital $digitalstationband $digitalch");

    }			  #end #もし新開始時刻が15分移譲先なら再キュー

  } else {
    &writelog("addpidatq drop:expire $pid $startafter $now $startdatetime");
  }				#放送が未来の日付なら

} else {
  print "error record TID=$tid SID=$station $titlecount[0] match:$DBQuery\n";
  &writelog("addpidatq error record TID=$tid SID=$station $titlecount[0] match:$DBQuery");

}				#end if ($titlecount[0]  == 1 ){


