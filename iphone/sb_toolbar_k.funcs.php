<?php
// p2 -  �T�u�W�F�N�g -  �c�[���o�[�\���i�g�сj
// for subject.php

$matome_accesskey_at = "";
$matome_accesskey_navi = "{$_conf['k_accesskey']['matome']}.";

// �V���܂Ƃߓǂ�
if (!empty($upper_toolbar_done)) {
	$matome_accesskey_at = " {$_conf['accesskey']}=\"{$_conf['k_accesskey']['matome']}\"";
}

// �q�ɂłȂ����
//�V�܂Ƃ�({$shinchaku_num}) iphone
if ($aThreadList->spmode != "soko") {
	if (!empty($shinchaku_attayo)) {
		$shinchaku_matome_ht = <<<EOP
		<a class="button" href="{$_conf['read_new_k_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}{$norefresh_q}&amp;nt={$newtime}{$_conf['k_at_a']}">�V�܂Ƃ�</a>
EOP;
	} else {
		$shinchaku_matome_ht = <<<EOP
		<a class="button" href="{$_conf['read_new_k_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}&amp;nt={$newtime}{$_conf['k_at_a']}">�V�܂Ƃ�</a>
EOP;
	}
}

// �v�����g
//echo "{$ptitle_ht}<p>{$shinchaku_matome_ht}</p>\n";
echo "<p>{$shinchaku_matome_ht}</p>\n";
 //iPhone   
    // ��ϐ�
$upper_toolbar_done = true;

