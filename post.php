<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

/*
    p2 - ���X��������
*/

require_once './conf/conf.inc.php';

require_once P2_LIB_DIR . '/DataPhp.php';
require_once P2_LIB_DIR . '/FileCtl.php';
require_once P2_LIB_DIR . '/P2Validate.php';

$_login->authorize(); // ���[�U�F��

if (!empty($_conf['disable_res'])) {
    p2die('�������݋@�\�͖����ł��B');
}

if (!isset($_POST['csrfid']) or $_POST['csrfid'] != P2Util::getCsrfId()) {
    p2die('�y�[�W�J�ڂ̑Ó������m�F�ł��܂���ł����B�iCSRF�΍�j', '���e�t�H�[����ǂݍ��ݒ����Ă���A���߂ē��e���Ă��������B');
}

//================================================================
// �ϐ�
//================================================================
$newtime = date('gis');

$post_keys = array(
    'FROM', 'mail', 'MESSAGE',
    'bbs', 'key', 'time',
    'host', 'popup', 'rescount',
    'subject', 'submit',
    'sub',
    'ttitle_en'
);

foreach ($post_keys as $pk) {
    ${$pk} = isset($_POST[$pk]) ? $_POST[$pk] : null;
}

// �����G���[
if (empty($host)) {
    p2die('�����̎w�肪�ςł�');
}
if (P2Validate::host($host) || ($bbs) && P2Validate::bbs($bbs) || ($key) && P2Validate::host($key)) {
    p2die('�s���Ȉ����ł�');
}

if ($bbs and _isThreTateSugi()) {
    p2die('�X�����Đ��ł��i���΂��҂����j');
}

$_conf['last_post_time_file'] = $_conf['pref_dir'] . '/last_post_time.txt';
if (P2Util::isHost2chs($host)) {
    $server_id = preg_replace('{\.2ch\.net$}', '', $host);
    $_conf['last_post_time_file'] = P2Util::idxDirOfHost($host) . '/' . rawurlencode($server_id) . '_' . 'last_post_time.txt';
}

if (!isset($ttitle)) {
    if ($ttitle_en) {
        $ttitle = base64_decode($ttitle_en);
    } elseif ($subject) {
        $ttitle = $subject;
    } else {
        $ttitle = '';
    }
}

// �i�ݒ�ɉ����āj�\�[�X�R�[�h��HTML��ł����ꂢ�ɍČ������悤�ɁAPOST���b�Z�[�W��ϊ�����
$MESSAGE = _formatCodeToPost($MESSAGE);

// ������΂�livedoor�ړ]�ɑΉ��Bpost���livedoor�Ƃ���B
$host = P2Util::adjustHostJbbsShitaraba($host);

// machibbs�AJBBS@������� �Ȃ�
if (P2Util::isHostMachiBbs($host) or P2Util::isHostJbbsShitaraba($host)) {
    $bbs_cgi = '/bbs/write.cgi';
    
    // JBBS@������� �Ȃ�
    if (P2Util::isHostJbbsShitaraba($host)) {
        $bbs_cgi = '/../bbs/write.cgi';
        preg_match('/(\w+)$/', $host, $ar);
        $dir = $ar[1];
        $dir_k = 'DIR';
    }
    
    $submit_k  = 'submit';
    $bbs_k     = 'BBS';
    $key_k     = 'KEY';
    $time_k    = 'TIME';
    $FROM_k    = 'NAME';
    $mail_k    = 'MAIL';
    $MESSAGE_k = 'MESSAGE';
    $subject_k = 'SUBJECT';

// cha2�Ȃ�
} elseif (P2Util::isHostCha2($host)) {
    $bbs_cgi = '/cgi-bin/test/bbs.cgi';

    $submit_k  = 'submit';
    $bbs_k     = 'bbs';
    $key_k     = 'key';
    $time_k    = 'time';
    $FROM_k    = 'FROM';
    $mail_k    = 'mail';
    $MESSAGE_k = 'MESSAGE';
    $subject_k = 'SUBJECT';

// 2ch�n�Ȃ�
} else { 
    if ($sub) {
        $bbs_cgi = "/test/{$sub}bbs.cgi";
    } else {
        $bbs_cgi = '/test/bbs.cgi';
    }
    $submit_k  = 'submit';
    $bbs_k     = 'bbs';
    $key_k     = 'key';
    $time_k    = 'time';
    $FROM_k    = 'FROM';
    $mail_k    = 'mail';
    $MESSAGE_k = 'MESSAGE';
    $subject_k = 'subject';

}

$post_cache = array(
    'bbs' => $bbs, 'key' => $key,
    'FROM' => $FROM, 'mail' => $mail, 'MESSAGE' => $MESSAGE,
    'subject' => $subject,
    'time' => $time
);

// submit �͏������ނŌŒ肵�Ă��܂��iBe�ŏ������ނ̏ꍇ�����邽�߁j
$submit = '��������';

if (!empty($_POST['newthread'])) {
    $post = array(
        $subject_k => $subject,
        $FROM_k => $FROM, $mail_k => $mail, $MESSAGE_k => $MESSAGE,
        $bbs_k  => $bbs,
        $submit_k => $submit,
        $time_k => $time
    );
    if (P2Util::isHostJbbsShitaraba($host)) {
        $post[$dir_k] = $dir;
    }
    $qs_sid = $qs = array(
        'host' => $host,
        'bbs'  => $bbs,
        UA::getQueryKey() => UA::getQueryValue()
    );
    if (defined('SID') && strlen(SID)) {
        $qs_sid[session_name()] = session_id();
    }
    
    $location_url     = P2Util::buildQueryUri($_conf['subject_php'], $qs);
    $location_sid_url = P2Util::buildQueryUri($_conf['subject_php'], $qs_sid);
    
} else {
    $post = array(
        $FROM_k => $FROM, $mail_k => $mail, $MESSAGE_k => $MESSAGE,
        $bbs_k  => $bbs,
        $key_k  => $key,
        $submit_k => $submit,
        $time_k => $time
    );
    if (P2Util::isHostJbbsShitaraba($host)) {
        $post[$dir_k] = $dir;
    }
    $qs_sid = $qs = array(
        'host' => $host,
        'bbs'  => $bbs,
        'key'  => $key,
        'ls'   => "$rescount-",
        'refresh' => 1,
        'nt'   => $newtime,
        UA::getQueryKey() => UA::getQueryValue()
    );
    if (defined('SID') && strlen(SID)) {
        $qs_sid[session_name()] = session_id();
    }
    
    $location_url     = P2Util::buildQueryUri($_conf['read_php'], $qs) . "#r{$rescount}";
    $location_sid_url = P2Util::buildQueryUri($_conf['read_php'], $qs_sid) . "#r{$rescount}";
}

// {{{ 2ch�Ł����O�C�����Ȃ�sid�ǉ�

if (!empty($_POST['maru_kakiko']) and P2Util::isHost2chs($host) && file_exists($_conf['sid2ch_php'])) {
    
    // ���O�C����A24���Ԉȏ�o�߂��Ă����玩���ă��O�C��
    if (file_exists($_conf['idpw2ch_php']) and filemtime($_conf['sid2ch_php']) < time() - 60*60*24) {
        require_once P2_LIB_DIR . '/login2ch.func.php';
        login2ch();
    }
    
    if ($r = _getSID2ch()) {
        $post['sid'] = $r;
    }
}

// }}}

// for hana mogera�B�N�b�L�[�m�F��ʂł�post�A���̌��cookie�Ƃ����d�l�炵���B
foreach ($_POST as $k => $v) {
    if (!isset($post[$k]) and !in_array($k, $post_keys)) {
        $post[$k] = $_POST[$k];
    }
}


if (!empty($_POST['newthread'])) {
    $ptitle = "p2 - �V�K�X���b�h�쐬";
} else {
    $ptitle = "p2 - ���X��������";
}

//================================================================
// ���C������
//================================================================

// �|�X�g���s
$posted = _postIt($host, $bbs, $key, $post, $bbs_cgi); // @return  boolean|string

// �ŏI���e���Ԃ��L�^���� �m�F����
if ($posted === true) {
    _recLastPostTime('SUCCESS');

// �N�b�L�[�Ȃ玎�s���Ԃ�߂�
} elseif ($posted === 'COOKIE') {
    _recLastPostTime('COOKIE');

// ���̑��̃G���[�͘A�łŔ�������P�[�X������̂Ŗ߂��Ȃ�
} else {
    _recLastPostTime();
}

// �X�����Đ����Ȃ�Asubject����key���擾
if (!empty($_POST['newthread']) && $posted === true) {
    sleep(1);
    $key = _getKeyInSubject();
}


$tagCsv = array();

// <> ���O���B�B
$tagCsv['FROM'] = str_replace('<>', '', $FROM);
$tagCsv['mail'] = str_replace('<>', '', $mail);

// ���O�ƃ��[���A�󔒎��� P2NULL ���L�^
$tagCsvF['FROM'] = ($tagCsv['FROM'] == '') ? 'P2NULL' : $tagCsv['FROM'];
$tagCsvF['mail'] = ($tagCsv['mail'] == '') ? 'P2NULL' : $tagCsv['mail'];

// key.idx�ɖ��O�ƃ��[����ۑ�����
if ($host && $bbs && $key) {
    _recKeyIdx($host, $bbs, $key, $tagCsvF);
}


if ($posted !== true) {
    exit;
}

// �������ݗ������L�^����
if ($host && $bbs && $key) {
    _recResHist($host, $bbs, $key, $tagCsv, $ttitle);
}

// �������݃��O�L�^

$tagCsv['message'] = _formatMessageTagCvs($MESSAGE);

if ($_conf['res_write_rec']) {
    _recResLog($tagCsv['FROM'], $tagCsv['mail'], $tagCsv['message'], $ttitle, $host, $bbs, $key, $rescount);
}

_recResLogSecu($tagCsv['FROM'], $tagCsv['mail'], $tagCsv['message'], $ttitle, $host, $bbs, $key, $rescount);

exit;


//=============================================================================
// �֐� �i���̃t�@�C�����ł̂ݗ��p�j
//=============================================================================

/**
 * key.idx�ɖ��O�ƃ��[����ۑ�����
 *
 * @return  void
 */
function _recKeyIdx($host, $bbs, $key, $tagCsvF)
{
    if (!($host && $bbs && $key)) {
        return;
    }
    
    $idx_host_dir = P2Util::idxDirOfHost($host);
    $keyidx = $idx_host_dir . '/' . $bbs . '/' . $key . '.idx';
    
    $akeyline = array();
    if (file_exists($keyidx) and $keylines = file($keyidx)) {
        $akeyline = explode('<>', rtrim($keylines[0]));
    }
    $sar = array(
        $akeyline[0], $akeyline[1], $akeyline[2], $akeyline[3], $akeyline[4],
        $akeyline[5], $akeyline[6], $tagCsvF['FROM'], $tagCsvF['mail'], $akeyline[9],
        $akeyline[10], $akeyline[11], $akeyline[12]
    );
    P2Util::recKeyIdx($keyidx, $sar);
}

/**
 * �������ݗ������L�^����
 *
 * @return  void
 */
function _recResHist($host, $bbs, $key, $tagCsv, $ttitle)
{
    global $_conf;
    
    if (!($host && $bbs && $key)) {
        return;
    }
    
    $rh_idx = $_conf['pref_dir'] . '/p2_res_hist.idx';
    FileCtl::make_datafile($rh_idx, $_conf['res_write_perm']);
    
    $lines = file($rh_idx);
    $neolines = array();
    
    // �ŏ��ɏd���v�f���폜���Ă���
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $line = rtrim($line);
            $lar = explode('<>', $line);
            if ($lar[1] == $key) { continue; } // �d�����
            if (!$lar[1]) { continue; } // key�̂Ȃ����͕̂s���f�[�^
            $neolines[] = $line;
        }
    }
    
    // �V�K�f�[�^�ǉ�
    $newdata = "$ttitle<>$key<><><><><><>" . $tagCsv['FROM'] . '<>' . $tagCsv['mail'] . "<><>$host<>$bbs";
    array_unshift($neolines, $newdata);
    while (sizeof($neolines) > $_conf['res_hist_rec_num']) {
        array_pop($neolines);
    }
    
    // ��������
    if ($neolines) {
        $cont = '';
        foreach ($neolines as $l) {
            $cont .= $l . "\n";
        }
        
        if (false === FileCtl::filePutRename($rh_idx, $cont)) {
            $errmsg = sprintf('p2 error: %s(), FileCtl::filePutRename() failed.', __FUNCTION__);
            trigger_error($errmsg, E_USER_WARNING);
            //return false;
        }
    }
}

/**
 * �^�OCSV�L�^�̂��߂Ƀ��b�Z�[�W���t�H�[�}�b�g�ϊ�����
 *
 * @return  string
 */
function _formatMessageTagCvs($message)
{
    $message = htmlspecialchars($message, ENT_NOQUOTES);
    return preg_replace("/\r?\n/", "<br>", $message);
}

/**
 * �������݃��O���L�^����
 *
 * @param   string   $from     �^�OCVS�L�^�̂��߂Ƀt�H�[�}�b�g�ς݂ł��邱��
 * @param   string   $mail     ����
 * @param   string   $message  ����
 * @param   string   $ttitle   ����i���X�t�H�[�}�b�g�̕K�v�Ȃ��j
 * @return  boolean
 */
function _recResLog($from, $mail, $message, $ttitle, $host, $bbs, $key, $rescount)
{
    global $_conf;
    
    // ����݊��[�u�B
    // �f�[�^PHP�`���ip2_res_hist.dat.php, �^�u��؂�j�̏������ݗ������Adat�`���ip2_res_hist.dat, <>��؂�j�ɕϊ�����
    P2Util::transResHistLogPhpToDat();

    $date_and_id = date("y/m/d H:i");

    FileCtl::make_datafile($_conf['p2_res_hist_dat'], $_conf['res_write_perm']);
    
    $resnum = '';
    if (!empty($_POST['newthread'])) {
        $resnum = 1;
    } else {
        if ($rescount) {
            $resnum = $rescount + 1;
        }
    }
    
    $newdata = $from . '<>' . $mail . "<>$date_and_id<>$message<>$ttitle<>$host<>$bbs<>$key<>$resnum";

    // �܂��^�u��S�ĊO���āi2ch�̏������݂ł̓^�u�͍폜����� 2004/12/13�j
    $newdata = str_replace("\t", '', $newdata);
    // <>���^�u�ɕϊ�����
    //$newdata = str_replace('<>', "\t", $newdata);
    
    $cont = $newdata . "\n";
    
    if (false === file_put_contents($_conf['p2_res_hist_dat'], $cont, FILE_APPEND | LOCK_EX)) {
        trigger_error('p2 error: �������݃��O�̕ۑ��Ɏ��s���܂���', E_USER_WARNING);
        return false;
    }
    return true;
}

/**
 * �r�炵�ʕ�p�Ƀ��O�����
 *
 * @param   string   $from     �^�OCVS�L�^�̂��߂Ƀt�H�[�}�b�g�ς݂ł��邱��
 * @param   string   $mail     ����
 * @param   string   $message  ����
 * @param   string   $ttitle   ����i���X�t�H�[�}�b�g�̕K�v�Ȃ��j
 * @return  boolean|null
 */
function _recResLogSecu($from, $mail, $message, $ttitle, $host, $bbs, $key, $rescount)
{
    global $_conf;
    
    if (!$_conf['rec_res_log_secu_num']) {
        return null;
    }
    
    if (false === FileCtl::make_datafile($_conf['p2_res_hist_dat_secu'], $_conf['res_write_perm'])) {
        return false;
    }
    
    $resnum = '';
    if (!empty($_POST['newthread'])) {
        $resnum = 1;
    } else {
        if ($rescount) {
            $resnum = $rescount + 1;
        }
    }
    
    $newdata_ar = array(
        $from, $mail, date("y/m/d H:i"), $message, $ttitle, $host, $bbs, $key, $resnum, $_SERVER['REMOTE_ADDR']
    );
    $newdata = implode('<>', $newdata_ar) . "\n";

    // �܂��^�u��S�ĊO���āi2ch�̏������݂ł̓^�u�͍폜����� 2004/12/13�j
    $newdata = str_replace("\t", '', $newdata);
    // <>���^�u�ɕϊ�����
    //$newdata = str_replace('<>', "\t", $newdata);

    if (false === $lines = file($_conf['p2_res_hist_dat_secu'])) {
        return false;
    }
    
    while (count($lines) > $_conf['rec_res_log_secu_num']) {
        array_shift($lines);
    }
    array_push($lines, $newdata);
    $cont = implode('', $lines);
    
    if (false === file_put_contents($_conf['p2_res_hist_dat_secu'], $cont, LOCK_EX)) {
        trigger_error('p2 error: ' . __FUNCTION__ . '()', E_USER_WARNING);
        return false;
    }
    return true;
}

/**
 * �X�����Ă������Ȃ�true��Ԃ�
 */
function _isThreTateSugi()
{
    global $_conf;
    
    if (!file_exists($_conf['p2_res_hist_dat_secu']) or !$lines = file($_conf['p2_res_hist_dat_secu'])) {
        return false;
    }
    $lines = array_reverse($lines);
    
    $count = 0;
    $check_time = 60*60*1; // 1h
    $limit = 6;
    
    foreach ($lines as $v) {
        // $from, $mail, date("y/m/d H:i"), $message, $ttitle, $host, $bbs, $key, $resnum, $_SERVER['REMOTE_ADDR']
        $e = explode('<>', $v);
        $key = geti($e[7]);
        $time_str = '20' . $e[2]; // $e[2] -> 07/12/21 09:27
        //echo '<br>';
        
        // �`�F�b�N���鎞��
        if (strtotime($time_str) < time() - $check_time) {
            break;
        }
        // �X�����ĂȂ�
        if (!$key) {
            ++$count;
            if ($count > $limit) {
                return true;
            }
        }
    }
    return false;
}

/**
 * �i�ݒ�ɉ����āj�\�[�X�R�[�h��HTML��ł����ꂢ�ɍČ������悤�ɁAPOST���b�Z�[�W��ϊ�����
 *
 * @param   string  $MESSAGE
 * @return  string
 */
function _formatCodeToPost($MESSAGE)
{
    if (!empty($_POST['fix_source'])) {
        // �^�u���X�y�[�X��
        $MESSAGE = _tab2space($MESSAGE);
        // ���ꕶ�������̎Q�Ƃ�
        $MESSAGE = htmlspecialchars($MESSAGE, ENT_QUOTES);
        // ����URL�����N���
        $MESSAGE = str_replace('tp://', 't&#112;://', $MESSAGE);
        // �s���̃X�y�[�X�����̎Q�Ƃ�
        $MESSAGE = preg_replace('/^ /m', '&nbsp;', $MESSAGE);
        // ������X�y�[�X�̈�ڂ����̎Q�Ƃ�
        $MESSAGE = preg_replace('/(?<!&nbsp;)  /', '&nbsp; ', $MESSAGE);
        // ���X�y�[�X������Ԃ��Ƃ��̎d�グ
        $MESSAGE = preg_replace('/(?<=&nbsp;)  /', ' &nbsp;', $MESSAGE);
    }
    
    return $MESSAGE;
}

/**
 * �z�X�g������N�b�L�[�t�@�C���p�X��Ԃ�
 *
 * @return  string
 */
function _cachePathForCookieByHost($host)
{
    global $_conf;

    $cachefile = $_conf['cookie_dir'] . "/" . P2Util::escapeDirPath($host) . "/" . $_conf['cookie_file_name'];

    FileCtl::mkdirFor($cachefile);
    
    return $cachefile;
}

/**
 * �N�b�L�[��ݒ�t�@�C������ǂݍ���
 *
 * @param   string  $cookie_file
 * @return  array
 */
function _readCookieFile($cookie_file)
{
    if (!file_exists($cookie_file)) {
        return array();
    }
    
    if (!$cookie_cont = file_get_contents($cookie_file)) {
        //return false;
        return array();
    }
    
    if (!$p2cookies = unserialize($cookie_cont)) {
        //return false;
        return array();
    }
    
    // �ܖ������؂�Ȃ�j������i�{���Ȃ�L�[���ƂɊ�����ێ����Ȃ���΂Ȃ�Ȃ��Ƃ��낾���A��𔲂��Ă���j
    if (!empty($p2cookies['expires']) and time() > strtotime($p2cookies['expires'])) {

        //P2Util::pushInfoHtml("<p>�����؂�̃N�b�L�[���폜���܂���</p>");
        unlink($cookie_file);
        return array();
    }
    
    return $p2cookies;
}

/**
 * �N�b�L�[��ݒ�t�@�C���ɕۑ�����
 *
 * @param   array   $p2cookies
 * @param   string  $cookie_file
 * @return  boolean
 */
function _saveCookieFile($p2cookies, $cookie_file)
{
    global $_conf;
    
    // �L�^����f�[�^���Ȃ��ꍇ�́A���������ŉ������Ȃ�
    if (!$p2cookies) {
        return true;
    }

    $cookie_cont = serialize($p2cookies);

    FileCtl::make_datafile($cookie_file, $_conf['p2_perm']);
    if (false === file_put_contents($cookie_file, $cookie_cont, LOCK_EX)) {
        return false;
    }
    
    return true;
}

/**
 * @return  array  array($request, $send_host, $send_port)
 */
function _buildRequestForPost($host, $bbs, $key, $post, $bbs_cgi)
{
    global $_conf, $_login;
    
    $method = 'POST';
    $bbs_cgi_url = 'http://' . $host . $bbs_cgi;
    
    $purl = parse_url($bbs_cgi_url);
    $purl['query'] = isset($purl['query']) ? '?' . $purl['query'] : '';

    // �v���L�V
    if ($_conf['proxy_use']) {
        $send_host = $_conf['proxy_host'];
        $send_port = $_conf['proxy_port'];
        $send_path = $bbs_cgi_url;
    } else {
        $send_host = $purl['host'];
        $send_port = isset($purl['port']) ? $purl['port'] : null;
        $send_path = $purl['path'] . $purl['query'];
    }

    !$send_port and $send_port = 80;
    
    $request = $method . ' ' . $send_path . " HTTP/1.0" . "\r\n";
    $request .= 'Host: ' . $purl['host'] . "\r\n";
    
    $remote_host = P2Util::getRemoteHost($_SERVER['REMOTE_ADDR']);
    
    $add_user_info = '';
    //$add_user_info = "; p2-client-ip: {$_SERVER['REMOTE_ADDR']}";
    //$add_user_info .= "; p2-client-host: {$remote_host}";
    
    $request .= sprintf(
        'User-Agent: Monazilla/1.00 (%s/%s%s)',
        $_conf['p2uaname'], $_conf['p2version'], $add_user_info
    ) . "\r\n";
    
    $request .= 'Referer: http://' . $purl['host'] . '/' . "\r\n";
    
    // �N���C�A���g��IP�𑗐M����p2�Ǝ��̃w�b�_
    $request .= "X-P2-Client-IP: " . $_SERVER['REMOTE_ADDR'] . "\r\n";
    $request .= "X-P2-Client-Host: " . $remote_host . "\r\n";
    
    // �N�b�L�[
    $cookies_to_send = '';
    $p2cookies = _readCookieFile(_cachePathForCookieByHost($host));
    
    if ($p2cookies) {
        foreach ($p2cookies as $cname => $cvalue) {
            if ($cname != 'expires') {
                $cookies_to_send .= " {$cname}={$cvalue};";
            }
        }
    }
    
    // be.2ch �F�؃N�b�L�[
    // be�ł͎���Be�������݂����݂�
    if (P2Util::isBbsBe2chNet($host, $bbs) || !empty($_REQUEST['submit_beres'])) {
        $cookies_to_send .= ' MDMD=' . $_conf['be_2ch_code'] . ';';    // be.2ch.net�̔F�؃R�[�h(�p�X���[�h�ł͂Ȃ�)
        $cookies_to_send .= ' DMDM=' . $_conf['be_2ch_mail'] . ';';    // be.2ch.net�̓o�^���[���A�h���X
    }
    
    !$cookies_to_send and $cookies_to_send = ' ;';
    
    $request .= 'Cookie:' . $cookies_to_send . "\r\n";
    //$request .= 'Cookie: PON='.$SPID.'; NAME='.$FROM.'; MAIL='.$mail."\r\n";
    
    $request .= 'Connection: Close' . "\r\n";
    
    // {{{ POST�̎��̓w�b�_��ǉ����Ė�����URL�G���R�[�h�����f�[�^��Y�t
    
    if (strtoupper($method) == 'POST') {
        $post_enc = array();
        while (list($name, $value) = each($post)) {
            
            if (!isset($value)) {
                continue;
            }
            
            // ������� or be.2ch.net�Ȃ�AEUC�ɕϊ�
            if (P2Util::isHostJbbsShitaraba($host) || P2Util::isHostBe2chNet($host)) {
                $value = mb_convert_encoding($value, 'eucJP-win', 'SJIS-win');
            }
            
            $post_enc[] = $name . "=" . urlencode($value);
        }

        $postdata = implode('&', $post_enc);
        
        $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-Length: " . strlen($postdata) . "\r\n";
        $request .= "\r\n";
        $request .= $postdata;
        
    } else {
        $request .= "\r\n";
    }
    
    return array($request, $send_host, $send_port);
}

/**
 * ���X���������� or �V�K�X���b�h�𗧂Ă�
 * �X�����Ă̏ꍇ�́A$key �͋� '' �ł悢
 *
 * @return  boolean|string  �������ݐ����Ȃ� true�A���s�Ȃ� false �܂��͎��s���R������
 */
function _postIt($host, $bbs, $key, $post, $bbs_cgi)
{
    global $_conf, $popup, $rescount, $ttitle_en, $STYLE, $post_cache;

    list($request, $send_host, $send_port) = _buildRequestForPost($host, $bbs, $key, $post, $bbs_cgi);

    $maru_kakiko = empty($_POST['maru_kakiko']) ? 0 : 1;
    P2Util::setConfUser('maru_kakiko', $maru_kakiko);

    // �������݂��ꎞ�I�ɕۑ�
    $failed_post_file = P2Util::getFailedPostFilePath($host, $bbs, $key);
    $cont = serialize($post_cache);
    if (!DataPhp::writeDataPhp($failed_post_file, $cont, $_conf['res_write_perm'])) {
        p2die('�t�@�C���̏������݃G���[');
    }
    
    // p2 samba�̃`�F�b�N
    if ($p2SambaErrorMsgHtml = _getP2SambaErrorMsgHtml($host, $bbs)) {
        _showPostMsg(false, $p2SambaErrorMsgHtml, false);
        return false;
    }
    
    // WEB�T�[�o�֐ڑ�
    $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
    if (!$fp) {
        _showPostMsg(false, "�T�[�o�ڑ��G���[: $errstr ($errno)<br>p2 Error: �T�[�o�ւ̐ڑ��Ɏ��s���܂���", false);
        return false;
    }

    // HTTP���N�G�X�g���M
    fwrite($fp, $request, strlen($request));
    
    $post_seikou = false;
    
    $p2cookies = _readCookieFile(_cachePathForCookieByHost($host));
    
    // header
    while (!feof($fp)) {
    
        $l = fgets($fp, 8192);
        
        // �N�b�L�[�L�^
        if (preg_match("/Set-Cookie: (.+?)\r\n/", $l, $matches)) {
            $cgroups = explode(";", $matches[1]);
            if ($cgroups) {
                foreach ($cgroups as $v) {
                    if (preg_match("/(.+)=(.*)/", $v, $m)) {
                        $k = ltrim($m[1]);
                        if ($k != 'path') {
                            $p2cookies[$k] = $m[2];
                        }
                    }
                }
            }

        // �]���͏������ݐ����Ɣ��f
        } elseif (preg_match("/^Location: /", $l, $matches)) {
            $post_seikou = true;
        }
        if ($l == "\r\n") {
            break;
        }
    }
    
    // �N�b�L�[���t�@�C���ɕۑ�����
    _saveCookieFile($p2cookies, _cachePathForCookieByHost($host));
    
    // body
    $response = '';
    while (!feof($fp)) {
        $response .= fread($fp, 164000);
    }

    fclose($fp);
    
    // be.2ch.net or JBBS������� �����R�[�h�ϊ� EUC��SJIS
    if (P2Util::isHostBe2chNet($host) || P2Util::isHostJbbsShitaraba($host)) {
        $response = mb_convert_encoding($response, 'SJIS-win', 'eucJP-win');
        
        //<META http-equiv="Content-Type" content="text/html; charset=EUC-JP">
        $response = preg_replace("{(<head>.*<META http-equiv=\"Content-Type\" content=\"text/html; charset=)EUC-JP(\">.*</head>)}is", "$1Shift_JIS$2", $response);
    }
    
    $kakikonda_match = "/<title>.*(�������݂܂���|�� �������݂܂��� ��|�������ݏI�� - SubAll BBS).*<\/title>/is";
    $cookie_kakunin_match = "/<!-- 2ch_X:cookie -->|<title>�� �������݊m�F ��<\/title>|>�������݊m�F�B</";
    
    if (eregi("(<.+>)", $response, $matches)) {
        $response = $matches[1];
    }
    
    // �J�L�R�~����
    if (preg_match($kakikonda_match, $response, $matches) or $post_seikou) {
        
        // �N�b�L�[�̏������ݎ����ۑ�����������
        if (isset($_COOKIE['post_msg'])) {
            setcookie('post_msg', '', time() - 3600);
        }
        
        $reload = empty($_POST['from_read_new']);
        _showPostMsg(true, '�������݂��I���܂����B', $reload);
        
        // ���e���s�L�^������΍폜����
        if (file_exists($failed_post_file)) {
            unlink($failed_post_file);
        }
        
        return true;
        
        //$response_ht = htmlspecialchars($response, ENT_QUOTES);
        //echo "<pre>{$response_ht}</pre>";
    
    // ��cookie�m�F�\���ipost�ă`�������W���Ăˁj
    } elseif (preg_match($cookie_kakunin_match, $response, $matches)) {

        $htm['more_hidden_post'] = '';
        // p2�p�̒ǉ��L�[
        $more_hidden_keys = array(
            'newthread', 'submit_beres', 'from_read_new', 'maru_kakiko', 'csrfid', 'k',
            UA::getQueryKey() // 'b'
        );
        foreach ($more_hidden_keys as $hk) {
            if (isset($_POST[$hk])) {
                $htm['more_hidden_post'] .= sprintf(
                    '<input type="hidden" name="%s" value="%s">',
                    hs($hk), hs($_POST[$hk])
                ) . "\n";
            }
        }

        $form_pattern = '/<form method="?POST"? action="?\\.\\.\\/test\\/(sub)?bbs\\.cgi(?:\\?guid=ON)?"?>/i';
        $myname = basename($_SERVER['SCRIPT_NAME']);
        $host_hs = hs($host);
        $popup_hs = hs($popup);
        $rescount_hs = hs($rescount);
        $ttitle_en_hs = hs($ttitle_en);
        
        $form_replace = <<<EOFORM
<form method="POST" action="{$myname}?guid=ON" accept-charset="{$_conf['accept_charset']}">
    <input type="hidden" name="detect_hint" value="����">
    <input type="hidden" name="host" value="{$host_hs}">
    <input type="hidden" name="popup" value="{$popup_hs}">
    <input type="hidden" name="rescount" value="{$rescount_hs}">
    <input type="hidden" name="ttitle_en" value="{$ttitle_en_hs}">
    <input type="hidden" name="sub" value="\$1">
    {$htm['more_hidden_post']}
EOFORM;
        $response = preg_replace($form_pattern, $form_replace, $response);
        
        $h_b = explode("</head>", $response);
        
        // HTML�v�����g
        echo $h_b[0];
        if (!$_conf['ktai']) {
            P2View::printIncludeCssHtml('style');
            P2View::printIncludeCssHtml('post');
        }
        if ($popup) {
            $mado_okisa = explode(',', $STYLE['post_pop_size']);
            $mado_okisa_x = $mado_okisa[0];
            $mado_okisa_y = $mado_okisa[1] + 200;
            echo <<<EOSCRIPT
            <script language="JavaScript">
            <!--
                resizeTo({$mado_okisa_x},{$mado_okisa_y});
            // -->
            </script>
EOSCRIPT;
        }
        
        echo "</head>";
        echo $h_b[1];
        
        //return false;
        return 'COOKIE';
        
    // ���̑��̓��X�|���X�����̂܂ܕ\���i���ʂ̓G���[�Ƃ���false��Ԃ��j
    } else {
        $response = ereg_replace('������Ń����[�h���Ă��������B<a href="\.\./[a-z]+/index\.html"> GO! </a><br>', "", $response);
        echo $response;
        return false;
    }
}

/**
 * p2 samba�Ɉ����������Ă�����A�G���[���b�Z�[�WHTML��Ԃ�
 *
 * @return  string|null
 */
function _getP2SambaErrorMsgHtml($host, $bbs)
{
    global $_conf;
    
    $kisei_second = 10;
    $samba24 = null;
    if (P2Util::isHost2chs($host)) {
        if (!empty($_POST['maru_kakiko']) and file_exists($_conf['sid2ch_php'])) {
            // samba24�X���[
        } else {
            if ($r = P2Util::getSamba24TimeCache($host, $bbs)) {
                $kisei_second = $r;
                $samba24 = true;
            }
        }
    }
    //if (_isSambaDeny($kisei_second)) { // �m�F���ԂŃ`�F�b�N����ꍇ
    if (_isDenyWithUpdateLastPostTime($kisei_second)) { // ���s���ԂŃ`�F�b�N����ꍇ
        $samba24_msg = $samba24 ? '2ch��samba24�ݒ� ' : '';
        return sprintf('p2 samba�K��: �A�����e�͂ł��܂���B�i%s%d�b�j', hs($samba24_msg), $kisei_second);
    }
    
    return null;
}

/**
 * �������ݏ������ʂ�HTML�\������
 *
 * @param   boolean  $is_done       �������݊��������Ȃ�true
 * @param   string   $msg_ht        ���ʃ��b�Z�[�WHTML
 * @param   boolean  $reload_opener opener��ʂ������ōX�V����Ȃ�true
 * @return  void
 */
function _showPostMsg($is_done, $msg_ht, $reload_opener)
{
    global $_conf, $location_url, $location_sid_url, $popup, $STYLE, $ttitle, $ptitle;
    
    $body_at = P2View::getBodyAttrK();
    
    $class_ttitle = '';
    if (!$_conf['ktai']) {
        $class_ttitle = ' class="thre_title"';
    }
    $ttitle_ht = "<b{$class_ttitle}>{$ttitle}</b>";
    
    // 2005/04/25 rsk: <script>�^�O����CDATA�Ƃ��Ĉ����邽�߁A&amp;�ɂ��Ă͂����Ȃ�
    $popup_ht = '';
    $meta_refresh_ht = '';
    if ($popup) {
        $reload_js = $reload_opener ? 'opener.location.href="' . $location_sid_url . '"' : '';
        $popup_ht = <<<EOJS
<script language="JavaScript">
<!--
    resizeTo({$STYLE['post_pop_size']});
    {$reload_js}
    var delay = 3*1000;
    var closeid = setTimeout("window.close()", delay);
// -->
</script>
EOJS;
        $body_at .= ' onUnload="clearTimeout(closeid)"';
    
    } else {
        // 2005/03/01 aki: jig�u���E�U�ɑΉ����邽�߁A&amp; �ł͂Ȃ� & ��
        // 2007/10/17 �����������Ȃ̂��ȁBhs()����悤�ɕύX���Ă݂��B
        $meta_refresh_ht = '<meta http-equiv="refresh" content="1;URL=' . hs($location_sid_url) . '">';
    }

    // HTML�v�����g
    P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
<?php
P2View::printExtraHeadersHtml();
echo $meta_refresh_ht;

    if ($is_done) {
        echo "<title>p2 - �������݂܂����B</title>";
    } else {
        echo "<title>{$ptitle}</title>";
    }

    $kakunin_ht = '';
    
    // PC����
    if (!$_conf['ktai']) {
        P2View::printIncludeCssHtml('style');
        P2View::printIncludeCssHtml('post');
        ?>
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<?php
        echo $popup_ht;
        
    // �g�ь���
    } else {
        $kakunin_ht = '<p><a href="' . hs($location_url) . '">�m�F</a></p>';
    }
    
    echo "</head><body{$body_at}>\n";

    P2Util::printInfoHtml();

    echo <<<EOP
<p>{$ttitle_ht}</p>
<p>{$msg_ht}</p>
{$kakunin_ht}
</body>
</html>
EOP;
}


/**
 * @return  boolean  �K�����Ȃ� true ��Ԃ�
 */
function _isSambaDeny($sambatime)
{
    if (!$times = _getLastPostTime()) {
        return false;
    }
    $last_try_time = $times[0];
    if (time() - $lasttrytime < $sambatime) {
        return true;
    }
    return false;
}

/**
 * �ŏI���e���ԋK�����`�F�b�N���čX�V����
 *
 * @return  boolean  �e���Ȃ�true
 */
function _isDenyWithUpdateLastPostTime($kisei_second)
{
    global $_conf;
    
    $file = $_conf['last_post_time_file'];
    
    FileCtl::make_datafile($file, $_conf['res_write_perm']);
    
    if (!$fp = fopen($file, 'rb+')) {
        return false;
    }
    flock($fp, LOCK_EX);
    $bytes = 12000;
    $lines = array();
    while (!feof($fp)) {
        if ($line = rtrim(fgets($fp, $bytes))) {
            $lines[] = $line;
        }
    }
    
    // �O�񏑂����ݎ��Ԃ�ǂݍ���Ń`�F�b�N
    $last_post_times = $lines;
    if ($last_try_time = $last_post_times[0]) {
        if ($last_try_time > time() - $kisei_second) {
            flock($fp, LOCK_UN);
            fclose($fp);
            return true;
        }
    }
    
    $last_confirmed_time = empty($last_post_times[1]) ? '' : $last_post_times[1];
    // ���s���� : ���s�m�F����
    $cont = time() . "\n" . $last_confirmed_time . "\n";

    rewind($fp);    // ���ꂢ�� http://jp.php.net/manual/ja/function.ftruncate.php#44702
    ftruncate($fp, 0);
    if (false === fwrite($fp, $cont)) {
        die("p2 error: �ŏI���e���Ԃ��X�V�ł��܂���ł���");
        return false;
    }
    
    flock($fp, LOCK_UN);
    fclose($fp);
    
    return false;
}

/**
 * �ŏI���e���Ԃ��擾����
 *
 * @return array|false [0]�Ɏ��s���ԁA[1]�ɐ����m�F���Ԃ��i�[�����z��
 */
function _getLastPostTime()
{
    global $_conf;
    
    $file = $_conf['last_post_time_file'];
    
    if (!file_exists($file)) {
        return false;
    }
    if (!$fp = fopen($file, 'rb')) {
        return false;
    }
    flock($fp, LOCK_EX);
    $bytes = 12000;
    $lines = array();
    while (!feof($fp)) {
        if ($line = rtrim(fgets($fp, $bytes))) {
            $lines[] = $line;
        }
    }
    flock($fp, LOCK_UN);
    fclose($fp);
    
    return $lines ? $lines : false;
}

/**
 * �ŏI���e���Ԃ��L�^����
 *
 *   -���e���s����
 *   --���s���ԂŘA���`�F�b�N�B
 *   ---OK�Ȃ�m�F���Ԃ͂��̂܂܂ɁA���s���Ԃ��X�V���đ��s�i�f�t�H���g����j
 *   ---NG�Ȃ�Samba�����i���s���Ԃ̍X�V�͍s��Ȃ��ł��������j
 *
 *   -���e�m�F����
 *   --�����Ȃ玎�s/�m�F���ԍX�V�i$confirmed = 'SUCCESS'�j
 *   --Cookie�m�F�Ȃ玎�s���Ԃ�O��̊m�F���Ԃɖ߂��i$confirmed = 'COOKIE'�j
 *
 * @param   $confirmed  ���e�m�F�����̏ꍇ�A'SUCCESS' or 'COOKIE' ���w�肷��
 * @return  boolean
 */
function _recLastPostTime($confirmed = '')
{
    global $_conf;
    
    // ���e���s����\n���e�m�F����\n
    
    // ���e�m�F���� �����Ȃ�
    if ($confirmed == 'SUCCESS') {
        // ���s���� : �i���e�����́j�m�F����
        $cont = time() . "\n" . time() . "\n";
    
    // ���e�m�F���� Cookie�m�F�Ȃ玎�s���Ԃ�߂�
    } elseif ($confirmed == 'COOKIE') {
        $last_post_times = _getLastPostTime();
        $last_confirmed_time = empty($last_post_times[1]) ? '' : $last_post_times[1];
        $cont = $last_confirmed_time . "\n" . $last_confirmed_time . "\n";
        
    // ���e���s���� ���e���s���ԍX�V
    } else {
        // ���e�O�Ɏ��s�m�F���Ă���ꍇ�A�����̏����͂���Ȃ�����
        $last_post_times = _getLastPostTime();
        $last_confirmed_time = empty($last_post_times[1]) ? '' : $last_post_times[1];
        $cont = time() . "\n" . $last_confirmed_time . "\n";
    }
    
    FileCtl::make_datafile($_conf['last_post_time_file'], $_conf['res_write_perm']);
    
    if (false === file_put_contents($_conf['last_post_time_file'], $cont, LOCK_EX)) {
        die("p2 error: �ŏI���e���Ԃ��X�V�ł��܂���ł���");
        return false;
    }
    
    return true;
}

/**
 * subject����key���擾����
 *
 * @return  string|false
 */
function _getKeyInSubject()
{
    global $host, $bbs, $ttitle;

    require_once P2_LIB_DIR . '/SubjectTxt.php';
    $aSubjectTxt = new SubjectTxt($host, $bbs);

    foreach ($aSubjectTxt->subject_lines as $l) {
        if (strstr($l, $ttitle)) {
            if (preg_match("/^([0-9]+)\.(dat|cgi)(,|<>)(.+) ?(\(|�i)([0-9]+)(\)|�j)/", $l, $matches)) {
                return $key = $matches[1];
            }
        }
    }
    return false;
}

/**
 * ���`���ێ����Ȃ���A�^�u���X�y�[�X�ɒu��������
 *
 * @return  string
 */
function _tab2space($in_str, $tabwidth = 4, $crlf = "\n")
{
    $out_str = '';
    $lines = preg_split('/\r\n|\r|\n/', $in_str);
    $ln = count($lines);

    for ($i = 0; $i < $ln; $i++) {
        $parts = explode("\t", rtrim($lines[$i]));
        $pn = count($parts);

        for ($j = 0; $j < $pn; $j++) {
            if ($j == 0) {
                $l = $parts[$j];
            } else {
                //$t = $tabwidth - (strlen($l) % $tabwidth);
                $sn = $tabwidth - (mb_strwidth($l) % $tabwidth); // UTF-8�ł��S�p��������2�ƃJ�E���g����
                for ($k = 0; $k < $sn; $k++) {
                    $l .= ' ';
                }
                $l .= $parts[$j];
            }
        }

        $out_str .= $l;
        if ($i + 1 < $ln) {
            $out_str .= $crlf;
        }
    }

    return $out_str;
}

/**
 * @return  string|null
 */
function _getSID2ch()
{
    global $_conf;
    
    $SID2ch = null;
    if (file_exists($_conf['sid2ch_php'])) {
        include $_conf['sid2ch_php']; // $uaMona, $SID2ch ���Z�b�g�����
    }
    return $SID2ch;
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
