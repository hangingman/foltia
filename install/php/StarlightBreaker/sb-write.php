<?php

/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/
目的
blogツール、スターライトブレイカー、書き込み、画像アップロード
引数
tid:TID
path:PATH (1004-15-20061018-0145)
f:filename (00000102.jpg)
 DCC-JPL Japan/foltia project
*/

//スタブレコンフィグ
//include("./sb-config.php");
include("./foltialib.php");
$con = m_connect();

if ($useenvironmentpolicy == 1){
	if (!isset($_SERVER['PHP_AUTH_USER'])) {
	    header("WWW-Authenticate: Basic realm=\"foltia\"");
	    header("HTTP/1.0 401 Unauthorized");
		redirectlogin();
	    exit;
	} else {
	login($con,$_SERVER['PHP_AUTH_USER'],$_SERVER['PHP_AUTH_PW']);
	}
}//end if login


$tid = getgetnumform(tid);
$path = getgetform(path);
$filename = getgetform(f);
$blogwritepw = "$_POST[blogpw]" ;

$blogtitle = stripslashes(mb_convert_encoding(urldecode($_POST[subjects]), "UTF-8", "UTF-8"));
$blogmessages = stripslashes(mb_convert_encoding(urldecode($_POST[maintext]), "UTF-8", "UTF-8"));

if (($tid == "") ||($filename == "") || ($path == "")) {
	header("Status: 404 Not Found",TRUE,404);
}

printtitle("<title>Starlight Breaker - 書き込み</title>", false);

?>

<body>
<div align="center">

<?php

printhtmlpageheader();

if (($tid == "") ||($filename == "") || ($path == "")) {
	print "No pictures<br></body></html>";
	exit;
}

$htmloutput = " <p align=\"left\"><font color=\"#494949\" size=\"6\">書き込み・アップロード</font></p>  <hr size=\"4\">";
$htmloutput =  mb_convert_encoding($htmloutput,"UTF-8", "UTF-8");
print "$htmloutput";


print "\n<!-- ";
print "$tid \n";//1004
print "$path \n";// 1004-15-20061018-0145
print "$filename \n";// 00000102.jpg

// copy src
// /home/foltia/php/tv/1004.localized/img/1004-15-20061018-0145/00000183.jpg

// dist
// /home/jplcom/public_html/diary/wp-content/uploads/StarlightBreaker/

$imgpathfilename = "$path-$filename";
$copycmd = "scp $recfolderpath/$tid.localized/img/$path/$filename  $scpuploaduseraccount@$uploadserver:$uploaddir/$imgpathfilename";

$blogmessages = "<img src=\"$wordpressimgdir/$imgpathfilename\" width=\"160\" height=\"120\" alt=\"$blogtitle\"> ".$blogmessages ;


print "$copycmd \n";
$oserr = `$copycmd`;
print "$oserr \n";

// 書き込み
require_once 'Services/Blogging.php';

$settings = Services_Blogging::discoverSettings($blogurl);
$choicedriver =  Services_Blogging::getBestAvailableDriver($settings) . "\r\n";
$server = $settings["apis"]["MetaWeblog"]["server"];
$path =  $settings["apis"]["MetaWeblog"]["path"];
$bl = Services_Blogging::factory('MetaWeblog', $blogwriteid , $blogwritepw , "$server", "$path");
print "$server \n";
print "$path \n";
print "--> \n";

$post = $bl->createNewPost();
$post->title = "$blogtitle";
$post->content = "$blogmessages";
$bl->savePost($post);

print "<a href = \"$blogurl/?p=";
echo $post->id;
print "\">Go Entry</a><br /><hr>\n";

$blogtitle =  mb_convert_encoding($blogtitle,"UTF-8", "UTF-8");
print "$blogtitle <br />\n";

$blogmessages =  mb_convert_encoding($blogmessages,"UTF-8", "UTF-8");
print "$blogmessages <br />\n";

?>
</body>
</html>
