<?php
// p2 - �f�U�C���p �ݒ�t�@�C��
/*
	�R�����g�`����() ���̓f�t�H���g�l
	�ݒ�� style/*_css.inc �ƘA��
*/

//======================================================================
// �f�U�C���J�X�^�}�C�Y
//======================================================================

$STYLE['a_underline_none'] = "2"; // ("2") �����N�ɉ������i����:0, ���Ȃ�:1, �X���^�C�g���ꗗ�������Ȃ�:2�j

// �t�H���g ======================================================

if (strstr(geti($_SERVER['HTTP_USER_AGENT']), "Mac")) {
	/* Mac�p�t�H���g�t�@�~���[*/
	if (strstr(geti($_SERVER['HTTP_USER_AGENT']), "AppleWebKit")) { /* �u���E�U�� Mac�� Safari���� WebKit���g���Ă�����̂Ȃ� */
		$STYLE['fontfamily'] = array("Comic Sans MS", "Hiragino Maru Gothic Pro"); // ("Hiragino Kaku Gothic Pro") ��{�̃t�H���g for Safari
		$STYLE['fontfamily_bold'] = array("Arial Black", "Hiragino Kaku Gothic Std"); // ("") ��{�{�[���h�p�t�H���g for Safari�i���ʂ̑�����葾���������ꍇ��"Hiragino Kaku Gothic Std"�j
	} else {
		$STYLE['fontfamily'] = array("Comic Sans MS", "�q���M�m�ۃS Pro W4"); // ("�q���M�m�p�S Pro W3") ��{�̃t�H���g
		$STYLE['fontfamily_bold'] = array("Arial Black", "�q���M�m�p�S Std W8"); // ("�q���M�m�p�S Pro W6") ��{�{�[���h�p�t�H���g�i���ʂɑ����ɂ������ꍇ�͎w�肵�Ȃ�("")�j
	}
}

//======================================================================
// �F�ʂ̐ݒ�
//======================================================================
// ���w��("")�̓u���E�U�̃f�t�H���g�F�A�܂��͊�{�w��ƂȂ�܂��B
// �D��x�́A�ʃy�[�W�w�� �� ��{�w�� �� �g�p�u���E�U�̃f�t�H���g�w�� �ł��B

// ��{(style)=======================
$STYLE['bgcolor'] = "#1F3F2F"; // ("#ffffff") ��{ �w�i�F
$STYLE['background'] = ""; // ("") ��{ �w�i�摜
$STYLE['textcolor'] = "#FFFFFF"; // ("#000") ��{ �e�L�X�g�F
$STYLE['acolor'] = "#FFAFAF"; // ("") ��{ �����N�F
$STYLE['acolor_v'] = "#AFFFAF"; // ("") ��{ �K��ς݃����N�F�B
$STYLE['acolor_h'] = "#FFFFAF"; // ("#09c") ��{ �}�E�X�I�[�o�[���̃����N�F

$STYLE['fav_color'] = "#FFFFFF"; // ("#999") ���C�Ƀ}�[�N�̐F

// ���j���[(menu)====================
$STYLE['menu_bgcolor'] = "#1F3F2F"; //("#fff") ���j���[�̔w�i�F
$STYLE['menu_background'] = ""; //("") ���j���[�̔w�i�摜
$STYLE['menu_cate_color'] = "#FFFFFF"; // ("#333") ���j���[�J�e�S���[�̐F

$STYLE['menu_acolor_h'] = "#FFFFAF"; // ("#09c") ���j���[ �}�E�X�I�[�o�[���̃����N�F

$STYLE['menu_ita_color'] = "#FFFFFF"; // ("") ���j���[ �� �����N�F
$STYLE['menu_ita_color_v'] = "#FFFFFF"; // ("") ���j���[ �� �K��ς݃����N�F
$STYLE['menu_ita_color_h'] = "#FFFFFF"; // ("#09c") ���j���[ �� �}�E�X�I�[�o�[���̃����N�F

$STYLE['menu_newthre_color'] = "#FFFFAF";	// ("hotpink") menu �V�K�X���b�h���̐F
$STYLE['menu_newres_color'] = "#FFFF00";	// ("#ff3300") menu �V�����X���̐F

// �X���ꗗ(subject)====================
$STYLE['sb_bgcolor'] = "#1F3F2F"; // ("#fff") subject �w�i�F
$STYLE['sb_background'] = ""; // ("") subject �w�i�摜
$STYLE['sb_color'] = "#FFFFFF";  // ("#000") subject �e�L�X�g�F

$STYLE['sb_acolor'] = "#FFAFAF"; // ("#000") subject �����N�F
$STYLE['sb_acolor_v'] = "#AFFFAF"; // ("#000") subject �K��ς݃����N�F
$STYLE['sb_acolor_h'] = "#FFFFAF"; // ("#09c") subject �}�E�X�I�[�o�[���̃����N�F

$STYLE['sb_th_bgcolor'] = "#1F3F2F"; // ("#d6e7ff") subject �e�[�u���w�b�_�w�i�F
$STYLE['sb_th_background'] = ""; // ("") subject �e�[�u���w�b�_�w�i�摜
$STYLE['sb_tbgcolor'] = "#1F3F2F"; // ("#fff") subject �e�[�u�����w�i�F0
$STYLE['sb_tbgcolor1'] = "#1F3F2F"; // ("#eef") subject �e�[�u�����w�i�F1
$STYLE['sb_tbackground'] = ""; // ("") subject �e�[�u�����w�i�摜0
$STYLE['sb_tbackground1'] = ""; // ("") subject �e�[�u�����w�i�摜1

$STYLE['sb_ttcolor'] = "#FFFFFF"; // ("#333") subject �e�[�u���� �e�L�X�g�F
$STYLE['sb_tacolor'] = "#FFAFAF"; // ("#000") subject �e�[�u���� �����N�F
$STYLE['sb_tacolor_h'] = "#FFFFAF"; // ("#09c")subject �e�[�u���� �}�E�X�I�[�o�[���̃����N�F

$STYLE['sb_order_color'] = "#FFFFFF"; // ("#111") �X���ꗗ�̔ԍ� �����N�F

$STYLE['thre_title_color'] = "#FFFFFF"; // ("#000") subject �X���^�C�g�� �����N�F
$STYLE['thre_title_color_v'] = "#FFFFFF"; // ("#999") subject �X���^�C�g�� �K��ς݃����N�F
$STYLE['thre_title_color_h'] = "#FFFFFF"; // ("#09c") subject �X���^�C�g�� �}�E�X�I�[�o�[���̃����N�F

$STYLE['sb_tool_bgcolor'] = "#1F3F2F"; // ("#8cb5ff") subject �c�[���o�[�̔w�i�F
$STYLE['sb_tool_background'] = ""; // ("") subject �c�[���o�[�̔w�i�摜
$STYLE['sb_tool_border_color'] = "#FFFFFF"; // ("#6393ef") subject �c�[���o�[�̃{�[�_�[�F
$STYLE['sb_tool_color'] = "#FFFFFF"; // ("#d6e7ff") subject �c�[���o�[�� �����F
$STYLE['sb_tool_acolor'] = "#FFAFAF"; // ("#d6e7ff") subject �c�[���o�[�� �����N�F
$STYLE['sb_tool_acolor_v'] = "#AFFFAF"; // ("#d6e7ff") subject �c�[���o�[�� �K��ς݃����N�F
$STYLE['sb_tool_acolor_h'] = "#FFFFAF"; // ("#fff") subject �c�[���o�[�� �}�E�X�I�[�o�[���̃����N�F
$STYLE['sb_tool_sepa_color'] = "#FFFFFF"; // ("#000") subject �c�[���o�[�� �Z�p���[�^�����F

$STYLE['sb_now_sort_color'] = "#FFFF00";	// ("#1144aa") subject ���݂̃\�[�g�F

$STYLE['sb_thre_title_new_color'] = "#FFFF00";	// ("red") subject �V�K�X���^�C�g���̐F

$STYLE['sb_tool_newres_color'] = "#FFFF00"; // ("#ff3300") subject �c�[���o�[�� �V�K���X���̐F
$STYLE['sb_newres_color'] = "#FFFF00"; // ("#ff3300") subject �V�����X���̐F

// �X�����e(read)====================
$STYLE['read_bgcolor'] = "#1F3F2F"; // ("#efefef") �X���b�h�\���̔w�i�F
$STYLE['read_background'] = ""; // ("") �X���b�h�\���̔w�i�摜
$STYLE['read_color'] = "#FFFFFF"; // ("#000") �X���b�h�\���̃e�L�X�g�F

$STYLE['read_acolor'] = "#FFAFAF"; // ("") �X���b�h�\�� �����N�F
$STYLE['read_acolor_v'] = "#AFFFAF"; // ("") �X���b�h�\�� �K��ς݃����N�F
$STYLE['read_acolor_h'] = "#FFFFAF"; // ("#09c") �X���b�h�\�� �}�E�X�I�[�o�[���̃����N�F

$STYLE['read_newres_color'] = "#FFFF00"; // ("#ff3300")  �V�����X�Ԃ̐F

$STYLE['read_thread_title_color'] = "#FFFFFF"; // ("#f40") �X���b�h�^�C�g���F
$STYLE['read_name_color'] = "#FFFFFF"; // ("#1144aa") ���e�҂̖��O�̐F
$STYLE['read_mail_color'] = "#FFFFFF"; // ("") ���e�҂�mail�̐F ex)"#a00000"
$STYLE['read_mail_sage_color'] = "#FFFFFF"; // ("") sage�̎��̓��e�҂�mail�̐F ex)"#00b000"
$STYLE['read_ngword'] = "#000000"; // ("#bbbbbb") NG���[�h�̐F

// ���X�������݃t�H�[��================
$STYLE['post_pop_size'] = "610,350"; // ("610,350") ���X�������݃|�b�v�A�b�v�E�B���h�E�̑傫���i��,�c�j
$STYLE['post_msg_rows'] = 10; // (10) ���X�������݃t�H�[���A���b�Z�[�W�t�B�[���h�̍s��
$STYLE['post_msg_cols'] = 70; // (70) ���X�������݃t�H�[���A���b�Z�[�W�t�B�[���h�̌���

// ���X�|�b�v�A�b�v====================
$STYLE['respop_color'] = "#FFFFFF"; // ("#000") ���X�|�b�v�A�b�v�̃e�L�X�g�F
$STYLE['respop_bgcolor'] = "#1F3F2F"; // ("#ffffcc") ���X�|�b�v�A�b�v�̔w�i�F
$STYLE['respop_background'] = ""; // ("") ���X�|�b�v�A�b�v�̔w�i�摜
$STYLE['respop_b_width'] = "3px"; // ("1px") ���X�|�b�v�A�b�v�̃{�[�_�[��
$STYLE['respop_b_color'] = "#FFFFFF"; // ("black") ���X�|�b�v�A�b�v�̃{�[�_�[�F
$STYLE['respop_b_style'] = "double"; // ("solid") ���X�|�b�v�A�b�v�̃{�[�_�[�`��

$STYLE['info_pop_size'] = "600,380"; // ("600,380") ���|�b�v�A�b�v�E�B���h�E�̑傫���i��,�c�j

// �X�^�C���̏㏑��====================

//�V�����X
$MYSTYLE['subject']['sb_td']['border-bottom'] = "1px dashed #FFFFFF";
$MYSTYLE['subject']['sb_td1']['border-bottom'] = "1px dashed #FFFFFF";

//�t�B���^�����O����
$MYSTYLE['base']['.filtering']['background-color'] = "transparent";
$MYSTYLE['base']['.filtering']['border-bottom'] = "3px #FFFFFF double";

//HTML�|�b�v�A�b�v
$MYSTYLE['read']['#iframespace']['border'] = "2px #FFFFFF inset";
$MYSTYLE['read']['#closebox']['border'] = "2px #FFFFFF outset";
$MYSTYLE['read']['#closebox']['color'] = "#FFFFFF";
$MYSTYLE['read']['#closebox']['background-color'] = "#808080";
$MYSTYLE['subject']['#iframespace'] = &$MYSTYLE['read']['#iframespace'];
$MYSTYLE['subject']['#closebox'] = &$MYSTYLE['read']['#closebox'];

//���E�C���h�E
$MYSTYLE['info']['td.tdleft']['color'] = "#90F0C0";
$MYSTYLE['kanban']['td.tdleft']['color'] = "#1F3F2F";

?>