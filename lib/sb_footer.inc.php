<?php
/*
	p2 - �T�u�W�F�N�g - �t�b�^�\��
	for subject.php
*/

$bbs_q = "&amp;bbs=" . $aThreadList->bbs;
$sid_q = defined('SID') ? '&amp;' . strip_tags(SID) : '';

// dat�q��
// �X�y�V�������[�h�łȂ���΁A�܂��͂��ځ[�񃊃X�g�Ȃ�
if (!$aThreadList->spmode or $aThreadList->spmode == "taborn") {
	$dat_soko_ht = <<<EOP
	<a href="{$_conf['subject_php']}?host={$aThreadList->host}{$bbs_q}{$norefresh_q}&amp;spmode=soko" target="_self">dat�q��</a> | 
EOP;
} else {
    $dat_soko_ht = '';
}

// ���ځ[�񒆂̃X���b�h
if (!empty($ta_num)) {
	$taborn_link_ht = <<<EOP
	<a href="{$_conf['subject_php']}?host={$aThreadList->host}{$bbs_q}{$norefresh_q}&amp;spmode=taborn" target="_self">���ځ[�񒆂̃X���b�h ({$ta_num})</a> | 
EOP;
} else {
    $taborn_link_ht = '';
}

// �V�K�X���b�h�쐬
if (!$aThreadList->spmode and !P2Util::isHostKossoriEnq($aThreadList->host)) {
	$buildnewthread_ht = <<<EOP
	<a href="post_form.php?host={$aThreadList->host}{$bbs_q}&amp;newthread=true" target="_self" onClick="return !openSubWin('post_form.php?host={$aThreadList->host}{$bbs_q}&amp;newthread=true&amp;popup=1{$sid_q}',{$STYLE['post_pop_size']},1,0)">�V�K�X���b�h�쐬</a>
EOP;
} else {
    $buildnewthread_ht = '';
}


//================================================================
// HTML�v�����g
//================================================================

echo "</table>\n";

// �`�F�b�N�t�H�[��
echo $check_form_ht;

// �t�H�[���t�b�^
echo <<<EOP
		<input type="hidden" name="host" value="{$aThreadList->host}">
		<input type="hidden" name="bbs" value="{$aThreadList->bbs}">
		<input type="hidden" name="spmode" value="{$aThreadList->spmode}">
	</form>\n
EOP;
	
// sbject �c�[���o�[
include P2_LIB_DIR . '/sb_toolbar.inc.php';

echo "<p>";
echo $dat_soko_ht;
echo $taborn_link_ht;
echo $buildnewthread_ht;
echo "</p>";

// �X�y�V�������[�h�łȂ���΃t�H�[�����͂�⊮
$ini_url_text = '';
if (!$aThreadList->spmode) {
    // �������
	if (P2Util::isHostJbbsShitaraba($aThreadList->host)) {
		$ini_url_text = "http://{$aThreadList->host}/bbs/read.cgi?BBS={$aThreadList->bbs}&KEY=";
    // �܂�BBS
	} elseif (P2Util::isHostMachiBbs($aThreadList->host)) {
		$ini_url_text = "http://{$aThreadList->host}/bbs/read.pl?BBS={$aThreadList->bbs}&KEY=";
    // �܂��r�˂���
	} elseif (P2Util::isHostMachiBbsNet($aThreadList->host)) {
		$ini_url_text = "http://{$aThreadList->host}/test/read.cgi?bbs={$aThreadList->bbs}&key=";
	} else {
		$ini_url_text = "http://{$aThreadList->host}/test/read.cgi/{$aThreadList->bbs}/";
	}
}

//if (!$aThreadList->spmode || $aThreadList->spmode=="fav" || $aThreadList->spmode=="recent" || $aThreadList->spmode=="res_hist") {

$onClick_ht = <<<EOP
var url_v=document.forms["urlform"].elements["url_text"].value;
if (url_v=="" || url_v=="{$ini_url_text}") {
	alert("�������X���b�h��URL����͂��ĉ������B ��Fhttp://pc.2ch.net/test/read.cgi/mac/1034199997/");
	return false;
}
EOP;

echo <<<EOP
	<form id="urlform" method="GET" action="{$_conf['read_php']}" target="read">
			2ch�̃X��URL�𒼐ڎw��
			<input id="url_text" type="text" value="{$ini_url_text}" name="url" size="62">
			<input type="submit" name="btnG" value="�\��" onClick='{$onClick_ht}'>
	</form>\n
EOP;

//}

echo '</body></html>';

