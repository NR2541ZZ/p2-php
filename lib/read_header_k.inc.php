<?php
/*
    p2 -  �X���b�h�\�� -  �w�b�_���� -  �g�їp for read.php
*/


// �ϐ� =====================================
$diedat_msg = "";

$info_st = "��";
$delete_st = "��";
$prev_st = "�O";
$next_st = "��";
$shinchaku_st = "�V��";
$moto_thre_st = "��";
$siml_thre_st = "��";
$latest_st = "�V";
$dores_st = "ڽ";
$find_st = '��';

$motothre_url = $aThread->getMotoThread();
$ttitle_en = rawurlencode(base64_encode($aThread->ttitle));
$ttitle_en_q = "&amp;ttitle_en=".$ttitle_en;
$bbs_q = "&amp;bbs=".$aThread->bbs;
$key_q = "&amp;key=".$aThread->key;
$offline_q = "&amp;offline=1";

$hd['word'] = htmlspecialchars($GLOBALS['word'], ENT_QUOTES);

//=================================================================
// �w�b�_
//=================================================================

// ���C�Ƀ}�[�N�ݒ�
$favmark = ($aThread->fav) ? '<span class="fav">��</span>' : '<span class="fav">+</span>';
$favdo = ($aThread->fav) ? 0 : 1;

// ���X�i�r�ݒ� =====================================================

$rnum_range = $_conf['k_rnum_range'];
$latest_show_res_num = $_conf['k_rnum_range']; // �ŐVXX

$read_navi_range = "";
$read_navi_previous = "";
$read_navi_previous_btm = "";
$read_navi_next = "";
$read_navi_next_btm = "";
$read_footer_navi_new = "";
$read_footer_navi_new_btm = "";
$read_navi_latest = "";
$read_navi_latest_btm = "";
$read_navi_filter = '';
$read_navi_filter_btm = '';

$pointer_header_at = ' id="header" name="header"';

//----------------------------------------------
// $htm['read_navi_range'] -- 1- 101- 201-

$htm['read_navi_range'] = '';
for ($i = 1; $i <= $aThread->rescount; $i = $i + $rnum_range) {
    $offline_range_q = "";
    $accesskey_at = "";
    if ($i == 1) {
        $accesskey_at = " {$_conf['accesskey']}=\"1\"";
    }
    $ito = $i + $rnum_range -1;
    if ($ito <= $aThread->gotnum) {
        $offline_range_q = $offline_q;
    }
    $htm['read_navi_range'] .= "<a{$accesskey_at}{$pointer_header_at} href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$i}-{$ito}{$offline_range_q}{$_conf['k_at_a']}\">{$i}-</a>\t";
    break;    // 1-�̂ݕ\��
}


//----------------------------------------------
// $read_navi_previous -- �O100
$before_rnum = $aThread->resrange['start'] - $rnum_range;
if ($before_rnum < 1) { $before_rnum = 1; }
if ($aThread->resrange['start'] == 1) {
    $read_navi_previous_isInvisible = true;
}
//if ($before_rnum != 1) {
//    $read_navi_previous_anchor = "#r{$before_rnum}";
//}

if (!$read_navi_previous_isInvisible) {
    $read_navi_previous = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$before_rnum}-{$aThread->resrange['start']}n{$offline_q}{$_conf['k_at_a']}{$read_navi_previous_anchor}\">{$prev_st}</a>";
    $read_navi_previous_btm = "<a {$_conf['accesskey']}=\"{$_conf['k_accesskey']['prev']}\" href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$before_rnum}-{$aThread->resrange['start']}n{$offline_q}{$_conf['k_at_a']}{$read_navi_previous_anchor}\">{$_conf['k_accesskey']['prev']}.{$prev_st}</a>";
}

//----------------------------------------------
// $read_navi_next -- ��100
if ($aThread->resrange['to'] >= $aThread->rescount) {
    $aThread->resrange['to'] = $aThread->rescount;
    //$read_navi_next_anchor = "#r{$aThread->rescount}";
    $read_navi_next_isInvisible = true;
 }else {
    // $read_navi_next_anchor = "#r{$aThread->resrange['to']}";
}
if ($aThread->resrange['to'] == $aThread->rescount) {
    $read_navi_next_anchor = "#r{$aThread->rescount}";
}
$after_rnum=$aThread->resrange['to'] + $rnum_range;

if (!$read_navi_next_isInvisible) {
    $read_navi_next = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->resrange['to']}-{$after_rnum}n{$offline_q}&amp;nt={$newtime}{$_conf['k_at_a']}{$read_navi_next_anchor}\">{$next_st}</a>";
    $read_navi_next_btm = "<a {$_conf['accesskey']}=\"{$_conf['k_accesskey']['next']}\" href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->resrange['to']}-{$after_rnum}n{$offline_q}&amp;nt={$newtime}{$_conf['k_at_a']}{$read_navi_next_anchor}\">{$_conf['k_accesskey']['next']}.{$next_st}</a>";
}

//----------------------------------------------
// $read_footer_navi_new  ������ǂ� �V�����X�̕\��

if($aThread->resrange['to'] == $aThread->rescount) {
    $read_footer_navi_new = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->rescount}-n&amp;nt={$newtime}{$_conf['k_at_a']}#r{$aThread->rescount}\">{$shinchaku_st}</a>";
    $read_footer_navi_new_btm = "<a {$_conf['accesskey']}=\"{$_conf['k_accesskey']['next']}\" href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->rescount}-n&amp;nt={$newtime}{$_conf['k_at_a']}#r{$aThread->rescount}\">{$_conf['k_accesskey']['next']}.{$shinchaku_st}</a>";
}

if (!$read_navi_next_isInvisible) {
    $read_navi_latest = <<<EOP
<a href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls=l{$latest_show_res_num}{$_conf['k_at_a']}">{$latest_st}{$latest_show_res_num}</a> 
EOP;
    $read_navi_latest_btm = <<<EOP
<a {$_conf['accesskey']}="{$_conf['k_accesskey']['latest']}" href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls=l{$latest_show_res_num}{$_conf['k_at_a']}">{$_conf['k_accesskey']['latest']}.{$latest_st}{$latest_show_res_num}</a> 
EOP;
}

// {{{ ����
$read_navi_filter = <<<EOP
<a href="read_filter_k.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$_conf['k_at_a']}">{$find_st}</a>
EOP;
$read_navi_filter_btm = <<<EOP
<a {$_conf['accesskey']}="{$_conf['k_accesskey']['filter']}" href="read_filter_k.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$_conf['k_at_a']}">{$_conf['k_accesskey']['filter']}.{$find_st}</a>
EOP;
// }}}

//====================================================================
// �������̓��ʂȏ���
//====================================================================
if ($filter_hits !== NULL) {
    include (P2_LIBRARY_DIR . '/read_filter_k.inc.php');
    resetReadNaviHeaderK();
}

//====================================================================
// HTML�v�����g
//====================================================================

// {{{ �c�[���o�[����HTML

$similar_q = '&amp;itaj_en=' . rawurlencode(base64_encode($aThread->itaj)) . '&amp;method=similar&amp;word=' . rawurlencode($aThread->ttitle_hc);// . '&amp;refresh=1';
$itaj_hd = htmlspecialchars($aThread->itaj, ENT_QUOTES);
$toolbar_right_ht = <<<EOTOOLBAR
    <a href="{$_conf['subject_php']}?host={$aThread->host}{$bbs_q}{$key_q}{$_conf['k_at_a']}" {$_conf['accesskey']}="{$_conf['k_accesskey']['up']}">{$_conf['k_accesskey']['up']}.{$itaj_hd}</a>
    <a href="{$_conf['subject_php']}?host={$aThread->host}{$bbs_q}{$key_q}{$similar_q}{$_conf['k_at_a']}">{$siml_thre_st}</a>
    <a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$_conf['k_at_a']}" {$_conf['accesskey']}="{$_conf['k_accesskey']['info']}">{$_conf['k_accesskey']['info']}.{$info_st}</a> 
    <a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}&amp;dele=1{$_conf['k_at_a']}" {$_conf['accesskey']}="{$_conf['k_accesskey']['dele']}">{$_conf['k_accesskey']['dele']}.{$delete_st}</a> 
    <a href="{$motothre_url}">{$moto_thre_st}</a>
EOTOOLBAR;

// }}}

$body_at = '';
if (!empty($STYLE['read_k_bgcolor'])) {
    $body_at .= " bgcolor=\"{$STYLE['read_k_bgcolor']}\"";
}
if (!empty($STYLE['read_k_color'])) {
    $body_at .= " text=\"{$STYLE['read_k_color']}\"";
}

//=====================================
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOHEADER
<html>
<head>
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <title>{$ptitle_ht}</title>\n
EOHEADER;

echo <<<EOP
</head>
<body{$body_at}>\n
EOP;

echo $_info_msg_ht;
$_info_msg_ht = "";

// �X�����T�[�o�ɂȂ����============================
if ($aThread->diedat) { 

    if ($aThread->getdat_error_msg_ht) {
        $diedat_msg = $aThread->getdat_error_msg_ht;
    } else {
        $diedat_msg = "<p><b>p2 info - �T�[�o����ŐV�̃X���b�h�����擾�ł��܂���ł����B</b></p>";
    }

    $motothre_ht = "<a href=\"{$motothre_url}\">{$motothre_url}</a>";

    echo $diedat_msg;
    echo "<p>";
    echo  $motothre_ht;
    echo "</p>";
    echo "<hr>";
    
    // �������X���Ȃ���΃c�[���o�[�\��
    if (!$aThread->rescount) {
        echo <<<EOP
<p>
    {$toolbar_right_ht}
</p>
EOP;
    }
}


if (($aThread->rescount or $_GET['one'] && !$aThread->diedat) and (!$_GET['renzokupop'])) {

    echo <<<EOP
<p>
{$htm['read_navi_range']}
{$read_navi_previous}
{$read_navi_next}
{$read_navi_latest}
<a {$_conf['accesskey']}="{$_conf['k_accesskey']['bottom']}" href="#footer">{$_conf['k_accesskey']['bottom']}.��</a>
</p>\n
EOP;

}

echo "<hr>";
echo "<h3><font color=\"{$STYLE['read_k_thread_title_color']}\">{$aThread->ttitle_hd}</font></h3>\n";

$filter_fields = array('hole' => '', 'msg' => 'ү���ނ�', 'name' => '���O��', 'mail' => 'Ұق�', 'date' => '���t��', 'id' => 'ID��', 'belv' => '�߲�Ă�');

if ($word) {
    echo "��������: ";
    echo "{$filter_fields[$res_filter['field']]}";
    echo "&quot;{$hd['word']}&quot;��";
    echo ($res_filter['match'] == 'on') ? '�܂�' : '�܂܂Ȃ�';
}

echo "<hr>";

?>
