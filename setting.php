<?php
/*
	p2 -  �ݒ�Ǘ��y�[�W
*/

include_once './conf/conf.inc.php';  // ��{�ݒ�
require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X
require_once './filectl.class.php';

authorize(); // ���[�U�F��

// �����o���p�ϐ� ========================================
$ptitle = '���O�C���Ǘ�';

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

// HOST���擾
if (!$hc[remoto_host] = $_SERVER['REMOTE_HOST']) {
	$hc[remoto_host] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
}
if ($hc[remoto_host] == $_SERVER['REMOTE_ADDR']) {
	$hc[remoto_host] = "";
}

$hc['ua'] = $_SERVER['HTTP_USER_AGENT'];

$hd = array_map('htmlspecialchars', $hc);

//=========================================================
// �� HTML�v�����g
//=========================================================
P2Util::header_nocache();
P2Util::header_content_type();
if ($_conf['doctype']) {
	echo $_conf['doctype'];
}
echo <<<EOP
<html>
<head>
	{$_conf['meta_charset_ht']}
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>{$ptitle}</title>
EOP;
if (!$_conf['ktai']) {
	@include("./style/style_css.inc");
	@include("./style/setting_css.inc");
	echo <<<EOP
	<script type="text/javascript" src="js/basic.js"></script>\n
EOP;
}
echo <<<EOP
</head>
<body{$body_onload}>
EOP;

// �g�їp�\��
if (!$_conf['ktai']) {
	echo <<<EOP
<p id="pan_menu">���O�C���Ǘ�</p>
EOP;
}

// �C���t�H���b�Z�[�W�\��
echo $_info_msg_ht;
$_info_msg_ht = "";

echo "<ul id=\"setting_menu\">";

if ($login['use']) {
	echo <<<EOP
	<li><a href="login.php{$_conf['k_at_q']}"{$access_login_at}>p2���O�C���Ǘ�</a></li>
EOP;
}

echo <<<EOP
	<li><a href="login2ch.php{$_conf['k_at_q']}"{$access_login2ch_at}>2ch���O�C���Ǘ�</a></li>
EOP;

echo '</ul>'."\n";

if ($_conf['ktai']) {
	echo "<hr>";
}

echo "<p id=\"client_status\">";
echo <<<EOP
{$autho_user_ht}
{$client_host_st}: {$hd['remoto_host']}<br>
{$client_ip_st}: {$_SERVER['REMOTE_ADDR']}<br>
{$browser_ua_st}: {$hd['ua']}<br>
EOP;
echo "</p>\n";


// �t�b�^�v�����g===================
if ($_conf['ktai']) {
	echo '<hr>'.$_conf['k_to_index_ht']."\n";
}

echo '</body></html>';

?>