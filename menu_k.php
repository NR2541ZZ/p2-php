<?php
// p2 -  ���j���[ �g�їp

require_once("./conf.php");  //��{�ݒ�t�@�C���Ǎ�
require_once("./brdctl_class.inc");
require_once("./showbrdmenuk_class.inc");

authorize(); //���[�U�F��

//==============================================================
// �ϐ��ݒ�
//==============================================================
$ktai=1;
$_info_msg_ht="";
$brd_menus = array();

// ���� ====================================
if( isset($_GET['word'])||isset($_POST['word']) ){

	if($_POST['word']){ $word = $_POST['word']; }
	if($_GET['word']){ $word = $_GET['word']; }
	if(get_magic_quotes_gpc()) {
		$word = stripslashes($word);
	}
	if($word=="."){$word="";}
	
	//���K�\������
	include_once("./strctl_class.inc");
	$word_fm = StrCtl::wordForMatch($word);
}


//============================================================
//����ȑO�u����
//============================================================
//���C�ɔ̒ǉ��E�폜
if( isset($_GET['setfavita']) ){
	include("./setfavita.inc");
}

//================================================================
// ���C��
//================================================================
$aShowBrdMenuK = new ShowBrdMenuK;

//============================================================
// �w�b�_
//============================================================
if($_GET['view']=="favita"){
	$ptitle="���C�ɔ�";
}elseif($_GET['view']=="cate"){
	$ptitle="��ؽ�";
}elseif(isset($_GET['cateid'])){
	$ptitle="��ؽ�";
}else{
	$ptitle="��޷��p2";
}

header_content_type();
if($doctype){ echo $doctype;}
echo <<<EOP
<html>
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<title>{$ptitle}</title>
EOP;

echo <<<EOP
</head>
<body>
EOP;

echo $_info_msg_ht;
$_info_msg_ht="";

//==============================================================
// ���C�ɔ��v�����g����
//==============================================================
if($_GET['view']=="favita"){
	$aShowBrdMenuK->print_favIta();

}else{ //����ȊO�Ȃ�brd�ǂݍ���
	//brd�ǂݍ���
	$brd_menus =  BrdCtl::read_brds();		
}

//==============================================================
// �J�e�S����\��
//==============================================================
if($_GET['view']=="cate"){
	echo "��ؽ�<hr>";
	if($brd_menus){
		foreach($brd_menus as $a_brd_menu){
			$aShowBrdMenuK->printCate($a_brd_menu->categories);
		}
	}

}

//==============================================================
// �J�e�S���̔�\��
//==============================================================
if(isset($_GET['cateid'])){
	if($brd_menus){
		foreach($brd_menus as $a_brd_menu){
			$aShowBrdMenuK->printIta($a_brd_menu->categories);
		}
	}
	$modori_url_ht=<<<EOP
<a href="menu_k.php?view=cate&amp;nr=1{$k_at_a}">��ؽ�</a><br>
EOP;
}

//===========================================================
// �������ʂ��v�����g
//===========================================================
if( isset($_GET['word'])||isset($_POST['word']) ){
	if($mikke){
		$hit_ht="<br>\"{$word}\" {$mikke}hit!";
	}
	echo "��ؽČ�������{$hit_ht}<hr>";
	if($word){

		//�����������ăv�����g����==========================
		if($brd_menus){
			foreach($brd_menus as $a_brd_menu){
				$aShowBrdMenuK->printItaSearch($a_brd_menu->categories);
			}
		}
		
	}
	if(!$mikke){
		$_info_msg_ht .=  "<p>\"{$word}\"���܂ޔ͌�����܂���ł����B</p>\n";
		unset($word);
	}
	$modori_url_ht=<<<EOP
<div><a href="menu_k.php?view=cate&amp;nr=1{$k_at_a}">��ؽ�</a></div>
EOP;
}
		
echo $_info_msg_ht;
$_info_msg_ht="";

//==============================================================
// �t�b�^��\��
//==============================================================

//����===============================
if($_GET['view']!="favita" and !$_GET['cateid']){
	$kensaku_form_ht = <<<EOFORM
<form method="GET" action="{$_SERVER['PHP_SELF']}">
	{$k_input_ht}
	<input type="hidden" name="nr" value="1">
	<input type="text" id="word" name="word" value="{$word}" size="12">
	<input type="submit" name="submit" value="����">
</form>\n
EOFORM;
}

echo <<<EOFOOTER
<hr>
$list_navi_ht
$kensaku_form_ht
$modori_url_ht
$k_to_index_ht
</body>
</html>
EOFOOTER;


?>