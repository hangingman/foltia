static STATION bsSta[] = {
	{ "NHK BS1", "3001.ontvjapan.com", 16625, 4, 101},
	{ "NHK BS2", "3002.ontvjapan.com", 16625, 4, 102},//廃止 2011/3/31 
	{ "NHK BSプレミアム", "3003.ontvjapan.com", 16626, 4, 103},
	{ "BS日テレ", "3004.ontvjapan.com", 16592, 4, 141},
	{ "BS朝日", "3005.ontvjapan.com", 16400, 4, 151},
	{ "BS-TBS", "3006.ontvjapan.com", 16401, 4, 161},
	{ "BSジャパン", "3007.ontvjapan.com", 16433, 4, 171},
	{ "BSフジ", "3008.ontvjapan.com", 16593, 4, 181},
//	{ "WOWOW", "3009.ontvjapan.com", 16432, 4, 191},
//	{ "WOWOW2", "3010.ontvjapan.com", 16432, 4, 192},
//	{ "WOWOW3", "3011.ontvjapan.com", 16432, 4, 193},
	{ "WOWOWプライム", "3009.ontvjapan.com", 16432, 4, 191},
	{ "WOWOWライブ", "4192.epgdata.ontvjapan", 17488, 4, 192},
	{ "WOWOWシネマ", "4193.epgdata.ontvjapan", 17489, 4, 193},
//	{ "スター・チャンネル", "3012.ontvjapan.com", 16529, 4, 200},
	{ "スター・チャンネル1", "3012.ontvjapan.com", 16529, 4, 200},
	{ "スター・チャンネル2", "4201.epgdata.ontvjapan", 17520, 4, 201},
	{ "スター・チャンネル3", "4202.epgdata.ontvjapan", 17520, 4, 202},
	{ "BS11", "3013.ontvjapan.com", 16528, 4, 211},
	{ "TwellV", "3014.ontvjapan.com", 16530, 4, 222},
	{ "放送大学1", "4231.epgdata.ontvjapan", 18098, 4, 231},
	{ "放送大学2", "4232.epgdata.ontvjapan", 18098, 4, 232},
	{ "放送大学3", "4233.epgdata.ontvjapan", 18098, 4, 233},
	{ "グリーンチャンネル", "4234.epgdata.ontvjapan", 18224, 4, 234},
	{ "BSアニマックス", "1047.ontvjapan.com", 18033, 4, 236},
	{ "FOX bs238", "4238.epgdata.ontvjapan", 18096, 4, 238},
	{ "BSスカパー！", "4241.epgdata.ontvjapan", 18097, 4, 241},
	{ "J SPORTS 1", "4242.epgdata.ontvjapan", 18225, 4, 242},
	{ "J SPORTS 2", "4243.epgdata.ontvjapan", 18226, 4, 243},
	{ "J SPORTS 3", "4244.epgdata.ontvjapan", 18257, 4, 244},
	{ "J SPORTS 4", "4245.epgdata.ontvjapan", 18258, 4, 245},
	{ "BS釣りビジョン", "4251.epgdata.ontvjapan", 18288, 4, 251},
	{ "IMAGICA BS", "4252.epgdata.ontvjapan", 18256, 4, 252},
	{ "日本映画専門チャンネル", "4255.epgdata.ontvjapan", 18289, 4, 255},
	{ "ディズニー・チャンネル", "1090.ontvjapan.com", 18034, 4, 256},
	{ "D-Life", "4258.epgdata.ontvjapan", 18290, 4, 258},
	{ "NHK総合テレビジョン（東京）", "4291.epgdata.ontvjapan", 17168, 4, 291},
	{ "NHK教育テレビジョン（東京）", "4292.epgdata.ontvjapan", 17168, 4, 292},
	{ "日本テレビ", "4294.epgdata.ontvjapan", 17169, 4, 294},
	{ "テレビ朝日", "4295.epgdata.ontvjapan", 17169, 4, 295},
	{ "TBSテレビ", "4296.epgdata.ontvjapan", 17169, 4, 296},
	{ "テレビ東京", "4297.epgdata.ontvjapan", 17169, 4, 297},
	{ "フジテレビ", "4298.epgdata.ontvjapan", 17168, 4, 298},
	{ "放送大学ラジオ", "4531.epgdata.ontvjapan", 18098, 4, 531},
	{ "WNI", "4910.ontvjapan.com", 16626, 4, 910},
};

static int bsStaCount = sizeof(bsSta) / sizeof (STATION);



static STATION csSta[] = {
//	{ "スターｃｈプラス", "1002.ontvjapan.com", 24608, 6, 237},//廃止
//	{ "日本映画専門ｃｈＨＤ", "1086.ontvjapan.com", 24608, 6, 239},//BS変更
	{ "フジテレビＮＥＸＴ", "309ch.epgdata.ontvjapan", 24608, 6, 309},//306→309にチャンネル変更
	{ "ショップチャンネル", "1059.ontvjapan.com", 24704, 6, 55},
	{ "ザ・シネマ", "1217.ontvjapan.com", 24736, 6, 227},//228→227
	{ "スカチャン0 HD", "800ch.epgdata.ontvjapan", 24736, 6, 800},
	{ "スカチャン1 HD", "801ch.epgdata.ontvjapan", 24736, 6, 801},
	{ "スカチャン2", "802ch.epgdata.ontvjapan", 24736, 6, 802},
	{ "ｅ２プロモ", "100ch.epgdata.ontvjapan", 28736, 7, 100},
//	{ "インターローカルＴＶ", "194ch.epgdata.ontvjapan", 28736, 7, 194},//廃止 2010/9/16/
//	{ "Ｊスポーツ　ＥＳＰＮ", "1025.ontvjapan.com", 28736, 7, 256},//BSに変更
	{ "ＦＯＸ", "1016.ontvjapan.com", 28736, 7, 312},
	{ "FOXプラス", "315ch.epgdata.ontvjapan", 28736, 7, 315},
	{ "スペースシャワーＴＶ", "1018.ontvjapan.com", 28736, 7, 322},
	{ "カートゥーン　ネット", "1046.ontvjapan.com", 28736, 7, 331},
	{ "ディズニーＸＤ", "1213.ontvjapan.com", 28736, 7, 334},//トゥーン・ディズニー →
	{ "東映チャンネル", "1010.ontvjapan.com", 28768, 7, 221},
	{ "衛星劇場", "1005.ontvjapan.com", 28768, 7, 222},
	{ "チャンネルＮＥＣＯ", "1008.ontvjapan.com", 28768, 7, 223},
//	{ "洋画★シネフィル", "1009.ontvjapan.com", 28768, 7, 224},//BS変更
//	{ "スター・クラシック", "1003.ontvjapan.com", 28768, 7, 238},//BS変更
	{ "時代劇専門チャンネル", "1133.ontvjapan.com", 28768, 7, 292},
	{ "スーパードラマ", "1006.ontvjapan.com", 28768, 7, 310},
	{ "ＡＸＮ", "1014.ontvjapan.com", 28768, 7, 311},
	{ "ナショジオチャンネル", "1204.ontvjapan.com", 28768, 7, 343},
//	{ "ワンテンポータル", "110ch.epgdata.ontvjapan", 28864, 7, 110},//2011年11月30日廃止
//	{ "ゴルフチャンネル", "1028.ontvjapan.com", 28864, 7, 260},//2012年3月31日廃止
	{ "テレ朝チャンネル", "1092.ontvjapan.com", 28864, 7, 303},
	{ "ＭＴＶ", "1019.ontvjapan.com", 28864, 7, 323},
	{ "ミュージック・エア", "1024.ontvjapan.com", 28864, 7, 324},
	{ "朝日ニュースター", "1067.ontvjapan.com", 28864, 7, 352},
	{ "ＢＢＣワールド", "1070.ontvjapan.com", 28864, 7, 353},
	{ "ＣＮＮｊ", "1069.ontvjapan.com", 28864, 7, 354},
//	{ "ジャスト・アイ", "361ch.epgdata.ontvjapan", 28864, 7, 361},// 廃止 2011/8/31
	{ "ホームドラマチャンネル", "294ch.epgdata.ontvjapan", 28736, 7, 294}, 
//	{ "Ｊスポーツ　１", "1041.ontvjapan.com", 28896, 7, 251},// 2011/10/1 BSへ変更
//	{ "Ｊスポーツ　２", "1042.ontvjapan.com", 28896, 7, 252},// 2011/10/1 BSへ変更
//	{ "ＪスポーツＰｌｕｓＨ", "1043.ontvjapan.com", 28896, 7, 253},//BS変更
	{ "ＧＡＯＲＡ", "1026.ontvjapan.com", 28896, 7, 254},
	{ "スカイ・A sports＋", "1040.ontvjapan.com", 28896, 7, 250},//2012年1月24日 Ch.255からCh.250に変更
//	{ "宝塚プロモチャンネル", "101ch.epgdata.ontvjapan", 28928, 7, 101},
	{ "TAKARAZUKA SKY STAGE", "1207.ontvjapan.com", 28928, 7, 290},
	{ "チャンネル銀河", "305ch.epgdata.ontvjapan", 28928, 7, 305},
	{ "ＡＴ-Ｘ", "1201.ontvjapan.com", 28928, 7, 333},
	{ "ヒストリーチャンネル", "1050.ontvjapan.com", 28928, 7, 342},
//	{ "スカチャン８０３", "803ch.epgdata.ontvjapan", 28928, 7, 803},//スカチャン3に変更
//	{ "スカチャン８０４", "804ch.epgdata.ontvjapan", 28928, 7, 804},
	{ "スカチャン3", "805ch.epgdata.ontvjapan", 28928, 7, 805},
	{ "ムービープラスＨＤ", "1007.ontvjapan.com", 28960, 7, 240},
	{ "ゴルフネットワーク", "1027.ontvjapan.com", 28960, 7, 262},
	{ "ＬａＬａ　ＨＤ", "1074.ontvjapan.com", 28960, 7, 314},
	{ "フジテレビＯＮＥ", "1073.ontvjapan.com", 28992, 7, 307},//フジテレビ739→
	{ "フジテレビＴＷＯ", "1072.ontvjapan.com", 28992, 7, 308},//フジテレビ721→
//	{ "アニマックス", "1047.ontvjapan.com", 28992, 7, 332},//BSアニマックスに移動
	{ "ディスカバリー", "1062.ontvjapan.com", 28992, 7, 340},
	{ "アニマルプラネット", "1193.ontvjapan.com", 28992, 7, 341},
//	{ "Ｃ-ＴＢＳウエルカム", "160ch.epgdata.ontvjapan", 29024, 7, 160},//2012年3月31日廃止
	{ "ＱＶＣ", "1120.ontvjapan.com", 29024, 7, 161},
//	{ "プライム３６５．ＴＶ", "185ch.epgdata.ontvjapan", 29024, 7, 185},//2012年3月31日 廃止
	{ "ファミリー劇場", "1015.ontvjapan.com", 29024, 7, 293},
	{ "ＴＢＳチャンネル", "3201.ontvjapan.com", 29024, 7, 301},
//	{ "ディズニーチャンネル", "1090.ontvjapan.com", 29024, 7, 304},//BSへ変更
	{ "MUSIC ON! TV", "1022.ontvjapan.com", 29024, 7, 325},
	{ "キッズステーションHD", "1045.ontvjapan.com", 29024, 7, 335},//HDに
	{ "ＴＢＳニュースバード", "1076.ontvjapan.com", 29024, 7, 351},
//	{ "ＣＳ日本番組ガイド", "147ch.epgdata.ontvjapan", 29056, 7, 147},//廃止 2010/2/28
	{ "日テレＧ＋ＨＤ", "1068.ontvjapan.com", 29056, 7, 257},//HD化
//	{ "fashion TV", "5004.ontvjapan.com", 29056, 7, 291},//廃止 2009/3/31
	{ "日テレプラス", "300ch.epgdata.ontvjapan", 29056, 7, 300},
//	{ "エコミュージックＴＶ", "1023.ontvjapan.com", 29056, 7, 320},//廃止	2009/3/31
//	{ "Music Japan TV", "1208.ontvjapan.com", 29056, 7, 321},//廃止 2012/3/31
	{ "スペースシャワーＴＶ プラス", "321ch.epgdata.ontvjapan", 29056, 7, 321},
	{ "日テレＮＥＷＳ２４", "2002.ontvjapan.com", 29056, 7, 350},
	{ "旅チャンネル", "1052.ontvjapan.com", 29056, 7, 362},
};

static int csStaCount = sizeof(csSta) / sizeof (STATION);
