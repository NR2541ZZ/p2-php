<?php
/*
    p2 -  �X���b�h�\�� -  �t�b�^���� -  for read.php
*/

require_once (P2_LIBRARY_DIR . '/dataphp.class.php');

//=====================================================================
// ���t�b�^
//=====================================================================

if ($_conf['bottom_res_form']) {

    $bbs = $aThread->bbs;
    $key = $aThread->key;
    $host = $aThread->host;
    $rescount = $aThread->rescount;
    $ttitle_en = base64_encode($aThread->ttitle);
    
    $submit_value = '��������';

    $key_idx = $aThread->keyidx;

    // �t�H�[���̃I�v�V�����ǂݍ���
    include_once (P2_LIBRARY_DIR . '/post_options_loader.inc.php');

    $htm['resform_ttitle'] = <<<EOP
<p><b class="thre_title">{$aThread->ttitle_hd}</b></p>
EOP;
    
    include_once (P2_LIBRARY_DIR . '/post_form.inc.php');

    // �t�H�[��
    $res_form_ht = <<<EOP
<div id="kakiko">
{$htm['post_form']}
</div>\n
EOP;

    $onmouse_showform_ht = <<<EOP
 onMouseover="document.getElementById('kakiko').style.display = 'block';"
EOP;

}

// ============================================================
$sid_q = (defined('SID')) ? '&amp;'.strip_tags(SID) : '';

if ($aThread->rescount or ($_GET['one'] && !$aThread->diedat)) { // and (!$_GET['renzokupop'])

    if (!$aThread->diedat) {
        if (!empty($_conf['disable_res'])) {
            $htm['dores'] = <<<EOP
<a href="{$motothre_url}" target="_blank">{$dores_st}</a>
EOP;
        } else {
            $htm['dores'] = <<<EOP
<a href="post_form.php?host={$aThread->host}{$bbs_q}{$key_q}&amp;rc={$aThread->rescount}{$ttitle_en_q}" target='_self' onClick="return OpenSubWin('post_form.php?host={$aThread->host}{$bbs_q}{$key_q}&amp;rc={$aThread->rescount}{$ttitle_en_q}&amp;popup=1{$sid_q}',{$STYLE['post_pop_size']},0,0)"{$onmouse_showform_ht}>{$dores_st}</a>
EOP;
        }
        
        $res_form_ht_pb = $res_form_ht;
    }
    
    if ($res1['body']) {
        $q_ichi = $res1['body']." | ";
    }
    
    // ���X�̂��΂₳
    $htm['spd'] = '';
    if ($spd_st = $aThread->getTimePerRes() and $spd_st != '-') {
        $htm['spd'] = '<span class="spd" title="���΂₳������/���X">'."" . $spd_st."".'</span>';
    }

    // ���X�Ԏw��ړ�
    $htm['goto'] = <<<GOTO
            <form method="get" action="{$_conf['read_php']}" class="inline-form">
                <input type="hidden" name="host" value="{$aThread->host}">
                <input type="hidden" name="bbs" value="{$aThread->bbs}">
                <input type="hidden" name="key" value="{$aThread->key}">
                <input type="text" size="5" name="ls" value="{$aThread->ls}">
                <input type="submit" value="go">
            </form>
GOTO;

    // {{{ �t�B���^�q�b�g���������ꍇ�A��X�Ƒ�����ǂނ��X�V
    /*
    //if (!$read_navi_next_isInvisible) {
    $read_navi_next = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->resrange['to']}-{$after_rnum}{$offline_range_q}&amp;nt={$newtime}{$read_navi_next_anchor}\">{$next_st}{$rnum_range}</a>";
    //}
    
    $read_footer_navi_new = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$aThread->resrange['to']}-{$offline_q}\" accesskey=\"r\">{$tuduki_st}</a>";
    */
    
    if (!empty($GLOBALS['last_hit_resnum'])) {
        $read_navi_next_anchor = "";
        if ($GLOBALS['last_hit_resnum'] == $aThread->rescount) {
            $read_navi_next_anchor = "#r{$aThread->rescount}";
        }
        $after_rnum = $GLOBALS['last_hit_resnum'] + $rnum_range;
        $read_navi_next = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$GLOBALS['last_hit_resnum']}-{$after_rnum}{$offline_range_q}&amp;nt={$newtime}{$read_navi_next_anchor}\">{$next_st}{$rnum_range}</a>";

        // �u������ǂށv
        $read_footer_navi_new = "<a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$GLOBALS['last_hit_resnum']}-{$offline_q}\" accesskey=\"r\">{$tuduki_st}</a>";
    }
    // }}}
    
    // ���v�����g
    echo <<<EOP
<hr>
<table id="footer" width="100%" style="padding:0px 10px 0px 0px;">
    <tr>
        <td align="left">
            {$q_ichi}
            <a href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls=all">{$all_st}</a> 
            {$read_navi_previous} 
            {$read_navi_next} 
            <a href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls=l{$latest_show_res_num}">{$latest_st}{$latest_show_res_num}</a>
            {$htm['goto']}
            | {$read_footer_navi_new}
            | {$htm['dores']}
            {$htm['spd']}
        </td>
        <td align="right">
            {$htm['p2frame']}
            {$toolbar_right_ht}
        </td>
        <td align="right">
            <a href="#header">��</a>
        </td>
    </tr>
</table>
{$res_form_ht_pb}
EOP;

    if ($diedat_msg) {
        echo "<hr>";
        echo $diedat_msg;
        echo "<p>";
        echo  $motothre_ht;
        echo "</p>";
    }
}

if (!empty($_GET['showres'])) {
    echo <<<EOP
    <script type="text/javascript">
    <!--
    document.getElementById('kakiko').style.display = 'block';
    //-->
    </script>\n
EOP;
}

// ====
echo '</body>
</html>
';

?>
