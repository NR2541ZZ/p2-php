<?php
// p2 - �X�^�C���V�[�g���O���X�^�C���V�[�g�Ƃ��ďo�͂���

// �����ݒ�ǂݍ��� & ���[�U�F��
require_once 'conf/conf.inc.php';
$_login->authorize();

// �Ó��ȃt�@�C��������
if (isset($_GET['css']) && preg_match('/^\w+$/', $_GET['css'])) {
    $css = P2_STYLE_DIR . '/' . $_GET['css'] . '_css.inc';
}
if (!isset($css) || !file_exists($css)) {
    exit;
}

// �w�b�_
header('Content-Type: text/css; charset=Shift_JIS');

// �X�^�C���V�[�g�Ǎ�
ob_start();
include_once $css;
$stylesheet = ob_get_contents();
ob_end_clean();

// �\��
echo "@charset \"Shift_JIS\";\n\n";
echo $stylesheet;

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
