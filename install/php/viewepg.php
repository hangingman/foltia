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
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" type="text/css" href="graytable.css"> 
<title>foltia:EPG</title>
</head>
<?php
include("./foltialib.php");
  
$con = m_connect();
$start = getgetnumform(start);

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
  <hr size="4">
<p align="left">EPG番組表を表示します。
<?php 

$startyear =   substr($start,0,4);
$startmonth =   substr($start,4,2);
$startday =   substr($start,6,2);
$starthour =   substr($start,8,2);
$startmin =   substr($start,10,2);
print "($startyear/$startmonth/$startday $starthour:$startmin-)<BR>\n";

$yesterday = date ("YmdHi",mktime($starthour , 0 , 0, $startmonth , $startday -1 , $startyear));
$today0400 = date ("YmdHi",mktime(4 , 0 , 0, $startmonth , $startday  , $startyear));
$today1200 = date ("YmdHi",mktime(12 , 0 , 0, $startmonth , $startday , $startyear));
$today2000 = date ("YmdHi",mktime(20 , 0 , 0, $startmonth , $startday , $startyear));
$day1after = date ("YmdHi",mktime($starthour , 0 , 0, $startmonth , $startday +1 , $startyear));
$day1 = date ("m/d",mktime($starthour , 0 , 0, $startmonth , $startday +1 , $startyear));
$day2after = date ("YmdHi",mktime($starthour , 0 , 0, $startmonth , $startday +2 , $startyear));
$day2 = date ("m/d",mktime($starthour , 0 , 0, $startmonth , $startday +2 , $startyear));
$day3after = date ("YmdHi",mktime($starthour , 0 , 0, $startmonth , $startday +3 , $startyear));
$day3 = date ("m/d",mktime($starthour , 0 , 0, $startmonth , $startday +3 , $startyear));
$day4after = date ("YmdHi",mktime($starthour , 0 , 0, $startmonth , $startday +4 , $startyear));
$day4 = date ("m/d",mktime($starthour , 0 , 0, $startmonth , $startday +4 , $startyear));
$day5after = date ("YmdHi",mktime($starthour , 0 , 0, $startmonth , $startday +5 , $startyear));
$day5 = date ("m/d",mktime($starthour , 0 , 0, $startmonth , $startday +5 , $startyear));
$day6after = date ("YmdHi",mktime($starthour , 0 , 0, $startmonth , $startday +6 , $startyear));
$day6 = date ("m/d",mktime($starthour , 0 , 0, $startmonth , $startday +6 , $startyear));
$day7after = date ("YmdHi",mktime($starthour , 0 , 0, $startmonth , $startday +7 , $startyear));
$day7 = date ("m/d",mktime($starthour , 0 , 0, $startmonth , $startday +7 , $startyear));



//表示局選定
// $page = 1 ~ 
$maxdisplay = 8;

	$query = "SELECT stationid, stationname, stationrecch, ontvcode FROM foltia_station WHERE \"ontvcode\" ~~ '%ontvjapan%' 
	";
	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$maxrows = pg_num_rows($rs);

if ($maxrows > $maxdisplay){
	$pages = ceil($maxrows / $maxdisplay) ;
}

$page = getgetnumform(p);

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


print "←<A HREF=\"./viewepg.php?p=$page&start=$yesterday\">前の日</A>　<A HREF=\"./viewepg.php\">現在</A>　当日(<A HREF=\"./viewepg.php?p=$page&start=$today0400\">4:00</A>　<A HREF=\"./viewepg.php?p=$page&start=$today1200\">12:00</A>　<A HREF=\"./viewepg.php?p=$page&start=$today2000\">20:00</A>)　<A HREF=\"./viewepg.php?p=$page&start=$day1after\">次の日</A>　<A HREF=\"./viewepg.php?p=$page&start=$day2after\">$day2</A>　<A HREF=\"./viewepg.php?p=$page&start=$day3after\">$day3</A>　<A HREF=\"./viewepg.php?p=$page&start=$day4after\">$day4</A>　<A HREF=\"./viewepg.php?p=$page&start=$day5after\">$day5</A>　<A HREF=\"./viewepg.php?p=$page&start=$day6after\">$day6</A>　<A HREF=\"./viewepg.php?p=$page&start=$day7after\">$day7</A>→<BR>\n";


if ($maxrows > $maxdisplay){
//複数ページ
//$pages = ceil($maxrows / $maxdisplay) ;
if ($page > 1){
	$beforepage = $page - 1;
	print "<a href = \"./viewepg.php?p=$beforepage&start=$start\">←</A>";
}

print " $page / $pages (放送局) ";

if ($page < $pages){
	$nextpage = $page + 1;
	print "<a href = \"./viewepg.php?p=$nextpage&start=$start\">→</A>";
}
}
//ココから新コード
//・局リスト
$query = "SELECT stationid, stationname, stationrecch, ontvcode 
FROM foltia_station 
WHERE \"ontvcode\" ~~ '%ontvjapan%'  
ORDER BY stationid ASC , stationrecch 
OFFSET $offset LIMIT $maxdisplay 
";
$slistrs = m_query($con, $query, "DBクエリに失敗しました");
$stations =  pg_num_rows($slistrs);
for ($i=0 ; $i < $stations ; $i++){
	$rowdata = pg_fetch_row($slistrs, $i);
	$stationhash[$i] = $rowdata[3] ;
}

//・時間と全順番のハッシュ作る
$epgstart = $start ;
$epgend = calcendtime($start , (8*60));

$query = "SELECT DISTINCT startdatetime   
FROM foltia_epg
WHERE foltia_epg.ontvchannel in (
	SELECT ontvcode 
	FROM foltia_station 
	WHERE \"ontvcode\" ~~ '%ontvjapan%'  
	ORDER BY stationid ASC , stationrecch 
	OFFSET $offset LIMIT $maxdisplay
	)
AND startdatetime  >= $start  
AND startdatetime  < $epgend  
ORDER BY foltia_epg.startdatetime  ASC	";

$rs = m_query($con, $query, "DBクエリに失敗しました");
$colmnums =  pg_num_rows($rs);
if ($colmnums == 0){
//番組データがない
$colmnums = 2;
}else{
	for ($i=0 ; $i < $colmnums ; $i++){
		$rowdata = pg_fetch_row($rs, $i);
		$timetablehash["$rowdata[0]"] = $i;
	}
}
//・局ごとに縦に配列入れていく
for ($j=0 ; $j < $stations ; $j++){
	$rowdata = pg_fetch_row($slistrs, $j);
	$stationname = $rowdata[3];

$epgstart = $start ;
$epgend = calcendtime($start , (8*60));
$query = "
SELECT startdatetime , enddatetime , lengthmin , epgtitle , epgdesc , epgcategory  ,ontvchannel  ,epgid ,	epgcategory 
FROM foltia_epg 
WHERE foltia_epg.ontvchannel = '$stationname' AND 
enddatetime  > $epgstart  AND 
startdatetime  < $epgend  
ORDER BY foltia_epg.startdatetime  ASC
	";
	$statiodh = m_query($con, $query, "DBクエリに失敗しました");
	$maxrowsstation = pg_num_rows($statiodh);
if ($maxrowsstation == 0) {
		//print("番組データがありません<BR>");
		$item[0]["$stationname"] =  ">番組データがありません";
}else{

for ($srow = 0; $srow < $maxrowsstation ; $srow++) { 
	 
$stationrowdata = pg_fetch_row($statiodh, $srow);

$printstarttime = substr($stationrowdata[0],8,2) . ":" .  substr($stationrowdata[0],10,2);
$tdclass = "t".substr($stationrowdata[0],8,2) .  substr($stationrowdata[0],10,2);
$title = $stationrowdata[3];
$title = htmlspecialchars(z2h($title));
$desc = $stationrowdata[4];
$desc = htmlspecialchars(z2h($desc));
$height =  htmlspecialchars($stationrowdata[2]) * 3;
$epgid =  htmlspecialchars($stationrowdata[7]);
$epgcategory = htmlspecialchars($stationrowdata[8]);

if (isset($timetablehash["$stationrowdata[0]"])){
	$number = $timetablehash["$stationrowdata[0]"];
}else{
	$number = 0;
}
if ($epgcategory == ""){
$item["$number"]["$stationname"] =  " onClick=\"location = './reserveepg.php?epgid=$epgid'\"><span id=\"epgstarttime\">$printstarttime</span> <A HREF=\"./reserveepg.php?epgid=$epgid\"><span id=\"epgtitle\">$title</span></A> <span id=\"epgdesc\">$desc</span>";
}else{
$item["$number"]["$stationname"] =  " id=\"$epgcategory\" onClick=\"location = './reserveepg.php?epgid=$epgid'\"><span id=\"epgstarttime\">$printstarttime</span> <A HREF=\"./reserveepg.php?epgid=$epgid\"><span id=\"epgtitle\">$title</span></A> <span id=\"epgdesc\">$desc</span></span>";
}//if

}//for
}//if

//・局ごとに間隔決定
//$item[$i][NHK] はヌルかどうか判定
$dataplace = 0 ; //初期化
$rowspan = 0;

for ($i=1; $i <= $colmnums ; $i++){
	if ($i === ($colmnums - 1)){//最終行
		$rowspan = $i - $dataplace + 1;
		//そして自分自身にタグを
			if ($item[$i][$stationname] == ""){
			$item[$i][$stationname]  = "";
			}else{
			$item[$i][$stationname]  = "<td ". $item[$i][$stationname] . "</td>";
			$rowspan--;
			}
			//ROWSPAN
			if ($rowspan === 1 ){
			$item[$dataplace][$stationname]  = "<td ". $item[$dataplace][$stationname] . "</td>";
			}else{
			$item[$dataplace][$stationname]  = "<td  rowspan = $rowspan ". $item[$dataplace][$stationname] . "</td>";
			}

	}elseif ($item[$i][$stationname] == ""){
	//ヌルなら
		$item[$i][$stationname]  =  $item[$i][$stationname] ;
	}else{
	//なんか入ってるなら
		$rowspan = $i - $dataplace;
			if ($rowspan === 1 ){
			$item[$dataplace][$stationname]  = "<td ". $item[$dataplace][$stationname] . "</td>";
			}else{
			$item[$dataplace][$stationname]  = "<td rowspan = $rowspan ". $item[$dataplace][$stationname] . "</td>";
			}
		$dataplace = $i;
		
	}
}//for
}// end of for://・局ごとに縦に配列入れていく

//・テーブルレンダリング
print "<table>\n<tr>";

//ヘッダ
for ($i=0;$i<$stations;$i++){
	$rowdata = pg_fetch_row($slistrs, $i);
	print "<th>".htmlspecialchars($rowdata[1])."</th>" ;
}
//本体
for ($l = 0 ;$l <  $colmnums; $l++){
	print "<tr>";
	for ($m = 0 ; $m < $stations ; $m++ ){
		$stationname = $stationhash[$m];
		print_r($item[$l]["$stationname"]);
	}
	print "</tr>\n";
}
print "</table>\n";
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


