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
    } elseif (!$_conf['ktai']) {
        $ptitle_url = "http://{$aThreadList->host}/{$aThreadList->bbs}/i/";
    // �g��
    } else {
        if (!empty($GLOBALS['word']) || !empty($GLOBALS['wakati_words'])) {
            $ptitle_url = $p2_subject_url;
        } else {
            if (P2Util::isHostBbsPink($aThreadList->host)) {
                $ptitle_url = "http://{$aThreadList->host}/{$aThreadList->bbs}/i/";
            } else {
                $ptitle_url = "http://c.2ch.net/test/-/{$aThreadList->bbs}/i";
            }
        }
    }
}

// }}}
// {{{ �y�[�W�^�C�g������HTML�ݒ�

if ($aThreadList->spmode == 'fav' && $_conf['expack.favset.enabled'] && $_conf['favlist_set_num'] > 0) {
    $ptitle_hd = FavSetManager::getFavSetPageTitleHt('m_favlist_set', $aThreadList->ptitle);
} else {
    $ptitle_hd = htmlspecialchars($aThreadList->ptitle, ENT_QUOTES);
}

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

// �t�H�[��
$sb_form_hidden_ht = <<<EOP
{$_conf['detect_hint_input_ht']}
<input type="hidden" name="bbs" value="{$aThreadList->bbs}">
<input type="hidden" name="host" value="{$aThreadList->host}">
<input type="hidden" name="spmode" value="{$aThreadList->spmode}">
{$_conf['k_input_ht']}
EOP;

// �t�B���^����
$hd['word'] = htmlspecialchars($word, ENT_QUOTES);
if (!$aThreadList->spmode) {
    $filter_form_ht = <<<EOP
<form method="GET" action="subject.php" accept-charset="{$_conf['accept_charset']}">
{$sb_form_hidden_ht}
<input type="text" id="word" name="word" value="{$hd['word']}" size="12">
<input type="submit" name="submit_kensaku" value="����">
</form>\n
EOP;
}

// ��������
if (!empty($GLOBALS['sb_mikke_num'])) {
    $hit_ht = "<div>\"{$word}\" {$GLOBALS['sb_mikke_num']}hit!</div>";
}


//=================================================
// �w�b�_HTML���v�����g
//=================================================
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOP
<html>
<head>
{$_conf['meta_charset_ht']}
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
<title>{$ptitle_hd}</title>
</head>
<body{$_conf['k_colors']}>
EOP;

P2Util::printInfoHtml();

include P2_LIBRARY_DIR . '/sb_toolbar_k.inc.php';

echo $filter_form_ht;
echo $hit_ht;
echo '<hr>';

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
