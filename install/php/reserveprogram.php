<?php
/*
  Anime recording system foltia
  http://www.dcc-jpl.com/soft/foltia/

  reserveprogram.php

  目的
  番組録画予約ページを表示します。

  引数
  tid:タイトルID

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

$tid = getgetnumform(tid);

if ($tid == "") {
    printtitle_and_die("<title>foltia</title>", "登録番組がありません<BR>");
}

printtitle("<title>foltia</title>", false);

$now = date("YmdHi");   

//タイトル取得
$query = "select title from foltia_program where tid = ? ";
$rs = sql_query($con, $query, "DBクエリに失敗しました",array($tid));
$rowdata = $rs->fetch();
if (! $rowdata) {
    die_exit("登録番組がありません<BR>");
}

$title = htmlspecialchars($rowdata[0]);

?>


<body>
  <div id="wrapper">


  <?php 

    printhtmlpageheader();

  ?>

  <!-- 表示するページ FIXME: テンプレートが有効に使える場面であるためあとで重複コードは排除する -->
  <div id="page-wrapper">
    <div id="container-fluid">

      <!-- ページタイトル -->
      <div class="row">
        <div class="col-lg-12">
          <h1 class="page-header">
            &nbsp;番組予約
	  </h1>

	  <?php

       if ($tid == 0) {
	   print "<p>EPG予約の追加は「<a href=\"./viewepg.php\">番組表</a>」メニューから行って下さい。</p>\n</body>\n</html>\n";
	   exit ;
       }

	?>

	<p align="left">「<?php print "$title"; ?>」を番組予約モードで録画予約します。</p>
	<ol class="breadcrumb">
	  <li>
	    <i class="fa fa-fw fa-table"></i>  <a href="./index.php"> 放映予定</a>
	  </li>
	  <li class="active">
	    <i class=\"fa fa-fw fa-table\"></i>  <a href=\"./index.php\"> 放映予定</a>
	  </li>
	</ol>
        </div>
      </div>    
      <!-- /.row -->


      <!-- ページのコンテンツ -->
      <div class="row">
	<div class="col-lg-6">


	  <form name="recordingsetting" method="GET" action="reservecomp.php">
	    <input type="submit" value="予約" >
	      <br>
		<br>

		<div class="table-responsive">
		  <table class="table table-bordered table-hover">
		    <tr>
		      <td>放送局</td>
		      <td>デジタル録画優先</td>
		      <td>アナログビットレート</td>
		    </tr>
		    <tr>
		      <td>
			<?php

       //録画候補局検索
       $rowdata = get_record_candidate($con, $tid);

if (! $rowdata) {
    echo("放映局情報がまだはいってません<BR>");
} else {
    $maxcols = $rs->columnCount();
    echo("<select name=\"station\">\n");
    /* テーブルのデータを出力 */
    do {
	echo("<option value=\"");
	echo(htmlspecialchars($rowdata[0]));
	echo("\">");
	echo(htmlspecialchars($rowdata[1]));
	echo("</option>\n");
    } while ($rowdata = $rs->fetch());
    echo("<option value=\"0\">全局</option>\n</select>\n");

}//endif

		?>

	      </td>
	      <td>
		<select name="usedigital">
		  <?php

    if ( $usedigital == 1 ) {
	print "
		<option value=\"1\" selected>する</option>
		<option value=\"0\">しない</option>
		";
    } else {
	print "
		<option value=\"1\">する</option>
		<option value=\"0\" selected>しない</option>
		";
    }
		  ?>
		</select>
	      </td>

	      <td><select name="bitrate">
		<option value="14">最高画質</option>
		<option value="13">13Mbps</option>
		<option value="12">12Mbps</option>
		<option value="11">11Mbps</option>
		<option value="10">10Mbps</option>
		<option value="9">9Mbps</option>
		<option value="8">高画質</option>
		<option value="7">7Mbps</option>
		<option value="6">6Mbps</option>
		<option value="5" selected>標準画質</option>
		<option value="4">4Mbps</option>
		<option value="3">3Mbps</option>
		<option value="2">高い圧縮</option>
	      </select></td>
	    </tr>
	  </table>
	</div>
	  <input type="hidden" name="tid" value="<?php print "$tid"; ?>">
	</form>
	<p>&nbsp; </p>
	<p><br>
	  今後の放映予定 </p>

	  <?php

	    list($rs, $rowdata) = get_plan_of_program($con, $now, $tid);

if ( ! $rowdata ) {
    echo("放映予定はありません<BR>");
} else {
    $maxcols = $rs->columnCount();

	  ?>
	  <table class="table table-bordered table-hover">
	    <thead>
	      <tr>
		<th align="left">放映局</th>
		<th align="left">話数</th>
		<th align="left">サブタイトル</th>
		<th align="left">開始時刻</th>
		<th align="left">総尺</th>
		<th align="left">時刻ずれ</th>

	      </tr>
	    </thead>

	    <tbody>
	      <?php
		       /* テーブルのデータを出力 */
		       do {
			   echo("<tr>\n");
			   for ($col = 0; $col < $maxcols; $col++) { /* 列に対応 */
			       if ($col == 3) {
				   echo("<td>".htmlspecialchars(foldate2print($rowdata[$col]))."<br></td>\n");
			       } else {
				   echo("<td>".htmlspecialchars($rowdata[$col])."<br></td>\n");
			       }
			   }
			   echo("</tr>\n");
		       } while ($rowdata = $rs->fetch());
}//end if
	      ?>
	    </tbody>
	  </table>
	</div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
