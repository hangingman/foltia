#!/usr/bin/perl 
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
#xmltv2foltia.pl 
#XMLTV日本語版の出力するXMLを受け取り、EPGデータベースに挿入します。
#
#↓本家に取り込まれたっぽい(未確認)
#
#XMLTVは
# http://www.systemcreate-inc.com/gsxr/pc/mythtv.html#patches
#のパッチをあてたものを想定しています。オリジナルと比較して、サブタイトルや内容など
#より詳細な内容を取得できます。
#
# usage;perl  /usr/bin/tv_grab_jp | ./xmltv2foltia.pl 
#
#
# DCC-JPL Japan/foltia project
#
#

use LWP::Simple;
#use Encode qw(from_to);
#use encoding 'euc-jp', STDIN=>'utf8', STDOUT=>'euc-jp' ; # 標準入力:utf8 
# http://www.lr.pi.titech.ac.jp/~abekawa/perl/perl_unicode.html
use Jcode;
# use Data::Dumper; 
use Time::Local;
use DBI;
use DBD::Pg;

$path = $0;
$path =~ s/xmltv2foltia.pl$//i;
if ($pwd  ne "./"){
push( @INC, "$path");
}
require "foltialib.pl";

$currentworkdate = "" ;
$currentworkch = "" ;
$today =`date "+%Y%m%d"`;
$todaytime =`date "+%Y%m%d%H%M"`;

# DB Connect
	my $data_source = sprintf("dbi:%s:dbname=%s;host=%s;port=%d",
		$DBDriv,$DBName,$DBHost,$DBPort);
	 $dbh = DBI->connect($data_source,$DBUser,$DBPass) ||die $DBI::error;;

while(<>){
#print $_;
Jcode::convert(\$_,'euc','utf8');
#    from_to($_, "utf8","euc-jp");
if(/<channel/){

#  <channel id="0031.ontvjapan.com">
#    <display-name lang="ja_JP">ＮＨＫ総合</display-name>
#    <display-name lang="en">NHK</display-name>
#  </channel>

	s/^[\s]*//gio;
	s/<channel//i;
	#s/\"\/>/\" /i;
	s/\"\>/\" /i;
	s/\"[\s]/\";\n/gio;
	s/[\w]*=/\$channel{$&}=/gio;
	s/\=}=/}=/gio;
	chomp();
	eval("$_");
#print Dumper($_) ;

}elsif(/<display-name lang=\"ja_JP/){
	s/^[\s]*//gio;
	chomp();
	$channel{ja}  = &removetag($_);
	#print Dumper($_) ;
	#print "$result  \n";


}elsif(/<display-name lang=\"ja_JP/){
	s/^[\s]*//gio;
	chomp();
	$channel{ja}  = &removetag($_);
	#print Dumper($_) ;
	#print "$result  \n";
}elsif(/<display-name lang=\"en/){
	s/^[\s]*//gio;
	chomp();
	$channel{en}  = &removetag($_);
	#print Dumper($_) ;
	#print "$result  \n";

}elsif(/<\/channel>/){
# foltia 局リストに載ってない放送局は追加しない

#	print "$channel{id}
#$channel{ja}
#$channel{en}
#-------------------------------\n";

	$channel{id} = "";
	$channel{ja} = "";
	$channel{en} = "";

}elsif (/<programme /){

# <programme start="20051112210000 +0900" stop="20051112225100 +0900" channel="0007.ontvjapan.com">
#    <title lang="ja_JP">土曜ワイド劇場</title>
#    <sub-title lang="ja_JP">「救命士・牧田さおり緊急出動！毒劇物災害の現場になぜ刺殺体?意識不明の患者と少年に謎の接点」</sub-title>
#    <desc lang="ja_JP">寺田敏雄脚本　岡本弘監督　浅野温子　宇崎竜童　遠藤憲一　細川ふみえ　石丸謙二郎　根岸季衣　　そのまんま東</desc>
#    <category lang="ja_JP">ドラマ</category>
#    <category lang="en">series</category>
#  </programme>

	s/<programme //i;
	#s/\"\/>/\" /i;
	s/\"\>/\" /i;
	s/\"[\s]/\";\n/gio;
	s/[\w]*=/\$item{$&}=/gio;
	s/\=}=/}=/gio;
	chomp();
	eval("$_");
	#print Dumper($_) ;
	#print "$item{start}/$item{stop}/$item{channel}\n";
	

}elsif(/<sub-title /){
	s/^[\s]*//gio;
	chomp();
	$item{subtitle}  = &removetag($_);
	#print Dumper($_) ;
	#print "$result  \n";

}elsif(/<title /){
	s/^[\s]*//gio;
	chomp();
	$item{title}  = &removetag($_);
	#print Dumper($_) ;
	#print "$result  \n";

}elsif(/<desc /){
	s/^[\s]*//gio;
	chomp();
	$item{desc}  = &removetag($_);
	#print Dumper($_) ;
	#print "$result  \n";

}elsif(/<category lang=\"ja_JP/){
	s/^[\s]*//gio;
	chomp();
	$item{category} = &removetag($_);
	
	if ($item{category} =~ /情報/){
	$item{category} = "information";
	}elsif ($item{category} =~ /趣味・実用/){
	$item{category} = "hobby";
	}elsif ($item{category} =~ /教育/){
	$item{category} = "education";
	}elsif ($item{category} =~ /音楽/){
	$item{category} = "music";
	}elsif ($item{category} =~ /演劇/){
	$item{category} = "stage";
	}elsif ($item{category} =~ /映画/){
	$item{category} = "cinema";
	}elsif ($item{category} =~ /バラエティ/){
	$item{category} = "variety";
	}elsif ($item{category} =~ /ニュース・報道/){
	$item{category} = "news";
	}elsif ($item{category} =~ /ドラマ/){
	$item{category} = "drama";
	}elsif ($item{category} =~ /ドキュメンタリー・教養/){
	$item{category} = "documentary";
	}elsif ($item{category} =~ /スポーツ/){
	$item{category} = "sports";
	}elsif ($item{category} =~ /キッズ/){
	$item{category} = "kids";
	}elsif ($item{category} =~ /アニメ・特撮/){
	$item{category} = "anime";
	}elsif ($item{category} =~ /その他/){
	$item{category} = "etc";
	}
	
	#print Dumper($_) ;
	#print "$result  \n";


}elsif(/<\/programme>/){
#登録処理はココで
#&writelog("xmltv2foltia DEBUG call chkerase $item{start},$item{channel}");

&chkerase($item{start},$item{channel});
if ($item{subtitle} ne "" ){
	$registdesc = $item{subtitle}." ".$item{desc};
}else{
	$registdesc = $item{desc};
}
&registdb($item{start},$item{stop},$item{channel},$item{title},$registdesc ,$item{category});

#	print "$item{start}
#$item{stop}
#$item{channel}
#$item{title}
#$item{desc}
#$item{category}
# -------------------------------\n";

	$item{start} = "";
	$item{stop} = "";
	$item{channel} = "";
	$item{title} = "";
	$item{subtitle} = "";
	$item{desc} = "";
	$item{category} = "";
	$registdesc = "";
}# endif
}# while

sub chkerase{
# xmltvからきた日付とチャンネルをfoltia epgと比較
my $foltiastarttime = $_[0]; # 14桁
my $ontvepgchannel =  $_[1];
my $epgstartdate = substr($foltiastarttime,0,8); # 8桁　20050807
my  @epgcounts = "";
my $DBQuery = "";

#if ($currentworkdate eq "" ){#初回起動なら
if ( $currentworkch ne $ontvepgchannel){


if ($epgstartdate >= $today){# xmltvtvから今日以降のデータが来ていれば
my $epgstartdatetime = $today * 10000 ; # 200508070000 12桁
# 新規に入る予定の未来の番組表、全部いったん消す
# $DBQuery =  "DELETE from foltia_epg where startdatetime > $epgstartdatetime AND ontvchannel = '$ontvepgchannel' ";
 $DBQuery =  "DELETE from foltia_epg where startdatetime > $todaytime AND ontvchannel = '$ontvepgchannel' ";
	 $sth = $dbh->prepare($DBQuery);
	$sth->execute();
&writelog("xmltv2foltia DELETE EPG $epgstartdatetime:$DBQuery");
#$currentworkdate = "$today";
$currentworkch = $ontvepgchannel ;
}else{
	&writelog("xmltv2foltia ERROR EPG INVALID:$epgstartdate:$today");
	#exit();
}# endif xmltvtvから今日のデータが来ていれば
}#end if 初回起動なら

}
sub registdb{
my $foltiastarttime = $_[0];
my $foltiaendtime = $_[1];
my $channel = $_[2];
my $title = $_[3];
my $desc = $_[4];
my $category = $_[5];

#&writelog("xmltv2foltia DEBUG $foltiastarttime:$foltiaendtime");

 
$foltiastarttime = substr($foltiastarttime,0,12);
$foltiaendtime = substr($foltiaendtime,0,12);

if($foltiastarttime > $todaytime){
	
	my $DBQuery =  "SELECT max(epgid) FROM foltia_epg ";
		 $sth = $dbh->prepare($DBQuery);
		$sth->execute();
	 @currentepgid = $sth->fetchrow_array;
	 
	if ($currentepgid[0] < 1 ){
		$newepgid = 1;
	}else{
		$newepgid = $currentepgid[0]; 
		$newepgid++; 
	}
#&writelog("xmltv2foltia DEBUG $currentepgid[0] /  $newepgid");
my $lengthmin = &calclength($foltiastarttime , $foltiaendtime);
$newepgid = $dbh->quote($newepgid );
$foltiastarttime = $dbh->quote($foltiastarttime);
$foltiaendtime = $dbh->quote($foltiaendtime );
$lengthmin = $dbh->quote($lengthmin );
$channel = $dbh->quote($channel );
$title = $dbh->quote($title);
$desc = $dbh->quote($desc);
$category = $dbh->quote($category);

$DBQuery =  "insert into  foltia_epg values ($newepgid,$foltiastarttime,$foltiaendtime,$lengthmin,$channel,$title,$desc,$category)";
#	$DBQuery = $dbh->quote($DBQuery);
	 $sth = $dbh->prepare($DBQuery);
	$sth->execute();


# &writelog("xmltv2foltia DEBUG $DBQuery");

}else{
#&writelog("xmltv2foltia DEBUG SKIP $foltiastarttime:$foltiaendtime");
}#未来じゃなければ挿入しない

}








sub removetag(){
my $str = $_[0];

# HTMLタグの正規表現 $tag_regex
my $tag_regex_ = q{[^"'<>]*(?:"[^"]*"[^"'<>]*|'[^']*'[^"'<>]*)*(?:>|(?=<)|$(?!\n))}; #'}}}}
my $comment_tag_regex =
    '<!(?:--[^-]*-(?:[^-]+-)*?-(?:[^>-]*(?:-[^>-]+)*?)??)*(?:>|$(?!\n)|--.*$)';
my $tag_regex = qq{$comment_tag_regex|<$tag_regex_};


my    $text_regex = q{[^<]*};

 my   $result = '';
    while ($str =~ /($text_regex)($tag_regex)?/gso) {
      last if $1 eq '' and $2 eq '';
      $result .= $1;
      $tag_tmp = $2;
      if ($tag_tmp =~ /^<(XMP|PLAINTEXT|SCRIPT)(?![0-9A-Za-z])/i) {
        $str =~ /(.*?)(?:<\/$1(?![0-9A-Za-z])$tag_regex_|$)/gsi;
        ($text_tmp = $1) =~ s/</&lt;/g;
        $text_tmp =~ s/>/&gt;/g;
        $result .= $text_tmp;
      }
    }


return $result ;

} # end sub removetag