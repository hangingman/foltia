#!/usr/bin/perl
#
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
#
# チャンネルスキャン
# 初期インストール時に受信可能局をスキャンします
#
# DCC-JPL Japan/foltia project
#

my $recpt1path = "/home/foltia/perl/tool/recpt1"; #ほかのキャプチャデバイス作ってる人はココを変更
my $epgdumppath = "/home/foltia/perl/tool"; #epgdumpのあるディレクトリ
my $recfolderpath = "/home/foltia/php/tv";#tsを出力するディレクトリ
my $xmloutpath = "/tmp";
my $channel = 13 ; #地デジチャンネルは13-62
my $oserr = "";
my $line = "";

print "Initialize\n";
print "Tool path are\n";
print "REC:$recpt1path\n";
print "EPGDUMP:$epgdumppath/epgdump\n";
print "TS OUT:$recfolderpath/\n";
print "XML OUT:$xmloutpath/\n";

#ツールがあるか確認
unless (-e "$recpt1path"){
	print "Please install $recpt1path.\n";
	exit 1;
}
unless (-e "$epgdumppath/epgdump"){
	print "Please install $epgdumppath/epgdump.\n";
	exit 1;
}
unless (-e "$recfolderpath"){
	print "Please make directory $recfolderpath.\n";
	exit 1;
}
unless (-e "$xmloutpath"){
	print "Please make directory $xmloutpath.\n";
	exit 1;
}


#地デジスキャンループ
for ($channel = 13; $channel <= 62 ; $channel++){
	print "\nChannel: $channel\n";
	$oserr = `$recpt1path $channel 4 $recfolderpath/__$channel.m2t`;
	$oserr = `$epgdumppath/epgdump $channel $recfolderpath/__$channel.m2t $xmloutpath/__$channel-epg.xml`;

	if (-s "$xmloutpath/__$channel-epg.xml" ){
		print "\t\t This channel can view :  $channel \n";
		open(XML, "< $xmloutpath/__$channel-epg.xml");
		while ( $line = <XML>) {
			#Jcode::convert(\$line,'euc','utf8');
			if($line =~ /<display-name/){
				$line =~ s/<.*?>//g;
				#Jcode::convert(\$line,'utf8','euc');
				print "\t\t $channel $line\n";
			}#end if
		}#end while
		close(XML);
	}else{
		print "\t\t Not Available :  $channel \n";
	}#end if 
}#end for


#BSデジタル
$channel = 211;
	print "\nBS Digital Scan\n";
	$oserr = `$recpt1path $channel 4 $recfolderpath/__$channel.m2t`;
	$oserr = `$epgdumppath/epgdump /BS $recfolderpath/__$channel.m2t $xmloutpath/__$channel-epg.xml`;

	if (-s "$xmloutpath/__$channel-epg.xml" ){
		print "\t\t BS Digital can view :   \n";
		open(XML, "< $xmloutpath/__$channel-epg.xml");
		while ( $line = <XML>) {
			#Jcode::convert(\$line,'euc','utf8');
			if($line =~ /<display-name/){
				$line =~ s/<.*?>//g;
				#Jcode::convert(\$line,'utf8','euc');
				print "\t\t $line\n";
			}#end if
		}#end while
		close(XML);
	}else{
		print "\t\t Not Available :  BS Digital \n";
	}#end if 


#  <channel id="3001.ontvjapan.com">
#    <display-name lang="ja_JP">NHK BS1</display-name>
#  </channel>
#  <channel id="3002.ontvjapan.com">
#    <display-name lang="ja_JP">NHK BS2</display-name>
#  </channel>
#  <channel id="3003.ontvjapan.com">
#    <display-name lang="ja_JP">NHK BSh</display-name>
#  </channel>
#  <channel id="3004.ontvjapan.com">
#    <display-name lang="ja_JP">BS日テレ</display-name>
#  </channel>
#  <channel id="3005.ontvjapan.com">
#    <display-name lang="ja_JP">BS朝日</display-name>
#  </channel>
#  <channel id="3006.ontvjapan.com">
#    <display-name lang="ja_JP">BS-TBS</display-name>
#  </channel>
#  <channel id="3007.ontvjapan.com">
#    <display-name lang="ja_JP">BSジャパン</display-name>
#  </channel>
#  <channel id="3008.ontvjapan.com">
#    <display-name lang="ja_JP">BSフジ</display-name>
#  </channel>
#  <channel id="3009.ontvjapan.com">
#    <display-name lang="ja_JP">WOWOW</display-name>
#  </channel>
#  <channel id="3010.ontvjapan.com">
#    <display-name lang="ja_JP">WOWOW2</display-name>
#  </channel>
#  <channel id="3011.ontvjapan.com">
#    <display-name lang="ja_JP">WOWOW3</display-name>
#  </channel>
#  <channel id="3012.ontvjapan.com">
#    <display-name lang="ja_JP">スター・チャンネル</display-name>
#  </channel>
#  <channel id="3013.ontvjapan.com">
#    <display-name lang="ja_JP">BS11</display-name>
#  </channel>
#  <channel id="3014.ontvjapan.com">
#    <display-name lang="ja_JP">TwellV</display-name>
#  </channel>
#

#CSデジタル
$channel = "CS8";
	print "\nCS Digital Scan\n";
	$oserr = `$recpt1path $channel 4 $recfolderpath/__$channel.m2t`;
	$oserr = `$epgdumppath/epgdump /CS $recfolderpath/__$channel.m2t $xmloutpath/__$channel-epg.xml`;

	if (-s "$xmloutpath/__$channel-epg.xml" ){
		print "\t\t CS Digital can view :   \n";
		open(XML, "< $xmloutpath/__$channel-epg.xml");
		while ( $line = <XML>) {
			#Jcode::convert(\$line,'euc','utf8');
			if($line =~ /<display-name/){
				$line =~ s/<.*?>//g;
				#Jcode::convert(\$line,'utf8','euc');
				print "\t\t $line\n";
			}#end if
		}#end while
		close(XML);
	}else{
		print "\t\t Not Available :  CS Digital \n";
	}#end if 

#  <channel id="1002.ontvjapan.com">
#    <display-name lang="ja_JP">スターｃｈプラス</display-name>
#  </channel>
#  <channel id="1086.ontvjapan.com">
#    <display-name lang="ja_JP">日本映画専門ｃｈＨＤ</display-name>
#  </channel>
#  <channel id="306ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">フジテレビＣＳＨＤ</display-name>
#  </channel>
#  <channel id="1059.ontvjapan.com">
#    <display-name lang="ja_JP">ショップチャンネル</display-name>
#  </channel>
#  <channel id="1217.ontvjapan.com">
#    <display-name lang="ja_JP">ザ・シネマ</display-name>
#  </channel>
#  <channel id="800ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">スカチャンＨＤ８００</display-name>
#  </channel>
#  <channel id="801ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">スカチャン８０１</display-name>
#  </channel>
#  <channel id="802ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">スカチャン８０２</display-name>
#  </channel>
#  <channel id="100ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">ｅ２プロモ</display-name>
#  </channel>
#  <channel id="194ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">インターローカルＴＶ</display-name>
#  </channel>
#  <channel id="1025.ontvjapan.com">
#    <display-name lang="ja_JP">Ｊスポーツ　ＥＳＰＮ</display-name>
#  </channel>
#  <channel id="1016.ontvjapan.com">
#    <display-name lang="ja_JP">ＦＯＸ</display-name>
#  </channel>
#  <channel id="1018.ontvjapan.com">
#    <display-name lang="ja_JP">スペースシャワーＴＶ</display-name>
#  </channel>
#  <channel id="1046.ontvjapan.com">
#    <display-name lang="ja_JP">カートゥーン　ネット</display-name>
#  </channel>
#  <channel id="1213.ontvjapan.com">
#    <display-name lang="ja_JP">トゥーン・ディズニー</display-name>
#  </channel>
#  <channel id="1010.ontvjapan.com">
#    <display-name lang="ja_JP">東映チャンネル</display-name>
#  </channel>
#  <channel id="1005.ontvjapan.com">
#    <display-name lang="ja_JP">衛星劇場</display-name>
#  </channel>
#  <channel id="1008.ontvjapan.com">
#    <display-name lang="ja_JP">チャンネルＮＥＣＯ</display-name>
#  </channel>
#  <channel id="1009.ontvjapan.com">
#    <display-name lang="ja_JP">洋画★シネフィル</display-name>
#  </channel>
#  <channel id="1003.ontvjapan.com">
#    <display-name lang="ja_JP">スター・クラシック</display-name>
#  </channel>
#  <channel id="1133.ontvjapan.com">
#    <display-name lang="ja_JP">時代劇専門チャンネル</display-name>
#  </channel>
#  <channel id="1006.ontvjapan.com">
#    <display-name lang="ja_JP">スーパードラマ</display-name>
#  </channel>
#  <channel id="1014.ontvjapan.com">
#    <display-name lang="ja_JP">ＡＸＮ</display-name>
#  </channel>
#  <channel id="1204.ontvjapan.com">
#    <display-name lang="ja_JP">ナショジオチャンネル</display-name>
#  </channel>
#  <channel id="110ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">ワンテンポータル</display-name>
#  </channel>
#  <channel id="1028.ontvjapan.com">
#    <display-name lang="ja_JP">ゴルフチャンネル</display-name>
#  </channel>
#  <channel id="1092.ontvjapan.com">
#    <display-name lang="ja_JP">テレ朝チャンネル</display-name>
#  </channel>
#  <channel id="1019.ontvjapan.com">
#    <display-name lang="ja_JP">ＭＴＶ</display-name>
#  </channel>
#  <channel id="1024.ontvjapan.com">
#    <display-name lang="ja_JP">ミュージック・エア</display-name>
#  </channel>
#  <channel id="1067.ontvjapan.com">
#    <display-name lang="ja_JP">朝日ニュースター</display-name>
#  </channel>
#  <channel id="1070.ontvjapan.com">
#    <display-name lang="ja_JP">ＢＢＣワールド</display-name>
#  </channel>
#  <channel id="1069.ontvjapan.com">
#    <display-name lang="ja_JP">ＣＮＮｊ</display-name>
#  </channel>
#  <channel id="361ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">ジャスト・アイ</display-name>
#  </channel>
#  <channel id="1041.ontvjapan.com">
#    <display-name lang="ja_JP">Ｊスポーツ　１</display-name>
#  </channel>
#  <channel id="1042.ontvjapan.com">
#    <display-name lang="ja_JP">Ｊスポーツ　２</display-name>
#  </channel>
#  <channel id="1043.ontvjapan.com">
#    <display-name lang="ja_JP">ＪスポーツＰｌｕｓＨ</display-name>
#  </channel>
#  <channel id="1026.ontvjapan.com">
#    <display-name lang="ja_JP">ＧＡＯＲＡ</display-name>
#  </channel>
#  <channel id="1040.ontvjapan.com">
#    <display-name lang="ja_JP">ｓｋｙ・Ａスポーツ＋</display-name>
#  </channel>
#  <channel id="101ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">宝塚プロモチャンネル</display-name>
#  </channel>
#  <channel id="1207.ontvjapan.com">
#    <display-name lang="ja_JP">ＳＫＹ・ＳＴＡＧＥ</display-name>
#  </channel>
#  <channel id="305ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">チャンネル銀河</display-name>
#  </channel>
#  <channel id="1201.ontvjapan.com">
#    <display-name lang="ja_JP">ＡＴ-Ｘ</display-name>
#  </channel>
#  <channel id="1050.ontvjapan.com">
#    <display-name lang="ja_JP">ヒストリーチャンネル</display-name>
#  </channel>
#  <channel id="803ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">スカチャン８０３</display-name>
#  </channel>
#  <channel id="804ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">スカチャン８０４</display-name>
#  </channel>
#  <channel id="1007.ontvjapan.com">
#    <display-name lang="ja_JP">ムービープラスＨＤ</display-name>
#  </channel>
#  <channel id="1027.ontvjapan.com">
#    <display-name lang="ja_JP">ゴルフネットワーク</display-name>
#  </channel>
#  <channel id="1074.ontvjapan.com">
#    <display-name lang="ja_JP">ＬａＬａ　ＨＤ</display-name>
#  </channel>
#  <channel id="1073.ontvjapan.com">
#    <display-name lang="ja_JP">フジテレビ７３９</display-name>
#  </channel>
#  <channel id="1072.ontvjapan.com">
#    <display-name lang="ja_JP">フジテレビ７２１</display-name>
#  </channel>
#  <channel id="1047.ontvjapan.com">
#    <display-name lang="ja_JP">アニマックス</display-name>
#  </channel>
#  <channel id="1062.ontvjapan.com">
#    <display-name lang="ja_JP">ディスカバリー</display-name>
#  </channel>
#  <channel id="1193.ontvjapan.com">
#    <display-name lang="ja_JP">アニマルプラネット</display-name>
#  </channel>
#  <channel id="160ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">Ｃ-ＴＢＳウエルカム</display-name>
#  </channel>
#  <channel id="1120.ontvjapan.com">
#    <display-name lang="ja_JP">ＱＶＣ</display-name>
#  </channel>
#  <channel id="185ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">プライム３６５．ＴＶ</display-name>
#  </channel>
#  <channel id="1015.ontvjapan.com">
#    <display-name lang="ja_JP">ファミリー劇場</display-name>
#  </channel>
#  <channel id="3201.ontvjapan.com">
#    <display-name lang="ja_JP">ＴＢＳチャンネル</display-name>
#  </channel>
#  <channel id="1090.ontvjapan.com">
#    <display-name lang="ja_JP">ディズニーチャンネル</display-name>
#  </channel>
#  <channel id="1022.ontvjapan.com">
#    <display-name lang="ja_JP">MUSIC ON! TV</display-name>
#  </channel>
#  <channel id="1045.ontvjapan.com">
#    <display-name lang="ja_JP">キッズステーション</display-name>
#  </channel>
#  <channel id="1076.ontvjapan.com">
#    <display-name lang="ja_JP">ＴＢＳニュースバード</display-name>
#  </channel>
#  <channel id="147ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">ＣＳ日本番組ガイド</display-name>
#  </channel>
#  <channel id="1068.ontvjapan.com">
#    <display-name lang="ja_JP">日テレＧ＋</display-name>
#  </channel>
#  <channel id="5004.ontvjapan.com">
#    <display-name lang="ja_JP">fashion TV</display-name>
#  </channel>
#  <channel id="300ch.epgdata.ontvjapan">
#    <display-name lang="ja_JP">日テレプラス</display-name>
#  </channel>
#  <channel id="1023.ontvjapan.com">
#    <display-name lang="ja_JP">エコミュージックＴＶ</display-name>
#  </channel>
#  <channel id="1208.ontvjapan.com">
#    <display-name lang="ja_JP">Music Japan TV</display-name>
#  </channel>
#  <channel id="2002.ontvjapan.com">
#    <display-name lang="ja_JP">日テレＮＥＷＳ２４</display-name>
#  </channel>


#CATV
# /home/foltia/perl/tool/recpt1 --b25 C13 10 /home/foltia/php/tv/__C13.m2t 
# /home/foltia/perl/tool/epgdump /CS /home/foltia/php/tv/__C13.m2t /tmp/__C13-epg.xml
