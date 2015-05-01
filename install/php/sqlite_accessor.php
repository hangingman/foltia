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

?>
