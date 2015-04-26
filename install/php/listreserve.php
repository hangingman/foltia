<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

listreserve.php

目的
録画予約番組放映予定と予約番組名を表示します。

引数
r:録画デバイス数
startdate:特定日付からの予約状況。YYYYmmddHHii形式で。表示数に限定かけてないのでレコード数が大量になると重くなるかも知れません。


 DCC-JPL Japan/foltia project


History

2009/5/1 
重複予約検出処理の修正 http://www.dcc-jpl.com/foltia/ticket/7
パッチ適用
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
$userclass = getuserclass($con);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" type="text/css" href="graytable.css"> 
<title>foltia:record plan</title>
</head>

<?php
$mymemberid = getmymemberid($con);
$now = getgetnumform(startdate);
if ($now == ""){
$now = getgetnumform(date);
}

if ($now > 200501010000){
}else{
	$now = date("YmdHi");   
}
	$query = "
SELECT
 foltia_program.tid, stationname, foltia_program.title,
 foltia_subtitle.countno, foltia_subtitle.subtitle,
 foltia_subtitle.startdatetime as x, foltia_subtitle.lengthmin,
 foltia_tvrecord.bitrate, foltia_subtitle.startoffset,
 foltia_subtitle.pid, foltia_subtitle.epgaddedby,
foltia_tvrecord.digital 
FROM foltia_subtitle , foltia_program ,foltia_station ,foltia_tvrecord
WHERE foltia_tvrecord.tid = foltia_program.tid AND foltia_tvrecord.stationid = foltia_station .stationid AND foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid
AND foltia_subtitle.enddatetime >= ? 
UNION
SELECT
 foltia_program.tid, stationname, foltia_program.title,
 foltia_subtitle.countno, foltia_subtitle.subtitle,
 foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
 foltia_tvrecord.bitrate,  foltia_subtitle.startoffset,
 foltia_subtitle.pid,  foltia_subtitle.epgaddedby,
foltia_tvrecord.digital 
FROM foltia_tvrecord
LEFT OUTER JOIN foltia_subtitle on (foltia_tvrecord.tid = foltia_subtitle.tid )
LEFT OUTER JOIN foltia_program on (foltia_tvrecord.tid = foltia_program.tid )
LEFT OUTER JOIN foltia_station on (foltia_subtitle.stationid = foltia_station.stationid )
WHERE foltia_tvrecord.stationid = 0 AND
 foltia_subtitle.enddatetime >= ? ORDER BY x ASC
	";

	$rs = sql_query($con, $query, "DBクエリに失敗しました",array($now,$now));

//チューナー数
if (getgetnumform(r) != ""){
	$recunits = getgetnumform(r);
}elseif($recunits == ""){
	$recunits = 2;
}

?>

<body BGCOLOR="#ffffff" TEXT="#494949" LINK="#0047ff" VLINK="#000000" ALINK="#c6edff" >
<div align="center">
<?php 
printhtmlpageheader();
?>
  <p align="left"><font color="#494949" size="6">予約一覧</font></p>
  <hr size="4">
<p align="left">録画予約番組放映予定と予約番組名を表示します。</p>

<?php
     $rowdata = $rs->fetch();
     if (! $rowdata) {
		print "番組データがありません<BR>\n";			
		}else{
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
			<th align="left">画質</th>
			<th align="left">デジタル優先</th>

		</tr>
	</thead>

	<tbody>
		<?php
			/* テーブルのデータを出力 */
		  do {
				echo("<tr>\n");

$pid = htmlspecialchars($rowdata[9]);

$tid = htmlspecialchars($rowdata[0]);
$title = htmlspecialchars($rowdata[2]);
$subtitle = htmlspecialchars($rowdata[4]);
$dbepgaddedby = htmlspecialchars($rowdata[10]);

//重複検出
//開始時刻 $rowdata[5]
//終了時刻
$endtime = calcendtime($rowdata[5],$rowdata[6]);
//番組の開始時刻より遅い時刻に終了し、終了時刻より前にはじまる番組があるかどうか
//オンボードチューナー録画
$query = "
SELECT
 foltia_program.tid, stationname, foltia_program.title,
 foltia_subtitle.countno, foltia_subtitle.subtitle,
 foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
 foltia_tvrecord.bitrate, foltia_subtitle.startoffset,
 foltia_subtitle.pid, foltia_tvrecord.digital
FROM foltia_subtitle , foltia_program ,foltia_station ,foltia_tvrecord
WHERE foltia_tvrecord.tid = foltia_program.tid AND foltia_tvrecord.stationid = foltia_station .stationid AND foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid
AND foltia_subtitle.enddatetime > ? 
AND foltia_subtitle.startdatetime < ?  
UNION
SELECT
 foltia_program.tid, stationname, foltia_program.title,
 foltia_subtitle.countno, foltia_subtitle.subtitle,
 foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
 foltia_tvrecord.bitrate, foltia_subtitle.startoffset,
 foltia_subtitle.pid, foltia_tvrecord.digital
FROM foltia_tvrecord
LEFT OUTER JOIN foltia_subtitle on (foltia_tvrecord.tid = foltia_subtitle.tid )
LEFT OUTER JOIN foltia_program on (foltia_tvrecord.tid = foltia_program.tid )
LEFT OUTER JOIN foltia_station on (foltia_subtitle.stationid = foltia_station.stationid )
WHERE foltia_tvrecord.stationid = 0 AND
foltia_subtitle.enddatetime > ?  
AND foltia_subtitle.startdatetime < ?  
";
	$rclass = "";
	$overlap = sql_query($con, $query, "DBクエリに失敗しました",array($rowdata[5],$endtime,$rowdata[5],$endtime));
			  $owrowall = $overlap->fetchAll();
			  $overlapmaxrows = count($owrowall);
	if ($overlapmaxrows > ($recunits) ){
		
		$owtimeline = array();
		
		for ($rrow = 0; $rrow < $overlapmaxrows ; $rrow++) {
					  $owrowdata = $owrowall[$rrow];
			$owtimeline[ $owrowdata['startdatetime'] ] = $owtimeline[ $owrowdata['startdatetime'] ] +1;
			
			$owrend = calcendtime( $owrowdata['startdatetime'], $owrowdata['lengthmin'] );
			$owtimeline[ $owrend ] = $owtimeline[ $owrend ] -1;
			//注意: NULL に減算子を適用しても何も起こりませんが、NULL に加算子を 適用すると 1 となります。
		}
		
		ksort ( $owtimeline );
		
		$owcount = 0;
		foreach ( $owtimeline as $key => $val ) {
			$owcount += $val;
			
			if ( $owcount > $recunits ) {
				$rclass = "overwraped";
				break;
			}
		}
	}

//外部チューナー録画
$externalinputs = 1; //現状一系統のみ
$query = "
SELECT
 foltia_program.tid, stationname, foltia_program.title,
 foltia_subtitle.countno, foltia_subtitle.subtitle,
 foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
 foltia_tvrecord.bitrate, foltia_subtitle.startoffset,
 foltia_subtitle.pid, foltia_tvrecord.digital
FROM foltia_subtitle , foltia_program ,foltia_station ,foltia_tvrecord
WHERE foltia_tvrecord.tid = foltia_program.tid AND foltia_tvrecord.stationid = foltia_station .stationid AND foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid
AND foltia_subtitle.enddatetime > ? 
AND foltia_subtitle.startdatetime < ?  
AND  (foltia_station.stationrecch = '0' OR  foltia_station.stationrecch = '-1' ) 
UNION
SELECT
 foltia_program.tid, stationname, foltia_program.title,
 foltia_subtitle.countno, foltia_subtitle.subtitle,
 foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
 foltia_tvrecord.bitrate, foltia_subtitle.startoffset,
 foltia_subtitle.pid, foltia_tvrecord.digital
FROM foltia_tvrecord
LEFT OUTER JOIN foltia_subtitle on (foltia_tvrecord.tid = foltia_subtitle.tid )
LEFT OUTER JOIN foltia_program on (foltia_tvrecord.tid = foltia_program.tid )
LEFT OUTER JOIN foltia_station on (foltia_subtitle.stationid = foltia_station.stationid )
WHERE foltia_tvrecord.stationid = 0 AND
foltia_subtitle.enddatetime > ?  
AND foltia_subtitle.startdatetime < ?  
AND  (foltia_station.stationrecch = '0' OR  foltia_station.stationrecch = '-1' ) 
	";
	$eoverlap = sql_query($con, $query, "DBクエリに失敗しました",array($rowdata[5], $endtime,$rowdata[5],  $endtime));
			  $eowrowall = $eoverlap->fetchAll();
			  $eoverlapmaxrows = count($eowrowall);
	if ($eoverlapmaxrows > ($externalinputs) ){
		
		$eowtimeline = array();
		
		for ($erow = 0; $erow < $eoverlapmaxrows ; $erow++) {
					  $eowrowdata = $eowrowall[$erow];
			$eowtimeline[ $eowrowdata['startdatetime'] ] = $eowtimeline[ $eowrowdata['startdatetime'] ] +1;
			
			$eowrend = calcendtime( $eowrowdata['startdatetime'], $eowrowdata['lengthmin'] );
			$eowtimeline[ $eowrend ] = $eowtimeline[ $eowrend ] -1;
		}
		
		ksort ( $eowtimeline );
		
		$eowcount = 0;
		foreach ( $eowtimeline as $key => $val ) {
			$eowcount += $val;
			
			if ( $eowcount > $externalinputs ) {
				$rclass = "exoverwraped";
				break;
			}
		}
	
	}
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
					//if ( $userclass <= 2){
					if (($mymemberid == $dbepgaddedby)||($userclass <= 1)){
						if ($userclass <= 1 ){//管理者なら
							$membername = getmemberid2name($con,$dbepgaddedby);
							$membername = ":" . $membername ;
						}else{
						$membername = "";
						}
					print "<td>$subtitle [<a href=\"delepgp.php?pid=$pid\">予約解除</a>$membername]<br></td>\n";
					}else{
					print "<td>$subtitle [解除不能]<br></td>\n";
					}
					}
					// 開始時刻(ズレ)
					echo("<td>".htmlspecialchars(foldate2print($rowdata[5]))."<br>(".htmlspecialchars($rowdata[8]).")</td>\n");
					// 総尺
					echo("<td>".htmlspecialchars($rowdata[6])."<br></td>\n");
					
					//録画レート
					echo("<td>".htmlspecialchars($rowdata[7])."<br></td>\n");
					
					//デジタル優先
					echo("<td>");
					if (htmlspecialchars($rowdata[11]) == 1){
					print "する";
					}else{
					print "しない";
					}
					echo("<br></td>\n");
				echo("</tr>\n");
		  } while ($rowdata = $rs->fetch());
		?>
	</tbody>
</table>


<table>
	<tr><td>アナログ重複表示</td><td><br /></td></tr>
	<tr><td>エンコーダ数</td><td><?php print "$recunits"; ?></td></tr>
	<tr class="overwraped"><td>チューナー重複</td><td><br /></td></tr>
	<tr class="exoverwraped"><td>外部入力重複</td><td><br /></td></tr>
</table>


<?php
    } //if ($maxrows == 0)
	$query = "
SELECT 
 foltia_program.tid, stationname, foltia_program.title,
 foltia_tvrecord.bitrate, foltia_tvrecord.stationid, 
foltia_tvrecord.digital   
FROM  foltia_tvrecord , foltia_program , foltia_station 
WHERE foltia_tvrecord.tid = foltia_program.tid  AND foltia_tvrecord.stationid = foltia_station .stationid 
ORDER BY foltia_program.tid  DESC
";
	$rs = sql_query($con, $query, "DBクエリに失敗しました");
$rowdata = $rs->fetch();			
if (! $rowdata) {
//なければなにもしない
			
		}else{
	$maxcols = $rs->columnCount();

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
			<th align="left">デジタル優先</th>

		</tr>
	</thead>

	<tbody>
		<?php
			/* テーブルのデータを出力 */
	     do {
				$tid = htmlspecialchars($rowdata[0]);
				
				if ($tid > 0){
				echo("<tr>\n");
				//予約解除
				if ( $userclass <= 1){
					echo("<td><a href=\"delreserve.php?tid=$tid&sid=" .
					htmlspecialchars($rowdata[4])  . "\">解除</a></td>\n");
				}else{
				echo("<td>−</td>");		
				}
				//TID
					echo("<td><a href=\"reserveprogram.php?tid=$tid\">$tid</a></td>\n");
				     //放映局
				     echo("<td>".htmlspecialchars($rowdata[1])."<br></td>\n");
				     //タイトル
				     echo("<td><a href=\"http://cal.syoboi.jp/tid/$tid\" target=\"_blank\">" .
				     htmlspecialchars($rowdata[2]) . "</a></td>\n");

					//MP4
					echo("<td><a href=\"showlibc.php?tid=$tid\">mp4</a></td>\n");
					//画質(アナログビットレート)
					echo("<td>".htmlspecialchars($rowdata[3])."<br></td>\n");
					//デジタル優先
					echo("<td>");
					if (htmlspecialchars($rowdata[5]) == 1){
					print "する";
					}else{
					print "しない";
					}
				echo("</tr>\n");
				}else{
				print "<tr>
				<td>−</td><td>0</td>
				<td>[全局]<br></td>
				<td>EPG録画</td>
				<td><a href=\"showlibc.php?tid=0\">mp4</a></td>";
				echo("<td>".htmlspecialchars($rowdata[3])."<br></td>");
					//デジタル優先
					echo("<td>");
					if (htmlspecialchars($rowdata[5]) == 1){
					print "する";
					}else{
					print "しない";
					}
				echo("\n</tr>");
				}//if tid 0
	     } while ($rowdata = $rs->fetch());
		}//else
		?>
	</tbody>
</table>


</body>
</html>
