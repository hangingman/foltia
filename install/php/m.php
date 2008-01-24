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
?>

<?php
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
WHERE stationid = $recstid";
	$stationvalid = m_query($con, $query, "DBクエリに失敗しました");
	$stationcount = pg_num_rows($stationvalid);

	if ($stationcount == 1){
		$recstationname = pg_fetch_row($stationvalid, 0);
	//valid
	}else{
		$errflag = 3;
		$errmsg = "放送局設定が異常です。";
	}
}
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
	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$maxrows = pg_num_rows($rs);
	if ($maxrows == 0){
	$insertpid = -1 ;
	}else{
	$rowdata = pg_fetch_row($rs, 0);
	$insertpid = $rowdata[0];
	$insertpid-- ;
	}
// next 話数を探す
$query = "SELECT max(countno) FROM  foltia_subtitle WHERE tid = 0";
	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$maxrows = pg_num_rows($rs);
	if ($maxrows == 0){
	$nextcno = 1 ;
	}else{
	$rowdata = pg_fetch_row($rs, 0);
	$nextcno = $rowdata[0];
	$nextcno++ ;
	}

//INSERT
if ($demomode){
}else{

$query = "
insert into foltia_subtitle  
values ( '$insertpid','0','$recstid',
	'$nextcno','$pname','$startdatetime','$enddatetime','0' ,'$lengthmin')";

	$rs = m_query($con, $query, "DBクエリに失敗しました");

//addatq.pl
//キュー入れプログラムをキック
//引数　TID チャンネルID
//echo("$toolpath/perl/addatq.pl $tid $station");

	$oserr = system("$toolpath/perl/addatq.pl 0 0");

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
<form id="record" name="record" method="get" action="./m.php">
  <p>放送日:
    <input name="startdate" type="text" id="startdate" size="9" value="<?=$startdate?>" />
  年月日 Ex.19800121</p>
  <p>録画開始時刻:
    <input name="starttime" type="text" id="starttime" size="5" value="<?=$starttime?>" />
  時分 Ex.2304  </p>
  <p>
    録画尺:
      <input name="lengthmin" type="text" id="lengthmin" size="4" value="<?=$lengthmin?>"/> 
    分 (最長360分) </p>

  <p>録画局:
<?php
$query = "
SELECT stationid,stationname,stationrecch 
FROM foltia_station 
WHERE stationrecch > 0 
ORDER BY \"stationid\" ASC";

	$stations = m_query($con, $query, "DBクエリに失敗しました");
	$stationcount = pg_num_rows($stations);
	
if ($stationcount > 0 ){
	for ($row = 0; $row < $stationcount ; $row++) {
		$rowdata = pg_fetch_row($stations, $row);
			if ($recstid == $rowdata[0]){
			print " <input name=\"recstid\" type=\"radio\" value=\"$rowdata[0]\" checked />  $rowdata[1] ($rowdata[2]ch)　\n";
			}else{
				print " <input name=\"recstid\" type=\"radio\" value=\"$rowdata[0]\" />  $rowdata[1] ($rowdata[2]ch)　\n";
			}
	}
}else{
print "放送局データベースが正しくセットアップされていません。録画可能局がありません";
}

$query = "
SELECT stationid,stationname,stationrecch 
FROM foltia_station 
WHERE stationrecch > -2 AND stationrecch < 1 
ORDER BY \"stationid\" ASC";

	$stations = m_query($con, $query, "DBクエリに失敗しました");
	$stationcount = pg_num_rows($stations);
	
if ($stationcount > 0 ){
	for ($row = 0; $row < $stationcount ; $row++) {
		$rowdata = pg_fetch_row($stations, $row);
		if ($rowdata[0] != 0){
			if ($recstid == $rowdata[0]){
			print " <input name=\"recstid\" type=\"radio\" value=\"$rowdata[0]\" checked />  $rowdata[1]　\n";
			}else{
				print " <input name=\"recstid\" type=\"radio\" value=\"$rowdata[0]\" />  $rowdata[1]　\n";
			}

		}
	}
}

?>
  <p>番組名:
    <input name="pname" type="text" id="pname" value="<?=$pname ?>" />
  </p>
<p  style='background-color: #DDDDFF'>
繰り返し指定-毎週以下の曜日に録画:
<input name="weeklyloop" type="radio" value="128" />  日曜　
<input name="weeklyloop" type="radio" value="64" />  月曜　
<input name="weeklyloop" type="radio" value="32" />  火曜　
<input name="weeklyloop" type="radio" value="16" />  水曜　
<input name="weeklyloop" type="radio" value="8" />  木曜　
<input name="weeklyloop" type="radio" value="4" />  金曜　
<input name="weeklyloop" type="radio" value="2" />  土曜　
 </p>
 
<input type="submit" value="予約">　
</form>

</body>
</html>
