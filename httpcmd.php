<?php
/*
    cmd �������ŃR�}���h����
    �Ԃ�l�́A�e�L�X�g�ŕԂ�
*/

include_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

// {{{ HTTP�w�b�_��XML�錾

P2Util::header_nocache();
header('Content-Type: text/html; charset=Shift_JIS');

// }}}

$r_msg = '';

// cmd���w�肳��Ă��Ȃ���΁A�����Ԃ����ɏI��
if (!isset($_GET['cmd']) && !isset($_POST['cmd'])) {
    die('');
}

// �R�}���h�擾
if (isset($_GET['cmd'])) {
    $cmd = $_GET['cmd'];
} elseif (isset($_POST['cmd'])) {
    $cmd = $_POST['cmd'];
}

// {{{ ���O�폜

if ($cmd == 'delelog') {
    if (isset($_REQUEST['host']) && isset($_REQUEST['bbs']) && isset($_REQUEST['key'])) {
        include_once P2_LIBRARY_DIR . '/dele.inc.php';
        $r = deleteLogs($_REQUEST['host'], $_REQUEST['bbs'], array($_REQUEST['key']));
        if ($r == 1) {
            $r_msg = "1"; // ����
        } elseif ($r == 2) {
            $r_msg = "2"; // �Ȃ�
        } else {
            $r_msg = "0"; // ���s
        }
    }

// }}}
// {{{ ���C�ɃX��

} elseif ($cmd == 'setfav') {
    if (isset($_REQUEST['host']) && isset($_REQUEST['bbs']) && isset($_REQUEST['key']) && isset($_REQUEST['setfav'])) {
        include_once P2_LIBRARY_DIR . '/setfav.inc.php';
        if (isset($_REQUEST['setnum'])) {
            $r = setFav($_REQUEST['host'], $_REQUEST['bbs'], $_REQUEST['key'], $_REQUEST['setfav'], $_REQUEST['setnum']);
        } else {
            $r = setFav($_REQUEST['host'], $_REQUEST['bbs'], $_REQUEST['key'], $_REQUEST['setfav']);
        }
        if (empty($r)) {
            $r_msg = "0"; // ���s
        } elseif ($r == 1) {
            $r_msg = "1"; // ����
        }
    }

// }}}
// {{{ �X���b�h���ځ[��

} elseif ($cmd == 'taborn') {
    if (isset($_REQUEST['host']) && isset($_REQUEST['bbs']) && isset($_REQUEST['key']) && isset($_REQUEST['taborn'])) {
        include_once P2_LIBRARY_DIR . '/settaborn.inc.php';
        $r = settaborn($_REQUEST['host'], $_REQUEST['bbs'], $_REQUEST['key'], $_REQUEST['taborn']);
        if (empty($r)) {
            $r_msg = "0"; // ���s
        } elseif ($r == 1) {
            $r_msg = "1"; // ����
        }
    }

// }}}
// {{{ �������݃t�H�[���̃I�[�g�Z�[�u�i������͎g���Ă��Ȃ��B�ʐM���ׂ�����āA�N�b�L�[�ɂ܂������j

} elseif ($cmd == 'auto_save_post_form') {
    // �������̃e�X�g
    ob_start();
    var_dump($_POST);
    $r_msg = ob_get_clean();

}
// }}}
// {{{ ���ʏo��

if (P2Util::isBrowserSafariGroup()) {
    $r_msg = P2Util::encodeResponseTextForSafari($r_msg);
}
echo $r_msg;

// }}}

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
