<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

titlelist.php

目的
全番組一覧を表示します。
録画有無にかかわらず情報を保持しているもの全てを表示します

引数
なし

 DCC-JPL Japan/foltia project

*/

include("./foltialib.php");
include("./sqlite_accessor.php");
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

printtitle("<title>foltia</title>", false);

//////////////////////////////////////////////////////////
//１ページの表示レコード数
$lim = 1000;
//クエリ取得
$p = getgetnumform(p);
//ページ取得の計算
list($st,$p,$p2) = number_page($p,$lim);
///////////////////////////////////////////////////////////

$now = date("YmdHi");
   
// タイトルリスト取得
list($rowdata, $maxcols, $rs) = get_all_titlelist_or_die($con, $lim, $st);
//行数取得
$dtcnt = get_all_title_count_or_die($con);

?>

<body>
<div align="center">

<?php 
printhtmlpageheader();
?>
  <p align="left"><font color="#494949" size="6">番組一覧</font></p>
  <hr size="4">
<p align="left">全番組リストを表示します。</p>

<?php

// 		/* フィールド数 */
// $maxcols = $rs->columnCount();
	
//Autopager 
echo "<div id=contents class=autopagerize_page_element />";
?>

  <table BORDER="0" CELLPADDING="0" CELLSPACING="2" WIDTH="100%">
	<thead>
		<tr>
			<th align="left">TID</th>
			<th align="left">タイトル</th>
			<th align="left">MPEG4リンク</th>
		</tr>
	</thead>
	
	<tbody>
		<?php
			/* テーブルのデータを出力 */

  do {
				echo("<tr>\n");

				//TID
					echo("<td><a href=\"reserveprogram.php?tid=" .
					htmlspecialchars($rowdata[0])  . "\">" .
					htmlspecialchars($rowdata[0]) . "</a></td>\n");
				      //タイトル
				        echo("<td><a href=\"http://cal.syoboi.jp/progedit.php?TID=" .
					htmlspecialchars($rowdata[0])  . "\" target=\"_blank\">" .
					htmlspecialchars($rowdata[1]) . "</a></td>\n");
					print "<td><A HREF = \"showlibc.php?tid=".htmlspecialchars($rowdata[0])."\">mp4</A></td>\n";

				echo("</tr>\n");
    } while ($rowdata = $rs->fetch());

		?>

	</tbody>
</table>

<?php

/////////////////////////////////////////////////////////
//Autopageing処理とページのリンクを表示
page_display("",$p,$p2,$lim,$dtcnt,"");
////////////////////////////////////////////////////////

?>
</body>
</html>
