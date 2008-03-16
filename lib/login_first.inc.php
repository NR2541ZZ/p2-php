<?php

/**
 *  p2 - �ŏ��̃��O�C����ʂ�HTML�\������֐�
 *
 * @access  public
 * @return  void
 */
function printLoginFirst(&$_login)
{
    global $STYLE, $_conf;
    global $_login_failed_flag, $_p2session;
    global $skin_en;

    // {{{ �f�[�^�ۑ��f�B���N�g���ɏ������݌������Ȃ���Β��ӂ�\���Z�b�g����

    P2Util::checkDirWritable($_conf['dat_dir']);
    $checked_dirs[] = $_conf['dat_dir']; // �`�F�b�N�ς݂̃f�B���N�g�����i�[����z���

    if (!in_array($_conf['idx_dir'], $checked_dirs)) {
        P2Util::checkDirWritable($_conf['idx_dir']);
        $checked_dirs[] = $_conf['idx_dir'];
    }
    if (!in_array($_conf['pref_dir'], $checked_dirs)) {
        P2Util::checkDirWritable($_conf['pref_dir']);
        $checked_dirs[] = $_conf['pref_dir'];
    }

    // }}}

    // �O����
    $_login->cleanInvalidAuthUserFile();
    clearstatcache();

    //=========================================================
    // �����o���p�ϐ�
    //=========================================================
    $ptitle = 'rep2';

    $myname = basename($_SERVER['SCRIPT_NAME']);

    $auth_sub_input_ht = '';
    $body_ht = '';

    $p_str = array(
        'user'      => '���[�U',
        'password'  => '�p�X���[�h'
    );

    // �g�їp�\��������S�p�����p�ϊ�
    if ($_conf['ktai'] && function_exists('mb_convert_kana')) {
        foreach ($p_str as $k => $v) {
            $p_str[$k] = mb_convert_kana($v, 'rnsk');
        }
    }

    //==============================================
    // �⏕�F��
    //==============================================
    $mobile = &Net_UserAgent_Mobile::singleton();

    // {{{ EZ�F��

    if (!empty($_SERVER['HTTP_X_UP_SUBNO'])) {
        if (file_exists($_conf['auth_ez_file'])) {
            include $_conf['auth_ez_file'];
            if ($_SERVER['HTTP_X_UP_SUBNO'] == $registed_ez) {
                $auth_sub_input_ht = '�[��ID OK : հ�ޖ�������۸޲݂ł��܂��<br>';
            }
        } else {
            $auth_sub_input_ht = '<input type="hidden" name="ctl_regist_ez" value="1">'."\n".
                '<input type="checkbox" name="regist_ez" value="1" checked>EZ�[��ID�ŔF�؂�o�^<br>';
        }

    // }}}
    // {{{ J�F��

    // http://www.dp.j-phone.com/dp/tool_dl/web/useragent.php
    } elseif ($mobile->isVodafone() && ($SN = $mobile->getSerialNumber()) !== null) {
        if (file_exists($_conf['auth_jp_file'])) {
            include $_conf['auth_jp_file'];
            if ($SN == $registed_jp) {
                $auth_sub_input_ht = '�[��ID OK : հ�ޖ�������۸޲݂ł��܂��<br>';
            }
        } else {
            $auth_sub_input_ht = '<input type="hidden" name="ctl_regist_jp" value="1">'."\n".
                '<input type="checkbox" name="regist_jp" value="1" checked>J�[��ID�ŔF�؂�o�^<br>';
        }

    // }}}
    // {{{ DoCoMo�F��

    } elseif ($mobile->isDoCoMo()) {
        if (file_exists($_conf['auth_docomo_file'])) {
        } else {
            $auth_sub_input_ht = '<input type="hidden" name="ctl_regist_docomo" value="1">'."\n".
                '<input type="checkbox" name="regist_docomo" value="1" checked>DoCoMo�[��ID�ŔF�؂�o�^<br>';
        }

    // }}}
    // {{{ Cookie�F��

    } else {

        $regist_cookie_checked = ' checked';
        if (isset($_POST['submit_new']) || isset($_POST['submit_member'])) {
            if ($_POST['regist_cookie'] != '1') {
                $regist_cookie_checked = '';
            }
        }
        $auth_sub_input_ht = '<input type="hidden" name="ctl_regist_cookie" value="1">'."\n".
            '<input type="checkbox" id="regist_cookie" name="regist_cookie" value="1"'.$regist_cookie_checked.'><label for="regist_cookie">cookie�ɕۑ�����i�����j</label><br>';
    }

    // }}}

    // ���O�C���t�H�[������̎w��
    if (!empty($GLOBALS['brazil'])) {
        $add_mail = '.,@-';
    } else {
        $add_mail = '';
    }

    if (preg_match("/^[0-9a-zA-Z_{$add_mail}]+$/", $_login->user_u)) {
        $hd['form_login_id'] = htmlspecialchars($_login->user_u, ENT_QUOTES);
    } elseif (!empty($_POST['form_login_id']) && preg_match("/^[0-9a-zA-Z_{$add_mail}]+$/", $_POST['form_login_id'])) {
        $hd['form_login_id'] = htmlspecialchars($_POST['form_login_id'], ENT_QUOTES);
    } else {
        $hd['form_login_id'] = '';
    }


    if (!empty($_POST['form_login_pass']) && preg_match('/^[0-9a-zA-Z_]+$/', $_POST['form_login_pass'])) {
        $hd['form_login_pass'] = htmlspecialchars($_POST['form_login_pass'], ENT_QUOTES);
    } else {
        $hd['form_login_pass'] = '';
    }

    // DoCoMo�̌ŗL�[���F�؁i�Z�b�V�������p���̂ݗL���j
    $docomo_utn_ht = '';

    //if ($_conf['use_session'] && $_login->user_u && $mobile->isDoCoMo()) {
    if ($_conf['use_session'] && $mobile->isDoCoMo()) {
        $docomo_utn_ht = '<p><a href="' . $myname . '?user=' . htmlspecialchars($_login->user_u, ENT_QUOTES) . '" utn>DoCoMo�ŗL�[���F��</a></p>';
    }

    // DoCoMo�Ȃ�password�ɂ��Ȃ�
    if ($mobile->isDoCoMo()) {
        $type = "text";
    } else {
        $type = "password";
    }

    // {{{ ���O�C���p�t�H�[���𐶐�

    $hd['REQUEST_URI'] = htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES);

    if (file_exists($_conf['auth_user_file'])) {
        $submit_ht = '<input type="submit" name="submit_member" value="���[�U���O�C��">';
    } else {
        $submit_ht = '<input type="submit" name="submit_new" value="�V�K�o�^">';
    }

    if ($_conf['ktai']) {
        //$k_roman_input_at = ' istyle="3" format="*m" mode="alphabet"';
        $k_roman_input_at = ' istyle="3" format="*x" mode="alphabet"';
        $k_input_size_at = '';
    } else {
        $k_roman_input_at = '';
        $k_input_size_at = ' size="32"';
    }
    $login_form_ht = <<<EOP
{$docomo_utn_ht}
<form id="login" method="POST" action="{$hd['REQUEST_URI']}" target="_self" utn>
    {$_conf['k_input_ht']}
    {$p_str['user']}: <input type="text" name="form_login_id" value="{$hd['form_login_id']}"{$k_roman_input_at}{$k_input_size_at}><br>
    {$p_str['password']}: <input type="{$type}" name="form_login_pass" value="{$hd['form_login_pass']}"{$k_roman_input_at}><br>
    {$auth_sub_input_ht}
    <br>
    {$submit_ht}
</form>\n
EOP;

    // }}}

    //=================================================================
    // �V�K���[�U�o�^����
    //=================================================================

    if (!file_exists($_conf['auth_user_file']) && !$_login_failed_flag and !empty($_POST['submit_new']) && !empty($_POST['form_login_id']) && !empty($_POST['form_login_pass'])) {

        // {{{ ���̓G���[���`�F�b�N�A����

        if (!preg_match('/^[0-9a-zA-Z_]+$/', $_POST['form_login_id']) || !preg_match('/^[0-9a-zA-Z_]+$/', $_POST['form_login_pass'])) {
            P2Util::pushInfoHtml("<p class=\"infomsg\">rep2 error: �u{$p_str['user']}�v���Ɓu{$p_str['password']}�v�͔��p�p�����œ��͂��ĉ������B</p>");
            $show_login_form_flag = true;

        // }}}
        // {{{ �o�^����

        } else {

            $_login->makeUser($_POST['form_login_id'], $_POST['form_login_pass']);

            // �V�K�o�^����
            $hd['form_login_id'] = htmlspecialchars($_POST['form_login_id'], ENT_QUOTES);
            $body_ht .= "<p class=\"infomsg\">�� �F��{$p_str['user']}�u{$hd['form_login_id']}�v��o�^���܂���</p>";
            $body_ht .= "<p><a href=\"{$myname}?form_login_id={$hd['form_login_id']}{$_conf['k_at_a']}\">rep2 start</a></p>";

            $_login->setUser($_POST['form_login_id']);
            $_login->pass_x = sha1($_POST['form_login_pass']);

            // �Z�b�V���������p����Ă���Ȃ�A�Z�b�V�������X�V
            if (isset($_p2session)) {
                // ���[�U���ƃp�XX���X�V
                $_SESSION['login_user'] = $_login->user_u;
                $_SESSION['login_pass_x'] = $_login->pass_x;
            }

            // �v��������΁A�⏕�F�؂�o�^
            $_login->registCookie();
            $_login->registKtaiId();
        }

        // }}}

    // {{{ ���O�C���G���[������

    } else {

        if (isset($_POST['form_login_id']) || isset($_POST['form_login_pass'])) {
            P2Util::pushInfoHtml('<p class="infomsg">');
            if (!$_POST['form_login_id']) {
                P2Util::pushInfoHtml("rep2 error: �u{$p_str['user']}�v�����͂���Ă��܂���B"."<br>");
            }
            if (!$_POST['form_login_pass']) {
                P2Util::pushInfoHtml("rep2 error: �u{$p_str['password']}�v�����͂���Ă��܂���B");
            }
            P2Util::pushInfoHtml('</p>');
        }

        $show_login_form_flag = true;

    }

    // }}}

    //=========================================================
    // HTML�v�����g
    //=========================================================
    P2Util::header_nocache();
    echo $doctype;
    echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$ptitle}</title>
EOP;
    if (!$_conf['ktai']) {
        echo "<style type=\"text/css\" media=\"all\">\n<!--\n";
        @include 'style/style_css.inc';
        @include 'style/login_first_css.inc';
        echo "\n-->\n</style>\n";
    }
    echo "</head><body>\n";
    echo "<h3>{$ptitle}</h3>\n";

    // ���\��
    P2Util::printInfoHtml();

    echo $body_ht;

    if (!empty($show_login_form_flag)) {
        echo $login_form_ht;
    }

    echo '</body></html>';
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
