<?php
/*
    p2 - ���[�U�ݒ�ҏW�C���^�t�F�[�X
*/

include_once './conf/conf.inc.php';  // ��{�ݒ�
require_once (P2_LIBRARY_DIR . '/dataphp.class.php');

$_login->authorize(); // ���[�U�F��

if (!empty($_POST['submit_save']) || !empty($_POST['submit_default'])) {
    if (!isset($_POST['csrfid']) or $_POST['csrfid'] != P2Util::getCsrfId()) {
        die('p2 error: �s���ȃ|�X�g�ł�');
    }
}

//=====================================================================
// �O����
//=====================================================================

// {{{ ���ۑ��{�^����������Ă�����A�ݒ��ۑ�

if (!empty($_POST['submit_save'])) {

    // �l�̓K���`�F�b�N�A����
    
    // �g����
    $_POST['conf_edit'] = array_map('trim', $_POST['conf_edit']);
    
    // �I�����ɂȂ����� �� �f�t�H���g����
    $names = array_keys($conf_user_sel);
    notSelToDef($names);
    
    // empty �� �f�t�H���g����
    emptyToDef();

    // ���̐��� or 0 �łȂ����� �� �f�t�H���g����
    notIntExceptMinusToDef();

    /**
     * �f�t�H���g�l $conf_user_def �ƕύX�l $_POST['conf_edit'] ���������݂��Ă��āA
     * �f�t�H���g�ƕύX�l���قȂ�ꍇ�̂ݐݒ�ۑ�����i���̑��̃f�[�^�͕ۑ����ꂸ�A�j�������j
     */
    $conf_save = array();
    foreach ($conf_user_def as $k => $v) {
        if (isset($conf_user_def[$k]) && isset($_POST['conf_edit'][$k])) {
            if ($conf_user_def[$k] != $_POST['conf_edit'][$k]) {
                $conf_save[$k] = $_POST['conf_edit'][$k];
            }
        }
    }

    // �V���A���C�Y���āA�f�[�^PHP�`���ŕۑ�
    $cont = serialize($conf_save);
    if (DataPhp::writeDataPhp($_conf['conf_user_file'], $cont, $_conf['conf_user_perm'])) {
        $_info_msg_ht .= "<p>���ݒ���X�V�ۑ����܂���</p>";
        // �ύX������΁A�����f�[�^���X�V���Ă���
        $_conf = array_merge($_conf, $conf_user_def);
        $_conf = array_merge($_conf, $conf_save);
    } else {
        $_info_msg_ht .= "<p>�~�ݒ���X�V�ۑ��ł��܂���ł���</p>";
    }

// }}}
// {{{ ���f�t�H���g�ɖ߂��{�^����������Ă�����

} elseif (!empty($_POST['submit_default'])) {
    if (@unlink($_conf['conf_user_file'])) {
        $_info_msg_ht .= "<p>���ݒ���f�t�H���g�ɖ߂��܂���</p>";
        // �ύX������΁A�����f�[�^���X�V���Ă���
        $_conf = array_merge($_conf, $conf_user_def);
        $_conf = array_merge($_conf, $conf_save);
    }
}

// }}}

//=====================================================================
// �v�����g�ݒ�
//=====================================================================
$ptitle = '���[�U�ݒ�ҏW';

$csrfid = P2Util::getCsrfId();

//=====================================================================
// �v�����g
//=====================================================================
// �w�b�_HTML���v�����g
P2Util::header_nocache();
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$ptitle}</title>\n
EOP;

if (empty($_conf['ktai'])) {
    echo <<<EOP
    <script type="text/javascript" src="js/basic.js"></script>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">\n
EOP;
}

if (empty($_conf['ktai'])) {
    @include("./style/style_css.inc");
    @include("./style/edit_conf_user_css.inc");
}

echo <<<EOP
</head>
<body onLoad="top.document.title=self.document.title;">\n
EOP;

// PC�p�\��
if (empty($_conf['ktai'])) {
    echo <<<EOP
<p id="pan_menu"><a href="editpref.php">�ݒ�Ǘ�</a> &gt; {$ptitle}</p>\n
EOP;
}

// PC�p�\��
if (empty($_conf['ktai'])) {
    $htm['form_submit'] = <<<EOP
        <tr class="group">
            <td colspan="3" align="center">
                <input type="submit" name="submit_save" value="�ύX��ۑ�����">
                <input type="submit" name="submit_default" value="�f�t�H���g�ɖ߂�" onClick="if (!window.confirm('���[�U�ݒ���f�t�H���g�ɖ߂��Ă���낵���ł����H�i��蒼���͂ł��܂���j')) {return false;}"><br>
            </td>
        </tr>\n
EOP;
// �g�їp�\��
} else {
    $htm['form_submit'] = <<<EOP
        <input type="submit" name="submit_save" value="�ύX��ۑ�����">\n
EOP;
}

// ��񃁃b�Z�[�W�\��
if (!empty($_info_msg_ht)) {
    echo $_info_msg_ht;
    $_info_msg_ht = "";
}

echo <<<EOP
<form method="POST" action="{$_SERVER['PHP_SELF']}" target="_self">
    <input type="hidden" name="csrfid" value="{$csrfid}">\n
EOP;

// PC�p�\���itable�j
if (empty($_conf['ktai'])) {
    echo '<table id="edit_conf_user" cellspacing="0">'."\n";
}

echo $htm['form_submit'];

// PC�p�\���itable�j
if (empty($_conf['ktai'])) {
    echo <<<EOP
        <tr>
            <td>�ϐ���</td>
            <td>�l</td>
            <td>����</td>
        </tr>\n
EOP;
}

echo getGroupSepaHtml('be.2ch.net �A�J�E���g');

echo getEditConfHtml('be_2ch_code', '<a href="http://be.2ch.net/" target="_blank">be.2ch.net</a>�̔F�؃R�[�h(�p�X���[�h�ł͂Ȃ�)');
echo getEditConfHtml('be_2ch_mail', 'be.2ch.net�̓o�^���[���A�h���X');

echo getGroupSepaHtml('PATH');

//echo getEditConfHtml('first_page', '�E�������ɍŏ��ɕ\�������y�[�W�B�I�����C��URL���B');
echo getEditConfHtml('brdfile_online', 
    '���X�g�̎w��i�I�����C��URL�j<br>
    ���X�g���I�����C��URL���玩���œǂݍ��ށB
    �w���� menu.html �`���A2channel.brd �`���̂ǂ���ł��悢�B
    <!-- �K�v�Ȃ���΁A�󔒂ɁB --><br>

    2ch��{ <a href="http://www.ff.iij4u.or.jp/~ch2/bbsmenu.html" target="_blank">http://www.ff.iij4u.or.jp/~ch2/bbsmenu.html</a><br>
    2ch + �O��BBS <a href="http://azlucky.s25.xrea.com/2chboard/bbsmenu.html" target="_blank">http://azlucky.s25.xrea.com/2chboard/bbsmenu.html</a><br>
    ');


echo getGroupSepaHtml('subject');

echo getEditConfHtml('refresh_time', '�X���b�h�ꗗ�̎����X�V�Ԋu (���w��B0�Ȃ玩���X�V���Ȃ�)');

echo getEditConfHtml('sb_show_motothre', '�X���b�h�ꗗ�Ŗ��擾�X���ɑ΂��Č��X���ւ̃����N�i�E�j��\�� (����, ���Ȃ�)');
echo getEditConfHtml('sb_show_one', '�X���b�h�ꗗ�i�\���j��>>1��\�� (����, ���Ȃ�, �j���[�X�n�̂�)');
echo getEditConfHtml('sb_show_spd', '�X���b�h�ꗗ�ł��΂₳�i���X�Ԋu�j��\�� (����:1, ���Ȃ�:0)');
echo getEditConfHtml('sb_show_ikioi', '�X���b�h�ꗗ�Ő����i1��������̃��X���j��\�� (����:1, ���Ȃ�:0)');
echo getEditConfHtml('sb_show_fav', '�X���b�h�ꗗ�ł��C�ɃX���}�[�N����\�� (����:1, ���Ȃ�:0)');
echo getEditConfHtml('sb_sort_ita', '�\���̃X���b�h�ꗗ�ł̃f�t�H���g�̃\�[�g�w��');
echo getEditConfHtml('sort_zero_adjust', '�V���\�[�g�ł́u�����Ȃ��v�́u�V�����[���v�ɑ΂���\�[�g�D�揇�� (���, ����, ����)');
echo getEditConfHtml('cmp_dayres_midoku', '�����\�[�g���ɐV�����X�̂���X����D�� (����, ���Ȃ�)');
echo getEditConfHtml('k_sb_disp_range', '�g�щ{�����A��x�ɕ\������X���̐�');
echo getEditConfHtml('viewall_kitoku', '�����X���͕\�������Ɋւ�炸�\�� (����, ���Ȃ�)');

echo getGroupSepaHtml('read');

echo getEditConfHtml('respointer', '�X�����e�\�����A���ǂ̉��R�O�̃��X�Ƀ|�C���^�����킹�邩');
echo getEditConfHtml('before_respointer', 'PC�{�����A�|�C���^�̉��R�O�̃��X����\�����邩');
echo getEditConfHtml('before_respointer_new', '�V���܂Ƃߓǂ݂̎��A�|�C���^�̉��R�O�̃��X����\�����邩');
echo getEditConfHtml('rnum_all_range', '�V���܂Ƃߓǂ݂ň�x�ɕ\�����郌�X��');
echo getEditConfHtml('preview_thumbnail', '�摜URL�̐�ǂ݃T���l�C����\���i����, ���Ȃ�)');
echo getEditConfHtml('pre_thumb_limit', '�摜URL�̐�ǂ݃T���l�C������x�ɕ\�����鐧����');
//echo getEditConfHtml('preview_thumbnail', '�摜�T���l�C���̏c�̑傫�����w�� (�s�N�Z��)');
////echo getEditConfHtml('pre_thumb_width', '�摜�T���l�C���̉��̑傫�����w�� (�s�N�Z��)');
echo getEditConfHtml('iframe_popup', 'HTML�|�b�v�A�b�v (����, ���Ȃ�, p�ł���)');
//echo getEditConfHtml('iframe_popup_delay', 'HTML�|�b�v�A�b�v�̕\���x������ (�b)');
echo getEditConfHtml('ext_win_target', '�O���T�C�g���փW�����v���鎞�ɊJ���E�B���h�E�̃^�[�Q�b�g�� (����:"", �V��:"_blank")');
echo getEditConfHtml('bbs_win_target', 'p2�Ή�BBS�T�C�g���ŃW�����v���鎞�ɊJ���E�B���h�E�̃^�[�Q�b�g�� (����:"", �V��:"_blank")');
echo getEditConfHtml('bottom_res_form', '�X���b�h�����ɏ������݃t�H�[����\�� (����, ���Ȃ�)');
echo getEditConfHtml('quote_res_view', '���p���X��\�� (����, ���Ȃ�)');

echo getEditConfHtml('k_rnum_range', '�g�щ{�����A��x�ɕ\�����郌�X�̐�');
echo getEditConfHtml('ktai_res_size', '�g�щ{�����A��̃��X�̍ő�\���T�C�Y');
echo getEditConfHtml('ktai_ryaku_size', '�g�щ{�����A���X���ȗ������Ƃ��̕\���T�C�Y');
echo getEditConfHtml('before_respointer_k', '�g�щ{�����A�|�C���^�̉��R�O�̃��X����\�����邩');
echo getEditConfHtml('k_use_tsukin', '�g�щ{�����A�O�������N�ɒʋ΃u���E�U(��)�𗘗p(����, ���Ȃ�)');
echo getEditConfHtml('k_use_picto', '�g�щ{�����A�摜�����N��pic.to(��)�𗘗p(����, ���Ȃ�)');

echo getGroupSepaHtml('ETC');

echo getEditConfHtml('my_FROM', '���X�������ݎ��̃f�t�H���g�̖��O');
echo getEditConfHtml('my_mail', '���X�������ݎ��̃f�t�H���g��mail');

echo getEditConfHtml('editor_srcfix', 'PC�{�����A�\�[�X�R�[�h�̃R�s�y�ɓK�����␳������`�F�b�N�{�b�N�X��\���i����, ���Ȃ�, pc�I�̂݁j');

echo getEditConfHtml('get_new_res', '�V�����X���b�h���擾�������ɕ\�����郌�X��(�S�ĕ\������ꍇ:"all")');
echo getEditConfHtml('rct_rec_num', '�ŋߓǂ񂾃X���̋L�^��');
echo getEditConfHtml('res_hist_rec_num', '�������ݗ����̋L�^��');
echo getEditConfHtml('res_write_rec', '�������ݓ��e���O���L�^(����, ���Ȃ�)');
echo getEditConfHtml('through_ime', '�O��URL�W�����v����ۂɒʂ��Q�[�g (����:"", p2 ime(�����]��):"p2", p2 ime(�蓮�]��):"p2m", p2 ime(p�̂ݎ蓮�]��):"p2pm")');
echo getEditConfHtml('join_favrank', '<a href="http://akid.s17.xrea.com:8080/favrank/favrank.html" target="_blank">���C�ɃX�����L</a>�ɎQ��(����, ���Ȃ�)');
echo getEditConfHtml('enable_menu_new', '���j���[�ɐV������\�� (����:1, ���Ȃ�:0, ���C�ɔ̂�:2)');
echo getEditConfHtml('menu_refresh_time', '���j���[�����̎����X�V�Ԋu (���w��B0�Ȃ玩���X�V���Ȃ��B)');
echo getEditConfHtml('k_save_packet', '�g�щ{�����A�p�P�b�g�ʂ����炷���߁A�S�p�p���E�J�i�E�X�y�[�X�𔼊p�ɕϊ� (����, ���Ȃ�)');
echo getEditConfHtml('ngaborn_daylimit', '���̊��ԁANG���ځ[���HIT���Ȃ���΁A�o�^���[�h�������I�ɊO���i�����j');
echo getEditConfHtml('precede_openssl', '�����O�C�����A�܂���openssl�Ŏ��݂�B��PHP 4.3.0�ȍ~�ŁAOpenSSL���ÓI�Ƀ����N����Ă���K�v������B');
echo getEditConfHtml('precede_phpcurl', 'curl���g�����A�R�}���h���C���ł�PHP�֐��łǂ����D�悷�邩 (�R�}���h���C����:0, PHP�֐���:1)');

echo $htm['form_submit'];

if (empty($_conf['ktai'])) {
    echo '</table>'."\n";
}

echo '</form>'."\n";


// �g�тȂ�
if ($_conf['ktai']) {
    echo '<hr>'.$_conf['k_to_index_ht'];
}

echo '</body></html>';

// �������܂�
exit;

//=====================================================================
// �֐�
//=====================================================================

/**
 * ���[���ݒ�i$conf_user_rules�j�Ɋ�Â��āA
 * �w���name�ɂ����āAPOST�w�肪empty�̎��́A�f�t�H���g�Z�b�g����
 */
function emptyToDef()
{
    global $conf_user_def, $conf_user_rules;
    
    $rule = 'NotEmpty';
    
    if (is_array($conf_user_rules)) {
        foreach ($conf_user_rules as $n => $va) {
            if (in_array($rule, $va)) {
                if (isset($_POST['conf_edit'][$n])) {
                    if (empty($_POST['conf_edit'][$n])) {
                        $_POST['conf_edit'][$n] = $conf_user_def[$n];
                    }
                }
            }
        } // foreach
    }
    return true;
}

/**
 * ���[���ݒ�i$conf_user_rules�j�Ɋ�Â��āA
 * POST�w��𐳂̐������ł��鎞�͐��̐������i0���܂ށj���A
 * �ł��Ȃ����́A�f�t�H���g�Z�b�g����
 */
function notIntExceptMinusToDef()
{
    global $conf_user_def, $conf_user_rules;
    
    $rule = 'IntExceptMinus';
    
    if (is_array($conf_user_rules)) {
        foreach ($conf_user_rules as $n => $va) {
            if (in_array($rule, $va)) {
                if (isset($_POST['conf_edit'][$n])) {
                    // �S�p�����p ����
                    $_POST['conf_edit'][$n] = mb_convert_kana($_POST['conf_edit'][$n], 'a');
                    // �������ł���Ȃ�
                    if (is_numeric($_POST['conf_edit'][$n])) {
                        // ����������
                        $_POST['conf_edit'][$n] = intval($_POST['conf_edit'][$n]);
                        // ���̐��̓f�t�H���g��
                        if ($_POST['conf_edit'][$n] < 0) {
                            $_POST['conf_edit'][$n] = intval($conf_user_def[$n]);
                        }
                    // �������ł��Ȃ����̂́A�f�t�H���g��
                    } else {
                        $_POST['conf_edit'][$n] = intval($conf_user_def[$n]);
                    }
                }
            }
        } // foreach
    }
    return true;
}

/**
 * �w���name�ɂ����āA�I�����ɂȂ��l�̓f�t�H���g�Z�b�g����
 *
 * @param array $names �w�肷��name���i�[�����z��
 */
function notSelToDef($names)
{
    global $conf_user_def, $conf_user_sel;
    
    if (is_array($names)) {
        foreach ($names as $n) {
            if (isset($_POST['conf_edit'][$n])) {
                if (!array_key_exists($_POST['conf_edit'][$n], $conf_user_sel[$n])) {
                    $_POST['conf_edit'][$n] = $conf_user_def[$n];
                }
            }
        } // foreach
    }
    return true;
}

/**
 * �O���[�v�����p��HTML�𓾂�i�֐�����PC�A�g�їp�\����U�蕪���j
 */
function getGroupSepaHtml($title)
{
    global $_conf;
    
    // PC�p
    if (empty($_conf['ktai'])) {
        $ht = <<<EOP
        <tr class="group">
            <td colspan="4"><h4 style="display:inline;">{$title}</h4></td>
        </tr>\n
EOP;
    // �g�їp
    } else {
        $ht = "<hr><h4>{$title}</h4>"."\n";
    }
    return $ht;
}

/**
 * �ҏW�t�H�[��input�pHTML�𓾂�i�֐�����PC�A�g�їp�\����U�蕪���j
 */
function getEditConfHtml($name, $description_ht)
{
    global $_conf, $conf_user_def, $conf_user_sel;

    // �f�t�H���g�l�̋K�肪�Ȃ���΁A�󔒂�Ԃ�
    if (!isset($conf_user_def[$name])) {
        return '';
    }

    $name_view = $_conf[$name];
    
    if (empty($_conf['ktai'])) {
        $input_size_at = ' size="38"';
    } else {
        $input_size_at = '';
    }
    
    // select �I���`���Ȃ�
    if ($conf_user_sel[$name]) {
        $form_ht = getEditConfSelHtml($name);
        $key = $conf_user_def[$name];
        $def_views[$name] = htmlspecialchars($conf_user_sel[$name][$key]);
    // input ���͎��Ȃ�
    } else {
        $form_ht = <<<EOP
<input type="text" name="conf_edit[{$name}]" value="{$name_view}"{$input_size_at}>\n
EOP;
        if (is_string($conf_user_def[$name])) {
            $def_views[$name] = htmlspecialchars($conf_user_def[$name]);
        } else {
            $def_views[$name] = $conf_user_def[$name];
        }
    }
    
    // PC�p
    if (empty($_conf['ktai'])) {
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
 * �ҏW�t�H�[��select�pHTML�𓾂�
 */
function getEditConfSelHtml($name)
{
    global $_conf, $conf_user_def, $conf_user_sel;

    foreach ($conf_user_sel[$name] as $key => $value) {
        /*
        if ($value == "") {
            continue;
        }
        */
        $selected = "";
        if ($_conf[$name] == $key) {
            $selected = " selected";
        }
        $key_ht = htmlspecialchars($key);
        $value_ht = htmlspecialchars($value);
        $options_ht .= "\t<option value=\"{$key_ht}\"{$selected}>{$value_ht}</option>\n";
    } // foreach
    
    $form_ht = <<<EOP
        <select name="conf_edit[{$name}]">
        {$options_ht}
        </select>\n
EOP;
    return $form_ht;
}

?>
