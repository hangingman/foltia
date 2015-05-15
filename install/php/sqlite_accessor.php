<?php
/*
  Anime recording system foltia
  http://www.dcc-jpl.com/soft/foltia/

  settings.php

  目的
  SQLへのアクセスを行うコードをユーリーティ関数として使用する

  引数
  DBへのコネクション

  DCC-JPL Japan/foltia project

*/

// 放送局一覧情報を取得する
function get_foltia_station_data($con) {

    $station_array = array();
    $query = "SELECT stationid, stationname, stationrecch FROM foltia_station_temp";
    $rs = sql_query($con, $query, "DBクエリに失敗しました");

    $row = $rs->fetch();
    if ($row) {
	do {
	    //$val = var_dump($row);
	    $stationid = $row['stationid'];
	    $stationname = $row['stationname'];
	    $stationrecch = $row['stationrecch'];

	    array_push($station_array, $row);
	} while ($row = $rs->fetch());
    }

    return $station_array;
}

// 使用されている放送局のmap情報を取得する
function get_used_foltia_station_map($con) {

    $used_station_map = array();
    $query = "SELECT stationid, stationrecch FROM foltia_station";
    $rs = sql_query($con, $query, "DBクエリに失敗しました");

    $row = $rs->fetch();
    if ($row) {
	do {
	    $stationid = $row['stationid'];
	    $stationrecch = $row['stationrecch'];
	    $used_station_map[$stationid] = $stationrecch;
	} while ($row = $rs->fetch());
    }

    return $used_station_map;
}

// 指定された放送局情報をfoltia_stationのテーブルに突っ込む
function set_foltia_station_recch($con, $post_map) {

    //$query = "DELETE FROM foltia_station WHERE stationid = {$post_map['stationid']}";
    //sql_query($con, $query, "DBクエリに失敗しました");

    $query = <<<EOF
INSERT INTO foltia_station (
    stationid,
    stationname, 
    stationrecch,
    stationcallsign,
    stationuri,  
    tunertype,   
    tunerch,     
    device,	     
    ontvcode,    
    digitalch,
    digitalstationband
) SELECT 
    stationid,
    stationname,
    '{$post_map['stationrecch']}' as stationrecch,
    stationcallsign,
    stationuri,  
    tunertype,   
    tunerch,     
    device,	     
    ontvcode,    
    digitalch,
    digitalstationband
FROM foltia_station_temp WHERE stationid = '{$post_map['stationid']}'

EOF
	   ;
    logging($query);
    $rs = sql_query($con, $query, "DBクエリに失敗しました");
}

// 指定された放送局情報をデータベースから削除する
function delete_foltia_station_recch($con, $delete_map) {

    $query = <<<EOF
DELETE FROM foltia_station WHERE stationid = '{$delete_map['stationid']}'
EOF
	   ;
    logging($query);
    $rs = sql_query($con, $query, "DBクエリに失敗しました");
}

// 録画番組検索
function get_reserved_rs_tid($con, $now) {

    $query = <<<EOF
SELECT
    foltia_program.tid, stationname, foltia_program.title,
    foltia_subtitle.countno, foltia_subtitle.subtitle,
    foltia_subtitle.startdatetime as x, foltia_subtitle.lengthmin,
    foltia_tvrecord.bitrate, foltia_subtitle.pid
FROM foltia_subtitle , foltia_program ,foltia_station ,foltia_tvrecord
WHERE foltia_tvrecord.tid = foltia_program.tid
    AND foltia_tvrecord.stationid = foltia_station.stationid
    AND foltia_program.tid = foltia_subtitle.tid 
    AND foltia_station.stationid = foltia_subtitle.stationid
    AND foltia_subtitle.enddatetime >= ? 
UNION
SELECT
    foltia_program.tid, stationname, foltia_program.title,
    foltia_subtitle.countno, foltia_subtitle.subtitle,
    foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
    foltia_tvrecord.bitrate, foltia_subtitle.pid
FROM foltia_tvrecord

LEFT OUTER JOIN foltia_subtitle on (foltia_tvrecord.tid = foltia_subtitle.tid )
LEFT OUTER JOIN foltia_program on (foltia_tvrecord.tid = foltia_program.tid )
LEFT OUTER JOIN foltia_station on (foltia_subtitle.stationid = foltia_station.stationid )
WHERE foltia_tvrecord.stationid = 0
    AND foltia_subtitle.enddatetime >= ? ORDER BY x ASC

EOF
	   ;

    $reservedrs = sql_query($con, $query, "DBクエリに失敗しました",array($now,$now));
    $rowdata = $reservedrs->fetch();
    if ($rowdata) {
        do {
	    $reservedpid[] = $rowdata[8];
	} while ($rowdata = $reservedrs->fetch());
    } else {
	$reservedpid = array();
    }//end if

    return $reservedpid;
}

// 同一番組他局検索
function get_reserved_rs_same_tid($con) {

    $query = <<<EOF
SELECT
    foltia_program.tid,
    foltia_program.title,
    foltia_subtitle.countno,
    foltia_subtitle.subtitle,
    foltia_subtitle.startdatetime ,
    foltia_subtitle.lengthmin ,
    foltia_tvrecord.bitrate ,
    foltia_subtitle.pid  
FROM foltia_subtitle, foltia_program, foltia_tvrecord
WHERE foltia_tvrecord.tid = foltia_program.tid 
    AND foltia_program.tid = foltia_subtitle.tid 
    AND foltia_subtitle.enddatetime >= ? 
ORDER BY startdatetime ASC 
LIMIT 1000
EOF
	   ;

    $reservedrssametid = sql_query($con, $query, "DBクエリに失敗しました",array($now));
    $rowdata = $reservedrssametid->fetch();
    if ($rowdata) {
	do {
	    $reservedpidsametid[] = $rowdata[7];
	} while ($rowdata = $reservedrssametid->fetch());
	
	$rowdata = "";
    } else {
	$reservedpidsametid = array();
    }//end if
    $reservedrssametid->closeCursor();

    return $reservedpidsametid;
}

// 新番組表示モード用クエリ取得
function get_query_for_new_program($con) {

    $query = <<<EOF
SELECT 
    foltia_program.tid, stationname, foltia_program.title,
    foltia_subtitle.countno, foltia_subtitle.subtitle,
    foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
    foltia_subtitle.pid, foltia_subtitle.startoffset
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid
    AND foltia_station.stationid = foltia_subtitle.stationid 
    AND foltia_subtitle.enddatetime >= ?  
    AND foltia_subtitle.countno = '1' 
ORDER BY foltia_subtitle.startdatetime ASC 
LIMIT 1000

EOF
	   ;

    return $query;
}

// 通常の番組表示用クエリ取得
function get_query_for_program($con, $lim, $st) {

    $query = <<<EOF
SELECT 
    foltia_program.tid, stationname, foltia_program.title,
    foltia_subtitle.countno, foltia_subtitle.subtitle,
    foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
    foltia_subtitle.pid, foltia_subtitle.startoffset
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid 
    AND foltia_station.stationid = foltia_subtitle.stationid 
    AND foltia_subtitle.enddatetime >= ?  
ORDER BY foltia_subtitle.startdatetime  ASC 
LIMIT {$lim} OFFSET {$st}

EOF
	   ;

    return $query;
}

// レコード総数取得
function get_all_record_or_die($con, $now) {

    $query = <<<EOF
SELECT
    COUNT(*) AS cnt 
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid 
    AND foltia_station.stationid = foltia_subtitle.stationid 
    AND foltia_subtitle.enddatetime >= ?  
LIMIT 1000 

EOF
	   ;

    $rs = sql_query($con, $query, "DBクエリに失敗しました",array($now));
    $rowdata = $rs->fetch();
    $dtcnt = htmlspecialchars($rowdata[0]);

    if (! $rowdata) {
	die_exit("番組データがありません<BR>");
    }//endif

    return $dtcnt;
}

// タイトルリスト取得
function get_all_titlelist_or_die($con, $lim, $st) {

    $query = <<<EOF
SELECT 
    foltia_program.tid,
    foltia_program.title 
FROM foltia_program 
ORDER BY foltia_program.tid DESC
LIMIT {$lim} OFFSET {$st}
EOF
	   ;

    $rs = sql_query($con, $query, "DBクエリに失敗しました");
    $rowdata = $rs->fetch();
    if (! $rowdata) {
	die_exit("番組データがありません<BR>");
    }

    /* フィールド数 */
    $maxcols = $rs->columnCount();

    return array($rowdata, $maxcols, $rs);
}

// タイトル総数取得
function get_all_title_count_or_die($con) {

    $query = "SELECT COUNT(*) AS cnt FROM foltia_program";
    $rs = sql_query($con, $query, "DBクエリに失敗しました");
    $rowdata = $rs->fetch();
    if (! $rowdata) {
	die_exit("番組データがありません<BR>");
    }
    //行数取得
    $dtcnt =  $rowdata[0];
    return $dtcnt;
}

// 予約一覧用ResultSetの取得
function get_all_list_reserve($con, $now) {

    $query = <<<EOF
SELECT
    foltia_program.tid, stationname, foltia_program.title,
    foltia_subtitle.countno, foltia_subtitle.subtitle,
    foltia_subtitle.startdatetime as x, foltia_subtitle.lengthmin,
    foltia_tvrecord.bitrate, foltia_subtitle.startoffset,
    foltia_subtitle.pid, foltia_subtitle.epgaddedby,
    foltia_tvrecord.digital 
FROM foltia_subtitle , foltia_program ,foltia_station ,foltia_tvrecord
WHERE foltia_tvrecord.tid = foltia_program.tid
    AND foltia_tvrecord.stationid = foltia_station.stationid
    AND foltia_program.tid = foltia_subtitle.tid
    AND foltia_station.stationid = foltia_subtitle.stationid
    AND foltia_subtitle.enddatetime >= ? 
UNION
SELECT
    foltia_program.tid, stationname, foltia_program.title,
    foltia_subtitle.countno, foltia_subtitle.subtitle,
    foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
    foltia_tvrecord.bitrate,  foltia_subtitle.startoffset,
    foltia_subtitle.pid,  foltia_subtitle.epgaddedby,
    foltia_tvrecord.digital 
FROM foltia_tvrecord
LEFT OUTER JOIN foltia_subtitle on (foltia_tvrecord.tid = foltia_subtitle.tid )
LEFT OUTER JOIN foltia_program on (foltia_tvrecord.tid = foltia_program.tid )
LEFT OUTER JOIN foltia_station on (foltia_subtitle.stationid = foltia_station.stationid )
WHERE foltia_tvrecord.stationid = 0 
    AND foltia_subtitle.enddatetime >= ? ORDER BY x ASC

EOF
	   ;

// WHERE foltia_tvrecord.stationid = 0 
//     AND foltia_subtitle.enddatetime >= ? ORDER BY x ASC

    $rs = sql_query($con, $query, "DBクエリに失敗しました",array($now,$now));

    return $rs;
}

// 重複しているオンボードチューナー録画のリストを取得する
function get_overlap_recording($con, $rowdata, $endtime) {

    //番組の開始時刻より遅い時刻に終了し、終了時刻より前にはじまる番組があるかどうか
    $query = <<<EOF
SELECT
    foltia_program.tid, stationname, foltia_program.title,
    foltia_subtitle.countno, foltia_subtitle.subtitle,
    foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
    foltia_tvrecord.bitrate, foltia_subtitle.startoffset,
    foltia_subtitle.pid, foltia_tvrecord.digital
FROM foltia_subtitle , foltia_program ,foltia_station ,foltia_tvrecord
WHERE foltia_tvrecord.tid = foltia_program.tid
    AND foltia_tvrecord.stationid = foltia_station.stationid
    AND foltia_program.tid = foltia_subtitle.tid
    AND foltia_station.stationid = foltia_subtitle.stationid
    AND foltia_subtitle.enddatetime > ? 
    AND foltia_subtitle.startdatetime < ?  
UNION
SELECT
    foltia_program.tid, stationname, foltia_program.title,
    foltia_subtitle.countno, foltia_subtitle.subtitle,
    foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
    foltia_tvrecord.bitrate, foltia_subtitle.startoffset,
    foltia_subtitle.pid, foltia_tvrecord.digital
FROM foltia_tvrecord
LEFT OUTER JOIN foltia_subtitle on (foltia_tvrecord.tid = foltia_subtitle.tid )
LEFT OUTER JOIN foltia_program on (foltia_tvrecord.tid = foltia_program.tid )
LEFT OUTER JOIN foltia_station on (foltia_subtitle.stationid = foltia_station.stationid )
WHERE foltia_tvrecord.stationid = 0
    AND foltia_subtitle.enddatetime > ?	 
    AND foltia_subtitle.startdatetime < ?  
EOF
	   ;

    return sql_query($con, $query, "DBクエリに失敗しました",array($rowdata[5],$endtime,$rowdata[5],$endtime));
}

// 重複している外部チューナー録画のリストを取得する
function get_eoverlap_recording($con, $rowdata, $endtime) {

    $query = <<<EOF
SELECT
    foltia_program.tid, stationname, foltia_program.title,
    foltia_subtitle.countno, foltia_subtitle.subtitle,
    foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
    foltia_tvrecord.bitrate, foltia_subtitle.startoffset,
    foltia_subtitle.pid, foltia_tvrecord.digital
FROM foltia_subtitle , foltia_program ,foltia_station ,foltia_tvrecord
WHERE foltia_tvrecord.tid = foltia_program.tid
    AND foltia_tvrecord.stationid = foltia_station.stationid 
    AND foltia_program.tid = foltia_subtitle.tid 
    AND foltia_station.stationid = foltia_subtitle.stationid
    AND foltia_subtitle.enddatetime > ? 
    AND foltia_subtitle.startdatetime < ?  
    AND  (foltia_station.stationrecch = '0' OR  foltia_station.stationrecch = '-1' ) 
UNION
SELECT
    foltia_program.tid, stationname, foltia_program.title,
    foltia_subtitle.countno, foltia_subtitle.subtitle,
    foltia_subtitle.startdatetime, foltia_subtitle.lengthmin,
    foltia_tvrecord.bitrate, foltia_subtitle.startoffset,
    foltia_subtitle.pid, foltia_tvrecord.digital
FROM foltia_tvrecord
LEFT OUTER JOIN foltia_subtitle on (foltia_tvrecord.tid = foltia_subtitle.tid )
LEFT OUTER JOIN foltia_program on (foltia_tvrecord.tid = foltia_program.tid )
LEFT OUTER JOIN foltia_station on (foltia_subtitle.stationid = foltia_station.stationid )
WHERE foltia_tvrecord.stationid = 0 
    AND foltia_subtitle.enddatetime > ?	 
    AND foltia_subtitle.startdatetime < ?  
    AND  (foltia_station.stationrecch = '0' OR  foltia_station.stationrecch = '-1' ) 

EOF
	   ;
    return sql_query($con, $query, "DBクエリに失敗しました", array($rowdata[5], $endtime, $rowdata[5], $endtime));
}

// 録画中のtitldのidがあることをチェックする
function set_maxcols_for_update($con, $maxcols) {

    $query = <<<EOF
SELECT 
    foltia_program.tid, stationname, foltia_program.title,
    foltia_tvrecord.bitrate, foltia_tvrecord.stationid, 
    foltia_tvrecord.digital   
FROM  foltia_tvrecord , foltia_program , foltia_station 
WHERE foltia_tvrecord.tid = foltia_program.tid
    AND foltia_tvrecord.stationid = foltia_station.stationid 
ORDER BY foltia_program.tid DESC
EOF
	   ;

    $rs = sql_query($con, $query, "DBクエリに失敗しました");
    $rowdata = $rs->fetch();
    
    if (! $rowdata) {
	//なければなにもしない
    } else {
	// あれば更新
	$maxcols = $rs->columnCount();
    }
}

// 録画候補局検索
function get_record_candidate($con, $tid) {

    $query = <<<EOF
SELECT distinct  foltia_station.stationid , stationname , foltia_station.stationrecch 
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid AND foltia_station.stationid = foltia_subtitle.stationid 
    AND foltia_program.tid = ? 
ORDER BY stationrecch DESC
EOF
	   ;

    $rs = sql_query($con, $query, "DBクエリに失敗しました",array($tid));
    return $rs->fetch();
}

// 今後の放送予定取得
function get_plan_of_program($con, $now, $tid) {

    $query = <<<EOF
SELECT 
    stationname,
    foltia_subtitle.countno,
    foltia_subtitle.subtitle,
    foltia_subtitle.startdatetime ,
    foltia_subtitle.lengthmin ,
    foltia_subtitle.startoffset 
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid
    AND foltia_station.stationid = foltia_subtitle.stationid 
    AND foltia_subtitle.startdatetime >= ?
    AND foltia_program.tid = ? 
ORDER BY foltia_subtitle.startdatetime ASC
EOF
	   ;

    $rs = sql_query($con, $query, "DBクエリに失敗しました",array($now,$tid));
    $rowdata = $rs->fetch();

    return array($rs, $rowdata);
}

// 今後の放送予定取得
function get_schedule_of_reserve($con, $now, $station, $tid) {

    if ($station != 0) {
	//局限定
	$query = <<<EOF
SELECT 
    foltia_subtitle.pid ,  
    stationname,
    foltia_subtitle.countno,
    foltia_subtitle.subtitle,
    foltia_subtitle.startdatetime ,
    foltia_subtitle.lengthmin ,
    foltia_subtitle.startoffset 
FROM foltia_subtitle , foltia_program ,foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid
    AND foltia_station.stationid = foltia_subtitle.stationid 
    AND foltia_station.stationid = ?
    AND foltia_subtitle.startdatetime >= ?
    AND foltia_program.tid = ?
ORDER BY foltia_subtitle.startdatetime  ASC

EOF
;

	$rs = sql_query($con, $query, "DBクエリに失敗しました",array($station, $now, $tid));
	return array($rs->fetch(), $rs);

    } else {
	//全局
	$query = <<<EOF
SELECT 
    foltia_subtitle.pid ,  
    stationname,
    foltia_subtitle.countno,
    foltia_subtitle.subtitle,
    foltia_subtitle.startdatetime ,
    foltia_subtitle.lengthmin ,
    foltia_subtitle.startoffset 
FROM foltia_subtitle , foltia_program , foltia_station  
WHERE foltia_program.tid = foltia_subtitle.tid
    AND foltia_station.stationid = foltia_subtitle.stationid 
    AND foltia_subtitle.startdatetime >= ?
    AND foltia_program.tid = ?
ORDER BY foltia_subtitle.startdatetime  ASC

EOF
;

	$rs = sql_query($con, $query, "DBクエリに失敗しました",array($now, $tid));
	return array($rs->fetch(), $rs);
    }
}

// tidからタイトルを取得する
function get_title_with_tid($con, $tid) {

    $title = "(未登録)";

    $query = "SELECT title FROM foltia_program WHERE tid = ? ";
    $rs = sql_query($con, $query, "DBクエリに失敗しました",array($tid));
    $rowdata = $rs->fetch();

    if ($rowdata) {
	$title = htmlspecialchars($rowdata[0]);
    }

    return $title;
}

// 録画のキューを入れる
function set_queue_from_php($con, $demomode, $station, $tid, $bitrate, $usedigital) {

    include("./foltia_config2.php");

    if ($demomode) {
    } else {
	//foltia_tvrecord　書き込み
	//既存が予約あって、新着が全局予約だったら
	if ($station ==0) {
	    //既存局を消す
	    $query = "DELETE FROM foltia_tvrecord WHERE tid = ? ";
	    $rs = sql_query($con, $query, "DBクエリに失敗しました",array($tid));
	}//endif

	$query = "SELECT count(*) FROM foltia_tvrecord WHERE tid = ?  AND stationid = ? ";
	$rs = sql_query($con, $query, "DBクエリに失敗しました",array($tid,$station));
	$maxrows = $rs->fetchColumn(0);

	if ($maxrows == 0) { //新規追加
	    $query = "INSERT INTO  foltia_tvrecord  values (?,?,?,?)";
	    $rs = sql_query($con, $query, "DB書き込みに失敗しました",array($tid,$station,$bitrate,$usedigital));
	} else {//修正　(ビットレート)
	    $query = "UPDATE foltia_tvrecord SET bitrate = ? , digital = ? WHERE tid = ? AND stationid = ? ";
	    $rs = sql_query($con, $query, "DB書き込みに失敗しました",array( $bitrate, $usedigital , $tid , $station ));
	}
	
	//キュー入れプログラムをキック
	//引数　TID チャンネルID
	logging("{$toolpath}/perl/addatq.pl {$tid} {$station}");
	$oserr = system("{$toolpath}/perl/addatq.pl $tid $station");
    }//end if demomode
}

?>
