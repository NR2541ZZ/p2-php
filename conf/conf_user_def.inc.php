<?php
/*
    rep2 - ���[�U�ݒ� �f�t�H���g
    
    ���̃t�@�C���̓f�t�H���g�l�̐ݒ�Ȃ̂ŁA���ɕύX����K�v�͂���܂���
*/

// {{{ ��be.2ch.net�A�J�E���g

// be.2ch.net�̔F�؃R�[�h(�p�X���[�h�ł͂Ȃ�)
$conf_user_def['be_2ch_code'] = ""; // ("")

// be.2ch.net�̓o�^���[���A�h���X
$conf_user_def['be_2ch_mail'] = ""; // ("")

// }}}
// {{{ ��PATH

// �E�������ɍŏ��ɕ\�������y�[�W�B�I�����C��URL���B
$conf_user_def['first_page'] = "first_cont.php"; // ("first_cont.php") 

/*
    ���X�g�̓I�����C���ƃ��[�J���̗�������ǂݍ��߂�
    �I�����C���� $conf_user_def['brdfile_online'] �Őݒ�
    ���[�J���� ./board �f�B���N�g�����쐬���A���̒���brd�t�@�C����u���i�����j
*/

/*
    ���X�g���I�����C��URL($conf_user_def['brdfile_online'])���玩���œǂݍ��ށB
    �w���� menu.html �`���A2channel.brd �`���̂ǂ���ł��悢�B
    �K�v�Ȃ���΁A���w��("")�ɂ���B
*/
// ("http://azlucky.s25.xrea.com/2chboard/bbsmenu.html")    // 2ch + �O��BBS
// ("http://menu.2ch.net/bbsmenu.html")                     // 2ch��{

$conf_user_def['brdfile_online'] = "http://azlucky.s25.xrea.com/2chboard/bbsmenu.html";
$conf_user_rules['brdfile_online'] = array('NotEmpty');

// }}}
// {{{ ��subject

// �X���b�h�ꗗ�̎����X�V�Ԋu�B�i���w��B0�Ȃ玩���X�V���Ȃ��B�j
$conf_user_def['refresh_time'] = 0; // (0)

// �X���b�h�ꗗ�Ŗ��擾�X���ɑ΂��Č��X���ւ̃����N�i�E�j��\�� (����:1, ���Ȃ�:0)
$conf_user_def['sb_show_motothre'] = 1; // (1)
$conf_user_sel['sb_show_motothre'] = array('1' => '����', '0' => '���Ȃ�');

// �X���b�h�ꗗ�i�\���j��>>1��\�� (����:1, ���Ȃ�:0, �j���[�X�n�̂�:2)
$conf_user_def['sb_show_one'] = 0; // (0)
$conf_user_sel['sb_show_one'] = array('1' => '����', '0' => '���Ȃ�', '2' => '�j���[�X�n�̂�');

// �X���b�h�ꗗ�ł��΂₳�i���X�Ԋu�j��\�� (����:1, ���Ȃ�:0)
$conf_user_def['sb_show_spd'] = 0; // (0)
$conf_user_sel['sb_show_spd'] = array('1' => '����', '0' => '���Ȃ�');

// �X���b�h�ꗗ�Ő����i1��������̃��X���j��\�� (����:1, ���Ȃ�:0)
$conf_user_def['sb_show_ikioi'] = 1; // (1)
$conf_user_sel['sb_show_ikioi'] = array('1' => '����', '0' => '���Ȃ�');

// �X���b�h�ꗗ�ł��C�ɃX���}�[�N����\�� (����:1, ���Ȃ�:0)
$conf_user_def['sb_show_fav'] = 0; // (0)
$conf_user_sel['sb_show_fav'] = array('1' => '����', '0' => '���Ȃ�');

// �\���̃X���b�h�ꗗ�ł̃f�t�H���g�̃\�[�g�w��
$conf_user_def['sb_sort_ita'] = 'ikioi'; // ('ikioi')
$conf_user_sel['sb_sort_ita'] = array(
    'midoku' => '�V��', 'res' => '���X', 'no' => 'No.', 'title' => '�^�C�g��', // 'spd' => '���΂₳', 
    'ikioi' => '����', 'bd' => 'Birthday'); // , 'fav' => '���C�ɃX��'

// �V���\�[�g�ł́u�����Ȃ��v�́u�V�����[���v�ɑ΂���\�[�g�D�揇�� (���:0.1, ����:0, ����:-0.1)
$conf_user_def['sort_zero_adjust'] = '0.1'; // (0.1)
$conf_user_sel['sort_zero_adjust'] = array('0.1' => '���', '0' => '����', '-0.1' => '����');

// �����\�[�g���ɐV�����X�̂���X����D�� (����:1, ���Ȃ�:0)
$conf_user_def['cmp_dayres_midoku'] = 1; // (1)
$conf_user_sel['cmp_dayres_midoku'] = array('1' => '����', '0' => '���Ȃ�');

// �g�щ{�����A��x�ɕ\������X���̐�
$conf_user_def['k_sb_disp_range'] = 30; // (30)
$conf_user_rules['k_sb_disp_range'] = array('NotEmpty', 'IntExceptMinus');

// �����X���͕\�������Ɋւ�炸�\�� (����:1, ���Ȃ�:0)
$conf_user_def['viewall_kitoku'] = 1; // (1)
$conf_user_sel['viewall_kitoku'] = array('1' => '����', '0' => '���Ȃ�');

// }}}
// {{{ ��read

// �X�����e�\�����A���ǂ̉��R�O�̃��X�Ƀ|�C���^�����킹�邩
$conf_user_def['respointer'] = 1; // (1)
$conf_user_rules['respointer'] = array('IntExceptMinus');

// PC�{�����A�|�C���^�̉��R�O�̃��X����\�����邩
$conf_user_def['before_respointer'] = 25; // (25)
$conf_user_rules['before_respointer'] = array('IntExceptMinus');

// �V���܂Ƃߓǂ݂̎��A�|�C���^�̉��R�O�̃��X����\�����邩
$conf_user_def['before_respointer_new'] = 0; // (0)
$conf_user_rules['before_respointer_new'] = array('IntExceptMinus');

// �V���܂Ƃߓǂ݂ň�x�ɕ\�����郌�X��
$conf_user_def['rnum_all_range'] = 200; // (200)
$conf_user_rules['rnum_all_range'] = array('NotEmpty', 'IntExceptMinus');

// �摜URL�̐�ǂ݃T���l�C����\��(����:1, ���Ȃ�:0)
$conf_user_def['preview_thumbnail'] = 0; // (0)
$conf_user_sel['preview_thumbnail'] = array('1' => '����', '0' => '���Ȃ�');

// �摜URL�̐�ǂ݃T���l�C������x�ɕ\�����鐧����
$conf_user_def['pre_thumb_limit'] = 7; // (7)
$conf_user_rules['pre_thumb_limit'] = array('IntExceptMinus');

// �摜�T���l�C���̏c�̑傫�����w��i�s�N�Z���j
$conf_user_def['pre_thumb_height'] = "32"; // ("32")

// �摜�T���l�C���̉��̑傫�����w��i�s�N�Z���j
$conf_user_def['pre_thumb_width'] = "32"; // ("32")

// HTML�|�b�v�A�b�v�i����:1, ���Ȃ�:0, p�ł���:2, �摜�ł���:3�j
$conf_user_def['iframe_popup'] = 2; // (2)
$conf_user_sel['iframe_popup'] = array('1' => '����', '0' => '���Ȃ�', '2' => 'p�ł���', '3' => '�摜�ł���');

// HTML�|�b�v�A�b�v�̕\���x�����ԁi�b�j
$conf_user_def['iframe_popup_delay'] = 0.2; // (0.2)

// ID:xxxxxxxx��ID�t�B���^�����O�̃����N�ɕϊ��i����:1, ���Ȃ�:0�j
$conf_user_def['flex_idpopup'] = 1; // (1)
$conf_user_sel['flex_idpopup'] = array('1' => '����', '0' => '���Ȃ�');

// �O���T�C�g���փW�����v���鎞�ɊJ���E�B���h�E�̃^�[�Q�b�g���i����:"", �V��:"_blank"�j
$conf_user_def['ext_win_target'] = "_blank"; // ("_blank")

// p2�Ή�BBS�T�C�g���ŃW�����v���鎞�ɊJ���E�B���h�E�̃^�[�Q�b�g���i����:"", �V��:"_blank"�j
$conf_user_def['bbs_win_target'] = ""; // ("")

// �X���b�h�����ɏ������݃t�H�[����\���i����:1, ���Ȃ�:0�j
$conf_user_def['bottom_res_form'] = 1; // (1)
$conf_user_sel['bottom_res_form'] = array('1' => '����', '0' => '���Ȃ�');

// ���p���X��\���i����:1, ���Ȃ�:0�j
$conf_user_def['quote_res_view'] = 1; // (1)
$conf_user_sel['quote_res_view'] = array('1' => '����', '0' => '���Ȃ�');

// �g�щ{�����A��x�ɕ\�����郌�X�̐�
$conf_user_def['k_rnum_range'] = 15; // (15)
$conf_user_rules['k_rnum_range'] = array('NotEmpty', 'IntExceptMinus');

// �g�щ{�����A��̃��X�̍ő�\���T�C�Y
$conf_user_def['ktai_res_size'] = 600; // (600)
$conf_user_rules['ktai_res_size'] = array('NotEmpty', 'IntExceptMinus');

// �g�щ{�����A���X���ȗ������Ƃ��̕\���T�C�Y
$conf_user_def['ktai_ryaku_size'] = 120; // (120)
$conf_user_rules['ktai_ryaku_size'] = array('IntExceptMinus');

// �g�щ{�����A�|�C���^�̉��R�O�̃��X����\�����邩
$conf_user_def['before_respointer_k'] = 0; // (0)
$conf_user_rules['before_respointer_k'] = array('IntExceptMinus');

// �g�щ{�����A�O�������N�ɒʋ΃u���E�U(��)�𗘗p(����:1, ���Ȃ�:0)
$conf_user_def['k_use_tsukin'] = 1; // (1)
$conf_user_sel['k_use_tsukin'] = array('1' => '����', '0' => '���Ȃ�');

// �g�щ{�����A�摜�����N��pic.to(��)�𗘗p(����:1, ���Ȃ�:0)
$conf_user_def['k_use_picto'] = 1; // (1)
$conf_user_sel['k_use_picto'] = array('1' => '����', '0' => '���Ȃ�');

// �g�щ{�����A�f�t�H���g�̖���������\���i����:1, ���Ȃ�:0�j
$conf_user_def['k_bbs_noname_name'] = 0; // (0)
$conf_user_sel['k_bbs_noname_name'] = array('1' => '����', '0' => '���Ȃ�');

// �g�щ{�����A�u�ʁv�̃R�s�[�p�e�L�X�g�{�b�N�X�𕪊����镶����
$conf_user_def['k_copy_divide_len'] = 0; // (0)

// }}}
// {{{ ��ETC

// ���X�������ݎ��̃f�t�H���g�̖��O
$conf_user_def['my_FROM'] = ""; // ("")

// ���X�������ݎ��̃f�t�H���g��mail
$conf_user_def['my_mail'] = "sage"; // ("sage")

// PC�{�����A�\�[�X�R�[�h�̃R�s�y�ɓK�����␳������`�F�b�N�{�b�N�X��\���i����:1, ���Ȃ�:0, pc�I�̂�:2�j
$conf_user_def['editor_srcfix'] = 0; // (0)
$conf_user_sel['editor_srcfix'] = array('1' => '����', '0' => '���Ȃ�', '2' => 'pc�I�̂�');

// �V�����X���b�h���擾�������ɕ\�����郌�X��(�S�ĕ\������ꍇ:"all")
$conf_user_def['get_new_res'] = 200; // (200)

// �ŋߓǂ񂾃X���̋L�^��
$conf_user_def['rct_rec_num'] = 50; // (50)
$conf_user_rules['rct_rec_num'] = array('IntExceptMinus');

// �������ݗ����̋L�^��
$conf_user_def['res_hist_rec_num'] = 20; // (20)
$conf_user_rules['res_hist_rec_num'] = array('IntExceptMinus');

// �������ݓ��e���O���L�^(����:1, ���Ȃ�:0)
$conf_user_def['res_write_rec'] = 1; // (1)
$conf_user_sel['res_write_rec'] = array('1' => '����', '0' => '���Ȃ�');

// �O��URL�W�����v����ۂɒʂ��Q�[�g�B
// �i����:"", p2 ime(�����]��):"p2", p2 ime(�蓮�]��):"p2m", p2 ime(p�̂ݎ蓮�]��):"p2pm"�j
$conf_user_def['through_ime'] = "p2pm"; // ("p2pm") 
$conf_user_sel['through_ime'] = array(
    '' => '����', 'p2' => 'p2 ime(�����]��)', 'p2m' => 'p2 ime(�蓮�]��)', 'p2pm' => 'p2 ime(p�̂ݎ蓮�]��)'
);

// ���C�ɃX�����L�ɎQ���i����:1, ���Ȃ�:0�j
$conf_user_def['join_favrank'] = 0; // (0)
$conf_user_sel['join_favrank'] = array('1' => '����', '0' => '���Ȃ�');

// ���j���[�ɐV������\���i����:1, ���Ȃ�:0, ���C�ɔ̂�:2�j
$conf_user_def['enable_menu_new'] = 1; // (0)
$conf_user_sel['enable_menu_new'] = array('1' => '����', '0' => '���Ȃ�', '2' => '���C�ɔ̂�');

// ���j���[�����̎����X�V�Ԋu�i���w��B0�Ȃ玩���X�V���Ȃ��B�j
$conf_user_def['menu_refresh_time'] = 0; // (0)

// �u���N���`�F�b�J (����:1, ���Ȃ�:0)
$conf_user_def['brocra_checker_use'] = 0; // (0)
$conf_user_sel['brocra_checker_use'] = array('1' => '����', '0' => '���Ȃ�');

// �u���N���`�F�b�JURL
$conf_user_def['brocra_checker_url'] = "http://www.jah.ne.jp/~fild/cgi-bin/LBCC/lbcc.cgi";

// �u���N���`�F�b�J�̃N�G���[
$conf_user_def['brocra_checker_query'] = "url";

// �t�B���^�����O��AND/OR�������\�ɂ���ioff:0, ���X�̂�:1, �T�u�W�F�N�g��:2�j
$conf_user_def['enable_exfilter'] = 2; // (2)
$conf_user_sel['enable_exfilter'] = array('1' => '���X�݂̂���', '0' => '���Ȃ�', '2' => '���X�A�T�u�W�F�N�g�Ƃ�����');

// �g�щ{�����A�p�P�b�g�ʂ����炷���߁A�S�p�p���E�J�i�E�X�y�[�X�𔼊p�ɕϊ� (����:1, ���Ȃ�:0)
$conf_user_def['k_save_packet'] = 1; // (1) 
$conf_user_sel['k_save_packet'] = array('1' => '����', '0' => '���Ȃ�');

// ���̊��ԁANG���ځ[���HIT���Ȃ���΁A�o�^���[�h�������I�ɊO���i�����j
$conf_user_def['ngaborn_daylimit'] = 180; // (180)
$conf_user_rules['ngaborn_daylimit'] = array('NotEmpty', 'IntExceptMinus');

// �v���L�V�𗘗p(����:1, ���Ȃ�:0)
$conf_user_def['proxy_use'] = 0; // (0)
$conf_user_sel['proxy_use'] = array('1' => '����', '0' => '���Ȃ�');

// �v���L�V�z�X�g ex)"127.0.0.1", "www.p2proxy.com"
$conf_user_def['proxy_host'] = ""; // ("")

// �v���L�V�|�[�g ex)"8080"
$conf_user_def['proxy_port'] = ""; // ("")

// �t���[�� menu �̕\����
$conf_user_def['frame_menu_width'] = "156"; // ("156")

// �t���[�� subject �̕\����
$conf_user_def['frame_subject_width'] = "40%"; // ("40%")

// �t���[�� read �̕\����
$conf_user_def['frame_read_width'] = "60%"; // ("40%") 


// �����O�C�����A�܂���openssl�Ŏ��݂�B��PHP 4.3.0�ȍ~�ŁAOpenSSL���ÓI�Ƀ����N����Ă���K�v������
$conf_user_def['precede_openssl'] = 0;  // (0)
$conf_user_sel['precede_openssl'] = array('1' => 'Yes', '0' => 'No');

// curl���g�����A�R�}���h���C���ł�PHP�֐��łǂ����D�悷�邩 (�R�}���h���C����:0, PHP�֐���:1)
$conf_user_def['precede_phpcurl'] = 0;  // (0)
$conf_user_sel['precede_phpcurl'] = array('0' => '�R�}���h���C����', '1' => 'PHP�֐���');

// }}}
?>
