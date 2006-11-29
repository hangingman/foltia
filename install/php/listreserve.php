<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

listreserve.php

目的
録画予約番組放映予定と予約番組名を表示します。

引数
なし

 DCC-JPL Japan/foltia project

*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" type="text/css" href="graytable.css"> 
<title>foltia:record plan</title>
</head>

<?php

  include("./foltialib.php");

$con = m_connect();

$now = date("YmdHi");   

	$query = "
SELECT
foltia_program .tid,
stationname,
foltia_program .title,
foltia_subtitle.countno,
foltia_subtitle.subtitle,
foltia_subtitle.startdatetime ,
foltia_subtitle.lengthmin ,
foltia_tvrecord.bitrate  , 
foltia_subtitle.startoffset , 
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
foltia_subtitle.startoffset , 
foltia_subtitle.pid  
FROM foltia_tvrecord
LEFT OUTER JOIN foltia_subtitle on (foltia_tvrecord.tid = foltia_subtitle.tid )
LEFT OUTER JOIN foltia_program on (foltia_tvrecord.tid = foltia_program.tid )
LEFT OUTER JOIN foltia_station on (foltia_subtitle.stationid = foltia_station.stationid )
WHERE foltia_tvrecord.stationid = 0 AND
foltia_subtitle.enddatetime >= '$now' ORDER BY \"startdatetime\" ASC
	";

	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$maxrows = pg_num_rows($rs);
			
?>



<body BGCOLOR="#ffffff" TEXT="#494949" LINK="#0047ff" VLINK="#000000" ALINK="#c6edff" >
<div align="center">
<?php 
printhtmlpageheader();
?>
  <p align="left"><font color="#494949" size="6">予約一覧</font></p>
  <hr size="4">
<p align="left">録画予約番組放映予定と予約番組名を表示します。</p>

<?
	if ($maxrows == 0) {
		print "番組データがありません<BR>\n";			
		}else{


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
			<th align="left">開始時刻</th>
			<th align="left">総尺</th>
			<th align="left">画質</th>

		</tr>
	</thead>

	<tbody>
		<?php
			/* テーブルのデータを出力 */
			for ($row = 0; $row < $maxrows; $row++) { /* 行に対応 */
				echo("<tr>\n");
				/* pg_fetch_row で一行取り出す */
				$rowdata = pg_fetch_row($rs, $row);
$pid = htmlspecialchars($rowdata[9]);

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
					if ($protectmode) {
					print "<td>$subtitle<br></td>\n";
					}else{
					print "<td>$subtitle [<a href=\"delepgp.php?pid=$pid\">予約解除</a>]<br></td>\n";
					}
					}
					// 開始時刻(ズレ)
					echo("<td>".htmlspecialchars(foldate2print($rowdata[5]))."<br>(".htmlspecialchars($rowdata[8]).")</td>\n");
					// 総尺
					echo("<td>".htmlspecialchars($rowdata[6])."<br></td>\n");
					
					//録画レート
					echo("<td>".htmlspecialchars($rowdata[7])."<br></td>\n");
				echo("</tr>\n");
			}
		?>
	</tbody>
</table>
<?php
} //if ($maxrows == 0) {


	$query = "
SELECT 
foltia_program.tid,
stationname,
foltia_program .title ,
foltia_tvrecord.bitrate ,
foltia_tvrecord.stationid  
FROM  foltia_tvrecord , foltia_program , foltia_station 
WHERE foltia_tvrecord.tid = foltia_program.tid  AND foltia_tvrecord.stationid = foltia_station .stationid   
ORDER BY foltia_program.tid  DESC
";
	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$maxrows = pg_num_rows($rs);
			
		if ($maxrows == 0) {
//なければなにもしない
			
		}else{
		$maxcols = pg_num_fields($rs);

?>
<p align="left">録画予約番組タイトルを表示します。</p>
  <table BORDER="0" CELLPADDING="0" CELLSPACING="2" WIDTH="100%">
	<thead>
		<tr>
			<th align="left">予約解除</th>
			<th align="left">TID</th>
			<th align="left">放映局</th>
			<th align="left">タイトル</th>
			<th align="left">録画リスト</th>
			<th align="left">画質</th>
		</tr>
	</thead>

	<tbody>
		<?php
			/* テーブルのデータを出力 */
			for ($row = 0; $row < $maxrows; $row++) { /* 行に対応 */
				/* pg_fetch_row で一行取り出す */
				$rowdata = pg_fetch_row($rs, $row);

				$tid = htmlspecialchars($rowdata[0]);
				
				if ($tid > 0){
				echo("<tr>\n");
				//予約解除
				if ($protectmode) {
					echo("<td>−</td>");				
				}else{
					echo("<td><a href=\"delreserve.php?tid=$tid&sid=" .
					 htmlspecialchars($rowdata[4])  . "\">解除</a></td>\n");
				}
				//TID
					echo("<td><a href=\"reserveprogram.php?tid=$tid\">$tid</a></td>\n");
				     //放映局
				     echo("<td>".htmlspecialchars($rowdata[1])."<br></td>\n");
				     //タイトル
				     echo("<td><a href=\"http://cal.syoboi.jp/progedit.php?TID=$tid\" target=\"_blank\">" .
				     htmlspecialchars($rowdata[2]) . "</a></td>\n");

					//MP4
					echo("<td><a href=\"showlibc.php?tid=$tid\">mp4</a></td>\n");

					echo("<td>".htmlspecialchars($rowdata[3])."<br></td>\n");
	
				echo("</tr>\n");
				}else{
				print "<tr>
				<td>−</td><td>0</td>
				<td>[全局]<br></td>
				<td>EPG録画</td>
				<td><a href=\"showlibc.php?tid=0\">mp4</a></td>";
				echo("<td>".htmlspecialchars($rowdata[3])."<br></td>\n</tr>");
				}//if tid 0
			}//for
		}//else
		?>
	</tbody>
</table>


</body>
</html>
