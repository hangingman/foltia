<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

deletemovie.php

目的
指定された番組を削除処理します。

引数
showplaylist.phpから削除対象mpeg2リスト。

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
<title>foltia:ファイル削除</title>
</head>

<?php
$now = date("YmdHi");   

$delete = $_POST['delete'];

?>

<body BGCOLOR="#ffffff" TEXT="#494949" LINK="#0047ff" VLINK="#000000" ALINK="#c6edff" >
<div align="center">
<?php 
printhtmlpageheader();
?>
  <p align="left"><font color="#494949" size="6">録画番組削除</font></p>
  <hr size="4">
<?php
if ($delete == ""){
	print "<p align=\"left\">削除番組はありません。</p>\n";
}else{


$userclass = getuserclass($con);
if ( $userclass <= 1){

print "<p align=\"left\">次の番組を削除しました。</p>
  <table BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"2\" WIDTH=\"100%\">
	<thead>
		<tr>
			<th align=\"left\">ファイル名</th>
			<th align=\"left\">タイトル</th>
			<th align=\"left\">話数</th>
			<th align=\"left\">サブタイ</th>
		</tr>
	</thead>
	<tbody>";




foreach ($delete as $fName) {

		$filesplit = split("-",$fName);
	
if ($filesplit[1] == ""){
$query = "
SELECT 
foltia_program.tid,foltia_program.title,foltia_subtitle.subtitle  
FROM foltia_subtitle , foltia_program   
WHERE foltia_program.tid = foltia_subtitle.tid  
 AND foltia_subtitle.tid = ? 
";
//$rs = m_query($con, $query, "DBクエリに失敗しました");
$rs = sql_query($con, $query, "DBクエリに失敗しました",array($filesplit[0]));
				$rall = $rs->fetchAll();
				$rowdata = $rall[$row];
//print" $fName./$rowdata[1]//$rowdata[2]<BR>\n";
$title = $rowdata[1];
$subtitle = "";
$count = "";
}else{

$query = "
SELECT 
foltia_program.tid,foltia_program.title,foltia_subtitle.countno,foltia_subtitle.subtitle  
FROM foltia_subtitle , foltia_program   
WHERE foltia_program.tid = foltia_subtitle.tid  
 AND foltia_subtitle.tid = ? 
 AND foltia_subtitle.countno = ? 
";
//$rs = m_query($con, $query, "DBクエリに失敗しました");
$rs = sql_query($con, $query, "DBクエリに失敗しました",array($filesplit[0] ,$filesplit[1]));
				$rall = $rs->fetchAll();
				$rowdata = $rall[$row];
//print" $fName./$rowdata[1]/$rowdata[2]/$rowdata[3]<BR>\n";
$title = $rowdata[1];
$count = $rowdata[2];
$subtitle = $rowdata[3];

}//end if 話数がNULL

$tid = htmlspecialchars($rowdata[0]);
$title = htmlspecialchars($title);
$count = htmlspecialchars($count);
$subtitle = htmlspecialchars($subtitle);

print "
<tr>
<td>$fName<br></td>
<td><a href=\"http://cal.syoboi.jp/tid/$tid\" target=\"_blank\">$title</a></td>
<td>$count<br></td>
<td>$subtitle<br></td>
</tr>\n
";

//DBから削除
if ($demomode){
}else{

$query = "
DELETE  FROM  foltia_m2pfiles  
WHERE m2pfilename = ? 
";
//$rs = m_query($con, $query, "DBクエリに失敗しました");
$rs = sql_query($con, $query, "DBクエリに失敗しました",array($fName));

//削除処理
$oserr = system("$toolpath/perl/deletemovie.pl $fName");
}//end if demomode

}//foreach

print "	</tbody></table>\n";

}else{//権限なし
	print "<p align=\"left\">ファイル削除権限がありません。</p>";
}
}//if $delete == ""
?>

</body>
</html>
