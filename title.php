<?php
// p2 -  �^�C�g���y�[�W

include_once './conf/conf.inc.php';   // ��{�ݒ�t�@�C���Ǎ�
require_once (P2_LIBRARY_DIR . '/filectl.class.php');

$_login->authorize(); // ���[�U�F��

//=========================================================
// �ϐ�
//=========================================================

if (!empty($GLOBALS['pref_dir_realpath_failed_msg'])) {
    $_info_msg_ht .= '<p>'.$GLOBALS['pref_dir_realpath_failed_msg'].'</p>';
}

$p2web_url_r = P2Util::throughIme($_conf['p2web_url']);

// {{{ �f�[�^�ۑ��f�B���N�g���̃p�[�~�b�V�����̒��ӂ����N����

P2Util::checkDirWritable($_conf['dat_dir']);
$checked_dirs[] = $_conf['dat_dir']; // �`�F�b�N�ς݂̃f�B���N�g�����i�[����z���

// �܂��`�F�b�N���Ă��Ȃ����
if (!in_array($_conf['idx_dir'], $checked_dirs)) {
    P2Util::checkDirWritable($_conf['idx_dir']);
    $checked_dirs[] = $_conf['idx_dir'];
}
if (!in_array($_conf['pref_dir'], $checked_dirs)) {
    P2Util::checkDirWritable($_conf['pref_dir']);
    $checked_dirs[] = $_conf['pref_dir'];
}

// }}}

//=========================================================
// �O����
//=========================================================
// ��ID 2ch �I�[�g���O�C��
if ($array = P2Util::readIdPw2ch()) {
    list($login2chID, $login2chPW, $autoLogin2ch) = $array;
    if ($autoLogin2ch) {
        include_once (P2_LIBRARY_DIR . '/login2ch.inc.php');
        login2ch();
    }
}

//=========================================================
// �v�����g�ݒ�
//=========================================================
// �ŐV�Ń`�F�b�N
if (!empty($_conf['updatan_haahaa'])) {
    $newversion_found = checkUpdatan();
}

// ���O�C�����[�U���
$htm['auth_user'] = "<p>���O�C�����[�U: {$_login->user_u} - ".date("Y/m/d (D) G:i")."</p>\n";

// �i�g�сj���O�C���pURL
//$user_u_q = !empty($_conf['ktai']) ? '' : '?user=' . $_login->user_u;
//$url = rtrim(dirname(P2Util::getMyUrl()), '/') . '/' . $user_u_q . '&amp;b=k';
$url = rtrim(dirname(P2Util::getMyUrl()), '/') . '/?b=k';

$htm['ktai_url'] = '<p>�g�у��O�C���pURL <a href="'.$url.'" target="_blank">'.$url.'</a></p>'."\n";

// �O��̃��O�C�����
if ($_conf['login_log_rec'] && $_conf['last_login_log_show']) {
    if (($log = P2Util::getLastAccessLog($_conf['login_log_file'])) !== false) {
        $htm['log'] = array_map('htmlspecialchars', $log);
        $htm['last_login'] = <<<EOP
�O��̃��O�C����� - {$htm['log']['date']}<br>
���[�U:     {$htm['log']['user']}<br>
IP:         {$htm['log']['ip']}<br>
HOST:       {$htm['log']['host']}<br>
UA:         {$htm['log']['ua']}<br>
REFERER:    {$htm['log']['referer']}
EOP;
    }
/*
    $htm['last_login'] =<<<EOP
<table cellspacing="0" cellpadding="2";>
    <tr>
        <td colspan="2">�O��̃��O�C�����</td>
    </tr>
    <tr>
        <td align="right">����: </td><td>{$alog['date']}</td>
    </tr>
    <tr>
        <td align="right">���[�U: </td><td>{$alog['user']}</td>
    </tr>
    <tr>
        <td align="right">IP: </td><td>{$alog['ip']}</td>
    </tr>
    <tr>
        <td align="right">HOST: </td><td>{$alog['host']}</td>
    </tr>
    <tr>
        <td align="right">UA: </td><td>{$alog['ua']}</td>
    </tr>
    <tr>
        <td align="right">REFERER: </td><td>{$alog['referer']}</td>
</table>
EOP;
*/
}

//=========================================================
// HTML�v�����g
//=========================================================
$ptitle = "rep2 - title";

P2Util::header_content_type();
if (!empty($_conf['doctype'])) {
    echo $_conf['doctype'];
}
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$ptitle}</title>
    <base target="read">
EOP;

@include("./style/style_css.inc");

echo <<<EOP
</head>
<body>
EOP;

// ��񃁃b�Z�[�W�\��
if (!empty($_info_msg_ht)) {
    echo $_info_msg_ht;
    $_info_msg_ht = '';
}

echo <<<EOP
<br>
<div class="container">
    {$newversion_found}
    <p>rep2 version {$_conf['p2version']} �@<a href="{$p2web_url_r}" target="_blank">{$_conf['p2web_url']}</a></p>
    <ul>
        <li><a href="viewtxt.php?file=doc/README.txt">README.txt</a></li>
        <li><a href="img/how_to_use.png">�����ȒP�ȑ���@</a></li>
        <li><a href="viewtxt.php?file=doc/ChangeLog.txt">ChangeLog�i�X�V�L�^�j</a></li>
    </ul>
    <!-- <p><a href="{$p2web_url_r}" target="_blank">rep2 web &lt;{$_conf['p2web_url']}&gt;</a></p> -->
    {$htm['auth_user']}
    {$htm['ktai_url']}
    {$htm['last_login']}
</div>
</body>
</html>
EOP;

//==================================================
// ���֐�
//==================================================
/**
* �I�����C�����rep2�ŐV�ł��`�F�b�N����
*/
function checkUpdatan()
{
    global $_conf, $p2web_url_r;

    $ver_txt_url = $_conf['p2web_url'] . 'p2status.txt';
    $cachefile = $_conf['pref_dir'] . '/p2_cache/p2status.txt';
    FileCtl::mkdir_for($cachefile);
    
    if (file_exists($cachefile)) {
        // �L���b�V���̍X�V���w�莞�Ԉȓ��Ȃ�
        if (@filemtime($cachefile) > time() - $_conf['p2status_dl_interval'] * 60) {
            $no_p2status_dl_flag = true;
        }
    }
    
    if (!$no_p2status_dl_flag) {
        P2Util::fileDownload($ver_txt_url, $cachefile);
    }
    
    $ver_txt = file($cachefile);
    $update_ver = $ver_txt[0];
    $kita = '�����������i߁�߁j��������!!!!!!';
    //$kita = '��*��ߥ*:.�..�.:*��(߁��)ߥ*:.�. .�.:*��ߥ*!!!!!';
    
    if ($update_ver && version_compare($update_ver, $_conf['p2version'], '>')) {
        $newversion_found = <<<EOP
<div class="kakomi">
    {$kita}<br>
    �I�����C����� rep2 �̍ŐV�o�[�W�����������܂����B<br>
    rep2<!-- version {$update_ver}--> �� <a href="{$p2web_url_r}cgi/dl/dl.php?dl=p2">�_�E�����[�h</a> / <a href="{$p2web_url_r}p2/doc/ChangeLog.txt"{$_conf['ext_win_target_at']}>�X�V�L�^</a>
</div>
<hr class="invisible">
EOP;
    }
    return $newversion_found;
}

?>
