<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

settings.php

目的
取得する放送局の物理チャンネルを設定します

引数
なし

 DCC-JPL Japan/foltia project

*/

include("./foltialib.php");
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

printtitle("<title>foltia:設定</title>", false);

?>

<body>
  <!-- ナビゲーションバーなど -->
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
              &nbsp;foltia:設定
            </h1>
            <ol class="breadcrumb">
              <li>
		<i class="fa fa-fw fa-table"></i>  <a href="./index.php"> 放映予定</a>
              </li>
              <li class="active">
		<i class="fa fa-fw fa-wrench"></i> 設定
              </li>
            </ol>
          </div>
	</div>
	<!-- /.row -->


	<!-- ページのコンテンツ -->
	<div class="row">
	  <div class="col-lg-6">
	    <h2>&nbsp;foltiaが使用する物理チャンネル設定</h2>
	    <div class="table-responsive">
	      <table class="table table-bordered table-hover">

		<!-- テーブルのヘッダ -->
		<thead>
                  <tr>
		    <th>No.</th>
		    <th>使用する</th>
                    <th>局名</th>
		    <th>物理チャンネル</th>
                  </tr>
                </thead>

	      </table>
	    </div>
	  </div>
	</div>
	<!-- /.row -->

    </div>
  </div>
  </div>
</body>
</html>
