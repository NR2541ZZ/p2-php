<?php
/**
 *  p2 - �ŏ��̃��O�C����ʂ�HTML�\������֐�
 *
 * @access  public
 * @return  void
 */
function printLoginFirst(&$_login)
{
    global $_info_msg_ht, $STYLE, $_conf;
    global $_login_failed_flag, $_p2session;
    
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
    
    // �O������̕ϐ�
    $post['form_login_id']   = isset($_POST['form_login_id'])   ? $_POST['form_login_id']   : null;
    $post['form_login_pass'] = isset($_POST['form_login_pass']) ? $_POST['form_login_pass'] : null;
    
    //=========================================================
    // �����o���p�ϐ�
    //=========================================================
    $ptitle = 'rep2';
    
    $myname = basename($_SERVER['SCRIPT_NAME']);

    $auth_sub_input_ht = "";
    $body_ht = "";
    $show_login_form_flag = false;
    
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

    // {{{ �⏕�F��
    
    $mobile = &Net_UserAgent_Mobile::singleton();
    
    // EZ�F��
    if (!empty($_SERVER['HTTP_X_UP_SUBNO'])) {
        if (file_exists($_conf['auth_ez_file'])) {
        } else {
            $auth_sub_input_ht = '<input type="hidden" name="ctl_regist_ez" value="1">' . "\n" .
                '<input type="checkbox" name="regist_ez" value="1" checked>EZ�[��ID�ŔF�؂�o�^<br>';
        }

    // J�F��
    // http://www.dp.j-phone.com/dp/tool_dl/web/useragent.php
    } elseif ($mobile->isVodafone() && ($SN = $mobile->getSerialNumber()) !== NULL) {
        if (file_exists($_conf['auth_jp_file'])) {
        } else {
            $auth_sub_input_ht = '<input type="hidden" name="ctl_regist_jp" value="1">' . "\n" .
                '<input type="checkbox" name="regist_jp" value="1" checked>J�[��ID�ŔF�؂�o�^<br>';
        }

    // DoCoMo�F��
    } elseif ($mobile->isDoCoMo()) {
        if (file_exists($_conf['auth_docomo_file'])) {
        } else {
            $auth_sub_input_ht = '<input type="hidden" name="ctl_regist_docomo" value="1">' . "\n" .
                '<input type="checkbox" name="regist_docomo" value="1" checked>DoCoMo�[��ID�ŔF�؂�o�^<br>';
        }

    // Cookie�F��
    } else {

        $regist_cookie_checked = ' checked';
        if (isset($_POST['submit_new']) || isset($_POST['submit_member'])) {
            if (!isset($_POST['regist_cookie']) or $_POST['regist_cookie'] != '1') {
                $regist_cookie_checked = '';
            }
        }
        $auth_sub_input_ht = '<input type="hidden" name="ctl_regist_cookie" value="1">' . "\n" .
            '<input type="checkbox" id="regist_cookie" name="regist_cookie" value="1"' . $regist_cookie_checked . '><label for="regist_cookie">cookie�ɕۑ�����i�����j</label><br>';
    }
    
    // }}}
    
    // ���O�C���t�H�[������̎w��

    $form_login_id_hs = '';
    if ($_login->validLoginId($_login->user_u)) {
        $form_login_id_hs = hs($_login->user_u);
    } elseif ($_login->validLoginId($post['form_login_id'])) {
        $form_login_id_hs = hs($post['form_login_id']);
    }
    
    
    if (preg_match('/^[0-9a-zA-Z_]+$/', $post['form_login_pass'])) {
        $form_login_pass_hs = hs($post['form_login_pass']);
    } else {
        $form_login_pass_hs = '';
    }

    // DoCoMo�̌ŗL�[���F�؁i�Z�b�V�������p���̂ݗL���j
    $docomo_utn_ht = '';
    
    //if ($_conf['use_session'] && $_login->user_u && $mobile->isDoCoMo()) {
    if ($_conf['use_session'] && $mobile->isDoCoMo()) {
        $docomo_utn_ht = '<p><a href="' . $myname . '?user=' . hs($_login->user_u) . '" utn>DoCoMo�ŗL�[���F��</a></p>';
    }

    // DoCoMo�Ȃ烊�g���C���Ƀp�X���[�h���͂� password �� text �Ƃ���
    // �iDoCoMo��password���͂����S�}�X�N�����UI�ŁA���̓G���[���킩��ɂ��߂���j
    if (isset($post['form_login_pass']) and $mobile->isDoCoMo()) {
        $type = "text";
    } else {
        $type = "password";
    }

    // {{{ ���O�C���p�t�H�[���𐶐�
    
    $REQUEST_URI_hs = hs($_SERVER['REQUEST_URI']);
    
    if (file_exists($_conf['auth_user_file'])) {
        $submit_ht = '<input type="submit" name="submit_member" value="���[�U���O�C��">';
    } else {
        $submit_ht = '<input type="submit" name="submit_new" value="�V�K�o�^">';
    }
    
    $login_form_ht = <<<EOP
{$docomo_utn_ht}
<form id="login" method="POST" action="{$REQUEST_URI_hs}" target="_self" utn>
    {$_conf['k_input_ht']}
    {$p_str['user']}: <input type="text" name="form_login_id" value="{$form_login_id_hs}" istyle="3" size="32"><br>
    {$p_str['password']}: <input type="{$type}" name="form_login_pass" value="{$form_login_pass_hs}" istyle="3"><br>
    {$auth_sub_input_ht}
    <br>
    {$submit_ht}
</form>\n
EOP;

    // }}}

    //=================================================================
    // �V�K���[�U�o�^���� 
    //=================================================================
    
    if (!file_exists($_conf['auth_user_file']) && !$_login_failed_flag and !empty($_POST['submit_new']) && $post['form_login_id'] && $post['form_login_pass']) {

        // {{{ ���̓G���[���`�F�b�N�A����
        
        if (!preg_match('/^[0-9a-zA-Z_]+$/', $post['form_login_id']) || !preg_match('/^[0-9a-zA-Z_]+$/', $post['form_login_pass'])) {
            $_info_msg_ht .= "<p class=\"infomsg\">rep2 error: �u{$p_str['user']}�v���Ɓu{$p_str['password']}�v�͔��p�p�����œ��͂��ĉ������B</p>";
            $show_login_form_flag = true;
        
        // }}}
        // {{{ �o�^����
        
        } else {
            
            $_login->makeUser($post['form_login_id'], $post['form_login_pass']);
            
            // �V�K�o�^����
            $form_login_id_hs = hs($post['form_login_id']);
            $body_ht .= "<p class=\"infomsg\">�� �F��{$p_str['user']}�u{$form_login_id_hs}�v��o�^���܂���</p>";
            $body_ht .= "<p><a href=\"{$myname}?form_login_id={$form_login_id_hs}{$_conf['k_at_a']}\">rep2 start</a></p>";
        
            $_login->setUser($post['form_login_id']);
            $_login->pass_x = sha1($post['form_login_pass']);
            
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
    
        if (isset($post['form_login_id']) || isset($post['form_login_pass'])) {
            $msg_ht .= '<p class="infomsg">';
            if (!$post['form_login_id']) {
                $msg_ht .= "p2 error: �u{$p_str['user']}�v�����͂���Ă��܂���B" . "<br>";
            } elseif (!$_login->validLoginId($post['form_login_id'])) {
                $msg_ht .= "p2 error: �u{$p_str['user']}�v�����񂪕s���ł��B" . "<br>";
            }
            if (!$post['form_login_pass']) {
                $msg_ht .= "p2 error: �u{$p_str['password']}�v�����͂���Ă��܂���B";
            }
            $msg_ht .= '</p>';
            P2Util::pushInfoHtml($msg_ht);
        }

        $show_login_form_flag = true;

    }
    
    // }}}
    
    //=========================================================
    // HTML�\���o��
    //=========================================================
    P2Util::header_nocache();
    echo $_conf['doctype'];
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
        include_once "./style/style_css.inc";
        include_once "./style/login_first_css.inc";
    }
    echo "</head><body>\n";
    echo "<h3>{$ptitle}</h3>\n";

    P2Util::printInfoHtml();
    
    echo $body_ht;

    if ($show_login_form_flag) {
        echo $login_form_ht;
    }

    echo '</body></html>';
}
