<?php
/*
    ajax�p��
    cmd �������ŃR�}���h����
    �Ԃ�l�́A�e�L�X�g�ŕԂ�
*/

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

// {{{ HTTP�w�b�_��XML�錾

P2Util::headerNoCache();
if (UA::isSafariGroup()) {
    header('Content-Type: application/xml; charset=UTF-8');
    $xmldecTag = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
} else {
    header('Content-Type: text/html; charset=Shift_JIS');
    // ���p�Łu�H���v�������Ă镶������R�����g�ɂ���ƃp�[�X�G���[
    //$xmldecTag = '<' . '?xml version="1.0" encoding="Shift_JIS" ?' . '>' . "\n";
    $xmldecTag = '';
}

// }}}

$r_msg_ht = '';

// cmd���w�肳��Ă��Ȃ���΁A�����Ԃ����ɏI��
if (!isset($_GET['cmd']) && !isset($_POST['cmd'])) {
    die;
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
        require_once P2_LIB_DIR . '/dele.inc.php';
        $r = deleteLogs($_REQUEST['host'], $_REQUEST['bbs'], array($_REQUEST['key']));
        if ($r == 1) {
            $r_msg_ht = '1'; // ����
        } elseif ($r == 2) {
            $r_msg_ht = '2'; // �Ȃ�
        } else {
            $r_msg_ht = '0'; // ���s
        }
    }
    
// }}}
// {{{ ���C�ɃX��

} elseif ($cmd == 'setfav') {
    if (isset($_REQUEST['host']) && isset($_REQUEST['bbs']) && isset($_REQUEST['key']) && isset($_REQUEST['setfav'])) {
        require_once P2_LIB_DIR . '/setfav.inc.php';
        $r = setFav($_REQUEST['host'], $_REQUEST['bbs'], $_REQUEST['key'], $_REQUEST['setfav']);
        if (empty($r)) {
            $r_msg_ht = '0'; // ���s
        } elseif ($r == 1) {
            $r_msg_ht = '1'; // ����
        }
    }

// }}}
// {{{ �������݃t�H�[���̃I�[�g�Z�[�u�i������͎g���Ă��Ȃ��B�ʐM���ׂ�����āA�N�b�L�[�ɂ܂������j

} elseif ($cmd == 'auto_save_post_form') {
    // �������̃e�X�g
    ob_start();
    var_dump($_POST);
    $r_msg = ob_get_clean();
    $r_msg_ht = hs($r_msg);

}

// }}}

if (UA::isSafariGroup()) {
    $r_msg_ht = mb_convert_encoding($r_msg_ht, 'UTF-8', 'SJIS-win');
}

// ���ʏo��
echo $xmldecTag;
echo $r_msg_ht;


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
