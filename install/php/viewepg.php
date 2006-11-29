<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

reserveprogram.php

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
  <p align="left"><font color="#494949" size="6">番組表</font></p>
  <hr size="4">
<p align="left">番組表を表示します。
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


	$query = "SELECT stationid, stationname, stationrecch, ontvcode FROM foltia_station WHERE \"ontvcode\" ~~ '%ontvjapan%'  ORDER BY stationid ASC , stationrecch OFFSET $offset LIMIT $maxdisplay 
	";
	$rs = m_query($con, $query, "DBクエリに失敗しました");

//print "<!--$query  -->";
$viewstations = pg_num_rows($rs);
print "</p>
 <table width=\"100%\"  border=\"0\">
  <tbody>
   <tr class=\"kyoku\">
";
for ($i=0 ; $i < $viewstations ; $i++){
	$rowdata = pg_fetch_row($rs, $i);
	if ($rowdata[1] != ""){
	print "    <th scope=\"col\">$rowdata[1]</th>\n";
	}
}

print "  </tr>

 <tr  valign = top>
";
for ($i=0 ; $i < $viewstations ; $i++){
	$rowdata = pg_fetch_row($rs, $i);
	if ($rowdata[3] != ""){
	print "<td>";
		renderepgstation($con,$rowdata[3],$start);
	print "</td>\n";
	}
}

print " </tr>
  	</tbody>
</table>
";

 ?>

</body>
</html>
