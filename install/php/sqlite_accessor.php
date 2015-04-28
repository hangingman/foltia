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


?>
