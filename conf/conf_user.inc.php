<?php
/*
	p2 - ���[�U��`�p �ݒ�t�@�C��
	�R�����g�`���� () ���̓f�t�H���g�l
*/

// p2 �F�ؐݒ� ====================================================
// �K�����̔F�؂��I���ɂ��邩�A��O�҂ɃA�N�Z�X����Ȃ��悤�Ɏ��ȑ΍���{������
$login['use'] = 1;	// (1) Basic�F�؂𗘗p (����:1, ���Ȃ�:0)

// be.2ch.net�A�J�E���g ===========================================
$_conf['be_2ch_code'] = "";	// ("") be.2ch.net�̔F�؃R�[�h(�p�X���[�h�ł͂Ȃ�)
$_conf['be_2ch_mail'] = "";	// ("") be.2ch.net�̓o�^���[���A�h���X

// PATH ===========================================================
// �擾�X���b�h��dat & idx �f�[�^�ۑ��f�B���N�g��(�p�[�~�b�V������707��) 
$datdir = "./data";	// ("./data")

// �����ݒ�f�[�^�ۑ��f�B���N�g��(�p�[�~�b�V������707��) 
$_conf['pref_dir'] = "./data";	// ("./data")

$_conf['first_page'] = "first_cont.php";	// ("first_cont.php") �E�������ɍŏ��ɕ\�������y�[�W�B�I�����C��URL���B

/*
���X�g�̓I�����C���ƃ��[�J���̗�������ǂݍ��߂�
�I�����C���� $_conf['brdfile_online'] �Őݒ�
���[�J���� ./board �f�B���N�g�����쐬���A���̒���brd�t�@�C����u���i�����j
*/

/* ���X�g���I�����C��URL($_conf['brdfile_online'])���玩���œǂݍ��ށB
�w���� menu.html �`���A2channel.brd �`���̂ǂ���ł��悢�B
�K�v�Ȃ���΁A���w��("")�ɂ��邩�A�R�����g�A�E�g���Ă����B */
// ("http://azlucky.s25.xrea.com/2chboard/bbsmenu.html")	//2ch + �O��BBS
// ("http://www6.ocn.ne.jp/%7Emirv/2chmenu.html")	//2ch��{
$_conf['brdfile_online'] = "http://azlucky.s25.xrea.com/2chboard/bbsmenu.html";

// subject ==========================================================
$_conf['refresh_time'] = 0;	// (0) �X���b�h�ꗗ�̎����X�V�Ԋu�B�i���w��B0�Ȃ玩���X�V���Ȃ��B�j
$_conf['sb_show_motothre'] = 1;	// (1) �X���b�h�ꗗ�Ŗ��擾�X���ɑ΂��Č��X���ւ̃����N�i�E�j��\�� (����:1, ���Ȃ�:0)
$_conf['sb_show_one'] = 0;	// (0) �X���b�h�ꗗ�i�\���j��>>1��\�� (����:1, ���Ȃ�:0, �j���[�X�n�̂�:2)
$_conf['sb_show_spd'] = 0;	// (0) �X���b�h�ꗗ�ł��΂₳��\�� (����:1, ���Ȃ�:0)
$_conf['sb_show_ikioi'] = 1;	// (1) �X���b�h�ꗗ�Ő����i1��������̃��X���j��\�� (����:1, ���Ȃ�:0)
$_conf['sb_show_fav'] = 0;	// (0) �X���b�h�ꗗ�ł��C�ɃX���}�[�N����\�� (����:1, ���Ȃ�:0)

$_conf['sb_sort_ita'] = 'ikioi';	// ('ikioi') �\���̃X���b�h�ꗗ�ł̃f�t�H���g�̃\�[�g�w��
// (�V��:'midoku', ���X:'res', No.:'no', �^�C�g��:'title', ���΂₳:'spd', ����:'ikioi', Birthday:'bd', ���C�ɃX��:'fav')
	
$_conf['sort_zero_adjust'] = 0.1;	// (0.1) �V���\�[�g�ł́u�����Ȃ��v�́u�V�����[���v�ɑ΂���\�[�g�D�揇�� (���:0.1, ����:0, ����:-0.1)
$_conf['cmp_dayres_midoku'] = 1;	// (1) �����\�[�g���ɐV�����X�̂���X����D�� (����:1, ���Ȃ�:0)
$_conf['k_sb_disp_range'] = 30;	// (30) �g�щ{�����A��x�ɕ\������X���̐�
$_conf['viewall_kitoku'] = 1;	// (1) �����X���͕\�������Ɋւ�炸�\�� (����:1, ���Ȃ�:0)
$_conf['sb_dl_interval'] = 300;	// (300) subject.txt �̃L���b�V�����X�V�����ɕێ����鎞�� (�b)

// read =============================================================
$_conf['respointer'] = 1;	// (1) �X�����e�\�����A���ǂ̉��R�O�̃��X�Ƀ|�C���^�����킹�邩
$_conf['before_respointer'] = 20;	// (20) PC�{�����A�|�C���^�̉��R�O�̃��X����\�����邩
$_conf['before_respointer_new'] = 0;	// (0) �V���܂Ƃߓǂ݂̎��A�|�C���^�̉��R�O�̃��X����\�����邩
$_conf['rnum_all_range'] = 200;	// (200) �V���܂Ƃߓǂ݂ň�x�ɕ\�����郌�X��
$_conf['preview_thumbnail'] = 0;	// (0) �摜URL�̐�ǂ݃T���l�C�� (�\������:1, ���Ȃ�:0)
$_conf['pre_thumb_limit'] = 7;	// (7) �摜URL�̐�ǂ݃T���l�C������x�ɕ\�����鐧����
$_conf['pre_thumb_height'] = "32";	// ("32") �摜�T���l�C���̏c�̑傫�����w��i�s�N�Z���j
$_conf['pre_thumb_width'] = "32";	// ("32") �摜�T���l�C���̉��̑傫�����w��i�s�N�Z���j
$_conf['iframe_popup'] = 2;	// (2) HTML�|�b�v�A�b�v�i����:1, ���Ȃ�:0, p�ł���:2�j
$_conf['iframe_popup_delay'] = 0.2;	// (0.2) HTML�|�b�v�A�b�v�̕\���x�����ԁi�b�j
$_conf['ext_win_target'] = "_blank";	// ("") �O���T�C�g���փW�����v���鎞�ɊJ���E�B���h�E�̃^�[�Q�b�g���i����:"", �V��:"_blank"�j
$_conf['bbs_win_target'] = "";	// ("") p2�Ή�BBS�T�C�g���ŃW�����v���鎞�ɊJ���E�B���h�E�̃^�[�Q�b�g���i����:"", �V��:"_blank"�j
$_conf['bottom_res_form'] = 1;	// (1) �X���b�h�����ɏ������݃t�H�[����\���i����:1, ���Ȃ�:0�j
$_conf['quote_res_view'] = 1;	// (1) ���p���X��\���i����:1, ���Ȃ�:0�j
$_conf['k_rnum_range'] = 15;	// (15) �g�щ{�����A��x�ɕ\�����郌�X�̐�
$_conf['ktai_res_size'] = 600; 		// (600) �g�їp�A��̃��X�̍ő�\���T�C�Y
$_conf['ktai_ryaku_size'] = 120; 	// (120) �g�їp�A���X���ȗ������Ƃ��̕\���T�C�Y
$_conf['before_respointer_k'] = 0;	// (0) �g�щ{�����A�|�C���^�̉��R�O�̃��X����\�����邩
$_conf['k_use_tsukin'] = 1;	// (1) �g�щ{�����A�O�������N�ɒʋ΃u���E�U(��)�𗘗p(����:1, ���Ȃ�:0)
$_conf['k_use_picto'] = 1;	// (1) �g�щ{�����A�摜�����N��pic.to(��)�𗘗p(����:1, ���Ȃ�:0)

// ETC ==============================================================
$_conf['my_FROM'] = "";	// ("") ���X�������ݎ��̃f�t�H���g�̖��O
$_conf['my_mail'] = "sage";	// ("sage") ���X�������ݎ��̃f�t�H���g��mail

// PC�{�����A�\�[�X�R�[�h�̃R�s�y�ɓK�����␳������`�F�b�N�{�b�N�X��\���i����:1, ���Ȃ�:0, pc�I�̂�:2�j
$_conf['editor_srcfix'] = 0; // (0)

$_conf['get_new_res'] = 200;	// (200) �V�����X���b�h���擾�������ɕ\�����郌�X��(�S�ĕ\������ꍇ:"all")
$_conf['rct_rec_num'] = 20;	// (20) �ŋߓǂ񂾃X���̋L�^��
$_conf['res_hist_rec_num'] = 20;	// (20) �������ݗ����̋L�^��
$_conf['res_write_rec'] = 1;	// (1) �������ݓ��e���L�^(����:1, ���Ȃ�:0)
$_conf['updatan_haahaa'] = 1;	// (1) p2�̍ŐV�o�[�W�����������`�F�b�N(����:1, ���Ȃ�:0)
$_conf['through_ime'] = "p2pm";	// ("p2pm") �O��URL�W�����v����ۂɒʂ��Q�[�g�B�i����:"", p2 ime(�����]��):"p2", p2 ime(�蓮�]��):"p2m", p2 ime(p�̂ݎ蓮�]��):"p2pm"�j
$_conf['join_favrank'] = 0;	// (0) ���C�ɃX�����L�ɎQ���i����:1, ���Ȃ�:0�j
$_conf['enable_menu_new'] = 0;	// (0) ���j���[�ɐV������\���i����:1, ���Ȃ�:0, ���C�ɔ̂�:2�j
$_conf['menu_refresh_time'] = 0;	// (0) ���j���[�����̎����X�V�Ԋu�i���w��B0�Ȃ玩���X�V���Ȃ��B�j
$_conf['brocra_checker_use'] = 0;	// (0) �u���N���`�F�b�J (����:1, ���Ȃ�:0)
$_conf['brocra_checker_url'] = "http://www.jah.ne.jp/~fild/cgi-bin/LBCC/lbcc.cgi"; // �u���N���`�F�b�JURL
$_conf['brocra_checker_query'] = "url";	// �u���N���`�F�b�J�̃N�G���[

// �g�щ{�����A�p�P�b�g�ʂ����炷���߁A�S�p�p���E�J�i�E�X�y�[�X�𔼊p�ɕϊ� (����:1, ���Ȃ�:0)
$_conf['k_save_packet'] = 1;	// (1) 

$_conf['enable_exfilter'] = 1;	// (1) �t�B���^�����O��AND/OR�������\�ɂ���ioff:0, ���X�̂�:1, �T�u�W�F�N�g��:2�j
$_conf['flex_idpopup'] = 1;	// (1) ID:xxxxxxxx��ID�t�B���^�����O�̃����N�ɕϊ��ioff:0, on:1�j
$_conf['precede_phpcurl'] = 0;		// (0) curl���g�����A�R�}���h���C���łƊ֐��łǂ����D�悷�邩 (�R�}���h���C��:0, �֐�:1)
$_conf['ngaborn_daylimit'] = 180;	// (180) ���̊��ԁANG���ځ[���HIT���Ȃ���΁A�o�^���[�h�������I�ɊO���i�����j

$_conf['proxy_use'] = 0;	// (0) �v���L�V�𗘗p(����:1, ���Ȃ�:0)
$_conf['proxy_host'] = "";	// ("") �v���L�V�z�X�g ex)"127.0.0.1", "www.p2proxy.com"
$_conf['proxy_port'] = "";	// ("") �v���L�V�|�[�g ex)"8080"

?>
