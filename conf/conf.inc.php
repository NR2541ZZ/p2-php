<?php
/*
    rep2 - ��{�ݒ�t�@�C��

    ���̃t�@�C���̓V�X�e�������ݒ�p�ł��B���ɗ��R�̖�������ύX���Ȃ��ŉ������B
    ���[�U�ݒ�́A�u���E�U�ォ��u���[�U�ݒ�ҏW�v�ŕύX�\�ł��B
    �Ǘ��Ҍ����ݒ�̓t�@�C�� conf/conf_admin.inc.php �𒼐ڏ��������ĉ������B
*/


// �V�X�e���ݒ��ǂݍ���
if (!include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'conf_system.inc.php') {
    die("p2 error: �Ǘ��җp�ݒ�t�@�C����ǂݍ��߂܂���ł����B");
}

// �ȉ��A���[�U�Ώۂ̐ݒ�
//�iconf_user.inc.php������Ă܂Ƃ߂������A�̎g���Ă����t�@�C�����Ƃ��Ԃ�̂Ŗ����Ă���j

// {{{ ���[�U�ݒ� �Ǎ�

// �f�t�H���g�ݒ�iconf_user_def.inc.php�j��ǂݍ���
require_once P2_CONF_DIR . DIRECTORY_SEPARATOR . 'conf_user_def.inc.php';
$_conf = array_merge($_conf, $conf_user_def);

// ���[�U�ݒ肪����Γǂݍ���
$_conf['conf_user_file'] = $_conf['pref_dir'] . '/conf_user.srd.cgi';

// 2006-02-27 ���`���t�@�C��������Εϊ����ăR�s�[
//_copyOldConfUserFileIfExists();

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
$_conf['cookie_dir']            = $_conf['pref_dir'] . '/p2_cookie'; // cookie �ۑ��f�B���N�g��

// �ŋߓǂ񂾃X��
$_conf['recent_file']           = $_conf['pref_dir'] . '/p2_recent.idx';
// �݊��p
$_conf['recent_idx']            = $_conf['recent_file'];

$_conf['res_hist_idx']          = $_conf['pref_dir'] . '/p2_res_hist.idx';      // �������݃��O (idx)

// �������݃��O�t�@�C���idat�j
$_conf['p2_res_hist_dat']       = $_conf['pref_dir'] . '/p2_res_hist.dat';

// �������݃��O�t�@�C���i�f�[�^PHP�j��
$_conf['p2_res_hist_dat_php']   = $_conf['pref_dir'] . '/p2_res_hist.dat.php';

// �������݃��O�t�@�C���idat�j �Z�L�����e�B�ʕ�p
$_conf['p2_res_hist_dat_secu']  = $_conf['pref_dir'] . '/p2_res_hist.secu.cgi';

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


// {{{ ���肦�Ȃ������̃G���[

// �V�K���O�C���ƃ����o�[���O�C���̓����w��͂��肦�Ȃ��̂ŁA�G���[�o��
if (isset($_POST['submit_newuser']) && isset($_POST['submit_userlogin'])) {
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
// �i[todo]���̏�������Ɏ����čs���������A���[�U�[���O�C�����V�K�o�^�ǂ����̋�ʂ��ł��Ȃ��Ȃ�B
// login_first.inc.php��file_exists($_conf['auth_user_file']) �ŐV�K�o�^���ǂ����𔻒肵�Ă���̂����߂�K�v������)
$_login = new Login;

// ���̃t�@�C�����ł̏����͂����܂�


//=============================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//=============================================================================

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
 * @return  Session|null|die
 */
function _startSession()
{
    global $_conf;
    
    // ���O�́A�Z�b�V�����N�b�L�[��j������Ƃ��̂��߂ɁA�Z�b�V�������p�̗L���Ɋւ�炸�ݒ肷��
    session_name('PS');

    $cookie = session_get_cookie_params();
    session_set_cookie_params($cookie['lifetime'], '/', P2Util::getCookieDomain(), $secure = false);
    
    // css.php �͓��ʂɃZ�b�V��������O���B
    //if (basename($_SERVER['SCRIPT_NAME']) == 'css.php') {
    //    return null;
    //}
    
    if ($_conf['use_session'] == 1 or ($_conf['use_session'] == 2 && empty($_COOKIE['cid']))) { 

        if ($_conf['session_save'] == 'p2' and session_module_name() == 'files') {
            _prepareFileSession();
        }

        return new Session;
    }
    return null;
}

/**
 * @return  void
 */
function _prepareFileSession()
{
    global $_conf;
    
    // �Z�b�V�����f�[�^�ۑ��f�B���N�g����ݒ�
    if ($_conf['session_save'] == 'p2' and session_module_name() == 'files') {
        // $_conf['data_dir'] ���΃p�X�ɕϊ�����
        define('P2_DATA_DIR_REAL_PATH', File_Util::realPath($_conf['data_dir']));
        $_conf['session_dir'] = P2_DATA_DIR_REAL_PATH . DIRECTORY_SEPARATOR . 'session';
    }
    
    if (!is_dir($_conf['session_dir'])) {
        require_once P2_LIB_DIR . '/FileCtl.php';
        FileCtl::mkdirFor($_conf['session_dir'] . '/dummy_filename');
    }
    if (!is_writable($_conf['session_dir'])) {
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
