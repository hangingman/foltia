<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

m.php

目的
番組表を用いない完全手動録画予約を実現します。
ケータイなどで予約する場合もここを開くとよさそうです。

引数
startdate:録画開始日 (ex.20051207)
starttime:録画開始時刻 (ex.2304)
lengthmin:録画尺分
recstid:録画局ID
pname:番組名

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

$now = date("YmdHi");   
$today = date("Ymd");
$nowdate = date("Hi",(mktime(date("G"),date("i")+8,date("s"),date("m"),date("d"),date("Y"))));
$errflag = 0;
$pname = "手動録画";

function printtitle(){
print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">
<html lang=\"ja\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=EUC-JP\">
<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">
<link rel=\"stylesheet\" type=\"text/css\" href=\"graytable.css\"> ";

print "<title>foltia:番組手動予約</title>
</head>";
}//end function printtitle()

printtitle();
?>
<body BGCOLOR="#ffffff" TEXT="#494949" LINK="#0047ff" VLINK="#000000" ALINK="#c6edff" >
<div align="center">
<?php 
printhtmlpageheader();
?>
</div>
<p align="left"><font color="#494949" size="6">
番組手動予約
</font></p>
<hr size="4">
<?php
//値取得
$startdate = getgetnumform(startdate);
$starttime = getgetnumform(starttime);

if (($startdate == "") || ($starttime == "")){
	print "<p align=\"left\">全項目手動指定で予約します。</p>\n";
}else{

$lengthmin = getgetnumform(lengthmin);
$recstid = getgetnumform(recstid);
$pname = getgetform(pname);
//$usedigital = getgetnumform(usedigital);

//確認
$startdatetime = "$startdate"."$starttime";
if (foldatevalidation($startdatetime)){
//print "valid";
}else{
	$errflag = 1;
	$errmsg = "日付が不正です。";
}
if ($lengthmin < 361){
//valid
}else{
	$errflag = 2;
	$errmsg = "録画時間は360分で区切ってください。";
}
//局確認
if ($recstid != ""){
$query = "
SELECT stationname  
FROM foltia_station 
WHERE stationid = ? ";
//	$stationvalid = m_query($con, $query, "DBクエリに失敗しました");
	$stationvalid = sql_query($con, $query, "DBクエリに失敗しました",array($recstid));
		$recstationname = $stationvalid->fetch();
		if (! $recstationname) {
		$errflag = 3;
		$errmsg = "放送局設定が異常です。";
	}
}
//デジタル優先
/*if ($usedigital == 1){
}else{
	$usedigital = 0;
}
*/
//正しければ
if ($errflag == 0){
//重複があるか?
//未チェック

//デモモードじゃなかったら書き込み
$enddatetime = calcendtime($startdatetime,$lengthmin);

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
		if ($rowdata[0] > 0) {
			$insertpid = -1 ;
		}else{
			$insertpid = $rowdata[0];
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
}else{
	$userclass = getuserclass($con);
	if ( $userclass <= 2){
	$memberid = getmymemberid($con);
	
	$query = "
	insert into foltia_subtitle  (pid ,tid ,stationid , countno ,subtitle ,
startdatetime ,enddatetime ,startoffset , lengthmin , epgaddedby )  
	values ( ?,'0',?,?,?,?,?,'0',?,?)";
	
//		$rs = m_query($con, $query, "DBクエリに失敗しました");
//print "【DEBUG】$insertpid,$recstid,$nextcno,$pname,$startdatetime,$enddatetime ,$lengthmin,$memberid <br>\n";
		$rs = sql_query($con, $query, "DBクエリに失敗しました",array($insertpid,$recstid,$nextcno,$pname,$startdatetime,$enddatetime ,$lengthmin,$memberid));
	
	//addatq.pl
	//キュー入れプログラムをキック
	//引数　TID チャンネルID
	//echo("$toolpath/perl/addatq.pl $tid $station");
	exec("$toolpath/perl/addatq.pl 0 0");
	$oserr = system("$toolpath/perl/addatq.pl 0 0");
	//---------------------------------------------------
			if ($oserr){
			print "[DEBUG]$oserr 「$toolpath/perl/addatq.pl 0 0」<br>\n";
		}else{
			print "[DEBUG]exec addatq.pl false 「$toolpath/perl/addatq.pl 0 0」<br>\n";
			
			$oserr = system("$toolpath/perl/perltestscript.pl");
			if ($oserr){
				print "[DEBUG]exec perltestscript.pl $oserr<br>\n";
			}else{
				print "[DEBUG]exec perltestscript.pl false <br>\n";
			}
			
		}
	//-----------------------------------------------------
	}else{
		print "EPG予約を行う権限がありません。";
	}// end if $userclass <= 2
}//end if demomode

print "下記予約を完了いたしました。<br>";
//結果表示
print "録画開始:";
echo foldate2print($startdatetime);
print "<br />
録画終了:";
echo foldate2print($enddatetime);
print "<br />
録画尺: $lengthmin 分<br />
録画局:$recstationname[0]<br />
番組名:$pname<br />
";
exit();
}else{
print "時刻が不正なために予約できませんでした。 <br>";

}


}else{
	print "入力項目が正しくなさそうです。$errmsg<br />\n";
}

}//　初回表示かデータ処理か
?>
<form id="record" name="record" method="get" action="./m.php" autocomplete="off">
  <p>放送日:
    <input name="startdate" type="text" id="startdate" size="9" value="<?=$startdate?>" />
  年月日 Ex.<?=$today?></p>
  <p>録画開始時刻:
    <input name="starttime" type="text" id="starttime" size="5" value="<?=$starttime?>" />
  時分 Ex.<?=$nowdate?>  </p>
  <p>
    録画尺:
      <input name="lengthmin" type="text" id="lengthmin" size="4" value="<?=$lengthmin?>"/> 
    分 (最長360分) </p>

  <p>録画局:
<?php
$query = "
SELECT stationid as x, stationname, stationrecch, digitalch 
FROM foltia_station 
WHERE stationrecch > 0 
UNION 
SELECT DISTINCT  stationid,stationname,stationrecch ,digitalch 
FROM  foltia_station 
WHERE digitalch > 0 
ORDER BY x ASC";

$stations = sql_query($con, $query, "DBクエリに失敗しました");
$rowdata = $stations->fetch();

if ($rowdata) {
			   do {
			if ($recstid == $rowdata[0]){
			print " <input name=\"recstid\" type=\"radio\" value=\"$rowdata[0]\" checked />  $rowdata[1] ($rowdata[2]ch / $rowdata[3]ch)　\n";
			}else{
				print " <input name=\"recstid\" type=\"radio\" value=\"$rowdata[0]\" />  $rowdata[1] ($rowdata[2]ch / $rowdata[3]ch)　\n";
			}
			   } while ($rowdata = $stations->fetch());
}else{
print "放送局データベースが正しくセットアップされていません。録画可能局がありません";
}
//外部入力チャンネル
$query = "
SELECT stationid as x ,stationname,stationrecch 
FROM foltia_station 
WHERE stationrecch > -2 AND stationrecch < 1 
ORDER BY x ASC";

//	$stations = m_query($con, $query, "DBクエリに失敗しました");
	$stations = sql_query($con, $query, "DBクエリに失敗しました");
$rowdata = $stations->fetch();	
if ($rowdata) {
	do {
		if ($rowdata[0] != 0){
			if ($recstid == $rowdata[0]){
			print " <input name=\"recstid\" type=\"radio\" value=\"$rowdata[0]\" checked />  $rowdata[1]　\n";
			}else{
				print " <input name=\"recstid\" type=\"radio\" value=\"$rowdata[0]\" />  $rowdata[1]　\n";
			}

		}
	} while ($rowdata = $stations->fetch());
}
/*
print "<p>デジタル録画を優先:";

if ($usedigital == 1){
print "<input name="useditial" type="radio" value="1" selected />  する　
<input name="useditial" type="radio" value="0" />  しない　
";
}else{
print "<input name="useditial" type="radio" value="1" />  する　
<input name="useditial" type="radio" value="0" selected />  しない　
";
}
*/
?>
  <p>番組名:
    <input name="pname" type="text" id="pname" value="<?=$pname ?>" />
  </p>
<!-- <p  style='background-color: #DDDDFF'>
繰り返し指定-毎週以下の曜日に録画:
<input name="weeklyloop" type="radio" value="128" />  日曜　
<input name="weeklyloop" type="radio" value="64" />  月曜　
<input name="weeklyloop" type="radio" value="32" />  火曜　
<input name="weeklyloop" type="radio" value="16" />  水曜　
<input name="weeklyloop" type="radio" value="8" />  木曜　
<input name="weeklyloop" type="radio" value="4" />  金曜　
<input name="weeklyloop" type="radio" value="2" />  土曜　
 </p>
 -->
<input type="submit" value="予約">　
</form>

</body>
</html>
