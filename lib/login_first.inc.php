<?php
// p2 ���O�C��

require_once (P2_LIBRARY_DIR . '/filectl.class.php');
require_once (P2_LIBRARY_DIR . '/login.inc.php');

/**
 *  p2 �ŏ��̃��O�C����ʂ�\������
 */
function printLoginFirst()
{
    global $_conf, $_info_msg_ht, $login;
    global $STYLE;
    
    // {{{ �f�[�^�ۑ��f�B���N�g���̃p�[�~�b�V�����̒��ӂ����N����
    P2Util::checkDirWritable($_conf['dat_dir']);
    $checked_dirs[] = $_conf['dat_dir']; // �`�F�b�N�ς݂̃f�B���N�g�����i�[����z���
    
    if (!in_array($_conf['idx_dir'], $checked_dirs)) {
        P2Util::checkDirWritable($_conf['idx_dir']);
        $checked_dirs[] = $_conf['idx_dir'];
    }
    if (!in_array($_conf['pref_dir'], $checked_dirs)) {
        P2Util::checkDirWritable($_conf['pref_dir']);
        $checked_dirs[] = $_conf['pref_dir']
    }
    // }}}
    
    //=========================================================
    // �����o���p�ϐ�
    //=========================================================
    $ptitle = "p2";

    $auth_sub_input_ht = "";
    $body_ht = "";

    if ($_conf['ktai']) {
        $user_st = "հ��";
        $password_st = "�߽ܰ��";
    
    } else {
        $user_st = "���[�U";
        $password_st = "�p�X���[�h";
    }


    // ���⏕�F��
    
    // {{{ EZ�F��
    if ($_SERVER['HTTP_X_UP_SUBNO']) {
        if (file_exists($_conf['auth_ez_file'])) {
        } else {
            $auth_sub_input_ht = <<<EOP
    <input type="checkbox" name="regist_ez" value="in" checked>EZ�[��ID�ŔF�؂�o�^<br>
EOP;
        }
    // }}}
    
    // {{{ J�F��
    $mobile = &Net_UserAgent_Mobile::singleton();
    } elseif ($mobile->isVodafone() && $mobile->getSerialNumber()) {
        if (file_exists($_conf['auth_jp_file'])) {
        } else {
            $auth_sub_input_ht = <<<EOP
    <input type="checkbox" name="regist_jp" value="in" checked>J�[��ID�ŔF�؂�o�^<br>
EOP;
        }
    // }}}
    
    // {{{ Cookie�F��
    } else {
        $auth_sub_input_ht = '<input type="hidden" name="ctl_regist_cookie" value="1">'."\n".
        '<input type="checkbox" id="regist_cookie" name="regist_cookie" value="1" checked><label for="regist_cookie">cookie�ɕۑ�����</label><br>';
    }
    // }}}
    
    // {{{ ���O�C���p�t�H�[��
    $login_form_ht = <<<EOP
<p>�F��{$user_st}��{$password_st}��V�K�o�^���܂�</p>
<form id="login" method="POST" action="{$_SERVER['REQUEST_URI']}" target="_self">
    {$_conf['k_input_ht']}
    {$user_st}: <input type="text" name="login_user" value="{$_POST['login_user']}"><br>
    {$password_st}: <input type="password" name="login_pass" value="{$_POST['login_pass']}"><br>
    {$auth_sub_input_ht}
    <br>
    <input type="submit" name="submit_new" value="�V�K�o�^">
</form>\n
EOP;
    // }}}

    //=================================================================
    // �V�K���[�U�o�^���� 
    //=================================================================
    if ($_POST['login_user'] && $_POST['login_pass']) {

        if (!preg_match('/^[0-9a-zA-Z_]+$/', $_POST['login_user']) || !preg_match('/^[0-9a-zA-Z_]+$/', $_POST['login_pass'])) {
            $_info_msg_ht .= "<p class=\"infomsg\">p2 error: {$user_st}����{$password_st}�͔��p�p�����œ��͂��ĉ������B</p>";
            $show_login_form = true;
    
        } else {
            $crypted_login_pass = crypt($_POST['login_pass']);
            $auth_user_cont = <<<EOP
<?php
\$login['user']='{$_POST["login_user"]}';
\$login['pass']='{$crypted_login_pass}';
?>
EOP;
            FileCtl::make_datafile($_conf['auth_user_file'], $_conf['pass_perm']); // �t�@�C�����Ȃ���ΐ���
            if (FileCtl::file_write_contents($_conf['auth_user_file'], $auth_user_cont) === false) {
                die("p2 error: {$_conf['auth_user_file']} ��ۑ��ł��܂���ł����B�F��{$user_st}�o�^���s�B");
            }
            
            // �o�^���� ======================================================

            $body_ht .= "<p class=\"infomsg\">�� �F��{$user_st}�u{$_POST['login_user']}�v��o�^���܂���</p>";
            $body_ht .= "<p><a href=\"{$_SERVER['REQUEST_URI']}{$_conf['k_at_q']}\">p2 start</a></p>";
        
            $login['user'] = $_POST['login_user'];
            $login['pass'] = $crypted_login_pass;
        
            // �v��������΁A�⏕�F�؂�o�^
            registKtaiId();
            registCookie();
        }
    
    } else {
    
        if ($_POST['login_user'] || $_POST['login_pass']) {
            if (!$_POST['login_user']) {
                $_info_msg_ht .= "<p class=\"infomsg\">p2 error: {$user_st}�������͂���Ă��܂���B</p>";
            } elseif (!$_POST['login_pass']) {
                $_info_msg_ht .= "<p class=\"infomsg\">p2 error: {$password_st}�����͂���Ă��܂���B</p>";
            }
        }
        $show_login_form = true;

    }

    //=========================================================
    // HTML�v�����g
    //=========================================================
    P2Util::header_nocache();
    P2Util::header_content_type();
    if ($_conf['doctype']) {
        echo $doctype;
    }
    echo <<<EOP
<html>
<head>
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$ptitle}</title>
EOP;
    if (!$_conf['ktai']) {
        @include("./style/style_css.inc");
        @include("./style/login_first_css.inc");
    }
    echo '</head>
<body>'."\n";

    echo "<h2>{$ptitle}</h2>";

    echo $_info_msg_ht;
    echo $body_ht;

    if ($show_login_form) {
        echo $login_form_ht;
    }

    echo '</body></html>';
    
    return true;
}
?>
