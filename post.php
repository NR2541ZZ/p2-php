<?php
/*
    p2 - ���X��������
*/

include_once './conf/conf.inc.php';
require_once P2_LIBRARY_DIR . '/dataphp.class.php';
require_once P2_LIBRARY_DIR . '/filectl.class.php';

$_login->authorize(); // ���[�U�F��

if (!empty($_conf['disable_res'])) {
    P2Util::printSimpleHtml('p2 error: �������݋@�\�͖����ł��B');
    die('');
}

// �����G���[
if (empty($_POST['host'])) {
    P2Util::printSimpleHtml('p2 error: �����̎w�肪�ςł�');
    die('');
}

if (!isset($_POST['csrfid']) or $_POST['csrfid'] != P2Util::getCsrfId()) {
    P2Util::printSimpleHtml('p2 error: �s���ȃ|�X�g�ł�');
    die('');
}

if ($_conf['expack.aas.enabled'] && !empty($_POST['PREVIEW_AAS'])) {
    include 'aas.php';
    exit;
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
    'ttitle_en');

foreach ($post_keys as $pk) {
    ${$pk} = (isset($_POST[$pk])) ? $_POST[$pk] : '';
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

// {{{ �\�[�X�R�[�h�����ꂢ�ɍČ������悤�ɕϊ�

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

// }}}
// {{{ �N�b�L�[�̓ǂݍ���

$cookie_file = P2Util::cachePathForCookie($host);
if ($cookie_cont = @file_get_contents($cookie_file)) {
    $p2cookies = unserialize($cookie_cont);
    if ($p2cookies['expires']) {
        if (time() > strtotime($p2cookies['expires'])) { // �����؂�Ȃ�j��
            // echo "<p>�����؂�̃N�b�L�[���폜���܂���</p>";
            unlink($cookie_file);
            unset($cookie_cont, $p2cookies);
        }
    }
}

// }}}

// ������΂�livedoor�ړ]�ɑΉ��Bpost���livedoor�Ƃ���B
$host = P2Util::adjustHostJbbs($host);

// machibbs�AJBBS@������� �Ȃ�
if (P2Util::isHostMachiBbs($host) or P2Util::isHostJbbsShitaraba($host)) {
    $bbs_cgi = "/bbs/write.cgi";

    // JBBS@������� �Ȃ�
    if (P2Util::isHostJbbsShitaraba($host)) {
        $bbs_cgi = "../../bbs/write.cgi";
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

// 2ch
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

$post_cache = array('bbs' => $bbs, 'key' => $key, 'time' => $time, 'FROM' => $FROM, 'mail' => $mail, 'MESSAGE' => $MESSAGE, 'subject' =>$subject);

// submit �͏������ނŌŒ肵�Ă��܂��iBe�ŏ������ނ̏ꍇ�����邽�߁j
$submit = '��������';

if (!empty($_POST['newthread'])) {
    $post = array($submit_k => $submit, $bbs_k => $bbs, $subject_k => $subject, $time_k => $time, $FROM_k => $FROM, $mail_k => $mail, $MESSAGE_k => $MESSAGE);
    if (P2Util::isHostJbbsShitaraba($host)) {
        $post[$dir_k] = $dir;
    }
    $location_ht = "{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$_conf['k_at_a']}";

} else {
    $post = array($submit_k => $submit, $bbs_k => $bbs, $key_k => $key, $time_k => $time, $FROM_k => $FROM, $mail_k => $mail, $MESSAGE_k => $MESSAGE);
    if (P2Util::isHostJbbsShitaraba($host)) {
        $post[$dir_k] = $dir;
    }
    $location_ht = "{$_conf['read_php']}?host={$host}&amp;bbs={$bbs}&amp;key={$key}&amp;ls={$rescount}-&amp;refresh=1&amp;nt={$newtime}{$_conf['k_at_a']}#r{$rescount}";
}

// {{{ 2ch�Ł����O�C�����Ȃ�sid�ǉ�

if (!empty($_POST['maru_kakiko']) and P2Util::isHost2chs($host) && file_exists($_conf['sid2ch_php'])) {

    // ���O�C����A24���Ԉȏ�o�߂��Ă����玩���ă��O�C��
    if (file_exists($_conf['idpw2ch_php']) and @filemtime($_conf['sid2ch_php']) < time() - 60*60*24) {
        include_once P2_LIBRARY_DIR . '/login2ch.inc.php';
        login2ch();
    }

    include $_conf['sid2ch_php'];
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
// �������ݏ���
//================================================================

// �|�X�g���s
$posted = postIt($host, $bbs, $key, $post);

// cookie �ۑ�
FileCtl::make_datafile($cookie_file, $_conf['p2_perm']);
if ($p2cookies) {
    $cookie_cont = serialize($p2cookies);
}
if ($cookie_cont) {
    if (file_put_contents($cookie_file, $cookie_cont, LOCK_EX) === false) {
        die("Error: cannot write file.");
    }
}

// �X�����Đ����Ȃ�Asubject����key���擾
if (!empty($_POST['newthread']) && $posted) {
    sleep(1);
    $key = getKeyInSubject();
}

//=============================================
// key.idx �ۑ�
//=============================================
// <> ���O���B�B
$tag_rec['FROM'] = str_replace('<>', '', $FROM);
$tag_rec['mail'] = str_replace('<>', '', $mail);

// ���O�ƃ��[���A�󔒎��� P2NULL ���L�^
$tag_rec_n['FROM'] = ($tag_rec['FROM'] == '') ? 'P2NULL' : $tag_rec['FROM'];
$tag_rec_n['mail'] = ($tag_rec['mail'] == '') ? 'P2NULL' : $tag_rec['mail'];

if ($host && $bbs && $key) {
    $idx_host_dir = P2Util::idxDirOfHost($host);
    $keyidx = $idx_host_dir . '/' . $bbs . '/' . $key . '.idx';

    $akeyline = array();
    if ($keylines = @file($keyidx)) {
        $akeyline = explode('<>', rtrim($keylines[0]));
    }
    $sar = array($akeyline[0], $akeyline[1], $akeyline[2], $akeyline[3], $akeyline[4],
                 $akeyline[5], $akeyline[6], $tag_rec_n['FROM'], $tag_rec_n['mail'], $akeyline[9],
                 $akeyline[10], $akeyline[11], $akeyline[12]);
    P2Util::recKeyIdx($keyidx, $sar); // key.idx�ɋL�^
}

//=============================================
// �������ݗ���
//=============================================
if (empty($posted)) {
    exit;
}

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
    $newdata = "$ttitle<>$key<><><><><><>".$tag_rec['FROM'].'<>'.$tag_rec['mail']."<><>$host<>$bbs";
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
            $errmsg = sprintf('p2 error: %s(), FileCtl::filePutRename() failed . ', __FUNCTION__);
            trigger_error($errmsg, E_USER_WARNING);
            //return false;
        }
    }
}

//=============================================
// �������݃��O�L�^
//=============================================
if ($_conf['res_write_rec']) {

    // �f�[�^PHP�`���ip2_res_hist.dat.php, �^�u��؂�j�̏������ݗ������Adat�`���ip2_res_hist.dat, <>��؂�j�ɕϊ�����
    P2Util::transResHistLogPhpToDat();

    $date_and_id = date("y/m/d H:i");
    $message = htmlspecialchars($MESSAGE, ENT_NOQUOTES);
    $message = preg_replace("/\r?\n/", "<br>", $message);

    FileCtl::make_datafile($_conf['p2_res_hist_dat'], $_conf['res_write_perm']);

    $resnum = '';
    if (!empty($_POST['newthread'])) {
        $resnum = 1;
    } else {
        if ($rescount) {
            $resnum = $rescount + 1;
        }
    }

    // �V�K�f�[�^
    $newdata = $tag_rec['FROM'].'<>'.$tag_rec['mail']."<>$date_and_id<>$message<>$ttitle<>$host<>$bbs<>$key<>$resnum";

    // �܂��^�u��S�ĊO���āi2ch�̏������݂ł̓^�u�͍폜����� 2004/12/13�j
    $newdata = str_replace("\t", '', $newdata);
    // <>���^�u�ɕϊ�����
    //$newdata = str_replace('<>', "\t", $newdata);

    $cont = $newdata."\n";

    // �������ݏ���
    if (file_put_contents($_conf['p2_res_hist_dat'], $cont, FILE_APPEND | LOCK_EX) === false) {
        trigger_error('p2 error: �������݃��O�̕ۑ��Ɏ��s���܂���', E_USER_WARNING);
        // ����͎��ۂ͕\������Ȃ�����ǂ�
        //P2Util::pushInfoHtml("<p>p2 error: �������݃��O�̕ۑ��Ɏ��s���܂���</p>");
    }
}

exit;

//===========================================================
// �֐� �i���̃t�@�C�����ł̂ݗ��p�j
//===========================================================

/**
 * ���X���������� or �V�K�X���b�h�𗧂Ă�
 * �X�����Ă̏ꍇ�́A$key �͋� '' �ł悢
 *
 * @return boolean �������ݐ����Ȃ� true�A���s�Ȃ� false
 */
function postIt($host, $bbs, $key, $post)
{
    global $_conf, $post_result, $post_error2ch, $p2cookies, $popup, $rescount, $ttitle_en, $STYLE;
    global $bbs_cgi, $post_cache;

    $method = 'POST';
    $bbs_cgi_url = 'http://' . $host . $bbs_cgi;

    $URL = parse_url($bbs_cgi_url);
    if (isset($URL['query'])) {
        $URL['query'] = '?' . $URL['query'];
    } else {
        $URL['query'] = '';
    }

    // �v���L�V
    if ($_conf['proxy_use']) {
        $send_host = $_conf['proxy_host'];
        $send_port = $_conf['proxy_port'];
        $send_path = $bbs_cgi_url;
    } else {
        $send_host = $URL['host'];
        $send_port = $URL['port'];
        $send_path = $URL['path'] . $URL['query'];
    }

    !$send_port and $send_port = 80;

    $request = $method . " " . $send_path . " HTTP/1.0\r\n";
    $request .= "Host: " . $URL['host'] . "\r\n";

    $add_user_info = ''; //"; p2-client-ip: {$_SERVER['REMOTE_ADDR']}";

    $httpua_fmt = "Monazilla/1.00 (%s/%s; expack-%s%s)";
    $httpua = sprintf($httpua_fmt, $_conf['p2name'], $_conf['p2version'], $_conf['p2expack'], $add_user_info);
    $request .= "User-Agent: ".$httpua."\r\n";
    $request .= 'Referer: http://' . $URL['host'] . '/' . "\r\n";

    // �N���C�A���g��IP�𑗐M����p2�Ǝ��̃w�b�_
    //$request .= 'X-P2-CLIENT-IP: ' . $_SERVER['REMOTE_ADDR'] . "/\r\n";

    // �N�b�L�[
    $cookies_to_send = '';
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
        $cookies_to_send .= ' MDMD=' . $_conf['be_2ch_code'] . ';'; // be.2ch.net�̔F�؃R�[�h(�p�X���[�h�ł͂Ȃ�)
        $cookies_to_send .= ' DMDM=' . $_conf['be_2ch_mail'] . ';'; // be.2ch.net�̓o�^���[���A�h���X
    }

    if (!$cookies_to_send) { $cookies_to_send = ' ;'; }
    $request .= 'Cookie:'.$cookies_to_send."\r\n";
    //$request .= 'Cookie: PON='.$SPID.'; NAME='.$FROM.'; MAIL='.$mail."\r\n";

    $request .= "Connection: Close\r\n";

    // {{{ POST�̎��̓w�b�_��ǉ����Ė�����URL�G���R�[�h�����f�[�^��Y�t

    if (strtoupper($method) == "POST") {
        $post_enc = array();
        while (list($name, $value) = each($post)) {

            // ������� or be.2ch.net�Ȃ�AEUC�ɕϊ�
            if (P2Util::isHostJbbsShitaraba($host) || P2Util::isHostBe2chNet($host)) {
                $value = mb_convert_encoding($value, 'eucJP-win', 'SJIS-win');
            }

            $post_enc[] = $name."=".urlencode($value);
        }
        $postdata = implode("&", $post_enc);
        $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-Length: ".strlen($postdata)."\r\n";
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
                unset($cookies_to_send);
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

    // body
    $response = '';
    while (!feof($fp)) {
        $response .= fread($fp, 164000);
    }

    fclose($fp);

    // be.2ch or JBBS������� �����R�[�h�ϊ� EUC��SJIS
    if (P2Util::isBbsBe2chNet($host, $bbs) || P2Util::isHostJbbsShitaraba($host)) {
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

    // cookie�m�F�ipost�ă`�������W�j
    } elseif (preg_match($cookie_kakunin_match, $response, $matches)) {

        $htm['more_hidden_post'] = '';
        $more_hidden_keys = array('newthread', 'submit_beres', 'from_read_new', 'maru_kakiko', 'csrfid');
        foreach ($more_hidden_keys as $hk) {
            if (isset($_POST[$hk])) {
                $value_hd = htmlspecialchars($_POST[$hk], ENT_QUOTES);
                $htm['more_hidden_post'] .= "<input type=\"hidden\" name=\"{$hk}\" value=\"{$value_hd}\">\n";
            }
        }

        $form_pattern = '/<form method=\"?POST\"? action=\"?\\.\\.\\/test\\/(sub)?bbs\\.cgi\"?>/i';
        $form_replace = <<<EOFORM
<form method="POST" action="./post.php" accept-charset="{$_conf['accept_charset']}">
    {$_conf['detect_hint_input_ht']}
    <input type="hidden" name="host" value="{$host}">
    <input type="hidden" name="popup" value="{$popup}">
    <input type="hidden" name="rescount" value="{$rescount}">
    <input type="hidden" name="ttitle_en" value="{$ttitle_en}">
    <input type="hidden" name="sub" value="\$1">
    {$htm['more_hidden_post']}
    {$_conf['k_input_ht']}
EOFORM;
        $response = preg_replace($form_pattern, $form_replace, $response);

        $h_b = explode("</head>", $response);

        // HTML�v�����g
        echo $h_b[0];
        if (!$_conf['ktai']) {
            echo <<<EOP
    <link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=post&amp;skin={$skin_en}" type="text/css">\n
EOP;
        }
        if ($popup) {
            $mado_okisa = explode(',', $STYLE['post_pop_size']);
            $mado_okisa_x = $mado_okisa[0];
            $mado_okisa_y = $mado_okisa[1] + 200;
            echo <<<EOSCRIPT
            <script type="text/javascript">
            <!--
                resizeTo({$mado_okisa_x},{$mado_okisa_y});
            // -->
            </script>
EOSCRIPT;
        }

        echo "</head>";
        echo $h_b[1];

        return false;

    // ���̑��̓��X�|���X�����̂܂ܕ\��
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
    global $_conf, $location_ht, $popup, $STYLE, $ttitle;

    // �v�����g�p�ϐ�
    if (!$_conf['ktai']) {
        $class_ttitle = ' class="thre_title"';
    }
    $ttitle_ht = "<b{$class_ttitle}>{$ttitle}</b>";
    // 2005/03/01 aki: jig�u���E�U�ɑΉ����邽�߁A&amp; �ł͂Ȃ� & ��
    // 2005/04/25 rsk: <script>�^�O����CDATA�Ƃ��Ĉ����邽�߁A&amp;�ɂ��Ă͂����Ȃ�
    $location_noenc = preg_replace("/&amp;/", "&", $location_ht);
    if ($popup) {
        $popup_ht = <<<EOJS
<script type="text/javascript">
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

    // �v�����g
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

    if (!$_conf['ktai']) {
        echo <<<EOP
    <link rel="stylesheet" href="css.php?css=style&amp;skin={$skin_en}" type="text/css">
    <link rel="stylesheet" href="css.php?css=post&amp;skin={$skin_en}" type="text/css">\n
EOP;
        if ($popup) {
            echo <<<EOSCRIPT
            <script type="text/javascript">
            <!--
                resizeTo({$STYLE['post_pop_size']});
            // -->
            </script>
EOSCRIPT;
        }
        if ($reload) {
            echo $popup_ht;
        }
    } else {
        $kakunin_ht = <<<EOP
<p><a href="{$location_ht}">�m�F</a></p>
EOP;
    }

    echo "</head><body{$_conf['k_colors']}>\n";

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
 *  subject����key���擾����
 *
 * @return  string|false
 */
function getKeyInSubject()
{
    global $host, $bbs, $ttitle;

    require_once P2_LIBRARY_DIR . '/SubjectTxt.class.php';
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
