<?php
/**
 * p2 DAT���_�E�����[�h����
 */
 
require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/thread.class.php';

$_login->authorize(); // ���[�U�F��

//================================================
// �ϐ��ݒ�
//================================================
isset($_GET['host']) and $host = $_GET['host']; // "pc.2ch.net"
isset($_GET['bbs'])  and $bbs  = $_GET['bbs'];  // "php"
isset($_GET['key'])  and $key  = $_GET['key'];  // "1022999539"

// �ȉ��ǂꂩ����Ȃ��Ă��_���o��
if (empty($host) || !isset($bbs) || !isset($key)) {
    p2die('����������������܂���');
}

//================================================
// ���C������
//================================================
$aThread = new Thread;

// host�𕪉�����dat�t�@�C���̃p�X�����߂�
$aThread->setThreadPathInfo($host, $bbs, $key);

if (!file_exists($aThread->keydat)) {
    p2die("���w���DAT�͂���܂���ł���");
}

//================================================
// ���X�|���X
//================================================
header('Content-Type: text/plain; name=' . basename($aThread->keydat));
header("Content-Disposition: attachment; filename=" . basename($aThread->keydat));
readfile($aThread->keydat);

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
