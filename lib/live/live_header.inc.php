<?php
/*
	+live - �\���ݒ�̏d���G���[���� ../read_header.inc.php ���ǂݍ��܂��
*/

// �G���[����
$live_error_top = "<html><body><h3>+live error:";
$live_error_end = "</h3></body></html>";
$live_conf_link = "<br><br>�@�@�@�@�@�@�@<span><a href=\"./edit_conf_user.php\">���[�U�ݒ�ҏW�ŏC������</a></span>";

// �\���ݒ�̏d���G���[�\��
$view_bbs_error = "{$live_error_top} �� <span style=\"color: #900;\">{$aThread->bbs}</span> �Œʏ�\���Ǝ����p�\���̐ݒ�l���d�����Ă��܂��B{$live_conf_link}{$live_error_end}";
$view_host_error = "{$live_error_top} �I <span style=\"color: #900;\">{$aThread->host}</span> �Œʏ�\���Ǝ����p�\���̐ݒ�l���d�����Ă��܂��B<br>���O�ݒ�o����̂� <span style=\"color: #900;\">�P��</span> �݂̂ł��B{$live_conf_link}{$live_error_end}";
$view_all_error = "{$live_error_top} <span style=\"color: #900;\">�S�Ă̎I�Ɣ�</span> �Œʏ�\���Ǝ����p�\���̐ݒ�l���d�����Ă��܂��B<br><br>�@�@�@�@�@�@�@���O�ݒ�o����̂� <span style=\"color: #900;\">�P��</span> �݂̂ł��B{$live_conf_link}{$live_error_end}";

// �I�[�g�����[�h+�X�N���[���ݒ�̏d���G���[�\��
$rel_bbs_error = "{$live_error_top} �� <span style=\"color: #900;\">{$aThread->bbs}</span> �ŃI�[�g�����[�h/�X�N���[���L���̐ݒ�l���d�����Ă��܂��B{$live_conf_link}{$live_error_end}";
$rel_host_error = "{$live_error_top} �I <span style=\"color: #900;\">{$aThread->host}</span> �ŃI�[�g�����[�h/�X�N���[���L���̐ݒ�l���d�����Ă��܂��B<br>���O�ݒ�o����̂� <span style=\"color: #900;\">�P��</span> �݂̂ł��B{$live_conf_link}{$live_error_end}";
$rel_all_error = "{$live_error_top} <span style=\"color: #900;\">�S�Ă̎I�Ɣ�</span> �ŃI�[�g�����[�h/�X�N���[���L���̐ݒ�l���d�����Ă��܂��B<br>���O�ݒ�o����̂� <span style=\"color: #900;\">�P��</span> �݂̂ł��B{$live_conf_link}{$live_error_end}";

// ���Ԑݒ�̃G���[�\��
$rel_time_error = "{$live_error_top} �I�[�g�����[�h���Ԃ� <span style=\"color: #900;\">{$_conf['live.reload_time']}</span> �b�ɐݒ肳��Ă��܂��A�ݒ�l�͍ŒZ�� <span style=\"color: #900;\">5</span> �b�ɂȂ�܂��B{$live_conf_link}{$live_error_end}";

// ���Ԑݒ�
if ($_GET['lastres'] == $aThread->rescount) {
	$reload_time = $_GET['reltime'] + 5000;
} else {
	$reload_time = $_conf['live.reload_time'] * 1000;
}

// �X�����Ă���̓����ɂ�鏈��
$thr_birth = date("U", $aThread->key);

if ($_conf['live.time_lag'] != 0) {
	$thr_time_lag = $_conf['live.time_lag'] * 86400;
} else {
	$thr_time_lag = 365 * 86400;
}

// �\���ݒ�̏d���G���[����
// bbs
if (preg_match("({$aThread->bbs})", $_conf['live.default_view']) && preg_match("({$aThread->bbs})", $_conf['live.view'])) {
	die($view_bbs_error);
// host
} elseif (preg_match("({$aThread->host})", $_conf['live.default_view']) && preg_match("({$aThread->host})", $_conf['live.view'])) {
	die($view_host_error);
// all
} elseif ($_conf['live.default_view'] == all && $_conf['live.view'] == all) {
	die($view_all_error);
// �I�[�g�����[�h+�X�N���[���ݒ�̏d���G���[����
// bbs
} elseif (preg_match("({$aThread->bbs})", $_conf['live.default_reload']) && preg_match("({$aThread->bbs})", $_conf['live.reload'])) {
	die($rel_bbs_error);
// host
} elseif (preg_match("({$aThread->host})", $_conf['live.default_reload']) && preg_match("({$aThread->host})", $_conf['live.reload'])) {
	die($rel_host_error);
// all
} elseif ($_conf['live.default_reload'] == all && $_conf['live.reload'] == all) {
	die($rel_all_error);
// 
} elseif (!preg_match("({$aThread->bbs})", $_conf['live.default_reload'])
&& (preg_match("({$aThread->bbs}|{$aThread->host})", $_conf['live.reload']) || $_conf['live.reload'] == all)) {
	// ���Ԑݒ�̃G���[����
	if ($reload_time < 5000 && $reload_time > 0) {
		die($rel_time_error);
	// �X�����Ă���̓����ɂ�鏈��
	} elseif (date("U") > $thr_birth + $thr_time_lag) {
		echo "";
	// �������ʓ�
	} elseif ($_GET['word'] || !$_GET['live']) {
		echo "";
	} else {
		include_once (P2_LIBRARY_DIR . '/live/live_js.inc.php');
	}
} else {
	echo "";
}

?>