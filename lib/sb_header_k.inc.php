<?php
// p2 -  �T�u�W�F�N�g - �g�уw�b�_�\��
// for subject.php

//===============================================================
// HTML�\���p�ϐ�
//===============================================================
$newtime = date("gis");
$norefresh_q = "&amp;norefresh=1";

// {{{ �y�[�W�^�C�g������URL�ݒ�

$p2_subject_url = "{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}{$_conf['k_at_a']}";

// ���ځ[�� or �q��
if ($aThreadList->spmode == 'taborn' or $aThreadList->spmode == 'soko') {
    $ptitle_url = $p2_subject_url;
    
// �������ݗ���
} elseif ($aThreadList->spmode == 'res_hist') {
    $ptitle_url = "./read_res_hist.php{$_conf['k_at_q']}#footer";
    
// �ʏ� ��
} elseif (!$aThreadList->spmode) {
    // ���ʂȃp�^�[�� index2.html
    // match�o�^���head�Ȃ��ĕ������ق����悳���������A�������X�|���X������̂�����
    if (preg_match('/www\.onpuch\.jp/', $aThreadList->host)) {
        $ptitle_url = $ptitle_url . 'index2.html';
    } elseif (preg_match("/livesoccer\.net/", $aThreadList->host)) {
        $ptitle_url = $ptitle_url . 'index2.html';
    
    // PC
    } elseif (empty($_conf['ktai'])) {
        $ptitle_url = "http://{$aThreadList->host}/{$aThreadList->bbs}/i/";
    // �g��
    } else {
        if (!empty($GLOBALS['word'])) {
            $ptitle_url = $p2_subject_url;
        } else {
            $ptitle_url = "http://c.2ch.net/test/-/{$aThreadList->bbs}/i";
        }
    }
}

// }}}
// {{{ �y�[�W�^�C�g������HTML�ݒ�

$ptitle_hd = htmlspecialchars($aThreadList->ptitle);

if ($aThreadList->spmode == "taborn") {
    $ptitle_ht = <<<EOP
    <a href="{$ptitle_url}"><b>{$aThreadList->itaj_hd}</b></a>�i���ݒ��j
EOP;
} elseif ($aThreadList->spmode == "soko") {
    $ptitle_ht = <<<EOP
    <a href="{$ptitle_url}"><b>{$aThreadList->itaj_hd}</b></a>�idat�q�Ɂj
EOP;
} elseif ($ptitle_url) {
    $ptitle_ht = <<<EOP
    <a href="{$ptitle_url}"><b>{$ptitle_hd}</b></a>
EOP;
} else {
    $ptitle_ht = <<<EOP
    <b>{$ptitle_hd}</b>
EOP;
}

// }}}
// �t�H�[�� ==================================================
$sb_form_hidden_ht = <<<EOP
    <input type="hidden" name="detect_hint" value="����">
    <input type="hidden" name="bbs" value="{$aThreadList->bbs}">
    <input type="hidden" name="host" value="{$aThreadList->host}">
    <input type="hidden" name="spmode" value="{$aThreadList->spmode}">
    {$_conf['k_input_ht']}
EOP;

// �t�B���^���� ==================================================
if (!$aThreadList->spmode) {
    $filter_form_ht = <<<EOP
<form method="GET" action="subject.php" accept-charset="{$_conf['accept_charset']}">
    {$sb_form_hidden_ht}
    <input type="text" id="word" name="word" value="{$word}" size="12">
    <input type="submit" name="submit_kensaku" value="����">
</form>\n
EOP;
}

// ��������
if ($GLOBALS['sb_mikke_num']) {
    $hit_ht = "<div>\"{$word}\" {$GLOBALS['sb_mikke_num']}hit!</div>";
}


//=================================================
//�w�b�_�v�����g
//=================================================
P2Util::header_nocache();
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html>
<head>
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>{$ptitle_hd}</title>
</head>
<body>
EOP;

echo $_info_msg_ht;
$_info_msg_ht = "";

include (P2_LIBRARY_DIR . '/sb_toolbar_k.inc.php');

echo $filter_form_ht;
echo $hit_ht;
echo "<hr>";
?>
