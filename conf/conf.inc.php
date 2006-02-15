<?php
/*
    rep2 - ��{�ݒ�t�@�C��

    ���̃t�@�C���́A���ɗ��R�̖�������ύX���Ȃ�����
*/

$_conf['p2version'] = '1.7.8';

$_conf['p2name'] = 'REP2';    // rep2�̖��O�B


//======================================================================
// ��{�ݒ菈��
//======================================================================
error_reporting(E_ALL ^ E_NOTICE); // �G���[�o�͐ݒ�

// {{{ ��{�ϐ�

$_conf['p2web_url']             = 'http://akid.s17.xrea.com/';
$_conf['p2ime_url']             = 'http://akid.s17.xrea.com/p2ime.php';
$_conf['favrank_url']           = 'http://akid.s17.xrea.com:8080/favrank/favrank.php';
$_conf['menu_php']              = 'menu.php';
$_conf['subject_php']           = 'subject.php';
$_conf['read_php']              = 'read.php';
$_conf['read_new_php']          = 'read_new.php';
$_conf['read_new_k_php']        = 'read_new_k.php';
$_conf['cookie_file_name']      = 'p2_cookie.txt';

$_info_msg_ht = '';

// }}}
// {{{ �f�o�b�O

$debug = 0;
isset($_GET['debug']) and $debug = $_GET['debug'];
if (!empty($debug)) {
    include_once 'Benchmark/Profiler.php';
    $profiler =& new Benchmark_Profiler(true);
    
    // printMemoryUsage();
    register_shutdown_function('printMemoryUsage');
}

// }}}
// {{{ ��������m�F

if (version_compare(phpversion(), '4.3.0', 'lt')) {
    die('<html><body><h3>p2 error: PHP�o�[�W����4.3.0�����ł͎g���܂���B</h3></body></html>');
}
if (ini_get('safe_mode')) {
    die('<html><body><h3>p2 error: �Z�[�t���[�h�œ��삷��PHP�ł͎g���܂���B</h3></body></html>');
}
if (!extension_loaded('mbstring')) {
    die('<html><body><h3>p2 error: mbstring�g�����W���[�������[�h����Ă��܂���B</h3></body></html>');
}
// }}}
// {{{ ���ݒ�

@putenv('TZ=JST-9'); // �^�C���]�[�����Z�b�g

@set_time_limit(60); // (60) �X�N���v�g���s��������(�b)

// �����t���b�V�����I�t�ɂ���
ob_implicit_flush(0);

// �N���C�A���g����ڑ���؂��Ă������𑱍s����
// ignore_user_abort(1);

// session.trans_sid�L���� �� output_add_rewrite_var(), http_build_query() ���Ő����E�ύX�����
// URL��GET�p�����[�^��؂蕶��(��)��"&amp;"�ɂ���B�i�f�t�H���g��"&"�j
ini_set('arg_separator.output', '&amp;');

// ���N�G�X�gID��ݒ�
define('P2_REQUEST_ID', substr($_SERVER['REQUEST_METHOD'], 0, 1) . md5(serialize($_REQUEST)));

// Windows �Ȃ�
if (strstr(PHP_OS, 'WIN')) {
    defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ';');
    defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '\\');
} else {
    defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ':');
    defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '/');
}

// }}}
// {{{ �����R�[�h�̎w��

// mb_detect_order("SJIS-win,eucJP-win,ASCII");
mb_internal_encoding('SJIS-win');
mb_http_output('pass');
mb_substitute_character(63); // �����R�[�h�ϊ��Ɏ��s���������� "?" �ɂȂ�
//mb_substitute_character(0x3013); // ��
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

// ���C�u������ǂݍ���
if (!include_once('Net/UserAgent/Mobile.php')) {
    $url = 'http://akid.s17.xrea.com:8080/p2puki/pukiwiki.php?PEAR%A4%CE%A5%A4%A5%F3%A5%B9%A5%C8%A1%BC%A5%EB';
    $url_t = $_conf['p2ime_url'] . "?enc=1&amp;url=" . rawurlencode($url);
    $msg = '<html><body><h3>p2 error: PEAR �� Net_UserAgent_Mobile ���C���X�g�[������Ă��܂���</h3>
        <p><a href="' . $url_t . '" target="_blank">p2Wiki: PEAR�̃C���X�g�[��</a></p>
        </body></html>';
    die($msg);
}
require_once (P2_LIBRARY_DIR . '/p2util.class.php');
require_once (P2_LIBRARY_DIR . '/dataphp.class.php');
require_once (P2_LIBRARY_DIR . '/session.class.php');
require_once (P2_LIBRARY_DIR . '/login.class.php');

// }}}
// {{{ PEAR::PHP_Compat��PHP5�݊��̊֐���ǂݍ���

if (version_compare(phpversion(), '5.0.0', '<')) {
    require_once 'PHP/Compat.php';
    //PHP_Compat::loadFunction('clone');
    PHP_Compat::loadFunction('scandir');
    //PHP_Compat::loadFunction('http_build_query');
    //PHP_Compat::loadFunction('array_walk_recursive');
}

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

// ���Ǘ��җp�ݒ��ǂݍ���
if (!include_once './conf/conf_admin.inc.php') {
    die('p2 error: �Ǘ��җp�ݒ�t�@�C����ǂݍ��߂܂���ł����B');
}

// �Ǘ��p�ۑ��f�B���N�g�� (�p�[�~�b�V������707)
$_conf['admin_dir'] = $_conf['data_dir'].'/admin';

// cache �ۑ��f�B���N�g�� (�p�[�~�b�V������707)
$_conf['cache_dir'] = $_conf['data_dir'].'/cache'; // 2005/6/29 $_conf['pref_dir'] . '/p2_cache' ���ύX

$_conf['doctype'] = '';
$_conf['accesskey'] = 'accesskey';

$_conf['meta_charset_ht'] = '<meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">'."\n";

// {{{ �[������

$_conf['login_check_ip']  = 1; // ���O�C������IP�A�h���X�����؂���

$mobile = &Net_UserAgent_Mobile::singleton();

// PC
if ($mobile->isNonMobile()) {
    $_conf['ktai'] = FALSE;
    $_conf['disable_cookie'] = FALSE;

    if (P2Util::isBrowserSafariGroup()) {
        $_conf['accept_charset'] = 'UTF-8';
    } else {
        $_conf['accept_charset'] = 'Shift_JIS';
    }

// �g��
} else {
    require_once (P2_LIBRARY_DIR . '/hostcheck.class.php');
    
    $_conf['ktai'] = TRUE;
    $_conf['accept_charset'] = 'Shift_JIS';

    // �x���_����
    // DoCoMo i-Mode
    if ($mobile->isDoCoMo()) {
        if ($_conf['login_check_ip'] && !HostCheck::isAddrDocomo()) {
            die('UA��DoCoMo�ł����AIP�A�h���X�ш悪�}�b�`���܂���B');
        }
        $_conf['disable_cookie'] = TRUE;
    // EZweb (au or Tu-Ka)
    } elseif ($mobile->isEZweb()) {
        if ($_conf['login_check_ip'] && !HostCheck::isAddrAu()) {
            die('UA��EZweb�ł����AIP�A�h���X�ш悪�}�b�`���܂���B');
        }
        $_conf['disable_cookie'] = FALSE;
    // Vodafone Live!
    } elseif ($mobile->isVodafone()) {
        if ($_conf['login_check_ip'] && !HostCheck::isAddrVodafone()) {
            die('UA��Vodafone�ł����AIP�A�h���X�ш悪�}�b�`���܂���B');
        }
        $_conf['accesskey'] = 'DIRECTKEY';
        // W�^�[����3GC�^�[����Cookie���g����
        if ($mobile->isTypeW() || $mobile->isType3GC()) {
            $_conf['disable_cookie'] = FALSE;
        } else {
            $_conf['disable_cookie'] = TRUE;
        }
    // AirH" Phone
    } elseif ($mobile->isAirHPhone()) {
        /*
        // AirH"�ł͒[��ID�F�؂��s��Ȃ��̂ŁA�R�����g�A�E�g
        if ($_conf['login_check_ip'] && !HostCheck::isAddrAirh()) {
            die('UA��AirH&quot;�ł����AIP�A�h���X�ш悪�}�b�`���܂���B');
        }
        */
        $_conf['disable_cookie'] = FALSE;
    // ���̑�
    } else {
        $_conf['disable_cookie'] = TRUE;
    }
}

// }}}
// {{{ �N�G���[�ɂ�鋭���r���[�w��

// b=pc �͂܂������N�悪���S�łȂ�
// output_add_rewrite_var() �͕֗������A�o�͂��o�b�t�@����đ̊����x��������̂���_�B�B
// �̊����x�𗎂Ƃ��Ȃ��ǂ����@�Ȃ����ȁH

// ����PC�r���[�w��
if ($_GET['b'] == 'pc' || $_POST['b'] == 'pc') {
    $_conf['b'] = 'pc';
    $_conf['ktai'] = false;
    //output_add_rewrite_var('b', 'pc');

    $_conf['k_at_a'] = '&amp;b=pc';
    $_conf['k_at_q'] = '?b=pc';
    $_conf['k_input_ht'] = '<input type="hidden" name="b" value="pc">';

// �����g�уr���[�w��ib=k�Bk=1�͉ߋ��݊��p�j
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

// {{{ �����[�U�ݒ� �Ǎ�

// �f�t�H���g�ݒ�iconf_user_def.inc.php�j��ǂݍ���
include_once './conf/conf_user_def.inc.php';
$_conf = array_merge($_conf, $conf_user_def);

// ���[�U�ݒ肪����Γǂݍ���
$_conf['conf_user_file'] = $_conf['pref_dir'] . '/conf_user.inc.php';
$conf_user = array();
if ($cont = DataPhp::getDataPhpCont($_conf['conf_user_file'])) {
    $conf_user = unserialize($cont);
    $_conf = array_merge($_conf, $conf_user);
}

// }}}
/*
if (file_exists("./conf/conf_user.inc.php")) {
    include_once "./conf/conf_user.inc.php"; // ���[�U�ݒ� �Ǎ�
}
*/
if (file_exists("./conf/conf_user_style.inc.php")) {
    include_once "./conf/conf_user_style.inc.php"; // �f�U�C���ݒ� �Ǎ�
}

// {{{ �f�t�H���g�ݒ�

if (!is_dir($_conf['pref_dir']))    { $_conf['pref_dir'] = "./data"; }
if (!is_dir($_conf['dat_dir']))     { $_conf['dat_dir'] = "./data"; }
if (!is_dir($_conf['idx_dir']))     { $_conf['idx_dir'] = "./data"; }
if (!isset($_conf['rct_rec_num']))  { $_conf['rct_rec_num'] = 20; }
if (!isset($_conf['res_hist_rec_num'])) { $_conf['res_hist_rec_num'] = 20; }
if (!isset($_conf['posted_rec_num'])) { $_conf['posted_rec_num'] = 1000; }
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
$_conf['rct_file'] =            $_conf['pref_dir'] . '/p2_recent.idx';
$_conf['p2_res_hist_dat'] =     $_conf['pref_dir'] . '/p2_res_hist.dat'; // �������݃��O�t�@�C���idat�j
$_conf['p2_res_hist_dat_php'] = $_conf['pref_dir'] . '/p2_res_hist.dat.php'; // �������݃��O�t�@�C���i�f�[�^PHP�j
$_conf['cookie_dir'] =          $_conf['pref_dir'] . '/p2_cookie'; // cookie �ۑ��f�B���N�g��
$_conf['favlist_file'] =        $_conf['pref_dir'] . "/p2_favlist.idx";
$_conf['favita_path'] =         $_conf['pref_dir'] . "/p2_favita.brd";
$_conf['idpw2ch_php'] =         $_conf['pref_dir'] . "/p2_idpw2ch.php";
$_conf['sid2ch_php'] =          $_conf['pref_dir'] . "/p2_sid2ch.php";
$_conf['auth_user_file'] =      $_conf['pref_dir'] . "/p2_auth_user.php";
$_conf['auth_ez_file'] =        $_conf['pref_dir'] . "/p2_auth_ez.php";
$_conf['auth_jp_file'] =        $_conf['pref_dir'] . "/p2_auth_jp.php";
$_conf['auth_docomo_file'] =    $_conf['pref_dir'] . '/p2_auth_docomo.php';
$_conf['login_log_file'] =      $_conf['pref_dir'] . "/p2_login.log.php";
$_conf['login_failed_log_file'] = $_conf['pref_dir'] . '/p2_login_failed.dat.php';

// saveMatomeCache() �̂��߂� $_conf['pref_dir'] ���΃p�X�ɕϊ�����
// �����ɂ���ẮArealpath() �Œl���擾�ł��Ȃ��ꍇ������H
if ($rp = realpath($_conf['pref_dir'])) {
    $_conf['matome_cache_path'] = $rp.'/matome_cache';
    define('P2_PREF_DIR_REAL_PATH', $rp);
} else {
    if (substr($_conf['pref_dir'], 0, 1) == '/') {
        $_conf['matome_cache_path'] = $_conf['pref_dir'] . '/matome_cache';
        define('P2_PREF_DIR_REAL_PATH', $_conf['pref_dir']);
    } else {
        $GLOBALS['pref_dir_realpath_failed_msg'] = 'p2 error: realpath()�̎擾���ł��܂���ł����B�t�@�C�� conf_user.inc.php �� $_conf[\'pref_dir\'] �����[�g����̐�΃p�X�w��Őݒ肵�Ă��������B';
    }
}

$_conf['matome_cache_ext'] = '.htm';
$_conf['matome_cache_max'] = 3; // �\���L���b�V���̐�

// {{{ ���肦�Ȃ������̃G���[

// �V�K���O�C���ƃ����o�[���O�C���̓����w��͂��肦�Ȃ��̂ŁA�G���[�o��
if (isset($_POST['submit_new']) && isset($_POST['submit_member'])) {
    die('p2 Error: ������URL�ł��B');
}

// }}}
// {{{ �z�X�g�`�F�b�N

if ($_conf['secure']['auth_host'] || $_conf['secure']['auth_bbq']) {
    require_once (P2_LIBRARY_DIR . '/hostcheck.class.php');
    if (($_conf['secure']['auth_host'] && HostCheck::getHostAuth() == FALSE) ||
        ($_conf['secure']['auth_bbq'] && HostCheck::getHostBurned() == TRUE)
    ) {
        HostCheck::forbidden();
    }
}

// }}}
// {{{ ���Z�b�V����

// ���O�́A�Z�b�V�����N�b�L�[��j������Ƃ��̂��߂ɁA�Z�b�V�������p�̗L���Ɋւ�炸�ݒ肷��
session_name('PS');

// {{{ �Z�b�V�����f�[�^�ۑ��f�B���N�g�����K��

if ($_conf['session_save'] == 'p2' and session_module_name() == 'files') {

    // $_conf['data_dir'] ���΃p�X�ɕϊ�����
    // �����ɂ���ẮArealpath() �Œl���擾�ł��Ȃ��ꍇ������H
    if ($rp = realpath($_conf['data_dir'])) {
        define('P2_DATA_DIR_REAL_PATH', $rp);
    } else {
        if (substr($_conf['data_dir'], 0, 1) == '/') {
            define('P2_DATA_DIR_REAL_PATH', $_conf['data_dir']);
        } else {
            die('p2 error: realpath()�̎擾���ł��܂���ł����B�t�@�C�� conf_user.inc.php �� $_conf[\'data_dir\'] �����[�g����̐�΃p�X�w��Őݒ肵�Ă��������B');
        }
    }
    
    $_conf['session_dir'] = P2_DATA_DIR_REAL_PATH . DIRECTORY_SEPARATOR . 'session';
}

// }}}

// css.php �͓��ʂɃZ�b�V��������O���B
//if (basename($_SERVER['PHP_SELF']) != 'css.php') {
    if ($_conf['use_session'] == 1 or ($_conf['use_session'] == 2 && !$_COOKIE['cid'])) { 
    
        // {{{ �Z�b�V�����f�[�^�ۑ��f�B���N�g����ݒ�
        
        if ($_conf['session_save'] == 'p2' and session_module_name() == 'files') {
        
            if (!is_dir($_conf['session_dir'])) {
                require_once (P2_LIBRARY_DIR . '/filectl.class.php');
                FileCtl::mkdir_for($_conf['session_dir'] . '/dummy_filename');
            } elseif (!is_writable($_conf['session_dir'])) {
                die("Error: �Z�b�V�����f�[�^�ۑ��f�B���N�g�� ({$_conf['session_dir']}) �ɏ������݌���������܂���B");
            }

            session_save_path($_conf['session_dir']);

            // session.save_path �̃p�X�̐[����2���傫���ƃK�[�x�b�W�R���N�V�������s���Ȃ��̂�
            // ���O�ŃK�[�x�b�W�R���N�V��������
            P2Util::session_gc();
        }
        
        // }}}

        $_p2session =& new Session();
        if ($_conf['disable_cookie'] && !ini_get('session.use_trans_sid')) {
            output_add_rewrite_var(session_name(), session_id());
        }
    }
//}

// }}}

// �����O�C���N���X�̃C���X�^���X�����i���O�C�����[�U���w�肳��Ă��Ȃ���΁A���̎��_�Ń��O�C���t�H�[���\���Ɂj
@require_once (P2_LIBRARY_DIR . '/login.class.php');
$_login =& new Login();


//=====================================================================
// �֐�
//=====================================================================
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

/**
 * �������̎g�p�ʂ�\������
 *
 * @return void
 */
function printMemoryUsage()
{
    $kb = memory_get_usage() / 1024;
    $kb = number_format($kb, 2, '.', '');
    
    echo 'Memory Usage: ' . $kb . 'KB';
}

?>
