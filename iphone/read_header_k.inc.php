<?php
/*
    p2 -  �X���b�h�\�� -  �w�b�_���� -  �g�їp for read.php
*/

// �ϐ�
$diedat_msg_ht = '';

$info_st        = '�X�����\��';
$dele_st        = '���O�폜';
$prev_st        = '�O';
$next_st        = '��';
$shinchaku_st   = '�V��';
$moto_thre_st   = '���X��';
$siml_thre_st   = '�ގ��X��';
$latest_st      = '�V��';
$dores_st       = '���X';
$find_st        = '����';

$motothre_url   = $aThread->getMotoThread();
$ttitle_en      = base64_encode($aThread->ttitle);
$ttitle_urlen   = rawurlencode($ttitle_en);

// ��$xxx_q �͎g��Ȃ������i�p�~�������j
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

$read_navi_previous     = "";
$read_navi_previous_btm = "";
$read_navi_next         = "";
$read_navi_next_btm     = "";
$read_footer_navi_new_ht   = "";
$read_footer_navi_new_btm_ht = "";
$read_navi_latest       = "";
$read_navi_latest_btm   = "";
$read_navi_filter       = '';
$read_navi_filter_btm   = '';

$pointer_header_at = ' id="header" name="header"';

// ���X�͈̓Z���N�g�t�H�[��
$goto_select_ht = _csrangeform(isset($GLOBALS['word']) ? $last_hit_resnum : $aThread->resrange['to'], $aThread);

//----------------------------------------------
// $htm['read_navi_range'] -- 1- 101- 201-

$htm['read_navi_range'] = '';
//$htm['read_navi_range'] .= $goto_select_ht;
//080726�@�t�b�^�Ɉړ������܂�
/*
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
    $htm['read_navi_range'] .= "<a class=\"blueButton\" href=\"{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls={$i}-{$ito}{$offline_range_q}{$_conf['k_at_a']}\">{$i}-</a>\t";
    break;    // 1-�̂ݕ\��
}
*/

//----------------------------------------------
// $read_navi_previous -- �O
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
    $q = P2Util::buildQuery(array_merge(
        $thread_qs,
        array(
            //'ls'        => "{$before_rnum}-{$aThread->resrange['start']}n",
            'offline'   => '1',
            UA::getQueryKey() => UA::getQueryValue()
        )
    ));
    
//$html = "{$_conf['k_accesskey']['prev']}.{$prev_st}";
    $html = "{$prev_st}";
    $url = $_conf['read_php'] . '?' . $q;
    
    if ($aThread->resrange_multi and !empty($_REQUEST['page']) and $_REQUEST['page'] > 1) {
        $html = $html . '*';
       $url .= '&ls=' . $aThread->ls;
        $prev_page = intval($_REQUEST['page']) - 1;
        $url .= '&page=' . $prev_page;
    } else {
        $url .= '&ls=' . "{$before_rnum}-{$aThread->resrange['start']}n";
    }
    
    $read_navi_previous = P2View::tagA($url, $html);
    $read_navi_previous_btm = P2View::tagA($url, $html, array($_conf['accesskey_for_k'] => $_conf['k_accesskey']['prev']));
}

//----------------------------------------------
// $read_navi_next -- ��
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
    $url = P2Util::buildQueryUri($_conf['read_php'],
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
    
    // �傫�Ȏ�>�{�^��
    // disabled�̏ꍇ�A�Ȃ����Ԃ��J�����Ȃ�
    $goto_select_ht .= sprintf('<form style="margin-left: 1px; display:inline;" action="%s">', hs($_conf['read_php']));
    if (!$read_navi_next_isInvisible) {
        $goto_select_ht .= sprintf('<input type="hidden" name="host" value="%s">', hs($aThread->host));
        $goto_select_ht .= sprintf('<input type="hidden" name="bbs" value="%s">', hs($aThread->bbs));
        $goto_select_ht .= sprintf('<input type="hidden" name="key" value="%s">', hs($aThread->key));
        $goto_select_ht .= sprintf('<input type="hidden" name="offline" value="%s">', 1);
        $goto_select_ht .= sprintf('<input type="hidden" name="nt" value="%s">', $newtime);
        $goto_select_ht .= P2View::getInputHiddenKTag();
    }

    $st = "{$next_st}";
    //$url = $_conf['read_php'] . '?' . $q;

    // $aThread->resrange['to'] > $aThread->resrange_readnum
    if ($aThread->resrange_multi and !empty($aThread->resrange_multi_exists_next)) {
        $st = $st . '*';
        $url .= '&ls=' . $aThread->ls; // http_build_query() ��ʂ��� urlencode ���|�������Ȃ��H
        
        $page = isset($_REQUEST['page']) ? max(1, intval($_REQUEST['page'])) : 1;
        $next_page = $page + 1;
        $url .= '&page=' . $next_page;
        
        $goto_select_ht .= sprintf('<input type="hidden" name="ls" value="%s">', hs($aThread->ls));
        $goto_select_ht .= sprintf('<input type="hidden" name="page" value="%s">', hs($next_page));
        
    } else {
        $als = "{$aThread->resrange['to']}-{$after_rnum}n" . $read_navi_next_anchor;
        $url .= '&ls=' . $als;
        
        $goto_select_ht .= sprintf('<input type="hidden" name="ls" value="%s">', hs($als));
    }
    
    $read_navi_next = P2View::tagA($url, hs($st));
    $read_navi_next_btm = P2View::tagA($url, hs($st), array($_conf['accesskey_for_k'] => $_conf['k_accesskey']['next']));
}


$goto_select_ht .= sprintf(
    '<input id="large_next" type="submit" value="%s"%s />',
    $read_navi_next_isInvisible ? hs($next_st) . $rnum_range : hs($st) . $rnum_range,
    $read_navi_next_isInvisible ? ' disabled' : ''
);
$goto_select_ht .= '</form>';

//----------------------------------------------
// $read_footer_navi_new_ht  ������ǂ� �V�����X�̕\��
list($read_footer_navi_new_ht, $read_footer_navi_new_btm_ht) = _getReadFooterNaviNewHtmls($aThread, $shinchaku_st);


if (!$read_navi_next_isInvisible || $GLOBALS['_filter_hits'] !== null) {

    // �ŐVN��
    $read_navi_latest = <<<EOP
<a class="blueButton" href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls=l{$latest_show_res_num}{$_conf['k_at_a']}">{$latest_st}{$latest_show_res_num}</a> 
EOP;
    $time = time();
    $read_navi_latest_btm = <<<EOP
<a href="{$_conf['read_php']}?host={$aThread->host}{$bbs_q}{$key_q}&amp;ls=l{$latest_show_res_num}&amp;dummy={$time}{$_conf['k_at_a']}">{$latest_st}{$latest_show_res_num}</a> 
EOP;
}

// {{{ ����

$read_navi_filter = <<<EOP
<a href="read_filter_i.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$_conf['k_at_a']}">{$find_st}</a>
EOP;
$read_navi_filter_btm = <<<EOP
<a href="read_filter_i.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$_conf['k_at_a']}">{$find_st}</a>
EOP;

// }}}

// �������̓��ʂȏ���
if ($_filter_hits !== NULL) {
    require_once P2_LIB_DIR . '/read_filter_k.funcs.php';
    resetReadNaviHeaderK();
}

//====================================================================
// HTML�o��
//====================================================================

// {{{ �c�[���o�[����HTML

// ���C�Ƀ}�[�N�ݒ�
$favmark    = !empty($aThread->fav) ? '��' : '+';
$favvalue   = !empty($aThread->fav) ? 0 : 1;
$favtitle   = $favvalue ? '���C�ɃX���ɒǉ�' : '���C�ɃX������O��';
$favtitle   .= '�i�A�N�Z�X�L�[[f]�j';
$setfav_q   = '&amp;setfav=' . $favvalue;

$toolbar_right_ht = _getToolbarRightHtml($aThread, $ttitle_en, $info_st, $dele_st, $moto_thre_st);

//iPhone �p�@�߂�{�^��
$b_qs = array(
    UA::getQueryKey() => UA::getQueryValue()
);
$atag = P2View::tagA(
    P2Util::buildQueryUri($_conf['subject_php'],
        array_merge($thread_qs, $b_qs)
    ),
    hs($aThread->itaj),
    array('class' => 'button', 'id' => 'backButton')
);
$toolbar_back_board = "<p>$atag</p>";
    
// }}}

$body_at = '';
//$body_at = P2View::getBodyAttrK();

    //$body_at .= " onunload=\"document.frmresrange.reset()\"";
    /* iPhone �L���b�V�����̂��ߍ폜 2008/7/24 */
//=====================================
//!empty($_GET['nocache']) and P2Util::headerNoCache();
P2View::printDoctypeTag();

$onload_script .= "checkSage();"; // �������݃t�H�[����sage�Ƀ`�F�b�N������

?>
<html>
<head>
<?php
    P2View::printExtraHeadersHtml();
    ?>
	<script type="text/javascript" src="js/basic.js?v=20090429"></script>
	<script type="text/javascript" src="iphone/js/respopup.iPhone.js?v=20090429"></script>
	<script type="text/javascript" src="iphone/js/setfavjs.js?v=20090428"></script>
	<script type="text/javascript" src="js/post_form.js?v=20090724"></script>
    <script type="text/javascript"> 
	<!-- 
	// iPhone��URL�ҏW������\�����Ȃ��悤�X�N���[������
	window.onload = function() { 
		setTimeout(scrollTo, 100, 0, 1); 
	}

	// �y�[�W�ǂݍ��݊������R�[���o�b�N�֐�
	gIsPageLoaded = false;
	addLoadEvent(function() {	// basic.js�̃��\�b�h
		// �y�[�W���[�h�����t���O(true����Ȃ��Ƃ��C�ɓ���ύXjavascript�������Ȃ�)
		gIsPageLoaded = true;
		// �y�[�W�ǂݍ��݊������Ɏ��s����X�N���v�g�Q
		<?php echo $onload_script; ?>
	});

	// ���X�͈͂̃t�H�[���̓��e�����Z�b�g���Ă���y�[�W�ڍs���郁�\�b�h
	var onArreyt = 2;
	function formReset() {
		var uriValue = "<?php echo $_conf['read_php']; ?>?"
					+ "offline=1&"
					//+ "b=" + document.frmresrange.b.value + "&"
					+ "b=i&"
					+ "host=" + document.frmresrange.host.value + "&"
					+ "bbs=" + document.frmresrange.bbs.value + "&"
					+ "key=" + document.frmresrange.key.value + "&"
					+ "rescount=" + document.frmresrange.rescount.value + "&"
					+ "ttitle_en=" + document.frmresrange.ttitle_en.value + "&"
					+ "ls=" + document.frmresrange.ls.value + "&";
		document.frmresrange.reset();
		window.location.assign(uriValue);
	}
	// --> 
	</script>

	<link rel="stylesheet" type="text/css" href="./iui/read.css">
	<title><?php echo $ptitle_ht; ?></title>
<?php


//iPhone SMP
$onload_script = "";

if ($_conf['bottom_res_form']) {
    ?><script type="text/javascript" src="js/post_form.js?v=20090724"></script>
<?php
    $onload_script .= "checkSage();";
}

if (empty($_GET['onlyone'])) {
    $onload_script .= "setWinTitle();";
}

$fade = empty($_GET['fade']) ? 'false' : 'true';
$existWord = (strlen($GLOBALS['word']) > 0) ? 'true' : 'false';

echo <<<EOP
<script type="text/javascript">
<!--
gFade = {$fade};
gExistWord = {$existWord};
gIsPageLoaded = false;
addLoadEvent(function() {
	gIsPageLoaded = true;
	{$onload_script}
});
//-->
</script>\n
EOP;

// �X�}�[�g�|�b�v�A�b�v���j���[ JavaScript�R�[�h
if ($_conf['enable_spm']) {
    // smartpopup.iPhone.js needs post_form.js's popUpFootbarFormIPhone().
    ?><script type="text/javascript" src="iphone/js/smartpopup.iPhone.js?v=20070308"></script><?php
    
    // �t�H���g�T�C�Y�� conf_user_style.inc.php  ���������PC���ς��̂ł����ŏ�������
    $STYLE['respop_color'] = "#FFFFFF"; // ("#000") ���X�|�b�v�A�b�v�̃e�L�X�g�F
    $STYLE['respop_bgcolor'] = ""; // ("#ffffcc") ���X�|�b�v�A�b�v�̔w�i�F
    $STYLE['respop_fontsize'] = '13px';
    $aThread->showSmartPopUpMenuJs();
}

echo <<<EOP
</head>
<body{$body_at} >\n
EOP;

P2Util::printInfoHtml();

// �X�����T�[�o�ɂȂ����
if ($aThread->diedat) { 

    $motothre_atag = P2View::tagA($motothre_url, hs($moto_thre_st));
    
    echo $diedat_msg_ht = _getGetDatErrorMsgHtml($aThread);
    echo "<p>$motothre_atag</p>";
    
    // �������X���Ȃ���΃c�[���o�[�\��
    if (!$aThread->rescount) {
        echo "<p>{$toolbar_right_ht}</p>";
    }
}


if (($aThread->rescount or !empty($_GET['onlyone']) && !$aThread->diedat) and empty($_GET['renzokupop'])) {

    echo <<<EOP
<div class="toolbar">
{$htm['read_navi_range']}
EOP;

    // ���C�ɃX���ɒǉ�/�O��
	echo "<span class=\"setfav\" style=\"white-space: nowrap;\"><a class=\"favbutton\" href=\"info_i.php?host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$setfav_q}{$sid_q}\" target=\"info\" onClick=\"return setFavJs('host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$sid_q}', '{$favvalue}', {$STYLE['info_pop_size']}, 'read', this);\" accesskey=\"f\" title=\"{$favtitle}\">{$favmark}</a></span>";

	echo <<< EOP
<a class="button" href="javascript:window.scrollBy(0, document.height)" target="_self">��</a>
</div>
EOP;

/* iPhone �p�ɏ��O��
{$read_navi_previous}
<!-- {$read_navi_next} -->
{$read_navi_latest}
*/
}

//echo "<hr>";
/*
?><h3><font color="<?php eh($STYLE['read_k_thread_title_color']); ?>"><?php eh($aThread->ttitle); ?></font></h3><?php
*/
?><h4 class="thread_title"><?php eh($aThread->ttitle); ?></h4><?php

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

echo P2View::getHrHtmlK();


//=======================================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//=======================================================================================

/**
 * 1- �̂ݕ\����select�t�H�[���ŕ\������
 * �iiPhone�p�ɒǉ��j
 *
 * @return string
 */
function _csrangeform($default = '', &$aThread)
{
    global $_conf;

    //$numonly_at = 'maxlength="4" istyle="4" format="*N" mode="numeric"';
    $numonly_at = 'maxlength="4" istyle="4" format="4N" mode="numeric"';
    
    $form = '<form method="get" name="frmresrange" id="frmresrange" style="display:inline;">';
    $form .= '<input type="hidden" name="offline" value="1">';
    $form .= P2View::getInputHiddenKTag();
    
    $required_params = array('host', 'bbs', 'key');
    foreach ($required_params as $k) {
        if (!empty($_REQUEST[$k])) {
            $form .= sprintf(
                '<input type="hidden" name="%s" value="%s">',
                htmlspecialchars($k), htmlspecialchars($_REQUEST[$k], ENT_QUOTES)
            );
        } else {
            return '';
        }
    }

    $form .= '<input type="hidden" name="rescount" value="' . hs($aThread->rescount) . '">';
    $form .= '<input type="hidden" name="ttitle_en" value="' . hs(base64_encode($aThread->ttitle)) . '">';

    $form .= sprintf('<select name="ls" action="%s" onChange="formReset()">', hs($_conf['read_php']));
    $form .= "<option disabled>�X�����ړ�($aThread->rescount)</option>";
    for ($i = 1; $i <= $aThread->rescount; $i = $i + $_conf['k_rnum_range']) {
	    $offline_range_q = "";
	    $accesskey_at = "";
	    if ($i == 1) {
	        $accesskey_at = " {$_conf['accesskey_for_k']}=\"1\"";
	    }
	    $ito = $i + $_conf['k_rnum_range'] -1;
	    if ($ito <= $aThread->gotnum) {
	        $offline_range_q = '&amp;offline=1';
	    }
	    $form .= "<option value=\"{$i}-{$ito}\">{$i}-</option>";
	}
    /*
    2006/03/06 aki �m�[�}��p2�ł͖��Ή�
    if ($_conf['expack.aas.enabled']) {
        $form .= '<option value="aas">AAS</option>';
        $form .= '<option value="aas_rotate">AAS*</option>';
    }
    */
    $form .= '</select>';
    
    $form .= '</form>';

    return $form;
}

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
        $read_footer_navi_new_uri = P2Util::buildQueryUri(
            $_conf['read_php'],
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
            "{$shinchaku_st}"
        );
        $read_footer_navi_new_btm_ht = P2View::tagA(
            $read_footer_navi_new_uri,
            "{$shinchaku_st}"
        );
    }
    return array($read_footer_navi_new_ht, $read_footer_navi_new_btm_ht);
}

/**
 * �c�[���o�[����HTML
 *
 * @return  string  HTML
 */
function _getToolbarRightHtml($aThread, $ttitle_en, $info_st, $dele_st, $moto_thre_st)
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
    $similar_qs = array(
        'detect_hint' => '����',
        'itaj_en'     => base64_encode($aThread->itaj),
        'method'      => 'similar',
        'word'        => $aThread->ttitle_hc
        // 'refresh' => 1
    );

    $info_atag = P2View::tagA(
        P2Util::buildQueryUri(
            $_conf['subject_php'],
            array_merge($thread_qs, $b_qs, $similar_qs, array('refresh' => '1'))
        ),
        hs('�X�����/�ގ�')
    );
    
    $dele_atag     = P2View::tagA(
        P2Util::buildQueryUri(
            'info_i.php',
            array_merge($thread_qs, $b_qs,
                array(
                    'ttitle_en' => $ttitle_en,
                    'dele'      => 1
                )
            )
        ),
        hs($dele_st)
    );
    
    return $toolbar_right_ht = <<<EOTOOLBAR
    <li class="whiteButton">$info_atag</li>
    <li class="whiteButton">$dele_atag</li>
    <li class="whiteButton"><a href="{$motothre_url}">{$moto_thre_st}</a></li>
EOTOOLBAR;
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
        $diedat_msg_ht = "<p><b>p2 info - �T�[�o����ŐV�̃X���b�h�����擾�ł��܂���ł����B</b></p>";
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
