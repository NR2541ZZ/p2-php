<?php
/*
    p2 -  �X���b�h�\�� -  �w�b�_���� -  for read.php
*/

// �ϐ�
$diedat_msg = "";

$info_st = "���";
$delete_st = "�폜";
$all_st = "�S��";
$prev_st = "�O";
$next_st = "��";
$shinchaku_st = "�V�����X�̕\��";
$midoku_st = "���ǃ��X�̕\��";
$tuduki_st = "������ǂ�";
$moto_thre_st = "���X��";
$latest_st = "�ŐV";
$dores_st = "���X";
$aborn_st = "���ڂ�";

$motothre_url = $aThread->getMotoThread();
$ttitle_en = base64_encode($aThread->ttitle);
$ttitle_urlen = rawurlencode($ttitle_en);
$ttitle_en_q = "&amp;ttitle_en=" . $ttitle_urlen;
$bbs_q = "&amp;bbs=".$aThread->bbs;
$key_q = "&amp;key=".$aThread->key;
$popup_q = "&amp;popup=1";
$offline_q = "&amp;offline=1";

//=================================================================
// �w�b�_
//=================================================================

// ���X�i�r�ݒ�
$rnum_range = 100;
$latest_show_res_num = 50; //�ŐVXX

$read_navi_range = "";

//----------------------------------------------
// $read_navi_range -- 1- 101- 201-
for ($i = 1; $i <= $aThread->rescount; $i = $i + $rnum_range) {
    $offline_range_q = "";
    $ito = $i + $rnum_range - 1;
    if ($ito <= $aThread->gotnum) {
        $offline_range_q = $offline_q;
    }
    $read_navi_range = $read_navi_range . "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$i}-{$ito}{$offline_range_q}\">{$i}-</a>\n";

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
    $read_navi_previous = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$before_rnum}-{$aThread->resrange['start']}{$offline_q}{$read_navi_previous_anchor}\">{$prev_st}{$rnum_range}</a>";
    $read_navi_previous_header = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$before_rnum}-{$aThread->resrange['start']}{$offline_q}#r{$aThread->resrange['start']}\">{$prev_st}{$rnum_range}</a>";
}

//----------------------------------------------
//$read_navi_next -- ��100
if ($aThread->resrange['to'] > $aThread->rescount) {
    $aThread->resrange['to'] = $aThread->rescount;
    //$read_navi_next_anchor = "#r{$aThread->rescount}";
    //$read_navi_next_isInvisible = true;
} else {
    //$read_navi_next_anchor = "#r{$aThread->resrange['to']}";
}
if ($aThread->resrange['to'] == $aThread->rescount) {
    $read_navi_next_anchor = "#r{$aThread->rescount}";
}
$after_rnum = $aThread->resrange['to'] + $rnum_range;

$offline_range_q = "";
if ($after_rnum <= $aThread->gotnum) {
    $offline_range_q = $offline_q;
}

//if (!$read_navi_next_isInvisible) {
$read_navi_next = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->resrange['to']}-{$after_rnum}{$offline_range_q}&amp;nt={$newtime}{$read_navi_next_anchor}\">{$next_st}{$rnum_range}</a>";
//}

//----------------------------------------------
// $read_footer_navi_new  ������ǂ� �V�����X�̕\��

if ($aThread->resrange['to'] == $aThread->rescount) {
    $read_footer_navi_new = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->rescount}-&amp;nt={$newtime}#r{$aThread->rescount}\" accesskey=\"r\">{$shinchaku_st}</a>";
} else {
    $read_footer_navi_new = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->resrange['to']}-{$offline_q}\" accesskey=\"r\">{$tuduki_st}</a>";
}


// ���X�Ԏw��ړ�
$htm['goto'] = <<<GOTO
<form method="get" action="{$_conf['read_php']}" class="inline-form">
    <input type="hidden" name="host" value="{$aThread->host}">
    <input type="hidden" name="bbs" value="{$aThread->bbs}">
    <input type="hidden" name="key" value="{$aThread->key}">
    <input type="text" size="7" name="ls" value="{$aThread->ls}">
    {$_conf['k_input_ht']}
    <input type="submit" value="go">
</form>
GOTO;

//====================================================================
// HTML�v�����g
//====================================================================
$sid_q = (defined('SID')) ? '&amp;'.strip_tags(SID) : '';

// �c�[���o�[����HTML =======

// ���C�Ƀ}�[�N�ݒ�
$favmark = (!empty($aThread->fav)) ? '��' : '+';
$favdo = (!empty($aThread->fav)) ? 0 : 1;
$favtitle = $favdo ? '���C�ɃX���ɒǉ�' : '���C�ɃX������O��';
$favdo_q = '&amp;setfav='.$favdo;

$toolbar_right_ht = <<<EOTOOLBAR
            <a href="{$_conf['subject_php']}?host={$aThread->host}{$bbs_q}{$key_q}" target="subject" title="���J��">{$aThread->itaj}</a>
            <a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}" target="info" onClick="return OpenSubWin('info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$popup_q}{$sid_q}',{$STYLE['info_pop_size']},0,0)" title="�X���b�h����\��">{$info_st}</a> 
            <span class="favdo"><a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$favdo_q}{$sid_q}" target="info" onClick="return setFavJs('host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$sid_q}', '{$favdo}', {$STYLE['info_pop_size']}, 'read', this);" title="{$favtitle}">���C��{$favmark}</a></span> 
            <span><a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}&amp;dele=true" target="info" onClick="return deleLog('host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$sid_q}', {$STYLE['info_pop_size']}, 'read', this);" title="���O���폜����">{$delete_st}</a></span> 
<!--            <a href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}&amp;taborn=2" target="info" onClick="return OpenSubWin('info.php?host={$aThread->host}{$bbs_q}&amp;key={$aThread->key}{$ttitle_en_q}&amp;popup=2&amp;taborn=2{$sid_q}',{$STYLE['info_pop_size']},0,0)" title="�X���b�h�̂��ځ[���Ԃ��g�O������">{$aborn_st}</a> -->
            <a href="{$motothre_url}" title="�T�[�o��̃I���W�i���X����\��">{$moto_thre_st}</a>
EOTOOLBAR;

//=====================================
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOHEADER
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>{$ptitle_ht}</title>\n
EOHEADER;

@include("style/style_css.inc"); // �X�^�C���V�[�g
@include("style/read_css.inc"); // �X�^�C���V�[�g

echo <<<EOP
    <script type="text/javascript" src="js/basic.js"></script>
    <script type="text/javascript" src="js/respopup.js"></script>
    <script type="text/javascript" src="js/htmlpopup.js"></script>
    <script type="text/javascript" src="js/setfavjs.js"></script>
    <script type="text/javascript" src="js/delelog.js"></script>\n
EOP;

$onLoad_script = "";

if ($_conf['bottom_res_form']) {
    echo '<script type="text/javascript" src="js/post_form.js"></script>'."\n";
    $onLoad_script .= "checkSage();";
}

if (empty($_GET['one'])) {
    $onLoad_script .= "setWinTitle();";
}

echo <<<EOHEADER
    <script type="text/javascript">
    <!--
    gIsPageLoaded = false;
    
    function pageLoaded()
    {
        gIsPageLoaded = true;
        {$onLoad_script}
    }
    //-->
    </script>\n
EOHEADER;

echo <<<EOP
</head>
<body onLoad="pageLoaded();">
<div id="popUpContainer"></div>\n
EOP;

echo $_info_msg_ht;
$_info_msg_ht = "";

// �X�����T�[�o�ɂȂ���� ============================
if ($aThread->diedat) { 

    if ($aThread->getdat_error_msg_ht) {
        $diedat_msg = $aThread->getdat_error_msg_ht;
    } else {
        $diedat_msg = "<p><b>p2 info - �T�[�o����ŐV�̃X���b�h�����擾�ł��܂���ł����B</b></p>";
    }

    $motothre_popup = " onMouseover=\"showHtmlPopUp('{$motothre_url}',event,{$_conf['iframe_popup_delay']})\" onMouseout=\"offHtmlPopUp()\"";
    if ($_conf['iframe_popup'] == 1) {
        $motothre_ht = "<a href=\"{$motothre_url}\"{$_conf['bbs_win_target_at']}{$motothre_popup}>{$motothre_url}</a>";
    } elseif ($_conf['iframe_popup'] == 2) {
        $motothre_ht = "(<a href=\"{$motothre_url}\"{$_conf['bbs_win_target_at']}{$motothre_popup}>p</a>)<a href=\"{$motothre_url}\"{$_conf['bbs_win_target_at']}>{$motothre_url}</a>";
    } else {
        $motothre_ht = "<a href=\"{$motothre_url}\"{$_conf['bbs_win_target_at']}>{$motothre_url}</a>";
    }
    
    echo $diedat_msg;
    echo "<p>";
    echo  $motothre_ht;
    echo "</p>";
    echo "<hr>";
    
    // �������X���Ȃ���΃c�[���o�[�\��
    if (!$aThread->rescount) {
        echo <<<EOP
<table width="100%" style="padding:0px 0px 10px 0px;">
    <tr>
        <td align="left">
            &nbsp;
        </td>
        <td align="right">
            {$toolbar_right_ht}
        </td>
    </tr>
</table>
EOP;
    }
}


if ($aThread->rescount and (!$_GET['renzokupop'])) {
// ���X�t�B���^ ===============================
    $selected_field = array('hole' => '', 'name' => '', 'mail' => '', 'date' => '', 'id' => '', 'msg' => '');
    $selected_field[($res_filter['field'])] = ' selected';

    $selected_match = array('on' => '', 'off' => '');
    $selected_match[($res_filter['match'])] = ' selected';
    
    // �g������
    if ($_conf['enable_exfilter']) {
        $selected_method = array('and' => '', 'or' => '', 'just' => '', 'regex' => '');
        $selected_method[($res_filter['method'])] = ' selected';
        $select_method_ht = <<<EOP
    ��
    <select id="method" name="method">
        <option value="or"{$selected_method['or']}>�����ꂩ</option>
        <option value="and"{$selected_method['and']}>���ׂ�</option>
        <option value="just"{$selected_method['just']}>���̂܂�</option>
        <option value="regex"{$selected_method['regex']}>���K�\��</option>
    </select>
EOP;
    }
    
    $hd['word'] = htmlspecialchars($GLOBALS['word'], ENT_QUOTES);
    
    echo <<<EOP
<form id="header" method="GET" action="{$_conf['read_php']}" accept-charset="{$_conf['accept_charset']}" style="white-space:nowrap">
    <input type="hidden" name="detect_hint" value="����">
    <input type="hidden" name="bbs" value="{$aThread->bbs}">
    <input type="hidden" name="key" value="{$aThread->key}">
    <input type="hidden" name="host" value="{$aThread->host}">
    <input type="hidden" name="ls" value="all">
    <input type="hidden" name="offline" value="1">
    <select id="field" name="field">
        <option value="hole"{$selected_field['hole']}>�S�̂�</option>
        <option value="name"{$selected_field['name']}>���O��</option>
        <option value="mail"{$selected_field['mail']}>���[����</option>
        <option value="date"{$selected_field['date']}>���t��</option>
        <option value="id"{$selected_field['id']}>ID��</option>
        <option value="msg"{$selected_field['msg']}>���b�Z�[�W��</option>
    </select>
    <input id="word" name="word" value="{$hd['word']}" size="24">{$select_method_ht}
    ��
    <select id="match" name="match">
        <option value="on"{$selected_match['on']}>�܂�</option>
        <option value="off"{$selected_match['off']}>�܂܂Ȃ�</option>
    </select>
    ���X��
    <input type="submit" name="submit_filter" value="�t�B���^�\��">
</form>\n
EOP;
}

// {{{ p2�t���[�� 3�y�C���ŊJ��
$htm['p2frame'] = <<<EOP
<a href="index.php?url={$motothre_url}&amp;offline=1">p2�t���[�� 3�y�C���ŊJ��</a> | 
EOP;
$htm['p2frame'] = <<<EOP
<script type="text/javascript">
<!--
if (top == self) {
    document.writeln('{$htm['p2frame']}');
}
//-->
</script>\n
EOP;
// }}}

if (($aThread->rescount or $_GET['one'] && !$aThread->diedat) and !$_GET['renzokupop']) {

    if ($_GET['one']) {
        $id_header = ' id="header"';
    }
    echo <<<EOP
<table{$id_header} width="100%" style="padding:0px 0px 10px 0px;">
    <tr>
        <td align="left">
            <a href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls=all">{$all_st}</a>
            {$read_navi_range}
            {$read_navi_previous_header}
            <a href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls=l{$latest_show_res_num}">{$latest_st}{$latest_show_res_num}</a> {$htm['goto']}
        </td>
        <td align="right">
            {$htm['p2frame']}
            {$toolbar_right_ht}
        </td>
        <td align="right">
            <a href="#footer">��</a>
        </td>
    </tr>
</table>\n
EOP;

}


//if(!$_GET['renzokupop'] ){
    echo "<h3 class=\"thread_title\">{$aThread->ttitle_hd}</h3>\n";
//}


?>
