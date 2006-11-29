<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

reserveprogram.php

目的
番組の予約登録をします。

引数
tid:タイトルID
station:録画局
bitrate:録画ビットレート(単位:Mbps)

 DCC-JPL Japan/foltia project

*/
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" type="text/css" href="graytable.css"> 
<title>foltia</title>
</head>

<?php

  include("./foltialib.php");

$tid = getgetnumform(tid);
		if ($tid == "") {
		die_exit("番組が指定されていません<BR>");
		}

$station = getgetnumform(station);
		if ($station == "") {
		$station = 0;
		}

$bitrate = getgetnumform(bitrate);
		if ($bitrate == "") {
		$bitrate = 5;
		}


$con = m_connect();
$now = date("YmdHi");   

//タイトル取得
	$query = "select title from foltia_program where tid='$tid'";
	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$maxrows = pg_num_rows($rs);
			
		if ($maxrows == 0) {
		$title = "(未登録)";
		}else{
		$rowdata = pg_fetch_row($rs, 0);
		$title = htmlspecialchars($rowdata[0]);
		}

?>
<body BGCOLOR="#ffffff" TEXT="#494949" LINK="#0047ff" VLINK="#000000" ALINK="#c6edff" >

<?php 
	printhtmlpageheader();
?>
  <p align="left"><font color="#494949" size="6">予約完了</font></p>
  <hr size="4">

「<?=$title?>」を番組予約モードで予約しました。 <br>
 <br>
予約スケジュール <BR>

<?php

if ($station != 0){
//局限定
	$query = "
SELECT 
foltia_subtitle.pid ,  
stationname,
foltia_subtitle.countno,
foltia_subtitle.subtitle,
foltia_subtitle.startdatetime ,
foltia_subtitle.lengthmin ,
foltia_subtitle.startoffset 
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid 
 AND foltia_station.stationid = $station 
 AND foltia_subtitle.startdatetime >=  '$now'  AND foltia_program.tid ='$tid' 
ORDER BY foltia_subtitle.startdatetime  ASC
";

}else{
//全局
	$query = "
SELECT 
foltia_subtitle.pid ,  
stationname,
foltia_subtitle.countno,
foltia_subtitle.subtitle,
foltia_subtitle.startdatetime ,
foltia_subtitle.lengthmin ,
foltia_subtitle.startoffset 
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid 
 AND foltia_subtitle.startdatetime >=  '$now'  AND foltia_program.tid ='$tid' 
ORDER BY foltia_subtitle.startdatetime  ASC
";

}
	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$maxrows = pg_num_rows($rs);
			
		if ($maxrows == 0) {
		echo("放映予定はいまのところありません<BR>");
		}
		else{
		$maxcols = pg_num_fields($rs);		
?>
  <table BORDER="0" CELLPADDING="0" CELLSPACING="2" WIDTH="100%" BGCOLOR="#bcf1be">
	<thead>
		<tr>
			<th align="left">PID</th>
			<th align="left">放映局</th>
			<th align="left">話数</th>
			<th align="left">サブタイトル</th>
			<th align="left">開始時刻</th>
			<th align="left">総尺</th>
			<th align="left">時刻ずれ</th>

		</tr>
	</thead>

	<tbody>
		<?php
			/* テーブルのデータを出力 */
			for ($row = 0; $row < $maxrows; $row++) { /* 行に対応 */
				echo("<tr>\n");
				/* pg_fetch_row で一行取り出す */
				$rowdata = pg_fetch_row($rs, $row);

				for ($col = 0; $col < $maxcols; $col++) { /* 列に対応 */
					echo("<td>".htmlspecialchars($rowdata[$col])."<br></td>\n");
				}
				echo("</tr>\n");
			}
		}//end if
		?>
	</tbody>
</table>


<?php
if ($demomode){
}else{
//foltia_tvrecord　書き込み
//既存が予約あって、新着が全局予約だったら
if ($station ==0){
	$query = "
SELECT 
 * 
FROM foltia_tvrecord  
WHERE tid = '$tid' 
";
	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$maxrows = pg_num_rows($rs);
	if ($maxrows > 0){
	//既存局を消す
		$query = "DELETE 
FROM foltia_tvrecord  
WHERE tid = '$tid' 
";
	$rs = m_query($con, $query, "DBクエリに失敗しました");
		}
}//endif

	$query = "
SELECT 
 * 
FROM foltia_tvrecord  
WHERE tid = '$tid'  AND stationid = '$station' 
";
	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$maxrows = pg_num_rows($rs);

		if ($maxrows == 0) { //新規追加
				$query = "INSERT INTO  foltia_tvrecord  values ('$tid','$station','$bitrate')";
				$rs = m_query($con, $query, "DB書き込みに失敗しました");
		}else{//修正　(ビットレート)
			$query = "UPDATE  foltia_tvrecord  SET 
  bitrate = '$bitrate' WHERE tid = '$tid'  AND stationid = '$station'
			";
			$rs = m_query($con, $query, "DB書き込みに失敗しました");
		}
	
//キュー入れプログラムをキック
//引数　TID チャンネルID
//echo("$toolpath/perl/addatq.pl $tid $station");
$oserr = system("$toolpath/perl/addatq.pl $tid $station");
}//end if demomode
?>


</body>
</html>
