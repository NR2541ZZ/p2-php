<?php
/*
    rep2expack - ���[�U�ݒ� �f�t�H���g

    ���̃t�@�C���̓f�t�H���g�l�̐ݒ�Ȃ̂ŁA���ɕύX����K�v�͂���܂���
*/

// {{{ �g�т̃J���[�����O�ݒ�

// �w�i
$conf_user_def['mobile.background_color'] = ""; // ("")

// ��{�����F
$conf_user_def['mobile.text_color'] = ""; // ("")

// �����N
$conf_user_def['mobile.link_color'] = ""; // ("")

// �K��ς݃����N
$conf_user_def['mobile.vlink_color'] = ""; // ("")

// �V���X���b�h�}�[�N
$conf_user_def['mobile.newthre_color'] = "#ff0000"; // ("#ff0000")

// �X���b�h�^�C�g��
$conf_user_def['mobile.ttitle_color'] = "#1144aa"; // ("#1144aa")

// �V�����X�ԍ�
$conf_user_def['mobile.newres_color'] = "#ff6600"; // ("#ff6600")

// NG���[�h
$conf_user_def['mobile.ngword_color'] = "#bbbbbb"; // ("#bbbbbb")

// �I���U�t���C���X�ԍ�
$conf_user_def['mobile.onthefly_color'] = "#00aa00"; // ("#00aa00")

// sage
$conf_user_def['mobile.sage_color'] = "#aaaaaa"; // ("#aaaaaa")

// �t�B���^�����O�Ń}�b�`�����L�[���[�h
$conf_user_def['mobile.match_color'] = ""; // ("")

// ID������"O"�ɉ���������
$conf_user_def['mobile.id_underline'] = 0; // (0)
$conf_user_rad['mobile.id_underline'] = array('1' => '����', '0' => '���Ȃ�');

// }}}
// {{{ tGrep

// �ꔭ�����ioff:0, on:1�j
$conf_user_def['expack.tgrep.quicksearch'] = 1; // (1)
$conf_user_rad['expack.tgrep.quicksearch'] = array('1' => '�\��', '0' => '��\��');

// �����������L�^���鐔�ioff:0�j
$conf_user_def['expack.tgrep.recent_num'] = 10; // (10)
$conf_user_rules['expack.tgrep.recent_num'] = array('notIntExceptMinusToDef');

// �T�[�`�{�b�N�X�Ɍ����������L�^���鐔�ASafari��p�ioff:0�j
$conf_user_def['expack.tgrep.recent2_num'] = 10; // (10)
$conf_user_rules['expack.tgrep.recent2_num'] = array('notIntExceptMinusToDef');

// }}}
// {{{ �X�}�[�g�|�b�v�A�b�v���j���[

// �����Ƀ��X�ioff:0, on:1�j
// conf_admin_ex.inc.php �� $_conf['disable_res'] �� 1 �ɂȂ��Ă���Ǝg���Ȃ�
$conf_user_def['expack.spm.kokores'] = 1; // (1)
$conf_user_rad['expack.spm.kokores'] = array('1' => '�\��', '0' => '��\��');

// �����Ƀ��X�ŊJ���t�H�[���Ɍ����X�̓��e��\������ioff:0, on:1�j
$conf_user_def['expack.spm.kokores_orig'] = 1; // (1)
$conf_user_rad['expack.spm.kokores_orig'] = array('1' => '����', '0' => '���Ȃ�');

// ���ځ[�񃏁[�h�ENG���[�h�o�^�ioff:0, on:1�j
$conf_user_def['expack.spm.ngaborn'] = 1; // (1)
$conf_user_rad['expack.spm.ngaborn'] = array('1' => '�\��', '0' => '��\��');

// ���ځ[�񃏁[�h�ENG���[�h�o�^���Ɋm�F����ioff:0, on:1�j
$conf_user_def['expack.spm.ngaborn_confirm'] = 1; // (1)
$conf_user_rad['expack.spm.ngaborn_confirm'] = array('1' => '����', '0' => '���Ȃ�');

// �t�B���^�����O�ioff:0, on:1�j
$conf_user_def['expack.spm.filter'] = 1; // (1)
$conf_user_rad['expack.spm.filter'] = array('1' => '�\��', '0' => '��\��');

// �t�B���^�����O���ʂ��J���t���[���܂��̓E�C���h�E
$conf_user_def['expack.spm.filter_target'] = "read"; // ("read")

// }}}
// {{{ �A�N�e�B�u���i�[

// �t�H���g
$conf_user_def['expack.am.fontfamily'] = "Mona,���i�["; // ("Mona,���i�[")

// �����̑傫��
$conf_user_def['expack.am.fontsize'] = "16px"; // ("16px")

// �X�C�b�`��\������ʒu
$conf_user_def['expack.am.display'] = 0; // (0)
$conf_user_sel['expack.am.display'] = array('0' => 'ID�̉�', '1' => 'SPM', '2' => '����');

// �������� (PC)
$conf_user_def['expack.am.autodetect'] = 0; // (0)
$conf_user_rad['expack.am.autodetect'] = array('1' => '����', '0' => '���Ȃ�');

// �������� & NG ���[�h���AAAS ���L���Ȃ� AAS �̃����N���쐬 (�g��)
$conf_user_def['expack.am.autong_k'] = 0; // (0)
$conf_user_rad['expack.am.autong_k'] = array('1' => '����', '0' => '���Ȃ�');

// }}}
// {{{ ���͎x��

// ��^��
//$conf_user_def['expack.editor.constant'] = 0; // (0)
//$conf_user_rad['expack.editor.constant'] = array('1' => '�g��', '0' => '�g��Ȃ�');

// ���A���^�C���E�v���r���[
$conf_user_def['expack.editor.dpreview'] = 0; // (0)
$conf_user_sel['expack.editor.dpreview'] = array('1' => '���e�t�H�[���̏�ɕ\��', '2' => '���e�t�H�[���̉��ɕ\��', '0' => '��\��');

// ���A���^�C���E�v���r���[��AA�␳�p�̃`�F�b�N�{�b�N�X��\������
$conf_user_def['expack.editor.dpreview_chkaa'] = 0; // (0)
$conf_user_rad['expack.editor.dpreview_chkaa'] = array('1' => '����', '0' => '���Ȃ�');

// �{������łȂ����`�F�b�N
$conf_user_def['expack.editor.check_message'] = 0; // (0)
$conf_user_rad['expack.editor.check_message'] = array('1' => '����', '0' => '���Ȃ�');

// sage �`�F�b�N
$conf_user_def['expack.editor.check_sage'] = 0; // (0)
$conf_user_rad['expack.editor.check_sage'] = array('1' => '����', '0' => '���Ȃ�');

// }}}
// {{{ RSS���[�_

// �ǉ��Z�b�g�� (Bloglines �̃t�H���_�̂悤�Ȃ���)
$conf_user_def['expack.rss.set_num'] = 0; // (0)
$conf_user_rules['expack.rss.set_num'] = array('notIntExceptMinusToDef', 'tooLargeSetNumToMax');

// RSS���X�V���ꂽ���ǂ����m�F����Ԋu (���w��)
$conf_user_def['expack.rss.check_interval'] = 30; // (30)
$conf_user_rules['expack.rss.check_interval'] = array('notIntExceptMinusToDef');

// RSS�̊O�������N���J���t���[���܂��̓E�C���h�E
$conf_user_def['expack.rss.target_frame'] = "read"; // ("read")

// �T�v���J���t���[���܂��̓E�C���h�E
$conf_user_def['expack.rss.desc_target_frame'] = "read"; // ("read")

// }}}
// {{{ ImageCache2

// �L���b�V���Ɏ��s�����Ƃ��̊m�F�p��ime�o�R�Ń\�[�X�ւ̃����N���쐬
$conf_user_def['expack.ic2.through_ime'] = 0; // (0)
$conf_user_rad['expack.ic2.through_ime'] = array('1' => '����', '0' => '���Ȃ�');

// �|�b�v�A�b�v�摜�̑傫�����E�C���h�E�̑傫���ɍ��킹��
$conf_user_def['expack.ic2.fitimage'] = 0; // (0)
$conf_user_sel['expack.ic2.fitimage'] = array('1' => '����', '0' => '���Ȃ�', '2' => '�����傫���Ƃ���������', '3' => '�������傫���Ƃ���������', '4' => '�蓮�ł���');

// �g�тŃC�����C���E�T���l�C�����L���̂Ƃ��̕\�����鐧�����i0�Ŗ������j
$conf_user_def['expack.ic2.pre_thumb_limit_k'] = 5; // (5)
$conf_user_rules['expack.ic2.pre_thumb_limit_k'] = array('notIntExceptMinusToDef');

// �V�����X�̉摜�� pre_thumb_limit �𖳎����đS�ĕ\������
$conf_user_def['expack.ic2.newres_ignore_limit'] = 0; // (0)
$conf_user_rad['expack.ic2.newres_ignore_limit'] = array('1' => 'Yes', '0' => 'No');

// �g�тŐV�����X�̉摜�� pre_thumb_limit_k �𖳎����đS�ĕ\������
$conf_user_def['expack.ic2.newres_ignore_limit_k'] = 0; // (0)
$conf_user_rad['expack.ic2.newres_ignore_limit_k'] = array('1' => 'Yes', '0' => 'No');

// }}}
// {{{ Google����

// Google Web APIs �̓o�^�L�[
$conf_user_def['expack.google.key'] = ""; // ("")

// �����������L�^���鐔�ioff:0�j
//$conf_user_def['expack.google.recent_num'] = 10; // (10)
//$conf_user_rules['expack.google.recent_num'] = array('notIntExceptMinusToDef');

// �T�[�`�{�b�N�X�Ɍ����������L�^���鐔�ASafari��p�ioff:0�j
$conf_user_def['expack.google.recent2_num'] = 10; // (10)
$conf_user_rules['expack.google.recent2_num'] = array('notIntExceptMinusToDef');

// SOAP �G�N�X�e���V���� �����p�\�ȂƂ��� PEAR �� SOAP �p�b�P�[�W���g���i0:no; 1:yes;�j
$conf_user_def['expack.google.force_pear'] = 0; // (0)
$conf_user_rad['expack.google.force_pear'] = array('1' => 'PEAR', '0' => 'SOAP�G�N�X�e���V����');

// }}}
// {{{ AAS

// �g�т� AA �Ǝ������肳�ꂽ�Ƃ��C�����C�� AAS �\������i0:���Ȃ�; 1:����;�j
$conf_user_def['expack.aas.inline'] = 0; // (0)
$conf_user_rad['expack.aas.inline'] = array('1' => '����', '0' => '���Ȃ�');

// �摜�`���i0:PNG; 1:JPEG; 2:GIF;�j
$conf_user_def['expack.aas.image_type'] = 0; // (0)
$conf_user_sel['expack.aas.image_type'] = array('0' => 'PNG', '1' => 'JPEG', '2' => 'GIF');

// JPEG�̕i���i0-100�j
$conf_user_def['expack.aas.jpeg_quality'] = 80; // (80)
$conf_user_rules['expack.aas.jpeg_quality'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �g�їp�̉摜�̉��� (�s�N�Z��)
$conf_user_def['expack.aas.image_width'] = 230; // (230)
$conf_user_rules['expack.aas.image_width'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �g�їp�̉摜�̍��� (�s�N�Z��)
$conf_user_def['expack.aas.image_height'] = 450; // (450)
$conf_user_rules['expack.aas.image_height'] = array('emptyToDef', 'notIntExceptMinusToDef');

// PC�p�̉摜�̉��� (�s�N�Z��)
$conf_user_def['expack.aas.image_width_pc'] = 640; // (640)
$conf_user_rules['expack.aas.image_width_pc'] = array('emptyToDef', 'notIntExceptMinusToDef');

// PC�p�̉摜�̍��� (�s�N�Z��)
$conf_user_def['expack.aas.image_height_pc'] = 480; // (480)
$conf_user_rules['expack.aas.image_height_pc'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �C�����C���\���̉��� (�s�N�Z��)
$conf_user_def['expack.aas.image_width_il'] = 64; // (64)
$conf_user_rules['expack.aas.image_width_il'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �C�����C���\���̍��� (�s�N�Z��)
$conf_user_def['expack.aas.image_height_il'] = 64; // (64)
$conf_user_rules['expack.aas.image_height_il'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �摜�̗]�����g���~���O���� (0:���Ȃ�; 1:����)
$conf_user_def['expack.aas.trim'] = 1; // (1)
$conf_user_rad['expack.aas.trim'] = array('1' => '����', '0' => '���Ȃ�');

// �����ɂ��� (0:���Ȃ�; 1:����)
$conf_user_def['expack.aas.bold'] = 0; // (0)
$conf_user_rad['expack.aas.bold'] = array('1' => '����', '0' => '���Ȃ�');

// �����F (6���܂���3����16�i��)
$conf_user_def['expack.aas.fgcolor'] = '000000'; // ('000000')

// �w�i�F (6���܂���3����16�i��)
$conf_user_def['expack.aas.bgcolor'] = 'ffffff'; // ('ffffff')

// �ő�̕����T�C�Y (�|�C���g)
$conf_user_def['expack.aas.max_fontsize'] = 36; // (36)
$conf_user_rules['expack.aas.max_fontsize'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �ŏ��̕����T�C�Y (�|�C���g)
$conf_user_def['expack.aas.min_fontsize'] = 6; // (6)
$conf_user_rules['expack.aas.min_fontsize'] = array('emptyToDef', 'notIntExceptMinusToDef');

// �C�����C���\���̕����T�C�Y (�|�C���g)
// 0�̂Ƃ��͒ʏ��AAS�Ɠ����悤�ɍœK�ȃT�C�Y���v�Z����
$conf_user_def['expack.aas.inline_fontsize'] = 6; // (6)
$conf_user_rules['expack.aas.inline_fontsize'] = array('notIntExceptMinusToDef');

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
