<?php
/**
 * p2 ���O�C��
 */

require_once './conf/conf.inc.php';

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

// {{{ �i�g�сj���O�C���pURL
/*
$qs = array();
if ($_conf['ktai']) {
    $qs['user'] = $_login->user_u;
}
$qs[UA::getQueryKey()] = UA::getMobileQuery();
$atag = P2View::tagA(
    $uri = P2Util::buildQueryUri(
        rtrim(dirname(P2Util::getMyUrl()), '/') . '/',
        $qs
    ),
    $uri,
    array('target' => '_blank')
);
$ktai_url_ht   = sprintf('�g��%s�pURL %s<br>', hs($p_str['login']), $atag);
*/
// }}}

$csrfid = P2Util::getCsrfId();
$hr = P2View::getHrHtmlK();


// �p�X���[�h�ύX�o�^����
_preExecChangePass();


//====================================================
// �⏕�F��
//====================================================
$auth_ctl_html = '';
$auth_cookie_html = '';

$mobile = &Net_UserAgent_Mobile::singleton();
require_once P2_LIB_DIR . '/HostCheck.php';

// EZ�F��
if (!empty($_SERVER['HTTP_X_UP_SUBNO'])) {
    if ($_login->hasRegistedAuthCarrier('EZWEB')) {
        $atag = P2View::tagA(
            P2Util::buildQueryUri($_SERVER['SCRIPT_NAME'],
                array(
                    'ctl_regist_ez' => '1',
                    UA::getQueryKey() => UA::getQueryValue()
                )
            ),
            '����'
        );
        $auth_ctl_html = sprintf('EZ�[��ID�F�ؓo�^��[%s]<br>', $atag);

    } else {
        if ($_login->pass_x) {
            $atag = P2View::tagA(
                P2Util::buildQueryUri($_SERVER['SCRIPT_NAME'],
                    array(
                        'ctl_regist_ez' => '1',
                        'regist_ez' => '1',
                        UA::getQueryKey() => UA::getQueryValue()
                    )
                ),
                'EZ�[��ID�ŔF�؂�o�^'
            );
            $auth_ctl_html = sprintf('[%s]<br>', $atag);
        }
    }

// SoftBank�F��
} elseif (HostCheck::isAddrSoftBank() && P2Util::getSoftBankID()) {
    if ($_login->hasRegistedAuthCarrier('SOFTBANK')) {
        $atag = P2View::tagA(
            P2Util::buildQueryUri($_SERVER['SCRIPT_NAME'],
                array(
                    'ctl_regist_jp' => '1',
                    UA::getQueryKey() => UA::getQueryValue()
                )
            ),
            '����'
        );
        $auth_ctl_html = sprintf('SoftBank�[��ID�F�ؓo�^��[%s]<br>', $atag);

    } else {
        if ($_login->pass_x) {
            $atag = P2View::tagA(
                P2Util::buildQueryUri($_SERVER['SCRIPT_NAME'],
                    array(
                        'ctl_regist_jp' => '1',
                        'regist_jp' => '1',
                        UA::getQueryKey() => UA::getQueryValue()
                    )
                ),
                'SoftBank�[��ID�ŔF�؂�o�^'
            );
            $auth_ctl_html = sprintf('[%s]<br>', $atag);
        }
    }
    
// docomo�F��
} elseif ($mobile->isDoCoMo()) {
    if ($_login->hasRegistedAuthCarrier('DOCOMO')) {
        $atag = P2View::tagA(
            P2Util::buildQueryUri($_SERVER['SCRIPT_NAME'],
                array(
                    'ctl_regist_docomo' => '1',
                    UA::getQueryKey() => UA::getQueryValue()
                )
            ),
            '����'
        );
        $auth_ctl_html = sprintf('docomo�[��ID�F�ؓo�^��[%s]<br>', $atag);

    } else {
        if ($_login->pass_x) {
            $uri = P2Util::buildQueryUri($_SERVER['SCRIPT_NAME'],
                array(
                    'ctl_regist_docomo' => '1',
                    'regist_docomo' => '1',
                    'guid' => 'ON',
                    UA::getQueryKey() => UA::getQueryValue()
                )
            );
            $atag = sprintf('<a href="%s" utn>%s</a>', $uri, 'docomo�[��ID�ŔF�؂�o�^');
            $auth_ctl_html = sprintf('[%s]<br>', $atag);
        }
    }
    
// Cookie�F��
} else {
    if ($_login->checkUserPwWithCid($_COOKIE['cid'])) {
        $atag = P2View::tagA(
            P2Util::buildQueryUri('cookie.php',
                array(
                    'ctl_regist_cookie' => '1',
                    UA::getQueryKey() => UA::getQueryValue()
                )
            ),
            '����'
        );
        $auth_cookie_html = sprintf('cookie�F�ؓo�^��[%s]<br>', $atag);
        
    } else {
        if ($_login->pass_x) {
            $atag = P2View::tagA(
                P2Util::buildQueryUri('cookie.php',
                    array(
                        'ctl_regist_cookie' => '1',
                        'regist_cookie' => '1',
                        UA::getQueryKey() => UA::getQueryValue()
                    )
                ),
                'cookie�ɔF�؂�o�^'
            );
            $auth_cookie_html = sprintf('[%s]<br>', $atag);
        }
    }
}

// Cookie�F�ؓo�^��������
_preExecCheckRegistCookie();

//=================================================================
// HTML�v�����g
//=================================================================
$p_htm['body_onload'] = '';
if (!$_conf['ktai']) {
    $p_htm['body_onload'] = ' onLoad="setWinTitle();"';
}

$body_at = P2View::getBodyAttrK();


P2Util::headerNoCache();
P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
<?php
P2View::printExtraHeadersHtml();
?>
    <title><?php eh($p_str['ptitle']); ?></title>
<?php
if (!$_conf['ktai']) {
    P2View::printIncludeCssHtml('style');
    P2View::printIncludeCssHtml('login');
    ?>
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script type="text/javascript" src="js/basic.js?v=20090429"></script>
<?php
}
echo <<<EOP
</head>
<body{$p_htm['body_onload']}{$body_at}>
EOP;

if (!$_conf['ktai']) {
    ?>
<p id="pan_menu"><a href="setting.php">���O�C���Ǘ�</a> &gt; <?php eh($p_str['ptitle']); ?></p>
<?php
}

P2Util::printInfoHtml();
?>
<p id="login_status">
<?php eh($p_str['autho_user']) ?>: <?php eh($_login->user_u) ?><br>
<?php echo $auth_ctl_html, $auth_cookie_html ?>
<br>
[<a href="./index.php?logout=1" target="_parent">rep2����<?php eh($p_str['logout']); ?>����</a>]
</p>

<?php
// �F�؃��[�U�o�^�t�H�[��
if ($_conf['ktai']) {
    echo $hr;
}
?>
<form id="login_change" method="POST" action="<?php eh($_SERVER['SCRIPT_NAME']) ?>" target="_self">
    <input type="hidden" name="csrfid" value="<?php eh($csrfid) ?>">
    <?php eh($p_str['password']) ?>�̕ύX<br>
    <?php echo P2View::getInputHiddenKTag(); ?>
    �V����<?php eh($p_str['password']) ?>: <input type="password" name="form_login_pass">
    <br>
    <input type="submit" name="submit" value="�ύX�o�^">
</form>

<?php
if (UA::isK()) {
    echo "$hr\n";
    echo P2View::getBackToIndexKATag();
}
?>
</body></html>
<?php

exit;


//================================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//================================================================================
/**
 * �p�X���[�h�ύX�o�^����
 *
 * @return  void or P2Util::pushInfoHtml() or die
 */
function _preExecChangePass()
{
    global $_login;
    
    if (isset($_POST['form_login_pass'])) {

        // ���̓`�F�b�N
        if (!isset($_POST['csrfid']) || $_POST['csrfid'] != P2Util::getCsrfId()) {
            P2Util::pushInfoHtml('<p>p2 error: �s����POST�ł�</p>');
        
        } elseif (!preg_match('/^[0-9a-zA-Z_]+$/', $_POST['form_login_pass'])) {
            P2Util::pushInfoHtml(
                '<p>p2 error: �p�X���[�h�𔼊p�p�����œ��͂��ĉ������B</p>'
            );
        
        // �p�X���[�h�ύX�o�^�������s��
        } else {

            if (!$_login->savaRegistUserPass($_login->user_u, $_POST['form_login_pass'])) {
                p2die('���[�U�o�^�����������ł��܂���ł����B');
            }
            
            P2Util::pushInfoHtml('<p>���F�؃p�X���[�h��ύX�o�^���܂���</p>');
        }
    }
}

/**
 * Cookie�F�ؓo�^���������̌���
 *
 * @return  void, P2Util::pushInfoHtml()
 */
function _preExecCheckRegistCookie()
{
    global $_login;
    
    if (isset($_REQUEST['check_regist_cookie'])) {

        if ($_login->checkUserPwWithCid($_COOKIE['cid'])) {
            if (geti($_REQUEST['regist_cookie']) == '1') {
                P2Util::pushInfoHtml('<p>��cookie�F�ؓo�^����</p>');
            } else {
                P2Util::pushInfoHtml('<p>�~cookie�F�؉������s</p>');
            }
        
        } else {
            if (geti($_REQUEST['regist_cookie']) == '1') {
                P2Util::pushInfoHtml('<p>�~cookie�F�ؓo�^���s</p>');
            } else  {
                P2Util::pushInfoHtml('<p>��cookie�F�؉�������</p>');
            }
        }
    }
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
