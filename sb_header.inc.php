<?php
/*
	p2 -  �T�u�W�F�N�g - �w�b�_�\��
	for subject.php
*/

require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X

//===================================================================
// �ϐ�
//===================================================================
$newtime = date("gis");
$reloaded_time = date("m/d G:i:s"); //�X�V����

// �X�����ځ[��`�F�b�N�A�q�� =============================================
if ($aThreadList->spmode == "taborn" || $aThreadList->spmode == "soko" and $aThreadList->threads) {
	$offline_num = $aThreadList->num - $online_num;
	$taborn_check_ht = <<<EOP
	<form class="check" method="POST" action="{$_SERVER['PHP_SELF']}" target="_self">\n
EOP;
	if ($offline_num > 0) {
		if ($aThreadList->spmode == "taborn") {
			$taborn_check_ht .= <<<EOP
		<p>{$aThreadList->num}�����A{$offline_num}���̃X���b�h�����ɔT�[�o�̃X���b�h�ꗗ����O��Ă���悤�ł��i�����Ń`�F�b�N�����܂��j</p>\n
EOP;
		}
		/*
		elseif ($aThreadList->spmode == "soko") {
			$taborn_check_ht .= <<<EOP
		<p>{$aThreadList->num}����dat�����X���b�h���ۊǂ���Ă��܂��B</p>\n
EOP;
		}*/
	}
}

//===============================================================
// HTML�\���p�ϐ� for �c�[���o�[(sb_toolbar.inc.php) 
//===============================================================

$norefresh_q = "&amp;norefresh=true";

// �y�[�W�^�C�g������URL�ݒ� ====================================
if ($aThreadList->spmode == "taborn" or $aThreadList->spmode == "soko") {
	$ptitle_url = "{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}";
} elseif ($aThreadList->spmode == "res_hist") {
	$ptitle_url = "./read_res_hist.php#footer";
} elseif (!$aThreadList->spmode) {
	$ptitle_url = "http://{$aThreadList->host}/{$aThreadList->bbs}/";
	if (preg_match("/www\.onpuch\.jp/", $aThreadList->host)) {$ptitle_url = $ptitle_url."index2.html";}
	if (preg_match("/livesoccer\.net/", $aThreadList->host)) {$ptitle_url = $ptitle_url."index2.html";}
	// match�o�^���head�Ȃ��ĕ������ق����悳���������A�������X�|���X������̂�����
}

// �y�[�W�^�C�g������HTML�ݒ� ====================================
$ptitle_hd = htmlspecialchars($aThreadList->ptitle);

if ($aThreadList->spmode == "taborn") {
	$ptitle_ht = <<<EOP
	<span class="itatitle"><a class="aitatitle" href="{$ptitle_url}" target="_self"><b>{$aThreadList->itaj_hd}</b></a>�i���ځ[�񒆁j</span>
EOP;
} elseif ($aThreadList->spmode == "soko") {
	$ptitle_ht = <<<EOP
	<span class="itatitle"><a class="aitatitle" href="{$ptitle_url}" target="_self"><b>{$aThreadList->itaj_hd}</b></a>�idat�q�Ɂj</span>
EOP;
} elseif ($ptitle_url) {
	$ptitle_ht = <<<EOP
	<span class="itatitle"><a class="aitatitle" href="{$ptitle_url}"><b>{$ptitle_hd}</b></a></span>
EOP;
} else {
	$ptitle_ht = <<<EOP
	<span class="itatitle"><b>{$ptitle_hd}</b></span>
EOP;
}

// �r���[�����ݒ� ==============================================
if ($aThreadList->spmode) { // �X�y�V�������[�h��
	if($aThreadList->spmode=="fav" or $aThreadList->spmode=="palace"){	// ���C�ɃX�� or �a���Ȃ�
		if($sb_view=="edit"){
			$edit_ht="<a class=\"narabi\" href=\"{$_conf['subject_php']}?spmode={$aThreadList->spmode}{$norefresh_q}\" target=\"_self\">����</a>";
		}else{
			$edit_ht="<a class=\"narabi\" href=\"{$_conf['subject_php']}?spmode={$aThreadList->spmode}&amp;sb_view=edit{$norefresh_q}\" target=\"_self\">����</a>";

		}
	}
}

// �t�H�[��hidden ==================================================
$sb_form_hidden_ht = <<<EOP
	<input type="hidden" name="detect_hint" value="����">
	<input type="hidden" name="bbs" value="{$aThreadList->bbs}">
	<input type="hidden" name="host" value="{$aThreadList->host}">
	<input type="hidden" name="spmode" value="{$aThreadList->spmode}">
EOP;

//�\������ ==================================================
if(!$aThreadList->spmode || $aThreadList->spmode=="news"){

	if($p2_setting['viewnum']=="100"){$vncheck_100=" selected";}
	elseif($p2_setting['viewnum']=="150"){$vncheck_150=" selected";}
	elseif($p2_setting['viewnum']=="200"){$vncheck_200=" selected";}
	elseif($p2_setting['viewnum']=="250"){$vncheck_250=" selected";}
	elseif($p2_setting['viewnum']=="300"){$vncheck_300=" selected";}
	elseif($p2_setting['viewnum']=="400"){$vncheck_400=" selected";}
	elseif($p2_setting['viewnum']=="500"){$vncheck_500=" selected";}
	elseif($p2_setting['viewnum']=="all"){$vncheck_all=" selected";}
	else{$p2_setting['viewnum']="150"; $vncheck_150=" selected";} //��{�ݒ�
	
	$sb_disp_num_ht =<<<EOP
		<select name="viewnum">
			<option value="100"{$vncheck_100}>100��</option>
			<option value="150"{$vncheck_150}>150��</option>
			<option value="200"{$vncheck_200}>200��</option>
			<option value="250"{$vncheck_250}>250��</option>
			<option value="300"{$vncheck_300}>300��</option>
			<option value="400"{$vncheck_400}>400��</option>
			<option value="500"{$vncheck_500}>500��</option>
			<option value="all"{$vncheck_all}>�S��</option>
		</select>
EOP;
}

// �t�B���^���� ==================================================
if ($_conf['enable_exfilter'] == 2) {

	$filter_method_checked = array(' checked', '', '');
	if ($sb_filter_method == 'or') {
		$filter_method_checked[0] = '';
		$filter_method_checked[1] = ' checked';
	} elseif ($sb_filter_method == 'reg') {
		$filter_method_checked[0] = '';
		$filter_method_checked[2] = ' checked';
	}
	$sb_form_method_ht =<<<EOP

			<input type="radio" name="method" value="and"{$filter_method_checked[0]}>AND
			<input type="radio" name="method" value="or"{$filter_method_checked[1]}>OR
			<input type="radio" name="method" value="reg"{$filter_method_checked[2]}>���K�\��
EOP;
}

$word = htmlspecialchars($word);
$filter_form_ht = <<<EOP
		<form class="toolbar" method="GET" action="subject.php" accept-charset="{$_conf['accept_charset']}" target="_self">
			{$sb_form_hidden_ht}
			<input type="text" id="word" name="word" value="{$word}" size="16">
			{$sb_form_method_ht}
			<input type="submit" name="submit_kensaku" value="����">
		</form>
EOP;



// �`�F�b�N�t�H�[�� =====================================
if ($aThreadList->spmode == "taborn") {
	$abornoff_ht = <<<EOP
	<input type="submit" name="submit" value="{$abornoff_st}">
EOP;
}
if ($aThreadList->spmode == "taborn" || $aThreadList->spmode == "soko" and $aThreadList->threads) {
	$check_form_ht = <<<EOP
	<p>
		�`�F�b�N�������ڂ�
		<input type="submit" name="submit" value="{$deletelog_st}">
		$abornoff_ht
	</p>
EOP;
}

//===================================================================
// HTML�v�����g
//===================================================================

P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html lang="ja">
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">\n
EOP;

if ($_conf['refresh_time']) {
	$refresh_time_s = $_conf['refresh_time'] * 60;
	$refresh_url = "{$_conf['subject_php']}?host={$aThreadList->host}&amp;bbs={$aThreadList->bbs}&amp;spmode={$aThreadList->spmode}";
	echo <<<EOP
	<meta http-equiv="refresh" content="{$refresh_time_s};URL={$refresh_url}">
EOP;
}

echo <<<EOP
	<title>{$ptitle_hd}</title>
	<base target="read">
EOP;

@include("./style/style_css.inc"); //��{�X�^�C���V�[�g�Ǎ�
@include("./style/subject_css.inc"); //subject�p�X�^�C���V�[�g�Ǎ�

echo <<<EOJS
	<script type="text/javascript" src="js/basic.js"></script>
	<script language="JavaScript">
	<!--
	function setWinTitle(){
		var shinchaku_ari = "$shinchaku_attayo";
		if(shinchaku_ari){
			window.top.document.title="��{$aThreadList->ptitle}";
		}else{
			if (top != self) {top.document.title=self.document.title;}
		}
	}

	function chNewAllColor()
	{
		var smynum1 = document.getElementById('smynum1');
		if (smynum1) {
			smynum1.style.color="{$STYLE['sb_ttcolor']}";
		}
		var smynum2 = document.getElementById('smynum2')
		if (smynum2) {
			smynum2.style.color="{$STYLE['sb_ttcolor']}";
		}
		var a = document.getElementsByTagName('a');
		for (var i = 0; i < a.length; i++) {
			if (a[i].className == 'un_a') {
				a[i].style.color = "{$STYLE['sb_ttcolor']}";
			}
		}
	}
	function chUnColor(idnum){
		var unid = 'un'+idnum;
		var unid_obj = document.getElementById(unid);
		if (unid_obj) {
			unid_obj.style.color="{$STYLE['sb_ttcolor']}";
		}
	}
	function chTtColor(idnum){
		var ttid = "tt"+idnum;
		var toid = "to"+idnum;
		var ttid_obj = document.getElementById(ttid);
		if (ttid_obj) {
			ttid_obj.style.color="{$STYLE['thre_title_color_v']}";
		}
		var toid_obj = document.getElementById(toid);
		if (toid_obj) {
			toid_obj.style.color="{$STYLE['thre_title_color_v']}";
		}
	}
	// -->
	</script>
EOJS;

if ($aThreadList->spmode == "taborn" or $aThreadList->spmode == "soko") {
	echo <<<EOJS
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
EOJS;
}

echo <<<EOP
</head>
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" onLoad="setWinTitle();">
EOP;

include './sb_toolbar.inc.php';

echo $_info_msg_ht;
$_info_msg_ht = "";

echo <<<EOP
	$taborn_check_ht
	$check_form_ht
	<table cellspacing="0" width="100%">\n
EOP;

?>
