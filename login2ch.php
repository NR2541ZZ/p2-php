<?php
/**
 *    p2 - 2ch�����O�C���Ǘ�
 */

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/FileCtl.php';
require_once P2_LIB_DIR . '/P2Validate.php';

$_login->authorize(); // ���[�U�F��

//================================================================
// �ϐ�
//================================================================
$login2chID   = geti($_POST['login2chID']);
$login2chPW   = geti($_POST['login2chPW']);
$autoLogin2ch = intval(geti($_POST['autoLogin2ch']));

// 2ch ID (���A�h)
if ($login2chID and P2Validate::mail($login2chID)) {
    P2Util::pushInfoHtml('<p>p2 error: �g�p�ł��Ȃ�ID�����񂪊܂܂�Ă��܂�</p>');
    $login2chID = null;
}

// ���m�ȋ�������͕s��
if ($login2chPW and P2Validate::login2chPW($login2chPW)) {
    P2Util::pushInfoHtml('<p>p2 error: �g�p�ł��Ȃ��p�X���[�h�����񂪊܂܂�Ă��܂�</p>');
    $login2chPW = null;
}

//===============================================================
// ���O�C���Ȃ�AID��PW��o�^�ۑ����āA���O�C������
//===============================================================
if ($login2chID && $login2chPW) {

    P2Util::saveIdPw2ch($login2chID, $login2chPW, $autoLogin2ch);

    require_once P2_LIB_DIR . '/login2ch.inc.php';
    login2ch();
}

// �i�t�H�[�����͗p�ɁjID, PW�ݒ��ǂݍ���
if ($array = P2Util::readIdPw2ch()) {
    list($login2chID, $login2chPW, $autoLogin2ch) = $array;
}

// {{{ 2ch���O�C������

if (isset($_GET['login2ch'])) {
    if ($_GET['login2ch'] == "in") {
        require_once P2_LIB_DIR . '/login2ch.inc.php';
        login2ch();
    } elseif ($_GET['login2ch'] == "out") {
        if (file_exists($_conf['sid2ch_php'])) {
            unlink($_conf['sid2ch_php']);
        }
    }
}

// }}}

$hr = P2View::getHrHtmlK();

//================================================================
// �w�b�_
//================================================================
if ($_conf['ktai']) {
    $login_st       = "۸޲�";
    $logout_st      = "۸ޱ��";
    $password_st    = "�߽ܰ��";
} else {
    $login_st       = "���O�C��";
    $logout_st      = "���O�A�E�g";
    $password_st    = "�p�X���[�h";
}

if (file_exists($_conf['sid2ch_php'])) { // 2ch����������
    $ptitle = "��2ch{$login_st}�Ǘ�";
} else {
    $ptitle = "2ch{$login_st}�Ǘ�";
}

$body_at = P2View::getBodyAttrK();

if (!$_conf['ktai']) {
    $body_at .= " onLoad=\"setWinTitle();\"";
}

P2Util::headerNoCache();
P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
<?php
P2View::printExtraHeadersHtml();
echo <<<EOP
    <title>{$ptitle}</title>
EOP;

if (!$_conf['ktai']) {
    P2View::printIncludeCssHtml('style');
    P2View::printIncludeCssHtml('login2ch');
    echo <<<EOP
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script type="text/javascript" src="js/basic.js?v=20090429"></script>
EOP;
}

echo <<<EOP
    <script type="text/javascript">
    <!--
    function checkPass2ch(){ 
        if (pass2ch_input = document.getElementById('login2chPW')) {
            if (pass2ch_input.value == "") {
                alert("�p�X���[�h����͂��ĉ�����");
                return false;
            }
        }
    }
    // -->
    </script>
</head>
<body{$body_at}>
EOP;

if (!$_conf['ktai']) {
    echo <<<EOP
<p id="pan_menu"><a href="setting.php">���O�C���Ǘ�</a> &gt; {$ptitle}</p>
EOP;
}

P2Util::printInfoHtml();

//================================================================
// 2ch�����O�C���t�H�[��
//================================================================

// ���O�C�����Ȃ�
if (file_exists($_conf['sid2ch_php'])) {
    $idsub_str = "��{$login_st}����";
    $form_now_login_ht = <<<EOFORM
    <form id="form_logout" method="GET" action="{$_SERVER['SCRIPT_NAME']}" target="_self">
        ���݁A2�����˂��{$login_st}���ł� 
        {$_conf['k_input_ht']}
        <input type="hidden" name="login2ch" value="out">
        <input type="submit" name="submit" value="{$logout_st}����">
    </form>\n
EOFORM;

} else {
    $idsub_str = "�V�K{$login_st}����";
    if (file_exists($_conf['idpw2ch_php'])) {
        $form_now_login_ht = <<<EOFORM
    <form id="form_logout" method="GET" action="{$_SERVER['SCRIPT_NAME']}" target="_self">
        ���݁A{$login_st}���Ă��܂��� 
        {$_conf['k_input_ht']}
        <input type="hidden" name="login2ch" value="in">
        <input type="submit" name="submit" value="��{$login_st}����">
    </form>\n
EOFORM;
    } else {
        $form_now_login_ht = "<p>���݁A{$login_st}���Ă��܂���</p>";
    }
}

if ($autoLogin2ch) {
    $autoLogin2ch_checked = ' checked="true"';
} else {
    $autoLogin2ch_checked = '';
}

$tora3_url = "http://2ch.tora3.net/";
$tora3_url_r = P2Util::throughIme($tora3_url);

if (!$_conf['ktai']) {
    $id_input_size_at = ' size="30"';
    $pass_input_size_at = ' size="24"';
} else {
    $id_input_size_at = '';
    $pass_input_size_at = '';
}

// HTML�v�����g
?>
<div id="login_status">
<?php echo $form_now_login_ht; ?>
</div>
<?php
if ($_conf['ktai']) {
    echo $hr;
}
?>
<form id="login_with_id" method="POST" action="<?php eh($_SERVER['SCRIPT_NAME']); ?>" target="_self">
    <?php echo P2View::getInputHiddenKTag(); ?>
    ID: <input type="text" name="login2chID" value="<?php eh($login2chID); ?>"<?php echo $id_input_size_at; ?>><br>
    <?php eh($password_st); ?>: <input type="password" name="login2chPW" id="login2chPW"<?php echo $pass_input_size_at; ?>><br>
    <input type="checkbox" id="autoLogin2ch" name="autoLogin2ch" value="1"<?php echo $autoLogin2ch_checked; ?>><label for="autoLogin2ch">�N�����Ɏ���<?php eh($login_st); ?>����</label><br>
    <input type="submit" name="submit" value="<?php eh($idsub_str); ?>" onClick="return checkPass2ch();">
</form>
<?php

if ($_conf['ktai']) {
    echo $hr;
}

//================================================================
// �t�b�^HTML�\��
//================================================================

printf(
    '<p>2ch ID�ɂ��Ă̏ڍׂ͂����灨 <a href="%s" target="_blank">%s</a></p>',
    hs($tora3_url_r),
    hs($tora3_url)
);

if (UA::isK()) {
    echo $hr;
    echo P2View::getBackToIndexKATag();
}

?></body></html><?php


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
