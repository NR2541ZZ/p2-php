<?php
/*
	p2 -  �N�b�L�[�F�؏���
*/

include_once './conf.inc.php';  // ��{�ݒ�

authorize(); // ���[�U�F��

$_info_msg_ht = "";

if (isset($_GET['regist_cookie'])) {
	$regist_cookie = $_GET['regist_cookie'];
}

//================================================
// �F�ؓo�^���� cookie
//================================================
if (isset($regist_cookie)) {
	if ($regist_cookie == "in") {
		setcookie('p2_user', $login['user'], time()+60*60*24*1000);
		setcookie('p2_pass', crypt($_SERVER['PHP_AUTH_PW'], $_SERVER['PHP_AUTH_PW']), time()+60*60*24*1000); //
		$check_msg_st = "cookie�F�ؓo�^...";
	} elseif ($regist_cookie == "out") {
		setcookie("p2_user", "", time() - 3600);
		setcookie("p2_pass", "", time() - 3600);
		$check_msg_st = "cookie�F�؉���...";
	}
}

//�����o���p�ϐ�========================================

$ptitle = $check_msg_st;
$autho_user_ht = "";
$return_path = "login.php";

$next_url = <<<EOP
{$return_path}?regist_cookie_check={$_GET['regist_cookie']}{$k_at_a}
EOP;

//$meta_refresh_ht="<meta http-equiv=\"refresh\" content=\"1;URL={$next_url}\">";

$body_onload = "";
if (!$_conf['ktai']) {
	$body_onload = " onLoad=\"setWinTitle();\"";
}

//=========================================================
// HTML�v�����g
//=========================================================
header_nocache();
header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html>
<head>
	{$meta_refresh_ht}
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>{$ptitle}</title>
EOP;

if (!$_conf['ktai']) {
	@include("./style/style_css.inc");
	echo <<<EOP
	<script type="text/javascript" src="js/basic.js"></script>
EOP;
}

echo <<<EOP
</head>
<body{$body_onload}>
EOP;

echo $_info_msg_ht;
$_info_msg_ht = "";

echo <<<EOP
{$ptitle}<br>
[<a href="{$next_url}">���ʊm�F</a>]
</body>
</html>
EOP;

?>