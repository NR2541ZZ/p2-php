<?php
// p2 - �X���b�h�T�u�W�F�N�gHTML�\���֐� (PC�p)
// for subject.php

/**
 * �X���b�h�ꗗ��HTML�\������ (PC�p <tr>�`</tr>)
 *
 * @access  public
 * @return  void
 */
function sb_print($aThreadList)
{
    global $_conf, $sb_view, $p2_setting, $STYLE;
    
    $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('sb_print()');
    
    if (!$aThreadList->threads) {
        echo "<tr><td>�@�Y���T�u�W�F�N�g�͂Ȃ�������</td></tr>";
        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('sb_print()');
        return;
    }
    
    // �ϐ� ================================================
    
    // >>1 �\��
    $onlyone_bool = false;
    if (($_conf['sb_show_one'] == 1) or ($_conf['sb_show_one'] == 2 and ereg("news", $aThreadList->bbs) || $aThreadList->bbs == "bizplus")) {
        // spmode�͏���
        if (empty($aThreadList->spmode)) {
            $onlyone_bool = true;
        }
    }
    
    // �`�F�b�N�{�b�N�X
    if ($aThreadList->spmode == "taborn" or $aThreadList->spmode == "soko") {
        $checkbox_bool = true;
    } else {
        $checkbox_bool = false;
    }
    
    // ��
    if ($aThreadList->spmode and $aThreadList->spmode != "taborn" and $aThreadList->spmode != "soko") {
        $ita_name_bool = true;
    } else {
        $ita_name_bool = false;
    }

    $norefresh_q = "&amp;norefresh=1";

    // �\�[�g ==================================================
    
    // ���݂̃\�[�g�`����class�w���CSS�J���[�����O
    $class_sort_midoku  = '';   // �V��
    $class_sort_res     = '';   // ���X
    $class_sort_no      = '';   // No.
    $class_sort_title   = '';   // �^�C�g��
    $class_sort_ita     = '';   // ��
    $class_sort_spd     = '';   // ���΂₳
    $class_sort_ikioi   = '';   // ����
    $class_sort_bd      = '';   // Birthday
    $class_sort_fav     = '';   // ���C�ɓ���
    if ($GLOBALS['now_sort']) {
        $nowsort_code = <<<EOP
\$class_sort_{$GLOBALS['now_sort']}=' class="now_sort"';
EOP;
        eval($nowsort_code);
    }

    $sortq_spmode = '';
    $sortq_host   = '';
    $sortq_ita    = '';
    // spmode��
    if ($aThreadList->spmode) { 
        $sortq_spmode = "&amp;spmode={$aThreadList->spmode}";
    }
    // spmode�łȂ��A�܂��́Aspmode�����ځ[�� or dat�q�ɂȂ�
    if (!$aThreadList->spmode || $aThreadList->spmode == "taborn" || $aThreadList->spmode == "soko") { 
        $sortq_host = "&amp;host={$aThreadList->host}";
        $sortq_ita = "&amp;bbs={$aThreadList->bbs}";
    }
    
    $midoku_sort_ht = "<td class=\"tu\" nowrap><a{$class_sort_midoku} href=\"{$_conf['subject_php']}?sort=midoku{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}\" target=\"_self\" style=\"white-space:nowrap;\"><nobr>�V��</nobr></a></td>";

    //=====================================================
    // �e�[�u���w�b�_HTML�\��
    //=====================================================
    echo '<tr class="tableheader">' . "\n";
    
    // ����
    if ($sb_view == "edit") { echo '<td class="te">&nbsp;</td>'; }
    
    // �V��
    if ($sb_view != "edit") { echo $midoku_sort_ht; }
    
    // ���X��
    if ($sb_view != "edit") {
        echo "<td class=\"tn\" nowrap><a{$class_sort_res} href=\"{$_conf['subject_php']}?sort=res{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}\" target=\"_self\">���X</a></td>";
    }
    
    // >>1
    if ($onlyone_bool) {
        echo '<td class="t">&nbsp;</td>';
    }
    
    // �`�F�b�N�{�b�N�X
    if ($checkbox_bool) {
        echo '<td class="tc"><input id="allbox" name="allbox" type="checkbox" onClick="checkAll();" title="���ׂĂ̍��ڂ�I���A�܂��͑I������"></td>';
    }
    
    // No.
    $title = empty($aThreadList->spmode) ? " title=\"2ch�W���̕��я��ԍ�\"" : '';
    echo "<td class=\"to\"><a{$class_sort_no} href=\"{$_conf['subject_php']}?sort=no{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}\" target=\"_self\"{$title}>No.</a></td>";
    
    // �^�C�g��
    echo "<td class=\"tl\"><a{$class_sort_title} href=\"{$_conf['subject_php']}?sort=title{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}\" target=\"_self\">�^�C�g��</a></td>";
    
    // ��
    if ($ita_name_bool) {
        echo "<td class=\"t\"><a{$class_sort_ita} href=\"{$_conf['subject_php']}?sort=ita{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}\" target=\"_self\">��</a></td>";
    }
    
    // ���΂₳
    if ($_conf['sb_show_spd']) {
        echo "<td class=\"ts\"><a{$class_sort_spd} href=\"{$_conf['subject_php']}?sort=spd{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}\" target=\"_self\">���΂₳</a></td>";
    }
    
    // ����
    if ($_conf['sb_show_ikioi']) {
        echo "<td class=\"ti\"><a{$class_sort_ikioi} href=\"{$_conf['subject_php']}?sort=ikioi{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}\" target=\"_self\" title=\"���������̃��X��\">����</a></td>";
    }
    
    // Birthday
    echo "<td class=\"t\"><a{$class_sort_bd} href=\"{$_conf['subject_php']}?sort=bd{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}\" target=\"_self\">Birthday</a></td>";
    
    // ���C�ɓ���
    if ($_conf['sb_show_fav'] and $aThreadList->spmode != "taborn") {
        echo "<td class=\"t\"><a{$class_sort_fav} href=\"{$_conf['subject_php']}?sort=fav{$sortq_spmode}{$sortq_host}{$sortq_ita}{$norefresh_q}\" target=\"_self\" title=\"���C�ɃX��\">��</a></td>";
    }
    
    echo "\n</tr>\n";

    //=====================================================
    //�e�[�u���{�f�B
    //=====================================================

    //spmode������΃N�G���[�ǉ�
    if ($aThreadList->spmode) {
        $spmode_q = "&amp;spmode={$aThreadList->spmode}";
    } else {
        $spmode_q = '';
    }
    
    $sid_q = (defined('SID') && strlen(SID)) ? '&amp;' . hs(SID) : '';
    
    $i = 0;
    foreach ($aThreadList->threads as $aThread) {
        $i++;
        $midoku_ari = '';
        $anum_ht = ''; // #r1
        
        $offline_q = '';
        
        $bbs_q = "&amp;bbs=" . $aThread->bbs;
        $key_q = "&amp;key=" . $aThread->key;

        if ($aThreadList->spmode != 'taborn') {
            if (!$aThread->torder) { $aThread->torder = $i; }
        }

        // td�� css�N���X
        if (($i % 2) == 0) {
            $class_t  = ' class="t"';     // ��{
            $class_te = ' class="te"';    // ���ёւ�
            $class_tu = ' class="tu"';    // �V�����X��
            $class_tn = ' class="tn"';    // ���X��
            $class_tc = ' class="tc"';    // �`�F�b�N�{�b�N�X
            $class_to = ' class="to"';    // �I�[�_�[�ԍ�
            $class_tl = ' class="tl"';    // �^�C�g��
            $class_ts = ' class="ts"';    // ���΂₳
            $class_ti = ' class="ti"';    // ����
        } else {
            $class_t  = ' class="t2"';
            $class_te = ' class="te2"';
            $class_tu = ' class="tu2"';
            $class_tn = ' class="tn2"';
            $class_tc = ' class="tc2"';
            $class_to = ' class="to2"';
            $class_tl = ' class="tl2"';
            $class_ts = ' class="ts2"';
            $class_ti = ' class="ti2"';
        }
    
        // �V�����X�� =============================================
        $unum_ht_c = "&nbsp;";
        
        // �����ς�
        if ($aThread->isKitoku()) {
            
            // $ttitle_en_q �͐ߌ��ȗ�
            $onclick_at = " onClick=\"return deleLog('host={$aThread->host}{$bbs_q}{$key_q}{$sid_q}', {$STYLE['info_pop_size']}, 'subject', this);\"";
            $title_at = ' title="�N���b�N����ƃ��O�폜"';
            
            $unum_ht_c = "<a class=\"un\" href=\"{$_conf['subject_php']}?host={$aThread->host}{$bbs_q}{$key_q}{$spmode_q}&amp;dele=1\" target=\"_self\"{$onclick_at}{$title_at}>{$aThread->unum}</a>";
        
            $anum = $aThread->rescount - $aThread->unum + 1 - $_conf['respointer'];
            if ($anum > $aThread->rescount) {
                $anum = $aThread->rescount;
            }
            $anum_ht = '#r' . $anum;
            
            // {{{ �V������
            
            if ($aThread->unum > 0) {
                $midoku_ari = true;
                
                $dele_log_qs = $thread_qs = array(
                    'host' => $aThread->host, 'bbs' => $aThread->bbs, 'key' => $aThread->key
                );
                if (defined('SID') && strlen(SID)) {
                    $dele_log_qs[session_name()] = session_id();
                }
                $dele_log_q = P2Util::buildQuery($dele_log_qs);

                $unum_ht_c = P2View::tagA(
                    P2Util::buildQueryUri($_conf['subject_php'],
                        array_merge(
                            $thread_qs,
                            array(
                                'spmode' => $aThreadList->spmode, 'dele' => '1',
                                UA::getQueryKey() => UA::getQueryValue()
                            )
                        )
                    ),
                    hs($aThread->unum),
                    array(
                        'id' => "un{$i}", 'class' => 'un_a', 'target' => '_self', 'title' => '�N���b�N����ƃ��O�폜',
                        'onClick' => sprintf(
                            "return deleLog('%s', %s, 'subject', this);",
                            str_replace("'", "\\'", $dele_log_q), $STYLE['info_pop_size']
                        )
                    )
                );
            }
            
            // }}}
            
            // subject.txt�ɂȂ���
            if (!$aThread->isonline) {
                // JavaScript�ł̊m�F�_�C�A���O����
                $unum_ht_c = "<a class=\"un_n\" href=\"{$_conf['subject_php']}?host={$aThread->host}{$bbs_q}{$key_q}{$spmode_q}&amp;dele=true\" target=\"_self\" onClick=\"if (!window.confirm('���O���폜���܂����H')) {return false;} return deleLog('host={$aThread->host}{$bbs_q}{$key_q}{$sid_q}', {$STYLE['info_pop_size']}, 'subject', this)\"{$title_at}>-</a>";
            }

        }
        
        $unum_ht = "<td{$class_tu}>" . $unum_ht_c . "</td>";
        
        // �����X��
        $rescount_ht = "<td{$class_tn}>{$aThread->rescount}</td>";

        // {{{ ��
        
        $ita_td_ht = '';
        if ($ita_name_bool) {
            $ita_name = $aThread->itaj ? $aThread->itaj : $aThread->bbs;
            $ita_atag = P2View::tagA(
                P2Util::buildQueryUri($_conf['subject_php'],
                    array(
                        'host' => $aThread->host,
                        'bbs'  => $aThread->bbs
                    )
                ),
                $ita_name,
                array('target' => '_self')
            );
            $ita_td_ht = "<td{$class_t} nowrap>{$ita_atag}</td>";
        }
        
        // }}}
        
        // ���C�ɓ���
        if ($_conf['sb_show_fav'] and $aThreadList->spmode != 'taborn') {
            
            $favmark    = !empty($aThread->fav) ? '��' : '+';
            $favvalue   = !empty($aThread->fav) ? 0 : 1;
            $favtitle   = $favvalue ? '���C�ɃX���ɒǉ�' : '���C�ɃX������O��';
            $setfav_q   = '&amp;setfav=' . $favvalue;

            // $ttitle_en_q ���t���������������A�ߖ�̂��ߏȗ�����
            $fav_ht = <<<EOP
<td{$class_t}><a class="fav" href="info.php?host={$aThread->host}{$bbs_q}{$key_q}{$setfav_q}" target="info" onClick="return setFavJs('host={$aThread->host}{$bbs_q}{$key_q}', '{$favvalue}', {$STYLE['info_pop_size']}, 'subject', this);" title="{$favtitle}">{$favmark}</a></td>
EOP;
        }
        
        // torder(info) =================================================
        // ���C�ɃX��
        if ($aThread->fav) {
            $torder_st = "<b>{$aThread->torder}</b>";
        } else {
            $torder_st = $aThread->torder;
        }
        $torder_ht = "<a id=\"to{$i}\" class=\"info\" href=\"info.php?host={$aThread->host}{$bbs_q}{$key_q}\" target=\"_self\" onClick=\"return !openSubWin('info.php?host={$aThread->host}{$bbs_q}{$key_q}&amp;popup=1{$sid_q}',{$STYLE['info_pop_size']},0,0)\">{$torder_st}</a>";
        
        // title =================================================
        $chUnColor_ht = '';
        
        $rescount_q = "&amp;rc=" . $aThread->rescount;
        
        // dat�q�� or �a���Ȃ�
        if ($aThreadList->spmode == "soko" || $aThreadList->spmode == "palace") { 
            $rescount_q = '';
            $offline_q  = "&amp;offline=1";
            $anum_ht    = '';
        }
        
        // �^�C�g�����擾or�S�p�󔒂Ȃ�iIE�őS�p�󔒂������N�N���b�N�ł��Ȃ��̂Łj
        if (!$aThread->ttitle_ht or $aThread->ttitle_ht == '�@') { 
            $aThread->ttitle_ht = "http://{$aThread->host}/test/read.cgi/{$aThread->bbs}/{$aThread->key}/";
        }
        
        if ($aThread->similarity) {
            $aThread->ttitle_ht .= sprintf(' <var>(%0.1f)</var>', $aThread->similarity * 100);
        }
        
        // ���X��
        $moto_thre_ht = '';
        if ($_conf['sb_show_motothre']) {
            if (!$aThread->isKitoku()) {
                $moto_thre_ht = '<a class="thre_title" href="' . hs($aThread->getMotoThread()) . '">�E</a> ';
            }
        }
        
        // �V�K�X��
        if ($aThread->new) { 
            $classtitle_q = ' class="thre_title_new"';
        } else {
            $classtitle_q = ' class="thre_title"';
        }
        
        // �X�������N
        if (!empty($_REQUEST['find_cont']) && strlen($GLOBALS['word_fm'])) {
            $word_q = "&amp;word=" . urlencode($GLOBALS['word']) . "&amp;method=" . urlencode($GLOBALS['sb_filter']['method']);
            $rescount_q = '';
            $offline_q  = '&amp;offline=1';
            $anum_ht    = '';
        } else {
            $word_q = '';
        }
        $thre_url = "{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}{$rescount_q}{$offline_q}{$word_q}{$anum_ht}";
        
        
        if ($midoku_ari) {
            $chUnColor_ht = "chUnColor('{$i}');";
        }
        $change_color = " onClick=\"chTtColor('{$i}');{$chUnColor_ht}\"";
        
        // �I�����[>>1
        if ($onlyone_bool) {
            $one_ht = "<td{$class_t}><a href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}{$rescount_q}&amp;onlyone=true\">&gt;&gt;1</a></td>";
        } else {
            $one_ht = '';
        }
        
        // �`�F�b�N�{�b�N�X
        $checkbox_ht = '';
        if ($checkbox_bool) {
            $checked_ht = '';
            if ($aThreadList->spmode == "taborn") {
                if (!$aThread->isonline) { $checked_ht=" checked"; } // or ($aThread->rescount >= 1000)
            }
            $checkbox_ht = "<td{$class_tc}><input name=\"checkedkeys[]\" type=\"checkbox\" value=\"{$aThread->key}\"$checked_ht></td>";
        }
        
        // ����
        $edit_ht = '';
        if ($sb_view == "edit") {
            $unum_ht = '';
            $rescount_ht = '';
            if ($aThreadList->spmode == "fav") {
                $setkey = "setfav";
            } elseif ($aThreadList->spmode == "palace") {
                $setkey = "setpal";
            }
            
            $narabikae_a = P2Util::buildQueryUri(
                $_conf['subject_php'],
                array(
                    'host'    => $aThread->host,
                    'bbs'     => $aThread->bbs,
                    'key'     => $aThread->key,
                    'spmode'  => $aThreadList->spmode,
                    'sb_view' => 'edit'
                )
            );
            
            $edit_ht = <<<EOP
        <td{$class_te}>
            <a class="te" href="{$narabikae_a}&amp;{$setkey}=top" target="_self">��</a>
            <a class="te" href="{$narabikae_a}&amp;{$setkey}=up" target="_self">��</a>
            <a class="te" href="{$narabikae_a}&amp;{$setkey}=down" target="_self">��</a>
            <a class="te" href="{$narabikae_a}&amp;{$setkey}=bottom" target="_self">��</a>
        </td>
EOP;
        }
        
        // ���΂₳�i�� ����/���X �� ���X�Ԋu�j
        $spd_ht = '';
        if ($_conf['sb_show_spd']) {
            if ($spd_st = $aThread->getTimePerRes()) {
                $spd_ht = "<td{$class_ts}>{$spd_st}</td>";
            }        
        }
        
        // ����
        $ikioi_ht = '';
        if ($_conf['sb_show_ikioi']) {
            if ($aThread->dayres > 0) {
                // 0.0 �ƂȂ�Ȃ��悤�ɏ����_��2�ʂŐ؂�グ
                $dayres = ceil($aThread->dayres * 10) / 10;
                $dayres_st = sprintf("%01.1f", $dayres);
            } else {
                $dayres_st = "-";
            }
            $ikioi_ht = "<td{$class_ti}>" . hs($dayres_st) . "</td>";
        }
        
        // Birthday
        //if (preg_match('/^\d{9,10}$/', $aThread->key) {
        if (631119600 < $aThread->key && $aThread->key < time() + 1000) { // 1990�N-
            $birthday = date("y/m/d", $aThread->key); // (y/m/d H:i)
        } else {
            $birthday = '-';
        }
        $birth_ht = "<td{$class_t}>{$birthday}</td>";


        // �X���b�h�ꗗ table �{�f�B HTML�v�����g <tr></tr> 
        echo "<tr>
$edit_ht
$unum_ht
$rescount_ht
$one_ht
$checkbox_ht
<td{$class_to}>{$torder_ht}</td>
<td{$class_tl} nowrap>$moto_thre_ht<a id=\"tt{$i}\" href=\"{$thre_url}\"{$classtitle_q}{$change_color}>{$aThread->ttitle_ht}</a></td>
$ita_td_ht
$spd_ht
$ikioi_ht
$birth_ht
$fav_ht
</tr>
";
            ob_flush(); flush();
    }

    $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('sb_print()');
}

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
