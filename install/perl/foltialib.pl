
#config load

$path = $0;
$path =~ s/foltialib.pl$//i;
if ($pwd  ne "./"){
push( @INC, "$path");
}
require "foltia_conf1.pl";


#  foltia lib
use DBI;
use DBD::Pg;


	 $DBDriv=$main::DBDriv;
	 $DBHost=$main::DBHost;
	 $DBPort=$main::DBPort;
	 $DBName=$main::DBName;
	 $DBUser=$main::DBUser;
	 $DBPass="";
	

#------------------------------
sub writelog{
my $messages = $_[0];
my $timestump = `date  +%Y/%m/%d_%H:%M:%S`;
chomp($timestump);
if ($debugmode == 1){
	open (DEBUGLOG , ">>$toolpath/debuglog.txt") || die "Cant write log file.$! \n ";
}else{
	open (DEBUGLOG , '>-') || die "Cant write log file.$! \n ";
}
$messages =~ s/\n//gio;
print DEBUGLOG "$timestump $messages\n";
close (DEBUGLOG);
}#end writelog

sub syobocaldate2foltiadate{
#20041114213000 -> 200411142130
my $foltiadate = $_[0] ;

		$foltiadate = substr($foltiadate,0,12);

return  $foltiadate;

}


sub foldate2epoch{
my $foltiadate = $_[0] ;
#EPGをEPOCに
# 2004 11 14 21 30
my $eyear = substr($foltiadate , 0, 4);
my $emon = substr($foltiadate, 4, 2);
$emon--;
my $emday =  substr($foltiadate , 6, 2);
my $q_start_time_hour =  substr($foltiadate , 8, 2);
my $q_start_time_min = substr($foltiadate , 10, 2);

my $epoch = timelocal(0,$q_start_time_min,$q_start_time_hour,  $emday, $emon , $eyear);

return  $epoch;
}


sub epoch2foldate{
my $s;
my $mi;
my $h;
my $d;
my $mo;
 my $y;
my  $w;
 
    ($s, $mi, $h, $d, $mo, $y, $w) = localtime($_[0]);
    $mo++; $y += 1900;

my $foltiadate;
$mo = sprintf("%02d",$mo);
$d = sprintf("%02d",$d);

$h = sprintf("%02d",$h);
$mi = sprintf("%02d",$mi);
$foltiadate = "$y$mo$d$h$mi";

return  $foltiadate;
}

sub calclength{
#foltia開始時刻、folti終了時刻
#戻り値:分数
my $sttime  = $_[0] ;
my $edtime = $_[1] ;
my $length = -1;
$sttime = &foldate2epoch($sttime);
$edtime = &foldate2epoch($edtime);

if ($edtime >= $sttime){
	$length = $edtime - $sttime;
}else{
	$length =   $sttime - $edtime;
}
$length = $length / 60;

return $length ;
}

sub calcoffsetdate{
#引き数:foltia時刻、オフセット(+/-)分
#戻り値]foltia時刻
my $foltime  = $_[0] ;
my $offsetmin = $_[1] ;

my $epoch = &foldate2epoch($foltime );
$epoch = $epoch + ($offsetmin * 60 );
$foltime = &epoch2foldate($epoch);
return $foltime ;
}

sub getstationid{
#引き数:局文字列(NHK総合)
#戻り値:1
my $stationname =  $_[0] ;
my $stationid ;
my $DBQuery =  "SELECT count(*) FROM foltia_station WHERE stationname = '$item{ChName}'";

my $sth;
	 $sth = $dbh->prepare($DBQuery);
	$sth->execute();
 my  @stationcount;
 @stationcount= $sth->fetchrow_array;

if ($stationcount[0] == 1){
#チャンネルID取得
$DBQuery =  "SELECT stationid,stationname FROM foltia_station WHERE stationname = '$item{ChName}'";
	 $sth = $dbh->prepare($DBQuery);
	$sth->execute();
 @stationinfo= $sth->fetchrow_array;
#局ID
$stationid  = $stationinfo[0];
#print "StationID:$stationid \n";

}elsif($stationcount[0] == 0){
#新規登録
$DBQuery =  "SELECT max(stationid) FROM foltia_station";
	 $sth = $dbh->prepare($DBQuery);
	$sth->execute();
 @stationinfo= $sth->fetchrow_array;
my $stationid = $stationinfo[0] ;
$stationid  ++;
##$DBQuery =  "insert into  foltia_station values ('$stationid'  ,'$item{ChName}','0','','','','','','')";
#新規局追加時は非受信局をデフォルトに
$DBQuery =  "insert into  foltia_station  (stationid , stationname ,stationrecch )  values ('$stationid'  ,'$item{ChName}','-10')";

	 $sth = $dbh->prepare($DBQuery);
	$sth->execute();
#print "Add station;$DBQuery\n";
&writelog("foltialib Add station;$DBQuery");
}else{

#print "Error  getstationid $stationcount[0] stations found. $DBQuery\n";
&writelog("foltialib [ERR]  getstationid $stationcount[0] stations found. $DBQuery");
}


return $stationid ;
}

sub calcatqparam{
my $seconds = $_[0];
my $processstarttimeepoch = "";
	$processstarttimeepoch = &foldate2epoch($startdatetime);
	$processstarttimeepoch = $processstarttimeepoch - $seconds ;
my $sec = "";
my $min = "";
my $hour = "";
my $mday = "";
my $mon = "";
my $year = "";
my  $wday = "";
my $yday = "";
my $isdst = "";
	($sec, $min, $hour, $mday, $mon, $year, $wday, $yday, $isdst) = localtime($processstarttimeepoch) ;
	$year+=1900;
	$mon++;#範囲を0-11から1-12へ
my $atdateparam = "";
	$atdateparam = sprintf ("%04d%02d%02d%02d%02d",$year,$mon,$mday,$hour,$min);	

return  $atdateparam ;
}



sub processfind{
my $findprocess = $_[0];

my @processes ;
@processes = `ps ax | grep -i $findprocess `;
my $chkflag = 0;

foreach (@processes ){
if (/$findprocess/i){
	unless (/grep/){
		#print "process found:$_\n";
		$chkflag++ ;
		}else{
		#print "process NOT found:$_\n";
		}
	}

}
return ($chkflag);
}#endsub


sub filenameinjectioncheck{
my $filename = $_[0];
		$filename =~ s/\///gi;
		$filename =~ s/\;//gi;
		$filename =~ s/\&//gi;
		$filename  =~ s/\|//gi;

return ($filename );
}


sub getphpstyleconfig{
my $key = $_[0];
my $phpconfigpath = "";
my $configline = "";
 # read
if (-e "$phptoolpath/php/foltia_config2.php"){
	$phpconfigpath = "$phptoolpath/php/foltia_config2.php";
}elsif(-e "$toolpath/php/foltia_config2.php"){
	$phpconfigpath = "$toolpath/php/foltia_config2.php";
}else{
	$phpconfigpath = `locate foltia_config2.php | head -1`;
	chomp($phpconfigpath);
}


if (-r $phpconfigpath ){
open (CONFIG ,"$phpconfigpath") || die "File canot read.$!";
while(<CONFIG>){
	if (/$key/){
	$configline = $_;
	$configline =~ s/\/\/.*$//;
	$configline =~ s/\/\*.*\*\///;
	}else{
	}
}
close(CONFIG);
}#end if -r $phpconfigpath 
return ($configline);
}#end sub getphpstyleconfig


1;

