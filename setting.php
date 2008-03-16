<?php
/*
    rep2 -  �ݒ�Ǘ��y�[�W
*/

include_once './conf/conf.inc.php';
require_once P2_LIBRARY_DIR . '/filectl.class.php';

$_login->authorize(); // ���[�U�F��

// �����o���p�ϐ� ========================================
$ptitle = '���O�C���Ǘ�';

if ($_conf['ktai']) {
    $status_st      = '�ð��';
    $autho_user_st  = '�F��հ��';
    $client_host_st = '�[��ν�';
    $client_ip_st   = '�[��IP���ڽ';
    $browser_ua_st  = '��׳��UA';
    $p2error_st     = 'rep2 �װ';
} else {
    $status_st      = '�X�e�[�^�X';
    $autho_user_st  = '�F�؃��[�U';
    $client_host_st = '�[���z�X�g';
    $client_ip_st   = '�[��IP�A�h���X';
    $browser_ua_st  = '�u���E�UUA';
    $p2error_st     = 'rep2 �G���[';
}

$autho_user_ht = "{$autho_user_st}: {$_login->user_u}<br>";


$body_onload = '';
if (!$_conf['ktai']) {
    $body_onload = ' onLoad="setWinTitle();"';
}

// HOST���擾
if (!$hc[remoto_host] = $_SERVER['REMOTE_HOST']) {
    $hc[remoto_host] = gethostbyaddr($_SERVER['REMOTE_ADDR']);
}
if ($hc[remoto_host] == $_SERVER['REMOTE_ADDR']) {
    $hc[remoto_host] = '';
}

$hc['ua'] = $_SERVER['HTTP_USER_AGENT'];

$hd = array_map('htmlspecialchars', $hc);

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
    <title>{$ptitle}</title>\n
EOP;

if (!$_conf['ktai']) {
    echo <<<EOP
    <link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=setting&amp;skin={$skin_en}" type="text/css">
    <script type="text/javascript" src="js/basic.js?{$_conf['p2expack']}"></script>\n
EOP;
}

$body_at = ($_conf['ktai']) ? $_conf['k_colors'] : $body_onload;
echo <<<EOP
</head>
<body{$body_at}>
EOP;

// �g�їp�\��
if (!$_conf['ktai']) {
    echo <<<EOP
<p id="pan_menu">���O�C���Ǘ�</p>
EOP;
}

// �C���t�H���b�Z�[�W�\��
P2Util::printInfoHtml();

echo "<ul id=\"setting_menu\">";

echo <<<EOP
    <li><a href="login.php{$_conf['k_at_q']}"{$access_login_at}>rep2���O�C���Ǘ�</a></li>
EOP;

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
