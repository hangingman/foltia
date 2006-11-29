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

 DCC-JPL Japan/foltia project

*/
?>

<?php
  include("./foltialib.php");

$con = m_connect();
$now = date("YmdHi");   


function printtitle(){

print "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">
<html lang=\"ja\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=EUC-JP\">
<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">
<link rel=\"stylesheet\" type=\"text/css\" href=\"graytable.css\"> 
<title>foltia:放映予定</title>
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
AND foltia_subtitle.enddatetime >= '$now'
 ORDER BY \"startdatetime\" ASC
	";
	$reservedrssametid = m_query($con, $query, "DBクエリに失敗しました");
	$reservedmaxrowssameid = pg_num_rows($reservedrssametid);
	
	if ($reservedmaxrowssameid > 0 ){
	for ($rrow = 0; $rrow < $reservedmaxrowssameid ; $rrow++) {
		$rowdata = pg_fetch_row($reservedrssametid, $rrow);
		$reservedpidsametid[] = $rowdata[7];
	}
	$rowdata = "";
	$rrow = ""; 
	}else{
	$reservedpidsametid = "" ;
	}//end if

//録画番組検索
$query = "
SELECT
foltia_program .tid,
stationname,
foltia_program .title,
foltia_subtitle.countno,
foltia_subtitle.subtitle,
foltia_subtitle.startdatetime ,
foltia_subtitle.lengthmin ,
foltia_tvrecord.bitrate ,
foltia_subtitle.pid  
FROM foltia_subtitle , foltia_program ,foltia_station ,foltia_tvrecord
WHERE foltia_tvrecord.tid = foltia_program.tid AND foltia_tvrecord.stationid = foltia_station .stationid AND foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid
AND foltia_subtitle.enddatetime >= '$now'
UNION
SELECT
foltia_program .tid,
stationname,
foltia_program .title,
foltia_subtitle.countno,
foltia_subtitle.subtitle,
foltia_subtitle.startdatetime ,
foltia_subtitle.lengthmin ,
foltia_tvrecord.bitrate ,
foltia_subtitle.pid  
FROM foltia_tvrecord
LEFT OUTER JOIN foltia_subtitle on (foltia_tvrecord.tid = foltia_subtitle.tid )
LEFT OUTER JOIN foltia_program on (foltia_tvrecord.tid = foltia_program.tid )
LEFT OUTER JOIN foltia_station on (foltia_subtitle.stationid = foltia_station.stationid )
WHERE foltia_tvrecord.stationid = 0 AND
foltia_subtitle.enddatetime >= '$now' ORDER BY \"startdatetime\" ASC
	";

	$reservedrs = m_query($con, $query, "DBクエリに失敗しました");
	$reservedmaxrows = pg_num_rows($reservedrs);
	
	if ($reservedmaxrows > 0 ){
	for ($rrow = 0; $rrow < $reservedmaxrows ; $rrow++) {
		$rowdata = pg_fetch_row($reservedrs, $rrow);
		$reservedpid[] = $rowdata[8];
	}
	}else{
	$reservedpid = "";
	}//end if

$mode = getgetform(mode);

if ($mode == "new"){
//新番組表示モード
	$query = "
	SELECT 
foltia_program .tid,
stationname,
foltia_program .title,
foltia_subtitle.countno,
foltia_subtitle.subtitle,
foltia_subtitle.startdatetime ,
foltia_subtitle.lengthmin  , 
foltia_subtitle.pid ,
foltia_subtitle.startoffset   
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid 
 AND foltia_subtitle.enddatetime >= '$now'  AND foltia_subtitle.countno = '1' 
ORDER BY foltia_subtitle.startdatetime  ASC
	";
	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$maxrows = pg_num_rows($rs);


}else{
	$query = "
	SELECT 
foltia_program .tid,
stationname,
foltia_program .title,
foltia_subtitle.countno,
foltia_subtitle.subtitle,
foltia_subtitle.startdatetime ,
foltia_subtitle.lengthmin , 
foltia_subtitle.pid  , 
foltia_subtitle.startoffset   
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid 
 AND foltia_subtitle.enddatetime >= '$now'  
ORDER BY foltia_subtitle.startdatetime  ASC
	";
	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$maxrows = pg_num_rows($rs);

}//end if

if ($maxrows == 0) {
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

<?
		/* フィールド数 */
		$maxcols = pg_num_fields($rs);
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
			for ($row = 0; $row < $maxrows; $row++) { /* 行に対応 */
				/* pg_fetch_row で一行取り出す */
				$rowdata = pg_fetch_row($rs, $row);

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
			}
		?>
	</tbody>
</table>


</body>
</html>
