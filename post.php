<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=0 fdm=marker: */
/* mi: charset=Shift_JIS */

/*
    p2 - ���X��������
*/

require_once './conf/conf.inc.php';
require_once P2_LIB_DIR . '/dataphp.class.php';
require_once P2_LIB_DIR . '/filectl.class.php';

$_login->authorize(); // ���[�U�F��

if (!empty($_conf['disable_res'])) {
    P2Util::printSimpleHtml('p2 error: �������݋@�\�͖����ł��B');
    die;
}

// �����G���[
if (empty($_POST['host'])) {
    P2Util::printSimpleHtml('p2 error: �����̎w�肪�ςł�');
    die;
}
if (!isset($_POST['csrfid']) or $_POST['csrfid'] != P2Util::getCsrfId()) {
    P2Util::printSimpleHtml('p2 error: �s���ȃ|�X�g�ł�');
    die;
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
$MESSAGE = formatCodeToPost($MESSAGE);

// ������΂�livedoor�ړ]�ɑΉ��Bpost���livedoor�Ƃ���B
$host = P2Util::adjustHostJbbs($host);

// machibbs�AJBBS@������� �Ȃ�
if (P2Util::isHostMachiBbs($host) or P2Util::isHostJbbsShitaraba($host)) {
    $bbs_cgi = "/bbs/write.cgi";
    
    // JBBS@������� �Ȃ�
    if (P2Util::isHostJbbsShitaraba($host)) {
        $bbs_cgi = "/../bbs/write.cgi";
        preg_match("/(\w+)$/", $host, $ar);
        $dir = $ar[1];
        $dir_k = "DIR";
    }
    
    $submit_k = "submit";
    $bbs_k = "BBS";
    $key_k = "KEY";
    $time_k = "TIME";
    $FROM_k = "NAME";
    $mail_k = "MAIL";
    $MESSAGE_k = "MESSAGE";
    $subject_k = "SUBJECT";
    
// 2ch�n�Ȃ�
} else { 
    if ($sub) {
        $bbs_cgi = "/test/{$sub}bbs.cgi";
    } else {
        $bbs_cgi = "/test/bbs.cgi";
    }
    $submit_k = "submit";
    $bbs_k = "bbs";
    $key_k = "key";
    $time_k = "time";
    $FROM_k = "FROM";
    $mail_k = "mail";
    $MESSAGE_k = "MESSAGE";
    $subject_k = "subject";

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
            $submit_k => $submit,
            $bbs_k => $bbs, $subject_k => $subject,
            $time_k => $time,
            $FROM_k => $FROM, $mail_k => $mail, $MESSAGE_k => $MESSAGE
        );
    if (P2Util::isHostJbbsShitaraba($host)) {
        $post[$dir_k] = $dir;
    }
    $location_ht = "{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$_conf['k_at_a']}";
    
} else {
    $post = array(
            $submit_k => $submit,
            $bbs_k => $bbs, $key_k => $key,
            $time_k => $time,
            $FROM_k => $FROM, $mail_k => $mail, $MESSAGE_k => $MESSAGE
        );
    if (P2Util::isHostJbbsShitaraba($host)) {
        $post[$dir_k] = $dir;
    }
    $location_ht = "{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}&amp;ls={$rescount}-&amp;refresh=1&amp;nt={$newtime}{$_conf['k_at_a']}#r{$rescount}";
}

// {{{ 2ch�Ł����O�C�����Ȃ�sid�ǉ�

if (!empty($_POST['maru_kakiko']) and P2Util::isHost2chs($host) && file_exists($_conf['sid2ch_php'])) {
    
    // ���O�C����A24���Ԉȏ�o�߂��Ă����玩���ă��O�C��
    if (file_exists($_conf['idpw2ch_php']) and filemtime($_conf['sid2ch_php']) < time() - 60*60*24) {
        require_once P2_LIB_DIR . '/login2ch.inc.php';
        login2ch();
    }
    
    include $_conf['sid2ch_php']; // $uaMona, $SID2ch
    $post['sid'] = $SID2ch;
}

// }}}

// 2006/05/27 �V�d�l�H
$post['hana'] = 'mogera';

if (!empty($_POST['newthread'])) {
    $ptitle = "p2 - �V�K�X���b�h�쐬";
} else {
    $ptitle = "p2 - ���X��������";
}

//================================================================
// ���C������
//================================================================

// �|�X�g���s
$posted = postIt($host, $bbs, $key, $post);

// �X�����Đ����Ȃ�Asubject����key���擾
if (!empty($_POST['newthread']) && $posted) {
    sleep(1);
    $key = getKeyInSubject();
}


// {{{ key.idx �ۑ�

$tagCsv = array();

// <> ���O���B�B
$tagCsv['FROM'] = str_replace('<>', '', $FROM);
$tagCsv['mail'] = str_replace('<>', '', $mail);

// ���O�ƃ��[���A�󔒎��� P2NULL ���L�^
$tagCsvF['FROM'] = ($tagCsv['FROM'] == '') ? 'P2NULL' : $tagCsv['FROM'];
$tagCsvF['mail'] = ($tagCsv['mail'] == '') ? 'P2NULL' : $tagCsv['mail'];

if ($host && $bbs && $key) {
    $idx_host_dir = P2Util::idxDirOfHost($host);
    $keyidx = $idx_host_dir . '/' . $bbs . '/' . $key . '.idx';
    
    $akeyline = array();
    if ($keylines = @file($keyidx)) {
        $akeyline = explode('<>', rtrim($keylines[0]));
    }
    $sar = array($akeyline[0], $akeyline[1], $akeyline[2], $akeyline[3], $akeyline[4],
                 $akeyline[5], $akeyline[6], $tagCsvF['FROM'], $tagCsvF['mail'], $akeyline[9],
                 $akeyline[10], $akeyline[11], $akeyline[12]);
    P2Util::recKeyIdx($keyidx, $sar);
}

// }}}

if (!$posted) {
    exit;
}

// {{{ �������ݗ���

if ($host && $bbs && $key) {
    
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
        
        if (FileCtl::filePutRename($rh_idx, $cont) === false) {
            $errmsg = sprintf('p2 error: %s(), FileCtl::filePutRename() failed.', __FUNCTION__);
            trigger_error($errmsg, E_USER_WARNING);
            //return false;
        }
    }
}

// }}}

// �������݃��O�L�^

$tagCsv['message'] = formatMessageTagCvs($MESSAGE);

if ($_conf['res_write_rec']) {
    recResLog($tagCsv['FROM'], $tagCsv['mail'], $tagCsv['message'], $ttitle, $host, $bbs, $key, $rescount);
}

recResLogSecu($tagCsv['FROM'], $tagCsv['mail'], $tagCsv['message'], $ttitle, $host, $bbs, $key, $rescount);

exit;


//=======================================================================
// �֐� �i���̃t�@�C�����ł̂ݗ��p�j
//=======================================================================

/**
 * �^�OCSV�L�^�̂��߂Ƀ��b�Z�[�W���t�H�[�}�b�g�ϊ�����
 *
 * @return  string
 */
function formatMessageTagCvs($message)
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
function recResLog($from, $mail, $message, $ttitle, $host, $bbs, $key, $rescount)
{
    global $_conf;
    
    // ���݊��[�u�B
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
 * @return  boolean
 */
function recResLogSecu($from, $mail, $message, $ttitle, $host, $bbs, $key, $rescount)
{
    global $_conf;
    
    if (!$_conf['rec_res_log_secu_num']) {
        return null;
    }
    
    FileCtl::make_datafile($_conf['p2_res_hist_dat_secu'], $_conf['res_write_perm']);
    
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

    $lines = file($_conf['p2_res_hist_dat_secu']);
    if ($lines === false) {
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
 * �i�ݒ�ɉ����āj�\�[�X�R�[�h��HTML��ł����ꂢ�ɍČ������悤�ɁAPOST���b�Z�[�W��ϊ�����
 *
 * @param   string  $MESSAGE
 * @return  string
 */
function formatCodeToPost($MESSAGE)
{
    if (!empty($_POST['fix_source'])) {
        // �^�u���X�y�[�X��
        $MESSAGE = tab2space($MESSAGE);
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
 * @access  public
 * @return  string
 */
function cachePathForCookie($host)
{
    global $_conf;

    $cachefile = $_conf['cookie_dir'] . "/{$host}/" . $_conf['cookie_file_name'];

    FileCtl::mkdirFor($cachefile);
    
    return $cachefile;
}

/**
 * �N�b�L�[��ݒ�t�@�C������ǂݍ���
 *
 * @param   string  $cookie_file
 * @return  array
 */
function readCookieFile($cookie_file)
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
function saveCookieFile($p2cookies, $cookie_file)
{
    global $_conf;
    
    // �L�^����f�[�^���Ȃ��ꍇ�́A���������ŉ������Ȃ�
    if (!$p2cookies) {
        return true;
    }

    $cookie_cont = serialize($p2cookies);

    FileCtl::make_datafile($cookie_file, $_conf['p2_perm']);
    if (file_put_contents($cookie_file, $cookie_cont, LOCK_EX) === false) {
        return false;
    }
    
    return true;
}

/**
 * ���X���������� or �V�K�X���b�h�𗧂Ă�
 * �X�����Ă̏ꍇ�́A$key �͋� '' �ł悢
 *
 * @return  boolean  �������ݐ����Ȃ� true�A���s�Ȃ� false
 */
function postIt($host, $bbs, $key, $post)
{
    global $_conf, $post_result, $post_error2ch, $popup, $rescount, $ttitle_en, $STYLE;
    global $bbs_cgi, $post_cache;
    
    $method = "POST";
    $bbs_cgi_url = "http://" . $host . $bbs_cgi;
    
    $purl = parse_url($bbs_cgi_url);
    if (isset($purl['query'])) {
        $purl['query'] = "?" . $purl['query'];
    } else {
        $purl['query'] = "";
    }

    // �v���L�V
    if ($_conf['proxy_use']) {
        $send_host = $_conf['proxy_host'];
        $send_port = $_conf['proxy_port'];
        $send_path = $bbs_cgi_url;
    } else {
        $send_host = $purl['host'];
        $send_port = $purl['port'];
        $send_port = isset($purl['port']) ? $purl['port'] : null;
        $send_path = $purl['path'] . $purl['query'];
    }

    !$send_port and $send_port = 80;
    
    $request = $method . " " . $send_path . " HTTP/1.0" . "\r\n";
    $request .= "Host: " . $purl['host'] . "\r\n";
    
    $remote_host = P2Util::getRemoteHost($_SERVER['REMOTE_ADDR']);
    
    $add_user_info = '';
    //$add_user_info = "; p2-client-ip: {$_SERVER['REMOTE_ADDR']}";
    //$add_user_info .= "; p2-client-host: {$remote_host}";
    
    $request .= "User-Agent: Monazilla/1.00 (" . $_conf['p2name'] . "/" . $_conf['p2version'] . "{$add_user_info})" . "\r\n";
    
    $request .= 'Referer: http://' . $purl['host'] . '/' . "\r\n";
    
    // �N���C�A���g��IP�𑗐M����p2�Ǝ��̃w�b�_
    $request .= "X-P2-Client-IP: " . $_SERVER['REMOTE_ADDR'] . "/\r\n";
    $request .= "X-P2-Client-Host: " . $remote_host . "/\r\n";
    
    // �N�b�L�[
    $cookies_to_send = "";

    // �N�b�L�[�̓ǂݍ���
    $cookie_file = cachePathForCookie($host);
    $p2cookies = readCookieFile($cookie_file);
    
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
    
    $request .= "Connection: Close\r\n";
    
    // {{{ POST�̎��̓w�b�_��ǉ����Ė�����URL�G���R�[�h�����f�[�^��Y�t
    
    if (strtoupper($method) == "POST") {
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

        $postdata = implode("&", $post_enc);
        
        $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-Length: " . strlen($postdata) . "\r\n";
        $request .= "\r\n";
        $request .= $postdata;
        
    } else {
        $request .= "\r\n";
    }
    
    // }}}

    $maru_kakiko = isset($_POST['maru_kakiko']) ? $_POST['maru_kakiko'] : '';
    setConfUser('maru_kakiko', $_POST['maru_kakiko']);

    // �������݂��ꎞ�I�ɕۑ�
    $failed_post_file = P2Util::getFailedPostFilePath($host, $bbs, $key);
    $cont = serialize($post_cache);
    DataPhp::writeDataPhp($failed_post_file, $cont, $_conf['res_write_perm']);
    
    // WEB�T�[�o�֐ڑ�
    $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
    if (!$fp) {
        showPostMsg(false, "�T�[�o�ڑ��G���[: $errstr ($errno)<br>p2 Error: �T�[�o�ւ̐ڑ��Ɏ��s���܂���", false);
        return false;
    }

    // HTTP���N�G�X�g���M
    fputs($fp, $request);
    
    $post_seikou = false;
    
    // header
    while (!feof($fp)) {
    
        $l = fgets($fp, 8192);
        
        // �N�b�L�[�L�^
        if (preg_match("/Set-Cookie: (.+?)\r\n/", $l, $matches)) {
            //echo "<p>".$matches[0]."</p>"; //
            $cgroups = explode(";", $matches[1]);
            if ($cgroups) {
                foreach ($cgroups as $v) {
                    if (preg_match("/(.+)=(.*)/", $v, $m)) {
                        $k = ltrim($m[1]);
                        if ($k != "path") {
                            $p2cookies[$k] = $m[2];
                        }
                    }
                }
            }
            if ($p2cookies) {
                $cookies_to_send = '';
                foreach ($p2cookies as $cname => $cvalue) {
                    if ($cname != "expires") {
                        $cookies_to_send .= " {$cname}={$cvalue};";
                    }
                }
                $newcokkies = "Cookie:{$cookies_to_send}\r\n";
                
                $request = preg_replace("/Cookie: .*?\r\n/", $newcokkies, $request);
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
    saveCookieFile($p2cookies, $cookie_file);
    
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
        isset($_COOKIE['post_msg']) and setcookie('post_msg', '', time() - 3600);
        
        $reload = empty($_POST['from_read_new']);
        showPostMsg(true, '�������݂��I���܂����B', $reload);
        
        // ���e���s�L�^������΍폜����
        if (file_exists($failed_post_file)) {
            unlink($failed_post_file);
        }
        
        return true;
        
        //$response_ht = htmlspecialchars($response, ENT_QUOTES);
        //echo "<pre>{$response_ht}</pre>";
    
    // ��cookie�m�F�ipost�ă`�������W�j
    } elseif (preg_match($cookie_kakunin_match, $response, $matches)) {

        $htm['more_hidden_post'] = '';
        $more_hidden_keys = array('newthread', 'submit_beres', 'from_read_new', 'maru_kakiko', 'csrfid', 'k', 'b');
        foreach ($more_hidden_keys as $hk) {
            if (isset($_POST[$hk])) {
                $value_hd = htmlspecialchars($_POST[$hk], ENT_QUOTES);
                $htm['more_hidden_post'] .= "<input type=\"hidden\" name=\"{$hk}\" value=\"{$value_hd}\">\n";
            }
        }

        $form_pattern = '/<form method=\"?POST\"? action=\"?\\.\\.\\/test\\/(sub)?bbs\\.cgi\"?>/i';
        
        $myname = basename($_SERVER['SCRIPT_NAME']);
        $form_replace = <<<EOFORM
<form method="POST" action="{$myname}" accept-charset="{$_conf['accept_charset']}">
    <input type="hidden" name="detect_hint" value="����">
    <input type="hidden" name="host" value="{$host}">
    <input type="hidden" name="popup" value="{$popup}">
    <input type="hidden" name="rescount" value="{$rescount}">
    <input type="hidden" name="ttitle_en" value="{$ttitle_en}">
    <input type="hidden" name="sub" value="\$1">
    {$htm['more_hidden_post']}
EOFORM;
        $response = preg_replace($form_pattern, $form_replace, $response);
        
        $h_b = explode("</head>", $response);
        
        // HTML�v�����g
        echo $h_b[0];
        if (!$_conf['ktai']) {
            include_once './style/style_css.inc';
            include_once './style/post_css.inc';
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
        
        return false;
        
    // ���̑��̓��X�|���X�����̂܂ܕ\���i���ʂ̓G���[�Ƃ���false��Ԃ��j
    } else {
        $response = ereg_replace('������Ń����[�h���Ă��������B<a href="\.\./[a-z]+/index\.html"> GO! </a><br>', "", $response);
        echo $response;
        return false;
    }
}

/**
 * �������ݏ������ʂ�HTML�\������
 *
 * @param   boolean  $is_done     �������݊��������Ȃ�true
 * @param   string   $result_msg  ���ʃ��b�Z�[�W
 * @param   boolean  $reload      opener��ʂ������ōX�V����Ȃ�true
 * @return  void
 */
function showPostMsg($is_done, $result_msg, $reload)
{
    global $_conf, $location_ht, $popup, $STYLE, $ttitle, $ptitle;
    
    // �v�����g�p�ϐ�
    if (!$_conf['ktai']) {
        $class_ttitle = ' class="thre_title"';
    }
    $ttitle_ht = "<b{$class_ttitle}>{$ttitle}</b>";
    // 2005/03/01 aki: jig�u���E�U�ɑΉ����邽�߁A&amp; �ł͂Ȃ� & ��
    // 2005/04/25 rsk: <script>�^�O����CDATA�Ƃ��Ĉ����邽�߁A&amp;�ɂ��Ă͂����Ȃ�
    $location_noenc = preg_replace("/&amp;/", "&", $location_ht);
    $popup_ht = '';
    if ($popup) {
        $popup_ht = <<<EOJS
<script language="JavaScript">
<!--
    opener.location.href="{$location_noenc}";
    var delay= 3*1000;
    setTimeout("window.close()", delay);
// -->
</script>
EOJS;

    } else {
        $meta_refresh_ht = <<<EOP
        <meta http-equiv="refresh" content="1;URL={$location_noenc}">
EOP;
    }

    // HTML�v�����g
    echo $_conf['doctype'];
    echo <<<EOHEADER
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
{$meta_refresh_ht}
EOHEADER;

    if ($is_done) {
        echo "<title>p2 - �������݂܂����B</title>";
    } else {
        echo "<title>{$ptitle}</title>";
    }

    $kakunin_ht = '';
    
    // PC
    if (!$_conf['ktai']) {
        include_once './style/style_css.inc';
        include_once './style/post_css.inc';
        if ($popup) {
            echo <<<EOSCRIPT
            <script language="JavaScript">
            <!--
                resizeTo({$STYLE['post_pop_size']});
            // -->
            </script>
EOSCRIPT;
        }
        if ($reload) {
            echo $popup_ht;
        }
        
    // �g��
    } else {
        $kakunin_ht = <<<EOP
<p><a href="{$location_ht}">�m�F</a></p>
EOP;
    }
    
    echo "</head><body>\n";

    P2Util::printInfoHtml();

    echo <<<EOP
<p>{$ttitle_ht}</p>
<p>{$result_msg}</p>
{$kakunin_ht}
</body>
</html>
EOP;
}

/**
 * subject����key���擾����
 *
 * @return  string|false
 */
function getKeyInSubject()
{
    global $host, $bbs, $ttitle;

    require_once P2_LIB_DIR . '/SubjectTxt.class.php';
    $aSubjectTxt =& new SubjectTxt($host, $bbs);

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
function tab2space($in_str, $tabwidth = 4, $crlf = "\n")
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

