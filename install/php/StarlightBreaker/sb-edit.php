<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/


目的
blogツール、スターライトブレイカー、編集画面

引数
pid:PID
f:file name

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


$pid = getgetform(pid);
$filename = getgetform(f);

if (($pid == "") ||($filename == "")) {
	header("Status: 404 Not Found",TRUE,404);
}
?>

<?php
printtitle("<title>Starlight Breaker - 編集</title>", false);
?>

<body>
<div align="center">

<?php
printhtmlpageheader();

if (($pid == "") ||($filename == "")) {
	print "画像がありません。<br></body></html>";
	exit;
}


$query = "
SELECT 
foltia_program.tid,
stationname,
foltia_program.title,
foltia_subtitle.countno,
foltia_subtitle.subtitle,
foltia_subtitle.startdatetime ,
foltia_subtitle.lengthmin  , 
foltia_subtitle.pid ,
foltia_subtitle.m2pfilename , 
foltia_subtitle.pspfilename 
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid 
AND foltia_subtitle.pid = ? 
";
	$rs = sql_query($con, $query, "DBクエリに失敗しました",array($pid));
$rows = pg_num_rows($rs);
if ($rows == 0){
	print "  <p align=\"left\"><font color=\"#494949\" size=\"6\">書き込み編集</font></p>
  <hr size=\"4\">
<p align=\"left\">
録画記録がありません。<br>
";

}else{
$rowdata = pg_fetch_row($rs, 0);

print "  <p align=\"left\"><font color=\"#494949\" size=\"6\">書き込み編集 </font></p>
  <hr size=\"4\">
<p align=\"left\">";
print "<a href = \"http://cal.syoboi.jp/tid/$rowdata[0]/\" target=\"_blank\">";
$title = htmlspecialchars($rowdata[2]);
$countno = htmlspecialchars($rowdata[3]);
print "$title</a> $countno " ;

$tid = $rowdata[0];
$subtitle = htmlspecialchars($rowdata[4]) ;
if ($tid > 0){
print "<a href = \"http://cal.syoboi.jp/tid/$tid/time#$pid\" target=\"_blank\">$subtitle</a> ";
}else{
print "$subtitle ";
}
print htmlspecialchars($rowdata[1]) . " ";
print htmlspecialchars($rowdata[6]) . "分 ";
print htmlspecialchars(foldate2print($rowdata[5]));
print "<br /><br />";
$mp4filename = $rowdata[9];
$serverfqdn = getserverfqdn();


$m2pfilename = $rowdata[8];

list($tid,$countno,$date,$time)= split ("-", $m2pfilename );
	$tid = ereg_replace("[^0-9]", "", $tid);

$path = ereg_replace("\.m2p$", "", $m2pfilename);
$serveruri = getserverfqdn ();

print "</div>\n";

//画像

print "<img src='http://$serveruri$httpmediamappath/$tid.localized/img/$path/$filename' width='160' height='120' alt='$tid:$countno:$filetid' align=\"left\">\n";


if (getform(preview) == 1){
//プレビュー表示
// htmlspecialchars(stripslashes( )) 
$subject = getform(subject); 
$maintext = $_POST["textarea"];
$maintext = pg_escape_string($maintext);
//$maintext = mbereg_replace("\n","<br />\n", $maintext);
$rate = getform(rank4);

switch ($rate) {
	case -2:
		$ratechara =  "× ";
	break;
	case -1:
	$ratechara =  "▲ ";
	break;
	case 0:
	$ratechara =  "− ";
	break;
	case 1:
	$ratechara =  "★ ";
	break;
	case 2:
	$ratechara =  "★★ ";
	break;
	case 3:
	$ratechara =  "★★★ ";
	break;
	case 4:
	$ratechara =  "★★★★ ";
	break;
	default:
	$ratechara =  "− ";
}
$subject = $ratechara . $subject;

print "". htmlspecialchars(stripslashes( $subject)) ."\n";
print "". stripslashes( $maintext) ."<br />\n";
print "<br />\n";
print "本文(source view):<br />". htmlspecialchars(stripslashes( $maintext)) ."<hr><br /><br /><br />\n";

print "<form id=\"form2\" name=\"form2\" method=\"post\" action=\"./sb-write.php?tid=$tid&path=$path&f=$filename\"><input type=\"password\" name=\"blogpw\">[ <a href = \"./sb-write.php?tid=$tid&path=$path&f=$filename\" target=\"_blank\">Send Picture Only</a> ] [ <input type=\"hidden\" name=\"subjects\" value=\"" . urlencode(stripslashes($subject)) . "\" /><input type=\"hidden\" name=\"maintext\" value=\"" . urlencode(stripslashes($maintext)) . "\" /><input type=submit value=\" Blog Write \"> ]</form>";


}else{//編集書き込みモード
//タイトル
if ($tid == 0){
	$subjects = "「".$subtitle."」";
}else{
	if ($countno == ""){
	$subjects = "$title 「".$subtitle."」";
	}else{
	$subjects = "$title ＃". $countno ." 「".$subtitle."」";
	}
}
print "<form id=\"form1\" name=\"form1\" method=\"post\" action=\"./sb-edit.php?pid=$pid&f=$filename\">
<input type=\"text\" name=\"subject\" size=\"70\"value=\"$subjects \"><br />
			<select class='hosi' name='rank4' size='1'>
				<option value='-2'>×見切り
				<option value='-1'>▲見切り候補
				<option value='0'>−見てない
				<option value='1' selected=\"selected\">★ふつう
				<option value='2'>★★おもしろい
				<option value='3'>★★★名作
				<option value='4'>★★★★殿堂
			</select> 
<br />
<br />
<input type=\"hidden\" name=\"preview\" value=\"1\" />

            <textarea name=\"textarea\" rows=\"40\" cols=\"55\">
";
if ($tid > 0){
print "
<br />
参考リンク:<a href = \"http://cal.syoboi.jp/tid/$tid/\" target=\"_blank\"> $title</a> "; 
	if ($countno != ""){ 
	print "第". $countno ."話 ";
	}
print"<a href = \"http://cal.syoboi.jp/tid/$tid/time#$pid\" target=\"_blank\">$subtitle</a> (情報:<a href = \"http://cal.syoboi.jp/\">しょぼいカレンダー</a>)";
}
print "			</textarea><br />
  <input type=submit value=\" ブレビュー \">
</form>

";
}//プレビュー表示かどうか
/*
ToDo
・Formプレビュー
・パブリッシュボタン
・
*/

// タイトル一覧　ここまで
}//if rowdata == 0

?>

</body>
</html>
