<?php
// p2 -  �T�u�W�F�N�g -  �c�[���o�[�\���i�g�сj
// for subject.php

$matome_accesskey_at = '';
$matome_accesskey_navi = '';
$spmode_q = '';

// spmode
if ($aThreadList->spmode) {
    $spmode_q = '&amp;spmode=' . $aThreadList->spmode;
    if ($aThreadList->spmode == 'cate' && isset($_GET['cate_id'])) {
        $spmode_q .= sprintf('&amp;cate_id=%d', $_GET['cate_id']);
        if (isset($_GET['cate_name'])) {
            $spmode_q .= '&amp;cate_name=' . rawurlencode($_GET['cate_name']);
        }
    }
}

// �V���܂Ƃߓǂ� =========================================
if ($upper_toolbar_done) {
    $matome_accesskey_at = " {$_conf['accesskey']}=\"{$_conf['k_accesskey']['matome']}\"";
    $matome_accesskey_navi = "{$_conf['k_accesskey']['matome']}.";
}

// �q�ɂłȂ����
if ($aThreadList->spmode != "soko") {
    if ($shinchaku_attayo) {
        $shinchaku_matome_ht = <<<EOP
<a href="{$_conf['read_new_k_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}{$spmode_q}{$norefresh_q}&amp;nt={$newtime}{$_conf['k_at_a']}"{$matome_accesskey_at}>{$matome_accesskey_navi}�V�܂Ƃ�({$shinchaku_num})</a>
EOP;
    } else {
        $shinchaku_matome_ht = <<<EOP
<a href="{$_conf['read_new_k_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}{$spmode_q}&amp;nt={$newtime}{$_conf['k_at_a']}"{$matome_accesskey_at}>{$matome_accesskey_navi}�V�܂Ƃ�</a>
EOP;
    }
}

// �v�����g==============================================
echo "<p>{$ptitle_ht} {$shinchaku_matome_ht}</p>\n";

// ��ϐ�==============================================
$upper_toolbar_done = true;

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
