<?php
// p2 �V�X�e���ݒ�
// ���̃t�@�C���́A���ɗ��R�̖�������ύX���Ȃ��ŉ������B
// include from conf.inc.php

$_conf['p2version'] = '1.8.61'; // rep2�̃o�[�W����

$_conf['p2name'] = 'rep2';    // rep2�̖��O�B

$_conf['p2uaname'] = 'r e p 2';  // UA�p��rep2�̖��O

//======================================================================
// ��{�ݒ菈��
//======================================================================
// �G���[�o�͐ݒ�
_setErrorReporting(); // error_reporting()

// �f�o�b�O�p�ϐ���ݒ�
_setDebug(); // void  $GLOBALS['debug'], $GLOBALS['profiler']

// PHP�̓�������m�F
_checkPHPInstalled(); // void|die

// PHP�̊��ݒ�
_setPHPEnvironments();

// p2�̃f�B���N�g���p�X�萔��ݒ肷��
_setP2DirConstants(); // P2_LIB_DIR ��

require_once P2_LIB_DIR . '/global.funcs.php';

// �����p�X���Z�b�g
_iniSetIncludePath(); // void

// PEAR���C�u������ǂݍ���
_includePears(); // void|die

// PEAR::PHP_Compat��PHP5�݊��̊֐���ǂݍ���
_loadPHPCompat();

require_once P2_LIB_DIR . DIRECTORY_SEPARATOR . 'UriUtil.php';
require_once P2_LIB_DIR . DIRECTORY_SEPARATOR . 'P2Util.php';
require_once P2_LIB_DIR . DIRECTORY_SEPARATOR . 'DataPhp.php';
require_once P2_LIB_DIR . DIRECTORY_SEPARATOR . 'Session.php';
require_once P2_LIB_DIR . DIRECTORY_SEPARATOR . 'Login.php';
require_once P2_LIB_DIR . DIRECTORY_SEPARATOR . 'UA.php';
require_once P2_LIB_DIR . DIRECTORY_SEPARATOR . 'P2View.php';
require_once P2_LIB_DIR . DIRECTORY_SEPARATOR . 'FileCtl.php';

// }}}

// �t�H�[������̓��́iPOST, GET�j���ꊇ�ŕ����R�[�h�ϊ����T�j�^�C�Y
_convertEncodingAndSanitizePostGet();

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

// �Ǘ��җp�ݒ��ǂݍ���
if (!require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'conf_admin.inc.php') {
    P2Util::printSimpleHtml("p2 error: �Ǘ��җp�ݒ�t�@�C����ǂݍ��߂܂���ł����B");
    trigger_error('!include_once conf_admin.inc.php', E_USER_ERROR);
    die;
}

ini_set('default_socket_timeout', $_conf['default_socket_timeout']);

// cache �ۑ��f�B���N�g�� (�p�[�~�b�V������707)
$_conf['cache_dir'] = $_conf['data_dir'] . '/cache'; // 2005/6/29 $_conf['pref_dir'] . '/p2_cache' ���ύX

// �e���|�����f�B���N�g�� (�p�[�~�b�V������707)
$_conf['tmp_dir'] = $_conf['data_dir'] . '/tmp';

// �Ǘ��p�ۑ��f�B���N�g�� (�p�[�~�b�V������707)
// 2010/02/01 �g���̐ݒ�B�g�p���Ă��Ȃ��B
$_conf['admin_dir'] = $_conf['data_dir'] . '/admin';

$_conf['accesskey_for_k'] = 'accesskey';

// �[������
_checkBrowser(); // $_conf, UA::setForceMode()

// b=pc �͂܂��S�Ẵ����N�ւ̒ǉ����������Ă��炸�A�@�\���Ă��Ȃ��ӏ�������B�n���ɐ������Ă��������B
// output_add_rewrite_var() �͕֗������A�o�͂��o�b�t�@����đ̊����x��������̂���_�B�B
// �̊����x�𗎂Ƃ��Ȃ��ǂ����@�Ȃ����ȁH
_setOldStyleKtaiQuery(); // $_conf['ktai'] �����Z�b�g

// $_conf['expack.use_pecl_http'] �̒���
_adjustConfUsePeclHttp(); // UA::isK()


// ���̃t�@�C�����ł̏����͂����܂�


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
 * �G���[�o�͐ݒ�Berror_reporting()
 * �iNOTICE�͍팸�������A�܂��c���Ă���Ǝv���j
 *
 * @return  void
 */
function _setErrorReporting()
{
    $except = E_NOTICE;
    if (defined('E_STRICT')) {
        $except = $except | E_STRICT;
    }
    if (defined('E_DEPRECATED')) {
        $except = $except | E_DEPRECATED;
    }
    error_reporting(E_ALL & ~$except);
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
 * PHP�̊��ݒ�
 *
 * @return  void
 */
function _setPHPEnvironments()
{
    // �^�C���]�[�����Z�b�g
    _setTimezone();

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

    // OS�ʂ̒萔��⊮�Z�b�g����BPATH_SEPARATOR, DIRECTORY_SEPARATOR
    _setOSDefine();

    // �����R�[�h�̎w��
    _setEncodings();
}

/**
 * @return  void
 */
function _setTimezone()
{
    if (function_exists('date_default_timezone_set')) { 
        date_default_timezone_set('Asia/Tokyo'); 
    } else { 
        @putenv('TZ=JST-9'); 
    }
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
 * OS�ʂ̒萔��⊮�Z�b�g����
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
 * p2�̃f�B���N�g���p�X�萔��ݒ肷��
 *
 * @return  void
 */
function _setP2DirConstants()
{
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
 * @return  void
 */
function _loadPHPCompat()
{
    if (version_compare(phpversion(), '5.0.0', '<')) {
        PHP_Compat::loadFunction('file_put_contents');
        //PHP_Compat::loadFunction('clone');
        PHP_Compat::loadFunction('scandir');
        //PHP_Compat::loadFunction('http_build_query'); // ��3�����ɑΉ�����܂ł͎g���Ȃ�
        //PHP_Compat::loadFunction('array_walk_recursive');
    }
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
 * �[������
 *
 * @return  void
 */
function _checkBrowser()
{
    global $_conf;
    
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

        $_conf['ktai'] = true;
        $_conf['disable_cookie'] = false;
        $_conf['accept_charset'] = 'Shift_JIS';

        // �x���_����
        // docomo i-Mode
        if ($mobile->isDoCoMo()) {
            // [todo] docomo�̐V�����̂�Cookie���g����c
            $_conf['disable_cookie'] = true;
        
        // EZweb (au or Tu-Ka)
        } elseif ($mobile->isEZweb()) {
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

        // WILLCOM�i��AirH"Phone�j
        } elseif ($mobile->isWillcom()) {
            $_conf['disable_cookie'] = false;
        }
    }

    // iPhone�w��
    if (UA::isIPhoneGroup()) {
        $_conf['ktai'] = true;
        UA::setForceMode(UA::getMobileQuery());

        define('P2_IPHONE_LIB_DIR', './iphone');

        $_conf['subject_php']    = 'subject_i.php';
        $_conf['read_new_k_php'] = 'read_new_i.php';
        $_conf['menu_k_php']     = 'menu_i.php';
        $_conf['editpref_php']   = 'editpref_i.php';
    }
}

/**
 * ���X�^�C���̌g�уr���[�ϐ� $_conf['ktai'] �����Z�b�g
 *
 * @return  void
 */
function _setOldStyleKtaiQuery()
{
    global $_conf;
    
    $b = UA::getQueryKey();

    // ?k=1�͋��d�l�B?b=k���V�����B
    // ����݊��p�[�u
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
}

/**
 * $_conf['expack.use_pecl_http'] �̒���
 *
 * @return  void
 */
function _adjustConfUsePeclHttp()
{
    global $_conf;
    
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
}
