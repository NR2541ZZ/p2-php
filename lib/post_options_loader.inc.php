<?php
/* vim: set fileencoding=cp932 autoindent noexpandtab ts=4 sw=4 sts=0: */
/* mi: charset=Shift_JIS */

// p2 - ���X�������݃t�H�[���̋@�\�ǂݍ���

$fake_time = -10; // time ��10���O�ɋU��
$time = time() - 9*60*60;
$time = $time + $fake_time * 60;

$csrfid = P2Util::getCsrfId();

$htm['disable_js'] = <<<EOP
<script type="text/javascript">
<!--
// Thanks naoya <http://d.hatena.ne.jp/naoya/20050804/1123152230>

function isNetFront() {
  var ua = navigator.userAgent;
  if (ua.indexOf("NetFront") != -1 || ua.indexOf("AVEFront/") != -1 || ua.indexOf("AVE-Front/") != -1) {
    return true;
  } else {
    return false;
  }
}

function disableSubmit(form) {

  // 2006/02/15 NetFront�Ƃ͑����������ł܂�炵���̂Ŕ�����
  if (isNetFront()) {
    return;
  }
  
  var elements = form.elements;
  for (var i = 0; i < elements.length; i++) {
    if (elements[i].type == 'submit') {
      elements[i].disabled = true;
    }
  }
}

function setHiddenValue(button) {
  
  // 2006/02/15 NetFront�Ƃ͑����������ł܂�炵���̂Ŕ�����
  if (isNetFront()) {
    return;
  }
  
  if (button.name) {
    var q = document.createElement('input');
    q.type = 'hidden';
    q.name = button.name;
    q.value = button.value;
    button.form.appendChild(q);
  }
}

//-->
</script>\n
EOP;

// {{{ key.idx���疼�O�ƃ��[����Ǎ���

if ($lines = @file($key_idx)) {
    $line = explode('<>', rtrim($lines[0]));
    $hd['FROM'] = htmlspecialchars($line[7], ENT_QUOTES);
    $hd['mail'] = htmlspecialchars($line[8], ENT_QUOTES);
}

// }}}

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
    $hd['MESSAGE'] = $last_posted['MESSAGE'];
    $hd['subject'] = $last_posted['subject'];
}

// �󔒂̓��[�U�ݒ�l�ɕϊ�
$hd['FROM'] = ($hd['FROM'] == '') ? htmlspecialchars($_conf['my_FROM'], ENT_QUOTES) : $hd['FROM'];
$hd['mail'] = ($hd['mail'] == '') ? htmlspecialchars($_conf['my_mail'], ENT_QUOTES) : $hd['mail'];

// P2NULL�͋󔒂ɕϊ�
$hd['FROM'] = ($hd['FROM'] == 'P2NULL') ? '' : $hd['FROM'];
$hd['mail'] = ($hd['mail'] == 'P2NULL') ? '' : $hd['mail'];


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

// Be.2ch
if (P2Util::isHost2chs($host) and $_conf['be_2ch_code'] && $_conf['be_2ch_mail']) {
    $htm['be2ch'] = '<input type="submit" name="submit_beres" value="BE�ŏ�������" onClick="setHiddenValue(this);">';
}

// PC�p sage checkbox
if (!$_conf['ktai']) {
    $on_check_sage = 'onChange="checkSage();"';
    $sage_cb_ht = <<<EOP
<input id="sage" type="checkbox" onClick="mailSage();"><label for="sage">sage</label><br>
EOP;
}

// {{{ 2ch����������

$htm['maru_post'] = '';
if (P2Util::isHost2chs($host) and file_exists($_conf['sid2ch_php'])) {
    $htm['maru_post'] = <<<EOP
<span title="2ch��ID�̎g�p"><input id="maru" name="maru" type="checkbox" value="1"><label for="maru">��</label></span>
EOP;
}

// }}}
// {{{�\�[�X�R�[�h�␳�p�`�F�b�N�{�b�N�X

$src_fix_ht = '';
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

?>
