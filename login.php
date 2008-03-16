<?php
/**
 * rep2 ���O�C��
 */

include_once './conf/conf.inc.php';
require_once P2_LIBRARY_DIR . '/filectl.class.php';

$_login->authorize(); // ���[�U�F��

//=========================================================
// �����o���p�ϐ�
//=========================================================
$p_htm = array();

// �\������
$p_str = array(
    'ptitle'        => 'rep2�F�؃��[�U�Ǘ�',
    'autho_user'    => '�F�؃��[�U',
    'logout'        => '���O�A�E�g',
    'password'      => '�p�X���[�h',
    'login'         => '���O�C��',
    'user'          => '���[�U'
);

// �g�їp�\��������ϊ�
if ($_conf['ktai'] && function_exists('mb_convert_kana')) {
    foreach ($p_str as $k => $v) {
        $p_str[$k] = mb_convert_kana($v, 'rnsk');
    }
}

// �i�g�сj���O�C���pURL
//$user_u_q = !empty($_conf['ktai']) ? '' : '?user=' . $_login->user_u;
//$url = rtrim(dirname(P2Util::getMyUrl()), '/') . '/' . $user_u_q . '&amp;b=k';
$url = rtrim(dirname(P2Util::getMyUrl()), '/') . '/?b=k';

$p_htm['ktai_url'] = '�g��' . $p_str['login'] . '�pURL <a href="' . $url . '" target="_blank">' . $url . '</a><br>';

//====================================================
// ���[�U�o�^����
//====================================================
if (isset($_POST['form_login_pass'])) {

    // ���̓`�F�b�N
    if (!preg_match('/^[0-9a-zA-Z_]+$/', $_POST['form_login_pass'])) {
        P2Util::pushInfoHtml("<p>rep2 error: {$p_str['password']}�𔼊p�p�����œ��͂��ĉ������B</p>");

    // �p�X���[�h�ύX�o�^�������s��
    } else {
        $crypted_login_pass = sha1($_POST['form_login_pass']);
        $auth_user_cont = <<<EOP
<?php
\$rec_login_user_u = '{$_login->user_u}';
\$rec_login_pass_x = '{$crypted_login_pass}';
?>
EOP;
        FileCtl::make_datafile($_conf['auth_user_file'], $_conf['pass_perm']);
        $fp = @fopen($_conf['auth_user_file'], "wb") or die("rep2 Error: {$_conf['auth_user_file']} ��ۑ��ł��܂���ł����B�F�؃��[�U�o�^���s�B");
        @flock($fp, LOCK_EX);
        fputs($fp, $auth_user_cont);
        @flock($fp, LOCK_UN);
        fclose($fp);

        P2Util::pushInfoHtml('<p>���F�؃p�X���[�h��ύX�o�^���܂���</p>');
    }

}

//====================================================
// �⏕�F��
//====================================================
$mobile = &Net_UserAgent_Mobile::singleton();

// EZ�F��
if (!is_null($_SERVER['HTTP_X_UP_SUBNO'])) {
    if (file_exists($_conf['auth_ez_file'])) {
        $p_htm['auth_ctl'] = <<<EOP
EZ�[��ID�F�ؓo�^��[<a href="{$_SERVER['SCRIPT_NAME']}?ctl_regist_ez=1{$_conf['k_at_a']}">����</a>]<br>
EOP;
    } else {
        if ($_login->pass_x) {
            $p_htm['auth_ctl'] = <<<EOP
[<a href="{$_SERVER['SCRIPT_NAME']}?ctl_regist_ez=1&amp;regist_ez=1{$_conf['k_at_a']}">EZ�[��ID�ŔF�؂�o�^</a>]<br>
EOP;
        }
    }

// J�F��
} elseif ($mobile->isVodafone() && ($SN = $mobile->getSerialNumber()) !== null) {
    if (file_exists($_conf['auth_jp_file'])) {
        $p_htm['auth_ctl'] = <<<EOP
J�[��ID�F�ؓo�^��[<a href="{$_SERVER['SCRIPT_NAME']}?ctl_regist_jp=1{$_conf['k_at_a']}">����</a>]<br>
EOP;
    } else {
        if ($_login->pass_x) {
            $p_htm['auth_ctl'] = <<<EOP
[<a href="{$_SERVER['SCRIPT_NAME']}?ctl_regist_jp=1&amp;regist_jp=1{$_conf['k_at_a']}">J�[��ID�ŔF�؂�o�^</a>]<br>
EOP;
        }
    }

// DoCoMo�F��
} elseif ($mobile->isDoCoMo()) {
    if (file_exists($_conf['auth_docomo_file'])) {
        $p_htm['auth_ctl'] = <<<EOP
DoCoMo�[��ID�F�ؓo�^��[<a href="{$_SERVER['SCRIPT_NAME']}?ctl_regist_docomo=1{$_conf['k_at_a']}">����</a>]<br>
EOP;
    } else {
        if ($_login->pass_x) {
            $p_htm['auth_ctl'] = <<<EOP
[<a href="{$_SERVER['SCRIPT_NAME']}?ctl_regist_docomo=1&amp;regist_docomo=1{$_conf['k_at_a']}" utn>DoCoMo�[��ID�ŔF�؂�o�^</a>]<br>
EOP;
        }
    }

// Cookie�F��
} else {
    if ($_login->checkUserPwWithCid($_COOKIE['cid'])) {
            $p_htm['auth_cookie'] = <<<EOP
cookie�F�ؓo�^��[<a href="cookie.php?ctl_regist_cookie=1{$_conf['k_at_a']}">����</a>]<br>
EOP;
    } else {
        if ($_login->pass_x) {
            $p_htm['auth_cookie'] = <<<EOP
[<a href="cookie.php?ctl_regist_cookie=1&amp;regist_cookie=1{$_conf['k_at_a']}">cookie�ŔF�؂�o�^</a>]<br>
EOP;
        }
    }
}

//====================================================
// Cookie�F�؃`�F�b�N
//====================================================
if (!empty($_REQUEST['check_regist_cookie'])) {

    if ($_login->checkUserPwWithCid($_COOKIE['cid'])) {
        if ($_REQUEST['regist_cookie'] == '1') {
            P2Util::pushInfoHtml('<p>��cookie�F�ؓo�^����</p>');
        } else {
            P2Util::pushInfoHtml('<p>�~cookie�F�؉������s</p>');
        }

    } else {
        if ($_REQUEST['regist_cookie'] == '1') {
            P2Util::pushInfoHtml('<p>�~cookie�F�ؓo�^���s</p>');
        } else  {
            P2Util::pushInfoHtml('<p>��cookie�F�؉�������</p>');
        }
    }
}

//====================================================
// �F�؃��[�U�o�^�t�H�[��
//====================================================
$login_form_ht = <<<EOP
<form id="login_change" method="POST" action="{$_SERVER['SCRIPT_NAME']}" target="_self">
    {$p_str['password']}�̕ύX<br>
    {$_conf['k_input_ht']}
    �V����{$p_str['password']}: <input type="password" name="form_login_pass">
    <br>
    <input type="submit" name="submit" value="�ύX�o�^">
</form>\n
EOP;

if ($_conf['ktai']) {
    $login_form_ht = '<hr>'.$login_form_ht;
}

//=========================================================
// HTML�v�����g
//=========================================================
$p_htm['body_onload'] = '';
if (!$_conf['ktai']) {
    $p_htm['body_onload'] = ' onLoad="setWinTitle();"';
}

P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$p_str['ptitle']}</title>\n
EOP;

if (!$_conf['ktai']) {
    echo <<<EOP
    <link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=login&amp;skin={$skin_en}" type="text/css">
    <script type="text/javascript" src="js/basic.js?{$_conf['p2expack']}"></script>\n
EOP;
}

$body_at = ($_conf['ktai']) ? $_conf['k_colors'] : $p_htm['body_onload'];
echo <<<EOP
</head>
<body{$body_at}>
EOP;

if (!$_conf['ktai']) {
    echo <<<EOP
<p id="pan_menu"><a href="setting.php">���O�C���Ǘ�</a> &gt; {$p_str['ptitle']}</p>
EOP;
}

// ���\��
P2Util::printInfoHtml();

echo '<p id="login_status">';
echo <<<EOP
{$p_str['autho_user']}: {$_login->user_u}<br>
{$p_htm['auth_ctl']}
{$p_htm['auth_cookie']}
<br>
[<a href="./index.php?logout=1" target="_parent">{$p_str['logout']}����</a>]
EOP;
echo '</p>';

echo $login_form_ht;

if ($_conf['ktai']) {
    echo "<hr>\n";
    echo $_conf['k_to_index_ht'];
}

echo '</body></html>';

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
