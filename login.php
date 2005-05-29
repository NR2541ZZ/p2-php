<?php
/*
    p2 ���O�C��
*/

include_once './conf/conf.inc.php';  // ��{�ݒ�
require_once (P2_LIBRARY_DIR . '/filectl.class.php');

authorize(); //���[�U�F��

if (!$login['use']) {
    die("p2 info: ���݁A���[�U�F�؂́u���p���Ȃ��v�ݒ�ɂȂ��Ă��܂��B<br>���̋@�\���Ǘ����邽�߂ɂ́A�܂� conf.inc.php �Őݒ��L���ɂ��ĉ������B");
}

//=========================================================
// �����o���p�ϐ�
//=========================================================
$ptitle = "p2�F�؃��[�U�Ǘ�";

$autho_user_ht = "";
$auth_ctl_ht = "";
$auth_sub_input_ht = "";
$ivalue_user = "";

if ($_conf['ktai']) {
    $status_st = "�ð��";
    $autho_user_st = "�F��հ��";
    $client_host_st = "�[��ν�";
    $client_ip_st = "�[��IP���ڽ";
    $browser_ua_st = "��׳��UA";
    $p2error_st = "p2 �װ";
    
    $user_st = "հ��";
    $password_st = "�߽ܰ��";
} else {
    $status_st = "�X�e�[�^�X";
    $autho_user_st = "�F�؃��[�U";
    $client_host_st = "�[���z�X�g";
    $client_ip_st = "�[��IP�A�h���X";
    $browser_ua_st = "�u���E�UUA";
    $p2error_st = "p2 �G���[";
    
    $user_st = "���[�U";
    $password_st = "�p�X���[�h";
}


if ($login['use']) {
    $autho_user_ht = "{$autho_user_st}: {$login['user']}<br>";
}

// �⏕�F�� =====================================
// EZ�F�� ===============
if (!is_null($_SERVER['HTTP_X_UP_SUBNO'])) {
    if (file_exists($_conf['auth_ez_file'])) {
        $auth_ctl_ht=<<<EOP
EZ�[��ID�F�ؓo�^��[<a href="{$_SERVER['PHP_SELF']}?regist_ez=out{$_conf['k_at_a']}">����</a>]<br>
EOP;
    } else {
        if (!is_null($_SERVER['PHP_AUTH_USER'])) {
            $auth_ctl_ht = <<<EOP
[<a href="{$_SERVER['PHP_SELF']}?regist_ez=in{$_conf['k_at_a']}">EZ�[��ID�ŔF�؂�o�^</a>]<br>
EOP;
        }
        $auth_sub_input_ht = <<<EOP
    <input type="checkbox" name="regist_ez" value="in" checked>EZ�[��ID�ŔF�؂�o�^<br>
EOP;
    }

// J�F�� ================
} elseif ($mobile->isVodafone() && $mobile->getSerialNumber()) {
    if (file_exists($_conf['auth_jp_file'])) {
        $auth_ctl_ht=<<<EOP
J�[��ID�F�ؓo�^��[<a href="{$_SERVER['PHP_SELF']}?regist_jp=out{$_conf['k_at_a']}">����</a>]<br>
EOP;
    } else {
        if (!is_null($_SERVER['PHP_AUTH_USER'])) {
            $auth_ctl_ht = <<<EOP
[<a href="{$_SERVER['PHP_SELF']}?regist_jp=in{$_conf['k_at_a']}">J�[��ID�ŔF�؂�o�^</a>]<br>
EOP;
        }
        $auth_sub_input_ht = <<<EOP
    <input type="checkbox" name="regist_jp" value="in" checked>J�[��ID�ŔF�؂�o�^<br>
EOP;
    }
    
// Cookie�F��================
} else {
    if (($_COOKIE['p2_user'] == $login['user']) && ($_COOKIE['p2_pass'] == $login['pass'])) {
            $auth_cookie_ht = <<<EOP
cookie�F�ؓo�^��[<a href="cookie.php?ctl_regist_cookie=1{$_conf['k_at_a']}">����</a>]<br>
EOP;
    } else {
        if (!is_null($_SERVER['PHP_AUTH_USER'])) {
            $auth_cookie_ht = <<<EOP
[<a href="cookie.php?ctl_regist_cookie=1&amp;regist_cookie=1{$_conf['k_at_a']}">cookie�ŔF�؂�o�^</a>]<br>
EOP;
        }
    }
}

//====================================================
// Cookie�F�؃`�F�b�N
//====================================================
if (!empty($_REQUEST['check_regist_cookie'])) {
    if (($_COOKIE['p2_user'] == $login['user']) && ($_COOKIE['p2_pass'] == $login['pass'])) {
        if ($_REQUEST['regist_cookie'] == '1') {
            $_info_msg_ht .= "<p>��cookie�F�ؓo�^����</p>";
        } else {
            $_info_msg_ht .= "<p>�~cookie�F�؉������s</p>";
        }
    } else {
        if ($_REQUEST['regist_cookie'] == '1') {
            $_info_msg_ht .= '<p>�~cookie�F�ؓo�^���s</p>';
        } else  {
            $_info_msg_ht .= '<p>��cookie�F�؉�������</p>';
        }
    }
}


// �F�؃��[�U�ݒ�ǂݍ��� ========
if (file_exists($_conf['auth_user_file'])) {
    include($_conf['auth_user_file']);    
    if (isset($login['user'])) {
        $ivalue_user = $login['user'];
    }
}
if (isset($_POST['login_user'])) {
    $ivalue_user=$_POST['login_user'];
}
    
// �F�؃��[�U�o�^�t�H�[��================
$login_form_ht = <<<EOP
<form id="login_change" method="POST" action="{$_SERVER['PHP_SELF']}" target="_self">
    �F��{$user_st}����{$password_st}�̕ύX<br>
    {$_conf['k_input_ht']}
    {$user_st}: <input type="text" name="login_user" value="{$ivalue_user}"><br>
    {$password_st}: <input type="password" name="login_pass"><br>
    {$auth_sub_input_ht}
    <br>
    <input type="submit" name="submit" value="�ύX�o�^">
</form>\n
EOP;

if ($_conf['ktai']) {
    $login_form_ht = '<hr>'.$login_form_ht;
}

// ���[�U�o�^����=================================
if ($_POST['login_user'] && $_POST['login_pass']) {

    if (!preg_match('/^[0-9a-zA-Z_]+$/', $_POST['login_user']) || !preg_match('/^[0-9a-zA-Z_]+$/', $_POST['login_pass'])) {
        $_info_msg_ht .= "<p>p2 error: {$user_st}����{$password_st}�͔��p�p�����œ��͂��ĉ������B</p>";

    } else {
        $crypted_login_pass = crypt($_POST['login_pass'], $_POST['login_pass']);
        $auth_user_cont = <<<EOP
<?php
\$login['user'] = '{$_POST["login_user"]}';
\$login['pass'] = '{$crypted_login_pass}';
?>
EOP;
        FileCtl::make_datafile($_conf['auth_user_file'], $_conf['pass_perm']); // �t�@�C�����Ȃ���ΐ���
        $fp = @fopen($_conf['auth_user_file'], "wb") or die("p2 Error: {$_conf['auth_user_file']} ��ۑ��ł��܂���ł����B�F�؃��[�U�o�^���s�B");
        @flock($fp, LOCK_EX);
        fputs($fp, $auth_user_cont);
        @flock($fp, LOCK_UN);
        fclose($fp);
        
        $_info_msg_ht.="<p>���F��{$user_st}�u{$_POST['login_user']}�v��o�^���܂���</p>";
    }
    
} else {
    
    if ($_POST['login_user'] || $_POST['login_pass']) {
        if (!$_POST['login_user']) {
            $_info_msg_ht .= "<p>p2 error: {$user_st}�������͂���Ă��܂���B</p>";
        } elseif (!$_POST['login_pass']) {
            $_info_msg_ht .= "<p>p2 error: {$password_st}�����͂���Ă��܂���B</p>";
        }
    }
    
}

$body_onload = "";
if (!$_conf['ktai']) {
    $body_onload = " onLoad=\"setWinTitle();\"";
}

//=========================================================
// HTML�v�����g
//=========================================================
P2Util::header_nocache();
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
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
    @include("./style/login_css.inc");
    echo <<<EOP
    <script type="text/javascript" src="js/basic.js"></script>
EOP;
}
echo <<<EOP
</head>
<body{$body_onload}>
EOP;

if (!$_conf['ktai']) {
    echo <<<EOP
<p id="pan_menu"><a href="setting.php">���O�C���Ǘ�</a> &gt; {$ptitle}</p>
EOP;
}

echo $_info_msg_ht;
$_info_msg_ht = "";
    
echo "<p id=\"login_status\">";
echo <<<EOP
{$autho_user_ht}
{$auth_ctl_ht}
{$auth_cookie_ht}
EOP;
echo "</p>";

echo $login_form_ht;

if ($_conf['ktai']) {
    echo "<hr>\n";
    echo $_conf['k_to_index_ht'];
}

echo '</body></html>';

?>
