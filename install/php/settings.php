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

// receive request
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
case 'PUT':
    parse_str(file_get_contents("php://input"), $post_vars);
    logging("Change setting for stationid: " . $post_vars['stationid'] . ", stationrecch: " . $post_vars['stationrecch']);
    set_foltia_station_recch($con, $post_vars);
    break;
case 'POST':
    logging("Delete setting for stationid: " . $_POST['stationid'] . ", stationrecch: " . $_POST['stationrecch']);
    delete_foltia_station_recch($con, $_POST);
    break;
default:
    break;
}

?>

<?php

printtitle_with_script("<title>foltia:設定</title>", "bower_components/jquery/dist/jquery.min.js");

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
	    <h2>&nbsp;foltiaが使用する物理チャンネルの設定</h2>
	    <p>&nbsp;・録画に使用するチャンネルをここで設定してください</p>





	    <div class="table-responsive">
	      <table class="table table-bordered table-hover">

		<!-- テーブルのヘッダ -->
		<thead>
                  <tr>
		    <th>No.</th>
		    <th>使用/不使用</th>
                    <th>局名</th>
		    <th>物理チャンネル</th>
		    <th></th>
                  </tr>
                </thead>
		<?php

echo "<tbody>\n";

$station_array = get_foltia_station_data($con);
$used_station_map = get_used_foltia_station_map($con);

for ($i = 0; $i < count($station_array); $i++) {

    $row = $station_array[$i];
    $stationid = $row['stationid'];
    $stationname = $row['stationname'];
    $stationrecch = $row['stationrecch'];

    echo "<tr>\n";
    echo "<td>$stationid</td>";

    if ( array_key_exists($stationid, $used_station_map) ) {
	echo "<td><button id=\"$stationid\" type=\"button\" class=\"btn btn-sm btn-success\">録画に使用する</button></td>";
	$stationrecch = $used_station_map[$stationid];
	echo "<td>$stationname</td>";
	echo "<td><input class=\"form-control\" name=\"stationrecch\" placeholder=\"{$stationrecch}\" value=\"{$stationrecch}\"></td>";
	echo "<td><button name=\"post\" type=\"submit\" class=\"btn btn-sm btn-danger\">削除する</button></td>";
    } else {
	echo "<td><button id=\"$stationid\" type=\"button\" class=\"btn btn-sm btn-default\">録画に使用しない</button></td>";
	echo "<td>$stationname</td>";
	echo "<td><input class=\"form-control\" name=\"stationrecch\" placeholder=\"{$stationrecch}\" value=\"{$stationrecch}\"></td>";
	echo "<td><button name=\"put\" type=\"submit\" class=\"btn btn-sm btn-primary\">登録する</button></td>";
    }
    
    echo "</tr>\n";
}
echo "</tbody>\n";

		?>
	      </table>
	    </div>
	  </div>
	</div>
	<!-- /.row -->

    </div>
  </div>
  </div>


<script type="text/javascript">

$(function() {

	$(":button:submit").click(function() {

		var stationid = $(this).parent().parent().find('td:eq(0)').text();
		var stationrecch = $(this).parent().parent().find('input:eq(0)').val();
		var type = $(this).attr('name');

		$.ajax({
			type: type,
			url: "settings.php",
			data: {
				"stationid": stationid,
				"stationrecch": stationrecch
			},
			timeout:10000
		}).done(function(data){
			window.location.reload(true);
		});
	});

	$("button").click(function() {
		var num = $(this).attr('id');
		if ( num ) {
			if ( $(this).hasClass("btn btn-sm btn-success") ) {
				$(this).attr('class', 'btn btn-sm btn-default');
				$(this).html('録画に使用しない');
			} else {
				$(this).attr('class', 'btn btn-sm btn-success');
				$(this).html('録画に使用する');
			}
		}
	});
});

</script>

</body>
</html>
