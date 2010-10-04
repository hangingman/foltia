<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

viewepg.php

目的
番組録画予約ページを表示します。

オプション
start:表示タイムスタンプ(Ex.200512281558)
　省略時、現在時刻。

 DCC-JPL Japan/foltia project

*/

include("./foltialib.php");
$con = m_connect();
$epgviewstyle = 1;// 0だと終了時刻も表示
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
<title>foltia:EPG番組表</title>
</head>
<?php
$start = getgetnumform("start");

if ($start == ""){
	$start =  date("YmdHi");
}else{
  $start = ereg_replace( "[^0-9]", "", $start); 
}


?>
<body BGCOLOR="#ffffff" TEXT="#494949" LINK="#0047ff" VLINK="#000000" ALINK="#c6edff" >
<div align="center">
<?php 
printhtmlpageheader();
?>
<p align="left"><font color="#494949" size="6">EPG番組表</font></p>
<p align="left"><a href="./m.php">番組手動予約</a ></p>
<hr size="4">
<p align="left">EPG番組表を表示します。
<?php 

///////////////////////////////////////////////////////////////////////////
//現在の日付情報取得
$begin =  date("YmdHi");
$beginyear =   substr($begin,0,4);
$beginmonth =   substr($begin,4,2);
$beginday =   substr($begin,6,2);
$beginhour =   substr($begin,8,2);
$beginmin =   substr($begin,10,2);
///////////////////////////////////////////////////////////////////////////

$startyear =   substr($start,0,4);
$startmonth =   substr($start,4,2);
$startday =   substr($start,6,2);
$starthour =   substr($start,8,2);
$startmin =   substr($start,10,2);
$day_of_the_week = date ("(D)",mktime($starthour , 0 , 0, $startmonth , $startday  , $startyear));

print "($startyear/$startmonth/$startday $day_of_the_week $starthour:$startmin-)<BR>\n";


$yesterday = date ("YmdHi",mktime($starthour , 0 , 0, $startmonth , $startday -1 , $startyear));
$dayyesterday = date ("m/d(D)",mktime($starthour , 0 , 0, $startmonth , $startday -1 , $startyear));

/////////////////////////////////////////////////////////// 
//時刻の隣の【翌日】の変数
$tomorrow  = date ("YmdHi",mktime($starthour , 0 , 0, $startmonth , $startday +1 , $startyear));   
/////////////////////////////////////////////////////////// 
//EPG番組表を取得しますのとなりの日付の【曜日】の変数
$daytomorrow  = date ("m/d(D)",mktime($starthour , 0 , 0, $startmonth , $startday +1 , $startyear));
///////////////////////////////////////////////////////////


$today0400 = date ("YmdHi",mktime(4 , 0 , 0, $startmonth , $startday  , $startyear));
$today0800 = date ("YmdHi",mktime(8 , 0 , 0, $startmonth , $startday  , $startyear));
$today1200 = date ("YmdHi",mktime(12 , 0 , 0, $startmonth , $startday , $startyear));
$today1600 = date ("YmdHi",mktime(16 , 0 , 0, $startmonth , $startday , $startyear));
$today2000 = date ("YmdHi",mktime(20 , 0 , 0, $startmonth , $startday , $startyear));
$today2359 = date ("YmdHi",mktime(23 , 59 , 0, $startmonth , $startday , $startyear));


///////////////////////////////////////////////////////////////////
//１週間分のページのリンクの変数
$day0after = date ("YmdHi",mktime($beginhour , 0 , 0, $beginmonth , $beginday  , $beginyear));
$day0 = date ("m/d(D)",mktime($beginhour , 0 , 0, $beginmonth , $beginday  , $beginyear));
$day1after = date ("YmdHi",mktime($beginhour , 0 , 0, $beginmonth , $beginday +1 , $beginyear));
$day1 = date ("m/d(D)",mktime($beginhour , 0 , 0, $beginmonth , $beginday +1 , $beginyear));
$day2after = date ("YmdHi",mktime($beginhour , 0 , 0, $beginmonth , $beginday +2 , $beginyear));
$day2 = date ("m/d(D)",mktime($beginhour , 0 , 0, $beginmonth , $beginday +2 , $beginyear));
$day3after = date ("YmdHi",mktime($beginhour , 0 , 0, $beginmonth , $beginday +3 , $beginyear));
$day3 = date ("m/d(D)",mktime($beginhour , 0 , 0, $beginmonth , $beginday +3 , $beginyear));
$day4after = date ("YmdHi",mktime($beginhour , 0 , 0, $beginmonth , $beginday +4 , $beginyear));
$day4 = date ("m/d(D)",mktime($beginhour , 0 , 0, $beginmonth , $beginday +4 , $beginyear));
$day5after = date ("YmdHi",mktime($beginhour , 0 , 0, $beginmonth , $beginday +5 , $beginyear));
$day5 = date ("m/d(D)",mktime($beginhour , 0 , 0, $beginmonth , $beginday +5 , $beginyear));
$day6after = date ("YmdHi",mktime($beginhour , 0 , 0, $beginmonth , $beginday +6 , $beginyear));
$day6 = date ("m/d(D)",mktime($beginhour , 0 , 0, $beginmonth , $beginday +6 , $beginyear));
$day7after = date ("YmdHi",mktime($beginhour , 0 , 0, $beginmonth , $beginday +7 , $beginyear));
$day7 = date ("m/d(D)",mktime($beginhour , 0 , 0, $beginmonth , $beginday +7 , $beginyear));
///////////////////////////////////////////////////////////////////


//表示局選定
// $page = 1 ~ 
$maxdisplay = 8;

$query = "SELECT count(*) FROM foltia_station WHERE \"ontvcode\" LIKE '%ontvjapan%'";
//$rs = m_query($con, $query, "DBクエリに失敗しました");
$rs = sql_query($con, $query, "DBクエリに失敗しました");
$maxrows = $rs->fetchColumn(0);
if ($maxrows > $maxdisplay){
	$pages = ceil($maxrows / $maxdisplay) ;
}

$page = getgetnumform("p");

if (($page == "")|| ($page <= 0) ){
	$page = 1 ;
	$offset = 0  ;
}else{
  $page = ereg_replace( "[^0-9]", "", $page); 
  if ($page > $pages){
  	$page = $pages ;
  }elseif ($page <= 0) {
  $page = 1 ;
  }
  $offset = ($page * $maxdisplay ) - $maxdisplay;
}


/////////////////////////////////////////////////////////////////
//表示部分
$navigationbar =  "

[<A HREF=\"./viewepg.php\">現在</A>] | 
<A HREF=\"./viewepg.php?p=$page&start=$yesterday\">$dayyesterday [前日]</A> | 
当日(
<A HREF=\"./viewepg.php?p=$page&start=$today0400\">4:00</A>　
<A HREF=\"./viewepg.php?p=$page&start=$today0800\">8:00</A>　
<A HREF=\"./viewepg.php?p=$page&start=$today1200\">12:00</A>　
<A HREF=\"./viewepg.php?p=$page&start=$today1600\">16:00</A>　
<A HREF=\"./viewepg.php?p=$page&start=$today2000\">20:00</A>　
<A HREF=\"./viewepg.php?p=$page&start=$today2359\">24:00</A>) | 
<A HREF=\"./viewepg.php?p=$page&start=$tomorrow\">$daytomorrow [翌日]</A>
<br>
 | 
<A HREF=\"./viewepg.php?p=$page&start=$day0after\">$day0</A> | 
<A HREF=\"./viewepg.php?p=$page&start=$day1after\">$day1</A> | 
<A HREF=\"./viewepg.php?p=$page&start=$day2after\">$day2</A> | 
<A HREF=\"./viewepg.php?p=$page&start=$day3after\">$day3</A> | 
<A HREF=\"./viewepg.php?p=$page&start=$day4after\">$day4</A> | 
<A HREF=\"./viewepg.php?p=$page&start=$day5after\">$day5</A> | 
<A HREF=\"./viewepg.php?p=$page&start=$day6after\">$day6</A> | 
<A HREF=\"./viewepg.php?p=$page&start=$day7after\">$day7</A> | <BR>\n";
print "$navigationbar";
///////////////////////////////////////////////////////////////////

if ($maxrows > $maxdisplay){
//複数ページ
//$pages = ceil($maxrows / $maxdisplay) ;
if ($page > 1){
	$beforepage = $page - 1;
	print "<a href = \"./viewepg.php?p=$beforepage&start=$start\">←</A>";
}

print " $page / $pages (放送局) ";
for ($i=1;$i<=$pages;$i++){
	print "<a href = \"./viewepg.php?p=$i&start=$start\">$i</a>・";
}


if ($page < $pages){
	$nextpage = $page + 1;
	print "<a href = \"./viewepg.php?p=$nextpage&start=$start\">→</a>";
}
}
//ココから新コード
//・局リスト
$query = "SELECT stationid, stationname, stationrecch, ontvcode 
FROM foltia_station 
WHERE \"ontvcode\" LIKE '%ontvjapan%'  
ORDER BY stationid ASC , stationrecch 
LIMIT ? OFFSET ?
";

//$slistrs = m_query($con, $query, "DBクエリに失敗しました");
$slistrs = sql_query($con, $query, "DBクエリに失敗しました",array($maxdisplay,$offset));
while ($rowdata = $slistrs->fetch()) {
	$stationhash[] = $rowdata[3];
	$snames[] = $rowdata[1]; // headder
}

//・時間と全順番のハッシュ作る
$epgstart = $start ;
$epgend = calcendtime($start , (8*60));

$query = "SELECT DISTINCT startdatetime   
FROM foltia_epg
WHERE foltia_epg.ontvchannel in (
	SELECT ontvcode 
	FROM foltia_station 
	WHERE \"ontvcode\" LIKE '%ontvjapan%' 
	ORDER BY stationid ASC , stationrecch 
	LIMIT ? OFFSET ?
	)
AND startdatetime  >= ? 
AND startdatetime  < ? 
ORDER BY foltia_epg.startdatetime  ASC	";

//$rs = m_query($con, $query, "DBクエリに失敗しました");
$rs = sql_query($con, $query, "DBクエリに失敗しました",array($maxdisplay,$offset,$start,$epgend));

//print "$query<br>\n";

$rowdata = $rs->fetch();
if (! $rowdata) {
//番組データがない
$colmnums = 2;
}else{
	$colmnums = 0;
	do {
		$colmnums++;
		$timetablehash[$rowdata[0]] = $colmnums;
//		print "$rowdata[0]:$i+1 <br>\n";
	} while ($rowdata = $rs->fetch());
}
//print "colmnums $colmnums <br>\n";

//・局ごとに縦に配列入れていく
foreach ($stationhash as $stationname) {
$epgstart = $start ;
$epgend = calcendtime($start , (8*60));
$query = "
SELECT startdatetime , enddatetime , lengthmin , epgtitle , epgdesc , epgcategory  ,ontvchannel  ,epgid ,	epgcategory 
FROM foltia_epg 
WHERE foltia_epg.ontvchannel = ? AND 
enddatetime  > ?  AND 
startdatetime  < ?  
ORDER BY foltia_epg.startdatetime  ASC
	";

//	$statiodh = m_query($con, $query, "DBクエリに失敗しました");
	$statiodh = sql_query($con, $query, "DBクエリに失敗しました",array($stationname,$epgstart,$epgend));
	$stationrowdata = $statiodh->fetch();
	if (! $stationrowdata) {
		//print("番組データがありません<BR>");
		$item[0]["$stationname"] =  ">番組データがありません";
}else{
		do {
$printstarttime = substr($stationrowdata[0],8,2) . ":" .  substr($stationrowdata[0],10,2);
$tdclass = "t".substr($stationrowdata[0],8,2) .  substr($stationrowdata[0],10,2);
$title = $stationrowdata[3];
$title = htmlspecialchars(z2h($title));
$desc = $stationrowdata[4];
$desc = htmlspecialchars(z2h($desc));

if ($epgviewstyle){
$desc=$desc ."<br><br><!-- ". htmlspecialchars(foldate2print($stationrowdata[1])) ."-->";
}else{
$desc=$desc ."<br><br>". htmlspecialchars(foldate2print($stationrowdata[1])) ;
}


$height =  htmlspecialchars($stationrowdata[2]) * 3;
$epgid =  htmlspecialchars($stationrowdata[7]);
$epgcategory = htmlspecialchars($stationrowdata[8]);

if (isset($timetablehash["$stationrowdata[0]"])){
	$number = $timetablehash["$stationrowdata[0]"];
//print "$stationname $stationrowdata[0] [$number] $printstarttime $title $desc<br>\n";
}else{
	$number = 0;
//print "$stationname $stationrowdata[0] 現在番組 $printstarttime $title $desc<br>\n";
}
if ($epgcategory == ""){
$item["$number"]["$stationname"] =  " onClick=\"location = './reserveepg.php?epgid=$epgid'\"><span id=\"epgstarttime\">$printstarttime</span> <A HREF=\"./reserveepg.php?epgid=$epgid\"><span id=\"epgtitle\">$title</span></A> <span id=\"epgdesc\">$desc</span>";
}else{
$item["$number"]["$stationname"] =  " id=\"$epgcategory\" onClick=\"location = './reserveepg.php?epgid=$epgid'\"><span id=\"epgstarttime\">$printstarttime</span> <A HREF=\"./reserveepg.php?epgid=$epgid\"><span id=\"epgtitle\">$title</span></A> <span id=\"epgdesc\">$desc</span></span>";
}//if

		} while ($stationrowdata = $statiodh->fetch());
}//if

//・局ごとに間隔決定
//$item[$i][NHK] はヌルかどうか判定
$dataplace = 0 ; //初期化
$rowspan = 0;

for ($i=1; $i <= $colmnums ; $i++){
	if ($i === ($colmnums )){//最終行
		$rowspan = $i - $dataplace ;
		//そして自分自身にタグを
			//if ((!isset($item[$i][$stationname])) && ($item[$i][$stationname] == "")){
			if (!isset($item[$i][$stationname])){
			$item[$i][$stationname]  = null ;
			}else{
			$item[$i][$stationname]  = "<td ". $item[$i][$stationname] . "</td>";
			$rowspan--;
			}
			//ROWSPAN
			if ($rowspan === 1 ){
			$item[$dataplace][$stationname]  = "<td ". $item[$dataplace][$stationname] . "</td>";
			}else{
			$item[$dataplace][$stationname]  = "<td  rowspan = $rowspan ". $item[$dataplace][$stationname] . "</td>";
//			$item[$dataplace][$stationname]  = "<td ". $item[$dataplace][$stationname] . "$rowspan </td>";
			}

//	}elseif ((!isset($item[$i][$stationname]))&&($item[$i][$stationname] == "")){
	}elseif (!isset($item[$i][$stationname])){
	//ヌルなら
		//$item[$i][$stationname]  =  $item[$i][$stationname] ;
		$item[$i][$stationname]  =  null ;
//		$item[$i][$stationname]  =  "<td><br></td>" ;
	}else{
	//なんか入ってるなら
		$rowspan = $i - $dataplace;
		$itemDataplaceStationname = null;
		if (isset($item[$dataplace][$stationname])){
		$itemDataplaceStationname = $item[$dataplace][$stationname];
		}
			if ($rowspan === 1 ){
			$item[$dataplace][$stationname]  = "<td ". $itemDataplaceStationname . "</td>";
			}else{
			$item[$dataplace][$stationname]  = "<td rowspan = $rowspan ". $itemDataplaceStationname . "</td>";
//			$item[$dataplace][$stationname]  = "<td ". $item[$dataplace][$stationname] . "$rowspan </td>";
			}
		$dataplace = $i;
		
	}
}//for
}// end of for://・局ごとに縦に配列入れていく

//・テーブルレンダリング
print "<table>\n<tr>";

//ヘッダ
foreach ($snames as $s) {
	print "<th>".htmlspecialchars($s)."</th>" ;
}
//本体
for ($l = 0 ;$l <  $colmnums; $l++){
	print "<tr>";
	foreach ($stationhash as $stationname) {
		print_r($item[$l]["$stationname"]);
	}
	print "</tr>\n";
}
print "</table>\n";

print "<p align=\"left\"> $navigationbar </p>";
?>
<hr>
凡例
<table>
<tr>
<td id="information">情報</td>
<td id="anime">アニメ・特撮</td>
<td id="news">ニュース・報道</td>
<td id="drama">ドラマ</td>
<td id="variety">バラエティ</td>
<td id="documentary">ドキュメンタリー・教養</td>
<td id="education">教育</td>
<td id="music">音楽</td>
<td id="cinema">映画</td>
<td id="hobby">趣味・実用</td>
<td id="kids">キッズ</td>
<td id="sports">スポーツ</td>
<td id="etc">その他</td>
<td id="stage">演劇</td>

</tr>
</table>
</body>
</html>


