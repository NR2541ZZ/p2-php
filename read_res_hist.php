<?php
// p2 - �������ݗ��� ���X���e�\��
// �t���[��������ʁA�E������

require_once("./conf.php"); //��{�ݒ�Ǎ�
require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X
require_once("datactl.inc");
require_once("res_hist_class.inc");
require_once("read_res_hist.inc");

//$debug=true;
$debug && include_once("./profiler.inc"); //
$debug && $prof = new Profiler( true ); //

authorize(); //���[�U�F��

//======================================================================
// �ϐ�
//======================================================================
$newtime = date("gis");
$p2_res_hist_dat_php = $prefdir."/p2_res_hist.dat.php";

$_info_msg_ht = "";
$deletemsg_st = "�폜";
$ptitle = "�������񂾃��X�̋L�^";

//================================================================
//����ȑO�u����
//================================================================
//�폜
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
if (!$datlines = P2Util::fileDataPhp($p2_res_hist_dat_php)) {
	die("p2 - �������ݗ�����e�͋���ۂ̂悤�ł�");
}

$aResHist = new ResHist;

$n = 1;
if ($datlines) {
	foreach ($datlines as $aline) {

		// &<>/ �� &xxx; �̃G�X�P�[�v�����ɖ߂�
		$aline = P2Util::unescapeDataPhp($aline);

		$aResArticle = new ResArticle;
		
		$resar = explode("\t", $aline);
		$aResArticle->name = $resar[0];
		$aResArticle->mail = $resar[1];
		$aResArticle->daytime = $resar[2];
		$aResArticle->msg = $resar[3];
		$aResArticle->ttitle = $resar[4];
		$aResArticle->host = $resar[5];
		$aResArticle->bbs = $resar[6];
		$aResArticle->itaj = getItaName($aResArticle->host, $aResArticle->bbs);
		if (!$aResArticle->itaj) {$aResArticle->itaj = $aResArticle->bbs;}
		$aResArticle->key = trim($resar[7]);
		$aResArticle->order = $n;
		
		$aResHist->addRes($aResArticle);
		
		$n++;
	}
}

//==================================================================
// �w�b�_ �\��
//==================================================================
header_content_type();
if($doctype){ echo $doctype;}
echo <<<EOP
<html>
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<title>{$ptitle}</title>
EOP;

if(!$_conf['ktai']){
	@include("style/style_css.inc"); //�X�^�C���V�[�g
	@include("style/read_css.inc"); //�X�^�C���V�[�g

	echo <<<EOSCRIPT
	<script type="text/javascript" src="{$basic_js}"></script>
	<script type="text/javascript" src="{$respopup_js}"></script>
EOSCRIPT;
}

echo <<<EOP
</head>
<body>
EOP;

echo $_info_msg_ht;
$_info_msg_ht="";

if($_conf['ktai']){
	echo "{$ptitle}<br>";
	echo "<div {$pointer_at}=\"header\">";
	$aResHist->showNaviK("header");
	echo " <a {$accesskey}=\"8\" href=\"#footer\"{$k_at_a}>8.��</a><br>";
	echo "</div>";
	echo "<hr>";
	
}else{
	echo <<<EOP
<form method="POST" action="./read_res_hist.php#footer" target="_self">
EOP;

	echo <<<EOP
<table id="header" width="100%" style="padding:0px 10px 0px 0px;">
	<tr>
		<td align="left">
			&nbsp;
		</td>
		<td align="right"><a href="#footer">��</a></td>
	</tr>
</table>\n
EOP;

	echo <<<EOP
<table id="header" width="100%" style="padding:0px 10px 0px 0px;">
	<tr>
		<td align="left">
			<h3 class="thread_title">{$ptitle}</h3>
		</td>
		<td align="right">&nbsp;
		</td>
	</tr>
</table>\n
EOP;
}


//==================================================================
// ���X�L�� �\��
//==================================================================
if($_conf['ktai']){
	$aResHist->showArticlesK();
}else{
	$aResHist->showArticles();
}

//==================================================================
// �t�b�^ �\��
//==================================================================
if($_conf['ktai']){
	echo "<div {$pointer_at}=\"footer\">";
	$aResHist->showNaviK("footer");
	echo " <a {$accesskey}=\"2\" href=\"#header\"{$k_at_a}>2.��</a><br>";
	echo "</div>";
	echo "<p>{$k_to_index_ht}</p>";
}else{
	echo "<hr>";
	echo <<<EOP
<table id="footer" width="100%" style="padding:0px 10px 0px 0px;">
	<tr>
		<td align="left">
			�`�F�b�N�������ڂ�<input type="submit" name="submit" value="{$deletemsg_st}">
		</td>
		<td align="right"><a href="#header">��</a></td>
	</tr>
</table>\n
EOP;
}

$debug && $prof->printTimers( true );//

if(!$_conf['ktai']){
	echo <<<EOP
	</form>
EOP;
}

echo <<<EOFOOTER
</body>
</html>
EOFOOTER;

?>