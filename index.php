<?php
// p2 -  �C���f�b�N�X�y�[�W

include_once './conf.inc.php';  // ��{�ݒ�t�@�C���Ǎ�
require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X

authorize(); //���[�U�F��

$_info_msg_ht = "";

// �A�N�Z�X���O���L�^
if ($_conf['login_log_rec']) {
	if (isset($_conf['login_log_rec_num'])) {
		P2Util::recAccessLog($_conf['login_log_file'], $_conf['login_log_rec_num']);
	} else {
		P2Util::recAccessLog($_conf['login_log_file']);
	}
}

$s = $_SERVER['HTTPS'] ? 's' : '';
$me_url = "http{$s}://".$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
$me_dir_url = dirname($me_url);

if ($_conf['ktai']) {

	//=========================================================
	// �g�їp �C���f�b�N�X
	//=========================================================
	// url�w�肪����΁A���̂܂܃X���b�h�ǂ݂֔�΂�
	if (!empty($_GET['url']) || !empty($_GET['nama_url'])) {
		header('Location: '.$me_dir_url.'/read.php?'.$_SERVER['QUERY_STRING']);
		exit;
	}
	include("./index_print_k.inc");
	index_print_k();
	
} else {
	//=========================================
	// PC�p �ϐ�
	//=========================================
	$title_page = "title.php";
	
	if (!empty($_GET['url']) || !empty($_GET['nama_url'])) {
		$htm['read_page'] = "read.php?".$_SERVER['QUERY_STRING'];
	} else {
		if (!empty($_conf['first_page'])) {
			$htm['read_page'] = $_conf['first_page'];
		} else {
			$htm['read_page'] = 'first_cont.php';
		}
	}
	
	$sidebar = $_GET['sidebar'];
	
	$ptitle = " p2";
	//======================================================
	// PC�p HTML�v�����g
	//======================================================
	P2Util::header_nocache();
	P2Util::header_content_type();
	if ($_conf['doctype']) { echo $_conf['doctype']; }
	echo <<<EOHEADER
<html lang="ja">
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>{$ptitle}</title>
</head>
EOHEADER;

	if(!$sidebar){
		echo <<<EOMENUFRAME
<frameset cols="156,*" frameborder="1" border="1">
	<frame src="menu.php" name="menu" scrolling="auto">
EOMENUFRAME;
	}
	
	echo <<<EOMAINFRAME
	<frameset rows="40%,60%" frameborder="1" border="2">
		<frame src="{$title_page}" name="subject" scrolling="auto">
		<frame src="{$htm['read_page']}" name="read" scrolling="auto">
	</frameset>
EOMAINFRAME;

	if(!$sidebar){
		echo <<<EOMENUFRAME
</frameset>
EOMENUFRAME;
	}
	
	echo <<<EOFOOTER
</html>
EOFOOTER;

}

?>