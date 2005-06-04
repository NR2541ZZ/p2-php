<?php
/*
    p2 - ��{�ݒ�t�@�C��

    ���̃t�@�C���́A���ɗ��R�̖�������ύX���Ȃ�����
*/

$_conf['p2version'] = '1.6.5';

//$_conf['p2name'] = 'p2';  // p2�̖��O�B
$_conf['p2name'] = 'P2';    // p2�̖��O�B


//======================================================================
// ��{�ݒ菈��
//======================================================================
error_reporting(E_ALL ^ E_NOTICE); // �G���[�o�͐ݒ�

$debug = 0;
isset($_GET['debug']) and $debug = $_GET['debug'];
if ($debug) {
    include_once 'Benchmark/Profiler.php';
    $profiler =& new Benchmark_Profiler(true);
    
    // printMemoryUsage();
    register_shutdown_function('printMemoryUsage');
}

$_info_msg_ht = '';

// {{{ ��������m�F

if (version_compare(phpversion(), '4.3.0', 'lt')) {
    die('<html><body><h1>p2 info: PHP�o�[�W����4.3.0�����ł͎g���܂���B</h1></body></html>');
}
if (ini_get('safe_mode')) {
    die('<html><body><h1>p2 info: �Z�[�t���[�h�œ��삷��PHP�ł͎g���܂���B</h1></body></html>');
}
if (!extension_loaded('mbstring')) {
    die('<html><body><h1>p2 info: mbstring�g�����W���[�������[�h����Ă��܂���B</h1></body></html>');
}
// }}}
// {{{ ���ݒ�

@putenv('TZ=JST-9'); // �^�C���]�[�����Z�b�g

// �����t���b�V�����I�t�ɂ���
ob_implicit_flush(0);

// �N���C�A���g����ڑ���؂��Ă������𑱍s����
// ignore_user_abort(1);

// session.trans_sid�L���� �� output_add_rewrite_var(), http_build_query() ���Ő����E�ύX�����
// URL��GET�p�����[�^��؂蕶��(��)��"&amp;"�ɂ���B�i�f�t�H���g��"&"�j
ini_set('arg_separator.output', '&amp;');

// ���N�G�X�gID��ݒ�
define('P2_REQUEST_ID', substr($_SERVER['REQUEST_METHOD'], 0, 1) . md5(serialize($_REQUEST)));

// OS����
if (strstr(PHP_OS, 'WIN')) {
    // Windows
    defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ';');
    defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '\\');
} else {
    defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ':');
    defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '/');
}

// �����������ɂ����镶���R�[�h�w��
// mb_detect_order("SJIS-win,eucJP-win,ASCII");
mb_internal_encoding('SJIS-win');
mb_http_output('pass');
mb_substitute_character(63); // �����R�[�h�ϊ��Ɏ��s���������� "?" �ɂȂ�
// ob_start('mb_output_handler');

if (function_exists('mb_ereg_replace')) {
    define('P2_MBREGEX_AVAILABLE', 1);
    @mb_regex_encoding('SJIS-win');
} else {
    define('P2_MBREGEX_AVAILABLE', 0);
}

// }}}
// {{{ ���C�u�����ނ̃p�X�ݒ�

// ��{�I�ȋ@�\��񋟂��邷�郉�C�u����
define('P2_LIBRARY_DIR', './lib');

// ���܂��I�ȋ@�\��񋟂��邷�郉�C�u����
define('P2EX_LIBRARY_DIR', './lib/expack');

// �X�^�C���V�[�g
define('P2_STYLE_DIR', './style');

// PEAR�C���X�g�[���f�B���N�g���A�����p�X�ɒǉ������
define('P2_PEAR_DIR', './includes');

// PEAR���n�b�N�����t�@�C���p�f�B���N�g���A�ʏ��PEAR���D��I�Ɍ����p�X�ɒǉ������
// Cache/Container/db.php(PEAR::Cache)��MySQL���肾�����̂ŁA�ėp�I�ɂ������̂�u���Ă���
define('P2_PEAR_HACK_DIR', './lib/pear_hack');

// �����p�X���Z�b�g
if (is_dir(P2_PEAR_DIR) || is_dir(P2_PEAR_HACK_DIR)) {
    $_include_path = '.';
    if (is_dir(P2_PEAR_HACK_DIR)) {
        $_include_path .= PATH_SEPARATOR . realpath(P2_PEAR_HACK_DIR);
    }
    if (is_dir(P2_PEAR_DIR)) {
        $_include_path .= PATH_SEPARATOR . realpath(P2_PEAR_DIR);
    }
    $_include_path .= PATH_SEPARATOR . ini_get('include_path');
    ini_set('include_path', $_include_path);
}

// ���[�e�B���e�B�N���X��ǂݍ���
require_once (P2_LIBRARY_DIR . '/p2util.class.php');

// }}}
// {{{ PEAR::PHP_Compat��PHP5�݊��̊֐���ǂݍ���
/*
if (version_compare(phpversion(), '5.0.0', '<')) {
    require_once 'PHP/Compat.php';
    PHP_Compat::loadFunction('clone');
    PHP_Compat::loadFunction('scandir');
    PHP_Compat::loadFunction('http_build_query');
    PHP_Compat::loadFunction('array_walk_recursive');
}
*/
// }}}
// {{{ �t�H�[������̓��͂��ꊇ�ŃT�j�^�C�Y

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

// }}}

if (P2Util::isBrowserSafariGroup()) {
    $_conf['accept_charset'] = 'UTF-8';
} else {
    $_conf['accept_charset'] = 'Shift_JIS';
}


$_conf['doctype'] = '';
$_conf['accesskey'] = 'accesskey';

// {{{ �g�уA�N�Z�X�L�[
$_conf['k_accesskey']['matome'] = '3';  // �V�܂Ƃ�
$_conf['k_accesskey']['latest'] = '3';  // �V
$_conf['k_accesskey']['res'] = '7';     // ڽ
$_conf['k_accesskey']['above'] = '2';   // ��
$_conf['k_accesskey']['up'] = '5';      // �i�j
$_conf['k_accesskey']['prev'] = '4';    // �O
$_conf['k_accesskey']['bottom'] = '8';  // ��
$_conf['k_accesskey']['next'] = '6';    // ��
$_conf['k_accesskey']['info'] = '9';    // ��
$_conf['k_accesskey']['dele'] = '*';    // ��
$_conf['k_accesskey']['filter'] = '#';  // ��

$_conf['meta_charset_ht'] = '<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">'."\n";

// {{{ �[������

require_once 'Net/UserAgent/Mobile.php';
$mobile = &Net_UserAgent_Mobile::singleton();

// PC
if ($mobile->isNonMobile()) {
    $_conf['ktai'] = FALSE;

    if (P2Util::isBrowserSafariGroup()) {
        $_conf['accept_charset'] = 'UTF-8';
    } else {
        $_conf['accept_charset'] = 'Shift_JIS';
    }

// �g��
} else {
    $_conf['ktai'] = TRUE;
    $_conf['accept_charset'] = 'Shift_JIS';

    // �x���_����
    // Vodafone Live!
    if ($mobile->isVodafone()) {
        $_conf['accesskey'] = 'DIRECTKEY';
    }
}

// }}}
// {{{ �N�G���[�ɂ�鋭���r���[�w��

// b=pc �͂܂������N�悪���S�łȂ�
// output_add_rewrite_var() �͕֗������A�o�͂��o�b�t�@����đ̊����x��������̂���_�B�B
// �̊����x�𗎂Ƃ��Ȃ��ǂ����@�Ȃ����ȁH

// PC�ib=pc�j
if ($_GET['b'] == 'pc' || $_POST['b'] == 'pc') {
    $_conf['b'] = 'pc';
    $_conf['ktai'] = false;
    //output_add_rewrite_var('b', 'pc');

    $_conf['k_at_a'] = '&amp;b=pc';
    $_conf['k_at_q'] = '?b=pc';
    $_conf['k_input_ht'] = '<input type="hidden" name="b" value="pc">';

// �g�сib=k�Bk=1�͉ߋ��݊��p�j
} elseif (!empty($_GET['k']) || !empty($_POST['k']) || $_GET['b'] == 'k' || $_POST['b'] == 'k') {
    $_conf['b'] = 'k';
    $_conf['ktai'] = true;
    //output_add_rewrite_var('b', 'k');
    
    $_conf['k_at_a'] = '&amp;b=k';
    $_conf['k_at_q'] = '?b=k';
    $_conf['k_input_ht'] = '<input type="hidden" name="b" value="k">';
}
// }}}

$_conf['k_to_index_ht'] = <<<EOP
<a {$_conf['accesskey']}="0" href="index.php{$_conf['k_at_q']}">0.TOP</a>
EOP;

// {{{ DOCTYPE HTML �錾
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
// }}}

//======================================================================

if (file_exists("./conf/conf_user.inc.php")) {
    include_once "./conf/conf_user.inc.php"; // ���[�U�ݒ� �Ǎ�
}
if (file_exists("./conf/conf_user_style.inc.php")) {
    include_once "./conf/conf_user_style.inc.php"; // �f�U�C���ݒ� �Ǎ�
}

$_conf['display_threads_num'] = 150; // (150) �X���b�h�T�u�W�F�N�g�ꗗ�̃f�t�H���g�\����
$posted_rec_num = 1000; // (1000) �������񂾃��X�̍ő�L�^�� //���݂͋@�\���Ă��Ȃ�

$_conf['p2status_dl_interval'] = 360; // (360) p2status�i�A�b�v�f�[�g�`�F�b�N�j�̃L���b�V�����X�V�����ɕێ����鎞�� (��)

// {{{ �f�t�H���g�ݒ�
if (!isset($login['use'])) { $login['use'] = 1; }
if (!is_dir($_conf['pref_dir'])) { $_conf['pref_dir'] = "./data"; }
if (!is_dir($_conf['dat_dir'])) { $_conf['dat_dir'] = "./data"; }
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
// }}}

// {{{ ���[�U�ݒ�̒�������
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
// }}}

//======================================================================
// �ϐ��ݒ�
//======================================================================
$_conf['login_log_rec'] = 1; // ���O�C�����O�̋L�^��
$_conf['login_log_rec_num'] = 100; // ���O�C�����O�̋L�^��
$_conf['last_login_log_show'] = 1; // �O�񃍃O�C�����\����

$_conf['p2web_url'] = "http://akid.s17.xrea.com/";
$_conf['p2ime_url'] = "http://akid.s17.xrea.com/p2ime.php";
$_conf['favrank_url'] = "http://akid.s17.xrea.com:8080/favrank/favrank.php";
$_conf['menu_php'] = "menu.php";
$_conf['subject_php'] = "subject.php";
$_conf['read_php'] = "read.php";
$_conf['read_new_php'] = "read_new.php";
$_conf['read_new_k_php'] = "read_new_k.php";
$_conf['rct_file'] = $_conf['pref_dir'] . '/' . 'p2_recent.idx';
$_conf['p2_res_hist_dat'] = $_conf['pref_dir'] . '/p2_res_hist.dat'; // �������݃��O�t�@�C���idat�j
$_conf['p2_res_hist_dat_php'] = $_conf['pref_dir'] . '/p2_res_hist.dat.php'; // �������݃��O�t�@�C���i�f�[�^PHP�j
$_conf['cache_dir'] = $_conf['pref_dir'] . '/p2_cache';
$_conf['cookie_dir'] = $_conf['pref_dir'] . '/p2_cookie'; // cookie �ۑ��f�B���N�g��
$_conf['cookie_file_name'] = 'p2_cookie.txt';
$_conf['favlist_file'] = $_conf['pref_dir'] . "/" . "p2_favlist.idx";
$_conf['favita_path'] = $_conf['pref_dir'] . "/" . "p2_favita.brd";
$_conf['idpw2ch_php'] = $_conf['pref_dir'] . "/p2_idpw2ch.php";
$_conf['sid2ch_php'] = $_conf['pref_dir'] . "/p2_sid2ch.php";
$_conf['auth_user_file'] = $_conf['pref_dir'] . "/p2_auth_user.php";
$_conf['auth_ez_file'] = $_conf['pref_dir'] . "/p2_auth_ez.php";
$_conf['auth_jp_file'] = $_conf['pref_dir'] . "/p2_auth_jp.php";
$_conf['login_log_file'] = $_conf['pref_dir'] . "/p2_login.log.php";

$_conf['idx_dir'] = $_conf['dat_dir'];

// saveMatomeCache() �̂��߂� $_conf['pref_dir'] ���΃p�X�ɕϊ�����
// �����ɂ���ẮArealpath() �Œl���擾�ł��Ȃ��ꍇ������H
if ($rp = realpath($_conf['pref_dir'])) {
    $_conf['matome_cache_path'] = $rp.'/matome_cache';
} else {
    if (substr($_conf['pref_dir'], 0, 1) == '/') {
        $_conf['matome_cache_path'] = $_conf['pref_dir'] . '/matome_cache';
    } else {
        $GLOBALS['pref_dir_realpath_failed_msg'] = 'p2 error: realpath()�̎擾���ł��܂���ł����B�t�@�C�� conf.inc.php �� $_conf[\'pref_dir\'] �����[�g����̐�΃p�X�w��Őݒ肵�Ă��������B';
    }
}

$_conf['matome_cache_ext'] = '.htm';
$_conf['matome_cache_max'] = 3; // �\���L���b�V���̐�

$_conf['md5_crypt_key'] = $_SERVER['SERVER_NAME'].$_SERVER['SERVER_SOFTWARE'];
$_conf['menu_dl_interval'] = 1; // (1) �� menu �̃L���b�V�����X�V�����ɕێ����鎞�� (hour)
$_conf['fsockopen_time_limit'] = 10; // (10) �l�b�g���[�N�ڑ��^�C���A�E�g����(�b)
set_time_limit(60); // �X�N���v�g���s��������(�b)

// {{{ �p�[�~�b�V�����ݒ�
$_conf['data_dir_perm'] = 0707; // �f�[�^�ۑ��p�f�B���N�g��
$_conf['dat_perm'] = 0606; // dat�t�@�C��
$_conf['key_perm'] = 0606; // key.idx �t�@�C��
$_conf['dl_perm'] = 0606; // ���̑���p2�������I��DL�ۑ�����t�@�C���i�L���b�V�����j
$_conf['pass_perm'] = 0604; // �p�X���[�h�t�@�C��
$_conf['p2_perm'] = 0606; // ���̑���p2�̓����ۑ��f�[�^�t�@�C��
$_conf['palace_perm'] = 0606; // �a������L�^�t�@�C��
$_conf['favita_perm'] = 0606; // ���C�ɔL�^�t�@�C��
$_conf['favlist_perm'] = 0606; // ���C�ɃX���L�^�t�@�C��
$_conf['rct_perm'] = 0606; // �ŋߓǂ񂾃X���L�^�t�@�C��
$_conf['res_write_perm'] = 0606; // �������ݗ����L�^�t�@�C��
// }}}

//=====================================================================
// �֐�
//=====================================================================

/**
 * �F�؊֐�
 */
function authorize()
{
    global $login;
    
    if ($login['use']) {
    
        include_once (P2_LIBRARY_DIR . '/login.inc.php');
    
        // �F�؃`�F�b�N
        if (!authCheck()) {
            // ���O�C�����s
            include_once (P2_LIBRARY_DIR . '/login_first.inc.php');
            printLoginFirst();
            exit;
        }
        
        // �v��������΁A�⏕�F�؂�o�^
        registCookie();
        registKtaiId();
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

function printMemoryUsage()
{
    echo memory_get_usage();
}
?>
