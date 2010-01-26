<?php
/*
    p2 -  �T�u�W�F�N�g - �w�b�_�\��
    for subject.php
*/

//===================================================================
// �ϐ�
//===================================================================
$newtime = date('gis');
$reloaded_time = date('m/d G:i:s'); // �X�V����

// ���ځ[������̃��b�Z�[�WHTML���擾����B�X�����ځ[�񒆁A�q�ɁB
$taborn_check_msg_html = _getTabornCheckMsgHtml($aThreadList, $online_num);

//===============================================================
// HTML�\���p�ϐ� for �c�[���o�[(sb_toolbar.inc.php) 
//===============================================================

// �y�[�W�^�C�g������URL
$ptitle_url = _getPageTitleUrl($aThreadList);

// �y�[�W�^�C�g������HTML
$ptitle_ht = _getPageTitleHtml($aThreadList, $ptitle_url);


// �r���[�����ݒ� ==============================================

$edit_ht = '';

// �X�y�V�������[�h��
if ($aThreadList->spmode) {

    // ���C�ɃX�� or �a���Ȃ�
    if ($aThreadList->spmode == 'fav' or $aThreadList->spmode == 'palace') {
        $qs = array(
            'spmode' => $aThreadList->spmode,
            'norefresh' => '1',
            UA::getQueryKey() => UA::getQueryValue()
        );
        $attrs = array('class' => 'narabi', 'target' => '_self');
        if ($sb_view == 'edit') {
            $edit_atag = P2View::tagA(
                UriUtil::buildQueryUri($_conf['subject_php'], $qs),
                hs('����'),
                $attrs
            );
            $edit_ht = $edit_atag;
        } else {
            $edit_atag = P2View::tagA(
                UriUtil::buildQueryUri($_conf['subject_php'], array_merge(array('sb_view' => 'edit'), $qs)),
                hs('����'),
                $attrs
            );
            $edit_ht = $edit_atag;
        }
    }
}

// �t�H�[�� hidden HTML���Z�b�g
$sb_form_hidden_ht = <<<EOP
			<input type="hidden" name="detect_hint" value="����">
			<input type="hidden" name="bbs" value="{$aThreadList->bbs}">
			<input type="hidden" name="host" value="{$aThreadList->host}">
			<input type="hidden" name="spmode" value="{$aThreadList->spmode}">
			{$_conf['k_input_ht']}
EOP;

// {{{ �\������ �t�H�[��HTML���Z�b�g

$sb_disp_num_ht = '';

if (!$aThreadList->spmode || $aThreadList->spmode == "news") {
    
    $keys = array(100, 150, 200, 250, 300, 400, 500, 'all');
    foreach ($keys as $v) {
        $vn_selecteds[$v] = null;
    }
    
    $viewnum = isset($p2_setting['viewnum']) ? $p2_setting['viewnum'] : 150;
    $vn_selecteds[$viewnum] = 'selected';
    
    $sb_disp_num_ht = <<<EOP
			<select name="viewnum" title="�X���b�h�\������">
				<option value="100"{$vn_selecteds[100]}>100��</option>
				<option value="150"{$vn_selecteds[150]}>150��</option>
				<option value="200"{$vn_selecteds[200]}>200��</option>
				<option value="250"{$vn_selecteds[250]}>250��</option>
				<option value="300"{$vn_selecteds[300]}>300��</option>
				<option value="400"{$vn_selecteds[400]}>400��</option>
				<option value="500"{$vn_selecteds[500]}>500��</option>
				<option value="all"{$vn_selecteds['all']}>�S��</option>
			</select>

EOP;
}

// }}}
// {{{ �t�B���^���� �t�H�[��HTML���Z�b�g

if ($_conf['enable_exfilter'] == 2) {

    $selected_method = array('and' => '', 'or' => '', 'just' => '', 'regex' => '', 'similar' => '');
    $selected_method[($sb_filter['method'])] = ' selected';
    
    $sb_form_method_ht = <<<EOP
			<select id="method" name="method">
				<option value="or"{$selected_method['or']}>�����ꂩ</option>
				<option value="and"{$selected_method['and']}>���ׂ�</option>
				<option value="just"{$selected_method['just']}>���̂܂�</option>
				<option value="regex"{$selected_method['regex']}>���K�\��</option>
				<option value="similar"{$selected_method['similar']}>���R��</option>
			</select>

EOP;
}

$word_hs = hsi($GLOBALS['wakati_word'], hsi($GLOBALS['word']));

$checked_ht['find_cont'] = !empty($_REQUEST['find_cont']) ? 'checked' : '';

$input_find_cont_ht = <<<EOP
<span title="���X�������ΏۂɊ܂߂�iDAT�擾�ς݃X���b�h�̂݁j"><input type="checkbox" name="find_cont" value="1"{$checked_ht['find_cont']}>���X</span>
EOP;

$filter_form_ht = <<<EOP
		<form class="toolbar" method="GET" action="{$_conf['subject_php']}" accept-charset="{$_conf['accept_charset']}" target="_self">
			{$sb_form_hidden_ht}
			<input type="text" id="word" name="word" value="{$word_hs}" size="16">{$sb_form_method_ht}
			{$input_find_cont_ht}
			<input type="submit" name="submit_kensaku" value="����">
		</form>

EOP;

// }}}

// �`�F�b�N�������ڂ� ���O���폜, ���ځ[������� �t�H�[��HTML�̃w�b�_�������擾����
$check_form_ht = _getCheckFormHeaderHtml($aThreadList, $deletelog_st, $abornoff_st);

//===================================================================
// HTML�v�����g
//===================================================================
P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
<?php
P2View::printExtraHeadersHtml();

if ($_conf['refresh_time']) {
    $refresh_time_s = $_conf['refresh_time'] * 60;
    $qs = array(
        'host'   => $aThreadList->host,
        'bbs'    => $aThreadList->bbs,
        'spmode' => $aThreadList->spmode,
        UA::getQueryKey() => UA::getQueryValue()
    );
    if (defined('SID') && strlen(SID)) {
        $qs[session_name()] = session_id();
    }
    $refresh_url = $_conf['subject_php'] . '?' . UriUtil::buildQuery($qs);
    ?>
    <meta http-equiv="refresh" content="<?php eh($refresh_time_s) ?>;URL=<?php eh($refresh_url); ?>">
    <?php
}

?>
    <title><?php eh($aThreadList->ptitle); ?></title>
    <base target="read">
<?php

    P2View::printIncludeCssHtml('style');
    P2View::printIncludeCssHtml('subject');
    ?>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	
	<script type="text/javascript" src="js/basic.js?v=20090429"></script>
	<script type="text/javascript" src="js/setfavjs.js?v=20090428"></script>
	<script type="text/javascript" src="js/delelog.js?v=20061206"></script>
	<script language="JavaScript">
	<!--
	function setWinTitle(){
		var shinchaku_ari = <?php echo !$shinchaku_num ? 'false' : 'true'; ?>;
		if (shinchaku_ari) {
			window.top.document.title="��<?php echo _addslashesJS($aThreadList->ptitle); ?>";
		} else {
			if (top != self) { top.document.title=self.document.title; }
		}
	}

	function chNewAllColor()
	{
		var smynum1 = document.getElementById('smynum1');
		if (smynum1) {
			smynum1.style.color="<?php echo _addslashesJS($STYLE['sb_ttcolor']); ?>";
		}
		var smynum2 = document.getElementById('smynum2')
		if (smynum2) {
			smynum2.style.color="<?php echo _addslashesJS($STYLE['sb_ttcolor']); ?>";
		}
		var a = document.getElementsByTagName('a');
		for (var i = 0; i < a.length; i++) {
			if (a[i].className == 'un_a') {
				a[i].style.color = "<?php echo _addslashesJS($STYLE['sb_ttcolor']); ?>";
			}
		}
	}
	function chUnColor(id){
		var unid_obj = document.getElementById('un'+id);
		if (unid_obj) {
			unid_obj.style.color="<?php echo _addslashesJS($STYLE['sb_ttcolor']); ?>";
		}
	}
	function chTtColor(idnum){
		var ttid = "tt"+idnum;
		var toid = "to"+idnum;
		var ttid_obj = document.getElementById(ttid);
		if (ttid_obj) {
			ttid_obj.style.color="<?php echo _addslashesJS($STYLE['thre_title_color_v']); ?>";
		}
		var toid_obj = document.getElementById(toid);
		if (toid_obj) {
			toid_obj.style.color="<?php echo _addslashesJS($STYLE['thre_title_color_v']); ?>";
		}
	}
	// -->
	</script>
<?php

/*
    // JavaScript �t���[���̎������T�C�Y�͎g������C�}�C�`�������i�̂Ŏg���Ă��Ȃ��j
    gResizedFrame = false;
    function resizeFrame(){
        var rr = window.parent.fsright;
        if (!gResizedFrame && rr) {
            rr.rows ='*,30%';
            gResizedFrame = true;
            window.parent.read.gResizedFrame = false;
        }
    }
*/

if ($aThreadList->spmode == "taborn" or $aThreadList->spmode == "soko") {
    ?>
	<script language="javascript">
	<!--
	function checkAll(){
		var trk = 0;
		var inp = document.getElementsByTagName('input');
		for (var i=0; i<inp.length; i++){
			var e = inp[i];
			if ((e.name != 'allbox') && (e.type=='checkbox')){
				trk++;
				e.checked = document.getElementById('allbox').checked;
			}
		}
	}
	// -->
	</script>
<?php
}

?>
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="setWinTitle();">
<?php

require P2_LIB_DIR . '/sb_toolbar.inc.php';

P2Util::printInfoHtml();

echo $taborn_check_msg_html;
echo $check_form_ht;
?>
    <table cellspacing="0" width="100%">
<?php


//============================================================================
// �֐��i���̃t�@�C�����݂̂ŗ��p�j
//============================================================================

/**
 * ���ځ[������`�F�b�N�̃��b�Z�[�WHTML���擾����B�X�����ځ[�񒆁A�q�ɁB
 *
 * @return  string  HTML
 */
function _getTabornCheckMsgHtml($aThreadList, $online_num)
{
    $taborn_check_msg_html = '';

    if ($aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko' and $aThreadList->threads) {
        $offline_num = $aThreadList->num - $online_num;
        if ($offline_num > 0) {
            if ($aThreadList->spmode == 'taborn') {
                $taborn_check_msg_html = sprintf(
                    '<p>%s�����A%s���̃X���b�h�����ɔT�[�o�̃X���b�h�ꗗ����O��Ă���悤�ł��i�����Ń`�F�b�N�����܂��j</p>',
                    hs($aThreadList->num), hs($offline_num)
                );
            }
            /*
            elseif ($aThreadList->spmode == 'soko') {
                $taborn_check_msg_html .= sprintf('<p>%s����dat�����X���b�h���ۊǂ���Ă��܂��B</p>', $aThreadList->num);
            }*/
        }
    }
    return $taborn_check_msg_html;
}


/**
 * �`�F�b�N�������ڂ� ���O���폜, ���ځ[������� �t�H�[��HTML�̃w�b�_�������擾����
 *
 * @return  string  HTML
 */
function _getCheckFormHeaderHtml($aThreadList, $deletelog_st, $abornoff_st)
{
    $check_form_ht = '';
    if ($aThreadList->spmode == 'taborn' || $aThreadList->spmode == 'soko' and $aThreadList->threads) {
        $check_form_ht = sprintf(
            '<form class="check" method="POST" action="%s" target="_self">
			<p>�`�F�b�N�������ڂ�
			<input type="submit" name="submit" value="%s"> %s
			</p>',
            hs($_SERVER['SCRIPT_NAME']),
            hs($deletelog_st),
            ($aThreadList->spmode == 'taborn')
                ? sprintf('<input type="submit" name="submit" value="%s">', hs($abornoff_st)) : ''
        );
    }
    return $check_form_ht;
}

/**
 * �y�[�W�^�C�g��������URL���擾����
 *
 * @return  string  URL
 */
function _getPageTitleUrl($aThreadList)
{
    global $_conf;
    
    $ptitle_url = '';

    if ($aThreadList->spmode == 'taborn' or $aThreadList->spmode == 'soko') {
        $ptitle_url = UriUtil::buildQueryUri($_conf['subject_php'], array(
            'host' => $aThreadList->host,
            'bbs'  => $aThreadList->bbs,
            UA::getQueryKey() => UA::getQueryValue()
        ));

    } elseif ($aThreadList->spmode == "res_hist") {
        $ptitle_url = "read_res_hist.php#footer";

    } elseif (!$aThreadList->spmode) {
        $ptitle_url = "http://{$aThreadList->host}/{$aThreadList->bbs}/";
        if (preg_match('/www\\.onpuch\\.jp/', $aThreadList->host)) {
            $ptitle_url = $ptitle_url . 'index2.html';
        }
        if (preg_match('/livesoccer\\.net/', $aThreadList->host)) {
            $ptitle_url = $ptitle_url . 'index2.html';
        }
        // match�o�^���head�Ȃ��ĕ������ق����悳���������A�������X�|���X������̂�����
    }
    
    return $ptitle_url;
}

/**
 * �y�[�W�^�C�g������HTML���擾����
 *
 * @return  string  HTML
 */
function _getPageTitleHtml($aThreadList, $ptitle_url)
{
    $ptitle_ht = '';
    
    $ptitle_url_hs = hs($ptitle_url);
    $ptitle_hs = hs($aThreadList->ptitle);
    
    if ($aThreadList->spmode == 'taborn') {
        $ptitle_ht = <<<EOP
        <span class="itatitle"><a class="aitatitle" href="{$ptitle_url_hs}" target="_self"><b>{$aThreadList->itaj_hs}</b></a>�i���ځ[�񒆁j</span>
EOP;

    } elseif ($aThreadList->spmode == 'soko') {
        $ptitle_ht = <<<EOP
        <span class="itatitle"><a class="aitatitle" href="{$ptitle_url_hs}" target="_self"><b>{$aThreadList->itaj_hs}</b></a>�idat�q�Ɂj</span>
EOP;

    } elseif ($ptitle_url) {
        $ptitle_ht = <<<EOP
        <span class="itatitle"><a class="aitatitle" href="{$ptitle_url_hs}"><b>{$ptitle_hs}</b></a></span>
EOP;

    } else {
        $ptitle_ht = <<<EOP
        <span class="itatitle"><b>{$ptitle_hs}</b></span>
EOP;
    }
    return $ptitle_ht;
}

/**
 * @param   string  $jsstr
 * @return  string
 */
function _addslashesJS($jsstr)
{
    // �s���S��JS�R�[�h��script�^�O�������̂�h������ > ���G�X�P�[�v����K�v������
    return str_replace(array('"', '>'), array('\"', '\>'), $jsstr); 
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
