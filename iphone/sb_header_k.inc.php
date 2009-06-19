<?php
// p2 -  �T�u�W�F�N�g - �g�уw�b�_�\��
// for subject.php

//===============================================================
// HTML�\���p�ϐ�
//===============================================================
$newtime = date("gis");

// {{{ �y�[�W�^�C�g������URL�ݒ�

$p2_subject_url = P2Util::buildQueryUri($_conf['subject_php'],
    array(
        'host' => $aThreadList->host,
        'bbs'  => $aThreadList->bbs,
        UA::getQueryKey() => UA::getQueryValue()
    )
);

$ptitle_url = null;

// ���ځ[�� or �q��
if ($aThreadList->spmode == 'taborn' or $aThreadList->spmode == 'soko') {
    $ptitle_url = $p2_subject_url;
    
// �������ݗ���
} elseif ($aThreadList->spmode == 'res_hist') {
    $ptitle_url = "./read_res_hist.php{$_conf['k_at_q']}";
    
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

$ptitle_hs = htmlspecialchars($aThreadList->ptitle, ENT_QUOTES);

if ($aThreadList->spmode == "taborn") {
    $ptitle_ht = <<<EOP
    <a href="{$ptitle_url}"><b>{$aThreadList->itaj_hs}</b></a>�i���ݒ��j
EOP;
} elseif ($aThreadList->spmode == "soko") {
    $ptitle_ht = <<<EOP
    <a href="{$ptitle_url}"><b>{$aThreadList->itaj_hs}</b></a>�idat�q�Ɂj
EOP;
} elseif (!empty($ptitle_url)) {
    $ptitle_ht = <<<EOP
    <a href="{$ptitle_url}"><b>{$ptitle_hs}</b></a>
EOP;
} else {
    $ptitle_ht = <<<EOP
    <b>{$ptitle_hs}</b>
EOP;
}

// }}}
// �t�H�[��
$sb_form_hidden_ht = <<<EOP
    <input type="hidden" name="detect_hint" value="����">
    <input type="hidden" name="bbs" value="{$aThreadList->bbs}">
    <input type="hidden" name="host" value="{$aThreadList->host}">
    <input type="hidden" name="spmode" value="{$aThreadList->spmode}">
    {$_conf['k_input_ht']}
EOP;

// �t�B���^����
$word_hs = htmlspecialchars($word, ENT_QUOTES);
if (!$aThreadList->spmode) {
    $filter_form_ht = <<<EOP
<ul><li class="group">����</li></ul>
    <div id="usage" class="panel"><filedset>
<form method="GET" action="subject_i.php" accept-charset="{$_conf['accept_charset']}">
    {$sb_form_hidden_ht}
    <input type="text" id="word" name="word" value="{$word_hs}" size="12">
    <input type="submit" name="submit_kensaku" value="����">
</form>\n
</filedset></div>\n
EOP;
} else {
    $filter_form_ht = '';
}

// ��������
if (!empty($GLOBALS['sb_mikke_num'])) {
    $hit_ht = "<div class=\"panel\"><h2>\"{$word}\" {$GLOBALS['sb_mikke_num']}hit!</h2></div>";
} else {
    $hit_ht = '';
}


//=================================================
// �w�b�_HTML���v�����g
//=================================================
P2Util::headerNoCache();
P2View::printDoctypeTag();
?>
<html>
<head>
<?php
P2View::printExtraHeadersHtml();
?>
<style type="text/css" media="screen">@import "./iui/iui.css";</style>
<script type="text/javascript" src="iphone/js/setfavjs.iphone.js?v=20061206"></script>
	<script type="text/javascript" src="js/basic.js?v=20090429"></script>

<script type="text/javascript"> 
<!-- 
window.onload = function() { 
setTimeout(scrollTo, 100, 0, 1); 
} 
// --> 
</script> 
<title><?php eh($aThreadList->ptitle) ?></title>
</head>
<body>
<div class="toolbar">
<h1 id="pageTitle"><?php eh($aThreadList->ptitle) ?></h1>
</div>
<?php

P2Util::printInfoHtml();

echo $filter_form_ht;
echo $hit_ht;

require_once P2_LIB_DIR . '/sb_toolbar_k.funcs.php'; // getShinchakuMatomeATag()
?>
<p><?php echo getShinchakuMatomeATag($aThreadList, $shinchaku_num); ?></p>
<?php


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
