<?php
/*
    rep2 - �Ǘ��җp�ݒ�t�@�C��
    
    ���̃t�@�C���̐ݒ�́A�K�v�ɉ����ĕύX���Ă�������
*/

// ----------------------------------------------------------------------
// {{{ �f�[�^�ۑ��f�B���N�g���̐ݒ�

// (���ꂼ��p�[�~�b�V������ 707 or 777 �ɁBWeb���J�O�f�B���N�g���ɐݒ肷��̂��]�܂���) 

// �����ݒ�f�[�^�ۑ��f�B���N�g��
$_conf['pref_dir'] = "./data";      // ("./data")

// �擾�X���b�h�� dat �f�[�^�ۑ��f�B���N�g��
$_conf['dat_dir'] = "./data";       // ("./data")

// �擾�X���b�h�� idx �f�[�^�ۑ��f�B���N�g��
$_conf['idx_dir'] = "./data";       // ("./data")

// }}}
// ----------------------------------------------------------------------
// {{{ �Z�L�����e�B�@�\

/**
 * �z�X�g�`�F�b�N�̏ڍאݒ�� conf/conf_hostcheck.php �ŁB
 * �������t�@�C�A�E�H�[����httpd.conf/.htaccess�̕����_��ɐݒ�ł��邵
 * �摜��conf.php�����[�h���Ȃ�php�X�N���v�g���A�N�Z�X������
 * �Ώۂɂł���̂ŁA�\�Ȃ炻�������g���ق��������B
 */

// �z�X�g�`�F�b�N������ (0:���Ȃ�; 1:�w�肳�ꂽ�z�X�g�̂݋���; 2:�w�肳�ꂽ�z�X�g�̂݋���;)
$_conf['secure']['auth_host'] = 1;  // (1)

// BBQ�𗘗p���ăv���L�V���ۂ����� (0:���Ȃ�; 1:����;)
$_conf['secure']['auth_bbq'] = 0;   // (0)

// �������݂��f���T�[�o�Œ��ڍs���悤�� �i����:1, ���Ȃ�:0�j
$_conf['disable_res'] = 0;          // (0)

// }}}
// ----------------------------------------------------------------------

// �Z�b�V�������g���ꍇ�́APHP�̐ݒ�� session.use_trans_sid ��L���ɂ��邱�Ƃ𐄏�����
$_conf['use_session'] = 2;          // (2) �Z�b�V�����𗘗p�i����:1, ���Ȃ�:0, cookie�F�؂����p����Ă��Ȃ����݂̂���:2�j

$_conf['fsockopen_time_limit'] = 7; // (7) �l�b�g���[�N�ڑ��^�C���A�E�g���� (�b)

$_conf['updatan_haahaa'] = 1;       // (1) p2�̍ŐV�o�[�W�����������`�F�b�N(����:1, ���Ȃ�:0)

$_conf['disable_res'] = 0;          // (0) �������݂��f���T�[�o�Œ��ڍs���悤�� �i����:1, ���Ȃ�:0�j

$_conf['display_threads_num'] = 150; // (150) �X���b�h�T�u�W�F�N�g�ꗗ�̃f�t�H���g�\���� (100, 150, 200, 250, 300, 400, 500, "all")
//$_conf['posted_rec_num'] = 1000;    // (1000) �������񂾃��X�̍ő�L�^�� // ���̐ݒ�͌��݂͋@�\���Ă��Ȃ�
$_conf['menu_dl_interval'] = 1;     // (1) �� menu �̃L���b�V�����X�V�����ɕێ����鎞�� (hour)
$_conf['sb_dl_interval'] = 300;     // (300) subject.txt �̃L���b�V�����X�V�����ɕێ����鎞�� (�b)

// $_conf['dat_dl_interval'] = 20;  // (20) dat �̃L���b�V�����X�V�����ɕێ����鎞�� (�b) // ���̐ݒ�͌��݂͋@�\���Ă��Ȃ�
$_conf['p2status_dl_interval'] = 360; // (360) p2status�i�A�b�v�f�[�g�`�F�b�N�j�̃L���b�V�����X�V�����ɕێ����鎞�� (��)

$_conf['login_log_rec'] = 1;        // (1) ���O�C�����O���L�^�i����:1, ���Ȃ�:0�j
$_conf['login_log_rec_num'] = 200;  // (200) ���O�C�����O�̋L�^��
$_conf['last_login_log_show'] = 1;  // (1) �O�񃍃O�C������\���i����:1, ���Ȃ�:0�j

$_conf['cid_expire_day'] = 30;      // (30) Cookie ID�̗L����������

// {{{ �g�уA�N�Z�X�L�[

$_conf['k_accesskey']['matome'] = '3'; // �V�܂Ƃ�
$_conf['k_accesskey']['latest'] = '3'; // �V
$_conf['k_accesskey']['res'] =    '7'; // ڽ
$_conf['k_accesskey']['above'] =  '2'; // ��
$_conf['k_accesskey']['up'] =     '5'; // �i�j
$_conf['k_accesskey']['prev'] =   '4'; // �O
$_conf['k_accesskey']['bottom'] = '8'; // ��
$_conf['k_accesskey']['next'] =   '6'; // ��
$_conf['k_accesskey']['info'] =   '9'; // ��
$_conf['k_accesskey']['dele'] =   '*'; // ��
$_conf['k_accesskey']['filter'] = '#'; // ��

// }}}
// {{{ �p�[�~�b�V�����̐ݒ�

$_conf['data_dir_perm'] =   0707;   // �f�[�^�ۑ��p�f�B���N�g��
$_conf['dat_perm'] =        0606;   // dat�t�@�C��
$_conf['key_perm'] =        0606;   // key.idx �t�@�C��
$_conf['dl_perm'] =         0606;   // ���̑���p2�������I��DL�ۑ�����t�@�C���i�L���b�V�����j
$_conf['pass_perm'] =       0604;   // �p�X���[�h�t�@�C��
$_conf['p2_perm'] =         0606;   // ���̑���p2�̓����ۑ��f�[�^�t�@�C��
$_conf['palace_perm'] =     0606;   // �a������L�^�t�@�C��
$_conf['favita_perm'] =     0606;   // ���C�ɔL�^�t�@�C��
$_conf['favlist_perm'] =    0606;   // ���C�ɃX���L�^�t�@�C��
$_conf['rct_perm'] =        0606;   // �ŋߓǂ񂾃X���L�^�t�@�C��
$_conf['res_write_perm'] =  0606;   // �������ݗ����L�^�t�@�C��
$_conf['conf_user_perm'] =  0606;   // ���[�U�ݒ�t�@�C��

// }}}

?>
