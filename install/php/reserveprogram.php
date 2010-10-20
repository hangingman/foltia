<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

reserveprogram.php

目的
番組録画予約ページを表示します。

引数
tid:タイトルID

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
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" type="text/css" href="graytable.css"> 
<title>foltia</title>
</head>

<?php


$tid = getgetnumform(tid);
		if ($tid == "") {
		die_exit("登録番組がありません<BR>");
		}


$now = date("YmdHi");   

//タイトル取得
	$query = "select title from foltia_program where tid = ? ";
//	$rs = m_query($con, $query, "DBクエリに失敗しました");
$rs = sql_query($con, $query, "DBクエリに失敗しました",array($tid));
$rowdata = $rs->fetch();
if (! $rowdata) {
		die_exit("登録番組がありません<BR>");
		}

		$title = htmlspecialchars($rowdata[0]);
?>
<body BGCOLOR="#ffffff" TEXT="#494949" LINK="#0047ff" VLINK="#000000" ALINK="#c6edff" >

<?php 
	printhtmlpageheader();
?>

  <p align="left"><font color="#494949" size="6">番組予約</font></p>
  <hr size="4">

<?php
if ($tid == 0){
	print "<p>EPG予約の追加は「<a href=\"./viewepg.php\">番組表</a>」メニューから行って下さい。</p>\n</body>\n</html>\n";
	exit ;
}

?>

「<?php print "$title"; ?>」を番組予約モードで録画予約します。 <br>

  
<form name="recordingsetting" method="GET" action="reservecomp.php">
<input type="submit" value="予約" >
<br>
<table width="100%" border="0">
  <tr>
    <td>放送局</td>
    <td>デジタル録画優先</td>
    <td>アナログビットレート</td>
  </tr>
  <tr>
    <td>
<?php	
	//録画候補局検索
		$query = "
SELECT distinct  foltia_station.stationid , stationname , foltia_station.stationrecch 
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid 
 AND foltia_program.tid = ? 
ORDER BY stationrecch DESC
";
//	$rs = m_query($con, $query, "DBクエリに失敗しました");
$rs = sql_query($con, $query, "DBクエリに失敗しました",array($tid));
$rowdata = $rs->fetch();
if (! $rowdata) {
		echo("放映局情報がまだはいってません<BR>");
		}
		else{
	$maxcols = $rs->columnCount();
		
			echo("<select name=\"station\">\n");
			/* テーブルのデータを出力 */
	do {
				echo("<option value=\"");
				echo(htmlspecialchars($rowdata[0]));
				echo("\">");
				echo(htmlspecialchars($rowdata[1]));
				echo("</option>\n");
	} while ($rowdata = $rs->fetch());
			echo("<option value=\"0\">全局</option>\n</select>\n");
		}//endif		
	?>

	</td>
	
	<td>
	<select name="usedigital">
	<?php
	 if ($usedigital == 1){
	 	print "
		<option value=\"1\" selected>する</option>
		<option value=\"0\">しない</option>
		";
		}else{
	 	print "
		<option value=\"1\">する</option>
		<option value=\"0\" selected>しない</option>
		";
		}
	?>
	</select>
	</td>

    <td><select name="bitrate">
        <option value="14">最高画質</option>
        <option value="13">13Mbps</option>
        <option value="12">12Mbps</option>
        <option value="11">11Mbps</option>
        <option value="10">10Mbps</option>
        <option value="9">9Mbps</option>
        <option value="8">高画質</option>
        <option value="7">7Mbps</option>
        <option value="6">6Mbps</option>
        <option value="5" selected>標準画質</option>
        <option value="4">4Mbps</option>
        <option value="3">3Mbps</option>
        <option value="2">高い圧縮</option>
      </select></td>
  </tr>
</table>
<input type="hidden" name="tid" value="<?=$tid?>">
</form>
<p>&nbsp; </p>
<p><br>
今後の放映予定 </p>

<?php
	$query = "
SELECT 
stationname,
foltia_subtitle.countno,
foltia_subtitle.subtitle,
foltia_subtitle.startdatetime ,
foltia_subtitle.lengthmin ,
foltia_subtitle.startoffset 
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid 
 AND foltia_subtitle.startdatetime >= ?  AND foltia_program.tid = ? 
ORDER BY foltia_subtitle.startdatetime  ASC
";
//	$rs = m_query($con, $query, "DBクエリに失敗しました");
$rs = sql_query($con, $query, "DBクエリに失敗しました",array($now,$tid));
$rowdata = $rs->fetch();
if (! $rowdata) {
		echo("放映予定はありません<BR>");
		}
		else{
	$maxcols = $rs->columnCount();
?>
  <table BORDER="0" CELLPADDING="0" CELLSPACING="2" WIDTH="100%" BGCOLOR="#bcf1be">
	<thead>
		<tr>
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
       do {
				echo("<tr>\n");
				for ($col = 0; $col < $maxcols; $col++) { /* 列に対応 */
					if ($col == 3){
					echo("<td>".htmlspecialchars(foldate2print($rowdata[$col]))."<br></td>\n");
					}else{
					echo("<td>".htmlspecialchars($rowdata[$col])."<br></td>\n");
					}
				}
				echo("</tr>\n");
       } while ($rowdata = $rs->fetch());
		}//end if
		?>
	</tbody>
</table>



</body>
</html>
