<?php
/*
  Anime recording system foltia
  http://www.dcc-jpl.com/soft/foltia/

  listreserve.php

  目的
  録画予約番組放映予定と予約番組名を表示します。

  引数
  r:録画デバイス数
  startdate:特定日付からの予約状況。YYYYmmddHHii形式で。表示数に限定かけてないのでレコード数が大量になると重くなるかも知れません。


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

$userclass = getuserclass($con);
$mymemberid = getmymemberid($con);
$now = getgetnumform(startdate);

if ($now == "") {
    $now = getgetnumform(date);
}

if ($now < 200501010000) {
    $now = date("YmdHi");   
}

$rs = get_all_list_reserve($con, $now);

//チューナー数
if (getgetnumform(r) != "") {
    $recunits = getgetnumform(r);
} elseif ($recunits == "") {
    $recunits = 2;
}


printtitle("<title>foltia:record plan</title>", false);

?>

<body>
  <div id="wrapper">

    <div align="center">
      <?php printhtmlpageheader(); ?>
    </div>

    <!-- 表示するページ FIXME: テンプレートが有効に使える場面であるためあとで重複コードは排除する -->
    <div id="page-wrapper">
      <div id="container-fluid">


	<!-- ページタイトル -->
	<div class="row">
          <div class="col-lg-12">
            <h1 class="page-header">
              &nbsp;予約一覧
            </h1>

	    <p align="left">録画予約番組放映予定と予約番組名を表示します。</p>

            <ol class="breadcrumb">
              <li>
		<i class="fa fa-fw fa-table"></i>  <a href="./index.php"> 放映予定</a>
              </li>
              <li class="active">
		<i class="fa fa-fw fa-dashboard"></i>  <a href="./listreserve.php"> 予約一覧</a>
              </li>
            </ol>
          </div>
	</div>
	<!-- /.row -->

	<?php

    $rowdata = $rs->fetch();

if (! $rowdata) {
    print "番組データがありません<BR>\n";			
} else {
    /* フィールド数 */
    $maxcols = $rs->columnCount();
      ?>


	<!-- ページのコンテンツ -->
	<div class="row">
	  <div class="col-lg-12">


	    <table class="table table-bordered table-hover">
	      <thead>
		<tr>
		  <th align="left">TID</th>
		  <th align="left">放映局</th>
		  <th align="left">タイトル</th>
		  <th align="left">話数</th>
		  <th align="left">サブタイトル</th>
		  <th align="left">開始時刻(ズレ)</th>
		  <th align="left">総尺</th>
		  <th align="left">画質</th>
		  <th align="left">デジタル優先</th>

		</tr>
	      </thead>

	      <tbody>
		<?php
	     /* テーブルのデータを出力 */
	     do {
		 echo("<tr>\n");

		 $pid = htmlspecialchars($rowdata[9]);
		 $tid = htmlspecialchars($rowdata[0]);
		 $title = htmlspecialchars($rowdata[2]);
		 $subtitle = htmlspecialchars($rowdata[4]);
		 $dbepgaddedby = htmlspecialchars($rowdata[10]);
		 //重複検出
		 //開始時刻 $rowdata[5]
		 //終了時刻
		 $endtime = calcendtime($rowdata[5],$rowdata[6]);
		 $rclass = "";
		 $overlap = get_overlap_recording($con, $rowdata, $endtime);
		 $owrowall = $overlap->fetchAll();
		 $overlapmaxrows = count($owrowall);

		 if ($overlapmaxrows > ($recunits) ) {

		     $owtimeline = array();

		     for ($rrow = 0; $rrow < $overlapmaxrows ; $rrow++) {
			 $owrowdata = $owrowall[$rrow];
			 $owtimeline[ $owrowdata['startdatetime'] ] = $owtimeline[ $owrowdata['startdatetime'] ] +1;

			 $owrend = calcendtime( $owrowdata['startdatetime'], $owrowdata['lengthmin'] );
			 $owtimeline[ $owrend ] = $owtimeline[ $owrend ] -1;
			 //注意: NULL に減算子を適用しても何も起こりませんが、NULL に加算子を 適用すると 1 となります。
		     }

		     ksort ( $owtimeline );

		     $owcount = 0;
		     foreach ( $owtimeline as $key => $val ) {
			 $owcount += $val;

			 if ( $owcount > $recunits ) {
			     $rclass = "overwraped";
			     break;
			 }
		     }
		 }

		 //外部チューナー録画
		 $externalinputs = 1; //現状一系統のみ
		 $eoverlap = get_eoverlap_recording($con, $rowdata, $endtime);
		 $eowrowall = $eoverlap->fetchAll();
		 $eoverlapmaxrows = count($eowrowall);
		 if ($eoverlapmaxrows > ($externalinputs) ) {

		     $eowtimeline = array();

		     for ($erow = 0; $erow < $eoverlapmaxrows ; $erow++) {
			 $eowrowdata = $eowrowall[$erow];
			 $eowtimeline[ $eowrowdata['startdatetime'] ] = $eowtimeline[ $eowrowdata['startdatetime'] ] +1;
			 $eowrend = calcendtime( $eowrowdata['startdatetime'], $eowrowdata['lengthmin'] );
			 $eowtimeline[ $eowrend ] = $eowtimeline[ $eowrend ] -1;
		     }

		     ksort ( $eowtimeline );

		     $eowcount = 0;
		     foreach ( $eowtimeline as $key => $val ) {
			 $eowcount += $val;

			 if ( $eowcount > $externalinputs ) {
			     $rclass = "exoverwraped";
			     break;
			 }
		     }

		 }
		 echo("<tr class=\"$rclass\">\n");
		 // TID
		 print "<td>";
		 if ($tid == 0 ) {
		     print "$tid";
		 } else {
		     print "<a href=\"reserveprogram.php?tid=$tid\">$tid</a>";
		 }
		 print "</td>\n";
		 // 放映局
		 echo("<td>".htmlspecialchars($rowdata[1])."<br></td>\n");
		 // タイトル
		 print "<td>";
		 if ($tid == 0 ) {
		     print "$title";
		 } else {
		     print "<a href=\"http://cal.syoboi.jp/tid/$tid\" target=\"_blank\">$title</a>";
                       }

		       print "</td>\n";
		       // 話数
		       echo("<td>".htmlspecialchars($rowdata[3])."<br></td>\n");
		       // サブタイ
		       if ($pid > 0) {
			   print "<td><a href=\"http://cal.syoboi.jp/tid/$tid/time#$pid\" target=\"_blank\">$subtitle<br></td>\n";
                       } else {
			   if (($mymemberid == $dbepgaddedby)||($userclass <= 1)) {
			       if ($userclass <= 1 ) {//管理者なら
				   $membername = getmemberid2name($con,$dbepgaddedby);
				   $membername = ":" . $membername;
			       } else {
				   $membername = "";
			       }
			       print "<td>$subtitle [<a href=\"delepgp.php?pid=$pid\">予約解除</a>$membername]<br></td>\n";
			   } else {
			       print "<td>$subtitle [解除不能]<br></td>\n";
			   }
		       }
		       // 開始時刻(ズレ)
		       echo("<td>".htmlspecialchars(foldate2print($rowdata[5]))."<br>(".htmlspecialchars($rowdata[8]).")</td>\n");
		       // 総尺
		       echo("<td>".htmlspecialchars($rowdata[6])."<br></td>\n");
		       
		       //録画レート
		       echo("<td>".htmlspecialchars($rowdata[7])."<br></td>\n");
		       
		       //デジタル優先
		       echo("<td>");
		       if (htmlspecialchars($rowdata[11]) == 1) {
			   print "する";
		       } else {
			   print "しない";
		       }
		       echo("<br></td>\n");
		       echo("</tr>\n");
		   } while ($rowdata = $rs->fetch());
	  ?>
</tbody>
</table>

</div>
</div>
<!-- /.row -->



<!-- ページのコンテンツ -->
<div class="row">
  <div class="col-lg-12">

    <table class="table table-bordered table-hover">
      <tr><td>アナログ重複表示</td><td><br /></td></tr>
      <tr><td>エンコーダ数</td><td><?php print "$recunits"; ?></td></tr>
      <tr class="overwraped"><td>チューナー重複</td><td><br /></td></tr>
      <tr class="exoverwraped"><td>外部入力重複</td><td><br /></td></tr>
    </table>

  </div>
</div>
<!-- /.row -->

<?php

								    set_maxcols_for_update($con, $maxcols);

?>


<!-- ページのコンテンツ -->
<div class="row">
  <div class="col-lg-12">


    <p align="left">録画予約番組タイトルを表示します。</p>
    <table class="table table-bordered table-hover">
      <thead>
	<tr>
	  <th align="left">予約解除</th>
	  <th align="left">TID</th>
	  <th align="left">放映局</th>
	  <th align="left">タイトル</th>
	  <th align="left">録画リスト</th>
	  <th align="left">画質</th>
	  <th align="left">デジタル優先</th>

	</tr>
      </thead>

      <tbody>
	<?php
     /* テーブルのデータを出力 */
     do {
	 $tid = htmlspecialchars($rowdata[0]);
	 
	 if ($tid > 0) {
	     echo("<tr>\n");
	     //予約解除
	     if ( $userclass <= 1) {
		 echo("<td><a href=\"delreserve.php?tid=$tid&sid=" .
			 htmlspecialchars($rowdata[4])  . "\">解除</a></td>\n");
	     } else {
		 echo("<td>−</td>");		
	     }
	     //TID
	     echo("<td><a href=\"reserveprogram.php?tid=$tid\">$tid</a></td>\n");
	     //放映局
	     echo("<td>".htmlspecialchars($rowdata[1])."<br></td>\n");
	     //タイトル
	     echo("<td><a href=\"http://cal.syoboi.jp/tid/$tid\" target=\"_blank\">" .
		     htmlspecialchars($rowdata[2]) . "</a></td>\n");

		//MP4
		echo("<td><a href=\"showlibc.php?tid=$tid\">mp4</a></td>\n");
		//画質(アナログビットレート)
		echo("<td>".htmlspecialchars($rowdata[3])."<br></td>\n");
		//デジタル優先
		echo("<td>");
		if (htmlspecialchars($rowdata[5]) == 1) {
		    print "する";
		} else {
		    print "しない";
		}
		echo("</tr>\n");
	    } else {
		print "<tr>
		<td>−</td><td>0</td>
		<td>[全局]<br></td>
		<td>EPG録画</td>
		<td><a href=\"showlibc.php?tid=0\">mp4</a></td>";
		echo("<td>".htmlspecialchars($rowdata[3])."<br></td>");
		//デジタル優先
		echo("<td>");
		if (htmlspecialchars($rowdata[5]) == 1) {
		    print "する";
		} else {
		    print "しない";
		}
		echo("\n</tr>");
	    }//if tid 0
	} while ($rowdata = $rs->fetch());
}//else
    ?>
  </tbody>
</table>

</div>
</div>
<!-- /.row -->

</div>
</div>
</div>
</body>
</html>
