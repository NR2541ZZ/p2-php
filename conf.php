<?php
// p2 - ��{�ݒ�t�@�C���i���ɗ��R�̖�������ύX���Ȃ����Ɓj

$_conf['p2version'] = "1.1.6";

$_conf['p2name'] = "p2";	// p2�̖��O�B

// $_conf['p2name'] = "P2";	// p2�̖��O�B


//======================================================================
// ��{�ݒ菈��
//======================================================================
error_reporting(E_ALL ^ E_NOTICE); 

require_once './p2util.class.php';

putenv("TZ=JST-9"); //�^�C���]�[�����Z�b�g

//PHP4.1.0�����ƌ݊��ێ�
if (phpversion()<"4.1.0") {
    $_GET = $HTTP_GET_VARS;
    $_POST = $HTTP_POST_VARS;
    $_SESSION = $HTTP_SESSION_VARS;
}

//���������ɂ����镶���R�[�h�w��
if (extension_loaded('mbstring')) {
	//mb_detect_order("SJIS,EUC-JP,ASCII");
	mb_http_output('SJIS');
	mb_internal_encoding('SJIS');
	//ob_start('mb_output_handler');
}

//UA����===========================================
$ua = $_SERVER['HTTP_USER_AGENT'];	//���̕ϐ��i$ua�j�͔p�~�\��B$_SERVER['HTTP_USER_AGENT']�𒼐ڗ��p����B
if (P2Util::isBrowserSafariGroup()) {
	$_conf['accept_charset'] = 'UTF-8';
} else {
	$_conf['accept_charset'] = 'Shift_JIS';
}
if (isset($_GET['k']) ||  isset($_POST['k'])) {
	$ktai = 1;
	$k_at_a = "&amp;k=1";
	$k_at_q = "?k=1";
	$k_input_ht = '<input type="hidden" name="k" value="1">';
}
//$ktai = 1;//
$doctype = "";
$accesskey = "accesskey";
$pointer_at = "id";
$k_accesskey['prev'] = "4";
$k_accesskey['next'] = "6";
$k_accesskey['latest'] = "9";
$k_accesskey['matome'] = "3";
$k_accesskey['above'] = "5";
$meta_charset_ht = <<<EOP
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
EOP;

//�g��===================================
if (strstr($ua, "UP.Browser/")) {
	$browser = "EZweb";
	$ktai = true;
	/*
	$doctype=<<<EOP
<?xml version="1.0" encoding="Shift_JIS"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN"
"http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">
EOP;
	*/

}elseif(strstr($ua, "DoCoMo/")){
	$browser="DoCoMo";
	$ktai=true;
	$pointer_at="name";

}elseif(strstr($ua, "J-PHONE/")){
	$browser="JPHONE";
	$ktai=true;
	$accesskey="DIRECTKEY";
	$pointer_at="name";

}elseif(strstr($ua, "DDIPOCKET")){
	$browser="DDIPOCKET";
	$ktai=true;
	$pointer_at="name";

//PC =====================================
}elseif(strstr($ua, "MSIE")){
	$browser="IE";
}elseif(strstr($ua, "Safari")){
	$browser="Safari";
}

$k_to_index_ht = <<<EOP
<a {$accesskey}="0" href="index.php{$k_at_q}">0.TOP</a>
EOP;

//DOCTYPE HTML �錾==========================
$ie_strict = false;
if(!$ktai){
	if($ie_strict){
		$doctype=<<<EODOC
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
		"http://www.w3.org/TR/html4/loose.dtd">\n
EODOC;
	}else{
		$doctype=<<<EODOC
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">\n
EODOC;
	}
}

//======================================================================

if(file_exists("./conf_user.php")){
	include_once("./conf_user.php"); // ���[�U�ݒ� �Ǎ�
}
if(file_exists("./conf_style.inc")){
	include_once("./conf_style.inc"); // �f�U�C���ݒ� �Ǎ�
}

$_conf['display_threads_num'] = 150; // (150) �X���b�h�T�u�W�F�N�g�ꗗ�̃f�t�H���g�\����
$posted_rec_num = 1000; // (1000) �������񂾃��X�̍ő�L�^�� //���݂͋@�\���Ă��Ȃ�


/* �f�t�H���g�ݒ� */
if (!isset($login['use'])) { $login['use'] = 1; }
if (!is_dir($prefdir)) { $prefdir = "./data"; }
if (!is_dir($datdir)) { $datdir = "./data"; }
if (!isset($rct_rec_num)) { $rct_rec_num = 20; }
if (!isset($res_hist_rec_num)) { $res_hist_rec_num = 20; }
if (!isset($posted_rec_num)) { $posted_rec_num = 1000; }
if (!isset($before_respointer)) { $before_respointer = 20; }
if (!isset($sort_zero_adjust)) { $sort_zero_adjust = 0.1; }
if (!isset($_conf['display_threads_num'])) { $_conf['display_threads_num'] = 150; }
if (!isset($cmp_dayres_midoku)) { $cmp_dayres_midoku = 1; }
if (!isset($k_sb_disp_range)) { $k_sb_disp_range = 30; }
if (!isset($k_rnum_range)) { $k_rnum_range = 10; }
if (!isset($pre_thumb_height)) { $pre_thumb_height = "32"; }
if (!isset($quote_res_view)) { $quote_res_view = 1; }
if (!isset($res_write_rec)) { $res_write_rec = 1; }

if (!isset($STYLE['post_pop_size'])) { $STYLE['post_pop_size'] = "610,350"; }
if (!isset($STYLE['post_msg_rows'])) { $STYLE['post_msg_rows'] = 10; }
if (!isset($STYLE['post_msg_cols'])) { $STYLE['post_msg_cols'] = 70; }
if (!isset($STYLE['info_pop_size'])) { $STYLE['info_pop_size'] = "600,380"; }

/* ���[�U�ݒ�̒������� */
$ext_win_target && $ext_win_target = " target=\"$ext_win_target\"";
$bbs_win_target && $bbs_win_target = " target=\"$bbs_win_target\"";

if ($get_new_res) {
	if ($get_new_res != "all") { $get_new_res = "l".$get_new_res; }
} else {
	$get_new_res = "l200";
}

//======================================================================
// �ϐ��ݒ�
//======================================================================
$_conf['login_log_rec'] = 1;	// ���O�C�����O�̋L�^��
$_conf['login_log_rec_num'] = 100;	// ���O�C�����O�̋L�^��
$_conf['last_login_log_show'] = 1;	// �O�񃍃O�C�����\����

$p2web_url = "http://akid.s17.xrea.com/";
$p2ime_url = "http://akid.s17.xrea.com/p2ime.php";
$favrank_url = "http://akid.s17.xrea.com:8080/favrank/favrank.php";
$menu_php = "menu.php";
$subject_php = "subject.php";
$_conf['read_php'] = "read.php";
$_conf['read_new_php'] = "read_new.php";
$_conf['read_new_k_php'] = "read_new_k.php";
$recent_php = "recent.php";
$fav_php = "fav.php";
$sb_header_inc = "sb_header.inc";
$sb_footer_inc = "sb_footer.inc";
$read_header_inc = "read_header.inc";
$read_footer_inc = "read_footer.inc";
$basic_js = "js/basic.js";
$respopup_js = "js/respopup.js"; //���X�|�b�v�A�b�v�pJavaScript�t�@�C��
$rctfile_name = "p2_recent.idx";
$rctfile = $prefdir."/".$rctfile_name;
$favlistfile_name = "p2_favlist.idx";
$favlistfile = $prefdir."/".$favlistfile_name;
$favita_name = "p2_favita.brd";
$favita_path = $prefdir."/".$favita_name;
$idpw2ch_php = $prefdir."/p2_idpw2ch.php";
$sid2ch_php = $prefdir."/p2_sid2ch.php";
$auth_user_file = $prefdir."/p2_auth_user.php";
$auth_ez_file = $prefdir."/p2_auth_ez.php";
$auth_jp_file = $prefdir."/p2_auth_jp.php";
$_conf['login_log_file'] = $prefdir . "/p2_login.log.php";
$crypt_xor_key = $_SERVER["SERVER_NAME"].$_SERVER["SERVER_SOFTWARE"];
$brocra_checker['url'] = "http://www.jah.ne.jp/~fild/cgi-bin/LBCC/lbcc.cgi"; // �u���N���`�F�b�JURL
$brocra_checker['query'] = "url";
$ktai_read_cgi = "r.i";
$before_respointer_k = 0;
$ktai_res_size = 500; 		// �g�їp�A��̃��X�̍ő�\���T�C�Y
$ktai_ryaku_size = 120; 	// �g�їp�A���X���ȗ������Ƃ��̕\���T�C�Y
$c_menu_dl_interval = 1;	// menu�̃L���b�V�����X�V�����ɕێ����鎞��(hour)
$fsockopen_time_limit = 15;	// �l�b�g���[�N�ڑ��^�C���A�E�g����(�b)
set_time_limit(60); 		// �X�N���v�g���s��������(�b)

$data_dir_perm = 0707;		// �f�[�^�ۑ��p�f�B���N�g���̃p�[�~�b�V����
$dat_perm = 0606; 		// dat�t�@�C���̃p�[�~�b�V����
$key_perm = 0606; 		// key.idx �t�@�C���̃p�[�~�b�V����
$_conf['dl_perm'] = 0606;	// ���̑���p2�������I��DL�ۑ�����t�@�C���i�L���b�V�����j�̃p�[�~�b�V����
$pass_perm = 0604;		// �p�X���[�h�t�@�C���̃p�[�~�b�V����
$p2_perm = 0606; 		// ���̑���p2�̓����ۑ��f�[�^�t�@�C��
$palace_perm = 0606;		// �a������L�^�t�@�C���̃p�[�~�b�V����
$favita_perm = 0606;		// ���C�ɔL�^�t�@�C���̃p�[�~�b�V����
$favlist_perm = 0606;		// ���C�ɃX���L�^�t�@�C���̃p�[�~�b�V����
$rct_perm = 0606;		// �ŋߓǂ񂾃X���L�^�t�@�C���̃p�[�~�b�V����
$res_write_perm = 0606;		// �������ݗ����L�^�t�@�C���̃p�[�~�b�V����

/**
 * http header �o�͊֐�
 */
function header_nocache()
{
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // ���t���ߋ�
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // ��ɏC������Ă���
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache"); // HTTP/1.0
}

function header_content_type()
{
	header("Content-Type: text/html; charset=Shift_JIS");
}

/**
 * �F�؊֐�
 */
function authorize()
{
	global $login;
	global $auth_ez_file, $auth_jp_file, $login_file, $auth_user_file, $pass_perm;
	global $_info_msg_ht, $doctype, $STYLE, $ktai, $auth_ez_file, $auth_jp_file, $prefdir, $datdir;
	
	if ($login['use']) {

		// �F�؃��[�U�ݒ�ǂݍ��� ========
		if (file_exists($auth_user_file)) {
			include($auth_user_file);
	
			// EZweb�F�؃X���[�p�X �T�u�X�N���C�oID
			if ($_SERVER['HTTP_X_UP_SUBNO']) {
				if (file_exists($auth_ez_file)) {
					include($auth_ez_file);
					if ($_SERVER['HTTP_X_UP_SUBNO'] == $registed_ez) {
						return true;
					}
				}
			}
			
			// J-PHONE�F�؃X���[�p�X //�p�P�b�g�Ή��@ �v���[�UID�ʒmON�̐ݒ� �[���V���A���ԍ�
			if (preg_match("/J-PHONE\/[^\/]+\/[^\/]+\/SN(.+?) /", $_SERVER['HTTP_USER_AGENT'], $matches)) {
				if (file_exists($auth_jp_file)) {
					include($auth_jp_file);
					if ($matches[1] == $registed_jp) {
						return true;
					}
				}
			}

			// �N�b�L�[�F�؃X���[�p�X
			if (($_COOKIE["p2_user"] == $login['user']) && ($_COOKIE["p2_pass"] == $login['pass'])) {
				return true;
			}
		
			// Basic�F��
			if (!isset($_SERVER['PHP_AUTH_USER']) || !( ($_SERVER['PHP_AUTH_USER'] == $login['user']) && (crypt($_SERVER['PHP_AUTH_PW'], $login['pass']) == $login['pass']))) {
				header('WWW-Authenticate: Basic realm="p2"');
				header('HTTP/1.0 401 Unauthorized');
				echo "Login Failed. ���[�U�F�؂��K�v�ł��B";
				exit;
			}

		// �ݒ�t�@�C�����Ȃ�����
		} else {
			include("./login_first.inc");
			exit;
		}
		
	}
	return true;
}

?>