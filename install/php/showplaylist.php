<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

showplaylist.php

目的
録画したmpeg2の番組一覧を表示します。


オプション
list:
　省略時、録画順にソートされる。
　titleのときに、番組順ソートされる。
　rawのときに、DBに記録されている番組録画情報ではなくディレクトリにあるm2p/m2tファイルを全て表示する。

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
<?php

// Syabas 判定
$useragent = $_SERVER['HTTP_USER_AGENT'];

//ディスク空き容量によって背景色表示変更
warndiskfreearea();

print "<title>foltia:recorded file list</title>
</head>";

$now = date("YmdHi");   
?>
<body BGCOLOR="#ffffff" TEXT="#494949" LINK="#0047ff" VLINK="#000000" ALINK="#c6edff" >
<div align="center">
<?php 
	printhtmlpageheader();
?>
  <p align="left"><font color="#494949" size="6">録画一覧表示</font></p>
  <hr size="4">
<p align="left">再生可能番組リストを表示します。<br>
<?php
if ($demomode){
}else{
	printdiskusage();
	printtrcnprocesses();
}
?>
<form name="deletemovie" method="POST" action="./deletemovie.php"> 
<p align="left"><input type="submit" value="項目削除" ></p>

  <table BORDER="0" CELLPADDING="0" CELLSPACING="2" WIDTH="100%">
	<thead>
		<tr>
			<th align="left">削除</th>
			<th align="left"><A HREF="./showplaylist.php">ファイル名</A></th>
			<th align="left"><A HREF="./showplaylist.php?list=title">タイトル</A></th>
			<th align="left">話数</th>
			<th align="left">サブタイ</th>
<?php
if (file_exists("./selectcaptureimage.php") ) {
print "			<th align=\"left\">キャプ</th>\n";
}
?>
		</tr>
	</thead>

	<tbody>


<?php
$list = getgetform('list');

//旧仕様
if($list == "raw"){
exec ("ls -t  $recfolderpath/*.???", $m2pfiles);


foreach($m2pfiles as $pathfName) {
$fNametmp = split("/",$pathfName);
$fName = array_pop($fNametmp);
//print "FILENAME:$fName<BR>\n";

        if(($fName == ".") or ($fName == "..") ){ continue; }
	if ((ereg(".m2.+", $fName))|| (ereg(".aac", $fName))){
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
				$rowdata = $rall[0];
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
$rs = sql_query($con, $query, "DBクエリに失敗しました",array($filesplit[0],$filesplit[1]));
				$rall = $rs->fetchAll();
				$rowdata = $rall[0];
//print" $fName./$rowdata[1]/$rowdata[2]/$rowdata[3]<BR>\n";
$title = $rowdata[1];
$count = $rowdata[2];
$subtitle = $rowdata[3];
}//if 話数あるかどうか

$tid = htmlspecialchars($rowdata[0]);
$title = htmlspecialchars($title);
$count = htmlspecialchars($count);
$subtitle = htmlspecialchars($subtitle);

//--
print "
<tr>
<td><INPUT TYPE='checkbox' NAME='delete[]' VALUE='$fName'><br></td>
<td><A HREF=\"$httpmediamappath/$fName\">$fName</A><br></td>
<td><a href=\"http://cal.syoboi.jp/tid/$tid\" target=\"_blank\">$title</a></td>
<td>$count<br></td>
<td>$subtitle<br></td>";
	if (file_exists("./selectcaptureimage.php") ) {
//	$capimgpath = preg_replace("/.m2p/", "", $fName);
	print "			<td align=\"left\"> N/A </td>\n";
	}

print "</tr>\n
";
}//ereg 
}//foreach
print "	</tbody>\n</table>\n</FORM>\n</body>\n</html>\n";
exit;
}elseif ($list== "title"){//新仕様
$query = "
SELECT 
foltia_program.tid,
foltia_program.title,
foltia_subtitle.countno,
foltia_subtitle.subtitle  ,
foltia_m2pfiles.m2pfilename  ,
foltia_subtitle.pid   
FROM foltia_subtitle , foltia_program , foltia_m2pfiles 
WHERE foltia_program.tid = foltia_subtitle.tid  
 AND foltia_subtitle.m2pfilename = foltia_m2pfiles.m2pfilename 
ORDER BY foltia_subtitle.tid  DESC , foltia_subtitle.startdatetime  ASC 


";
}else{
$query = "
SELECT 
foltia_program.tid,
foltia_program.title,
foltia_subtitle.countno,
foltia_subtitle.subtitle  ,
foltia_m2pfiles.m2pfilename  ,
foltia_subtitle.pid   
FROM foltia_subtitle , foltia_program , foltia_m2pfiles 
WHERE foltia_program.tid = foltia_subtitle.tid  
 AND foltia_subtitle.m2pfilename = foltia_m2pfiles.m2pfilename 
ORDER BY foltia_subtitle.startdatetime DESC 
";
}

//$rs = m_query($con, $query, "DBクエリに失敗しました");
$rs = sql_query($con, $query, "DBクエリに失敗しました");
$rowdata = $rs->fetch();
if ($rowdata) {
	do {
$tid = htmlspecialchars($rowdata[0]);
$title = htmlspecialchars($rowdata[1]);
$count = htmlspecialchars($rowdata[2]);
$subtitle = htmlspecialchars($rowdata[3]);
$fName  = htmlspecialchars($rowdata[4]);
$pid  = htmlspecialchars($rowdata[5]);
//--
print "
<tr>
<td><INPUT TYPE='checkbox' NAME='delete[]' VALUE='$fName'><br></td>";
if (ereg("syabas",$useragent)){
print "<td><A HREF=\"./view_syabas.php?pid=$pid\" vod=playlist>$fName</td>";
}
else{
print "<td><A HREF=\"$httpmediamappath/$fName\">$fName</A><br></td>";
}
if ($tid > 0){
print"<td><a href=\"http://cal.syoboi.jp/tid/$tid\" target=\"_blank\">$title</a></td>
<td>$count<br></td>
<td><a href = \"http://cal.syoboi.jp/tid/$tid/time#$pid\" target=\"_blank\">$subtitle</a><br></td>";
}else{
print"<td>$title</td>
<td>$count<br></td>
<td>$subtitle<br></td>";
}
	if (file_exists("./selectcaptureimage.php") ) {
	$capimgpath = preg_replace("/.m2.+/", "", $fName);
	print "			<td align=\"left\"><a href=\"./selectcaptureimage.php?pid=$pid\">キャプ</a></td>\n";
	}
print "</tr>\n
";
	} while ($rowdata = $rs->fetch());
}else{
print "
<tr>
<td COLSPAN=\"5\">ファイルがありません</td>
</tr>
";


}//end if

print "</tbody>
</table>
</FORM>\n";

//番組ソートの時、未読番組のタイトルだけ表示
if ($list== "title"){
$query = "
SELECT distinct
foltia_program.tid,
foltia_program.title 
FROM foltia_subtitle , foltia_program , foltia_m2pfiles 
WHERE foltia_program.tid = foltia_subtitle.tid  
 AND foltia_subtitle.m2pfilename = foltia_m2pfiles.m2pfilename 
ORDER BY foltia_program.tid DESC 
";

//$rs = m_query($con, $query, "DBクエリに失敗しました");
$rs = sql_query($con, $query, "DBクエリに失敗しました");
	$rowdata = $rs->fetch();
	if ($rowdata) {
print "<hr>
<p align=\"left\">未読タイトルを表示します。<br>
  <table BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"2\" WIDTH=\"100%\">
	<thead>
		<tr>
			<th align=\"left\">TID</th>
			<th align=\"left\">タイトル</th>
		</tr>
	</thead>
	<tbody>
";

		do {
$tid = htmlspecialchars($rowdata[0]);
$title = htmlspecialchars($rowdata[1]);

print "<tr><td>$tid</td><td>$title</td></tr>\n";

		} while ($rowdata = $rs->fetch());
print "</tbody></table>\n";
}//if maxrows
}//if title

?>
	

</body>
</html>
