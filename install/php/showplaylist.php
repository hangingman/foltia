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
include("./sqlite_accessor.php");
$con = m_connect();

if ($useenvironmentpolicy == 1) {
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

    // Syabas 判定
    $useragent = $_SERVER['HTTP_USER_AGENT'];

printtitle("<title>foltia:recorded file list</title>", true);

/////////////////////////////////////////////////??????
//１ページの表示レコード数
$lim = 300;
//クエリ取得
$p = getgetnumform(p);
//ページ取得の計算
list($st,$p,$p2) = number_page($p,$lim);
//////////////////////////////////////////////////????

$now = date("YmdHi");   
?>

<body>
  <div id="wrapper">
    <div align="center">

    <?php 
     printhtmlpageheader();
    ?>
    </div>

    <!-- 表示するページ FIXME: テンプレートが有効に使える場面であるためあとで重複コードは排除する -->
    <div id="page-wrapper">
      <div id="container-fluid">


	<!-- ページタイトル -->
	<div class="row">
          <div class="col-lg-12">
            <h1 class="page-header">
	      &nbsp;録画一覧表示
	    </h1>

	    <p align="left">再生可能番組リストを表示します。<br>

            <ol class="breadcrumb">
              <li>
		<i class="fa fa-fw fa-table"></i>  <a href="./index.php"> 放映予定</a>
              </li>
              <li class="active">
		<i class="fa fa-fw fa-bell"></i>  <a href="./index.php?mode=new"> 新番組 </li>
              </ol>


	      <?php
     if ($demomode) {
     } else {
	 printdiskusage();
	 printtrcnprocesses();
     }

//////////////////////////////////////////
//クエリ取得
$list = getgetform('list');
//Autopager
echo "<div id=contents class=autopagerize_page_element />";
//////////////////////////////////////////


      ?>

    </div>
  </div>
  <!-- /.row -->

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

	    //旧仕様
	    if($list == "raw") {
		exec ("ls -t  $recfolderpath/*.???", $m2pfiles);


		foreach($m2pfiles as $pathfName) {

		    $fNametmp = split("/",$pathfName);
		    $fName = array_pop($fNametmp);

		    if(($fName == ".") or ($fName == "..") ) { continue; }
		    if ((ereg(".m2.+", $fName))|| (ereg(".aac", $fName))) {
			$filesplit = split("-",$fName);

			if (preg_match("/^\d+$/", $filesplit[0])) {

			    list($tid, $title, $subtitle, $count) = get_foltia_program_with_tid($con, $filesplit);

			    $tid = htmlspecialchars($tid);
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
				    print "			<td align=\"left\"> N/A </td>\n";
				}

				print "</tr>\n
";
			    }

			}//ereg 
		    }//foreach
		    print "	</tbody>\n</table>\n</FORM>\n</body>\n</html>\n";
		    exit;

		} elseif ($list== "title") {//新仕様
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
LIMIT $lim OFFSET $st

";
} else {
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
LIMIT $lim OFFSET $st
";
}

$rs = sql_query($con, $query, "DBクエリに失敗しました");
$rowdata = $rs->fetch();

/////////////////////////////////////////
//テーブルの総数取得
$query2 = "
SELECT COUNT(*) AS cnt FROM foltia_subtitle , foltia_program , foltia_m2pfiles
WHERE foltia_program.tid = foltia_subtitle.tid
 AND foltia_subtitle.m2pfilename = foltia_m2pfiles.m2pfilename
        ";
$rs2 = sql_query($con, $query2, "DB\?\ィ\e?E?oCO???T????");
$rowdata2 = $rs2->fetch();
if (! $rowdata2) {
    die_exit("番組データがありません<BR>");
}
//1O?o?eAA
$dtcnt =  $rowdata2[0];

/////////////////////////////////////////

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
	if (ereg("syabas",$useragent)) {
	    print "<td><A HREF=\"./view_syabas.php?pid=$pid\" vod=playlist>$fName</td>";
	}
	else{
	    print "<td><A HREF=\"$httpmediamappath/$fName\">$fName</A><br></td>";
	}
	if ($tid > 0) {
	    print"<td><a href=\"http://cal.syoboi.jp/tid/$tid\" target=\"_blank\">$title</a></td>
<td>$count<br></td>
<td><a href = \"http://cal.syoboi.jp/tid/$tid/time#$pid\" target=\"_blank\">$subtitle</a><br></td>";
	} else {
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

	//}


    } while ($rowdata = $rs->fetch());
} else {

    print "
<tr>
<td COLSPAN=\"5\">ファイルがありません</td>
</tr>
";


}//end if

print "</tbody>
</table>
</FORM>\n";

//////////////////////////////////////////////////////////////////////
//クエリ代入
$query_st = $list;
//Autopageing処理とページのリンクを表示
list($p2,$page) = page_display($query_st,$p,$p2,$lim,$dtcnt,"");
//////////////////////////////////////////////////////////////////////
print "</div>";	//Auto pager終わり


//番組ソートの時、未読番組のタイトルだけ表示
//if ($list== "title" && $p2 > $page) {
if ($list== "title") {

    $query = "
SELECT distinct
foltia_program.tid,
foltia_program.title 
FROM foltia_subtitle , foltia_program , foltia_m2pfiles 
WHERE foltia_program.tid = foltia_subtitle.tid  
 AND foltia_subtitle.m2pfilename = foltia_m2pfiles.m2pfilename 
ORDER BY foltia_program.tid DESC 
";

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


</div>
</div>
</div>
</body>
</html>
