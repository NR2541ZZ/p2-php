<?php
// p2 -  �ݒ�Ǘ��y�[�W

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/FileCtl.php';

$_login->authorize(); // ���[�U�F��


// �����o���p�ϐ�

$ptitle = '���O�C���Ǘ�';

if (UA::isK()) {
    $status_st      = '�ð��';
    $autho_user_st  = '�F��հ��';
    $client_host_st = '�[��ν�';
    $client_ip_st   = '�[��IP���ڽ';
    $browser_ua_st  = '��׳��UA';
    $p2error_st     = 'rep2 �װ';
    $logout_st      = '۸ޱ��';
} else {
    $status_st      = '�X�e�[�^�X';
    $autho_user_st  = '�F�؃��[�U';
    $client_host_st = '�[���z�X�g';
    $client_ip_st   = '�[��IP�A�h���X';
    $browser_ua_st  = '�u���E�UUA';
    $p2error_st     = 'rep2 �G���[';
    $logout_st      = '���O�A�E�g';
}

$body_onload = '';
if (UA::isPC()) {
    $body_onload = ' onLoad="setWinTitle();"';
}

$hr = P2View::getHrHtmlK();
$body_at = P2View::getBodyAttrK();

//=========================================================
// HTML�v�����g
//=========================================================
P2Util::headerNoCache();
P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
<?php
P2View::printExtraHeadersHtml();
?>
	<title><?php eh($ptitle); ?></title>
<?php
if (UA::isPC()) {
    P2View::printIncludeCssHtml('style');
    P2View::printIncludeCssHtml('setting');
    ?>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	<script type="text/javascript" src="js/basic.js?v=20061206"></script>
<?php
}

echo <<<EOP
</head>
<body{$body_onload}{$body_at}>
EOP;

if (UA::isPC()) {
    ?><p id="pan_menu">���O�C���Ǘ�</p><?php
}

P2Util::printInfoHtml();

?><ul id="setting_menu">
	<li>
		<a href="login.php<?php eh($_conf['k_at_q']); ?>">rep2���O�C���Ǘ�</a>
	</li>
	<li><a href="login2ch.php<?php eh($_conf['k_at_q']); ?>">2ch���O�C���Ǘ�</a>�i�����遜�j</li>
</ul>

[<a href="./index.php?logout=1" target="_parent">rep2����<?php eh($logout_st); ?>����</a>]
<?php
if (UA::isK()) {
    echo $hr;
}
?>
<p id="client_status">
<?php eh($autho_user_st) ?>: <?php eh($_login->user_u) ?><br>
<?php eh($client_host_st) ?>: <?php eh(P2Util::getRemoteHost()) ?><br>
<?php eh($client_ip_st) ?>: <?php eh($_SERVER['REMOTE_ADDR']) ?><br>
<?php eh($browser_ua_st) ?>: <?php ehi($_SERVER['HTTP_USER_AGENT']) ?><br>
</p>
<?php

// �t�b�^HTML�\��
if (UA::isK()) {
	echo $hr . P2View::getBackToIndexKATag() . "\n";
}

?>
</body></html>
<?php

exit;

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
