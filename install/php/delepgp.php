<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

delepgp.php

目的
EPG録画予約の予約解除を行います

引数
pid:プログラムID
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
printtitle("<title>foltia:delete EPG Program</title>", false);
?>

<body BGCOLOR="#ffffff" TEXT="#494949" LINK="#0047ff" VLINK="#000000" ALINK="#c6edff" >

<?php

	printhtmlpageheader();

$pid = getgetnumform(pid);
		if ($pid == "") {
		die_exit("番組がありません<BR>");
		}

$now = date("YmdHi");   


//タイトル取得
$query = "
SELECT 
foltia_subtitle.pid  , 
foltia_subtitle.stationid , 
foltia_subtitle.countno , 
foltia_subtitle.subtitle  ,
foltia_subtitle.startdatetime , 
foltia_subtitle.enddatetime ,
foltia_subtitle.lengthmin ,
foltia_station.stationname , 
foltia_station.stationrecch 
FROM foltia_subtitle , foltia_station 
WHERE foltia_subtitle.tid = 0 AND 
foltia_station.stationid = foltia_subtitle.stationid AND 
foltia_subtitle.pid = ? 
 ";

	$rs = sql_query($con, $query, "DBクエリに失敗しました",array($pid));
	$rowdata = $rs->fetch();
	$rs->closeCursor();

		if (!is_array($rowdata) || empty($rowdata)) {
			die_exit("登録番組がありません<BR>");
		}

		$pid = htmlspecialchars($rowdata[0]);
		$stationid = htmlspecialchars($rowdata[1]);
		$countno = htmlspecialchars($rowdata[2]);
		$subtitle = htmlspecialchars($rowdata[3]);
		$starttime = htmlspecialchars($rowdata[4]);
		$startprinttime = htmlspecialchars(foldate2print($rowdata[4]));
		$endtime = htmlspecialchars($rowdata[5]);
		$endprinttime = htmlspecialchars(foldate2print($rowdata[5]));
		$lengthmin = htmlspecialchars($rowdata[6]);
		$stationjname = htmlspecialchars($rowdata[7]);
		$recch = htmlspecialchars($rowdata[8]);
$delflag = getgetnumform(delflag);
?>

  <p align="left"><font color="#494949" size="6">EPG予約解除</font></p>
  <hr size="4">
<?php
if ($delflag == "1") {
	//時刻確認
	if ($now < $starttime ){
		print "EPG予約「".$subtitle."」の録画予約を解除しました。 <br>\n";
		
		//削除処理
		if (($demomode) || ($protectmode) ){
		//demomodeやprotectmodeならなにもしない
		}else{
		//キュー更新
//		$oserr = system("$toolpath/perl/addatq.pl 0 $stationid ");
		$oserr = system("$toolpath/perl/addpidatq.pl $pid ");
		//DB削除
		$query = "
		DELETE  
		FROM  foltia_subtitle  
		WHERE foltia_subtitle.pid = ? AND  foltia_subtitle.tid = 0 ";
			$rs = sql_query($con, $query, "DBクエリに失敗しました",array($pid));
		}
	}else{
		print "<strong>過去番組は予約削除出来ません。</strong>";
	}//end if

}else{//delflagが1じゃなければ

	//時刻確認
	if ($now < $starttime ){
	print "EPG予約「".$subtitle."」の録画予約を解除します。 <br>\n";

	print "<form name=\"deletereserve\" method=\"GET\" action=\"delepgp.php\">
	<input type=\"submit\" value=\"予約解除\" >\n";
	}else{
	print "<strong>過去番組は予約削除出来ません。</strong>";
	}//end if
}

print "<br>
	<table width=\"100%\" border=\"0\">
    <tr><td>放送局</td><td>$stationjname</td></tr>
    <tr><td>放送開始</td><td>$startprinttime</td></tr>
    <tr><td>放送終了</td><td>$endprinttime</td></tr>
    <tr><td>尺(分)</td><td>$lengthmin</td></tr>
    <tr><td>放送チャンネル</td><td>$recch</td></tr>
    <tr><td>番組名</td><td>$subtitle</td></tr>
    <tr><td>番組ID</td><td>$pid</td></tr>
    <tr><td>局コード</td><td>$stationid</td></tr>
	
</table>
";

if ($delflag == "1") {

}else{
print "
<input type=\"hidden\" name=\"pid\" value=\"$pid\">
<input type=\"hidden\" name=\"delflag\" value=\"1\">
</form>\n";

}

?>  
</table>

</body>
</html>
