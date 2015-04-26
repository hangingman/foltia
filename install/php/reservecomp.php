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

<?php

printtitle("<title>foltia</title>", false);

$tid = getgetnumform(tid);
		if ($tid == "") {
		die_exit("番組が指定されていません<BR>");
		}

$station = getgetnumform(station);
		if ($station == "") {
		$station = 0;
		}
$usedigital = getgetnumform(usedigital);
		if ($usedigital == "") {
		$usedigital = 0;
		}
$bitrate = getgetnumform(bitrate);
		if ($bitrate == "") {
		$bitrate = 5;
		}


$now = date("YmdHi");   

//タイトル取得
	$query = "select title from foltia_program where tid = ? ";
	$rs = sql_query($con, $query, "DBクエリに失敗しました",array($tid));
$rowdata = $rs->fetch();
if (! $rowdata) {
		$title = "(未登録)";
		}else{
		$title = htmlspecialchars($rowdata[0]);
		}

?>
<body BGCOLOR="#ffffff" TEXT="#494949" LINK="#0047ff" VLINK="#000000" ALINK="#c6edff" >

<?php 
	printhtmlpageheader();
?>
  <p align="left"><font color="#494949" size="6">予約完了</font></p>
  <hr size="4">

「<?php print "$title"; ?>」を番組予約モードで予約しました。 <br>
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
$rowdata = $rs->fetch();
if (! $rowdata) {
		echo("放映予定はいまのところありません<BR>");
		}
		else{
	$maxcols = $rs->columnCount();
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
       do {
				echo("<tr>\n");
				for ($col = 0; $col < $maxcols; $col++) { /* 列に対応 */
					echo("<td>".htmlspecialchars($rowdata[$col])."<br></td>\n");
				}
				echo("</tr>\n");
       } while ($rowdata = $rs->fetch());
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
	//既存局を消す
		$query = "DELETE 
FROM foltia_tvrecord  
WHERE tid = ? 
";
	$rs = sql_query($con, $query, "DBクエリに失敗しました",array($tid));
}//endif

	$query = "
SELECT 
count(*) 
FROM foltia_tvrecord  
WHERE tid = ?  AND stationid = ? 
";
	$rs = sql_query($con, $query, "DBクエリに失敗しました",array($tid,$station));
	$maxrows = $rs->fetchColumn(0);
		if ($maxrows == 0) { //新規追加
				$query = "INSERT INTO  foltia_tvrecord  values (?,?,?,?)";
				$rs = sql_query($con, $query, "DB書き込みに失敗しました",array($tid,$station,$bitrate,$usedigital));
		}else{//修正　(ビットレート)
			$query = "UPDATE  foltia_tvrecord  SET 
  bitrate = ? , digital = ? WHERE tid = ? AND stationid = ? ";
			$rs = sql_query($con, $query, "DB書き込みに失敗しました",array( $bitrate, $usedigital , $tid , $station ));
		}
	
//キュー入れプログラムをキック
//引数　TID チャンネルID
//echo("$toolpath/perl/addatq.pl $tid $station");
$oserr = system("$toolpath/perl/addatq.pl $tid $station");
}//end if demomode
?>


</body>
</html>
