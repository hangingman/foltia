<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

showlibc.php

目的
録画ライブラリ番組を個別表示します。

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

$tid = getgetnumform(tid);

if ($tid == "") {
	header("Status: 404 Not Found",TRUE,404);
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<?php
if (file_exists  ( "./iui/iui.css"  )){
	$useragent = $_SERVER['HTTP_USER_AGENT'];
}
if(ereg("iPhone",$useragent)){
print "<meta name=\"viewport\" content=\"width=320; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;\"/>
<link rel=\"apple-touch-icon\" type=\"image/png\" href=\"./img/icon.png\" />
<style type=\"text/css\" media=\"screen\">@import \"./iui/iui.css\";</style>
<script type=\"application/x-javascript\" src=\"./iui/iui.js\"></script>";
}else{
print "<meta http-equiv=\"Content-Style-Type\" content=\"text/css\">
<link rel=\"stylesheet\" type=\"text/css\" href=\"graytable.css\">
<script src=\"http://images.apple.com/main/js/ac_quicktime.js\" language=\"JavaScript\" type=\"text/javascript\"></script> 
<link rel=\"alternate\" type=\"application/rss+xml\" title=\"RSS\" href=\"./folcast.php?tid=$tid\" />
";
}
if ($tid == "") {
	print "<title>foltia:Lib</title>
</head><body BGCOLOR=\"#ffffff\" TEXT=\"#494949\" LINK=\"#0047ff\" VLINK=\"#000000\" ALINK=\"#c6edff\" > \n";
		printhtmlpageheader();
		die_exit("再生可能番組がありません<BR>");
	}
$now = date("YmdHi");   

$query = "
SELECT foltia_program.title  
FROM  foltia_program   
WHERE foltia_program.tid = $tid  
";
$rs = m_query($con, $query, "DBクエリに失敗しました");
$maxrows = pg_num_rows($rs);
if ($maxrows == 0 ){
 $syobocaldb = `curl "http://cal.syoboi.jp/db?Command=TitleLookup&TID=$tid" | head -2 `;
$syobocaldb = mb_convert_encoding($syobocaldb, "EUC-JP", "UTF-8");
	$syobocaldb = preg_match("/<Title>.*<\/Title>/", $syobocaldb,$title);
	$title = $title[0];
	$title = strip_tags($title);
	$title =  htmlspecialchars($title) ;
}else{
$rowdata = pg_fetch_row($rs, 0);
$title = $rowdata[0];
$title =  htmlspecialchars($title) ;
}
//ヘッダ続き
print "<title>foltia:Lib $tid:$title</title></head>";
$serveruri = getserveruri();

if(ereg("iPhone",$useragent)){
	print "<body onclick=\"console.log('Hello', event.target);\">
    <div class=\"toolbar\">
        <h1 id=\"pageTitle\"></h1>
        <a id=\"backButton\" class=\"button\" href=\"#\"></a>
    </div>
";
}else{

print "<body BGCOLOR=\"#ffffff\" TEXT=\"#494949\" LINK=\"#0047ff\" VLINK=\"#000000\" ALINK=\"#c6edff\" >
<div align=\"center\">
";
	printhtmlpageheader();
print "  <p align=\"left\"><font color=\"#494949\" size=\"6\">録画ライブラリ番組個別表示</font></p>
  <hr size=\"4\">
<p align=\"left\">再生可能ムービーを表示します。<br>";
if ($tid == 0){
print "$title 【<A HREF = \"./folcast.php?tid=$tid\">この番組のFolcast</A> [<a href=\"itpc://$serveruri/folcast.php?tid=$tid\">iTunesに登録</a>】 <br>\n";
}else{

print "<a href=\"http://cal.syoboi.jp/tid/" .
				     htmlspecialchars($tid)  . "\" target=\"_blank\">$title</a> 【<A HREF = \"./folcast.php?tid=$tid\">この番組のFolcast</A> [<a href=\"itpc://$serveruri/folcast.php?tid=$tid\">iTunesに登録</a>]】 <br>\n";
}
}// endif if(ereg("iPhone",$useragent))

//確認
if (file_exists ("$recfolderpath/$tid.localized")){
//	print "ディレクトリは存在します\n";
}else{
//	print "ディレクトリはありません\n";
		print "再生可能番組がありません<BR>\n</body></html>";
	exit;
}					 

//新仕様/* 2006/10/26 */
if (file_exists("./selectcaptureimage.php") ) {
	$sbpluginexist = 1;
}
$serverfqdn = getserverfqdn();

$query = "
SELECT 
foltia_program.tid,
foltia_program.title,
foltia_subtitle.countno,
foltia_subtitle.subtitle,
foltia_subtitle.startdatetime ,
foltia_subtitle.m2pfilename ,
foltia_subtitle.pid ,
foltia_mp4files.mp4filename 
FROM foltia_mp4files  
LEFT JOIN foltia_subtitle 
ON   foltia_mp4files.mp4filename = foltia_subtitle.pspfilename   
LEFT JOIN foltia_program  
ON foltia_mp4files.tid = foltia_program.tid 
WHERE foltia_mp4files.tid = $tid  
ORDER BY \"startdatetime\" ASC
";

$rs = "";
$rs = m_query($con, $query, "DBクエリに失敗しました");
$maxrows = pg_num_rows($rs);
if ($maxrows > 0 ){
if(ereg("iPhone",$useragent)){
	print "<ul id=\"home\" title=\"$title\" selected=\"true\">";
}else{
print "
  <table BORDER=\"0\" CELLPADDING=\"0\" CELLSPACING=\"2\" WIDTH=\"100%\">
	<tbody>
";
}
for ($row = 0; $row < $maxrows; $row++) {
	$rowdata = pg_fetch_row($rs, $row);

$title = $rowdata[1];

if ($rowdata[2]== "" ){
	$count = "[話数]";
}else{
	$count = $rowdata[2];
}
if ($rowdata[3]== "" ){
	$subtitle = "[サブタイトル]";
}else{
	$subtitle = $rowdata[3];
}
$onairdate =  $rowdata[4];

$tid = htmlspecialchars($rowdata[0]);
$title = htmlspecialchars($title);
$count = htmlspecialchars($count);
$subtitle = htmlspecialchars($subtitle);
$onairdate = htmlspecialchars($onairdate);
$pid = htmlspecialchars($rowdata[6]);
$fName = htmlspecialchars($rowdata[7]);
if (ereg(".MP4", $fName)){
	$thumbnail = $fName;
	$thumbnail = ereg_replace(".MP4", ".THM", $thumbnail);
}
if ($onairdate == ""){
$onairdate = "[放送日]";
}else{
$day = substr($onairdate,0,4)."/".substr($onairdate,4,2)."/".substr($onairdate,6,2);
$time = substr($onairdate,8,2).":".substr($onairdate,10,2);
$onairdate = "$day $time";
}
//Starlight Breaker向け拡張
//$debug_pg_num_rows = pg_num_rows ($rs );
$caplink = "";

if (($sbpluginexist == 1) && (pg_num_rows ($rs ) > 0)){
 //$capimgpath = htmlspecialchars(preg_replace("/.m2p/", "", $rowdata[5]));
 $capimgpath = htmlspecialchars(preg_replace("/.m2./", "", $rowdata[5]));
 	
	if (($capimgpath != "") && (file_exists("$recfolderpath/$tid.localized/img/$capimgpath") )){
	$caplink = " / <a href = \"./selectcaptureimage.php?pid=$rowdata[6]\">キャプ</a>";
	}else{
	$caplink = " / キャプなし";
	}
}else{
$caplink = "";
}//end if sb

if (file_exists("$recfolderpath/$tid.localized/mp4/$thumbnail") ){
	$imgsrcuri = "$httpmediamappath/$tid.localized/mp4/$thumbnail\" alt=\"$title $count $subtitle"; 
}else{
	$imgsrcuri = "./img/no-thumbnail-img.png\" alt=\"NO IMAGE";
}

if(ereg("iPhone",$useragent)){

print "<li><a href=\"http://$serverfqdn/$httpmediamappath/$tid.localized/mp4/$fName\" target=\"_self\">$count $subtitle $onairdate</a></li>\n";

}else{
print "  <tr>
    <td rowspan=\"4\" width=\"170\"><a href = \"$httpmediamappath/$tid.localized/mp4/$fName\" target=\"_blank\"><img src = \"$imgsrcuri\" width=\"160\" height=\"120\"></a></td>
    <td>$count</td>
  </tr>
  <tr>
";
	if ($tid == 0){
	print "\n    <td>$subtitle</td>";
	}else{
	print "\n    <td><a href = \"http://cal.syoboi.jp/tid/$tid/time#$pid\" target=\"_blank\">$subtitle</a></td>";
	}//if
print "  </tr>
  <tr>
    <td>$onairdate</td>
  </tr>
  <tr>
    <td><a href =\"$httpmediamappath/$tid.localized/mp4/$fName\" target=\"_blank\">$fName</A> / <script language=\"JavaScript\" type=\"text/javascript\">QT_WriteOBJECT_XHTML('http://g.hatena.ne.jp/images/podcasting.gif','16','16','','controller','FALSE','href','http://$serverfqdn/$httpmediamappath/$tid.localized/mp4/$fName','target','QuickTimePlayer','type','video/mp4');</script> $caplink</td>
  </tr>
";

}//endif iPhone

}//for
}else{
print "録画ファイルがありません<br>\n";
}//if

if(ereg("iPhone",$useragent)){
	print "<li><a href=\"http://$serveruri/showlib.php\" target=\"_self\">一覧へ戻る</a></li>\n";
	print "</ul>\n";
}else{
	print "</tbody></table>\n";
}
?>
</body>
</html>

	