<?php
// p2 -  �ݒ�

include_once './conf.inc.php';  // ��{�ݒ�
require_once './filectl.class.php';
require_once './p2util.class.php';

authorize(); // ���[�U�F��

$_info_msg_ht = "";

//�����o���p�ϐ�========================================
$ptitle = "�ݒ�";

if ($_conf['ktai']) {
	$status_st = "�ð��";
	$autho_user_st = "�F��հ��";
	$client_host_st = "�[��ν�";
	$client_ip_st = "�[��IP���ڽ";
	$browser_ua_st = "��׳��UA";
	$p2error_st = "p2 �װ";
} else {
	$status_st = "�X�e�[�^�X";
	$autho_user_st = "�F�؃��[�U";
	$client_host_st = "�[���z�X�g";
	$client_ip_st = "�[��IP�A�h���X";
	$browser_ua_st = "�u���E�UUA";
	$p2error_st = "p2 �G���[";
}

$autho_user_ht = "";
if ($login['use']) {
	$autho_user_ht = "{$autho_user_st}: {$login['user']}<br>";
}


$body_onload = "";
if (!$_conf['ktai']) {
	$body_onload = " onLoad=\"setWinTitle();\"";
}

//=========================================================
// HTML�v�����g
//=========================================================
P2Util::header_nocache();
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html>
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>{$ptitle}</title>
EOP;
if (!$_conf['ktai']) {
	@include("./style/style_css.inc");
	@include("./style/setting_css.inc");
	echo <<<EOP
	<script type="text/javascript" src="js/basic.js"></script>
EOP;
}
echo <<<EOP
</head>
<body{$body_onload}>
EOP;

if (!$_conf['ktai']) {
	echo <<<EOP
<p id="pan_menu">�ݒ�</p>
EOP;
}

echo $_info_msg_ht;
$_info_msg_ht = "";

/*
if ($_conf['ktai']) {
	echo "<hr>";
}
*/

/*
if ($_conf['ktai']) {
	$access_login_at = " {$_conf['accesskey']}=\"1\"";
	$access_login2ch_at = " {$_conf['accesskey']}=\"2\"";
}
*/

echo "<ul id=\"setting_menu\">";

if ($login['use']) {
	echo <<<EOP
	<li><a href="login.php{$_conf['k_at_q']}"{$access_login_at}>p2�F�؃��[�U�Ǘ�</a></li>
EOP;
}

echo <<<EOP
	<li><a href="login2ch.php{$_conf['k_at_q']}"{$access_login2ch_at}>2ch���O�C���Ǘ�</a></li>
EOP;

if (!$_conf['ktai']) {
	echo <<<EOP
	<li><a href="editpref.php{$_conf['k_at_q']}">�ݒ�t�@�C���ҏW</a></li>
EOP;
} else {
	echo <<<EOP
	<li><a href="editpref.php{$_conf['k_at_q']}">�z�X�g�̓���</a>�i2ch�̔ړ]�ɑΉ����܂��j</li>
EOP;
}

echo <<<EOP
	</ul>
EOP;

if ($_conf['ktai']) {
	echo "<hr>";
}

echo "<p id=\"client_status\">";
echo <<<EOP
{$autho_user_ht}
{$client_host_st}: {$_SERVER['REMOTE_HOST']}<br>
{$client_ip_st}: {$_SERVER['REMOTE_ADDR']}<br>
{$browser_ua_st}: {$_SERVER['HTTP_USER_AGENT']}<br>
EOP;
echo "</p>";


// �t�b�^�v�����g===================
if ($_conf['ktai']) {
	echo '<hr>'.$_conf['k_to_index_ht'];
}

echo '</body></html>';

?>