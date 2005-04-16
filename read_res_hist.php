<?php
// p2 - �������ݗ��� ���X���e�\��
// �t���[��������ʁA�E������

include_once './conf/conf.inc.php'; // ��{�ݒ�Ǎ�
require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X
require_once './dataphp.class.php';
require_once './res_hist.class.php';
require_once './read_res_hist.inc.php';

$debug = 0;
$debug && include_once("./profiler.inc"); //
$debug && $prof =& new Profiler(true); //

authorize(); // ���[�U�F��

//======================================================================
// �ϐ�
//======================================================================
$newtime = date('gis');
$p2_res_hist_dat_php = $_conf['pref_dir'].'/p2_res_hist.dat.php';

$_info_msg_ht = '';
$deletemsg_st = '�폜';
$ptitle = '�������񂾃��X�̋L�^';

//================================================================
// ����ȑO�u����
//================================================================
// �폜
if ($_POST['submit'] == $deletemsg_st) {
	deleMsg($_POST['checked_hists']);
}

// ���`���̏������ݗ�����V�`���ɕϊ�����
P2Util::transResHistLog();

//======================================================================
// ���C��
//======================================================================

//==================================================================
// ����DAT�ǂ�
//==================================================================
// �ǂݍ����
if (!$datlines = DataPhp::fileDataPhp($p2_res_hist_dat_php)) {
	die("p2 - �������ݗ�����e�͋���ۂ̂悤�ł�");
}

$datlines = array_map('rtrim', $datlines);

// �t�@�C���̉��ɋL�^����Ă�����̂��V����
$datlines = array_reverse($datlines);

$aResHist =& new ResHist();

$n = 1;
if ($datlines) {
	foreach ($datlines as $aline) {

		$aResArticle =& new ResArticle();
		
		$resar = explode("\t", $aline);
		$aResArticle->name = $resar[0];
		$aResArticle->mail = $resar[1];
		$aResArticle->daytime = $resar[2];
		$aResArticle->msg = $resar[3];
		$aResArticle->ttitle = $resar[4];
		$aResArticle->host = $resar[5];
		$aResArticle->bbs = $resar[6];
		$aResArticle->itaj = P2Util::getItaName($aResArticle->host, $aResArticle->bbs);
		if (!$aResArticle->itaj) {
			$aResArticle->itaj = $aResArticle->bbs;
		}
		$aResArticle->key = trim($resar[7]);
		$aResArticle->order = $n;
		
		$aResHist->addRes($aResArticle);
		
		$n++;
	}
}

// HTML�v�����g�p�ϐ�
$htm['checkall'] = '�S�Ẵ`�F�b�N�{�b�N�X�� 
<input type="button" onclick="hist_checkAll(true)" value="�I��"> 
<input type="button" onclick="hist_checkAll(false)" value="����">';

$htm['toolbar'] = <<<EOP
			�`�F�b�N�������ڂ�<input type="submit" name="submit" value="{$deletemsg_st}">
			�@{$htm['checkall']}
EOP;

//==================================================================
// �w�b�_ �\��
//==================================================================
P2Util::header_content_type();
if (isset($_conf['doctype'])) {
	echo $_conf['doctype'];
}
echo <<<EOP
<html lang="ja">
<head>
	{$_conf['meta_charset_ht']}
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>{$ptitle}</title>
EOP;

// PC�p�\��
if (!$_conf['ktai']) {
	@include("style/style_css.inc"); // �X�^�C���V�[�g
	@include("style/read_css.inc"); // �X�^�C���V�[�g

	echo <<<EOSCRIPT
	<script type="text/javascript" src="js/basic.js"></script>
	<script type="text/javascript" src="js/respopup.js"></script>
	
	<script type="text/javascript"> 
	function hist_checkAll(mode) { 
		if (!document.getElementsByName) { 
			return; 
		} 
		var checkboxes = document.getElementsByName('checked_hists[]'); 
		var cbnum = checkboxes.length; 
		for (var i = 0; i < cbnum; i++) { 
			checkboxes[i].checked = mode; 
		} 
	} 
	</script> 
EOSCRIPT;
}

echo <<<EOP
</head>
<body onLoad="gIsPageLoaded = true;">\n
EOP;

echo $_info_msg_ht;
$_info_msg_ht = "";

// �g�їp�\��
if ($_conf['ktai']) {
	echo "{$ptitle}<br>";
	echo '<div id="header" name="header">';
	$aResHist->showNaviK("header");
	echo " <a {$_conf['accesskey']}=\"8\" href=\"#footer\"{$_conf['k_at_a']}>8.��</a><br>";
	echo "</div>";
	echo "<hr>";

// PC�p�\��
} else {
	echo <<<EOP
<form method="POST" action="./read_res_hist.php" target="_self" onSubmit="if(gIsPageLoaded){return true;}else{alert('�܂��y�[�W��ǂݍ��ݒ��Ȃ�ł��B����������Ƒ҂��āB');return false;}">
EOP;

	echo <<<EOP
<table id="header" width="100%" style="padding:0px 10px 0px 0px;">
	<tr>
		<td>
			<h3 class="thread_title">{$ptitle}</h3>
		</td>
		<td align="right">{$htm['toolbar']}</td>
		<td align="right" style="padding-left:12px;"><a href="#footer">��</a></td>
	</tr>
</table>\n
EOP;
}


//==================================================================
// ���X�L�� �\��
//==================================================================
if ($_conf['ktai']) {
	$aResHist->showArticlesK();
} else {
	$aResHist->showArticles();
}

//==================================================================
// �t�b�^ �\��
//==================================================================
// �g�їp�\��
if ($_conf['ktai']) {
	echo '<div id="footer" name="footer">';
	$aResHist->showNaviK("footer");
	echo " <a {$_conf['accesskey']}=\"2\" href=\"#header\"{$_conf['k_at_a']}>2.��</a><br>";
	echo "</div>";
	echo "<p>{$_conf['k_to_index_ht']}</p>";

// PC�p�\��
} else {
	echo "<hr>";
	echo <<<EOP
<table id="footer" width="100%" style="padding:0px 10px 0px 0px;">
	<tr>
		<td align="right">{$htm['toolbar']}</td>
		<td align="right" style="padding-left:12px;"><a href="#header">��</a></td>
	</tr>
</table>\n
EOP;
}

$debug && $prof->printTimers(true); //

if (!$_conf['ktai']) {
	echo '</form>'."\n";
}

echo '</body></html>';

?>