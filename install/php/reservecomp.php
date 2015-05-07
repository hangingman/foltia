<?php
/*
  Anime recording system foltia
  http://www.dcc-jpl.com/soft/foltia/

  reserveprogram.php

  目的
  番組の予約登録をします。

  引数
  tid:タイトルID
  station:録画局
  bitrate:録画ビットレート(単位:Mbps)

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

printtitle("<title>foltia</title>", false);

$tid = getgetnumform(tid);
if ($tid == "") {
    die_exit("番組が指定されていません<BR>");
}

$station = getgetnumform(station);
if ($station == "") {
    $station = 0;
}
$usedigital = getgetnumform(usedigital);
if ($usedigital == "") {
    $usedigital = 0;
}
$bitrate = getgetnumform(bitrate);
if ($bitrate == "") {
    $bitrate = 5;
}


$now = date("YmdHi");   

//タイトル取得
$title = get_title_with_tid($con, $tid);

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
              &nbsp;予約完了
	    </h1>

	    「<?php print "$title"; ?>」を番組予約モードで予約しました。 <br>
	    <br>
	      予約スケジュール 
	      <br>
	      </div>
	    </div>
	    <!-- /.row -->


	    <!-- ページのコンテンツ -->
	    <div class="row">
	      <div class="col-lg-6">

		<?php

       list($rowdata, $rs) = get_schedule_of_reserve($con, $now, $station, $tid);
if (! $rowdata) {
    echo("放映予定はいまのところありません<BR>");
} else {
    $maxcols = $rs->columnCount();
    
      ?>

		<div class="table-responsive">
		  <table class="table table-bordered table-hover">
		    <thead>
		      <tr>
			<th align="left">PID</th>
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
				  echo("<td>".htmlspecialchars($rowdata[$col])."<br></td>\n");
			      }
			      echo("</tr>\n");
			  } while ($rowdata = $rs->fetch());
}//end if

// 録画のキューを入れる
set_queue_from_php($con, $demomode, $station, $tid, $bitrate, $usedigital);

		      ?>
		    </tbody>
		  </table>
		</div>

	      </div>
	    </div>
	  </div>
	</div>
      </div>
    </body>
  </html>
