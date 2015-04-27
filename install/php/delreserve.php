<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

delreserve.php

目的
自動録画の予約解除を行います

引数
tid:タイトルID
sid:放送局ID
delflag:確認フラグ

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

printtitle("<title>foltia:delete schedule</title>", false);

$tid = getgetnumform("tid");
if ($tid == "") {
    die_exit("番組がありません<BR>");
}
$sid = getgetnumform("sid");
if ($sid == "") {
    die_exit("局がありません<BR>");
}

$now = date("YmdHi");   

?>

<body>

<?php 
     printhtmlpageheader();

//タイトル取得
$query = "
SELECT 
foltia_program.tid,
stationname,
foltia_program .title ,
foltia_tvrecord.bitrate ,
foltia_tvrecord.stationid  
FROM  foltia_tvrecord , foltia_program , foltia_station 
WHERE foltia_tvrecord.tid = foltia_program.tid  AND foltia_tvrecord.stationid = foltia_station .stationid  AND foltia_tvrecord.tid = ? AND foltia_tvrecord.stationid = ?  ";

$rs = sql_query($con, $query, "DBクエリに失敗しました",array($tid,$sid));
$rowdata = $rs->fetch();

if (! $rowdata ) {
		die_exit("登録番組がありません<BR>");
		}
		$tid = htmlspecialchars($rowdata[0]);
		$stationname = htmlspecialchars($rowdata[1]);
		$title = htmlspecialchars($rowdata[2]);
		$bitrate = htmlspecialchars($rowdata[3]);
		$stationid = htmlspecialchars($rowdata[4]);

$delflag = getgetnumform(delflag);

?>

<p align="left"><font color="#494949" size="6">予約解除</font></p>
<hr size="4">

<?php
if ($delflag == "1") {
	print "「".$title."」の自動録画予約を解除しました。 <br>\n";

//削除処理
if (($demomode) || ($protectmode) ){
//demomodeやprotectmodeならなにもしない
}else{

//キュー削除プログラムをキック
$oserr = system("$toolpath/perl/addatq.pl $tid $sid DELETE");
//DB削除
$query = "
DELETE  
FROM  foltia_tvrecord  
WHERE foltia_tvrecord.tid = ? AND foltia_tvrecord.stationid = ?  ";
$rs->closeCursor();
	$rs = sql_query($con, $query, "DBクエリに失敗しました",array($tid,$sid));
}

}else{
	print "「".$title."」の自動録画予約を解除します。 <br>\n";
    print "<form name=\"deletereserve\" method=\"GET\" action=\"delreserve.php\"><input type=\"submit\" value=\"予約解除\" >\n";
}

?>
  
<br>
<table width="100%" border="0">
  <tr>
    <td>タイトル</td>
    <td>放送局</td>
    <td>ビットレート</td>
  </tr>
  <tr>
    <td><?php print"$title"; ?></td>
    <td><?php print"$stationname"; ?></td>
    <td><?php print"$bitrate"; ?></td>

  </tr>
</table>

<?php
if ($delflag == "1") {

}else{
print "
<input type=\"hidden\" name=\"tid\" value=\"$tid\">
<input type=\"hidden\" name=\"sid\" value=\"$sid\">
<input type=\"hidden\" name=\"delflag\" value=\"1\">
</form>\n";

}

?>  

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

$rs = sql_query($con, $query, "DBクエリに失敗しました",array($now,$tid));
$rowdata = $rs->fetch();
if (! $rowdata) {
    echo("放映予定はありません<BR>");
} else {
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
					echo("<td>".htmlspecialchars($rowdata[$col])."<br></td>\n");
				}
				echo("</tr>\n");
	     } while ($row = $rs->fetch());
		}//end if
		?>
	</tbody>
</table>

</body>
</html>
