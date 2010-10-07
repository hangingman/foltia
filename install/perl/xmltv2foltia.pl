#!/usr/bin/perl 
#
# Anime recording system foltia
# http://www.dcc-jpl.com/soft/foltia/
#
# xmltv2foltia.pl 
#
# XMLTV日本語版形式のXMLを受け取り、EPGデータベースに挿入します。
# アナログ時代はXMLTVを利用していましたが、現在はepgimport.plを使用します。
#
# usage
# cat /tmp/__27-epg.xml | /home/foltia/perl/xmltv2foltia.pl
#
# DCC-JPL Japan/foltia project
#
#

#use LWP::Simple;
#use Encode qw(from_to);
#use encoding 'euc-jp', STDIN=>'utf8', STDOUT=>'euc-jp' ; # 標準入力:utf8 
# http://www.lr.pi.titech.ac.jp/~abekawa/perl/perl_unicode.html
use Jcode;
#use Data::Dumper; 
use Time::Local;
use DBI;
use DBD::Pg;
use DBD::SQLite;

$path = $0;
$path =~ s/xmltv2foltia.pl$//i;
if ($path ne "./"){
push( @INC, "$path");
}
require "foltialib.pl";

$currentworkdate = "" ;
$currentworkch = "" ;
$today = strftime("%Y%m%d", localtime);
$todaytime = strftime("%Y%m%d%H%M", localtime);
@deleteepgid = ();

# DB Connect
$dbh = DBI->connect($DSN,$DBUser,$DBPass) ||die $DBI::error;;

while(<>){
#print $_;
s/\xef\xbd\x9e/\xe3\x80\x9c/g; #wavedash
s/\xef\xbc\x8d/\xe2\x88\x92/g; #hyphenminus
s/&#([0-9A-Fa-f]{2,6});/(chr($1))/eg; #'遊戯王5D&#039;s'とかの数値参照対応を

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
	$item{title} =~ s/【.*?】//g;#【解】とか
	$item{title} =~ s/\[.*?\]//g;#[二]とか 
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
#&writelog("xmltv2foltia DEBUG call chkerase $item{'start'},$item{'channel'}");
#旧仕様	#&chkerase($item{'start'}, $item{'channel'});
	&replaceepg($item{'start'}, $item{'channel'},$item{'stop'});
	if ($item{'subtitle'} ne "" ){
	    $registdesc = $item{'subtitle'}." ".$item{'desc'};
}else{
	    $registdesc = $item{'desc'};
}
	&registdb($item{'start'},$item{'stop'},$item{'channel'},$item{'title'},$registdesc ,$item{'category'});

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
&commitdb;


#end
################

sub replaceepg{
#消すEPGのIDを配列に追加します
my $foltiastarttime = $_[0]; # 14桁
my $ontvepgchannel =  $_[1];
my $foltiaendtime = $_[2]; # 14桁
my @data = ();

$foltiastarttime = substr($foltiastarttime,0,12); # 12桁　200508072254
$foltiaendtime   = substr($foltiaendtime,0,12); # 12桁　200508072355

$sth = $dbh->prepare($stmt{'xmltv2foltia.replaceepg.1'});
my $now = &epoch2foldate(time());
$sth->execute($foltiastarttime , $foltiaendtime , $ontvepgchannel,$now);

while (@data = $sth->fetchrow_array()) {
	push(@deleteepgid,$data[0]);
	#&writelog("xmltv2foltia DEBUG push(\@deleteepgid,$data[0]);");
}#end while 

#上書きを消す
$sth = $dbh->prepare($stmt{'xmltv2foltia.replaceepg.2'});
$sth->execute($foltiastarttime , $foltiaendtime , $ontvepgchannel);
while (@data = $sth->fetchrow_array()) {
	push(@deleteepgid,$data[0]);
	#&writelog("xmltv2foltia DEBUG push(\@deleteepgid,$data[0]);");
}#end while 

}#endsub replaceepg

sub registdb{
my $foltiastarttime = $_[0];
my $foltiaendtime = $_[1];
my $channel = $_[2];
my $title = $_[3];
my $desc = $_[4];
my $category = $_[5];

#Encode::JP::H2Z::z2h(\$string);
$title = jcode($title)->tr('Ａ-Ｚａ-ｚ０-９！＃＄％＆（）＊＋，−．／：；＜＝＞？＠［＼］＾＿｀｛｜｝','A-Za-z0-9!#$%&()*+,-./:;<=>?@[\]^_`{|}');
$desc = jcode($desc)->tr('Ａ-Ｚａ-ｚ０-９！＃＄％＆（）＊＋，−．／：；＜＝＞？＠［＼］＾＿｀｛｜｝','A-Za-z0-9!#$%&()*+,-./:;<=>?@[\]^_`{|}');

#&writelog("xmltv2foltia DEBUG $foltiastarttime:$foltiaendtime");
$foltiastarttime = substr($foltiastarttime,0,12);
$foltiaendtime = substr($foltiaendtime,0,12);

if($foltiaendtime > $todaytime){
# epgidはAUTOINCREMENTに変更した #2010/8/10 
#	$sth = $dbh->prepare($stmt{'xmltv2foltia.registdb.1'});
#		$sth->execute();
#	 @currentepgid = $sth->fetchrow_array;
#	 
#	if ($currentepgid[0] < 1 ){
#		$newepgid = 1;
#	}else{
#		$newepgid = $currentepgid[0]; 
#		$newepgid++; 
#	}
#&writelog("xmltv2foltia DEBUG $currentepgid[0] /  $newepgid");
my $lengthmin = &calclength($foltiastarttime , $foltiaendtime);

#print "xmltv2foltia DEBUG :INSERT INTO foltia_epg VALUES ($newepgid, $foltiastarttime, $foltiaendtime, $lengthmin, $channel, $title, $desc, $category)\n";
push (@foltiastarttime,$foltiastarttime);
push (@foltiaendtime,$foltiaendtime); 
push (@lengthmin,$lengthmin); 
push (@channel,$channel); 
push (@title,$title); 
push (@desc,$desc);
push (@category,$category);
#	$sth = $dbh->prepare($stmt{'xmltv2foltia.registdb.2'});
#	$sth->execute($newepgid, $foltiastarttime, $foltiaendtime, $lengthmin, $channel, $title, $desc, $category) || warn "error: $newepgid, $foltiastarttime, $foltiaendtime, $lengthmin, $channel, $title, $desc, $category\n";
# &writelog("xmltv2foltia DEBUG $DBQuery");
}else{
#&writelog("xmltv2foltia DEBUG SKIP $foltiastarttime:$foltiaendtime");
}#未来じゃなければ挿入しない

}#end sub registdb

sub commitdb{
$dbh->{AutoCommit} = 0;
#print Dumper(\@dbarray);
my $loopcount = @foltiastarttime;
my $i = 0;

#削除
foreach $delid (@deleteepgid){
	$sth = $dbh->prepare($stmt{'xmltv2foltia.commitdb.1'});
	$sth->execute( $delid ) || warn "$delid\n";
	#&writelog("xmltv2foltia DEBUG $stmt{'xmltv2foltia.commitdb.1'}/$delid");
}
#追加
for ($i=0;$i<$loopcount;$i++){
	$sth = $dbh->prepare($stmt{'xmltv2foltia.commitdb.2'});
	$sth->execute( $foltiastarttime[$i],$foltiaendtime[$i], $lengthmin[$i], $channel[$i], $title[$i], $desc[$i], $category[$i]) || warn "error: $foltiastarttime, $foltiaendtime, $lengthmin, $channel, $title, $desc, $category\n";
	#&writelog("xmltv2foltia DEBUG $stmt{'xmltv2foltia.commitdb.2'}/$foltiastarttime[$i],$foltiaendtime[$i], $lengthmin[$i], $channel[$i], $title[$i], $desc[$i], $category[$i]");
}# end for
$dbh->commit;
$dbh->{AutoCommit} = 1;
}#end sub commitdb

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