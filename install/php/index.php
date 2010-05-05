<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

index.php

目的
全番組放映予定を表示します。
録画予約されている番組は別色でわかりやすく表現されています。


オプション
mode:"new"を指定すると、新番組(第1話)のみの表示となる。
now:YmdHi形式で日付を指定するとその日からの番組表が表示される。

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

$now = getgetnumform(date);
if(($now < 200001010000 ) || ($now > 209912342353 )){ 
	$now = date("YmdHi");   
}
function printtitle(){

print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">
<html lang=\"ja\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=EUC-JP\">
<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">
<link rel=\"stylesheet\" type=\"text/css\" href=\"graytable.css\"> ";
//ディスク空き容量によって背景色表示変更
warndiskfreearea();
print "<title>foltia:放映予定</title>
</head>";


}//end function printtitle()

//同一番組他局検索
$query = "
SELECT
foltia_program .tid,
foltia_program .title,
foltia_subtitle.countno,
foltia_subtitle.subtitle,
foltia_subtitle.startdatetime ,
foltia_subtitle.lengthmin ,
foltia_tvrecord.bitrate ,
foltia_subtitle.pid  
FROM foltia_subtitle , foltia_program  ,foltia_tvrecord
WHERE foltia_tvrecord.tid = foltia_program.tid 
AND foltia_program.tid = foltia_subtitle.tid 
AND foltia_subtitle.enddatetime >= ? 
ORDER BY \"startdatetime\" ASC 
LIMIT 1000
	";
//	$reservedrssametid = m_query($con, $query, "DBクエリに失敗しました");
$reservedrssametid = sql_query($con, $query, "DBクエリに失敗しました",array($now));
$rowdata = $reservedrssametid->fetch();
if ($rowdata) {
	do {
		$reservedpidsametid[] = $rowdata[7];
	} while ($rowdata = $reservedrssametid->fetch());

	$rowdata = "";
	}else{
	$reservedpidsametid = array();
	}//end if
$reservedrssametid->closeCursor();

//録画番組検索
$query = "
SELECT
 foltia_program.tid, stationname, foltia_program.title,
 foltia_subtitle.countno, foltia_subtitle.subtitle,
 foltia_subtitle.startdatetime as x, foltia_subtitle.lengthmin,
 foltia_tvrecord.bitrate, foltia_subtitle.pid
FROM foltia_subtitle , foltia_program ,foltia_station ,foltia_tvrecord
WHERE foltia_tvrecord.tid = foltia_program.tid AND foltia_tvrecord.stationid = foltia_station .stationid AND foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid
AND foltia_subtitle.enddatetime >= '$now'
UNION
SELECT
 foltia_program.tid, stationname, foltia_program.title,
 foltia_subtitle.countno, foltia_subtitle.subtitle,
 foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
 foltia_tvrecord.bitrate, foltia_subtitle.pid
FROM foltia_tvrecord
LEFT OUTER JOIN foltia_subtitle on (foltia_tvrecord.tid = foltia_subtitle.tid )
LEFT OUTER JOIN foltia_program on (foltia_tvrecord.tid = foltia_program.tid )
LEFT OUTER JOIN foltia_station on (foltia_subtitle.stationid = foltia_station.stationid )
WHERE foltia_tvrecord.stationid = 0 AND
 foltia_subtitle.enddatetime >= '$now' ORDER BY x ASC
LIMIT 1000
	";

//$reservedrs = m_query($con, $query, "DBクエリに失敗しました");
$query = "
SELECT
 foltia_program.tid, stationname, foltia_program.title,
 foltia_subtitle.countno, foltia_subtitle.subtitle,
 foltia_subtitle.startdatetime as x, foltia_subtitle.lengthmin,
 foltia_tvrecord.bitrate, foltia_subtitle.pid
FROM foltia_subtitle , foltia_program ,foltia_station ,foltia_tvrecord
WHERE foltia_tvrecord.tid = foltia_program.tid AND foltia_tvrecord.stationid = foltia_station .stationid AND foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid
AND foltia_subtitle.enddatetime >= ? 
UNION
SELECT
 foltia_program.tid, stationname, foltia_program.title,
 foltia_subtitle.countno, foltia_subtitle.subtitle,
 foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
 foltia_tvrecord.bitrate, foltia_subtitle.pid
FROM foltia_tvrecord
LEFT OUTER JOIN foltia_subtitle on (foltia_tvrecord.tid = foltia_subtitle.tid )
LEFT OUTER JOIN foltia_program on (foltia_tvrecord.tid = foltia_program.tid )
LEFT OUTER JOIN foltia_station on (foltia_subtitle.stationid = foltia_station.stationid )
WHERE foltia_tvrecord.stationid = 0 AND
 foltia_subtitle.enddatetime >= ? ORDER BY x ASC
LIMIT 1000
	";
$reservedrs = sql_query($con, $query, "DBクエリに失敗しました",array($now,$now));

$rowdata = $reservedrs->fetch();
if ($rowdata) {
	do {
		$reservedpid[] = $rowdata[8];
	} while ($rowdata = $reservedrs->fetch());
	}else{
	$reservedpid = array();
	}//end if

$mode = getgetform(mode);

if ($mode == "new"){
//新番組表示モード
	$query = "
	SELECT 
 foltia_program.tid, stationname, foltia_program.title,
 foltia_subtitle.countno, foltia_subtitle.subtitle,
 foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
 foltia_subtitle.pid, foltia_subtitle.startoffset
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid 
 AND foltia_subtitle.enddatetime >= '$now'  AND foltia_subtitle.countno = '1' 
ORDER BY foltia_subtitle.startdatetime  ASC 
LIMIT 1000
	";
$query = "
	SELECT 
 foltia_program.tid, stationname, foltia_program.title,
 foltia_subtitle.countno, foltia_subtitle.subtitle,
 foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
 foltia_subtitle.pid, foltia_subtitle.startoffset
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid 
 AND foltia_subtitle.enddatetime >= ?  AND foltia_subtitle.countno = '1' 
ORDER BY foltia_subtitle.startdatetime  ASC 
LIMIT 1000
	";
}else{
$query = "
	SELECT 
 foltia_program.tid, stationname, foltia_program.title,
 foltia_subtitle.countno, foltia_subtitle.subtitle,
 foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
 foltia_subtitle.pid, foltia_subtitle.startoffset
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid 
 AND foltia_subtitle.enddatetime >= '$now'  
ORDER BY foltia_subtitle.startdatetime  ASC 
LIMIT 1000
	";
$query = "
	SELECT 
 foltia_program.tid, stationname, foltia_program.title,
 foltia_subtitle.countno, foltia_subtitle.subtitle,
 foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
 foltia_subtitle.pid, foltia_subtitle.startoffset
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid 
 AND foltia_subtitle.enddatetime >= ?  
ORDER BY foltia_subtitle.startdatetime  ASC 
LIMIT 1000
	";
}//end if

//$rs = m_query($con, $query, "DBクエリに失敗しました");
$rs = sql_query($con, $query, "DBクエリに失敗しました",array($now));
$rowdata = $rs->fetch();
if (! $rowdata) {
header("Status: 404 Not Found",TRUE,404);
printtitle();
print "<body BGCOLOR=\"#ffffff\" TEXT=\"#494949\" LINK=\"#0047ff\" VLINK=\"#000000\" ALINK=\"#c6edff\" >
<div align=\"center\">\n";
printhtmlpageheader();
print "<hr size=\"4\">\n";
		die_exit("番組データがありません<BR>");
}//endif

printtitle();
?>
<body BGCOLOR="#ffffff" TEXT="#494949" LINK="#0047ff" VLINK="#000000" ALINK="#c6edff" >
<div align="center">
<?php 
printhtmlpageheader();
?>
  <p align="left"><font color="#494949" size="6">
<?php
if ($mode == "new"){
	print "新番組放映予定";
}else{
	print "放映予定";
}
?>
</font></p>
  <hr size="4">
<p align="left">放映番組リストを表示します。</p>

<?php
		/* フィールド数 */
    $maxcols = $rs->columnCount();
		?>
  <table BORDER="0" CELLPADDING="0" CELLSPACING="2" WIDTH="100%">
	<thead>
		<tr>
			<th align="left">TID</th>
			<th align="left">放映局</th>
			<th align="left">タイトル</th>
			<th align="left">話数</th>
			<th align="left">サブタイトル</th>
			<th align="left">開始時刻(ズレ)</th>
			<th align="left">総尺</th>

		</tr>
	</thead>

	<tbody>
		<?php
			/* テーブルのデータを出力 */
     do {
//他局で同一番組録画済みなら色変え
if (in_array($rowdata[7], $reservedpidsametid)) {
$rclass = "reservedtitle";
}else{
$rclass = "";
}
//録画予約済みなら色変え
if (in_array($rowdata[7], $reservedpid)) {
$rclass = "reserved";
}
$pid = htmlspecialchars($rowdata[7]);

$tid = htmlspecialchars($rowdata[0]);
$title = htmlspecialchars($rowdata[2]);
$subtitle =  htmlspecialchars($rowdata[4]);

				echo("<tr class=\"$rclass\">\n");
					// TID
					print "<td>";
					if ($tid == 0 ){
					print "$tid";
					}else{
					print "<a href=\"reserveprogram.php?tid=$tid\">$tid</a>";
					}
					print "</td>\n";
				     // 放映局
				     echo("<td>".htmlspecialchars($rowdata[1])."<br></td>\n");
				     // タイトル
					print "<td>";
					if ($tid == 0 ){
					print "$title";
					}else{
					print "<a href=\"http://cal.syoboi.jp/tid/$tid\" target=\"_blank\">$title</a>";
					}
					print "</td>\n";
					 // 話数
					echo("<td>".htmlspecialchars($rowdata[3])."<br></td>\n");
					// サブタイ
					if ($pid > 0 ){
					print "<td><a href=\"http://cal.syoboi.jp/tid/$tid/time#$pid\" target=\"_blank\">$subtitle<br></td>\n";
					}else{
					print "<td>$subtitle<br></td>\n";
					}
					// 開始時刻(ズレ)
					echo("<td>".htmlspecialchars(foldate2print($rowdata[5]))."<br>(".htmlspecialchars($rowdata[8]).")</td>\n");
					// 総尺
					echo("<td>".htmlspecialchars($rowdata[6])."<br></td>\n");

				echo("</tr>\n");
     } while ($rowdata = $rs->fetch());
		?>
	</tbody>
</table>


</body>
</html>
