<?php
/*
	p2 -  ���j���[�iMozilla�T�C�h�o�[�p�j
	�t���[��������ʁA��������
*/


require_once("./conf.php");  //�ݒ�Ǎ�
require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X
require_once("./datactl.inc");
require_once("./brdctl_class.inc");
require_once("./showbrdmenupc_class.inc");

authorize(); //���[�U�F��

//================================================================
// �ϐ��ݒ�
//================================================================
if($_SERVER['HTTPS']){ 
	$me_url = "https://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; 
}else{ 
	$me_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME']; 
} 
$me_dir_url = dirname($me_url);
$menu_side_url = $me_dir_url."/menu_side.php"; //menu_side.php �� URL�B�i���[�J���p�X�w��͂ł��Ȃ��悤���j

$_info_msg_ht="";
$brd_menus = array();

// ���� ====================================
if( isset($_GET['word'])||isset($_POST['word']) ){

	if($_POST['word']){ $word = $_POST['word']; }
	if($_GET['word']){ $word = $_GET['word']; }
	if(get_magic_quotes_gpc()) {
		$word = stripslashes($word);
	}
	if (preg_match('/^\.+$/', $word)) {
		$word = '';
	}
	
	// ���K�\������
	include_once './strctl.class.php';
	$word_fm = StrCtl::wordForMatch($word);
}


//================================================================
//����ȑO�u����
//================================================================
//���C�ɔ̒ǉ��E�폜
if( isset($_GET['setfavita']) ){
	include("./setfavita.inc");
}

//================================================================
// ���C��
//================================================================
$aShowBrdMenuPc = new ShowBrdMenuPc;

//==============================================================
// �w�b�_
//==============================================================
$reloaded_time = date("n/j G:i:s"); //�X�V����
$ptitle="p2 - menu";

header_content_type();
if($doctype){ echo $doctype;}
echo <<<EOP
<html>
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">\n
EOP;


if($c_menu_refresh_time){	// �����X�V
	$refresh_time_s = $c_menu_refresh_time * 60;
	echo <<<EOP
	<meta http-equiv="refresh" content="{$refresh_time_s};URL={$me_url}?new=1">
EOP;
}

echo <<<EOP
	<title>{$ptitle}</title>
	<base target="subject">
EOP;

@include("./style/style_css.inc");
@include("./style/menu_css.inc");

echo <<<EOSCRIPT
	<script type="text/javascript" src="js/showhide.js"></script>
	<script language="JavaScript">
	<!--
	function chUnColor(idnum){
		unid='un'+idnum;
		document.getElementById(unid).style.color="{$STYLE['menu_color']}";
	}
	
	function chMenuColor(idnum){
		newthreid='newthre'+idnum;
		if(document.getElementById(newthreid)){document.getElementById(newthreid).style.color="{$STYLE['menu_color']}";}
		unid='un'+idnum;
		document.getElementById(unid).style.color="{$STYLE['menu_color']}";
	}
	// -->
	</script>\n
EOSCRIPT;
echo <<<EOP
</head>
<body>
EOP;

echo $_info_msg_ht;
$_info_msg_ht="";

echo <<<EOP
<p><a href="index.php?sidebar=true" target="_content">p2 - 2�y�C���\��</a></p>\n
EOP;


if($c_enable_menu_new){
	echo <<<EOP
$reloaded_time [<a href="{$_SERVER['PHP_SELF']}?new=1" target="_self">�X�V</a>]
EOP;
}

//==============================================================
// ���C�ɔ��v�����g����
//==============================================================
$aShowBrdMenuPc->print_favIta();

//==============================================================
// ����
//==============================================================
$bbs_table_url = "http://www6.ocn.ne.jp/~mirv/bbstable.html";
$bbs_table_url_r = P2Util::throughIme($bbs_table_url);
$norefresh_q = "&amp;norefresh=true";

echo <<<EOP
<div class="menu_cate"><b><a class="menu_cate" href="javascript:void(0);" onClick="showHide('c_spacial');">����</a></b><br>
	<div class="itas" id="c_spacial">
EOP;

if($c_enable_menu_new==1 and  $_GET['new']){	// �V������\������ꍇ

	initMenuNew("fav");	// �V������������
	echo <<<EOP
	�@<a href="{$subject_php}?spmode=fav{$norefresh_q}" onClick="chMenuColor({$matome_i});" accesskey="f">���C�ɃX��</a> (<a href="{$_conf['read_new_php']}?spmode=fav" target="read" id="un{$matome_i}" onClick="chUnColor({$matome_i});"{$class_newres_num}>{$shinchaku_num}</a>)<br>
EOP;

	initMenuNew("recent");	// �V������������
	echo <<<EOP
	�@<a href="{$subject_php}?spmode=recent{$norefresh_q}" onClick="chMenuColor({$matome_i});" accesskey="h">�ŋߓǂ񂾃X��</a> (<a href="{$_conf['read_new_php']}?spmode=recent" target="read" id="un{$matome_i}" onClick="chUnColor({$matome_i});"{$class_newres_num}>{$shinchaku_num}</a>)<br>
EOP;

	initMenuNew("res_hist");	// �V������������
	echo <<<EOP
	�@<a href="{$subject_php}?spmode=res_hist{$norefresh_q}" onClick="chMenuColor({$matome_i});">�������ݗ���</a> <a href="read_res_hist.php#footer" target="read">��</a> (<a href="{$_conf['read_new_php']}?spmode=res_hist" target="read" id="un{$matome_i}" onClick="chUnColor({$matome_i});"{$class_newres_num}>{$shinchaku_num}</a>)<br>
EOP;

}else{	// �V������\�����Ȃ��ꍇ
	echo <<<EOP
	�@<a href="{$subject_php}?spmode=fav{$norefresh_q}" accesskey="f">���C�ɃX��</a><br>
	�@<a href="{$subject_php}?spmode=recent{$norefresh_q}" accesskey="h">�ŋߓǂ񂾃X��</a><br>
	�@<a href="{$subject_php}?spmode=res_hist{$norefresh_q}">�������ݗ���</a> (<a href="./read_res_hist.php#footer" target="read">��</a>)<br>
EOP;
}

echo <<<EOP
	�@<a href="{$subject_php}?spmode=palace{$norefresh_q}">�X���̓a��</a><br>
	<!--�@<a href="{$subject_php}?spmode=news">�j���[�X�`�F�b�N</a><br>-->
	�@<a href="setting.php">�ݒ�</a><!--<br>
	�@<a href="{$bbs_table_url_t}">BBS TABLE</a>-->
	</div>
</div>\n
EOP;

//==============================================================
// �J�e�S���Ɣ�\��
//==============================================================
//brd�ǂݍ���
$brd_menus =  BrdCtl::read_brds();

//===========================================================
// �v�����g
//===========================================================
if($word!=""){
	if(!$mikke){
		$_info_msg_ht .=  "<p>\"{$word}\"���܂ޔ͌�����܂���ł����B</p>\n";
		unset($word);
	}else{
		$_info_msg_ht .=  "<p>\"{$word}\"���܂ޔ� {$mikke}hit!</p>\n";
	}
}
		
echo $_info_msg_ht;
$_info_msg_ht = "";

// ����
echo <<<EOFORM
<form method="GET" action="{$_SERVER['PHP_SELF']}" target="_self">
	<p>
		<input type="text" id="word" name="word" value="{$word}" size="14">
		<input type="submit" name="submit" value="����">
	</p>
</form>\n
EOFORM;

// �J�e�S�����j���[��\��
if ($brd_menus) {
	foreach ($brd_menus as $a_brd_menu) {
		$aShowBrdMenuPc->printBrdMenu($a_brd_menu->categories);
	}
}

//==============================================================
// �t�b�^��\��
//==============================================================
echo <<<EOFOOTER
</body>
</html>
EOFOOTER;

//==============================================================
// �֐�
//==============================================================
// menu�̐V����������������֐�
function initMenuNew($spmode_in)
{
	global $shinchaku_num, $matome_i, $host, $bbs, $spmode, $STYLE, $class_newres_num;
	$matome_i++;
	$host = "";
	$bbs = "";
	$spmode = $spmode_in;
	include("./subject_new.php");
	if($shinchaku_num>0){
		$class_newres_num = " class=\"newres_num\"";
	}else{
		$class_newres_num = " class=\"newres_num_zero\"";
	}
}

?>