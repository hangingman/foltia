<?php
/*
 Anime recording system foltia
 http://www.dcc-jpl.com/soft/foltia/

accountregist.php

目的
　環境ポリシーのためのユーザ新規登録


引数


 DCC-JPL Japan/foltia project

*/
?>

<?php
  include("./foltialib.php");

$con = m_connect();
$now = date("YmdHi");   
$errflag = 0;
$errmsg = "";

printtitle("<title>foltia:新規アカウント登録</title>", false);
?>
<body BGCOLOR="#ffffff" TEXT="#494949" LINK="#0047ff" VLINK="#000000" ALINK="#c6edff" >

<p align="left"><font color="#494949" size="6">
新規アカウント登録
</font></p>
<hr size="4">
<?php
//値取得
$username = getform(username);
$userpasswd = getform(userpasswd);
if ($username == "") {
	print "<p align=\"left\">新規アカウント登録をします。</p>\n";

}else{
//すでにそのユーザが存在しているかどうか確認
if ($username != ""){
$query = "
SELECT count(memberid)
FROM foltia_envpolicy 
WHERE foltia_envpolicy.name  = ?  
";
	$isaccountexist = sql_query($con, $query, "DBクエリに失敗しました",array($username));

	$isaccountexistncount = $isaccountexist->fetchColumn(0);

	if ($isaccountexistncount == 0){
	//valid
	}else{
		$errflag = 1;
		$errmsg = "そのユーザ名は既に使われています。";
	}
}
if ($userpasswd == ""){
		$errflag = 2;
		$errmsg = "パスワードが不適切です。半角英数を指定して下さい。";
}


if ($errflag == 0){
// next midを探す
$query = "
SELECT max(memberid) 
FROM  foltia_envpolicy 
";
	$rs = m_query($con, $query, "DBクエリに失敗しました");
	$maxid = $rs->fetchColumn(0);
	if ($maxid) {
		$nextcno = $maxid + 1;
	}else{
		$nextcno = 1;
	}

//登録
//INSERT
if ($demomode){
}else{
/*
ユーザクラス
0:特権管理者
1:管理者:予約削除、ファイル削除が出来る
2:利用者:EPG追加、予約追加が出来る
3:ビュアー:ファイルダウンロードが出来る
4:ゲスト:インターフェイスが見れる
*/
$remotehost = gethostbyaddr($_SERVER['REMOTE_ADDR']);

$query = "
insert into foltia_envpolicy  
values ( ?,'2',?,?,now(),?)";
//print "$query <br>\n";
	$rs = sql_query($con, $query, "DBクエリに失敗しました",array($nextcno,$username,$userpasswd,$remotehost));

print "次のアカウントを登録しました。<br>
ログイン名:$username<br>
パスワード:$userpasswd";

if ($environmentpolicytoken != ""){
	print "＋セキュリティコード<br>\n";
}
print "<a href=\"./index.php\">ログイン</a><br>\n";

print "</body>
</html>
";
	$oserr = system("$toolpath/perl/envpolicyupdate.pl");
exit;

}//endif デモモード
}else{//errorフラグあったら
print "$errmsg / $errflag<br>\n";

}//end if エラーじゃなければ

}//end if ""
?>

<form id="account" name="account" method="post" action="./accountregist.php">
  <p>登録ユーザ名:
    <input name="username" type="text" id="username" size="19" value="" />
  (半角英数のみ)</p>
  <p>登録パスワード:
    <input name="userpasswd" type="text" id="userpasswd" size="19" value="" />
  (半角英数のみ)</p>

<input type="submit" value="新規登録">　
</form>

</body>
</html>
