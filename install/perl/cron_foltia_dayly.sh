#!/bin/sh
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
# 定期実行ジョブ記述ファイル。
#cronで1日1回程度実行するとよいでしょう。
#
# DCC-JPL Japan/foltia project
#

#デジタル放送から一週間分のEPGを取得
/home/foltia/perl/epgimport.pl long

# XMLTVをつかってEPG番組表インポート(アナログ専用旧仕様)
#
#/usr/bin/perl  /usr/bin/tv_grab_jp | /home/foltia/perl/xmltv2foltia.pl
# 2つの局設定使うような場合
#/usr/bin/perl  /usr/bin/tv_grab_jp --config-file ~/.xmltv/tv_grab_jp.conf.jcom  | /home/foltia/perl/xmltv2foltia.pl
#/usr/bin/perl  /usr/bin/tv_grab_jp --config-file ~/.xmltv/tv_grab_jp.conf.tvk  | /home/foltia/perl/xmltv2foltia.pl

#録画ファイルとテーブルの整合性を更新
/home/foltia/perl/updatem2pfiletable.pl

#2週間先のスケジュールを取得
/home/foltia/perl/getxml2db.pl long

