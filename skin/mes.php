<?php
// p2 - �f�U�C���p �ݒ�t�@�C��
/*
	�R�����g�`����() ���̓f�t�H���g�l
	�ݒ�� style/*_css.inc �ƘA��
*/

$STYLE['a_underline_none'] = "2"; // ("2") �����N�ɉ������i����:0, ���Ȃ�:1, �X���^�C�g���ꗗ�������Ȃ�:2�j

// {{{ �t�H���g

if (strstr($_SERVER['HTTP_USER_AGENT'], "Mac")) {
	/* Mac�p�t�H���g�t�@�~���[*/
	if (strstr($_SERVER['HTTP_USER_AGENT'], "AppleWebKit")) { /* �u���E�U�� Mac�� Safari���� WebKit���g���Ă�����̂Ȃ� */
		$STYLE['fontfamily'] = array("Lucida Grande", "Hiragino Kaku Gothic Pro"); // ("Hiragino Kaku Gothic Pro") ��{�̃t�H���g for Safari
		$STYLE['fontfamily_bold'] = ""; // ("") ��{�{�[���h�p�t�H���g for Safari�i���ʂ̑�����葾���������ꍇ��"Hiragino Kaku Gothic Std"�j
	} else {
		$STYLE['fontfamily'] = array("Lucida Grande", "�q���M�m�p�S Pro W3"); // ("�q���M�m�p�S Pro W3") ��{�̃t�H���g
		$STYLE['fontfamily_bold'] = "�q���M�m�p�S Pro W6"; // ("�q���M�m�p�S Pro W6") ��{�{�[���h�p�t�H���g�i���ʂɑ����ɂ������ꍇ�͎w�肵�Ȃ�("")�j
	}
}

// }}}
/**
 * �F�ʂ̐ݒ�
 *
 * ���w��("")�̓u���E�U�̃f�t�H���g�F�A�܂��͊�{�w��ƂȂ�܂��B
 * �D��x�́A�ʃy�[�W�w�� �� ��{�w�� �� �g�p�u���E�U�̃f�t�H���g�w�� �ł��B
 */
// {{{ ��{(style)

$STYLE['background'] = "./skin/mes/mes01.gif"; // ("") ��{ �w�i�摜
$STYLE['textcolor'] = "#000000"; // ("") ��{ �e�L�X�g�F
$STYLE['acolor'] = "#AA4400"; // ("") ��{ �����N�F
$STYLE['acolor_v'] = "#201000"; // ("") ��{ �K��ς݃����N�F�B
$STYLE['acolor_h'] = "#AA0000"; // ("") ��{ �}�E�X�I�[�o�[���̃����N�F

$STYLE['fav_color'] = "#222222"; // ("#222") ���C�Ƀ}�[�N�̐F

// }}}
// {{{ ���j���[(menu)

$STYLE['menu_background'] = "./skin/mes/mes06.gif"; //("") ���j���[�̔w�i�摜
$STYLE['menu_cate_color'] = "#100800"; // ("") ���j���[�J�e�S���[�̐F

// }}}
// {{{ �X���ꗗ(subject)

$STYLE['sb_background'] = "./skin/mes/mes01.gif"; // ("") subject �w�i�摜


$STYLE['sb_th_background'] = "./skin/mes/mes05.gif"; // ("") subject �e�[�u���w�b�_�w�i�摜
$STYLE['sb_tbackground'] = "./skin/mes/mes04.gif"; // ("") subject �e�[�u�����w�i0 �i�w�b�_�[�����j
$STYLE['sb_tbackground1'] = "./skin/mes/mes03.gif"; // ("") subject �e�[�u�����w�i1

$STYLE['sb_ttcolor'] = "#222222"; // ("") subject �e�[�u���� �e�L�X�g�F
$STYLE['sb_tacolor'] = "#000000"; // ("") subject �e�[�u���� �����N�F
$STYLE['sb_tacolor_h'] = "#AA0000"; // ("")subject �e�[�u���� �}�E�X�I�[�o�[���̃����N�F

$STYLE['sb_order_color'] = "#111111"; // ("#111") �X���ꗗ�̔ԍ� �����N�F

$STYLE['thre_title_color'] = "#000000"; // ("") subject �X���^�C�g�� �����N�F
$STYLE['thre_title_color_v'] = "#444400"; // ("") subject �X���^�C�g�� �K��ς݃����N�F
$STYLE['thre_title_color_h'] = "#AA0000"; // ("#") subject �X���^�C�g�� �}�E�X�I�[�o�[���̃����N�F

$STYLE['sb_tool_background'] = "./skin/mes/mes01.gif"; // ("") subject �c�[���o�[�̔w�i�摜
$STYLE['sb_tool_border_color'] = "#222222"; // ("") subject �c�[���o�[�̃{�[�_�[�F
$STYLE['sb_tool_color'] = "#111111"; // ("") subject �c�[���o�[�� �����F
$STYLE['sb_tool_acolor'] = "#111111"; // ("") subject �c�[���o�[�� �����N�F
$STYLE['sb_tool_acolor_v'] = "#111111"; // ("") subject �c�[���o�[�� �K��ς݃����N�F
$STYLE['sb_tool_acolor_h'] = "#880000"; // ("") subject �c�[���o�[�� �}�E�X�I�[�o�[���̃����N�F
$STYLE['sb_tool_sepa_color'] = "#000000"; // ("") subject �c�[���o�[�� �Z�p���[�^�����F

$STYLE['newres_color'] = "#1144aa"; // ("")  �V�K���X�Ԃ̐F
$STYLE['sb_newres_color'] = "#1144aa"; // ("")  �V�K���X�Ԃ̐F
$STYLE['sb_tool_newres_color'] = "#1144aa"; // ("") subject �c�[���o�[�� �V�K���X���̐F

// }}}
// {{{ �X�����e(read)

$STYLE['read_bgcolor'] = "#E7DED6"; // ("") �X���b�h�\���̔w�i�F
$STYLE['read_background'] = "./skin/mes/mes03.gif"; // ("") �X���b�h�\���̔w�i�摜
$STYLE['read_color'] = "#000000"; // ("#000") �X���b�h�\���̃e�L�X�g�F

$STYLE['read_newres_color'] = "#1144aa"; // ("")  �V�����X�Ԃ̐F

$STYLE['read_thread_title_color'] = "#420"; // ("#420") �X���b�h�^�C�g���F
$STYLE['read_name_color'] = "#221100"; // ("#210") ���e�҂̖��O�̐F
$STYLE['read_mail_sage_color'] = "#660000"; // ("#b00") sage�̎��̓��e�҂�mail�̐F
$STYLE['read_ngword'] = "#bbbbbb"; // ("#bbbbbb") NG���[�h�̐F

// }}}
// {{{ ���^�폜(info)

$MYSTYLE['info']['td.tdleft']['color'] = "#000000"; // ���ږ��F
$MYSTYLE['info']['table']['border'] = "solid #111111"; // �Z�p���[�^�[�F
$MYSTYLE['info']['table']['border-width'] = "1px 0px"; // �Z�p���[�^�[�g

// }}}
// {{{ ���X�������݃t�H�[��

$STYLE['post_pop_size'] = "610,350"; // ("610,350") ���X�������݃|�b�v�A�b�v�E�B���h�E�̑傫���i��,�c�j
$STYLE['post_msg_rows'] = 10; // (10) ���X�������݃t�H�[���A���b�Z�[�W�t�B�[���h�̍s��
$STYLE['post_msg_cols'] = 70; // (70) ���X�������݃t�H�[���A���b�Z�[�W�t�B�[���h�̌���

// }}}
// {{{ ���X�|�b�v�A�b�v

$STYLE['respop_color'] = "#000"; // ("#000") ���X�|�b�v�A�b�v�̃e�L�X�g�F
$STYLE['respop_bgcolor'] = "#efefff"; // ("#ffffcc") ���X�|�b�v�A�b�v�̔w�i�F
$STYLE['respop_background'] = "./skin/mes/mes02.gif"; // ("") ���X�|�b�v�A�b�v�̔w�i�摜
$STYLE['respop_b_width'] = "1px"; // ("1px") ���X�|�b�v�A�b�v�̃{�[�_�[��
$STYLE['respop_b_color'] = "#AA4400"; // ("2F4F4F") ���X�|�b�v�A�b�v�̃{�[�_�[�F
$STYLE['respop_b_style'] = "solid"; // ("dotted+solid") ���X�|�b�v�A�b�v�̃{�[�_�[�`��

$STYLE['info_pop_size'] = "600,380"; // ("600,380") ���|�b�v�A�b�v�E�B���h�E�̑傫���i��,�c�j

// }}}

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
