<?php

include("./foltia_config2.php");

/*
  こちらのモジュールは
  Apache + PHP + PostgreSQL 実験室
  http://www.hizlab.net/app/
  のサンプルを使わせていただいております。
  ありがとうございます。
*/

/* エラー表示の抑制 */
//error_reporting(0);

$foltia_header = "
<!DOCTYPE html>
<html lang=\"ja\">
<head>
<meta charset=\"UTF-8\">

";

// sb_adminのパス
const SB_ADMIN_PATH = "bower_components/startbootstrap-sb-admin";

// infoログ出力
function logging($message) {

    $log = '/home/foltia/debuglog.txt';
    $current = file_get_contents($log);
    $current .= "$message\n";
    file_put_contents($log, $current);
}

// タイトル・メタタグの表示
function printtitle($title, $use_warndiskfreearea) {

    print @$foltia_header;

    if ($use_warndiskfreearea) {
        //ディスク空き容量によって背景色表示変更
        warndiskfreearea();
    }

    // print "<title>foltia:放映予定</title>
    print $title;
    printcssinfo();
    print "</head>";
}

// タイトル・メタタグの表示
function printtitle_with_script($title, $scriptpath) {

    print @$foltia_header;

    // print "<title>foltia:放映予定</title>
    print $title;
    printcssinfo();

    if ($scriptpath != "") {
        print "<script src=\"{$scriptpath}\" language=\"JavaScript\" type=\"text/javascript\"></script>";
    }

    print "</head>";
}

// タイトル・メタタグの表示
function printtitle_and_die($title, $element) {

    print @$foltia_header;

    // print "<title>foltia:放映予定</title>
    print $title;
    printcssinfo();
    die_exit($element);
}

// css情報を取得して出力する
function printcssinfo() {

    $sb_admin = SB_ADMIN_PATH;

    $css = <<<EOF

<!-- Bootstrap Core CSS -->
<link href="{$sb_admin}/css/bootstrap.min.css" rel="stylesheet">

<!-- Custom CSS -->
<link href="{$sb_admin}/css/sb-admin.css" rel="stylesheet">

<!-- Morris Charts CSS -->
<link href="{$sb_admin}/css/plugins/morris.css" rel="stylesheet">

<!-- Custom Fonts -->
<link href="{$sb_admin}/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
	<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
	<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->

EOF
	 ;

    print $css;
}

function print_navigate_bar() {

    $nav = <<<EOF
<!-- Navigation -->
<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
	<!-- ヘッダ部分 -->
	<div class="navbar-header">
		<a class="navbar-brand" href="http://www.dcc-jpl.com/soft/foltia/">foltia</a>
	</div>

	<!-- Top Menu Items -->
	<ul class="nav navbar-right top-nav">
		<!-- メッセージ表示（お知らせに使う） -->
		<li class="dropdown">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-envelope"></i> <b class="caret"></b></a>
			<ul class="dropdown-menu message-dropdown">
			</ul>
		</li>
		<!-- ログインユーザー名表示（ログインする設定にしている場合のみ） -->
		<li class="dropdown">
			<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> foltia <b class="caret"></b></a>
			<ul class="dropdown-menu">
				<li>
					<a href="#"><i class="fa fa-fw fa-power-off"></i>ログアウト</a>
				</li>
			</ul>
		</li>
	</ul>

	<!-- 左側・foltiaの各ページへのリンク -->
	<div class="collapse navbar-collapse navbar-ex1-collapse">
		<ul class="nav navbar-nav side-nav">

			<li>
				<a href="./index.php"><i class="fa fa-fw fa-table"></i> 放映予定</a>
			</li>
			<li>
				<a href="./index.php?mode=new"><i class="fa fa-fw fa-bell"></i> 新番組</a>
			</li>
			<li>
				<a href="./listreserve.php"><i class="fa fa-fw fa-dashboard"></i> 予約一覧</a>
			</li>
			<li>
				<a href="./titlelist.php"><i class="fa fa-fw fa-edit"></i> 番組一覧</a>
			</li>
			<li>
				<a href="./viewepg.php"><i class="fa fa-fw fa-desktop"></i> EPG番組表</a>
			</li>

			<li>
				<a href="./settings.php"><i class="fa fa-fw fa-wrench"></i> 設定</a>
			</li>

			<li>
				<a href="./index.php"><i class="fa fa-fw fa-arrows-v"></i> 録画一覧 <i class="fa fa-fw fa-caret-down"></i></a>
				<li>
					<a href="./showplaylist.php">録画順</a>
				</li>
				<li>
					<a href="./showplaylist.php?list=title">番組順</a>
				</li>
				<li>
					<a href="./showplaylist.php?list=raw">全部</a>
				</li>
			</li>
			<li>
				<a href="./showlib.php"><i class="fa fa-fw fa-table"></i> 録画ライブラリ</a>
			</li>
			<li> 
				<a href="./folcast.php"><i class="fa fa-fw fa-table"></i> iTunesに登録</a>
			</li>
		</ul>
	</div>
	<!-- /.navbar-collapse -->
</nav>
EOF
	 ;

    print $nav;
}

// GET用フォームデコード
function getgetform($key) {
    if ($_GET["{$key}"] != "") {
	$value = $_GET["{$key}"];
	$value = escape_string($value);
	$value = htmlspecialchars($value);
	return ($value);
    }
}
//GET用数字フォームデコード
function getgetnumform($key) {
    if (isset($_GET["{$key}"] )) {
	$value = $_GET["{$key}"];
	$value = ereg_replace("[^-0-9]", "", $value);
	$value = escape_numeric($value);
	return ($value);
    }
}

//フォームデコード
function getform($key) {
    if ($_POST["{$key}"] != "") {
	$value = $_POST["{$key}"];
	$value = escape_string($value);
	$value = htmlspecialchars($value);
	return ($value);
    }
}
//数字専用フォームデコード
function getnumform($key) {
    if ($_POST["{$key}"] != "") {
	$value = $_POST["{$key}"];
	$value = escape_string($value);
	$value = htmlspecialchars($value);
	$value = ereg_replace("[^0-9]", "", $value);
	$value = escape_numeric($value);
	return ($value);
    }
}

/* 全角カタカナ化してスペースを削除してインデックス用にする */
function name2read($name) {
    $name = mb_convert_kana($name, "KVC", "UTF-8");
    $name = mb_convert_kana($name, "s", "UTF-8");
    $name = ereg_replace(" ", "", $name);

    return $name;
}

/* 数字を半角化して数字化してインデックス用にする */
function pnum2dnum($num) {
    $num = mb_convert_kana($num, "a", "UTF-8");
    $num = ereg_replace("[^0-9]", "", $num);

    return $num;
}

/* 終了関数の定義 */
function die_exit($message) {
?>
<p class="error"><?php print "$message"; ?></p>
<div class="index"><a href="./">トップ</a></div>
</body>
</html><?php
						 exit;
}

/* 入力した値のサイズをチェック */
function check_length($str, $maxlen, $must, $name) {
    $len = strlen($str);
    if ($must && $len == 0) {
        die_exit("$name が入力されてません。必須項目です。");
    }
    if ($len > $maxlen) {
        die_exit("$name は $len 文字以下で入力して下さい。全角文字は、一文字で二文字分と計算されます。");
    }
}

/* SQL 文字列のエスケープ */
function escape_string($sql, $quote = FALSE) {
    if ($quote && strlen($sql) == 0) {
        return "null";
    }
    if (preg_match("/^pgsql/", DSN)) {
	return ($quote ? "'" : "") .
                                   pg_escape_string($sql) .
                                   ($quote ? "'" : "");
    }else if (preg_match("/^sqlite/", DSN)) {
	/*	return ($quote ? "'" : "") .
		sqlite_escape_string($sql) .
		($quote ? "'" : "");
	*/
	return($sql);
    } else {
        return "null";
    }
} 

/* SQL 数値のエスケープ */
function escape_numeric($sql) {
    if (strlen($sql) == 0) {
        return "null";
    }
    if (!is_numeric($sql)) {
        die_exit("$sql は数値ではありません。");
    }
    return $sql;
}

/* DBに接続 */
function m_connect() { 
    try {
	$dbh = new PDO(DSN);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return($dbh);
    } catch (PDOException $e) {
	die_exit($e->getMessage() . ": データベースに接続出来ませんでした。");
    }
    /* データベースと、PHP の内部文字コードが違う場合 */
}

/* データベースとの接続を切り離す */
function m_close($dbh) {
    return null;
}

function sql_debug($sql_string, array $params = null) {
    if (!empty($params)) {
        $indexed = $params == array_values($params);
        foreach($params as $k=>$v) {
            if (is_object($v)) {
                if ($v instanceof \DateTime) $v = $v->format('Y-m-d H:i:s');
                else continue;
            }
            elseif (is_string($v)) $v="'$v'";
            elseif ($v === null) $v='NULL';
            elseif (is_array($v)) $v = implode(',', $v);

            if ($indexed) {
                $sql_string = preg_replace('/\?/', $v, $sql_string, 1);
            }
            else {
                if ($k[0] != ':') $k = ':'.$k; //add leading colon if it was left out
                $sql_string = str_replace($k,$v,$sql_string);
            }
        }
    }
    return $sql_string . "\n";
}

function pdo_debug($query, $paramarray) {
    logging(sql_debug($query, $paramarray));
}

/* SQL 文を実行 */
function sql_query($dbh, $query, $errmessage, $paramarray = null) {
    try {
	$rtn = $dbh->prepare("$query");
	$rtn->execute($paramarray);

        /* to debuglog */
	pdo_debug($query, $paramarray);

	return($rtn);

    } catch (PDOException $e) {
        /* to debuglog */
        $msg = $errmessage                   . "\n" .
             $e->getMessage()                . "\n" .
             var_export($e->errorInfo, true) . "\n" . $query;

	$dbh = null;
	logging($msg);
    }
}

/* select した結果をテーブルで表示 */
function m_showtable($rs) {
    /* 検索件数 */
    $maxrows = 0;
    
    $rowdata = $rs->fetch();
    if (! $rowdata) {
        echo("<p class=\"msg\">データが存在しません</p>\n");
        return 0;
    }
    
    /* フィールド数 */
    $maxcols = $rs->columnCount();
    ?>
<table class="list" summary="データ検索結果を表示" border="1">
  <thead>
    <tr>
      <?php
	    /* テーブルのヘッダーを出力 */
	    for ($col = 1; $col < $maxcols; $col++) {
		/* pg_field_name() はフィールド名を返す */
		$meta = $rs->getColumnMeta($col);
		$f_name = htmlspecialchars($meta["name"]);
		echo("<th abbr=\"$f_name\">$f_name</th>\n");
	    }
      ?>
    </tr>
  </thead>
  <tbody>
    <?php
	  /* テーブルのデータを出力 */
	  do {
	      $maxrows++;

	      echo("<tr>\n");
	      /* １列目にリンクを張る */
	      echo("<td><a href=\"edit.php?q_code=" .
		   urlencode($rowdata[0]) . "\">" .
		   htmlspecialchars($rowdata[1]) . "</a></td>\n");
	      for ($col = 2; $col < $maxcols; $col++) { /* 列に対応 */
		  echo("<td>".htmlspecialchars($rowdata[$col])."<br></td>\n");
	      }
	      echo("</tr>\n");
	  } while ($rowdata = $rs->fetch());
    ?>
  </tbody>
</table>
<?php
	return $maxrows;
}

function printhtmlpageheader() {

    global $useenvironmentpolicy;

    $serveruri = getserveruri();
    $username = $_SERVER['PHP_AUTH_USER'];

    print_navigate_bar();

    print $header;
    if ($useenvironmentpolicy == 1) {
        print "【 $username 】";
    }

    print "</font></p>\n";

}


function renderepgstation($con,$stationname,$start) { //戻り値　なし　EPGの局表示

    $now = date("YmdHi");
    $today = date("Ymd");   
    $tomorrow = date ("Ymd",mktime(0, 0, 0, date("m")  , date("d")+1, date("Y")));
    //$today = "20051013";   
    //$tomorrow = "20051014";
    //$epgstart = $today . "2000";
    $epgstart = $start ;
    //$epgend = $tomorrow . "0400";
    $epgend = calcendtime($start , (8*60));
    $query = "
SELECT startdatetime , enddatetime , lengthmin , epgtitle , epgdesc , epgcategory  ,ontvchannel  ,epgid 
FROM foltia_epg 
WHERE foltia_epg.ontvchannel = '$stationname' AND 
enddatetime  > $epgstart  AND 
startdatetime  < $epgend  
ORDER BY foltia_epg.startdatetime  ASC
	";
    $rs = m_query($con, $query, "DBクエリに失敗しました");
    $rowdata = $rs->fetch();
    if (! $rowdata) {
	print("番組データがありません<BR>");			
    } else {
        print "<table width=\"100%\"  border=\"0\">\n";

	do {
            $printstarttime = substr($rowdata[0],8,2) . ":" .  substr($rowdata[0],10,2);
            $tdclass = "t".substr($rowdata[0],8,2) .  substr($rowdata[0],10,2);
            $title = htmlspecialchars($rowdata[3]);
            $title = z2h($title);
            $desc = htmlspecialchars($rowdata[4]);
            $desc = z2h($desc);
            $height =  htmlspecialchars($rowdata[2]) * 3;
            $epgid =  htmlspecialchars($rowdata[7]);

            print"
      <tr>
        <td height = \"$height\" >$printstarttime  <A HREF=\"./reserveepg.php?epgid=$epgid\">$title</A> $desc <!-- $rowdata[0] - $rowdata[1] --></td>
      </tr>
";

	} while ($rowdata = $rs->fetch());//do
        print "</table>\n";

    }//if
}//end function

function calcendtime($start,$lengthmin) {//戻り値　終了時刻(Ex:200510170130) 
    $startyear =   substr($start,0,4);
    $startmonth =   substr($start,4,2);
    $startday =   substr($start,6,2);
    $starthour =   substr($start,8,2);
    $startmin =   substr($start,10,2);
    //int mktime ( [int hour [, int minute [, int second [, int month [, int day [, int year [, int is_dst]]]]]]] )
    $endtime = date ("YmdHi",mktime($starthour  , $startmin + $lengthmin , 0, $startmonth  , $startday, $startyear));

    return ($endtime );
}//end function


function z2h($string) { //戻り値　半角化した文字
    $stringh = mb_convert_kana($string, "a", "UTF-8");
    return ($stringh );
}

function foldate2rfc822($start) {//戻り値　RFC822スタイルの時刻表記
    $startyear =   substr($start,0,4);
    $startmonth =   substr($start,4,2);
    $startday =   substr($start,6,2);
    $starthour =   substr($start,8,2);
    $startmin =   substr($start,10,2);

    $rfc822 = date ("r",mktime($starthour  , $startmin , 0, $startmonth  , $startday, $startyear));
    
    return ($rfc822);
}//end sub

function foldate2print($start) {//戻り値　日本語風時刻表記
    $startyear =   substr($start,0,4);
    $startmonth =   substr($start,4,2);
    $startday =   substr($start,6,2);
    $starthour =   substr($start,8,2);
    $startmin =   substr($start,10,2);

    $printabledate = date ("Y/m/d H:i",mktime($starthour  , $startmin , 0, $startmonth  , $startday, $startyear));	
    return ($printabledate);
}//end sub

function getserveruri() {//戻り値　サーバアドレス Ex.www.dcc-jpl.com:8800/soft/foltia/

    //リンクURI組み立て
    $sv6 = $_SERVER['SCRIPT_NAME'];///dameNews/sarasorjyu/archives.php
    $sv8 = $_SERVER['SERVER_NAME'];//sync.dcc-jpl.com
    $sv9 = $_SERVER['SERVER_PORT'];
    if ($sv9 == 80) {
        $port = "";
    } else {
        $port = ":$sv9";
    }
    $a = split("/", $sv6);
    array_pop($a);

    $scriptpath = implode("/", $a);

    $serveruri = "$sv8$port$scriptpath";
    return ($serveruri );
}//end sub


function getserverfqdn() {//戻り値　サーバアドレス Ex.www.dcc-jpl.com:8800

    //リンクURI組み立て
    $sv6 = $_SERVER['SCRIPT_NAME'];///dameNews/sarasorjyu/archives.php
    $sv8 = $_SERVER['SERVER_NAME'];//sync.dcc-jpl.com
    $sv9 = $_SERVER['SERVER_PORT'];
    if ($sv9 == 80) {
        $port = "";
    } else {
        $port = ":$sv9";
    }
    $a = split("/", $sv6);
    array_pop($a);

    $scriptpath = implode("/", $a);

    $serveruri = "$sv8$port";
    return ($serveruri );
}//end sub


function printdiskusage() {//戻り値　なし

    // [,全体容量, 使用容量 , 空き容量, 利用割合]
    list (, $all, $use , $free, $usepercent) =  getdiskusage();

    $sb_admin = SB_ADMIN_PATH;

$disk_usage_element = <<<EOF

<div class="row">
<div class="col-lg-6">
  <div class="panel panel-yellow">
    <div class="panel-heading">
    <h3 class="panel-title"><i class="fa fa-long-arrow-right"></i> ディスク使用量</h3>
  </div>

  <div class="panel-body">
    <div id="annual"></div>
    <div class="text-right">
      <a href="#">詳細を見る <i class="fa fa-arrow-circle-right"></i></a>
    </div>
  </div>
</div>
</div>

EOF
;

// 使用容量
$int_usepercent = intval(rtrim($usepercent, '%'));
$int_freepercent = 100 - $int_usepercent;

$scripts = <<<EOF

<!-- jQuery Version 1.11.0 -->
<script src="{$sb_admin}/js/jquery.js"></script>

<!-- Bootstrap Core JavaScript -->
<script src="{$sb_admin}/js/bootstrap.min.js"></script>

<!-- Morris Charts JavaScript -->
<script src="{$sb_admin}/js/plugins/morris/raphael.min.js"></script>
<script src="{$sb_admin}/js/plugins/morris/morris.min.js"></script>

<script type="text/javascript">
Morris.Donut({
  element: 'annual',
  data: [
    {label: '空き容量'  , value: {$int_freepercent}, formatted: "{$free}B   {$int_freepercent}%" },
    {label: '使用量'	, value: {$int_usepercent} , formatted: "{$use}B    {$int_usepercent}%"  }
  ],
  formatter: function (x, data) { return data.formatted; },
  resize: true
 });

</script>

EOF
;

print $disk_usage_element;
print $scripts;

}//end sub


function getdiskusage() {//戻り値　配列　[,全体容量, 使用容量 , 空き容量, 利用割合]

    global $recfolderpath,$recfolderpath;

    exec ( "df -hP  $recfolderpath", $hdfreearea);
    $freearea = preg_split ("/[\s,]+/", $hdfreearea[count($hdfreearea)-1]);

    return $freearea;
    
}//endsub


function printtrcnprocesses() {

    $ffmpegprocesses = `ps ax | grep ffmpeg | grep -v grep |  wc -l `;
    $uptime = exec('uptime');

$trcn_processes_element = <<<EOF

<div class="row">
<div class="col-lg-4">
  <div class="panel panel-red">
    <div class="panel-heading">
    <h3 class="panel-title"><i class="fa fa-long-arrow-right"></i> トラコン稼働数</h3>
  </div>

  <div class="panel-body">
    連続稼働時間:{$uptime}<br>
    トラコン稼働数:{$ffmpegprocesses}<br>
  </div>
</div>
</div>

EOF
;

    print $trcn_processes_element;

}//endsub


function warndiskfreearea() {

    global $demomode;

    if ($demomode) {
        print "<!-- demo mode -->";
    } else {

        global $recfolderpath,$hdfreearea ;

        exec ( "df   $recfolderpath | grep $recfolderpath", $hdfreearea);
        $freearea = preg_split ("/[\s,]+/", $hdfreearea[0]);
        $freebytes = $freearea[3];
        if ($freebytes == "" ) {
            //
            //print "<!-- err:\$freebytes is null -->";
        } elseif($freebytes > 1024*1024*100 ) {// 100GB以上あいてれば
            //なにもしない
            print "<style type=\"text/css\"><!-- --></style>";
        } elseif($freebytes > 1024*1024*50 ) {// 100GB以下
            print "<style type=\"text/css\"><!--
	body {
	background-color: #CCCC99;
 	}
-->
</style>
";
        } elseif($freebytes > 1024*1024*30 ) {// 50GB以下
            print "<style type=\"text/css\"><!--
	body {
	background-color:#CC6666;
 	}
-->
</style>
";
        } elseif($freebytes > 0 ) {// 30GB以下
            print "<style type=\"text/css\"><!--
	body {
	background-color:#FF0000;
 	}
-->
</style>
";
        } else { //空き容量 0バイト
            print "<style type=\"text/css\"><!--
	body {
	background-color:#000000;
 	}
-->
</style>
";
        }//endif freebytess

    }//endif demomode

}//endsub



function foldatevalidation($foldate) {

    if (strlen($foldate) == 12 ) {

        $startyear =   substr($foldate,0,4);
        $startmonth =   substr($foldate,4,2);
        $startday =   substr($foldate,6,2);
        $starthour =   substr($foldate,8,2);
        $startmin =   substr($foldate,10,2);

        $startepoch = date ("U",mktime($starthour  , $startmin , 0, $startmonth  , $startday, $startyear));	
        $nowe = time();
        if ($startepoch > $nowe) {
            //print "$foldate:$startepoch:$nowe";
            return TRUE;
        } else {
            return FALSE;
        }	//end if $startepoch > $nowe
    } else {
        return FALSE;
    }//end if ($foldate) == 12 

}//end function



function login($con,$name,$passwd) {
    global $environmentpolicytoken;

    //入力内容確認
    if (((mb_ereg('[^0-9a-zA-Z]', $name)) ||(mb_ereg('[^0-9a-zA-Z]', $passwd) ))) {
	
        //print "エラー処理\n";
        //print "<!-- DEBUG name/passwd format error-->";
        redirectlogin();
	
    } else {
        //print "正常処理\n";
        //db検索
        escape_string($name);
        escape_string($passwd);

        $query = "
SELECT memberid ,userclass,name,passwd1 
FROM foltia_envpolicy 
WHERE foltia_envpolicy.name  = '$name'  
	";
        $useraccount = m_query($con, $query, "DBクエリに失敗しました");
	$rowdata = $useraccount->fetch();
	if (! $rowdata) {
	    header("HTTP/1.0 401 Unauthorized");
	    redirectlogin();
	}
	
	$memberid = $rowdata[0];
	$userclass = $rowdata[1];
	$username =  $rowdata[2];
	$dbpasswd = $rowdata[3];

	$rowdata = $useraccount->fetch();
	if ($rowdata) {
            header("HTTP/1.0 401 Unauthorized");
            redirectlogin();
	}

        // passwdをdbから取りだし
        if ($userclass == 0) {
            $dbpasswd = "$dbpasswd";
        } else {
            // db passwdとトークンを連結し
            $dbpasswd = "$dbpasswd"."$environmentpolicytoken";
        }
        //それが入力と一致すれば認証
        if ($passwd == $dbpasswd) {
            //print "認証成功<br>$dbpasswd  $passwd\n";
        } else {
            //print "認証失敗<br>$dbpasswd  $passwd\n";
            header("HTTP/1.0 401 Unauthorized");
            //print "<!-- DEBUG passwd unmatch error>";
            redirectlogin();
        }
    }//end if mb_ereg
}//end function login




function redirectlogin() {
    global $environmentpolicytoken;

    print "<!DOCTYPE html>\n";
    print "<html><head>\n";
    print "<title>foltia:Invalid login</title>\n";
    print "</head><body>\n";
    print "<h1>Invalid login</h1>";
    print "<p>foltiaヘのアクセスにはログインが必要です。再ログインはリロードやブラウザ再起動で、新規アカウント登録は<a href=\"./accountregist.php\">こちらから。</a></p>";
    if ($environmentpolicytoken == "") {
    } else {
        print "<p>突然この画面が表示された場合にはセキュリティコードが変更されたかも知れません。</p>";
    }
    print "</p><hr>\n";
    print "<address>foltia by DCC-JPL Japan/foltia Project.  <a href = \"http://www.dcc-jpl.com/soft/foltia/\">http://www.dcc-jpl.com/soft/foltia/</a></address>\n";
    print "</body></html>\n";

    exit;
}//end function redirectlogin

function getuserclass($con) {
    global $useenvironmentpolicy;
    $username = $_SERVER['PHP_AUTH_USER'];

    if ($useenvironmentpolicy == 1) {
        $query = "
SELECT memberid ,userclass,name,passwd1 
FROM foltia_envpolicy 
WHERE foltia_envpolicy.name  = '$username'  
	";
	$useraccount = m_query($con, $query, "DBクエリに失敗しました");
	$rowdata = $useraccount->fetch();
	if (! $rowdata) {
	    return (99);
	}
	
	$userclass = $rowdata[1];

	$rowdata = $useraccount->fetch();
	if ($rowdata) {
	    return (99);
	}

	return ($userclass);
	
    } else {
        return (0);//環境ポリシー使わないときはつねに特権モード
    }//end if
}//end function getuserclass



function getmymemberid($con) {
    global $useenvironmentpolicy;
    $username = $_SERVER['PHP_AUTH_USER'];

    if ($useenvironmentpolicy == 1) {
        $query = "
SELECT memberid ,userclass,name,passwd1 
FROM foltia_envpolicy 
WHERE foltia_envpolicy.name  = '$username'  
	";
	$useraccount = m_query($con, $query, "DBクエリに失敗しました");
	$rowdata = $useraccount->fetch();
	if (! $rowdata) {
            return (-1);//エラー
	}

	$memberid = $rowdata[0];

	$rowdata = $useraccount->fetch();
	if ($rowdata) {
	    return (-1);
	}

	return ($memberid);
	
    } else {
        return (0);//環境ポリシー使わないときはつねに特権モード
    }//end if
}//end function getuserclass


function getmemberid2name($con,$memberid) {
    global $useenvironmentpolicy;
    //$username = $_SERVER['PHP_AUTH_USER'];

    if ($useenvironmentpolicy == 1) {
        $query = "
SELECT memberid ,userclass,name,passwd1 
FROM foltia_envpolicy 
WHERE foltia_envpolicy.memberid  = '$memberid'  
	";
	$useraccount = m_query($con, $query, "DBクエリに失敗しました");
	$rowdata = $useraccount->fetch();
	if (! $rowdata) {
            return ("");//エラー
	}
	
	$name = $rowdata[2];

	$rowdata = $useraccount->fetch();
	if ($rowdata) {
            return ("");
	}

	return ($name);

    } else {
	return ("");
    }//end if

}//end function getmemberid2name



function number_page($p,$lim) {
    //Autopager・ページリンクで使用している関数
    //下記は関数をしているファイル名
    //index.php  showplaylist.php  titlelist.php  showlib.php  showlibc.php
    ///////////////////////////////////////////////////////////////////////////
    // ページ数の計算関係
    // 第１引数 : $p       : 現在のページ数
    // 第２引数 : $lim     : １ページあたりに表示するレコード数
    ///////////////////////////////////////////////////////////////////////////

    if($p == 0) {
	$p2 = 2;        //$p2の初期値設定
    } else {
	$p2 = $p;       //次のページ数の値を$p2に代入する
	$p2++;
    }

    if($p < 1) {
	$p = 1;
    }
    //表示するページの値を取得
    $st = ($p -1) * $lim;

    //
    return array($st,$p,$p2);
}//end number_page


function page_display($query_st,$p,$p2,$lim,$dtcnt,$mode) {
    //Autopager・ページリンクで使用している関数
    //下記は関数を使用しているファイル名
    //index.php　showplaylist.php　titlelist.php　showlib.php　showlibc.php
    /////////////////////////////////////////////////////////////////////////////
    // Autopager処理とページのリンクの表示
    // 第１引数 ： $query_st        : クエリの値
    // 第２引数 ： $p            : 現在のページ数の値
    // 第３引数 ： $p2           : 次のページ数の値
    // 第４引数 ： $lim          : 1ページあたりに表示するレコード数
    // 第５引数 ： $dtcnt        : レコードの総数
    // 第６引数 ： $mode         :【新番組】mode=newのときにリンクページを表示させないフラグ(index.phpのみで使用)
    ////////////////////////////////////////////////////////////////////////////
    if($query_st == "") {
        //ページ総数取得
        $page = ceil($dtcnt / $lim);
	//$modeのif文は【新番組】の画面のみで使用
	if($mode == '') {
	    echo "$p/$page";         //  現在のページ数/ページ総数
	}
        //ページのリンク表示
        for($i=1;$i <= $page; $i++) {
            print("<a href=\"".$_SERVER["PHP_SELF"]."?p=$i\" > $i </a>");
        }
        //Autopageingの処理
        if($page >= $p2 ) {
            print("<a rel=next href=\"".$_SERVER["PHP_SELF"]."?p=$p2\" > </a>");
        }
    } else {      //query_stに値が入っていれば
	$query_st = $_SERVER['QUERY_STRING'];
        $page = ceil($dtcnt / $lim);
        echo "$p/$page";
        //ページのリンク表示
        for($i=1;$i <= $page; $i++) {
	    $query_st =  preg_replace('/p=[0-9]+&/','',$query_st);    //p=0〜9&を空欄にする正規表現
            print("<a href=\"".$_SERVER["PHP_SELF"]."?p=$i&$query_st\" > $i </a>");
        }
        //Autopageingの処理
        if($page >= $p2 ) {
	    $query_st =  preg_replace('/p=[0-9]+&/','',$query_st);
            print("<a rel=next href=\"".$_SERVER["PHP_SELF"]."?p=$p2&$query_st\" > </a>");
	}
    }
    return array($p2,$page);
}// end page_display

function getnextstationid($con) {
    //stationidの最大値を取得して+1する。
    $query2 = "SELECT max(stationid) FROM  foltia_station";
    $rs2 = sql_query($con, $query2, "DBクエリに失敗しました");
    $rowdata2 = $rs2->fetch();
    if (! $rowdata2) {      //レコードにデータが無い時、$id =1
	$sid = 1 ;
    } else {                  //stationidの最大値を$idに入れて、+1する。
	$sid = $rowdata2[0];
	$sid ++ ;
    }
    return ($sid);
}//end getnextstationid

?>
