<?php
/*
	+live - ���[�U�ݒ� �f�t�H���g ���̃t�@�C���̓f�t�H���g�l�̐ݒ�Ȃ̂ŁA���ɕύX����K�v�͂���܂���
*/

// {{{ �������p�\��

// �X�����e�������p�\���ɂ���I�E�� (�I live22x.2ch.net ���͔� livenhk ���A������� | �S�w�� all ���� 0)
$conf_user_def['live.view'] = 'live25.2ch.net|live24.2ch.net|live23.2ch.net|24h.musume.org'; // (live25.2ch.net|live24.2ch.net|live23.2ch.net|24h.musume.org)

// ��L�ݒ�ŎI�w�肵���ꍇ�A���̒��ŏ��O����� (�� livenhk ���A������� | ���� 0)
$conf_user_def['live.default_view'] = '0'; // (0)

// �����p�\���̎��
$conf_user_def['live.view_type'] = "1"; // ("1")
$conf_user_sel['live.view_type'] = array('1' => 'Type-A', '2' => 'Type-B');

// �\�����郌�X�� (100�ȉ�����) (Auto-R/S����X���̂�)
$conf_user_def['live.before_respointer'] = "50"; // ("50")

// ���������t���[���̍��� (px)
$conf_user_def['live.post_width'] = "85"; // ("85")

// �f�t�H���g�̖������̕\�� (Auto-R/S����X���̂�)
$conf_user_def['live.bbs_noname'] = 0; // (0)
$conf_user_rad['live.bbs_noname'] = array('1' => '����', '0' => '���Ȃ�');

// sage �� �� �� (Auto-R/S����X���̂�)
$conf_user_def['live.mail_sage'] = 1; // (1)
$conf_user_rad['live.mail_sage'] = array('1' => '����', '0' => '���Ȃ�');

// ID������ O (�g��) P (����p2) Q (�t���u���E�U) �𑾎���
$conf_user_def['live.id_b'] = 0; // (0)
$conf_user_rad['live.id_b'] = array('1' => '����', '0' => '���Ȃ�');

// �A���������ʂȉ��s�̍폜
$conf_user_def['live.msg_a'] = 1; // (1)
$conf_user_rad['live.msg_a'] = array('1' => '����', '0' => '���Ȃ�');

// �S�Ẳ��s�ƃX�y�[�X�̍폜 (Auto-R/S����X���̂�)
$conf_user_def['live.msg_b'] = 1; // (1)
$conf_user_rad['live.msg_b'] = array('1' => '����', '0' => '���Ȃ�');

// ���X�̕��@ (Auto-R/S����X���̂�)
$conf_user_def['live.res_button'] = 0; // (0)
$conf_user_sel['live.res_button'] = array('0' => '���X�{�^���摜 (Re:)', '1' => '����', '2' => '���e���_�u���N���b�N');

// �摜 (P) �Ŕ�Q�ƃ��X�|�b�v�A�b�v
$conf_user_def['live.ref_res'] = 1; // (1)
$conf_user_rad['live.ref_res'] = array('1' => '����', '0' => '���Ȃ�');

// �n�C���C�g����G���A
$conf_user_def['live.highlight_area'] = 0; // (0)
$conf_user_sel['live.highlight_area'] = array('0' => '�Ώۃ��[�h��A���J�[�̂�', '1' => '�Ώۃ��X�S��');

// �A���n�C���C�g (�\���͈͂̃��X�݂̂ɘA��)
$conf_user_def['live.highlight_chain'] = 0; // (0)
$conf_user_rad['live.highlight_chain'] = array('1' => '����', '0' => '���Ȃ�');

// ����30�b�K���p�^�C�}�[���g�p
$conf_user_def['live.write_regulation'] = 1; // (1)
$conf_user_rad['live.write_regulation'] = array('1' => '����', '0' => '���Ȃ�');

// �����p�\���ł�YouTube�ƃj�R�j�R����̃����N���v���r���[�\��
$conf_user_def['live.link_movie'] = 0; // (0)
$conf_user_rad['live.link_movie'] = array('1' => '����', '0' => '���Ȃ�');

// YouTube�v���r���[�\���̃T�C�Y
$conf_user_def['live.youtube_winsize'] = 3; // (3)
$conf_user_sel['live.youtube_winsize'] = array('1' => '�� 150�~124px', '2' => '�� 300�~247px', '3' => '�� 425�~350px');

// }}}
// {{{ �������[�h/�X�N���[��

// �X�����e���I�[�g�����[�h/�X�N���[������I�E�� (�I live22x.2ch.net ���͔� livenhk ���A������� | �S�w�� all ���� 0)
$conf_user_def['live.reload'] = 'live25.2ch.net|live24.2ch.net|live23.2ch.net|24h.musume.org'; // (live25.2ch.net|live24.2ch.net|live23.2ch.net|24h.musume.org)

// ��L�ݒ�ŎI�w�肵���ꍇ�A���̒��ŏ��O����� (�� livenhk ���A������� | ���� 0)
$conf_user_def['live.default_reload'] = '0'; // (0)

// �I�[�g�����[�h�̊Ԋu (�b�w�� �ŒZ5�b�AAuto-R ���� 0)
$conf_user_def['live.reload_time'] = 10; // (10)

// �I�[�g�X�N���[���̊��炩�� (�ł����炩 1 �AAuto-S ���� 0)
$conf_user_def['live.scroll_move'] = 3; // (3)

// �I�[�g�X�N���[���̑��x (�ő� 1 �AAuto-S �����̏ꍇ�͏�̊��炩���̒l�� 0 ��)
$conf_user_def['live.scroll_speed'] = 10; // (10)

// �X�����Ă��炱�̊��Ԃ��o�߂����X���̓I�[�g�����[�h/�X�N���[�����Ȃ� (1�� = 1 �A���� = 0.5)')
$conf_user_def['live.time_lag'] = "1"; // ("1")

// }}}
?>
