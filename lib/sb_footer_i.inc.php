<?php
/**
 * rep2 - �T�u�W�F�N�g - iPhone�t�b�^�\��
 * for subject.php
 */

//=================================================
//�t�b�^�v�����g
//=================================================
$bbs_q = '&amp;bbs=' . $aThreadList->bbs;
$host_bbs_q = 'host=' . $aThreadList->host . $bbs_q;
$paging_q = $host_bbs_q . '&amp;spmode=' . $aThreadList->spmode . $norefresh_q;

if (!empty($GLOBALS['wakati_words'])) {
    $paging_q .= '&amp;method=similar&amp;word=' . rawurlencode($GLOBALS['wakati_word']);
    $word_input_ht = '<input type="hidden" name="method" value="similar">';
    $word_input_ht .= '<input type="hidden" name="word" value="' . htmlspecialchars($GLOBALS['wakati_word'], ENT_QUOTES, 'Shift_JIS') . '">';
} elseif ($word) {
    $paging_q .= '&amp;word=' . rawurlencode($word);
    $word_input_ht = '<input type="hidden" name="word" value="' . htmlspecialchars($word, ENT_QUOTES, 'Shift_JIS') . '">';
    if (isset($sb_filter['method']) && $sb_filter['method'] == 'or') {
        $paging_q .= '&amp;method=or';
        $word_input_ht = '<input type="hidden" name="method" value="or">';
    }
} else {
    $word_input_ht = '';
}

if ($aThreadList->spmode == 'fav' && $sb_view == 'shinchaku') {
    $allfav_ht = <<<EOP
<div class=\"pager\"><a href="{$_conf['subject_php']}?spmode=fav{$norefresh_q}{$_conf['k_at_a']}">�S�Ă̂��C�ɽڂ�\��</a></div>
EOP;
} else {
    $allfav_ht = '';
}

// �y�[�W�^�C�g������HTML�ݒ� ====================================
if ($aThreadList->spmode == 'taborn') {
    $ptitle_ht = <<<EOP
<a href="{$ptitle_url}"{$_conf['k_accesskey_at']['up']}">{$_conf['k_accesskey_st']['up']}<b>{$aThreadList->itaj}</b></a> (���ݒ�)
EOP;
} elseif ($aThreadList->spmode == 'soko') {
    $ptitle_ht = <<<EOP
<a href="{$ptitle_url}"{$_conf['k_accesskey_at']['up']}">{$_conf['k_accesskey_st']['up']}<b>{$aThreadList->itaj}</b></a> (dat�q��)
EOP;
} elseif (!empty($ptitle_url)) {
    $ptitle_ht = <<<EOP
<a href="{$ptitle_url}" class="nobutton"><b>{$ptitle_hd}</b></a>
EOP;
} else {
    $ptitle_ht = <<<EOP
<b>{$ptitle_hd}</b>
EOP;
}

// {{{ �i�r

if (!empty($_REQUEST['sb_view'])) {
    $paging_q = '&amp;sb_view=' . rawurlencode($_REQUEST['sb_view']);
    $sb_view_input_ht = '<input type="hidden" name="sb_view" value="' . htmlspecialchars($_REQUEST['sb_view'], ENT_QUOTES) . '">';
} else {
    $sb_view_input_ht = '';
}

if (!empty($_REQUEST['rsort'])) {
    $paging_q .= '&amp;rsort=1';
    $sb_view_input_ht .= '<input type="hidden" name="rsort" value="1">';
}

if ($aThreadList->spmode == 'merge_favita' && $_conf['expack.misc.multi_favs']) {
    $paging_q .= $_conf['m_favita_set_at_a'];
    $sb_view_input_ht .= $_conf['m_favita_set_input_ht'];
}

if ($disp_navi['from'] == $disp_navi['end']) {
    $sb_range_on = $disp_navi['from'];
} else {
    $sb_range_on = "{$disp_navi['from']}-{$disp_navi['end']}";
}

$sb_all_pages = 1;

if (!$disp_navi['all_once']) {
    if ($_conf['mobile.sb_disp_range'] < 1) {
        $k_sb_navi_select_from_ht = '<option value="1">$_conf[&#39;mobile.sb_disp_range&#39;] �̒l���s���ł�</option>';
    } else {
        if ($disp_navi['offset'] % $_conf['mobile.sb_disp_range']) {
            $k_sb_navi_select_from_ht = "<option value=\"{$disp_navi['from']}\" selected>{$sb_range_on}</option>";
        } else {
            $k_sb_navi_select_from_ht = '';
        }

        /*$k_sb_navi_select_optgroup = $_conf['mobile.sb_disp_range'] * 5;
        if ($k_sb_navi_select_optgroup >= $sb_disp_all_num) {
            $k_sb_navi_select_optgroup = 0;
        }*/

        for ($i = 0; $i < $sb_disp_all_num; $i += $_conf['mobile.sb_disp_range']) {
            $j = $i + 1;
            $k = $i + $_conf['mobile.sb_disp_range'];
            if ($k > $sb_disp_all_num) {
                $k = $sb_disp_all_num;
            }

            /*if ($k_sb_navi_select_optgroup && $i % $k_sb_navi_select_optgroup == 0) {
                if ($i) {
                    $k_sb_navi_select_from_ht .= '</optgroup>';
                }
                $k_sb_navi_select_from_ht .= "<optgroup label=\"{$j}-\">";
            }*/

            $l = ceil($j / $_conf['mobile.sb_disp_range']);

            if ($j == $disp_navi['from']) {
                $k_sb_navi_select_from_ht .= "<option value=\"{$j}\" selected>{$l}</option>";
            } else {
                $k_sb_navi_select_from_ht .= "<option value=\"{$j}\">{$l}</option>";
            }

            $sb_all_pages = $l;
        }

        /*if ($k_sb_navi_select_optgroup) {
            $k_sb_navi_select_from_ht .= '</optgroup>';
        }*/
    }

    $k_sb_navi_ht = "<select onchange=\"location.href = '{$_conf['subject_php']}?{$paging_q}&amp;from=' + this.options[this.selectedIndex].value + '{$_conf['k_at_a']}';\">{$k_sb_navi_select_from_ht}</select>";
}

if ($sb_all_pages < 2) {
    $sb_all_pages = 1;
    $k_sb_navi_ht = '<select><option>1</option></select>';
}

// }}}
// {{{ ���C�ɃX���Z�b�g�ؑ�

if ($aThreadList->spmode == 'fav' && $_conf['expack.misc.multi_favs']) {
    $switchfavlist_ht = '<div>' . FavSetManager::makeFavSetSwitchForm('m_favlist_set', '���C�ɃX��', NULL, NULL, FALSE, array('spmode' => 'fav')) . '</div>';
} else {
    $switchfavlist_ht = '';
}

// }}}
// {{{ �\�[�g�ύX �i�V�� ���X No. �^�C�g�� �� ���΂₳ ���� Birthday ���j

$sorts = array('midoku' => '�V��', 'res' => '���X', 'no' => 'No.', 'title' => '�^�C�g��');

if ($aThreadList->spmode and $aThreadList->spmode != 'taborn' and $aThreadList->spmode != 'soko') {
    $sorts['ita'] = '��';
}
if ($_conf['sb_show_spd']) {
    $sorts['spd'] = '���΂₳';
}
if ($_conf['sb_show_ikioi']) {
    $sorts['ikioi'] = '����';
}
$sorts['bd'] = 'Birthday';
if ($_conf['sb_show_fav'] and $aThreadList->spmode != 'taborn') {
    $sorts['fav'] = '��';
}

$htm['change_sort'] = "<form method=\"get\" action=\"{$_conf['subject_php']}\">";
$htm['change_sort'] .= $_conf['k_input_ht'];
$htm['change_sort'] .= '<input type="hidden" name="norefresh" value="1">';
// spmode��
if ($aThreadList->spmode) {
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"spmode\" value=\"{$aThreadList->spmode}\">";
}
// spmode�łȂ��A�܂��́Aspmode�����ځ[�� or dat�q�ɂȂ�
if (!$aThreadList->spmode || $aThreadList->spmode == "taborn" || $aThreadList->spmode == "soko") {
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"host\" value=\"{$aThreadList->host}\">";
    $htm['change_sort'] .= "<input type=\"hidden\" name=\"bbs\" value=\"{$aThreadList->bbs}\">";
}

$htm['change_sort'] .= '<select name="sort">';
foreach ($sorts as $k => $v) {
    if ($GLOBALS['now_sort'] == $k) {
        $sb_sort_selected_at = ' selected';
    } else {
        $sb_sort_selected_at = '';
    }
    $htm['change_sort'] .= "<option value=\"{$k}\"{$sb_sort_selected_at}>{$v}</option>";
}
$htm['change_sort'] .= '</select>';

if (!empty($_REQUEST['sb_view'])) {
    $htm['change_sort'] .= '<input type="hidden" name="sb_view" value="'
                        . htmlspecialchars($_REQUEST['sb_view']) . '">';
}

if (!empty($_REQUEST['rsort'])) {
    $sb_rsort_checked_at = ' checked';
} else {
    $sb_rsort_checked_at = '';
}
$htm['change_sort'] .= ' <input type="checkbox" id="sb_rsort" name="rsort" value="1"'
                    . $sb_rsort_checked_at . '><label for="sb_rsort">�t��</label>';
$htm['change_sort'] .= ' <input type="submit" value="���ёւ�"></form>';

// }}}
// {{{ �c�[���o�[��\��

$touch_handlers = ' ontouchstart="this.className=\'hover\'" ontouchend="this.className=\'\'"';

echo '<div class="ntoolbar" id="footer">';
echo '<table><tbody><tr>';

// {{{ �y�[�W��

// �O�̃y�[�W
echo '<td>';
if ($disp_navi['from'] > 1) {
    $escaped_url = "{$_conf['subject_php']}?{$paging_q}&amp;from={$disp_navi['mae_from']}{$_conf['k_at_a']}";
    echo toolbar_i_standard_button('img/gp3-prev.png', '�O', $escaped_url);
} else {
    echo toolbar_i_disabled_button('img/gp3-prev.png', '�O');
}
echo '</td>';

// ���̃y�[�W
echo '<td>';
if ($disp_navi['tugi_from'] <= $sb_disp_all_num) {
    $escaped_url = "{$_conf['subject_php']}?{$paging_q}&amp;from={$disp_navi['tugi_from']}{$_conf['k_at_a']}";
    echo toolbar_i_standard_button('img/gp4-next.png', '��', $escaped_url);
} else {
    echo toolbar_i_disabled_button('img/gp4-next.png', '��');
}
echo '</td>';

// �y�[�W�ԍ��𒼐ڎw��
echo "<td colspan=\"2\">{$k_sb_navi_ht}<span class=\"large\">/{$sb_all_pages}</span><br>�y�[�W</td>";

// ���
echo '<td>', toolbar_i_standard_button('img/gp1-up.png', '��', '#header'), '</td>';

// }}}

echo '</tr><tr>';

// {{{ ���̑��{�^����

// ���ځ[�񒆂̃X���b�h�ꗗ���J��
echo '<td>';
if ($ta_num) {
    $escaped_url = "{$_conf['subject_php']}?{$host_bbs_q}{$norefresh_q}&amp;spmode=taborn{$_conf['k_at_a']}";
    echo toolbar_i_badged_button('img/glyphish/icons2/21-skull.png', '���ځ[��', $escaped_url, $ta_num);
} elseif ($aThreadList->spmode == 'taborn') {
    $escaped_url = "{$_conf['subject_php']}?{$host_bbs_q}{$_conf['k_at_a']}";
    echo toolbar_i_standard_button('img/glyphish/icons2/63-runner.png', '�ɖ߂�', $escaped_url);
} else {
    echo toolbar_i_disabled_button('img/glyphish/icons2/21-skull.png', '���ځ[��');
}
echo '</td>';

// dat�q�ɂ��J��
echo '<td>';
if (!$aThreadList->spmode || $aThreadList->spmode == 'taborn') {
    $escaped_url = "{$_conf['subject_php']}?{$host_bbs_q}{$norefresh_q}&amp;spmode=soko{$_conf['k_at_a']}";
    echo toolbar_i_standard_button('img/glyphish/icons2/33-cabinet.png', 'dat�q��', $escaped_url);
} elseif ($aThreadList->spmode == 'soko') {
    $escaped_url = "{$_conf['subject_php']}?{$host_bbs_q}{$_conf['k_at_a']}";
    echo toolbar_i_standard_button('img/glyphish/icons2/63-runner.png', '�ɖ߂�', $escaped_url);
} else {
    echo toolbar_i_disabled_button('img/glyphish/icons2/33-cabinet.png', 'dat�q��');
}
echo '</td>';

// �g�b�v�ɖ߂�
echo '<td>', toolbar_i_standard_button('img/glyphish/icons2/53-house.png', 'TOP', "index.php{$_conf['k_at_q']}"), '</td>';

// BB2C�ŃX�����J��
echo '<td>';
if (!$aThreadList->spmode && $_conf['expack.misc.use_bb2c']) {
    $bb2c_open_uri = 'beebee2seeopen://' . str_replace('%2F', '/', rawurldecode("{$aThreadList->host}/{$aThreadList->bbs}/"));
    $escaped_url = "javascript:location.replace('{$bb2c_open_uri}')";
    echo toolbar_i_standard_button('img/glyphish/icons2/12-eye.png', 'BB2C', $escaped_url);
} else {
    echo toolbar_i_disabled_button('img/glyphish/icons2/12-eye.png', 'BB2C');
}
echo '</td>';

// �V�����X���b�h�𗧂Ă�
echo '<td>';
if (!$aThreadList->spmode) {
    $escaped_url = "post_form.php?{$host_bbs_q}&amp;newthread=1{$_conf['k_at_a']}";
    echo toolbar_i_standard_button('img/glyphish/icons2/23-bird.png', '�X������', $escaped_url);
} else {
    echo toolbar_i_disabled_button('img/glyphish/icons2/23-bird.png', '�X������');
}
echo '</td>';

// }}}

echo '</tr></tbody></table>';
echo '</div>';

// }}}

echo '</body></html>';

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