<?php
// p2 - ��{�ݒ�t�@�C���i���ɗ��R�̖�������ύX���Ȃ����Ɓj

$_conf['p2version'] = '1.5.2';

//$_conf['p2name'] = 'p2';	// p2�̖��O�B
$_conf['p2name'] = 'P2';	// p2�̖��O�B


//======================================================================
// ��{�ݒ菈��
//======================================================================
error_reporting(E_ALL ^ E_NOTICE); 

// ��������m�F
if (version_compare(phpversion(), '4.3.0', 'lt')) {
	die('<html><body><h1>p2 info: PHP�o�[�W����4.3.0�����ł͎g���܂���B</h1></body></html>');
} elseif (version_compare(phpversion(), '5.0.0', 'ge')) {
	ini_set('zend.ze1_compatibility_mode', 'On'); // �I�u�W�F�N�g�̂ӂ�܂���PHP4�Ɠ�����
}
if (ini_get('safe_mode')) {
	die('<html><body><h1>p2 info: �Z�[�t���[�h�œ��삷��PHP�ł͎g���܂���B</h1></body></html>');
}
if (!extension_loaded('mbstring')) {
	die('<html><body><h1>p2 info: mbstring�g�����W���[�������[�h����Ă��܂���B</h1></body></html>');
}

require_once './p2util.class.php';

@putenv("TZ=JST-9"); // �^�C���]�[�����Z�b�g

// session.trans_sid�L���� �� output_add_rewrite_var(), http_build_query() ���Ő����E�ύX�����
// URL��GET�p�����[�^��؂蕶��(��)��"&amp;"�ɂ���B�i�f�t�H���g��"&"�j
ini_set('arg_separator.output', '&amp;');

// �����������ɂ����镶���R�[�h�w��
// mb_detect_order("SJIS,EUC-JP,ASCII");
mb_internal_encoding('SJIS-win');
mb_http_output('pass');
// ob_start('mb_output_handler');

if (function_exists('mb_ereg_replace')) {
	define('P2_MBREGEX_AVAILABLE', 1);
	@mb_regex_encoding('SJIS-win');
} else {
	define('P2_MBREGEX_AVAILABLE', 0);
}

/**
 * �t�H�[������̓��͂��ꊇ�ŃN�H�[�g�����������R�[�h�ϊ�
 * �t�H�[����accept-encoding������UTF-8(Safari�n) or Shift_JIS(���̑�)�ɂ��A
 * �����hidden�v�f�Ŕ����e�[�u���̕������d���ނ��ƂŌ딻������炷
 * �ϊ�������eucJP-win������̂�HTTP���͂̕����R�[�h��EUC�Ɏ����ϊ������T�[�o�̂���
 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	if (get_magic_quotes_gpc()) {
		$_POST = array_map('stripslashes_r', $_POST);
	}
	mb_convert_variables('SJIS-win', 'UTF-8,eucJP-win,SJIS-win', $_POST);
	$_POST = array_map('nullfilter_r', $_POST);
} elseif (!empty($_GET)) {
	if (get_magic_quotes_gpc()) {
		$_GET = array_map('stripslashes_r', $_GET);
	}
	mb_convert_variables('SJIS-win', 'UTF-8,eucJP-win,SJIS-win', $_GET);
	$_GET = array_map('nullfilter_r', $_GET);
}

if (P2Util::isBrowserSafariGroup()) {
	$_conf['accept_charset'] = 'UTF-8';
} else {
	$_conf['accept_charset'] = 'Shift_JIS';
}

// UA���� ===========================================
if (!empty($_GET['k']) || !empty($_POST['k'])) {
	$_conf['ktai'] = 1;
	$k_at_a = "&amp;k=1";
	$k_at_q = "?k=1";
	$k_input_ht = '<input type="hidden" name="k" value="1">';
}
//$_conf['ktai'] = 1;//
$_conf['doctype'] = "";
$_conf['accesskey'] = "accesskey";
$_conf['pointer_name'] = "id";
$_conf['k_accesskey']['matome'] = '3';	// �V�܂Ƃ�	// 3
$_conf['k_accesskey']['latest'] = '3';	// �V // 9
$_conf['k_accesskey']['res'] = '7';		// ڽ
$_conf['k_accesskey']['above'] = '2';	// �� // 2
$_conf['k_accesskey']['up'] = '5';	// �i�j // 5
$_conf['k_accesskey']['prev'] = '4';	// �O // 4
$_conf['k_accesskey']['bottom'] = '8';	// �� // 8
$_conf['k_accesskey']['next'] = '6';	// �� // 6

$meta_charset_ht = <<<EOP
<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
EOP;

// �g�� ===================================
if (strstr($_SERVER['HTTP_USER_AGENT'], "UP.Browser/")) {
	$browser = "EZweb";
	$_conf['ktai'] = true;
	/*
	$_conf['doctype'] = <<<EOP
<?xml version="1.0" encoding="Shift_JIS"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN"
"http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">
EOP;
	*/

} elseif (preg_match('{^DoCoMo/}', $_SERVER['HTTP_USER_AGENT'])) {
	//$browser = "DoCoMo";
	$_conf['ktai'] = true;
	$_conf['pointer_name'] = "name";

} elseif (preg_match('{^(J-PHONE|Vodafone)/}', $_SERVER['HTTP_USER_AGENT'])) {
	//$browser = "JPHONE";
	$_conf['ktai'] = true;
	$_conf['accesskey'] = "DIRECTKEY";
	$_conf['pointer_name'] = "name";

} elseif (strstr($_SERVER['HTTP_USER_AGENT'], 'DDIPOCKET')) {
	//$browser="DDIPOCKET";
	$_conf['ktai'] = true;
	$_conf['pointer_name'] = "name";
}

$k_to_index_ht = <<<EOP
<a {$_conf['accesskey']}="0" href="index.php{$k_at_q}">0.TOP</a>
EOP;

// DOCTYPE HTML �錾 ==========================
$ie_strict = false;
if (empty($_conf['ktai'])) {
	if ($ie_strict) {
		$_conf['doctype'] = <<<EODOC
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
		"http://www.w3.org/TR/html4/loose.dtd">\n
EODOC;
	} else {
		$_conf['doctype'] = <<<EODOC
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">\n
EODOC;
	}
}

//======================================================================

if (file_exists("./conf_user.inc.php")) {
	include_once "./conf_user.inc.php"; // ���[�U�ݒ� �Ǎ�
}
if (file_exists("./conf_user_style.inc.php")) {
	include_once "./conf_user_style.inc.php"; // �f�U�C���ݒ� �Ǎ�
}

$_conf['display_threads_num'] = 150; // (150) �X���b�h�T�u�W�F�N�g�ꗗ�̃f�t�H���g�\����
$posted_rec_num = 1000; // (1000) �������񂾃��X�̍ő�L�^�� //���݂͋@�\���Ă��Ȃ�

$_conf['p2status_dl_interval'] = 360;	// (360) p2status�i�A�b�v�f�[�g�`�F�b�N�j�̃L���b�V�����X�V�����ɕێ����鎞�� (��)

/* �f�t�H���g�ݒ� */
if (!isset($login['use'])) { $login['use'] = 1; }
if (!is_dir($_conf['pref_dir'])) { $_conf['pref_dir'] = "./data"; }
if (!is_dir($datdir)) { $datdir = "./data"; }
if (!isset($_conf['rct_rec_num'])) { $_conf['rct_rec_num'] = 20; }
if (!isset($_conf['res_hist_rec_num'])) { $_conf['res_hist_rec_num'] = 20; }
if (!isset($posted_rec_num)) { $posted_rec_num = 1000; }
if (!isset($_conf['before_respointer'])) { $_conf['before_respointer'] = 20; }
if (!isset($_conf['sort_zero_adjust'])) { $_conf['sort_zero_adjust'] = 0.1; }
if (!isset($_conf['display_threads_num'])) { $_conf['display_threads_num'] = 150; }
if (!isset($_conf['cmp_dayres_midoku'])) { $_conf['cmp_dayres_midoku'] = 1; }
if (!isset($_conf['k_sb_disp_range'])) { $_conf['k_sb_disp_range'] = 30; }
if (!isset($_conf['k_rnum_range'])) { $_conf['k_rnum_range'] = 10; }
if (!isset($_conf['pre_thumb_height'])) { $_conf['pre_thumb_height'] = "32"; }
if (!isset($_conf['quote_res_view'])) { $_conf['quote_res_view'] = 1; }
if (!isset($_conf['res_write_rec'])) { $_conf['res_write_rec'] = 1; }

if (!isset($STYLE['post_pop_size'])) { $STYLE['post_pop_size'] = "610,350"; }
if (!isset($STYLE['post_msg_rows'])) { $STYLE['post_msg_rows'] = 10; }
if (!isset($STYLE['post_msg_cols'])) { $STYLE['post_msg_cols'] = 70; }
if (!isset($STYLE['info_pop_size'])) { $STYLE['info_pop_size'] = "600,380"; }

// ���[�U�ݒ�̒�������
$_conf['ext_win_target'] && $_conf['ext_win_target_at'] = " target=\"{$_conf['ext_win_target']}\"";
$_conf['bbs_win_target'] && $_conf['bbs_win_target_at'] = " target=\"{$_conf['bbs_win_target']}\"";

if ($_conf['get_new_res']) {
	if ($_conf['get_new_res'] == 'all') {
		$_conf['get_new_res_l'] = $_conf['get_new_res'];
	} else {
		$_conf['get_new_res_l'] = 'l'.$_conf['get_new_res'];
	}
} else {
	$_conf['get_new_res_l'] = 'l200';
}

//======================================================================
// �ϐ��ݒ�
//======================================================================
$_conf['login_log_rec'] = 1;	// ���O�C�����O�̋L�^��
$_conf['login_log_rec_num'] = 100;	// ���O�C�����O�̋L�^��
$_conf['last_login_log_show'] = 1;	// �O�񃍃O�C�����\����

$_conf['p2web_url'] = "http://akid.s17.xrea.com/";
$_conf['p2ime_url'] = "http://akid.s17.xrea.com/p2ime.php";
$_conf['favrank_url'] = "http://akid.s17.xrea.com:8080/favrank/favrank.php";
$_conf['menu_php'] = "menu.php";
$_conf['subject_php'] = "subject.php";
$_conf['read_php'] = "read.php";
$_conf['read_new_php'] = "read_new.php";
$_conf['read_new_k_php'] = "read_new_k.php";
$sb_header_inc = "sb_header.inc.php";
$sb_footer_inc = "sb_footer.inc.php";
$read_header_inc = "read_header.inc.php";
$read_footer_inc = "read_footer.inc.php";
$_conf['rct_file'] = $_conf['pref_dir']."/"."p2_recent.idx";
$_conf['cache_dir'] = $_conf['pref_dir'].'/p2_cache';
$_conf['cookie_dir'] = $_conf['pref_dir'].'/p2_cookie';	// cookie �ۑ��f�B���N�g��
$_conf['cookie_file_name'] = 'p2_cookie.txt';
$_conf['favlist_file'] = $_conf['pref_dir']."/"."p2_favlist.idx";
$_conf['favita_path'] = $_conf['pref_dir']."/"."p2_favita.brd";
$_conf['idpw2ch_php'] = $_conf['pref_dir']."/p2_idpw2ch.php";
$_conf['sid2ch_php'] = $_conf['pref_dir']."/p2_sid2ch.php";
$_conf['auth_user_file'] = $_conf['pref_dir']."/p2_auth_user.php";
$_conf['auth_ez_file'] = $_conf['pref_dir']."/p2_auth_ez.php";
$_conf['auth_jp_file'] = $_conf['pref_dir']."/p2_auth_jp.php";
$_conf['login_log_file'] = $_conf['pref_dir'] . "/p2_login.log.php";
$_conf['failed_post_file'] = $_conf['pref_dir'].'/p2_failed_post.data.php';

$_conf['crypt_xor_key'] = $_SERVER["SERVER_NAME"].$_SERVER["SERVER_SOFTWARE"];
$_conf['menu_dl_interval'] = 1;	// (1) �� menu �̃L���b�V�����X�V�����ɕێ����鎞�� (hour)
$_conf['fsockopen_time_limit'] = 10;	// (10) �l�b�g���[�N�ڑ��^�C���A�E�g����(�b)
set_time_limit(60); 		// �X�N���v�g���s��������(�b)

$_conf['data_dir_perm'] = 0707;		// �f�[�^�ۑ��p�f�B���N�g���̃p�[�~�b�V����
$_conf['dat_perm'] = 0606; 		// dat�t�@�C���̃p�[�~�b�V����
$_conf['key_perm'] = 0606; 		// key.idx �t�@�C���̃p�[�~�b�V����
$_conf['dl_perm'] = 0606;	// ���̑���p2�������I��DL�ۑ�����t�@�C���i�L���b�V�����j�̃p�[�~�b�V����
$_conf['pass_perm'] = 0604;		// �p�X���[�h�t�@�C���̃p�[�~�b�V����
$_conf['p2_perm'] = 0606; 		// ���̑���p2�̓����ۑ��f�[�^�t�@�C��
$_conf['palace_perm'] = 0606;		// �a������L�^�t�@�C���̃p�[�~�b�V����
$_conf['favita_perm'] = 0606;		// ���C�ɔL�^�t�@�C���̃p�[�~�b�V����
$_conf['favlist_perm'] = 0606;		// ���C�ɃX���L�^�t�@�C���̃p�[�~�b�V����
$_conf['rct_perm'] = 0606;		// �ŋߓǂ񂾃X���L�^�t�@�C���̃p�[�~�b�V����
$_conf['res_write_perm'] = 0606;		// �������ݗ����L�^�t�@�C���̃p�[�~�b�V����


//=====================================================================
// �֐�
//=====================================================================
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
	global $_conf, $login;
	global $login_file;
	global $_info_msg_ht, $STYLE, $datdir;
	
	if ($login['use']) {

		// �F�؃��[�U�ݒ�ǂݍ��� ========
		if (file_exists($_conf['auth_user_file'])) {
			include($_conf['auth_user_file']);
	
			// EZweb�F�؃X���[�p�X �T�u�X�N���C�oID
			if ($_SERVER['HTTP_X_UP_SUBNO']) {
				if (file_exists($_conf['auth_ez_file'])) {
					include($_conf['auth_ez_file']);
					if ($_SERVER['HTTP_X_UP_SUBNO'] == $registed_ez) {
						return true;
					}
				}
			}
			
			// J-PHONE�F�؃X���[�p�X //�p�P�b�g�Ή��@ �v���[�UID�ʒmON�̐ݒ� �[���V���A���ԍ�
			// http://www.dp.j-phone.com/dp/tool_dl/web/useragent.php
			if (preg_match('{(J-PHONE|Vodafone)/([^/]+?/)+?SN(.+?) }', $_SERVER['HTTP_USER_AGENT'], $matches)) {
				if (file_exists($_conf['auth_jp_file'])) {
					include($_conf['auth_jp_file']);
					if ($matches[3] == $registed_jp) {
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

/**
 * �ċA�I��stripslashes��������
 * GET/POST/COOKIE�ϐ��p�Ȃ̂ŃI�u�W�F�N�g�̃v���p�e�B�ɂ͑Ή����Ȃ�
 * (ExUtil)
 */
function stripslashes_r($var, $r = 0)
{
	if (is_array($var) && $r < 3) {
		foreach ($var as $key => $value) {
			$var[$key] = stripslashes_r($value, ++$r);
		}
	} elseif (is_string($var)) {
		$var = stripslashes($var);
	}
	return $var;
}

/**
 * �ċA�I�Ƀk���������폜����
 * mbstring�ŕϊ��e�[�u���ɂȂ�(?)�O����ϊ������
 * NULL(0x00)�ɂȂ��Ă��܂����Ƃ�����̂ŏ�������
 * (ExUtil)
 */
function nullfilter_r($var, $r = 0)
{
	if (is_array($var) && $r < 3) {
		foreach ($var as $key => $value) {
			$var[$key] = nullfilter_r($value, ++$r);
		}
	} elseif (is_string($var)) {
		$var = str_replace(chr(0), '', $var);
	}
	return $var;
}
	
?>