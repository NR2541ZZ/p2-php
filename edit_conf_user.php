<?php
/*
    p2 - ���[�U�ݒ�ҏWUI
*/

include_once './conf/conf.inc.php';
require_once P2_LIBRARY_DIR . '/dataphp.class.php';

$_login->authorize(); // ���[�U�F��

if (!empty($_POST['submit_save']) || !empty($_POST['submit_default'])) {
    if (!isset($_POST['csrfid']) or $_POST['csrfid'] != P2Util::getCsrfId()) {
        P2Util::printSimpleHtml("p2 error: �s���ȃ|�X�g�ł�");
        die('');
    }
}

define('P2_EDIT_CONF_USER_DEFAULT',     0);
define('P2_EDIT_CONF_USER_LONGTEXT',    1);
define('P2_EDIT_CONF_USER_HIDDEN',      2);
define('P2_EDIT_CONF_USER_DISABLED',    4);
define('P2_EDIT_CONF_USER_SKIPPED',     8);
define('P2_EDIT_CONF_FILE_ADMIN',    1024);
define('P2_EDIT_CONF_FILE_ADMIN_EX', 2048);

//=====================================================================
// �O����
//=====================================================================

// {{{ �ۑ��{�^����������Ă�����A�ݒ��ۑ�

if (!empty($_POST['submit_save'])) {

    // �l�̓K���`�F�b�N�A����

    // �g����
    $_POST['conf_edit'] = array_map('trim', $_POST['conf_edit']);

    // �I�����ɂȂ����� �� �f�t�H���g����
    notSelToDef();

    // ���[����K�p����
    applyRules();

    // ���̎��� or 0 �łȂ����� �� �f�t�H���g����
    //notFloatExceptMinusToDef();

    /**
     * �f�t�H���g�l $conf_user_def �ƕύX�l $_POST['conf_edit'] �̗��������݂��Ă��āA
     * �f�t�H���g�l�ƕύX�l���قȂ�ꍇ�̂ݐݒ�ۑ�����i���̑��̃f�[�^�͕ۑ����ꂸ�A�j�������j
     * �������A$_POST['conf_keep_old'] == true �̂Ƃ��̓f�[�^��j�����Ȃ��i�������̏��Ȃ��g�ё΍�j
     */
    $conf_save = array();
    foreach ($conf_user_def as $k => $v) {
        if (isset($_POST['conf_edit'][$k])) {
            if ($v != $_POST['conf_edit'][$k]) {
                $conf_save[$k] = $_POST['conf_edit'][$k];
            }
        } elseif (!empty($_POST['conf_keep_old']) && isset($_conf[$k])) {
            if ($v != $_conf[$k]) {
                $conf_save[$k] = $_conf[$k];
            }

        // ���ʁiedit_conf_user.php �ȊO�ł��ݒ肳�ꂤ����͎̂c���j
        } elseif (in_array($k, array('maru_kakiko'))) {
            $conf_save[$k] = $_conf[$k];
        }
    }

    // �V���A���C�Y���ĕۑ�
    FileCtl::make_datafile($_conf['conf_user_file'], $_conf['conf_user_perm']);
    if (file_put_contents($_conf['conf_user_file'], serialize($conf_save), LOCK_EX) === false) {
        P2Util::pushInfoHtml("<p>�~�ݒ���X�V�ۑ��ł��܂���ł���</p>");
        trigger_error("file_put_contents(" . $_conf['conf_user_file'] . ")", E_USER_WARNING);
    } else {
        P2Util::pushInfoHtml("<p>���ݒ���X�V�ۑ����܂���</p>");
        // �ύX������΁A�����f�[�^���X�V���Ă���
        $_conf = array_merge($_conf, $conf_user_def);
        if (is_array($conf_save)) {
            $_conf = array_merge($_conf, $conf_save);
        }
    }

// }}}
// {{{ �f�t�H���g�ɖ߂��{�^����������Ă�����

} elseif (!empty($_POST['submit_default'])) {
    if (file_exists($_conf['conf_user_file']) and unlink($_conf['conf_user_file'])) {
        P2Util::pushInfoHtml("<p>���ݒ���f�t�H���g�ɖ߂��܂���</p>");
        // �ύX������΁A�����f�[�^���X�V���Ă���
        $_conf = array_merge($_conf, $conf_user_def);
        if (is_array($conf_save)) {
            $_conf = array_merge($_conf, $conf_save);
        }
    }
}

// }}}
// {{{ �g�тŕ\������O���[�v

if ($_conf['ktai']) {
    if (isset($_POST['edit_conf_user_group_en'])) {
        $selected_group = base64_decode($_POST['edit_conf_user_group_en']);
    } elseif (isset($_POST['edit_conf_user_group'])) {
        $selected_group = $_POST['edit_conf_user_group'];
    } elseif (isset($_GET['edit_conf_user_group_en'])) {
        $selected_group = base64_decode($_GET['edit_conf_user_group_en']);
    } elseif (isset($_GET['edit_conf_user_group'])) {
        $selected_group = $_GET['edit_conf_user_group'];
    } else {
        $selected_group = null;
    }
} else {
    $selected_group = 'all';
}

$groups = array();
$keep_old = false;

// }}}

//=====================================================================
// �v�����g�ݒ�
//=====================================================================
$ptitle = '���[�U�ݒ�ҏW';

$csrfid = P2Util::getCsrfId();

$me = P2Util::getMyUrl();

//=====================================================================
// �v�����g
//=====================================================================
// �w�b�_HTML���v�����g
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$ptitle}</title>\n
EOP;

if (!$_conf['ktai']) {
    echo <<<EOP
    <script type="text/javascript" src="js/basic.js?{$_conf['p2expack']}"></script>
    <script type="text/javascript" src="js/tabber/tabber.js?{$_conf['p2expack']}"></script>
    <script type="text/javascript" src="js/edit_conf_user.js?{$_conf['p2expack']}"></script>
    <link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="style/tabber/tabber.css?{$_conf['p2expack']}" type="text/css">
    <link rel="stylesheet" href="css.php?css=edit_conf_user&amp;skin={$skin_en}" type="text/css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">\n
EOP;
}

$body_at = ($_conf['ktai']) ? $_conf['k_colors'] : '';
echo <<<EOP
</head>
<body{$body_at}>\n
EOP;

// PC�p�\��
if (!$_conf['ktai']) {
    echo <<<EOP
<p id="pan_menu"><a href="editpref.php">�ݒ�Ǘ�</a> &gt; {$ptitle} �i<a href="{$me}">�����[�h</a>�j</p>\n
EOP;
}

// �g�їp�\��
if ($_conf['ktai']) {
    $htm['form_submit'] = <<<EOP
<input type="submit" name="submit_save" value="�ύX��ۑ�����">\n
EOP;
}

// ��񃁃b�Z�[�W�\��
P2Util::printInfoHtml();

echo <<<EOP
<form id="edit_conf_user_form" method="POST" action="{$_SERVER['SCRIPT_NAME']}" target="_self" accept-charset="{$_conf['accept_charset']}">
    {$_conf['detect_hint_input_ht']}
    <input type="hidden" name="csrfid" value="{$csrfid}">
    {$_conf['k_input_ht']}\n
EOP;

// PC�p�\��
if (!$_conf['ktai']) {
    echo <<<EOP
<div class="tabber">
<div class="tabbertab" title="rep2��{�ݒ�">
<h3>rep2��{�ݒ�</h3>
<div class="tabber">\n
EOP;
// �g�їp�\��
} else {
    if (!empty($selected_group)) {
        echo $htm['form_submit'];
    }
}

// {{{ rep2��{�ݒ�
// {{{ be.2ch.net �A�J�E���g

$groupname = 'be.2ch.net �A�J�E���g';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('be_2ch_code', '<a href="http://be.2ch.net/" target="_blank">be.2ch.net</a>�̔F�؃R�[�h(�p�X���[�h�ł͂Ȃ�)', P2_EDIT_CONF_USER_LONGTEXT),
        array('be_2ch_mail', 'be.2ch.net�̓o�^���[���A�h���X', P2_EDIT_CONF_USER_LONGTEXT),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ PATH

$groupname = 'PATH';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
//        array('first_page', '�E�������ɍŏ��ɕ\�������y�[�W�B�I�����C��URL���B'),
        array('brdfile_online',
'���X�g�̎w��i�I�����C��URL�j<br>
���X�g���I�����C��URL���玩���œǂݍ��ށB
�w���� menu.html �`���A2channel.brd �`���̂ǂ���ł��悢�B
<!-- �K�v�Ȃ���΁A�󔒂ɁB --><br>
2ch��{ <a href="http://menu.2ch.net/bbsmenu.html" target="_blank">http://menu.2ch.net/bbsmenu.html</a><br>
2ch + �O��BBS <a href="http://azlucky.s25.xrea.com/2chboard/bbsmenu.html" target="_blank">http://azlucky.s25.xrea.com/2chboard/bbsmenu.html</a>',
            P2_EDIT_CONF_USER_LONGTEXT),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ subject

$groupname = 'subject';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('refresh_time', '�X���b�h�ꗗ�̎����X�V�Ԋu (���w��B0�Ȃ玩���X�V���Ȃ�)'),

        array('sb_show_motothre', '�X���b�h�ꗗ�Ŗ��擾�X���ɑ΂��Č��X���ւ̃����N�i�E�j��\�� (����, ���Ȃ�)'),
        array('sb_show_one', 'PC�{�����A�X���b�h�ꗗ�i�\���j��&gt;&gt;1��\�� (����, ���Ȃ�, �j���[�X�n�̂�)'),
        array('k_sb_show_first', '�g�т̃X���b�h�ꗗ�i�\���j���珉�߂ẴX�����J�����̕\�����@ (����ޭ�&gt;&gt;1, 1����N���\��, �ŐVN���\��)'),
        array('sb_show_spd', '�X���b�h�ꗗ�ł��΂₳�i���X�Ԋu�j��\�� (����, ���Ȃ�)'),
        array('sb_show_ikioi', '�X���b�h�ꗗ�Ő����i1��������̃��X���j��\�� (����, ���Ȃ�)'),
        array('sb_show_fav', '�X���b�h�ꗗ�ł��C�ɃX���}�[�N����\�� (����, ���Ȃ�)'),
        array('sb_sort_ita', '�\���̃X���b�h�ꗗ�ł̃f�t�H���g�̃\�[�g�w��'),
        array('sort_zero_adjust', '�V���\�[�g�ł́u�����Ȃ��v�́u�V�����[���v�ɑ΂���\�[�g�D�揇�� (���, ����, ����)'),
        array('cmp_dayres_midoku', '�����\�[�g���ɐV�����X�̂���X����D�� (����, ���Ȃ�)'),
        array('k_sb_disp_range', '�g�щ{�����A��x�ɕ\������X���̐�'),
        array('viewall_kitoku', '�����X���͕\�������Ɋւ�炸�\�� (����, ���Ȃ�)'),

        array('sb_ttitle_max_len', 'PC�{�����A�X���b�h�ꗗ�ŕ\������^�C�g���̒����̏�� (0�Ŗ�����)'),
        array('sb_ttitle_trim_len', 'PC�{�����A�X���b�h�^�C�g���������̏�����z�����Ƃ��A���̒����܂Ő؂�l�߂�'),
        array('sb_ttitle_trim_pos', 'PC�{�����A�X���b�h�^�C�g����؂�l�߂�ʒu (�擪, ����, ����)'),
        array('sb_ttitle_max_len_k', '�g�щ{�����A�X���b�h�ꗗ�ŕ\������^�C�g���̒����̏�� (0�Ŗ�����)'),
        array('sb_ttitle_trim_len_k', '�g�щ{�����A�X���b�h�^�C�g���������̏�����z�����Ƃ��A���̒����܂Ő؂�l�߂�'),
        array('sb_ttitle_trim_pos_k', '�g�щ{�����A�X���b�h�^�C�g����؂�l�߂�ʒu (�擪, ����, ����)'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ read

$groupname = 'read';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('respointer', '�X�����e�\�����A���ǂ̉��R�O�̃��X�Ƀ|�C���^�����킹�邩'),
        array('before_respointer', 'PC�{�����A�|�C���^�̉��R�O�̃��X����\�����邩'),
        array('before_respointer_new', '�V���܂Ƃߓǂ݂̎��A�|�C���^�̉��R�O�̃��X����\�����邩'),
        array('rnum_all_range', '�V���܂Ƃߓǂ݂ň�x�ɕ\�����郌�X��'),
        array('preview_thumbnail', '�摜URL�̐�ǂ݃T���l�C����\�� (����, ���Ȃ�)'),
        array('pre_thumb_limit', '�摜URL�̐�ǂ݃T���l�C������x�ɕ\�����鐧���� (0�Ŗ�����)'),
//        array('pre_thumb_height', '�摜�T���l�C���̏c�̑傫�����w�� (�s�N�Z��)'),
//        array('pre_thumb_width', '�摜�T���l�C���̉��̑傫�����w�� (�s�N�Z��)'),
        array('link_youtube', 'YouTube�̃����N���v���r���[�\���i����, ���Ȃ�)'),
        array('iframe_popup', 'HTML�|�b�v�A�b�v (����, ���Ȃ�, p�ł���, �摜�ł���)'),
//        array('iframe_popup_delay', 'HTML�|�b�v�A�b�v�̕\���x������ (�b)'),
        array('flex_idpopup', 'ID:xxxxxxxx��ID�t�B���^�����O�̃����N�ɕϊ� (����, ���Ȃ�)'),
        array('ext_win_target', '�O���T�C�g���փW�����v���鎞�ɊJ���E�B���h�E�̃^�[�Q�b�g�� (����:&quot;&quot;, �V��:&quot;_blank&quot;)'),
        array('bbs_win_target', 'p2�Ή�BBS�T�C�g���ŃW�����v���鎞�ɊJ���E�B���h�E�̃^�[�Q�b�g�� (����:&quot;&quot;, �V��:&quot;_blank&quot;)'),
        array('bottom_res_form', '�X���b�h�����ɏ������݃t�H�[����\�� (����, ���Ȃ�)'),
        array('quote_res_view', '���p���X��\�� (����, ���Ȃ�)'),

        array('k_rnum_range', '�g�щ{�����A��x�ɕ\�����郌�X�̐�'),
        array('ktai_res_size', '�g�щ{�����A��̃��X�̍ő�\���T�C�Y'),
        array('ktai_ryaku_size', '�g�щ{�����A���X���ȗ������Ƃ��̕\���T�C�Y'),
        array('k_aa_ryaku_size', '�g�щ{�����AAA�炵�����X���ȗ�����T�C�Y (0�Ȃ疳��)'),
        array('before_respointer_k', '�g�щ{�����A�|�C���^�̉��R�O�̃��X����\�����邩'),
        array('k_use_tsukin', '�g�щ{�����A�O�������N�ɒʋ΃u���E�U(��)�𗘗p (����, ���Ȃ�)'),
        array('k_use_picto', '�g�щ{�����A�摜�����N��pic.to(��)�𗘗p (����, ���Ȃ�)'),

        array('k_bbs_noname_name', '�g�щ{�����A�f�t�H���g�̖���������\�� (����, ���Ȃ�)'),
        array('k_clip_unique_id', '�g�щ{�����A�d�����Ȃ�ID�͖����݂̂̏ȗ��\�� (����, ���Ȃ�)'),
        array('k_date_zerosuppress', '�g�щ{�����A���t��0���ȗ��\�� (����, ���Ȃ�)'),
        array('k_clip_time_sec', '�g�щ{�����A�����̕b���ȗ��\�� (����, ���Ȃ�)'),
        array('k_copy_divide_len', '�g�щ{�����A�u�ʁv�̃R�s�[�p�e�L�X�g�{�b�N�X�𕪊����镶����'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ NG/���ځ[��

$groupname = 'NG/���ځ[��';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('ngaborn_frequent', '&gt;&gt;1 �ȊO�̕p�oID�����ځ[�񂷂� (����, ���Ȃ�, NG�ɂ���)'),
        array('ngaborn_frequent_one', '&gt;&gt;1 ���p�oID���ځ[��̑ΏۊO�ɂ��� (����, ���Ȃ�)'),
        array('ngaborn_frequent_num', '�p�oID���ځ[��̂������l (�o���񐔂�����ȏ��ID�����ځ[��)'),
        array('ngaborn_frequent_dayres', '�����̑����X���ł͕p�oID���ځ[�񂵂Ȃ� (�����X��/�X�����Ă���̓����A0�Ȃ疳��)'),
        array('ngaborn_chain', '�A��NG���ځ[�� (����, ���Ȃ�, ���ځ[�񃌃X�ւ̃��X��NG�ɂ���)<br>�������y�����邽�߁A�\���͈͂̃��X�ɂ����A�����Ȃ�'),
        array('ngaborn_daylimit', '���̊��ԁANG���ځ[���HIT���Ȃ���΁A�o�^���[�h�������I�ɊO�� (����)'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ ETC

$groupname = 'ETC';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('frame_menu_width', '�t���[���� ���j���[ �̕\����'),
        array('frame_subject_width', '�t���[���E�� �X���ꗗ �̕\����'),
        array('frame_read_width', '�t���[���E�� �X���{�� �̕\����'),

        array('my_FROM', '���X�������ݎ��̃f�t�H���g�̖��O'),
        array('my_mail', '���X�������ݎ��̃f�t�H���g��mail'),

        array('editor_srcfix', 'PC�{�����A�\�[�X�R�[�h�̃R�s�y�ɓK�����␳������`�F�b�N�{�b�N�X��\���i����, ���Ȃ�, pc�I�̂݁j'),

        array('get_new_res', '�V�����X���b�h���擾�������ɕ\�����郌�X��(�S�ĕ\������ꍇ:&quot;all&quot;)'),
        array('rct_rec_num', '�ŋߓǂ񂾃X���̋L�^��'),
        array('res_hist_rec_num', '�������ݗ����̋L�^��'),
        array('res_write_rec', '�������ݓ��e���O���L�^ (����, ���Ȃ�)'),
        array('favlist_set_num', '�ǉ����C�ɃX���Z�b�g��'),
        array('favita_set_num', '�ǉ����C�ɔZ�b�g��'),
        array('through_ime', '�O��URL�W�����v����ۂɒʂ��Q�[�g (����, p2 ime(�����]��), p2 ime(�蓮�]��), p2 ime(p�̂ݎ蓮�]��), r.p(�����]��1�b), r.p(�����]��0�b), r.p(�蓮�]��), r.p(p�̂ݎ蓮�]��))'),
        array('ime_manual_ext', '�Q�[�g�Ŏ����]�����Ȃ��g���q�i�J���}��؂�ŁA�g���q�̑O�̃s���I�h�͕s�v�j'),
        array('join_favrank', '<a href="http://akid.s17.xrea.com/favrank/favrank.html" target="_blank">���C�ɃX�����L</a>�ɎQ�� (����, ���Ȃ�)'),
        array('favita_order_dnd', '�h���b�O���h���b�v�ł��C�ɔ���בւ��� (����, ���Ȃ�)'),
        array('enable_menu_new', '���j���[�ɐV������\�� (����, ���Ȃ�, ���C�ɔ̂�)'),
        array('menu_refresh_time', '���j���[�����̎����X�V�Ԋu (���w��B0�Ȃ玩���X�V���Ȃ��B)'),
        array('menu_hide_brds', '�J�e�S���ꗗ�������Ԃɂ��� (����, ���Ȃ�)'),
//        array('brocra_checker_use', '�u���N���`�F�b�J (����, ���Ȃ�)'),
//        array('brocra_checker_url', '�u���N���`�F�b�JURL'),
//        array('brocra_checker_query', '�u���N���`�F�b�J�̃N�G���['),
        array('enable_exfilter', '�t�B���^�����O��AND/OR�������\�ɂ��� (off, ���X�̂�, �T�u�W�F�N�g��)'),
        array('k_save_packet', '�g�щ{�����A�p�P�b�g�ʂ����炷���߁A�S�p�p���E�J�i�E�X�y�[�X�𔼊p�ɕϊ� (����, ���Ȃ�)'),
        array('proxy_use', '�v���L�V�𗘗p (����, ���Ȃ�)'), 
        array('proxy_host', '�v���L�V�z�X�g ex)"127.0.0.1", "www.p2proxy.com"'), 
        array('proxy_port', '�v���L�V�|�[�g ex)"8080"'), 
        array('precede_openssl', '�����O�C�����A�܂���openssl�Ŏ��݂�B��PHP 4.3.0�ȍ~�ŁAOpenSSL���ÓI�Ƀ����N����Ă���K�v������B'),
        array('precede_phpcurl', 'curl���g�����A�R�}���h���C���ł�PHP�֐��łǂ����D�悷�邩 (�R�}���h���C����, PHP�֐���)'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ Mobile Color

$groupname = 'Mobile';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('mobile.background_color', '�w�i'),
        array('mobile.text_color', '��{�����F'),
        array('mobile.link_color', '�����N'),
        array('mobile.vlink_color', '�K��ς݃����N'),
        array('mobile.newthre_color', '�V���X���b�h�}�[�N'),
        array('mobile.ttitle_color', '�X���b�h�^�C�g��'),
        array('mobile.newres_color', '�V�����X�ԍ�'),
        array('mobile.ngword_color', 'NG���[�h'),
        array('mobile.onthefly_color', '�I���U�t���C���X�ԍ�'),
        array('mobile.sage_color', '���[������sage'),
        array('mobile.match_color', '�t�B���^�����O�Ń}�b�`�����L�[���[�h'),
        array('mobile.id_underline', 'ID������&quot;O&quot;�ɉ��������� (����, ���Ȃ�)'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// }}}

// PC�p�\��
if (!$_conf['ktai']) {
    echo <<<EOP
</div><!-- end of tab -->
</div><!-- end of child tabset "rep2��{�ݒ�" -->

<div class="tabbertab" title="�g���p�b�N�ݒ�">
<h3>�g���p�b�N�ݒ�</h3>
<div class="tabber">\n
EOP;
}

// {{{ �g���p�b�N�ݒ�
// {{{ expack - tGrep

$groupname = 'tGrep';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('expack.tgrep.quicksearch', '�ꔭ�����i�\��, ��\���j'),
        array('expack.tgrep.recent_num', '�����������L�^���鐔�i�L�^���Ȃ�:0�j'),
        array('expack.tgrep.recent2_num', '�T�[�`�{�b�N�X�Ɍ����������L�^���鐔�ASafari��p�i�L�^���Ȃ�:0�j'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ expack - �X�}�[�g�|�b�v�A�b�v���j���[

$groupname = 'SPM';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname, 'expack.spm.enabled');
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('expack.spm.kokores', '�����Ƀ��X'),
        array('expack.spm.kokores_orig', '�����Ƀ��X�ŊJ���t�H�[���Ɍ����X�̓��e��\������'),
        array('expack.spm.ngaborn', '���ځ[�񃏁[�h�ENG���[�h�o�^'),
        array('expack.spm.ngaborn_confirm', '���ځ[�񃏁[�h�ENG���[�h�o�^���Ɋm�F����'),
        array('expack.spm.filter', '�t�B���^�����O'),
        array('expack.spm.filter_target', '�t�B���^�����O���ʂ��J���t���[���܂��̓E�C���h�E'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ expack - �A�N�e�B�u���i�[

$groupname = 'ActiveMona';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname, 'expack.am.enabled');
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    if (isset($_conf['expack.am.fontfamily.orig'])) {
        $_current_am_fontfamily = $_conf['expack.am.fontfamily'];
        $_conf['expack.am.fontfamily'] = $_conf['expack.am.fontfamily.orig'];
    }
    $conflist = array(
        array('expack.am.fontfamily', 'AA�p�̃t�H���g'),
        array('expack.am.fontsize', 'AA�p�̕����̑傫��'),
        array('expack.am.display', '�X�C�b�`��\������ʒu'),
        array('expack.am.autodetect', '�����Ŕ��肵�AAA�p�\��������iPC�j'),
        array('expack.am.autong_k', '�����Ŕ��肵�ANG���[�h�ɂ���BAAS ���L���Ȃ� AAS �̃����N���쐬�i�g�сj'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
    if (isset($_conf['expack.am.fontfamily.orig'])) {
        $_conf['expack.am.fontfamily'] = $_current_am_fontfamily;
    }
}

// }}}
// {{{ expack - ���͎x��

$groupname = '���͎x��';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname);
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        //array('expack.editor.constant', '��^�� (�g��, �g��Ȃ�)'),
        array('expack.editor.dpreview', '���A���^�C���E�v���r���[ (���e�t�H�[���̏�ɕ\��, ���e�t�H�[���̉��ɕ\��, ��\��)'),
        array('expack.editor.dpreview_chkaa', '���A���^�C���E�v���r���[��AA�␳�p�̃`�F�b�N�{�b�N�X��\������ (����, ���Ȃ�)'),
        array('expack.editor.check_message', '�{������łȂ����`�F�b�N (����, ���Ȃ�)'),
        array('expack.editor.check_sage', 'sage�`�F�b�N (����, ���Ȃ�)'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ expack - RSS���[�_

$groupname = 'RSS';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname, 'expack.rss.enabled');
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('expack.rss.set_num', '�ǉ��Z�b�g�� (Bloglines �̃t�H���_�̂悤�Ȃ���)'),
        array('expack.rss.check_interval', 'RSS���X�V���ꂽ���ǂ����m�F����Ԋu (���w��)'),
        array('expack.rss.target_frame', 'RSS�̊O�������N���J���t���[���܂��̓E�C���h�E'),
        array('expack.rss.desc_target_frame', '�T�v���J���t���[���܂��̓E�C���h�E'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ expack - ImageCache2

$groupname = 'ImageCache2';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname, 'expack.ic2.enabled');
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('expack.ic2.through_ime', '�L���b�V���Ɏ��s�����Ƃ��̊m�F�p��ime�o�R�Ń\�[�X�ւ̃����N���쐬 (����, ���Ȃ�)'),
        array('expack.ic2.fitimage', '�|�b�v�A�b�v�摜�̑傫�����E�C���h�E�̑傫���ɍ��킹�� (����, ���Ȃ�, �����傫���Ƃ���������, �������傫���Ƃ���������, �蓮�ł���)'),
        array('expack.ic2.pre_thumb_limit_k', '�g�тŃC�����C���E�T���l�C�����L���̂Ƃ��̕\�����鐧���� (0�Ŗ�����)'),
        array('expack.ic2.newres_ignore_limit', '�V�����X�̉摜�� pre_thumb_limit �𖳎����đS�ĕ\�� (����, ���Ȃ�)'),
        array('expack.ic2.newres_ignore_limit_k', '�g�тŐV�����X�̉摜�� pre_thumb_limit_k �𖳎����đS�ĕ\�� (����, ���Ȃ�)'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ expack - Google����

$groupname = 'Google����';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname, 'expack.google.enabled');
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('expack.google.key', 'Google Web APIs �̓o�^�L�[', P2_EDIT_CONF_USER_LONGTEXT),
        //array('expack.google.recent_num', '�����������L�^���鐔�i�L�^���Ȃ�:0�j'),
        array('expack.google.recent2_num', '�T�[�`�{�b�N�X�Ɍ����������L�^���鐔�ASafari��p�i�L�^���Ȃ�:0�j'),
        array('expack.google.force_pear', 'SOAP �G�N�X�e���V���� �����p�\�ȂƂ��� PEAR �� SOAP �p�b�P�[�W���g���iYES, NO�j'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// {{{ expack - AAS

$groupname = 'AAS';
$groups[] = $groupname;
$flags = getGroupShowFlags($groupname, 'expack.aas.enabled');
if ($flags & P2_EDIT_CONF_USER_SKIPPED) {
    $keep_old = true;
} else {
    $conflist = array(
        array('expack.aas.inline', '�g�тŎ��� AA ����ƘA�����A�C�����C���\�� (����, ���Ȃ�)'),
        array('expack.aas.image_type', '�摜�`�� (PNG, JPEG, GIF)'),
        array('expack.aas.jpeg_quality', 'JPEG�̕i�� (0-100)'),
        array('expack.aas.image_width', '�g�їp�̉摜�̉��� (�s�N�Z��)'),
        array('expack.aas.image_height', '�g�їp�̉摜�̍��� (�s�N�Z��)'),
        array('expack.aas.image_width_pc', 'PC�p�̉摜�̉��� (�s�N�Z��)'),
        array('expack.aas.image_height_pc', 'PC�p�̉摜�̍��� (�s�N�Z��)'),
        array('expack.aas.image_width_il', '�C�����C���摜�̉��� (�s�N�Z��)'),
        array('expack.aas.image_height_il', '�C�����C���摜�̍��� (�s�N�Z��)'),
        array('expack.aas.trim', '�摜�̗]�����g���~���O (����, ���Ȃ�)'),
        array('expack.aas.bold', '���� (����, ���Ȃ�)'),
        array('expack.aas.fgcolor', '�����F (6���܂���3����16�i��)'),
        array('expack.aas.bgcolor', '�w�i�F (6���܂���3����16�i��)'),
        array('expack.aas.max_fontsize', '�ő�̕����T�C�Y (�|�C���g)'),
        array('expack.aas.min_fontsize', '�ŏ��̕����T�C�Y (�|�C���g)'),
        array('expack.aas.inline_fontsize', '�C�����C���\���̕����T�C�Y (�|�C���g)'),
    );
    printEditConfGroupHtml($groupname, $conflist, $flags);
}

// }}}
// }}}

// PC�p�\��
if (!$_conf['ktai']) {
    echo <<<EOP
</div><!-- end of tab -->
</div><!-- end of child tabset "�g���p�b�N�ݒ�" -->
</div><!-- end of parent tabset -->\n
EOP;
// �g�їp�\��
} else {
    if (!empty($selected_group)) {
        $group_en = htmlspecialchars(base64_encode($selected_group));
        echo "<input type=\"hidden\" name=\"edit_conf_user_group_en\" value=\"{$group_en}\">";
        echo $htm['form_submit'];
    }
}

if ($keep_old) {
    echo '<input type="hidden" name="conf_keep_old" value="true">' . "\n";
}
echo '</form>' . "\n";


// �g�тȂ�
if ($_conf['ktai']) {
    echo <<<EOP
<hr>
<form method="GET" action="{$_SERVER['SCRIPT_NAME']}">
{$_conf['k_input_ht']}
<select name="edit_conf_user_group_en">
EOP;
    foreach ($groups as $groupname) {
        $group_ht = htmlspecialchars($groupname, ENT_QUOTES);
        $group_en = htmlspecialchars(base64_encode($groupname));
        $selected = ($selected_group == $groupname) ? ' selected' : '';
        echo "<option value=\"{$group_en}\"{$selected}>{$group_ht}</option>";
    }
    echo <<<EOP
</select>
<input type="submit" value="�̐ݒ��ҏW">
</form>
<hr>
<a {$_conf['accesskey']}="{$_conf['k_accesskey']['up']}" href="editpref.php{$_conf['k_at_q']}">{$_conf['k_accesskey']['up']}.�ݒ�ҏW</a>
{$_conf['k_to_index_ht']}
EOP;
}

echo '</body></html>';

exit;

//=====================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//=====================================================================

/**
 * ���[���ݒ�i$conf_user_rules�j�Ɋ�Â��āA�t�B���^�����i�f�t�H���g�Z�b�g�j���s��
 *
 * @return  void
 */
function applyRules()
{
    global $conf_user_rules, $conf_user_def;

    if (is_array($conf_user_rules)) {
        foreach ($conf_user_rules as $k => $v) {
            if (isset($_POST['conf_edit'][$k])) {
                $def = isset($conf_user_def[$k]) ? $conf_user_def[$k] : null;
                foreach ($v as $func) {
                    $_POST['conf_edit'][$k] = call_user_func($func, $_POST['conf_edit'][$k], $def);
                }
            }
        }
    }
}

// emptyToDef() �Ȃǂ̃t�B���^��EditConfFiter�N���X�Ȃǂɂ܂Ƃ߂�\��

/**
 * CSS�l�̂��߂̃t�B���^�����O���s��
 *
 * @param   string  $str    ���͂��ꂽ�l
 * @param   string  $def    �f�t�H���g�̒l
 * @return  string
 */
function filterCssValue($str, $def = '')
{
    return preg_replace('/[^0-9a-zA-Z-%]/', '', $str);
}

/**
 * empty�̎��́A�f�t�H���g�Z�b�g����
 *
 * @param   string  $val    ���͂��ꂽ�l
 * @param   mixed   $def    �f�t�H���g�̒l
 * @return  mixed
 */
function emptyToDef($val, $def)
{
    if (empty($val)) {
        $val = $def;
    }
    return $val;
}

/**
 * ���̐������ł��鎞�͐��̐������i0���܂ށj���A
 * �ł��Ȃ����́A�f�t�H���g�Z�b�g����
 *
 * @param   string  $val    ���͂��ꂽ�l
 * @param   int     $def    �f�t�H���g�̒l
 * @return  int
 */
function notIntExceptMinusToDef($val, $def)
{
    // �S�p�����p ����
    $val = mb_convert_kana($val, 'a');
    // �������ł���Ȃ�
    if (is_numeric($val)) {
        // ����������
        $val = intval($val);
        // ���̐��̓f�t�H���g��
        if ($val < 0) {
            $val = intval($def);
        }
    // �������ł��Ȃ����̂́A�f�t�H���g��
    } else {
        $val = intval($def);
    }
    return $val;
}

/**
 * ���̎������ł��鎞�͐��̎������i0���܂ށj���A
 * �ł��Ȃ����́A�f�t�H���g�Z�b�g����
 *
 * @param   string  $val    ���͂��ꂽ�l
 * @param   float   $def    �f�t�H���g�̒l
 * @return  float
 */
function notFloatExceptMinusToDef($val, $def)
{
    // �S�p�����p ����
    $val = mb_convert_kana($val, 'a');
    // �������ł���Ȃ�
    if (is_numeric($val)) {
        // ����������
        $val = floatval($val);
        // ���̐��̓f�t�H���g��
        if ($val < 0.0) {
            $val = floatval($def);
        }
    // �������ł��Ȃ����̂́A�f�t�H���g��
    } else {
        $val = floatval($def);
    }
    return $val;
}

/**
 * �傫������Z�b�g�����ő�l�ɕ␳
 *
 * @param   string  $val    ���͂��ꂽ�l
 * @return  int
 */
function tooLargeSetNumToMax($val)
{
    $num = (int)$val;
    return ($num > P2_FAVSET_MAX_NUM) ? P2_FAVSET_MAX_NUM : $num;
}

/**
 * �I�����ɂȂ��l�̓f�t�H���g�Z�b�g����
 */
function notSelToDef()
{
    global $conf_user_def, $conf_user_sel, $conf_user_rad;

    $conf_user_list = array_merge($conf_user_sel, $conf_user_rad);
    $names = array_keys($conf_user_list);

    if (is_array($names)) {
        foreach ($names as $n) {
            if (isset($_POST['conf_edit'][$n])) {
                if (!array_key_exists($_POST['conf_edit'][$n], $conf_user_list[$n])) {
                    $_POST['conf_edit'][$n] = $conf_user_def[$n];
                }
            }
        }
    }
    return true;
}

/**
 * �O���[�v�̕\�����[�h�𓾂�
 *
 * @param   stirng  $group_key  �O���[�v��
 * @param   string  $conf_key   �ݒ荀�ږ�
 * @return  int
 */
function getGroupShowFlags($group_key, $conf_key = null)
{
    global $_conf, $selected_group;

    $flags = P2_EDIT_CONF_USER_DEFAULT;

    if (empty($selected_group) || ($selected_group != 'all' && $selected_group != $group_key)) {
        $flags |= P2_EDIT_CONF_USER_HIDDEN;
        if ($_conf['ktai']) {
            $flags |= P2_EDIT_CONF_USER_SKIPPED;
        }
    }
    if (!empty($conf_key)) {
        if (empty($_conf[$conf_key])) {
            $flags |= P2_EDIT_CONF_USER_DISABLED;
        }
        if (preg_match('/^expack\\./', $conf_key)) {
            $flags |= P2_EDIT_CONF_FILE_ADMIN_EX;
        } else {
            $flags |= P2_EDIT_CONF_FILE_ADMIN;
        }
    }
    return $flags;
}

/**
 * �O���[�v�����p��HTML�𓾂�i�֐�����PC�A�g�їp�\����U�蕪���j
 *
 * @param   stirng  $title  �O���[�v��
 * @param   int     $flags  �\�����[�h
 * @return  string
 */
function getGroupSepaHtml($title, $flags)
{
    global $_conf;

    $admin_php = ($flags & P2_EDIT_CONF_FILE_ADMIN_EX) ? 'conf_admin_ex' : 'conf_admin';

    // PC�p
    if (!$_conf['ktai']) {
        $ht = <<<EOP
<div class="tabbertab" title="{$title}">
<h4>{$title}</h4>\n
EOP;
        if ($flags & P2_EDIT_CONF_USER_DISABLED) {
            $ht .= <<<EOP
<p><i>���݁A���̋@�\�͖����ɂȂ��Ă��܂��B<br>
�L���ɂ���ɂ� conf/{$admin_php}.inc.php �� {$title} �� on �ɂ��Ă��������B</i></p>\n
EOP;
        }
        $ht .= <<<EOP
<table class="edit_conf_user" cellspacing="0">
    <tr>
        <th>�ϐ���</th>
        <th>�l</th>
        <th>����</th>
    </tr>\n
EOP;
    // �g�їp
    } else {
        if ($flags & P2_EDIT_CONF_USER_HIDDEN) {
            $ht = '';
        } else {
            $ht = "<hr><h4>{$title}</h4>" . "\n";
            if ($flags & P2_EDIT_CONF_USER_DISABLED) {
            $ht .= <<<EOP
<p>���݁A���̋@�\�͖����ɂȂ��Ă��܂��B<br>
�L���ɂ���ɂ� conf/{$admin_php}.inc.php �� {$title} �� on �ɂ��Ă��������B</p>\n
EOP;
            }
        }
    }
    return $ht;
}

/**
 * �O���[�v�I�[��HTML�𓾂�i�g�тł͋�j
 *
 * @param   int     $flags  �\�����[�h
 * @return  string
 */
function getGroupEndHtml($flags)
{
    global $_conf;

    // PC�p
    if (!$_conf['ktai']) {
        $ht = '';
        if (!($flags & P2_EDIT_CONF_USER_HIDDEN)) {
            $ht .= <<<EOP
    <tr class="group">
        <td colspan="3" align="center">
            <input type="submit" name="submit_save" value="�ύX��ۑ�����">
            <input type="reset"  name="reset_change" value="�ύX��������" onclick="return window.confirm('�ύX���������Ă���낵���ł����H\\n�i�S�Ẵ^�u�̕ύX�����Z�b�g����܂��j');">
            <input type="submit" name="submit_default" value="�f�t�H���g�ɖ߂�" onclick="return window.confirm('���[�U�ݒ���f�t�H���g�ɖ߂��Ă���낵���ł����H\\n�i��蒼���͂ł��܂���j');">
        </td>
    </tr>\n
EOP;
        }
        $ht .= <<<EOP
</table>
</div><!-- end of tab -->\n
EOP;
    // �g�їp
    } else {
        $ht = '';
    }
    return $ht;
}

/**
 * �ҏW�t�H�[��input�pHTML�𓾂�i�֐�����PC�A�g�їp�\����U�蕪���j
 *
 * @param   stirng  $name   �ݒ荀�ږ�
 * @param   string  $description_ht HTML�`���̐���
 * @param   int     $flags  �\�����[�h
 * @return  string
 */
function getEditConfHtml($name, $description_ht, $flags)
{
    global $_conf, $conf_user_def, $conf_user_sel, $conf_user_rad;

    // �f�t�H���g�l�̋K�肪�Ȃ���΁A�󔒂�Ԃ�
    if (!isset($conf_user_def[$name])) {
        return '';
    }

    $name_view = htmlspecialchars($_conf[$name], ENT_QUOTES);

    // ����or��\���Ȃ�
    if ($flags & (P2_EDIT_CONF_USER_HIDDEN | P2_EDIT_CONF_USER_DISABLED)) {
        $form_ht = getEditConfHidHtml($name);
        // �g�тȂ炻�̂܂ܕԂ�
        if ($_conf['ktai']) {
            return $form_ht;
        }
        if ($name_view === '') {
            $form_ht .= '<i>(empty)</i>';
        } else {
            $form_ht .= $name_view;
        }
        if (is_string($conf_user_def[$name])) {
            $def_views[$name] = htmlspecialchars($conf_user_def[$name], ENT_QUOTES);
        } else {
            $def_views[$name] = strval($conf_user_def[$name]);
        }
    // select �I���`���Ȃ�
    } elseif (isset($conf_user_sel[$name])) {
        $form_ht = getEditConfSelHtml($name);
        $key = $conf_user_def[$name];
        $def_views[$name] = htmlspecialchars($conf_user_sel[$name][$key], ENT_QUOTES);
    // radio �I���`���Ȃ�
    } elseif (isset($conf_user_rad[$name])) {
        $form_ht = getEditConfRadHtml($name);
        $key = $conf_user_def[$name];
        $def_views[$name] = htmlspecialchars($conf_user_rad[$name][$key], ENT_QUOTES);
    // input ���͎��Ȃ�
    } else {
        if (!$_conf['ktai']) {
            $input_size_at = sprintf(' size="%d"', ($flags & P2_EDIT_CONF_USER_LONGTEXT) ? 40 : 20);
        } else {
            $input_size_at = '';
        }
        $form_ht = <<<EOP
<input type="text" name="conf_edit[{$name}]" value="{$name_view}"{$input_size_at}>\n
EOP;
        if (is_string($conf_user_def[$name])) {
            $def_views[$name] = htmlspecialchars($conf_user_def[$name], ENT_QUOTES);
        } else {
            $def_views[$name] = strval($conf_user_def[$name]);
        }
    }

    // PC�p
    if (!$_conf['ktai']) {
        $r = <<<EOP
    <tr title="�f�t�H���g�l: {$def_views[$name]}">
        <td>{$name}</td>
        <td>{$form_ht}</td>
        <td>{$description_ht}</td>
    </tr>\n
EOP;
    // �g�їp
    } else {
        $r = <<<EOP
[{$name}]<br>
{$description_ht}<br>
{$form_ht}<br>
<br>\n
EOP;
    }

    return $r;
}

/**
 * �ҏW�t�H�[��hidden�pHTML�𓾂�
 *
 * @param   stirng  $name   �ݒ荀�ږ�
 * @return  string
 */
function getEditConfHidHtml($name)
{
    global $_conf, $conf_user_def;

    if (isset($_conf[$name]) && $_conf[$name] != $conf_user_def[$name]) {
        $value_ht = htmlspecialchars($_conf[$name], ENT_QUOTES);
    } else {
        $value_ht = htmlspecialchars($conf_user_def[$name], ENT_QUOTES);
    }

    $form_ht = "<input type=\"hidden\" name=\"conf_edit[{$name}]\" value=\"{$value_ht}\">";

    return $form_ht;
}

/**
 * �ҏW�t�H�[��select�pHTML�𓾂�
 *
 * @param   stirng  $name   �ݒ荀�ږ�
 * @return  string
 */
function getEditConfSelHtml($name)
{
    global $_conf, $conf_user_def, $conf_user_sel;

    $form_ht = "<select name=\"conf_edit[{$name}]\">\n";

    foreach ($conf_user_sel[$name] as $key => $value) {
        /*
        if ($value == "") {
            continue;
        }
        */
        $selected = '';
        if ($_conf[$name] == $key) {
            $selected = " selected";
        }
        $key_ht = htmlspecialchars($key, ENT_QUOTES);
        $value_ht = htmlspecialchars($value, ENT_QUOTES);
        $form_ht .= "\t<option value=\"{$key_ht}\"{$selected}>{$value_ht}</option>\n";
    }

    $form_ht .= "</select>\n";

    return $form_ht;
}

/**
 * �ҏW�t�H�[��radio�pHTML�𓾂�
 *
 * @param   stirng  $name   �ݒ荀�ږ�
 * @return  string
 */
function getEditConfRadHtml($name)
{
    global $_conf, $conf_user_def, $conf_user_rad;

    $form_ht = '';

    foreach ($conf_user_rad[$name] as $key => $value) {
        /*
        if ($value == "") {
            continue;
        }
        */
        $checked = '';
        if ($_conf[$name] == $key) {
            $checked = " checked";
        }
        $key_ht = htmlspecialchars($key, ENT_QUOTES);
        $value_ht = htmlspecialchars($value, ENT_QUOTES);
        $form_ht .= "<label><input type=\"radio\" name=\"conf_edit[{$name}]\" value=\"{$key_ht}\"{$checked}>{$value_ht}</label>\n";
    }

    return $form_ht;
}

/**
 * �ҏW�t�H�[����\������
 *
 * @param   stirng  $groupname  �O���[�v��
 * @param   array   $conflist   �ݒ荀�ږ��Ɛ����̔z��
 * @param   int     $flags      �\�����[�h
 * @return  void
 */
function printEditConfGroupHtml($groupname, $conflist, $flags)
{
    echo getGroupSepaHtml($groupname, $flags);
    foreach ($conflist as $c) {
        if (isset($c[2]) && is_integer($c[2]) && $c[2] > 0) {
            echo getEditConfHtml($c[0], $c[1], $c[2] | $flags);
        } else {
            echo getEditConfHtml($c[0], $c[1], $flags);
        }
    }
    echo getGroupEndHtml($flags);
}

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
