<?php
// p2 -  �T�u�W�F�N�g - �g�уw�b�_�\��
// for subject.php

//===============================================================
// HTML�\���p�ϐ�
//===============================================================
$newtime = date('gis');

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
    $ptitle_url = P2Util::buildQueryUri('read_res_hist.php',
        array(
            UA::getQueryKey() => UA::getQueryValue()
        )
    );
    
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
                //r.i�͂����g���Ă��Ȃ�
                //$ptitle_url = "http://{$aThreadList->host}/{$aThreadList->bbs}/i/";
                $ptitle_url = "http://speedo.ula.cc/test/p.so/{$aThreadList->host}/{$aThreadList->bbs}/"; 
            } else {
                $ptitle_url = "http://c.2ch.net/test/-/{$aThreadList->bbs}/i";
            }
        }
    }
}


// }}}
// {{{ �y�[�W�^�C�g������HTML�ݒ�

if ($aThreadList->spmode == 'taborn') {
    $ptitle_ht = P2View::tagA($ptitle_url, sprintf('<b>%s</b>', hs($aThreadList->itaj))) . '�i���ݒ��j';
} elseif ($aThreadList->spmode == 'soko') {
    $ptitle_ht = P2View::tagA($ptitle_url, sprintf('<b>%s</b>', hs($aThreadList->itaj))) . '�idat�q�Ɂj';
} elseif ($ptitle_url) {
    $ptitle_ht = P2View::tagA($ptitle_url, sprintf('<b>%s</b>', hs($aThreadList->ptitle)));
} else {
    $ptitle_ht = sprintf('<b>%s</b>', hs($aThreadList->ptitle));
}

// }}}

// �t�H�[��HTML
$sb_form_hidden_ht = <<<EOP
    <input type="hidden" name="detect_hint" value="����">
    <input type="hidden" name="bbs" value="{$aThreadList->bbs}">
    <input type="hidden" name="host" value="{$aThreadList->host}">
    <input type="hidden" name="spmode" value="{$aThreadList->spmode}">
    {$_conf['k_input_ht']}
EOP;

// �t�B���^�����t�H�[��HTML
$word_hs = htmlspecialchars($word, ENT_QUOTES);
$filter_form_ht = '';
if (
    !$aThreadList->spmode
    or $aThreadList->spmode == 'palace'
    or in_array($aThreadList->spmode, array('fav', 'recent')) && geti($_REQUEST['norefresh'])
) {
    $filter_form_ht = <<<EOP
<form method="GET" action="subject.php" accept-charset="{$_conf['accept_charset']}">
    {$sb_form_hidden_ht}
    <input type="text" id="word" name="word" value="{$word_hs}" size="12">
    <input type="submit" name="submit_kensaku" value="����">
</form>\n
EOP;
}

// ��������
$hit_ht = '';
if (!empty($GLOBALS['sb_mikke_num'])) {
    $hit_ht = sprintf(
        '<div>"%s" %shit!</div>',
        hs($GLOBALS['word']), hs($GLOBALS['sb_mikke_num'])
    );
}

$body_at = P2View::getBodyAttrK();
$hr = P2View::getHrHtmlK();

//=================================================
// �w�b�_HTML���v�����g
//=================================================
P2Util::headerNoCache();
P2View::printDoctypeTag();
?>
<html>
<head>
<?php
P2View::printHeadMetasHtml();
?>
	<title><?php eh($aThreadList->ptitle); ?></title>
</head>
<body<?php echo $body_at; ?>>
<?php
P2Util::printInfoHtml();

require_once P2_LIB_DIR . '/sb_toolbar_k.inc.php'; // getShinchakuMatomeATag()
?>
<p><?php echo $ptitle_ht; ?> <?php echo getShinchakuMatomeATag($aThreadList, $shinchaku_num); ?></p>
<?php

echo $filter_form_ht;
echo $hit_ht;
echo $hr;

