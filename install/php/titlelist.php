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
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="ja">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=EUC-JP">
<meta http-equiv="Content-Style-Type" content="text/css">
<link rel="stylesheet" type="text/css" href="graytable.css"> 
<title>foltia</title>
</head>

<?php

  include("./foltialib.php");

$con = m_connect();

$now = date("YmdHi");   

	$query = "
SELECT 
foltia_program.tid,
foltia_program .title 
FROM  foltia_program 
ORDER BY foltia_program.tid  DESC
	";
	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$maxrows = pg_num_rows($rs);
			
		if ($maxrows == 0) {
		die_exit("番組データがありません<BR>");
			
		}

?>

<body BGCOLOR="#ffffff" TEXT="#494949" LINK="#0047ff" VLINK="#000000" ALINK="#c6edff" >
<div align="center">
<?php 
printhtmlpageheader();
?>
  <p align="left"><font color="#494949" size="6">番組一覧</font></p>
  <hr size="4">
<p align="left">全番組リストを表示します。</p>

<?
		/* フィールド数 */
		$maxcols = pg_num_fields($rs);
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
			for ($row = 0; $row < $maxrows; $row++) { /* 行に対応 */
				echo("<tr>\n");
				/* pg_fetch_row で一行取り出す */
				$rowdata = pg_fetch_row($rs, $row);
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
			}
		?>
	</tbody>
</table>


</body>
</html>
