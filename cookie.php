<?php
/**
 * p2 -  �N�b�L�[�F�؏���
 * 
 * ���������G���R�[�f�B���O: Shift_JIS
 */

include_once './conf/conf.inc.php'; // ��{�ݒ�

authorize(); // ���[�U�F��


// �����o���p�ϐ�

$return_path = 'login.php';

$next_url = <<<EOP
{$return_path}?check_regist_cookie=1&amp;regist_cookie={$_REQUEST['regist_cookie']}{$_conf['k_at_a']}
EOP;


$next_url = str_replace('&amp;', '&', $next_url);

$sid_q = (defined('SID')) ? '&'.strip_tags(SID) : '';
header('Location: '.$next_url.$sid_q);
exit;

?>
