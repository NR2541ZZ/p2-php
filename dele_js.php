<?php
include_once './conf.inc.php';  // ��{�ݒ�
require_once './dele.inc.php';	// �폜�����p�̊֐��S

authorize(); // ���[�U�F��

$r_msg = "";

// �����O�폜
if (isset($_GET['host']) && isset($_GET['bbs']) && isset($_GET['key'])) {
	$r = deleteLogs($_GET['host'], $_GET['bbs'], array($_GET['key']));
	if (empty($r)) {
		$r_msg = "0"; // ���s
	} elseif ($r == 1) {
		$r_msg = "1"; // ����
	} elseif ($r == 2) {
		$r_msg = "2"; // �Ȃ�
	}
}

// ���ʃv�����g

//$r_msg = mb_convert_encoding($r_msg, 'UTF-8', 'SJIS-win');

echo $r_msg;

?>
