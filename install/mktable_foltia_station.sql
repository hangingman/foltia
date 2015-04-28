--
-- 暫定テーブル
--
drop table if exists foltia_station_temp;
CREATE TABLE foltia_station_temp (
    stationid integer  PRIMARY KEY,
    stationname text,
    stationrecch integer,
    stationcallsign text,
    stationuri text,
    tunertype text,
    tunerch text,
    device text,
    ontvcode text,
    digitalch integer,
    digitalstationband integer
);

INSERT INTO foltia_station_temp VALUES (0, '[全局]', 0, '', '', '', '', '', '',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (1, 'NHK総合', 1, 'NHK', '', NULL, NULL, NULL, '0031.ontvjapan.com',27,0);
INSERT INTO foltia_station_temp VALUES (3, 'NHK教育', 3, 'ETV', '', NULL, NULL, NULL, '0041.ontvjapan.com',26,0);
INSERT INTO foltia_station_temp VALUES (4, '日本テレビ', 4, 'NTV', '', NULL, NULL, NULL, '0004.ontvjapan.com',25,0);
INSERT INTO foltia_station_temp VALUES (6, 'TBS', 6, 'TBS', '', NULL, NULL, NULL, '0005.ontvjapan.com',22,0);
INSERT INTO foltia_station_temp VALUES (8, 'フジテレビ', 8, 'CX', '', NULL, NULL, NULL, '0006.ontvjapan.com',21,0);
INSERT INTO foltia_station_temp VALUES (10, 'テレビ朝日', 10, 'EX', '', NULL, NULL, NULL, '0007.ontvjapan.com',24,0);
INSERT INTO foltia_station_temp VALUES (12, 'テレビ東京', 12, 'TX', '', NULL, NULL, NULL, '0008.ontvjapan.com',23,0);
INSERT INTO foltia_station_temp VALUES (42, 'tvk', 42, 'TVK', '', '', '', '', '0009.ontvjapan.com',18,0);
INSERT INTO foltia_station_temp VALUES (14, 'TOKYO MX', 14, 'MX', '', '', '', '', '0016.ontvjapan.com',20,0);
INSERT INTO foltia_station_temp VALUES (46, 'チバテレビ', -10, 'CTC', '', NULL, NULL, NULL, NULL,30,0);
INSERT INTO foltia_station_temp VALUES (38, 'テレ玉', -10, 'TVS', '', NULL, NULL, NULL, NULL,32,0);
INSERT INTO foltia_station_temp VALUES (418, '放送大学', -10, 'UAIR', '', NULL, NULL, NULL, '0013.ontvjapan.com',28,0);
INSERT INTO foltia_station_temp VALUES (101, 'NHK-BS1', 0, 'BS7', '', '', '101', '', '3001.ontvjapan.com',101,1);
INSERT INTO foltia_station_temp VALUES (102, 'NHK-BS2', 0, 'BS11', '', '', '102', '', '3002.ontvjapan.com',102,1);
INSERT INTO foltia_station_temp VALUES (103, 'NHK-BShi', 0, '', '', '', '103', '', '3003.ontvjapan.com',103,1);
INSERT INTO foltia_station_temp VALUES (409, 'BS日テレ', 0, '', '', '', '141', '', '3004.ontvjapan.com',141,1);
INSERT INTO foltia_station_temp VALUES (384, 'BS朝日', 0, '', '', '', '151', '', '3005.ontvjapan.com',151,1);
INSERT INTO foltia_station_temp VALUES (161, 'BS-TBS', 0, '', '', '', '161', '', '3006.ontvjapan.com',161,1);
INSERT INTO foltia_station_temp VALUES (389, 'BS Japan', 0, '', '', '', '171', '','3007.ontvjapan.com',171,1);
INSERT INTO foltia_station_temp VALUES (381, 'BSフジ', 0, '', '', '', '181', '', '3008.ontvjapan.com',181,1);
INSERT INTO foltia_station_temp VALUES (191, 'WOWOW', 0, 'BS5', '', '', '191', '', '3009.ontvjapan.com',191,1);
INSERT INTO foltia_station_temp VALUES (420, 'WOWOW2', 0, '', '', '', '192', '', '3010.ontvjapan.com',192,1);
INSERT INTO foltia_station_temp VALUES (421, 'WOWOW3', 0, '', '', '', '193', '', '3011.ontvjapan.com',193,1);
INSERT INTO foltia_station_temp VALUES (468, 'BS11デジタル', 0, '', '', '', '211', '', '3013.ontvjapan.com',211,1);
INSERT INTO foltia_station_temp VALUES (469, 'Twellv', 0, '', '', '', '222', '', '3014.ontvjapan.com',222,1); 
INSERT INTO foltia_station_temp VALUES (408, 'ファミリー劇場', 0, '', '', '', '751', '', '1015.ontvjapan.com',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (397, 'カートゥーンネットワーク', 0, '', '', '', '780', '', '1046.ontvjapan.com',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (263, 'アニマックス', 0, '', '', '', '782', '', '1047.ontvjapan.com',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (261, 'キッズステーション', 0, '', '', 'c', '335', '', '1045.ontvjapan.com',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (449, 'ディスカバリーチャンネル', 0, '', '', '', '796', '', '1062.ontvjapan.com',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (448, 'MONDO21', 0, '', '', '', '722', '', '1049.ontvjapan.com',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (401, 'チャンネルNECO', 0, '', '', '', '750', '', '1008.ontvjapan.com',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (455, '330ch WOWOW', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (480, 'TBSラジオ(954)', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (477, '北海道文化放送', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (463, 'gooアニメ', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (461, '@nifty', -10, '', '', '', '', '', '',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (466, 'NHKラジオ第一', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (474, '日テレプラス', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (476, 'ニコニコアニメチャンネル', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (451, 'RKB毎日放送', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (452, '北海道放送', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (454, 'テレビ和歌山', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (456, '静岡放送', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (457, 'i-revo', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (459, '東北放送', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (464, 'テレビ山口', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (478, '札幌テレビ放送', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (395, 'カミングスーンTV', -10, '', '', '', '', '', '',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (443, 'スーパーチャンネル', -10, '', '', '', '', '', '',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (385, 'TBSチャンネル', 0, '', '', '', '765', '', '',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (462, '瀬戸内海放送', -10, '', '', '', '', '', '',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (473, 'フジCSHD', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (475, 'スカパー181ch', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (479, '北海道テレビ放送', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (450, 'NHK教育3', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (453, 'バンダイチャンネルキッズ', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (458, '日テレプラス＆サイエンス', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (460, 'ビクトリーチャンネル', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (472, 'ytv', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (422, '東海テレビ', -10, '', '', '', '', '', '',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (423, 'ShowTime', -10, '', '', '', '', '', '',NULL,NULL);
INSERT INTO foltia_station_temp VALUES (424, 'メ〜テレ', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (425, '三重テレビ', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (426, '中京テレビ', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (293, 'AT-X', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (295, 'フジ721', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (380, 'スカパー180ch', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (427, '岐阜放送', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (428, 'テレビ新広島', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (383, 'サンテレビ', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (429, 'CBCテレビ', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (386, 'テレ朝チャンネル', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (390, 'サンテレビジョン', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (392, 'スカパー183ch', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (394, 'スカパー160ch', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (396, 'KBS京都', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (398, 'テレビ大阪', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (399, 'ABCテレビ', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (400, 'なし', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (402, '関西テレビ', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (406, 'MBS毎日放送', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (407, 'animate.tv', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (410, 'テレビ愛知', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (411, 'インターネット', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (413, 'よみうりテレビ', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (414, 'LFX488', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (415, 'LFX', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (416, 'LFX BB', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (419, 'GyaO', -10, '', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (417, 'とちぎテレビ', -10, 'TTV', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (412, '群馬テレビ', -10, 'GTV', '', NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (430, '奈良テレビ', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (431, 'TVQ九州放送', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (432, 'テレビ北海道', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (433, 'BIGLOBEストリーム', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (434, 'テレビせとうち', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (435, '中国放送', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (436, '文化放送(1134)', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (437, '広島ホームテレビ', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (438, '広島テレビ', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (439, '岡山放送', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (440, '山陽放送', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (441, 'びわ湖放送', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (442, 'NHK-FM', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (444, 'バンダイチャンネル', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (445, 'フレッツ・スクウェア（NTT東日本）', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (446, 'フジ739', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
INSERT INTO foltia_station_temp VALUES (447, 'Yahoo!動画', -10, NULL, NULL, NULL, NULL, NULL, NULL,NULL,NULL);
