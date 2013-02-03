#!/bin/sh
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
# Copyright (C) 2013 DCC-JPL Japan/foltia project

# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation; either version 2
# of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

# 
# cron_foltia_daily.sh
#
# 定期実行ジョブ記述ファイル。
# cronで1日1回程度実行するとよいでしょう。
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

