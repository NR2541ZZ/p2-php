<?php
/*
    rep2 - ��{�ݒ�t�@�C��

    ���̃t�@�C���́A�V�X�e�������ݒ�Ȃ̂ŁA���ɗ��R�̖�������ύX���Ȃ�����
    ���[�U�ݒ�́A�u���E�U����u���[�U�ݒ�ҏW�v�ŁB�Ǘ��Ҍ����ݒ�́Aconf_admin.inc.php�ōs���B
*/

$_conf['p2version'] = '1.8.56'; // rep2�̃o�[�W����

$_conf['p2name'] = 'rep2';    // rep2�̖��O�B

$_conf['p2uaname'] = 'r e p 2';  // UA�p��rep2�̖��O

//======================================================================
// ��{�ݒ菈��
//======================================================================
// �G���[�o�͐ݒ�iNOTICE�팸���B�܂��c���Ă���Ǝv���j
if (defined('E_STRICT')) {
    error_reporting(E_ALL & ~(E_NOTICE | E_STRICT));
} else {
    error_reporting(E_ALL ^ E_NOTICE);
}
//error_reporting(E_ALL & ~(E_NOTICE | E_STRICT | E_DEPRECATED));

// {{{ ��{�ϐ�

$_conf['p2web_url']             = 'http://akid.s17.xrea.com/';
$_conf['p2ime_url']             = 'http://akid.s17.xrea.com/p2ime.phtml';
$_conf['favrank_url']           = 'http://akid.s17.xrea.com/favrank/favrank.php';
$_conf['menu_php']              = 'menu.php';
$_conf['subject_php']           = 'subject.php'; // subject_i.php
$_conf['read_php']              = 'read.php';
$_conf['read_new_php']          = 'read_new.php';
$_conf['read_new_k_php']        = 'read_new_k.php';
$_conf['post_php']              = 'post.php';
$_conf['cookie_file_name']      = 'p2_cookie.txt';
$_conf['menu_k_php']            = 'menu_k.php'; // menu_i.php
$_conf['editpref_php']          = 'editpref.php'; // editpref_i.php

// info.php ��JavaScript�t�@�C�����ɏ�����Ă���̂���

// }}}

// �f�o�b�O
_setDebug(); // void  $GLOBALS['debug'], $GLOBALS['profiler']

// PHP�̓�������m�F
_checkPHPInstalled(); // void|die

// {{{ ���ݒ�

// �^�C���]�[�����Z�b�g
if (function_exists('date_default_timezone_set')) { 
    date_default_timezone_set('Asia/Tokyo'); 
} else { 
    @putenv('TZ=JST-9'); 
}

// �����������l�̉����ݒ�(M)
// �ݒ�l���w��l�����Ȃ�w��l�Ɉ����グ�Đݒ肷��
_setMemoryLimit(32);

// �X�N���v�g�̎��s���Ԑ����̉����ݒ�(�b)
// �ݒ�l���w��b�����Ȃ�w��b�Ɉ����グ�Đݒ肷��
_setTimeLimit(60);

// �����t���b�V�����I�t�ɂ���
ob_implicit_flush(0);

// �N���C�A���g����ڑ���؂��Ă������𑱍s����
// ignore_user_abort(1);

// session.trans_sid�L���� �� output_add_rewrite_var(), http_build_query() ���Ő����E�ύX�����
// URL��GET�p�����[�^��؂蕶��(��)��"&amp;"�ɂ���B�i�f�t�H���g��"&"�j
ini_set('arg_separator.output', '&amp;');

// ���N�G�X�gID��ݒ�
define('P2_REQUEST_ID', substr($_SERVER['REQUEST_METHOD'], 0, 1) . md5(serialize($_REQUEST)));

// OS�ʂ̒萔���Z�b�g����BPATH_SEPARATOR, DIRECTORY_SEPARATOR
_setOSDefine();

// }}}


// �����R�[�h�̎w��
_setEncodings();

// {{{ ���C�u�����ނ̃p�X�ݒ�

define('P2_CONF_DIR', dirname(__FILE__)); // __DIR__ @php-5.3

define('P2_BASE_DIR', dirname(P2_CONF_DIR));

// ��{�I�ȋ@�\��񋟂��郉�C�u����
define('P2_LIB_DIR', P2_BASE_DIR . DIRECTORY_SEPARATOR . 'lib');

// ���܂��I�ȋ@�\��񋟂��郉�C�u����
define('P2EX_LIB_DIR', P2_BASE_DIR . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'expack');

// �X�^�C���V�[�g
define('P2_STYLE_DIR', P2_BASE_DIR . DIRECTORY_SEPARATOR . 'style');

// �X�L��
define('P2_SKIN_DIR', P2_BASE_DIR . DIRECTORY_SEPARATOR . 'skin');

// PEAR�C���X�g�[���f�B���N�g���A�����p�X�ɒǉ������
define('P2_PEAR_DIR', P2_BASE_DIR . DIRECTORY_SEPARATOR . 'includes');

// PEAR���n�b�N�����t�@�C���p�f�B���N�g���A�ʏ��PEAR���D��I�Ɍ����p�X�ɒǉ������
// Cache/Container/db.php(PEAR::Cache)��MySQL���肾�����̂ŁA�ėp�I�ɂ������̂�u���Ă���
define('P2_PEAR_HACK_DIR', P2_BASE_DIR . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'pear_hack');


require_once P2_LIB_DIR . '/global.funcs.php';

// �����p�X���Z�b�g
_iniSetIncludePath(); // void

// PEAR���C�u������ǂݍ���
_includePears(); // void|die

require_once P2_LIB_DIR . DIRECTORY_SEPARATOR . 'P2Util.php';
require_once P2_LIB_DIR . DIRECTORY_SEPARATOR . 'DataPhp.php';
require_once P2_LIB_DIR . DIRECTORY_SEPARATOR . 'Session.php';
require_once P2_LIB_DIR . DIRECTORY_SEPARATOR . 'Login.php';
require_once P2_LIB_DIR . DIRECTORY_SEPARATOR . 'UA.php';
require_once P2_LIB_DIR . DIRECTORY_SEPARATOR . 'P2View.php';

// }}}
// {{{ PEAR::PHP_Compat��PHP5�݊��̊֐���ǂݍ���

if (version_compare(phpversion(), '5.0.0', '<')) {
    PHP_Compat::loadFunction('file_put_contents');
    //PHP_Compat::loadFunction('clone');
    PHP_Compat::loadFunction('scandir');
    //PHP_Compat::loadFunction('http_build_query'); // ��3�����ɑΉ�����܂ł͎g���Ȃ�
    //PHP_Compat::loadFunction('array_walk_recursive');
}

// }}}

// �t�H�[������̓��́iPOST, GET�j���ꊇ�ŕ����R�[�h�ϊ����T�j�^�C�Y
_convertEncodingAndSanitizePostGet();

// �Ǘ��җp�ݒ��ǂݍ���
if (!include_once './conf/conf_admin.inc.php') {
    P2Util::printSimpleHtml("p2 error: �Ǘ��җp�ݒ�t�@�C����ǂݍ��߂܂���ł����B");
    die;
}

ini_set('default_socket_timeout', $_conf['fsockopen_time_limit']);

// �Ǘ��p�ۑ��f�B���N�g�� (�p�[�~�b�V������707)
$_conf['admin_dir'] = $_conf['data_dir'] . '/admin';

// cache �ۑ��f�B���N�g�� (�p�[�~�b�V������707)
$_conf['cache_dir'] = $_conf['data_dir'] . '/cache'; // 2005/6/29 $_conf['pref_dir'] . '/p2_cache' ���ύX

// �e���|�����f�B���N�g�� (�p�[�~�b�V������707)
$_conf['tmp_dir'] = $_conf['data_dir'] . '/tmp';

$_conf['accesskey_for_k'] = 'accesskey';

// {{{ �[������

$_conf['login_check_ip']  = 1; // �g�у��O�C������IP�A�h���X�����؂���

// ��{�iPC�j
$_conf['ktai'] = false;
$_conf['disable_cookie'] = false;

if (UA::isSafariGroup()) {
    $_conf['accept_charset'] = 'UTF-8';
} else {
    $_conf['accept_charset'] = 'Shift_JIS';
}

$mobile = &Net_UserAgent_Mobile::singleton();
if (PEAR::isError($mobile)) {
    trigger_error($mobile->toString(), E_USER_WARNING);

// UA���g�тȂ�
} elseif ($mobile and !$mobile->isNonMobile()) {

    require_once P2_LIB_DIR . '/HostCheck.php';
    
    $_conf['ktai'] = true;
    $_conf['accept_charset'] = 'Shift_JIS';

    // �x���_����
    // 2007/11/11 IP�`�F�b�N�͔F�؎��ɍs���������悳������
    // docomo i-Mode
    if ($mobile->isDoCoMo()) {
        if ($_conf['login_check_ip'] && !HostCheck::isAddrDocomo()) {
            P2Util::printSimpleHtml("p2 error: UA��docomo�ł����AIP�A�h���X�ш悪�}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
            die;
        }
        $_conf['disable_cookie'] = true;
        
    // EZweb (au or Tu-Ka)
    } elseif ($mobile->isEZweb()) {
        if ($_conf['login_check_ip'] && !HostCheck::isAddrAu()) {
            P2Util::printSimpleHtml("p2 error: UA��EZweb�ł����AIP�A�h���X�ш悪�}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
            die;
        }
        $_conf['disable_cookie'] = false;
        
    // SoftBank(��Vodafone Live!)
    } elseif ($mobile->isSoftBank()) {
        //$_conf['accesskey_for_k'] = 'DIRECTKEY';
        // W�^�[����3GC�^�[����Cookie���g����
        if ($mobile->isTypeW() || $mobile->isType3GC()) {
            $_conf['disable_cookie'] = false;
        } else {
            $_conf['disable_cookie'] = true;
        }
        if ($_conf['login_check_ip'] && !HostCheck::isAddrSoftBank()) {
            P2Util::printSimpleHtml("p2 error: UA��SoftBank�ł����AIP�A�h���X�ш悪�}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
            die;
        }

    // WILLCOM�i��AirH"Phone�j
    } elseif ($mobile->isWillcom()) {
        /*
        // WILLCOM�ł͒[��ID�F�؂��s��Ȃ��̂ŁA�R�����g�A�E�g
        if ($_conf['login_check_ip'] && !HostCheck::isAddrWillcom()) {
            P2Util::printSimpleHtml("p2 error: UA��AirH&quot;�ł����AIP�A�h���X�ш悪�}�b�`���܂���B({$_SERVER['REMOTE_ADDR']})");
            die;
        }
        */
        $_conf['disable_cookie'] = false;
        
    // ���̑�
    } else {
        $_conf['disable_cookie'] = true;
    }
}

// iPhone�w��
if (UA::isIPhoneGroup()) {
    $_conf['ktai'] = true;
    UA::setForceMode(UA::getMobileQuery());

    define('P2_IPHONE_LIB_DIR', './iphone');

    $_conf['ktai']           = true;
    $_conf['subject_php']    = 'subject_i.php';
    $_conf['read_new_k_php'] = 'read_new_i.php';
    $_conf['menu_k_php']     = 'menu_i.php';
    $_conf['editpref_php']   = 'editpref_i.php';
}

// }}}
// {{{ �N�G���[�ɂ�鋭���r���[�w��

// b=pc �͂܂��S�Ẵ����N�ɒǉ�����Ă��炸�A�@�\���Ȃ��ꍇ������B�n���ɐ������Ă��������B
// output_add_rewrite_var() �͕֗������A�o�͂��o�b�t�@����đ̊����x��������̂���_�B�B
// �̊����x�𗎂Ƃ��Ȃ��ǂ����@�Ȃ����ȁH

$b = UA::getQueryKey();

// ?k=1�͋��d�l�B?b=k���V�����B

// ����݊��p
if (!empty($_GET['k']) || !empty($_POST['k'])) {
    $_REQUEST[$b] = $_GET[$b] = 'k';
}

// $_conf[$b]�i$_conf['b']�j ���g��Ȃ��悤�ɂ��āAUA::getQueryValue()�𗘗p��������B
$_conf[$b] = UA::getQueryValue();

// $_conf['ktai'] �͎g��Ȃ������B
// UA::isK(), UA::isPC() �𗘗p����B

// ����PC�r���[�w��ib=pc�j
if (UA::isPCByQuery()) {
    $_conf['ktai'] = false;

// �����g�уr���[�w��ib=k�j
} elseif (UA::isMobileByQuery()) {
    $_conf['ktai'] = true;
}

// ��k_at_a, k_at_q, k_input_ht �͎g��Ȃ������B
// UA::getQueryKey(), UA::getQueryValue(), P2View::getInputHiddenKTag() �𗘗p����B
$_conf['k_at_a'] = '';
$_conf['k_at_q'] = '';
$_conf['k_input_ht'] = '';
if ($_conf[$b]) {
    //output_add_rewrite_var($b, htmlspecialchars($_conf[$b], ENT_QUOTES));

    $b_hs = hs($_conf[$b]);
    $_conf['k_at_a'] = "&amp;{$b}={$b_hs}";
    $_conf['k_at_q'] = "?{$b}={$b_hs}";
    $_conf['k_input_ht'] = P2View::getInputHiddenKTag();
}

// }}}

// 2008/09/28 $_conf['k_to_index_ht'] �͔p�~���āAP2View::getBackToIndexKATag() �𗘗p
// $_conf['k_to_index_ht'] = sprintf('<a %s="0" href="index.php%s">0.TOP</a>', $_conf['accesskey_for_k'], $_conf['k_at_q']);


//======================================================================

// {{{ ���[�U�ݒ� �Ǎ�

// �f�t�H���g�ݒ�iconf_user_def.inc.php�j��ǂݍ���
require_once './conf/conf_user_def.inc.php';
$_conf = array_merge($_conf, $conf_user_def);

// ���[�U�ݒ肪����Γǂݍ���
$_conf['conf_user_file'] = $_conf['pref_dir'] . '/conf_user.srd.cgi';

// 2006-02-27 ���`���t�@�C��������Εϊ����ăR�s�[
_copyOldConfUserFileIfExists();

$conf_user = array();
if (file_exists($_conf['conf_user_file'])) {
    if ($cont = file_get_contents($_conf['conf_user_file'])) {
        $conf_user = unserialize($cont);
        $_conf = array_merge($_conf, $conf_user);
    }
}

// }}}

$_conf['conf_user_style_inc_php']    = "./conf/conf_user_style.inc.php";

// �f�U�C���ݒ�i$STYLE�j�ǂݍ���

$_conf['skin_setting_path'] = $_conf['pref_dir'] . '/' . 'p2_user_skin.txt';
$_conf['skin_setting_perm'] = 0606;

_setStyle(); // $STYLE, $MYSTYLE

// {{{ �f�t�H���g�ݒ�

isset($_conf['rct_rec_num'])         or $_conf['rct_rec_num']       = 20;
isset($_conf['res_hist_rec_num'])    or $_conf['res_hist_rec_num']  = 20;
isset($_conf['posted_rec_num'])      or $_conf['posted_rec_num']    = 1000;
isset($_conf['before_respointer'])   or $_conf['before_respointer'] = 20;
isset($_conf['sort_zero_adjust'])    or $_conf['sort_zero_adjust']  = 0.1;
isset($_conf['display_threads_num']) or $_conf['display_threads_num'] = 150;
isset($_conf['cmp_dayres_midoku'])   or $_conf['cmp_dayres_midoku'] = 1;
isset($_conf['k_sb_disp_range'])     or $_conf['k_sb_disp_range']   = 30;
isset($_conf['k_rnum_range'])        or $_conf['k_rnum_range']      = 10;
isset($_conf['pre_thumb_height'])    or $_conf['pre_thumb_height']  = '32';
isset($_conf['quote_res_view'])      or $_conf['quote_res_view']    = 1;
isset($_conf['res_write_rec'])       or $_conf['res_write_rec']     = 1;

isset($STYLE['post_pop_size'])       or $STYLE['post_pop_size'] = '610,350';
isset($STYLE['post_msg_rows'])       or $STYLE['post_msg_rows'] = 10;
isset($STYLE['post_msg_cols'])       or $STYLE['post_msg_cols'] = 70;
isset($STYLE['info_pop_size'])       or $STYLE['info_pop_size'] = '600,380';

// }}}
// {{{ ���[�U�ݒ�̒�������

// $_conf['ext_win_target_at'], $_conf['bbs_win_target_at'] �͎g�p�����p�~�̕�����
$_conf['ext_win_target_at'] = '';
if ($_conf['ext_win_target']) {
    $_conf['ext_win_target_at'] = sprintf(' target="%s"',  htmlspecialchars($_conf['ext_win_target'], ENT_QUOTES));
}
/*
$_conf['bbs_win_target_at'] = '';
if ($_conf['bbs_win_target']) {
    $_conf['bbs_win_target_at'] = sprintf(' target="%s"',  htmlspecialchars($_conf['bbs_win_target'], ENT_QUOTES));
}
*/

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

if ($_conf['mobile.match_color']) {
    $_conf['k_filter_marker'] = "<font color=\"" . htmlspecialchars($_conf['mobile.match_color'], ENT_QUOTES) . '">\\0</font>';
} else {
    $_conf['k_filter_marker'] = null;
}


$_conf['output_callback'] = null;

// ob_start('mb_output_handler');

if (UA::isK() //&& $mobile && $mobile->isWillcom()
    // gzip�\���ǂ�����PHP�Ŕ��ʂ��Ă����͂�
    //&& !ini_get('zlib.output_compression') // �T�[�o�[�̐ݒ�Ŏ���gzip���k���L���ɂȂ��Ă��Ȃ�
    //&& strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') // �u���E�U��gzip���f�R�[�h�ł���
) {
    !defined('SID') || !strlen(SID) and $_conf['output_callback'] = 'ob_gzhandler';
}

// gzip���k����ƒ����o�͂͂ł��Ȃ�����
//!defined('SID') || !strlen(SID) and $_conf['output_callback'] = 'ob_gzhandler';

if ($_conf['output_callback']) {
    ob_start($_conf['output_callback']);
}

// ob_gzhandler ���p���A�o�b�t�@�������ԂŁAflush()���Ă��܂���gzip�]���ɂȂ�Ȃ��Ȃ�B���O��ob_flush()�������OK�B
//ob_flush();
//print_r(ob_list_handlers());
//print_r(getallheaders());

//======================================================================
// �ϐ��ݒ�
//======================================================================
// �ŋߓǂ񂾃X��
$_conf['recent_file']           = $_conf['pref_dir'] . '/p2_recent.idx';
// �݊��p
$_conf['recent_idx'] = $_conf['recent_file'];

$_conf['res_hist_idx']      = $_conf['pref_dir'] . '/p2_res_hist.idx';      // �������݃��O (idx)

// �������݃��O�t�@�C���idat�j
$_conf['p2_res_hist_dat']       = $_conf['pref_dir'] . '/p2_res_hist.dat';

// �������݃��O�t�@�C���i�f�[�^PHP�j��
$_conf['p2_res_hist_dat_php']   = $_conf['pref_dir'] . '/p2_res_hist.dat.php';

// �������݃��O�t�@�C���idat�j �Z�L�����e�B�ʕ�p
$_conf['p2_res_hist_dat_secu']  = $_conf['pref_dir'] . '/p2_res_hist.secu.cgi';

$_conf['cookie_dir']            = $_conf['pref_dir'] . '/p2_cookie'; // cookie �ۑ��f�B���N�g��

$_conf['favlist_file']          = $_conf['pref_dir'] . '/p2_favlist.idx';
// �݊��p
$_conf['favlist_idx'] = $_conf['favlist_file'];

$_conf['palace_file']           = $_conf['pref_dir'] . '/p2_palace.idx';
$_conf['favita_path']           = $_conf['pref_dir'] . '/p2_favita.brd';
$_conf['idpw2ch_php']           = $_conf['pref_dir'] . '/p2_idpw2ch.php';
$_conf['sid2ch_php']            = $_conf['pref_dir'] . '/p2_sid2ch.php';
$_conf['auth_user_file']        = $_conf['pref_dir'] . '/p2_auth_user.php';
$_conf['auth_ez_file']          = $_conf['pref_dir'] . '/p2_auth_ez.php';
$_conf['auth_jp_file']          = $_conf['pref_dir'] . '/p2_auth_jp.php';
$_conf['auth_docomo_file']      = $_conf['pref_dir'] . '/p2_auth_docomo.php';
$_conf['login_log_file']        = $_conf['pref_dir'] . '/p2_login.log.php';
$_conf['login_failed_log_file'] = $_conf['pref_dir'] . '/p2_login_failed.dat.php';

// saveMatomeCache() �̂��߂� $_conf['pref_dir'] ���΃p�X�ɕϊ�����
define('P2_PREF_DIR_REAL_PATH', File_Util::realPath($_conf['pref_dir']));

$_conf['matome_cache_path'] = P2_PREF_DIR_REAL_PATH . DIRECTORY_SEPARATOR . 'matome_cache';
$_conf['matome_cache_ext'] = '.htm';
$_conf['matome_cache_max'] = 3; // �\���L���b�V���̐�

// �␳
if (
    version_compare(phpversion(), '5.0.0', '<')
    or $_conf['expack.use_pecl_http'] && !extension_loaded('http')
) {
    //if (!($_conf['expack.use_pecl_http'] == 2 && $_conf['expack.dl_pecl_http'])) {
        $_conf['expack.use_pecl_http'] = 0;
    //}
} elseif ($_conf['expack.use_pecl_http'] == 3 && UA::isK()) {
    $_conf['expack.use_pecl_http'] = 1;
}


// {{{ ���肦�Ȃ������̃G���[

// �V�K���O�C���ƃ����o�[���O�C���̓����w��͂��肦�Ȃ��̂ŁA�G���[�o��
if (isset($_POST['submit_new']) && isset($_POST['submit_member'])) {
    P2Util::printSimpleHtml("p2 Error: ������URL�ł��B");
    die;
}

// }}}
// {{{ �z�X�g�`�F�b�N

if ($_conf['secure']['auth_host'] || $_conf['secure']['auth_bbq']) {
    require_once P2_LIB_DIR . '/HostCheck.php';
    if (($_conf['secure']['auth_host'] && HostCheck::getHostAuth() == FALSE) ||
        ($_conf['secure']['auth_bbq'] && HostCheck::getHostBurned() == TRUE)
    ) {
        HostCheck::forbidden();
    }
}

// }}}

// �Z�b�V�����̊J�n
$_p2session = _startSession();

// ���O�C���N���X�̃C���X�^���X�����i���O�C�����[�U���w�肳��Ă��Ȃ���΁A���̎��_�Ń��O�C���t�H�[���\���Ɂj
$_login = new Login;


//=============================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//=============================================================================

/**
 * �ċA�I��stripslashes��������
 * GET/POST/COOKIE�ϐ��p�Ȃ̂ŃI�u�W�F�N�g�̃v���p�e�B�ɂ͑Ή����Ȃ�
 * (ExUtil)
 *
 * @return  array|string
 */
function stripslashesR($var, $r = 0)
{
    $rlimit = 10;
    if (is_array($var) && $r < $rlimit) {
        foreach ($var as $key => $value) {
            $var[$key] = stripslashesR($value, ++$r);
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
 *
 * @return  array|string
 */
function nullfilterR($var, $r = 0)
{
    $rlimit = 10;
    if (is_array($var) && $r < $rlimit) {
        foreach ($var as $key => $value) {
            $var[$key] = nullfilterR($value, ++$r);
        }
    } elseif (is_string($var)) {
        $var = str_replace(chr(0), '', $var);
    }
    return $var;
}

/**
 * �������̎g�p�ʂ�\������
 *
 * @return  void
 */
function printMemoryUsage()
{
    $kb = memory_get_usage() / 1024;
    $kb = number_format($kb, 2, '.', '');
    
    echo 'Memory Usage: ' . $kb . 'KB';
}

/**
 * �����������l�̉����ݒ�(M)
 * �ݒ�l���w��l�����Ȃ�w��l�Ɉ����グ�Đݒ肷��
 *
 * @return  void
 */
function _setMemoryLimit($least_memory_limit_m = 32)
{
    if (preg_match('/^(\\d+)M$/', ini_get('memory_limit'), $m)) {
        if ($m[1] < $least_memory_limit_m) {
            ini_set('memory_limit', $least_memory_limit_m . 'M');
        }
    }
}

/**
 * �X�N���v�g�̎��s���Ԑ����̉����ݒ�(�b)
 * �ݒ�l���w��b�����Ȃ�w��b�Ɉ����グ�Đݒ肷��
 *
 * @return  void
 */
function _setTimeLimit($least_time_limit = 60)
{
    if ($t = ini_get('max_execution_time') and 0 < $t && $t < $least_time_limit) {
        if (!ini_get('safe_mode')) {
            set_time_limit($least_time_limit);
        }
    }
}

/**
 * @return  void  $GLOBALS['debug'], $GLOBALS['profiler']
 */
function _setDebug($debug = null)
{
    if (is_null($debug)) {
        $GLOBALS['debug'] = isset($_GET['debug']) ? intval($_GET['debug']) : 0;
    } else {
        $GLOBALS['debug'] = $debug;
    }
    if ($GLOBALS['debug']) {
        require_once 'Benchmark/Profiler.php';
        $GLOBALS['profiler'] = new Benchmark_Profiler(true);
        
        // 2007/08/03 Benchmark_Profiler 1.2.7 �� _Benchmark_Profiler ��PEAR���f�X�g���N�^���Ȃ��Ȃ��āA
        // close() �̎蓮���\�b�h�ɂȂ����H�̂ŁA�蓮�œo�^���Ă݂�B�Ȃ񂩕ςȋC�����邯�ǁB
        if (!method_exists($GLOBALS['profiler'], '_Benchmark_Profiler') && method_exists($GLOBALS['profiler'], 'close')) {
            register_shutdown_function(array($GLOBALS['profiler'], 'close'));
        }

        // printMemoryUsage();
        register_shutdown_function('printMemoryUsage');
    }
}

/**
 * �����R�[�h�̎w��
 *
 * @return  void
 */
function _setEncodings()
{
    // mb_detect_order("SJIS-win,eucJP-win,ASCII");
    mb_internal_encoding('SJIS-win');
    mb_http_output('pass');
    mb_substitute_character(63); // �����R�[�h�ϊ��Ɏ��s���������� "?" �ɂȂ�
    //mb_substitute_character(0x3013); // ��

    ini_set('default_mimetype', 'text/html');
    ini_set('default_charset', 'Shift_JIS');

    if (function_exists('mb_ereg_replace')) {
        define('P2_MBREGEX_AVAILABLE', 1);
        @mb_regex_encoding('SJIS-win');
    } else {
        define('P2_MBREGEX_AVAILABLE', 0);
    }
}

/**
 * �����p�X���Z�b�g����
 * P2_PEAR_HACK_DIR, P2_PEAR_DIR
 *
 * @return  void
 */
function _iniSetIncludePath()
{
    $include_path = '.';
    if (is_dir(P2_PEAR_HACK_DIR)) {
        $include_path .= PATH_SEPARATOR . realpath(P2_PEAR_HACK_DIR);
    }
    $include_path .= PATH_SEPARATOR . ini_get('include_path');
    if (is_dir(P2_PEAR_DIR)) {
        $include_path .= PATH_SEPARATOR . realpath(P2_PEAR_DIR);
    }
    //$include_path .= PATH_SEPARATOR . realpath(P2_LIB_DIR);
    ini_set('include_path', $include_path);
}

/**
 * PHP�̓�������m�F
 *
 * @return  void|die
 */
function _checkPHPInstalled()
{
    $errmsgs = array();
    if (version_compare(phpversion(), '4.3.0', 'lt')) {
        $errmsgs[] = 'PHP�̃o�[�W������4.3.0�����ł͎g���܂���B';
    }
    if (ini_get('safe_mode')) {
        $errmsgs[] = '�Z�[�t���[�h�œ��삷��PHP�ł͎g���܂���B';
    }
    if (!extension_loaded('mbstring')) {
        $errmsgs[] = 'PHP�̃C���X�g�[�����s�\���ł��BPHP��mbstring�g�����W���[�������[�h����Ă��܂���B';
    }
    if ($errmsgs) {
        $errmsgHtmls = array_map('htmlspecialchars', $errmsgs);
        die(sprintf(
            '<html><body><h3>p2 install error</h3><p>%s</p></body></html>',
            implode('<br>', $errmsgHtmls)
        ));
    }
}

/**
 * PEAR���C�u������ǂݍ���
 *
 * @return  void|die
 */
function _includePears()
{
    global $_conf;
    
    $requiredPears = array(
        'File/Util.php'             => 'File',
        'Net/UserAgent/Mobile.php'  => 'Net_UserAgent_Mobile',
        'PHP/Compat.php'            => 'PHP_Compat',
        'HTTP/Request.php'          => 'HTTP_Request'
    );
    foreach ($requiredPears as $pear_file => $pear_pkg) {
        if (!include_once($pear_file)) {
            $url = 'http://akid.s17.xrea.com/p2puki/pukiwiki.php?PEAR%A4%CE%A5%A4%A5%F3%A5%B9%A5%C8%A1%BC%A5%EB';
            $url_t = $_conf['p2ime_url'] . '?enc=1&url=' . rawurlencode($url);
            die(sprintf(
                '<html><body>
                <h3>p2 install error: PEAR �́u%s�v���C���X�g�[������Ă��܂���</h3>
                <p><a href="%s" target="_blank">p2Wiki: PEAR�̃C���X�g�[��</a></p>
                </body></html>',
                hs($pear_pkg), hs($url_t)
            ));
        }
    }
}

/**
 * OS�ʂ̒萔���Z�b�g����
 *
 * @return  void
 */
function _setOSDefine()
{
    // OS����
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // Windows
        defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ';');
        defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '\\');
    } else {
        defined('PATH_SEPARATOR') or define('PATH_SEPARATOR', ':');
        defined('DIRECTORY_SEPARATOR') or define('DIRECTORY_SEPARATOR', '/');
    }
}

/**
 * @return  void
 */
function _setStyle()
{
    global $_conf, $STYLE, $MYSTYLE;
    
    // �f�t�H���gCSS�ݒ�i$STYLE, $MYSTYLE�j��ǂݍ���
    include_once $_conf['conf_user_style_inc_php'];

    if ($_conf['skin'] = P2Util::getSkinSetting()) {
        // �X�L����$STYLE���㏑��
        $skinfile = P2Util::getSkinFilePathBySkinName($_conf['skin']);
        if (file_exists($skinfile)) {
            include_once $skinfile;
        }
    }

    // $STYLE�ݒ�̒�������
    //if ($_SERVER['SCRIPT_NAME'] == 'css.php') {
        foreach ($STYLE as $k => $v) {
            if (empty($v)) {
                $STYLE[$k] = '';
            } elseif (strpos($k, 'fontfamily') !== false) {
                $STYLE[$k] = p2_correct_css_fontfamily($v);
            } elseif (strpos($k, 'color') !== false) {
                $STYLE[$k] = p2_correct_css_color($v);
            } elseif (strpos($k, 'background') !== false) {
                $STYLE[$k] = 'url("' . p2_escape_css_url($v) . '")';
            }
        }
    //}
}

/**
 * �t�H�[������̓��͂��ꊇ�ŃN�H�[�g�����������R�[�h�ϊ�
 * �t�H�[����accept-encoding������UTF-8(Safari�n) or Shift_JIS(���̑�)�ɂ��A
 * �����hidden�v�f�Ŕ����e�[�u���̕������d���ނ��ƂŌ딻������炷
 * �ϊ�������eucJP-win������̂�HTTP���͂̕����R�[�h��EUC�Ɏ����ϊ������T�[�o�̂���
 */
function _convertEncodingAndSanitizePostGet()
{
    if (!empty($_POST)) {
        if (get_magic_quotes_gpc()) {
            $_POST = array_map('stripslashesR', $_POST);
        }
        mb_convert_variables('SJIS-win', 'UTF-8,eucJP-win,SJIS-win', $_POST);
        $_POST = array_map('nullfilterR', $_POST);
    }
    if (!empty($_GET)) {
        if (get_magic_quotes_gpc()) {
            $_GET = array_map('stripslashesR', $_GET);
        }
        mb_convert_variables('SJIS-win', 'UTF-8,eucJP-win,SJIS-win', $_GET);
        $_GET = array_map('nullfilterR', $_GET);
    }
}

/**
 * 2006-02-27 ���`���t�@�C��������Εϊ����ăR�s�[
 *
 * @return  void
 */
function _copyOldConfUserFileIfExists()
{
    global $_conf;
    
    $conf_user_file_old = $_conf['pref_dir'] . '/conf_user.inc.php';
    if (!file_exists($_conf['conf_user_file']) && file_exists($conf_user_file_old)) {
        $old_cont = DataPhp::getDataPhpCont($conf_user_file_old);
        FileCtl::make_datafile($_conf['conf_user_file'], $_conf['conf_user_perm']);
        file_put_contents($_conf['conf_user_file'], $old_cont);
    }
}

/**
 * @return  Session|null
 */
function _startSession()
{
    global $_conf;
    
    // ���O�́A�Z�b�V�����N�b�L�[��j������Ƃ��̂��߂ɁA�Z�b�V�������p�̗L���Ɋւ�炸�ݒ肷��
    session_name('PS');

    // �Z�b�V�����f�[�^�ۑ��f�B���N�g�����K��
    if ($_conf['session_save'] == 'p2' and session_module_name() == 'files') {
        // $_conf['data_dir'] ���΃p�X�ɕϊ�����
        define('P2_DATA_DIR_REAL_PATH', File_Util::realPath($_conf['data_dir']));
        $_conf['session_dir'] = P2_DATA_DIR_REAL_PATH . DIRECTORY_SEPARATOR . 'session';
    }

    // css.php �͓��ʂɃZ�b�V��������O���B
    //if (basename($_SERVER['SCRIPT_NAME']) != 'css.php') {
        if ($_conf['use_session'] == 1 or ($_conf['use_session'] == 2 && empty($_COOKIE['cid']))) { 
    
            // {{{ �Z�b�V�����f�[�^�ۑ��f�B���N�g����ݒ�
        
            if ($_conf['session_save'] == 'p2' and session_module_name() == 'files') {

                if (!is_dir($_conf['session_dir'])) {
                    require_once P2_LIB_DIR . '/FileCtl.php';
                    FileCtl::mkdirFor($_conf['session_dir'] . '/dummy_filename');
                } elseif (!is_writable($_conf['session_dir'])) {
                    die(sprintf(
                        'p2 error: �Z�b�V�����f�[�^�ۑ��f�B���N�g�� (%s) �ɏ������݌���������܂���B',
                        hs($_conf['session_dir'])
                    ));
                }

                session_save_path($_conf['session_dir']);

                // session.save_path �̃p�X�̐[����2���傫���ƃK�[�x�b�W�R���N�V�������s���Ȃ��̂�
                // ���O�ŃK�[�x�b�W�R���N�V��������
                P2Util::session_gc();
            }
        
            // }}}

            return new Session;
        }
    //}
    
    return null;
}


/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
