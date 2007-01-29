<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0: */
/* mi: charset=Shift_JIS */

// p2 - ���X�������݃t�H�[���̋@�\�ǂݍ���

$fake_time = -10; // time ��10���O�ɋU��
$time = time() - 9*60*60;
$time = $time + $fake_time * 60;

$csrfid = P2Util::getCsrfId();

// key.idx���疼�O�ƃ��[����Ǎ���
if (file_exists($key_idx) and $lines = file($key_idx)) {
    $line = explode('<>', rtrim($lines[0]));
    $hd['FROM'] = htmlspecialchars($line[7], ENT_QUOTES);
    $hd['mail'] = htmlspecialchars($line[8], ENT_QUOTES);
} else {
    $hd['FROM'] = null;
    $hd['mail'] = null;
}

// �󔒂̓��[�U�ݒ�l�ɕϊ�
$hd['FROM'] = ($hd['FROM'] == '') ? htmlspecialchars($_conf['my_FROM'], ENT_QUOTES) : $hd['FROM'];
$hd['mail'] = ($hd['mail'] == '') ? htmlspecialchars($_conf['my_mail'], ENT_QUOTES) : $hd['mail'];

// P2NULL�͋󔒂ɕϊ�
$hd['FROM'] = ($hd['FROM'] == 'P2NULL') ? '' : $hd['FROM'];
$hd['mail'] = ($hd['mail'] == 'P2NULL') ? '' : $hd['mail'];

// �O���POST���s������ΌĂяo��
$failed_post_file = P2Util::getFailedPostFilePath($host, $bbs, $key);
if ($cont_srd = DataPhp::getDataPhpCont($failed_post_file)) {
    $last_posted = unserialize($cont_srd);
    
    // �܂Ƃ߂ăT�j�^�C�Y
    $last_posted = array_map(create_function('$n', 'return htmlspecialchars($n, ENT_QUOTES);'), $last_posted);
    //$addslashesS = create_function('$str', 'return str_replace("\'", "\\\'", $str);');
    //$last_posted = array_map($addslashesS, $last_posted);

    $hd['FROM'] = $last_posted['FROM'];
    $hd['mail'] = $last_posted['mail'];
    $MESSAGE_hs = $last_posted['MESSAGE'];
    $hd['subject'] = $last_posted['subject'];

} else {
    $MESSAGE_hs = '';
    $hd['subject'] = '';
}


// �\���w��
// �Q�l �N���V�b�N COLS='60' ROWS='8'
$mobile = &Net_UserAgent_Mobile::singleton();
// PC
if (empty($_conf['ktai'])) {
    $name_size_at = ' size="19"';
    $mail_size_at = ' size="19"';
    $msg_cols_at = ' cols="' . $STYLE['post_msg_cols'] . '"';
    $wrap = 'off';
// willcom
} elseif($mobile->isAirHPhone()) {
    $msg_cols_at = ' cols="' . $STYLE['post_msg_cols'] . '"';
    $wrap = 'soft';
// �g��
} else {
    $STYLE['post_msg_rows'] = 5;
    $msg_cols_at = '';
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
if (!$_conf['ktai']) {
    $on_check_sage = 'onChange="checkSage();"';
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
        ($_conf['editor_srcfix'] == 2 && preg_match('/pc\d\.2ch\.net/', $host))
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
// {{{ ����Ƀ��X

// inyou:1 ���p
// inyou:2 �v���r���[
// inyou:3 ���p�{�v���r���[

$htm['orig_msg'] = '';
if ((basename($_SERVER['SCRIPT_NAME']) == 'post_form.php' || !empty($_GET['inyou'])) && !empty($_GET['resnum'])) {
    $q_resnum = $_GET['resnum'];
    if (!($_GET['inyou'] == 2 && strlen($MESSAGE_hs))) {
        $MESSAGE_hs = "&gt;&gt;" . $q_resnum . "\r\n";
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
        if ($_GET['inyou'] == 1 || $_GET['inyou'] == 3) {
            // ���p���X�ԍ����ł��Ă��܂�Ȃ��悤�ɁA��̔��p�X�y�[�X�����Ă���
            $MESSAGE_hs .= "&gt;  ";
            $MESSAGE_hs .= preg_replace("/ *<br> ?/","\r\n&gt;  ", $q_resar[3]);
            $MESSAGE_hs .= "\r\n";
        }
        if ($_GET['inyou'] == 2 || $_GET['inyou'] == 3) {
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
