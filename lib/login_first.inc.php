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
    $ptitle_ht = 'rep2';
    
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
    require_once P2_LIB_DIR . '/hostcheck.class.php';
    
    // EZ�F��
    if (!empty($_SERVER['HTTP_X_UP_SUBNO'])) {
        if (file_exists($_conf['auth_ez_file'])) {
        } else {
            $auth_sub_input_ht = '<input type="hidden" name="ctl_regist_ez" value="1">' . "\n" .
                '<input type="checkbox" name="regist_ez" value="1" checked>EZ�[��ID�ŔF�؂�o�^<br>';
        }

    // SoftBank�F��
    // http://www.dp.j-phone.com/dp/tool_dl/web/useragent.php
    } elseif (HostCheck::isAddrSoftBank() and P2Util::getSoftBankID()) {
        if (file_exists($_conf['auth_jp_file'])) {
        } else {
            $auth_sub_input_ht = '<input type="hidden" name="ctl_regist_jp" value="1">' . "\n" .
                '<input type="checkbox" name="regist_jp" value="1" checked>SoftBank�[��ID�ŔF�؂�o�^<br>';
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
            if (empty($_POST['regist_cookie'])) {
                $regist_cookie_checked = '';
            }
        }
        $ignore_cip_checked = '';
        if (isset($_POST['submit_new']) || isset($_POST['submit_member'])) {
            if (geti($_POST['ignore_cip']) == '1') {
                $ignore_cip_checked = ' checked';
            }
        } else {
            if (geti($_COOKIE['ignore_cip']) == '1') {
                $ignore_cip_checked = ' checked';
            }
        }
        $auth_sub_input_ht = '<input type="hidden" name="ctl_regist_cookie" value="1">'
          . sprintf('<input type="checkbox" id="regist_cookie" name="regist_cookie" value="1"%s><label for="regist_cookie">���O�C������Cookie�ɕۑ�����i�����j</label><br>', $regist_cookie_checked)
          . sprintf('<input type="checkbox" id="ignore_cip" name="ignore_cip" value="1"%s><label for="ignore_cip">Cookie�F�؎���IP�̓��ꐫ���`�F�b�N���Ȃ�</label><br>', $ignore_cip_checked);
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
        $uri = $myname . '?guid=ON&user=' . urlencode($_login->user_u);
        $docomo_utn_ht = '<p><a href="' . hs($uri) . '" utn>DoCoMo�ŗL�[���F��</a></p>';
    }

    // DoCoMo�Ȃ烊�g���C���Ƀp�X���[�h���͂� password �� text �Ƃ���
    // �iDoCoMo��password���͂����S�}�X�N�����UI�ŁA���̓G���[���킩��ɂ��߂���j
    if (isset($post['form_login_pass']) and $mobile->isDoCoMo()) {
        $type = "text";
    } else {
        $type = "password";
    }

    // {{{ ���O�C���p�t�H�[���𐶐�
    
    $ruri = $_SERVER['REQUEST_URI'];
    if (!preg_match('/(\\?|&)guid=ON/i', $ruri)) {
        $mark = (strpos($_SERVER['REQUEST_URI'], '?') === false) ? '?': '&';
        $ruri = $_SERVER['REQUEST_URI'] . $mark . 'guid=ON';
    }
    $REQUEST_URI_hs = hs($ruri);
    
    if (!empty($GLOBALS['brazil']) or file_exists($_conf['auth_user_file'])) {
        $submit_ht = '<input type="submit" name="submit_member" value="���[�U���O�C��">';
    } else {
        // submit_newuser��name��ς������C��
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
    $isAllowedNewUser = empty($GLOBALS['brazil']) ? true : false;
    
    if (
        $isAllowedNewUser
        and !file_exists($_conf['auth_user_file']) && !$_login_failed_flag
        and !empty($_POST['submit_new']) && $post['form_login_id'] && $post['form_login_pass']
    ) {
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
    P2Util::headerNoCache();
    P2View::printDoctypeTag();
    ?>
<html lang="ja">
<head>
<?php
    P2View::printExtraHeadersHtml();
    ?>
	<title><?php echo $ptitle_ht; ?></title>
    <?php
    if (UA::isPC()) {
        P2View::printIncludeCssHtml('style');
        P2View::printIncludeCssHtml('login_first');
        ?>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <?php
    }
    ?>
    </head><body>
    <h3><?php echo $ptitle_ht; ?></h3><?php

    P2Util::printInfoHtml();
    
    echo $body_ht;

    if ($show_login_form_flag) {
        echo $login_form_ht;
        if (!(HostCheck::isAddrLocal() || HostCheck::isAddrPrivate())) {
        ?><p>
	<font style="font-size:9pt" color="gray">���v���C�x�[�g���p�̂��߂̃V�X�e���ł��B<br>
	���̃y�[�W�ւ̃A�N�Z�XURL�𕔊O�҂�<br>
	�s���葽���Ɍ��m���邱�Ƃ��֎~���܂��B<br>
	���O�҂ɂ�郍�O�C�����s�́A<br>
	�s���A�N�Z�X�Ƃ��ċL�^����܂��B</font></p><?php
        }
    }

    ?></body></html><?php
}
