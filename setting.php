<?php
/*
    p2 -  �ݒ�Ǘ��y�[�W
*/

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/filectl.class.php';

$_login->authorize(); // ���[�U�F��

// �����o���p�ϐ� ========================================
$ptitle = '���O�C���Ǘ�';
$pStrs = array();

if ($_conf['ktai']) {
    $status_st      = "�ð��";
    $autho_user_st  = "�F��հ��";
    $client_host_st = "�[��ν�";
    $client_ip_st   = "�[��IP���ڽ";
    $browser_ua_st  = "��׳��UA";
    $p2error_st     = "rep2 �װ";
    $pStrs['logout'] = '۸ޱ��';
} else {
    $status_st      = "�X�e�[�^�X";
    $autho_user_st  = "�F�؃��[�U";
    $client_host_st = "�[���z�X�g";
    $client_ip_st   = "�[��IP�A�h���X";
    $browser_ua_st  = "�u���E�UUA";
    $p2error_st     = "rep2 �G���[";
    $pStrs['logout'] = '���O�A�E�g';
}

$autho_user_ht = "{$autho_user_st}: {$_login->user_u}<br>";


$body_onload = "";
if (!$_conf['ktai']) {
	$body_onload = " onLoad=\"setWinTitle();\"";
}

$hc['remoto_host'] = P2Util::getRemoteHost();

$hc['ua'] = $_SERVER['HTTP_USER_AGENT'];

$hs = array_map('htmlspecialchars', $hc);

$hr = P2Util::getHrHtmlK();
$body_at = P2Util::getBodyAttrK();

//=========================================================
// HTML�v�����g
//=========================================================
P2Util::header_nocache();
echo $_conf['doctype'];
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
    include_once './style/style_css.inc';
    include_once './style/setting_css.inc';
    echo <<<EOP
	<script type="text/javascript" src="js/basic.js?v=20061206"></script>\n
EOP;
}

echo <<<EOP
</head>
<body{$body_onload}{$body_at}>
EOP;

// �g�їp�\��
if (!$_conf['ktai']) {
	echo <<<EOP
<p id="pan_menu">���O�C���Ǘ�</p>
EOP;
}

P2Util::printInfoHtml();

?><ul id="setting_menu">
	<li>
		<a href="login.php<?php eh($_conf['k_at_q']); ?>">rep2���O�C���Ǘ�</a>
	</li>
<?php
echo <<<EOP
	<li><a href="login2ch.php{$_conf['k_at_q']}">2ch���O�C���Ǘ�</a>�i�����遜�j</li>
EOP;

echo '</ul>' . "\n";

?>
[<a href="./index.php?logout=1" target="_parent">rep2����<?php eh($pStrs['logout']); ?>����</a>]
<?php
if ($_conf['ktai']) {
	echo $hr;
}

echo '<p id="client_status">';
echo <<<EOP
{$autho_user_ht}
{$client_host_st}: {$hs['remoto_host']}<br>
{$client_ip_st}: {$_SERVER['REMOTE_ADDR']}<br>
{$browser_ua_st}: {$hs['ua']}<br>
EOP;
echo "</p>\n";


// �t�b�^HTML�\��
if ($_conf['ktai']) {
	echo $hr . $_conf['k_to_index_ht'] . "\n";
}

echo '</body></html>';


/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
