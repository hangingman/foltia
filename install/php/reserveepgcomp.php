<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

reserveepgcomp.php

目的
EPG番組の予約登録をします。

引数
stationid:録画局ID
subtitle:番組名
startdatetime:録画開始時刻 (ex.200510070145)
enddatetime:録画終了時刻 (ex.200510070215)
lengthmin:録画時間(単位:分)

 DCC-JPL Japan/foltia project

*/

include("./foltialib.php");
$con = m_connect();

if ($useenvironmentpolicy == 1){
if (!isset($_SERVER['PHP_AUTH_USER'])) {
    header("WWW-Authenticate: Basic realm=\"foltia\"");
    header("HTTP/1.0 401 Unauthorized");
	redirectlogin();
    exit;
} else {
login($con,$_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
}
}//end if login


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" type="text/css" href="graytable.css"> 
<body BGCOLOR="#ffffff" TEXT="#494949" LINK="#0047ff" VLINK="#000000" ALINK="#c6edff" >

<?php 

	printhtmlpageheader();
?>
  <p align="left"><font color="#494949" size="6">番組予約</font></p>
  <hr size="4">
<?php

/* $stationid = getnumform(stationid);
$subtitle = getform(subtitle);
$startdatetime = getnumform(startdatetime);
$enddatetime = getnumform(enddatetime);
$lengthmin = getnumform(lengthmin); */
$epgid = getnumform(epgid);

		if ($epgid == "" ) {
		print "	<title>foltia:EPG予約:Error</title></head>\n";
		die_exit("登録番組がありません<BR>");
		}
print "	<title>foltia:EPG予約:完了</title>
</head>\n";
$now = date("YmdHi");   
//タイトル取得
	$query = "
	SELECT epgid,startdatetime,enddatetime,lengthmin, ontvchannel,epgtitle,epgdesc,epgcategory , 
	stationname , stationrecch ,stationid 
	FROM foltia_epg , foltia_station 
	WHERE epgid = ? AND foltia_station.ontvcode = foltia_epg.ontvchannel
	";
	$rs = sql_query($con, $query, "DBクエリに失敗しました",array($epgid));
$rowdata = $rs->fetch();
if (! $rowdata) {
		die_exit("登録番組がありません。もう一度EPGに戻り操作して下さい。<BR>");
}else{
$stationid = $rowdata[10];
$subtitle = $rowdata[5] . $rowdata[6];
$startdatetime = $rowdata[1];
$enddatetime = $rowdata[2];
$lengthmin = $rowdata[3];
}



// - DB登録作業

//時刻検査
if (($startdatetime > $now ) && ($enddatetime > $now ) && ($enddatetime  > $startdatetime ) ){

//min pidを探す
$query = "SELECT min(pid) FROM  foltia_subtitle ";
//	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$rs = sql_query($con, $query, "DBクエリに失敗しました");
	$rowdata = $rs->fetch();
	if (! $rowdata) {
	$insertpid = -1 ;
	}else{
	$insertpid = $rowdata[0];
		if ($insertpid > 0){
		$insertpid = -1;
		}else{
		$insertpid-- ;
		}
	}
// next 話数を探す
$query = "SELECT max(countno) FROM  foltia_subtitle WHERE tid = 0";
//	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$rs = sql_query($con, $query, "DBクエリに失敗しました");
	$rowdata = $rs->fetch();
	if (! $rowdata) {
	$nextcno = 1 ;
	}else{
	$nextcno = $rowdata[0];
	$nextcno++ ;
	}

//INSERT
if ($demomode){
	print "下記予約を完了いたしました。<br>";
}else{
$userclass = getuserclass($con);
if ( $userclass <= 2){
/*
pid 
tid 
stationid  
countno 
subtitle
startdatetime  
enddatetime  
startoffset  
lengthmin  
m2pfilename 
pspfilename 
epgaddedby  

*/

$memberid = getmymemberid($con);
	$query = "
insert into foltia_subtitle  (pid ,tid ,stationid , countno ,subtitle ,
startdatetime ,enddatetime ,startoffset , lengthmin , epgaddedby ) 
values ( ?,'0',?,?,?,?,?,'0',?,?)";
//	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$rs = sql_query($con, $query, "DBクエリに失敗しました",array($insertpid,$stationid,$nextcno,$subtitle,$startdatetime,$enddatetime,$lengthmin,$memberid));

	//addatq.pl
	//キュー入れプログラムをキック
	//引数　TID チャンネルID
	//echo("$toolpath/perl/addatq.pl $tid $station");

	$oserr = system("$toolpath/perl/addatq.pl 0 0");
	print "下記予約を完了いたしました。<br>";
}else{
	print "EPG予約を行う権限がありません。";
}// end if $userclass <= 2
}//end if demomode



}else{
print "時刻が不正なために予約できませんでした。 <br>";

}


print "<table width=\"100%\" border=\"0\">\n";
print "<tr><td>放送開始</td><td>".htmlspecialchars($startdatetime)."</td></tr>";
print "<tr><td>放送終了</td><td>".htmlspecialchars($enddatetime)."</td></tr>\n";
print "<tr><td>局コード</td><td>".htmlspecialchars($stationid)."</td></tr>\n";
print "<tr><td>尺(分)</td><td>".htmlspecialchars($lengthmin)."</td></tr>\n";
print "<tr><td>番組名</td><td>".htmlspecialchars($subtitle)."</td></tr>\n";
print "</tbody>\n</table>";

?>
</body>
</html>
