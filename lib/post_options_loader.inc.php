<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0: */
/* mi: charset=Shift_JIS */

// p2 - ���X�������݃t�H�[���̋@�\�ǂݍ���
// read_footer.inc.php �� post_form.php ����Ă΂�Ă���

$fake_time = -10; // time ��10���O�ɋU��
$time = time() - 9*60*60;
$time = $time + $fake_time * 60;

$csrfid = P2Util::getCsrfId();


$resv = P2Util::getDefaultResValues($host, $bbs, $key);

// {{{ ����Ƀ��X

// inyou:1 ���p
// inyou:2 �v���r���[
// inyou:3 ���p�{�v���r���[

$htm['orig_msg'] = '';
if ((basename($_SERVER['SCRIPT_NAME']) == 'post_form.php' || !empty($_GET['inyou'])) && !empty($_GET['resnum'])) {
    $q_resnum = $_GET['resnum'];
    if (!($_GET['inyou'] == 2 && strlen($resv['MESSAGE']))) {
        $resv['MESSAGE'] = '>>' . $q_resnum . "\r\n";
    }
    if (!empty($_GET['inyou'])) {
        require_once P2_LIB_DIR . '/thread.class.php';
        require_once P2_LIB_DIR . '/threadread.class.php';
        $aThread = &new ThreadRead;
        $aThread->setThreadPathInfo($host, $bbs, $key);
        $aThread->readDat($aThread->keydat);
        $q_resar = $aThread->explodeDatLine($aThread->datlines[$q_resnum - 1]);
        $q_resar = array_map('trim', $q_resar);
        $q_resar[3] = strip_tags($q_resar[3], '<br>');
        $q_resar[3] = P2Util::htmlEntityDecodeLite($q_resar[3]);
        if ($_GET['inyou'] == 1 || $_GET['inyou'] == 3) {
            // ���p���X�ԍ����ł��Ă��܂�Ȃ��悤�ɁA��̔��p�X�y�[�X�����Ă���
            $resv['MESSAGE'] .= '>  ';
            $resv['MESSAGE'] .= preg_replace('/ *<br> ?/',"\r\n>  ", $q_resar[3]);
            $resv['MESSAGE'] .= "\r\n";
        }
        if ($_GET['inyou'] == 2 || $_GET['inyou'] == 3) {
            // <table border="0" cellpadding="0" cellspacing="0"><tr><td>
            $htm['orig_msg'] = <<<EOM
<blockquote id="original_msg">
    <div>
        <span class="prvw_resnum">{$q_resnum}</span>
        �F<b class="prvw_name">{$q_resar[0]}</b>
        �F<span class="prvw_mail">{$q_resar[1]}</span>
        �F<span class="prvw_dateid">{$q_resar[2]}</span>
    <br>
    <div class="prvw_msg">{$q_resar[3]}</div>
</blockquote>
EOM;
        }
    }
}

// }}}

$hs = array_map(create_function('$n', 'return htmlspecialchars($n, ENT_QUOTES);'), $resv);


// �\���w��
// �Q�l �N���V�b�N COLS='60' ROWS='8'
$mobile = &Net_UserAgent_Mobile::singleton();

$name_size_at = '';
$mail_size_at = '';

// PC
if (empty($_conf['ktai'])) {
    $name_size_at = ' size="19"';
    $mail_size_at = ' size="19"';
    $msg_cols_at = sprintf(' cols="%d"', $STYLE['post_msg_cols']);
    $wrap = 'off';
    
// willcom
// �ʏ��PC�p�ݒ�ɏ����邪�A�g�їp�ݒ肪�Z�b�g����Ă���΁A������ɏ�����B
} elseif($mobile->isWillcom()) {
    if ($_conf['k_post_msg_cols']) {
        $msg_cols_at = sprintf(' cols="%d"', $_conf['k_post_msg_cols']);
    } else {
        $msg_cols_at = sprintf(' cols="%d"', $STYLE['post_msg_cols']);
    }
    // $STYLE['post_msg_rows'] => 10
    $_conf['k_post_msg_rows'] and $STYLE['post_msg_rows'] = (int)$_conf['k_post_msg_rows'];
    
    $wrap = 'soft';

// �g��
} else {
    if ($_conf['k_post_msg_cols']) {
        $msg_cols_at = sprintf(' cols="%d"', $_conf['k_post_msg_cols']);
    } else {
        $msg_cols_at = '';
    }
    if ($_conf['k_post_msg_rows']) {
        $STYLE['post_msg_rows'] = (int)$_conf['k_post_msg_rows'];
    } else {
        $STYLE['post_msg_rows'] = 5; // �g�їp�f�t�H���g�l
    }
    
    $wrap = 'soft';
}


!isset($htm['res_disabled']) and $htm['res_disabled'] = '';

// Be��������
if (P2Util::isHost2chs($host) and $_conf['be_2ch_code'] && $_conf['be_2ch_mail']) {
    $htm['be2ch'] = '<input id="submit_beres" type="submit" name="submit_beres" value="BE�ŏ�������" onClick="setHiddenValue(this);">';
} else {
    $htm['be2ch'] = '';
}

// be�ł͏������݂𖳌��ɂ���
$htm['title_need_be'] = '';
if (P2Util::isBbsBe2chNet($host, $bbs)) {
    // ����ς薳���ɂ��Ȃ��B�������ݎ��s���ɁA2ch����Be���O�C���ւ̗U��������̂ŁB
    //$htm['res_disabled'] = ' disabled';
    if ($_conf['be_2ch_code'] && $_conf['be_2ch_mail']) {
        $htm['title_need_be'] = ' title="Be�ɂ��A����Be�������݂��܂�"';
    } else {
        $htm['title_need_be'] = ' title="�������ނɂ�Be���O�C�����K�v�ł�"';
    }
}

// PC�p sage checkbox
$on_check_sage = '';
$sage_cb_ht = '';
if (!$_conf['ktai']) {
    $on_check_sage = ' onChange="checkSage();"';
    $sage_cb_ht = <<<EOP
<input id="sage" type="checkbox" onClick="mailSage();"><label for="sage">sage</label><br>
EOP;
}

// {{{ 2ch����������

$htm['maru_kakiko'] = '';
if (P2Util::isHost2chs($host) and file_exists($_conf['sid2ch_php'])) {
    $maru_kakiko_checked = empty($_conf['maru_kakiko']) ? '' : ' checked';
    $htm['maru_kakiko'] = <<<EOP
<span title="2ch��ID�̎g�p"><input id="maru_kakiko" name="maru_kakiko" type="checkbox" value="1"{$maru_kakiko_checked}><label for="maru_kakiko">��</label></span>
EOP;
}

// }}}
// {{{�\�[�X�R�[�h�␳�p�`�F�b�N�{�b�N�X

$htm['src_fix'] = '';
if (!$_conf['ktai']) {
    if ($_conf['editor_srcfix'] == 1 ||
        ($_conf['editor_srcfix'] == 2 && preg_match('/pc\d+\.2ch\.net/', $host))
    ) {
        $htm['src_fix'] = '<input type="checkbox" id="fix_source" name="fix_source" value="1"><label for="fix_source">�\�[�X�R�[�h�␳</label>';
    }
}

// }}}

/*
// {{{ �{������̂Ƃ���sage�ĂȂ��Ƃ��ɑ��M���悤�Ƃ���ƒ��ӂ���

$onsubmit_ht = '';

if (!$_conf['ktai']) {
    if ($_exconf['editor']['check_message'] || $_exconf['editor']['check_sage']) {
        $_check_message = (int) $_exconf['editor']['check_message'];
        $_check_sage = (int) $_exconf['editor']['check_sage'];
        $onsubmit_ht = " onsubmit=\"return validateAll({$_check_message},{$_check_sage})\"";
    }
}

// }}}
*/
