<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

folcast.php

目的
foltia video podcast(folcast)用RSSを出力します。

オプション
tid:タイトルID
　省略時は新規録画全部
max:表示上限
　省略時は45件

 DCC-JPL Japan/foltia project

*/

header('Content-Type: application/rss+xml');
header('Content-Disposition: attachment; filename="folcast.xml"');

include("./foltialib.php");
$con = m_connect();
/*
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
*/
$now = date("YmdHi");   
$nowrfc822 =  date("r");

$max = getgetnumform(max);

if ($max > 0 ){
	//件数指定があればなにもしない
}else{
	$max = 45;
}
$tid = getgetnumform(tid);
if (($tid >= 0 ) && ($tid != "")){

$query = "
SELECT  foltia_program.tid,foltia_program.title,
foltia_subtitle.countno , foltia_subtitle.subtitle , foltia_subtitle.startdatetime, foltia_subtitle.pspfilename,foltia_subtitle.lengthmin,foltia_subtitle.enddatetime   FROM foltia_subtitle , foltia_program   WHERE \"pspfilename\" ~~ 'M%%'  AND foltia_program.tid = foltia_subtitle.tid AND foltia_program.tid = $tid   
ORDER BY \"enddatetime\" DESC 
offset 0 limit  $max 
	";

$titlequery = "
SELECT  foltia_program.tid,foltia_program.title 
FROM  foltia_program   
WHERE foltia_program.tid = $tid   
";
	$titlers = m_query($con, $query, "DBクエリに失敗しました");
	$rowdata = pg_fetch_row($titlers, 0); 
	$rsstitle = $rowdata[1];
}else{

$query = "
SELECT  foltia_program.tid,foltia_program.title,
foltia_subtitle.countno , foltia_subtitle.subtitle , foltia_subtitle.startdatetime, foltia_subtitle.pspfilename,foltia_subtitle.lengthmin,foltia_subtitle.enddatetime   FROM foltia_subtitle , foltia_program   WHERE \"pspfilename\" ~~ 'M%%'  AND foltia_program.tid = foltia_subtitle.tid ORDER BY \"enddatetime\" DESC 
offset 0 limit  $max 
	";
	$rsstitle = "新規録画";
}//if

$header = "<?xml version=\"1.0\" encoding=\"UTF-8\"?> 
<rss xmlns:itunes=\"http://www.itunes.com/DTDs/Podcast-1.0.dtd\" version=\"2.0\"> 
    <channel> 
        <title>$rsstitle:Folcast</title> 
        <itunes:author>DCC-JPL Japan/foltia project</itunes:author> 
        <link>http://www.dcc-jpl.com/soft/foltia/</link> 
        <description>フォルティアが未読処理をお助けしちゃいます</description> 
        <itunes:subtitle>foltia video podcast :$rsstitle:Folcast</itunes:subtitle> 
        <itunes:summary>フォルティアが未読処理をお助けしちゃいます</itunes:summary> 
		<language>ja</language>
        <copyright>foltia</copyright> 
        <itunes:owner> 
            <itunes:name>$rsstitle:Folcast</itunes:name> 
            <itunes:email>foltia@dcc-jpl.com</itunes:email> 
        </itunes:owner>          
        <category>Technology</category> 
        <itunes:category text=\"Technology\"></itunes:category> 

";
$header = mb_convert_encoding($header,"UTF-8", "EUC-JP");
print $header;

	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$maxrows = pg_num_rows($rs);

if ($maxrows == 0) {
				//die_exit("No items");	
}else{

for ($row = 0; $row < $maxrows; $row++) { 
		$rowdata = pg_fetch_row($rs, $row);
		
//$title = mb_convert_encoding($rowdata[1],"UTF-8", "EUC-JP");
$tid =  $rowdata[0];
$title = $rowdata[1];
$title = htmlspecialchars($title);
$countno = $rowdata[2];
if ($countno > 0 ){
	$countprint = "第".$countno."回";
}else{
	$countprint="";
}
$subtitle = $rowdata[3];
$subtitle = htmlspecialchars($subtitle);
$onairdate = $rowdata[4];
$day = substr($onairdate,0,4)."/".substr($onairdate,4,2)."/".substr($onairdate,6,2);
$time = substr($onairdate,8,2).":".substr($onairdate,10,2);
$onairdate = "$day $time";

$starttimerfc822 = foldate2rfc822($rowdata[4]);

$mp4filename = $rowdata[5];
$mp4uri = "http://". getserverfqdn()  .$httpmediamappath ."/$tid.localized/mp4/$mp4filename";
$mp4thmname = $rowdata[5];
$mp4thmname = ereg_replace(".MP4", ".THM", $mp4thmname);
$mp4thmnameuri = "http://". getserverfqdn() . $httpmediamappath ."/$tid.localized/mp4/$mp4thmname";

if (file_exists("$recfolderpath/$tid.localized/mp4/$mp4filename")) {
	$mp4filestat = stat("$recfolderpath/$tid.localized/mp4/$mp4filename");
	$mp4filesize = $mp4filestat[7];
} else {
	$mp4filesize = 0;
}

if ($rowdata[0] == 0 ){//EPG録画
	$showntitle = "$title $subtitle";
}else{
	$showntitle = "$title $countprint";
}


$item ="    <item> 
          <title>$showntitle</title> 
          <itunes:author>foltia</itunes:author> 
          <description>$title $countprint $subtitle</description> 
          <itunes:subtitle>$title $countprint $subtitle</itunes:subtitle> 
          <itunes:summary>$title $countprint $subtitle</itunes:summary> 
          <enclosure url=\"$mp4uri\" length=\"$mp4filesize\" type=\"video/mov\" /> 
          <guid isPermaLink=\"true\">$mp4thmnameuri</guid>
          <pubDate>$starttimerfc822</pubDate> 
          <itunes:explicit>no</itunes:explicit>
		  <itunes:keywords>foltia,Folcast,DCC-JPL Japan,$title,$subtitle</itunes:keywords>  
		  <itunes:image href=\"$mp4thmnameuri\" />
        </item> 
";

$item = mb_convert_encoding($item,"UTF-8", "EUC-JP");
print $item ;

}//for

}//if
		?>
	
    </channel> 
</rss> 
