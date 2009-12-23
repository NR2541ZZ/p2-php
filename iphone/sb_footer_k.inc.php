<?php
// p2 - �T�u�W�F�N�g - �t�b�^HTML��\������ �g��
// for subject.php

$word_qs = _getWordQs();

$allfav_atag = _getAllFavATag($aThreadList, $sb_view);

// �y�[�W�^�C�g������HTML�ݒ�
$ptitle_ht = _getPtitleHtml($aThreadList, $ptitle_url);

// {{{ �i�r HTML�ݒ�

$mae_ht = _getMaeATag($aThreadList, $disp_navi, $word_qs);

$tugi_ht = '';
if ($disp_navi['tugi_from'] <= $sb_disp_all_num) {
    $qs = array(
        'host'    => $aThreadList->host,
        'bbs'     => $aThreadList->bbs,
        'spmode'  => $aThreadList->spmode,
        'norefresh' => '1',
        'from'    => $disp_navi['tugi_from'],
        'sb_view' => geti($_REQUEST['sb_view']),
        UA::getQueryKey() => UA::getQueryValue()
    );
    $qs = array_merge($word_qs, $qs);
    $tugi_ht = P2View::tagA(
        P2Util::buildQueryUri($_conf['subject_php'], $qs),
        hs("��") // {$_conf['k_accesskey']['next']}.��
        //,array($_conf['accesskey_for_k'] => $_conf['k_accesskey']['next'])
    );
}

if ($disp_navi['from'] == $disp_navi['end']) {
	$sb_range_on = $disp_navi['from'];
} else {
	$sb_range_on = "{$disp_navi['from']}-{$disp_navi['end']}";
}
$sb_range_st = "{$sb_range_on}/{$sb_disp_all_num} ";

$k_sb_navi_ht = '';
if (!$disp_navi['all_once']) {
    $k_sb_navi_ht = "<div>{$sb_range_st}{$mae_ht} {$tugi_ht}</div>";
}

// }}}

// dat�q��
// �X�y�V�������[�h�łȂ���΁A�܂��͂��ځ[�񃊃X�g�Ȃ�
$dat_soko_ht = _getDatSokoATag($aThreadList);

// ���ځ[�񒆂̃X���b�h
$taborn_link_atag = _getTabornLinkATag($aThreadList, $ta_num);

// �V�K�X���b�h�쐬
$buildnewthread_atag = _getBuildNewThreadATag($aThreadList);

// {{{ �\�[�g�ύX �i�V�� ���X No. �^�C�g�� �� ���΂₳ ���� �X�����ē� ���j

$sorts = array('midoku' => '�V��', 'res' => 'ڽ', 'no' => 'No.', 'title' => '����');
if ($aThreadList->spmode and $aThreadList->spmode != 'taborn' and $aThreadList->spmode != 'soko') {
    $sorts['ita'] = '��';
}
if ($_conf['sb_show_spd']) {
    $sorts['spd'] = '���΂₳';
}
if ($_conf['sb_show_ikioi']) {
    $sorts['ikioi'] = '����';
}
$sorts['bd'] = '�X�����ē�';
if ($_conf['sb_show_fav'] and $aThreadList->spmode != 'taborn') {
    $sorts['fav'] = '��';
}

$htm['change_sort'] = "<form method=\"get\" action=\"{$_conf['subject_php']}\">";
$htm['change_sort'] .= P2View::getInputHiddenKTag();
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

if (!empty($_REQUEST['sb_view'])) {
    $htm['change_sort'] .= sprintf('<input type="hidden" name="sb_view" value="%s">', hs($_REQUEST['sb_view']));
}

$htm['change_sort'] .= '�\�[�g:<select name="sort">';
foreach ($sorts as $k => $v) {
    $selected = '';
    if ($GLOBALS['now_sort'] == $k) {
        $selected = ' selected';
    }
    $htm['change_sort'] .= "<option value=\"{$k}\"{$selected}>{$v}</option>";
}
$htm['change_sort'] .= '</select>';
$htm['change_sort'] .= '<input type="submit" value="�ύX"></form>';

// }}}

// {{{ HTML�v�����g

/*
echo "<hr>";
echo $k_sb_navi_ht;

require_once P2_LIB_DIR . '/sb_toolbar_k.funcs.php'; // getShinchakuMatomeATag()
echo getShinchakuMatomeATag();
echo '<p>' . $allfav_atag . '</p>';
echo "<p>";
echo $dat_soko_ht;
echo $taborn_link_atag;
echo $buildnewthread_atag;
echo "</p>";
echo '<p>'. $htm['change_sort'] . '</p>';
//echo "<hr>";
echo "<p><a {$_conf['accesskey_for_k']}=\"0\" href=\"index.php{$_conf['k_at_q']}\">TOP</a></p>";
echo '</body></html>';
*/
/*
$k_sb_navi_ht �͉��̂R�Ɠ���
{$sb_range_st}
*/
$foot_sure_ht = ''; 
if ($dat_soko_ht) $foot_sure_ht .= "<span class=\"soko\">{$dat_soko_ht}</span>";
if ($buildnewthread_atag) $foot_sure_ht .= "<span class=\"build\">{$buildnewthread_atag}</span>";
if ($allfav_atag) $foot_sure_ht .= "<span class=\"all\">{$allfav_atag}</span>";
if ($taborn_link_atag) $foot_sure_ht .= "<span class=\"abon\">{$taborn_link_atag}</span>";
if ($mae_ht) $foot_sure_ht .= "<span class=\"mae\">{$mae_ht}</span>";
if ($tugi_ht) $foot_sure_ht .= "<span class=\"tugi\">{$tugi_ht}</span>";

$index_uri = P2Util::buildQueryUri('index.php', array(UA::getQueryKey() => UA::getQueryValue()));
?>
<?php echo $htm['change_sort']; ?> 
<div id="foot">
	<div class="foot_sure">
		<?php echo $foot_sure_ht; ?>
	</div>
</div>
<p><a id="backButton"class="button" href="<?php eh($index_uri); ?>">TOP</a></p>
</body></html>
<?php

// }}}


// ���̃t�@�C���ł̏����͂����܂�


//================================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//================================================================================
/**
 * @return  array
 */
function _getWordQs()
{
    $word_qs = array();
    if (!empty($GLOBALS['wakati_words'])) {
        $word_qs = array(
            'detect_hint' => '����',
            'method' => 'similar',
            'word'   => $GLOBALS['wakati_word']
        );
    } elseif (isset($GLOBALS['word'])) {
        $word_qs = array(
            'detect_hint' => '����',
            'word'   => $GLOBALS['word']
        );
    }
    return $word_qs;
}

/**
 * @return  string  <a>
 */
function _getAllFavATag($aThreadList, $sb_view)
{
    global $_conf;
    
    $allfav_atag = '';
    if ($aThreadList->spmode == 'fav' && $sb_view == 'shinchaku') {
        $uri = P2Util::buildQueryUri($_conf['subject_php'],
            array(
                'spmode' => 'fav',
                'norefresh' => '1',
                UA::getQueryKey() => UA::getQueryValue()
            )
        );
        $allfav_atag = P2View::tagA($uri, hs("�S�Ă̂��C�ɽڂ�\��"));
    }
    return $allfav_atag;
}

/**
 * �y�[�W�^�C�g������HTML
 *
 * @return  string  HTML
 */
function _getPtitleHtml($aThreadList, $ptitle_url)
{
    global $_conf;
    
    $ptitle_ht = '';
    
    if ($aThreadList->spmode == 'taborn') {
        $ptitle_ht = P2View::tagA(
            $ptitle_url,
            sprintf('%s.<b>%s</b>',
                hs($_conf['k_accesskey']['up']), hs($aThreadList->itaj)
            ),
            array($_conf['accesskey_for_k'] => $_conf['k_accesskey']['up'])
        ) . '�i���ݒ��j';

    } elseif ($aThreadList->spmode == 'soko') {
        $ptitle_ht = P2View::tagA(
            $ptitle_url,
            sprintf('%s.<b>%s</b>',
                hs($_conf['k_accesskey']['up']), hs($aThreadList->itaj)
            ),
            array($_conf['accesskey_for_k'] => $_conf['k_accesskey']['up'])
        ) . '�idat�q�Ɂj';

    } elseif (!empty($ptitle_url)) {
        $ptitle_ht = P2View::tagA($ptitle_url, sprintf('<b>%s</b>', hs($aThreadList->ptitle)));

    } else {
        $ptitle_ht = '<b>' . hs($aThreadList->ptitle) . '</b>';
    }
    
    return $ptitle_ht;
}

/**
 * @return  string  <a>
 */
function _getTabornLinkATag($aThreadList, $ta_num)
{
    global $_conf;
    
    $taborn_link_atag = '';
    if (!empty($ta_num)) {
        $uri = P2Util::buildQueryUri($_conf['subject_php'], array(
            'host'   => $aThreadList->host,
            'bbs'    => $aThreadList->bbs,
            'norefresh' => '1',
            'spmode' => 'taborn',
            UA::getQueryKey() => UA::getQueryValue()
        ));
        $taborn_link_atag = P2View::tagA($uri, hs("���ݒ�({$ta_num})"));
    }
    return $taborn_link_atag;
}

/**
 * @return  string  <a>
 */
function _getBuildNewThreadATag($aThreadList)
{
    $buildnewthread_atag = '';
    if (!$aThreadList->spmode and !P2Util::isHostKossoriEnq($aThreadList->host)) {
        $uri = P2Util::buildQueryUri('post_form_i.php', array(
            'host'   => $aThreadList->host,
            'bbs'    => $aThreadList->bbs,
            'newthread' => '1',
            UA::getQueryKey() => UA::getQueryValue()
        ));
        $buildnewthread_atag = P2View::tagA($uri, hs('�ڗ���'));
    }
    return $buildnewthread_atag;
}

/**
 * �i�r �O
 *
 * @return  string  HTML
 */
function _getMaeATag($aThreadList, $disp_navi, $word_qs)
{
    global $_conf;
    
    $mae_atag = '';
    
    if ($disp_navi['from'] > 1) {
        $qs = array(
            'host'    => $aThreadList->host,
            'bbs'     => $aThreadList->bbs,
            'spmode'  => $aThreadList->spmode,
            'norefresh' => '1',
            'from'    => $disp_navi['mae_from'],
            'sb_view' => geti($_REQUEST['sb_view']),
            UA::getQueryKey() => UA::getQueryValue()
        );
        $qs = array_merge($word_qs, $qs);
        $mae_atag = P2View::tagA(
            P2Util::buildQueryUri($_conf['subject_php'], $qs),
            hs('�O') // {$_conf['k_accesskey']['prev']}.�O
            //,array($_conf['accesskey_for_k'] => $_conf['k_accesskey']['prev'])
        );
    }
    return $mae_atag;
}

/**
 * @return  string  <a>
 */
function _getDatSokoATag($aThreadList)
{
    global $_conf;
    
    $dat_soko_atag = '';
    if (!$aThreadList->spmode or $aThreadList->spmode == 'taborn') {
        $uri = P2Util::buildQueryUri($_conf['subject_php'], array(
            'host'   => $aThreadList->host,
            'bbs'    => $aThreadList->bbs,
            'norefresh' => '1',
            'spmode' => 'soko',
            UA::getQueryKey() => UA::getQueryValue()
        ));
        $dat_soko_atag = P2View::tagA($uri, hs('dat�q��'));
    }
    return $dat_soko_atag;
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
