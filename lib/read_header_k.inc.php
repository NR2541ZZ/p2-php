<?php
/*
    p2 -  �X���b�h�\�� -  �w�b�_���� -  �g�їp for read.php
*/

// �ϐ�
$diedat_msg_ht = '';

$info_st        = '��';
$dele_st        = '��';
$prev_st        = '�O';
$next_st        = '��';
$shinchaku_st   = '�V��';
$moto_thre_st   = '��';
$siml_thre_st   = '��';
$latest_st      = '�V';
$dores_st       = '��';
$find_st        = '��';

$motothre_url   = $aThread->getMotoThread();
$ttitle_en      = base64_encode($aThread->ttitle);
$ttitle_urlen   = rawurlencode($ttitle_en);

// ��$xxx_q �͎g��Ȃ������悢�i�p�~�������j
$ttitle_en_q    = "&amp;ttitle_en=" . $ttitle_urlen;
$bbs_q          = "&amp;bbs=" . $aThread->bbs;
$key_q          = "&amp;key=" . $aThread->key;
$offline_q      = "&amp;offline=1";

$word_hs        = hs($GLOBALS['word']);

$thread_qs = array(
    'host' => $aThread->host,
    'bbs'  => $aThread->bbs,
    'key'  => $aThread->key
);

$newtime = date('gis');  // ���������N���N���b�N���Ă��ēǍ����Ȃ��d�l�ɑ΍R����_�~�[�N�G���[

//=================================================================
// �w�b�_HTML
//=================================================================

// ���C�Ƀ}�[�N�ݒ�
$favmark = $aThread->fav ? '<span class="fav">��</span>' : '<span class="fav">+</span>';
$favvalue = $aThread->fav ? 0 : 1;

// ���X�i�r�ݒ� =====================================================

$rnum_range = $_conf['k_rnum_range'];
$latest_show_res_num = $_conf['k_rnum_range']; // �ŐVXX

$read_navi_previous_ht     = '';
$read_navi_previous_btm_ht = '';
$read_navi_next_ht         = '';
$read_navi_next_btm_ht  = '';
$read_footer_navi_new_ht   = '';
$read_footer_navi_new_btm_ht = '';
$read_navi_latest_ht       = '';
$read_navi_latest_btm_ht   = '';
$read_navi_filter_ht       = '';
$read_navi_filter_btm_ht   = '';

$pointer_header_at = ' id="header" name="header"';

//----------------------------------------------
// $htm['read_navi_range'] -- 1- 101- 201-

$htm['read_navi_range'] = '';
for ($i = 1; $i <= $aThread->rescount; $i = $i + $rnum_range) {
    $offline_range_q = "";
    $accesskey_at = "";
    if ($i == 1) {
        $accesskey_at = " {$_conf['accesskey_for_k']}=\"1\"";
    }
    $ito = $i + $rnum_range -1;
    if ($ito <= $aThread->gotnum) {
        $offline_range_q = $offline_q;
    }
    $htm['read_navi_range'] .= "<a{$accesskey_at}{$pointer_header_at} href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$i}-{$ito}{$offline_range_q}{$_conf['k_at_a']}\">{$i}-</a>"."\t";
    break;    // 1- �̂ݕ\��
}


//----------------------------------------------
// $read_navi_previous_ht -- �O
$before_rnum = $aThread->resrange['start'] - $rnum_range;
if ($before_rnum < 1) {
    $before_rnum = 1;
}
if ($aThread->resrange['start'] == 1 or !empty($_GET['onlyone'])) {
    $read_navi_prev_isInvisible = true;
} else {
    $read_navi_prev_isInvisible = false;
}

$read_navi_prev_anchor = '';
//if ($before_rnum != 1) {
//    $read_navi_prev_anchor = "#r{$before_rnum}";
//}

if (!$read_navi_prev_isInvisible) {
    $q = UriUtil::buildQuery(array_merge(
        $thread_qs,
        array(
            //'ls'        => "{$before_rnum}-{$aThread->resrange['start']}n",
            'offline'   => '1',
            UA::getQueryKey() => UA::getQueryValue()
        )
    ));
    $html = "{$_conf['k_accesskey']['prev']}.{$prev_st}";
    $url = $_conf['read_php'] . '?' . $q;
    
    if ($aThread->resrange_multi and !empty($_REQUEST['page']) and $_REQUEST['page'] > 1) {
        $html = $html . '*';
        // ls �� http_build_query() ��ʂ��� urlencode ���|�������Ȃ��̂�
        // �� 2007/10/04 urlencode�����Ƃǂ�����̂��낤�B���̗��R��Y�ꂽ�B�����Ă������悤�ȋC�����邪�B
        $url .= '&ls=' . $aThread->ls;
        $prev_page = intval($_REQUEST['page']) - 1;
        $url .= '&page=' . $prev_page;
    } else {
        $url .= '&ls=' . "{$before_rnum}-{$aThread->resrange['start']}n";
    }
    
    $read_navi_previous_ht = P2View::tagA($url, $html);
    $read_navi_previous_btm_ht = P2View::tagA($url, $html, array($_conf['accesskey_for_k'] => $_conf['k_accesskey']['prev']));
}

//----------------------------------------------
// $read_navi_next_ht -- ��
$read_navi_next_isInvisible = false;
if ($aThread->resrange['to'] >= $aThread->rescount and empty($_GET['onlyone'])) {
    $aThread->resrange['to'] = $aThread->rescount;
    //$read_navi_next_anchor = "#r{$aThread->rescount}";
    if (!($aThread->resrange_multi and !empty($aThread->resrange_multi_exists_next))) {
        $read_navi_next_isInvisible = true;
    }
} else {
    // $read_navi_next_anchor = "#r{$aThread->resrange['to']}";
}
if ($aThread->resrange['to'] == $aThread->rescount) {
    $read_navi_next_anchor = "#r{$aThread->rescount}";
} else {
    $read_navi_next_anchor = '';
}

$after_rnum = $aThread->resrange['to'] + $rnum_range;

if (!$read_navi_next_isInvisible) {
    $url = UriUtil::buildQueryUri($_conf['read_php'],
        array_merge(
            $thread_qs,
            array(
                //'ls'        => "{$aThread->resrange['to']}-{$after_rnum}n",
                'offline'   => '1',
                'nt'        => $newtime,
                UA::getQueryKey() => UA::getQueryValue()
            )
        )
    );
    
    $html = "{$_conf['k_accesskey']['next']}.{$next_st}";

    // $aThread->resrange['to'] > $aThread->resrange_readnum
    if ($aThread->resrange_multi and !empty($aThread->resrange_multi_exists_next)) {
        $html = $html . '*';
        $url .= '&ls=' . $aThread->ls; // http_build_query() ��ʂ��� urlencode ���|�������Ȃ��H
        $page = isset($_REQUEST['page']) ? max(1, intval($_REQUEST['page'])) : 1;
        $next_page = $page + 1;
        $url .= '&page=' . $next_page;
    } else {
        $url .= '&ls=' . "{$aThread->resrange['to']}-{$after_rnum}n" . $read_navi_next_anchor;
    }
    
    $read_navi_next_ht = P2View::tagA($url, $html);
    $read_navi_next_btm_ht = P2View::tagA($url, $html,
        array($_conf['accesskey_for_k'] => $_conf['k_accesskey']['next'])
    );
}

//----------------------------------------------
// $read_footer_navi_new_ht  ������ǂ� �V�����X�̕\��
list($read_footer_navi_new_ht, $read_footer_navi_new_btm_ht) = _getReadFooterNaviNewHtmls($aThread, $shinchaku_st);


if (!$read_navi_next_isInvisible || $GLOBALS['_filter_hits'] !== null) {

    // �ŐVN��
    $read_navi_latest_ht = <<<EOP
<a href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls=l{$latest_show_res_num}{$_conf['k_at_a']}">{$_conf['k_accesskey']['latest']}.{$latest_st}{$latest_show_res_num}</a> 
EOP;
    $time = time();
    $read_navi_latest_btm_ht = <<<EOP
<a {$_conf['accesskey_for_k']}="{$_conf['k_accesskey']['latest']}" href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls=l{$latest_show_res_num}&amp;dummy={$time}{$_conf['k_at_a']}">{$_conf['k_accesskey']['latest']}.{$latest_st}{$latest_show_res_num}</a> 
EOP;
}

// {{{ ����

$read_navi_filter_ht = <<<EOP
<a href="read_filter_k.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$_conf['k_at_a']}">{$find_st}</a>
EOP;
$read_navi_filter_btm_ht = <<<EOP
<a {$_conf['accesskey_for_k']}="{$_conf['k_accesskey']['filter']}" href="read_filter_k.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$_conf['k_at_a']}">{$_conf['k_accesskey']['filter']}.{$find_st}</a>
EOP;

// }}}

// �������̓��ʂȏ���
if ($_filter_hits !== NULL) {
    require_once P2_LIB_DIR . '/read_filter_k.funcs.php';
    resetReadNaviHeaderK();
}

// �c�[���o�[����HTML
$toolbar_right_ht = _getToolbarRightHtml($aThread, $ttitle_en, $siml_thre_st, $info_st, $dele_st, $moto_thre_st);


$hr = P2View::getHrHtmlK();

//====================================================================
// HTML�o��
//====================================================================

//!empty($_GET['nocache']) and P2Util::headerNoCache();
P2View::printDoctypeTag();
?>
<html>
<head>
<?php
P2View::printExtraHeadersHtml();
?>
	<title><?php echo $ptitle_ht ?> </title>
</head>
<body<?php echo P2View::getBodyAttrK() ?>>
<?php

P2Util::printInfoHtml();

// �X�����T�[�o�ɂȂ����
if ($aThread->diedat) { 

    echo $diedat_msg_ht = _getGetDatErrorMsgHtml($aThread);
    echo "<p>$motothre_atag</p>$hr";
    
    // �������X���Ȃ���΃c�[���o�[�\��
    if (!$aThread->rescount) {
        echo "<p>{$toolbar_right_ht}</p>";
    }
}


if (($aThread->rescount or !empty($_GET['onlyone']) && !$aThread->diedat) and empty($_GET['renzokupop'])) {

    echo <<<EOP
<p>
{$htm['read_navi_range']}
{$read_navi_previous_ht}
<!-- {$read_navi_next_ht} -->
{$read_navi_latest_ht}
<a {$_conf['accesskey_for_k']}="{$_conf['k_accesskey']['bottom']}" href="#footer">{$_conf['k_accesskey']['bottom']}.��</a>
</p>\n
EOP;

}

echo $hr;
?><h3><font color="<?php eh($STYLE['read_k_thread_title_color']); ?>"><?php eh($aThread->ttitle_hc); ?> </font></h3><?php

$filter_fields = array(
    'whole' => '',
    'msg'   => 'ү���ނ�',
    'name'  => '���O��',
    'mail'  => 'Ұق�',
    'date'  => '���t��',
    'id'    => 'ID��',
    'belv'  => '�߲�Ă�'
);

if (isset($GLOBALS['word']) && strlen($GLOBALS['word'])) {
    echo "��������: ";
    echo "{$filter_fields[$res_filter['field']]}";
    echo "&quot;{$word_hs}&quot;��";
    echo ($res_filter['match'] == 'on') ? '�܂�' : '�܂܂Ȃ�';
}

echo $hr;


// ���̃t�@�C���ł̏����͂����܂�


//=======================================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//=======================================================================================

/**
 * �V�����X�̕\�� <a>
 *
 * @return  array
 */
function _getReadFooterNaviNewHtmls($aThread, $shinchaku_st)
{
    global $_conf;
    
    $read_footer_navi_new_ht = '';
    $read_footer_navi_new_btm_ht = '';
    
    if ($aThread->resrange['to'] == $aThread->rescount) {
    
        // �V�����X�̕\�� <a>
        $read_footer_navi_new_uri = UriUtil::buildQueryUri($_conf['read_php'],
            array(
                'host' => $aThread->host,
                'bbs'  => $aThread->bbs,
                'key'  => $aThread->key,
                'ls'   => "{$aThread->rescount}-n",
                'nt'   => date('gis'), // �L���b�V������̃_�~�[�N�G���[
                UA::getQueryKey() => UA::getQueryValue()
            )
        ) . '#r' . rawurlencode($aThread->rescount);
    
        $read_footer_navi_new_ht = P2View::tagA(
            $read_footer_navi_new_uri,
            "{$_conf['k_accesskey']['next']}.{$shinchaku_st}"
        );
        $read_footer_navi_new_btm_ht = P2View::tagA(
            $read_footer_navi_new_uri,
            "{$_conf['k_accesskey']['next']}.{$shinchaku_st}",
            array($_conf['accesskey_for_k'] => $_conf['k_accesskey']['next'])
        );
    }
    return array($read_footer_navi_new_ht, $read_footer_navi_new_btm_ht);
}

/**
 * �c�[���o�[����HTML
 *
 * @return  string  HTML
 */
function _getToolbarRightHtml($aThread, $ttitle_en, $siml_thre_st, $info_st, $dele_st, $moto_thre_st)
{
    global $_conf, $motothre_url;
    
    $thread_qs = array(
        'host' => $aThread->host,
        'bbs'  => $aThread->bbs,
        'key'  => $aThread->key
    );

    $b_qs = array(
        UA::getQueryKey() => UA::getQueryValue()
    );

    $ita_atag      = P2View::tagA(
        UriUtil::buildQueryUri($_conf['subject_php'],
            array_merge($thread_qs, $b_qs)
        ),
        "{$_conf['k_accesskey']['up']}." . hs($aThread->itaj),
        array($_conf['accesskey_for_k'] => $_conf['k_accesskey']['up'])
    );
    
    $similar_qs = array(
        'detect_hint' => '����',
        'itaj_en'     => base64_encode($aThread->itaj),
        'method'      => 'similar',
        'word'        => $aThread->ttitle_hc
        // 'refresh' => 1
    );
    $similar_atag  = P2View::tagA(
        UriUtil::buildQueryUri($_conf['subject_php'],
            array_merge($similar_qs, $thread_qs, $b_qs, array('refresh' => '1'))
        ),
        $siml_thre_st
    );

    $info_php = UA::isIPhoneGroup() ? 'info_i.php' : 'info.php';

    $info_atag     = P2View::tagA(
        UriUtil::buildQueryUri($info_php,
            array_merge($thread_qs, $b_qs, array('ttitle_en' => $ttitle_en))
        ),
        "{$_conf['k_accesskey']['info']}." . hs($info_st),
        array($_conf['accesskey_for_k'] => $_conf['k_accesskey']['info'])
    );

    $dele_atag     = P2View::tagA(
        UriUtil::buildQueryUri($info_php,
            array_merge($thread_qs, $b_qs,
                array(
                    'ttitle_en' => $ttitle_en,
                    'dele'      => 1
                )
            )
        ),
        "{$_conf['k_accesskey']['dele']}." . hs($dele_st),
        array($_conf['accesskey_for_k'] => $_conf['k_accesskey']['dele'])
    );

    $motothre_atag = P2View::tagA($motothre_url, hs($moto_thre_st));

    return $toolbar_right_ht = "$ita_atag $similar_atag $info_atag $dele_atag $motothre_atag";
}

/**
 * @return  string  HTML
 */
function _getGetDatErrorMsgHtml($aThread)
{
    $diedat_msg_ht = '';
    if ($aThread->getdat_error_msg_ht) {
        $diedat_msg_ht = $aThread->getdat_error_msg_ht;
    } else {
        $diedat_msg_ht = $aThread->getDefaultGetDatErrorMessageHTML();
    }
    return $diedat_msg_ht;
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
