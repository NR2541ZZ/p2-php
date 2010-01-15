<?php
// p2 -  �^�C�g���y�[�W(PC�p)

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/FileCtl.php';

$_login->authorize(); // ���[�U�F��

//=========================================================
// �ϐ�
//=========================================================

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
        require_once P2_LIB_DIR . '/login2ch.func.php';
        login2ch();
    }
}

//=========================================================
// �v�����g�ݒ�
//=========================================================
// �ŐV�Ń`�F�b�N
if ($_conf['updatan_haahaa']) {
    $newversion_found_html = _checkUpdatan();
} else {
    $newversion_found_html = '';
}

// ���O�C�����[�U���
$htm['auth_user'] = "<p>���O�C�����[�U: {$_login->user_u} - " . date("Y/m/d (D) G:i") . '</p>' . "\n";

// �i�g�сj���O�C���pURL
$url = rtrim(dirname(P2Util::getMyUrl()), '/') . '/' . '?user=' . $_login->user_u . '&b=k';
$htm['ktai_url'] = '<p>�g�у��O�C���pURL <a href="' . hs($url) . '" target="_blank">' . hs($url) . '</a></p>' . "\n";

// �O��̃��O�C�����
if ($_conf['login_log_rec'] && $_conf['last_login_log_show']) {
    if (false !== $log = P2Util::getLastAccessLog($_conf['login_log_file'])) {
        $htm['log'] = array_map('htmlspecialchars', $log);
        $htm['last_login'] = <<<EOP
<div id="last_login">
�O��̃��O�C����� - {$htm['log']['date']}<br>
���[�U:     {$htm['log']['user']}<br>
IP:         {$htm['log']['ip']}<br>
HOST:       {$htm['log']['host']}<br>
UA:         {$htm['log']['ua']}<br>
REFERER:    {$htm['log']['referer']}
<div>
EOP;
    }
}

//=========================================================
// HTML�\���o��
//=========================================================
$ptitle = "rep2 - title";

P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
<?php
P2View::printExtraHeadersHtml();
?>
	<title><?php eh($ptitle); ?></title>
	<base target="read">
    <?php
    P2View::printIncludeCssHtml('style');
    P2View::printIncludeCssHtml('title');
    ?>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
</head>
<body>
<?php P2Util::printInfoHtml(); ?>

<div class="container">
	<?php echo $newversion_found_html; ?>

	<table border="0" cellspacing="0" cellpadding="0"><tr><td>
		<img src="img/rep2.gif" alt="rep2" width="119" height="63">
	</td><td style="padding-left:30px;">

	<p>rep2 version <?php eh($_conf['p2version']); ?> �@<a href="<?php eh($p2web_url_r); ?>" target="_blank"><?php eh($_conf['p2web_url']); ?></a></p>

	<ul>
		<li><a href="viewtxt.php?file=doc/README.txt">README.txt</a></li>
		<li><a href="img/how_to_use.png">�����ȒP�ȑ���@</a></li>
		<li><a href="viewtxt.php?file=doc/ChangeLog.txt">ChangeLog�i�X�V�L�^�j</a></li>
	</ul>
	<!-- <p><a href="<?php eh($p2web_url_r); ?>" target="_blank">rep2 web &lt;<?php eh($_conf['p2web_url']); ?>&gt;</a></p> -->

	</td></tr></table>

	<?php echo $htm['auth_user']; ?>
	<?php echo $htm['ktai_url']; ?>
	<?php echo $htm['last_login']; ?>
</div>
</body></html>
<?php

exit;

//=======================================================================
// �֐� �i���̃t�@�C�����ł̂ݗ��p�j
//=======================================================================
/**
 * �I�����C�����rep2�ŐV�ł��`�F�b�N����
 *
 * @return  string  HTML
 */
function _checkUpdatan()
{
    global $_conf, $p2web_url_r;

    $no_p2status_dl_flag  = false;
    
    $ver_txt_url = $_conf['p2web_url'] . 'p2status.txt';
    $cachefile = P2Util::cacheFileForDL($ver_txt_url);
    FileCtl::mkdirFor($cachefile);
    
    if (file_exists($cachefile)) {
        // �L���b�V���̍X�V���w�莞�Ԉȓ��Ȃ�
        if (filemtime($cachefile) > time() - $_conf['p2status_dl_interval'] * 60) {
            $no_p2status_dl_flag = true;
        }
    }
    
    if (empty($no_p2status_dl_flag)) {
        P2Util::fileDownload($ver_txt_url, $cachefile);
    }
    
    $ver_txt = file($cachefile);
    $update_ver = $ver_txt[0];
    $kita = '�����������i߁�߁j��������!!!!!!';
    //$kita = '��*��ߥ*:.�..�.:*��(߁��)ߥ*:.�. .�.:*��ߥ*!!!!!';
    
    $newversion_found_html = '';
    if ($update_ver && version_compare($update_ver, $_conf['p2version'], '>')) {
        $update_ver_hs = hs($update_ver);
        $p2web_url_r_hs = hs($p2web_url_r);
        $newversion_found_html = <<<EOP
<div class="kakomi">
    {$kita}<br>
    �I�����C����� rep2 �̍ŐV�o�[�W�����������܂����B<br>
    rep2<!-- version {$update_ver_hs}--> �� <a href="{$p2web_url_r_hs}cgi/dl/dl.php?dl=p2">�_�E�����[�h</a> / <a href="{$p2web_url_r_hs}p2/doc/ChangeLog.txt"{$_conf['ext_win_target_at']}>�X�V�L�^</a>
</div>
<hr class="invisible">
EOP;
    }
    
    return $newversion_found_html;
}

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
