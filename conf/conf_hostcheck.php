<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

/*
    rep2 - �z�X�g�P�ʂł̃A�N�Z�X����/���ۂ̐ݒ�t�@�C��

    ���̃t�@�C���̐ݒ�́A�K�v�ɉ����ĕύX���Ă�������
*/

$GLOBALS['_HOSTCHKCONF'] = array();

// �z�X�g���Ƃ̐ݒ� (0:����; 1:����;)
// $_conf['secure']['auth_host'] == 0 �̂Ƃ��A���R�Ȃ��疳���B
// $_conf['secure']['auth_host'] == 1 �̂Ƃ��A�l��1�i�^�j�̃z�X�g�̂݋��B
// $_conf['secure']['auth_host'] == 2 �̂Ƃ��A�l��0�i�U�j�̃z�X�g�̂݋��ہB
$GLOBALS['_HOSTCHKCONF']['host_type'] = array(
    // p2�����삵�Ă���}�V��
        'localhost' => 1,
    // �N���XA~C�̃v���C�x�[�g�A�h���X
        'private'   => 1,
    // i���[�h
        'DoCoMo'    => 1,
    // ezWEB
        'au'        => 1,
    // Vodafone Live!
        'Vodafone'  => 1,
    // Air H"
        'AirH'      => 1,
    // ���[�U�[�ݒ�
        'custom'    => 0,
);

// �A�N�Z�X��������IP�A�h���X�ш�
// �gIP�A�h���X => �}�X�N�h�`���̘A�z�z��
$GLOBALS['_HOSTCHKCONF']['custom_allowed_host'] = array(
    //'192.168.0.0' => 24,
);

// �A�N�Z�X�����ۂ���IP�A�h���X�ш�
// �gIP�A�h���X => �}�X�N�h�`���̘A�z�z��
$GLOBALS['_HOSTCHKCONF']['custom_denied_host'] = array(
    //'192.168.0.0' => 24,
);

// BBQ�L���b�V���̗L������ (�b���Ŏw��A0�Ȃ�i�v�Ă�)
$GLOBALS['_HOSTCHKCONF']['auth_bbq_burned_expire'] = 0;

// ��xBBQ�`�F�b�N������ł����z�X�g�ɑ΂���BBQ�F�؃p�X�X���[�̗L������ (�b���Ŏw��A0�Ȃ疈��m�F)
$GLOBALS['_HOSTCHKCONF']['auth_bbq_passed_expire'] = 3600;

?>
