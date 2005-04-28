<?php

require_once './dataphp.class.php';
require_once './filectl.class.php';

/**
* p2 - p2�p�̃��[�e�B���e�B�N���X
* �C���X�^���X����炸�ɃN���X���\�b�h�ŗ��p����
* 
* @create  2004/07/15
*/
class P2Util{
    
    /**
     * �� �t�@�C�����_�E�����[�h�ۑ�����
     */
    function fileDownload($url, $localfile, $disp_error = 1)
    {
        global $_conf, $_info_msg_ht;

        $perm = (isset($_conf['dl_perm'])) ? $_conf['dl_perm'] : 0606;
    
        if (file_exists($localfile)) {
            $modified = gmdate("D, d M Y H:i:s", filemtime($localfile))." GMT";
        } else {
            $modified = false;
        }
    
        // DL
        include_once './wap.class.php';
        $wap_ua =& new UserAgent();
        $wap_ua->setTimeout($_conf['fsockopen_time_limit']);
        $wap_req =& new Request();
        $wap_req->setUrl($url);
        $wap_req->setModified($modified);
        if ($_conf['proxy_use']) {
            $wap_req->setProxy($_conf['proxy_host'], $_conf['proxy_port']);
        }
        $wap_res = $wap_ua->request($wap_req);
    
        if ($wap_res->is_error() && $disp_error) {
            $url_t = P2Util::throughIme($wap_req->url);
            $_info_msg_ht .= "<div>Error: {$wap_res->code} {$wap_res->message}<br>";
            $_info_msg_ht .= "p2 info: <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$wap_req->url}</a> �ɐڑ��ł��܂���ł����B</div>";
        }
    
        // �X�V����Ă�����
        if ($wap_res->is_success() && $wap_res->code != "304") {
            FileCtl::file_write_contents($localfile, $wap_res->content) or die("Error: {$localfile} ���X�V�ł��܂���ł���");
            chmod($localfile, $perm);
        }

        return $wap_res;
    }

    /**
     * ���p�[�~�b�V�����̒��ӂ����N����
     */
    function checkDirWritable($aDir)
    {
        global $_info_msg_ht, $_conf;
    
        // �}���`���[�U���[�h���́A��񃁃b�Z�[�W��}�����Ă���B
        
        if (!is_dir($aDir)) {
            /*
            $_info_msg_ht .= '<p class="infomsg">';
            $_info_msg_ht .= '����: �f�[�^�ۑ��p�f�B���N�g��������܂���B<br>';
            $_info_msg_ht .= $aDir."<br>";
            */
            if (is_dir(dirname(realpath($aDir))) && is_writable(dirname(realpath($aDir)))) {
                //$_info_msg_ht .= "�f�B���N�g���̎����쐬�����݂܂�...<br>";
                if (mkdir($aDir, $_conf['data_dir_perm'])) {
                    //$_info_msg_ht .= "�f�B���N�g���̎����쐬���������܂����B";
                    chmod($aDir, $_conf['data_dir_perm']);
                } else {
                    //$_info_msg_ht .= "�f�B���N�g���������쐬�ł��܂���ł����B<br>�蓮�Ńf�B���N�g�����쐬���A�p�[�~�b�V������ݒ肵�ĉ������B";
                }
            } else {
                    //$_info_msg_ht .= "�f�B���N�g�����쐬���A�p�[�~�b�V������ݒ肵�ĉ������B";
            }
            //$_info_msg_ht .= '</p>';
            
        } elseif (!is_writable($aDir)) {
            $_info_msg_ht .= '<p class="infomsg">����: �f�[�^�ۑ��p�f�B���N�g���ɏ������݌���������܂���B<br>';
            //$_info_msg_ht .= $aDir.'<br>';
            $_info_msg_ht .= '�f�B���N�g���̃p�[�~�b�V�������������ĉ������B</p>';
        }
    }

    /**
     * ���_�E�����[�hURL����L���b�V���t�@�C���p�X��Ԃ�
     */
    function cacheFileForDL($url)
    {
        global $_conf;

        $parsed = parse_url($url); // URL����

        $save_uri = $parsed['host'] ? $parsed['host'] : '';
        $save_uri .= $parsed['port'] ? ':'.$parsed['port'] : ''; 
        $save_uri .= $parsed['path'] ? $parsed['path'] : ''; 
        $save_uri .= $parsed['query'] ? '?'.$parsed['query'] : '';
        
        $cachefile = $_conf['cache_dir'] . "/".$save_uri;

        FileCtl::mkdir_for($cachefile);
        
        return $cachefile;
    }


    /**
     * �� host��bbs�������Ԃ�
     */
    function getItaName($host, $bbs)
    {
        global $_conf, $ita_names;
    
        if (!isset($ita_names["$host/$bbs"])) {
            $datdir_host = P2Util::datdirOfHost($host);
            
            $p2_setting_txt = $datdir_host."/".$bbs."/p2_setting.txt";
            
            $p2_setting_cont = @file_get_contents($p2_setting_txt);
            if ($p2_setting_cont) { $p2_setting = unserialize($p2_setting_cont); }
            $ita_names["$host/$bbs"] = $p2_setting['itaj'];
        }

        /* ��Long�̎擾
        // itaj���Z�b�g��2ch pink �Ȃ�SETTING.TXT��ǂ�ŃZ�b�g
        if (!$p2_setting['itaj']) {
            if (P2Util::isHost2chs($host)) {
                $tempfile = $_conf['pref_dir']."/SETTING.TXT.temp";
                P2Util::fileDownload("http://{$host}/{$bbs}/SETTING.TXT", $tempfile);
                // $setting = getHttpContents("http://{$host}/{$bbs}/SETTING.TXT", "", "GET", "", array(""), $httpua="p2");
                $setting = file($tempfile);
                if (file_exists($tempfile)) { unlink($tempfile); }
                if ($setting) {
                    foreach ($setting as $sl) {
                        $sl = trim($sl);
                        if (preg_match("/^BBS_TITLE=(.+)/", $sl, $matches)) {
                            $p2_setting['itaj'] = $matches[1];
                        }
                    }
                    if ($p2_setting['itaj']) {
                        FileCtl::make_datafile($p2_setting_txt, $_conf['p2_perm']);
                        if ($p2_setting) {$p2_setting_cont = serialize($p2_setting);}
                        if ($p2_setting_cont) {
                            $fp = fopen($p2_setting_txt, "wb") or die("Error: $p2_setting_txt ���X�V�ł��܂���ł���");
                            @flock($fp, LOCK_EX);
                            fputs($fp, $p2_setting_cont);
                            @flock($fp, LOCK_UN);
                            fclose($fp);
                        }
                    }
                }
            }
        }
        */
    
        return $ita_names["$host/$bbs"];
    }


    /**
     * host����dat�̕ۑ��f�B���N�g����Ԃ�
     */
    function datdirOfHost($host)
    {
        global $datdir;

        // 2channel or bbspink
        if (P2Util::isHost2chs($host)) {
            $datdir_host = $datdir."/2channel";
        // machibbs.com
        } elseif (P2Util::isHostMachiBbs($host)) {
            $datdir_host = $datdir."/machibbs.com";
        } else {
            $datdir_host = $datdir."/".$host;
        }
        return $datdir_host;
    }

    /**
     * �� failed_post_file �̃p�X�𓾂�֐�
     */
    function getFailedPostFilePath($host, $bbs, $key = false)
    {
        if ($key) {
            $filename = $key.'.failed.data.php';
        } else {
            $filename = 'failed.data.php';
        }
        return $failed_post_file = P2Util::datdirOfHost($host).'/'.$bbs.'/'.$filename;
    }


    /**
     * �����X�g�̃i�r�͈͂�Ԃ�
     */
    function getListNaviRange($disp_from, $disp_range, $disp_all_num)
    {
        $disp_end = 0;
        $disp_navi = array();

        if (!$disp_all_num) {
            $disp_navi['from'] = 0;
            $disp_navi['end'] = 0;
            $disp_navi['all_once'] = true;
            $disp_navi['mae_from'] = 1;
            $disp_navi['tugi_from'] = 1;
            return $disp_navi;
        }    

        $disp_navi['from'] = $disp_from;
    
        $disp_range = $disp_range-1;
    
        // from���z����
        if ($disp_navi['from'] > $disp_all_num) {
            $disp_navi['from'] = $disp_all_num - $disp_range;
            if ($disp_navi['from'] < 1) {
                $disp_navi['from'] = 1;
            }
            $disp_navi['end'] = $disp_all_num;
        
        // from �z���Ȃ�
        } else {
            // end �z����
            if ($disp_navi['from'] + $disp_range > $disp_all_num) {
                $disp_navi['end'] = $disp_all_num;
                if ($disp_navi['from'] == 1) {
                    $disp_navi['all_once'] = true;
                }
            // end �z���Ȃ�
            } else {
                $disp_navi['end'] = $disp_from + $disp_range;
            }
        }
        
        $disp_navi['mae_from'] = $disp_from -1 -$disp_range;
        if ($disp_navi['mae_from'] < 1) {
            $disp_navi['mae_from'] = 1;
        }    
        $disp_navi['tugi_from'] = $disp_navi['end'] +1;


        if ($disp_navi['from'] == $disp_navi['end']) {
            $range_on_st = $disp_navi['from'];
        } else {
            $range_on_st = "{$disp_navi['from']}-{$disp_navi['end']}";
        }
        $disp_navi['range_st'] = "{$range_on_st}/{$disp_all_num} ";


        return $disp_navi;
    
    }


    /**
     * �� key.idx �� data ���L�^����
     */
    function recKeyIdx($keyidx, $data)
    {
        global $_conf;
    
        $data = rtrim($data);
    
        FileCtl::make_datafile($keyidx, $_conf['key_perm']);
        $fp = fopen($keyidx, 'wb') or die("Error: {$keyidx} ���X�V�ł��܂���ł���");
        @flock($fp, LOCK_EX);
        fputs($fp, $data."\n");
        @flock($fp, LOCK_UN);
        fclose($fp);
        
        return true;
    }

    /**
     * �� subject.txt���_�E�����[�h����
     */
    function subjectDownload($in_url, $subjectfile)
    {
        global $_conf, $datdir, $_info_msg_ht;

        $perm = (isset($_conf['dl_perm'])) ? $_conf['dl_perm'] : 0606;
    
        if (file_exists($subjectfile)) {
            if ($_GET['norefresh'] || isset($_REQUEST['word'])) {
                return;    // �X�V���Ȃ��ꍇ�́A���̏�Ŕ����Ă��܂�
            } elseif ((!$_POST['newthread']) and P2Util::isSubjectFresh($subjectfile)) {
                return;    // �V�K�X�����Ď��łȂ��A�X�V���V�����ꍇ��������
            }
            $modified = gmdate("D, d M Y H:i:s", filemtime($subjectfile))." GMT";
        } else {
            $modified = false;
        }

        if (extension_loaded('zlib') and strstr($in_url, ".2ch.net")) {
            $headers = "Accept-Encoding: gzip\r\n";
        }

        // ������΂�livedoor�ړ]�ɑΉ��B�Ǎ����livedoor�Ƃ���B
        $url = P2Util::adjustHostJbbs($in_url);

        // ��DL
        include_once './wap.class.php';
        $wap_ua =& new UserAgent();
        $wap_ua->setAgent("Monazilla/1.00 (".$_conf['p2name']."/".$_conf['p2version'].")");
        $wap_ua->setTimeout($_conf['fsockopen_time_limit']);
        $wap_req =& new Request();
        $wap_req->setUrl($url);
        $wap_req->setModified($modified);
        $wap_req->setHeaders($headers);
        if ($_conf['proxy_use']) {
            $wap_req->setProxy($_conf['proxy_host'], $_conf['proxy_port']);
        }
        $wap_res = $wap_ua->request($wap_req);
    
        if ($wap_res->is_error()) {
            $url_t = P2Util::throughIme($wap_req->url);
            $_info_msg_ht .= "<div>Error: {$wap_res->code} {$wap_res->message}<br>";
            $_info_msg_ht .= "p2 info: <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$wap_req->url}</a> �ɐڑ��ł��܂���ł����B</div>";
        } else {
            $body = $wap_res->content;
        }
    
        // �� DL�������� ���� �X�V����Ă�����
        if ($wap_res->is_success() && $wap_res->code != "304") {
        
            // ������΂Ȃ�EUC��SJIS�ɕϊ�
            if (strstr($subjectfile, $datdir."/jbbs.shitaraba.com") || strstr($subjectfile, $datdir."/jbbs.livedoor.com") || strstr($subjectfile, $datdir."/jbbs.livedoor.jp")) {
                $body = mb_convert_encoding($body, 'SJIS-win', 'eucJP-win');
            }
        
            // �t�@�C���ɕۑ�����
            FileCtl::file_write_contents($subjectfile, $body) or die("Error: {$subjectfile} ���X�V�ł��܂���ł���");
            chmod($subjectfile, $perm);
            
        } else {
            // touch���邱�ƂōX�V�C���^�[�o���������̂ŁA���΂炭�ă`�F�b�N����Ȃ��Ȃ�
            // �i�ύX���Ȃ��̂ɏC�����Ԃ��X�V����̂́A�����C���i�܂Ȃ����A�����ł͓��ɖ��Ȃ����낤�j
            touch($subjectfile);
        }
    
        return $wap_res;
    }

    /**
     * �� subject.txt ���V�N�Ȃ� true ��Ԃ�
     */
    function isSubjectFresh($subjectfile)
    {
        global $_conf;
        
        // �L���b�V��������ꍇ
        if (file_exists($subjectfile)) {
            // �L���b�V���̍X�V���w�莞�Ԉȓ��Ȃ�
            // clearstatcache();
            if (@filemtime($subjectfile) > time() - $_conf['sb_dl_interval']) {
                return true;
            }
        }
        return false;
    }

    /**
     * ���z�X�g����N�b�L�[�t�@�C���p�X��Ԃ�
     */
    function cachePathForCookie($host)
    {
        global $_conf;

        $cachefile = $_conf['cookie_dir']."/{$host}/".$_conf['cookie_file_name'];

        FileCtl::mkdir_for($cachefile);
        
        return $cachefile;
    }

    /**
     * �����p�Q�[�g��ʂ����߂�URL�ϊ�
     */
    function throughIme($url)
    {
        global $_conf;
    
        // p2ime�́Aenc, m, url �̈����������Œ肳��Ă���̂Œ���
    
        if ($_conf['through_ime'] == "2ch") {
            $purl = parse_url($url);
            $url_r = $purl['scheme'] . "://ime.nu/" . $purl['host'] . $purl['path'];
        } elseif ($_conf['through_ime'] == "p2" || $_conf['through_ime'] == "p2pm") {
            $url_r = $_conf['p2ime_url'] . "?enc=1&amp;url=" . rawurlencode($url);
        } elseif ($_conf['through_ime'] == "p2m") {
            $url_r = $_conf['p2ime_url'] . "?enc=1&amp;m=1&amp;url=" . rawurlencode($url);
        } else {
            $url_r = $url;
        }
        return $url_r;
    }

    /**
     * �� host �� 2ch or bbspink �Ȃ� true ��Ԃ�
     */
    function isHost2chs($host)
    {
        if (preg_match("/\.(2ch\.net|bbspink\.com)/", $host)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * �� host �� be.2ch.net �Ȃ� true ��Ԃ�
     */
    function isHostBe2chNet($host)
    {
        if (preg_match("/^be\.2ch\.net/", $host)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * �� host �� bbspink �Ȃ� true ��Ԃ�
     */
    function isHostBbsPink($host)
    {
        if (preg_match("/\.bbspink\.com/", $host)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * �� host �� machibbs �Ȃ� true ��Ԃ�
     */
    function isHostMachiBbs($host)
    {
        if (preg_match("/\.(machibbs\.com|machi\.to)/", $host)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * �� host �� machibbs.net �܂��r�˂��� �Ȃ� true ��Ԃ�
     */
    function isHostMachiBbsNet($host)
    {
        if (preg_match("/\.(machibbs\.net)/", $host)) {
            return true;
        } else {
            return false;
        }
    }
        
    /**
     * �� host �� JBBS@������� �Ȃ� true ��Ԃ�
     */
    function isHostJbbsShitaraba($in_host)
    {
        if (preg_match("/jbbs\.shitaraba\.com|jbbs\.livedoor\.com|jbbs\.livedoor\.jp/", $in_host)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * ��JBBS@������΂̃z�X�g���ύX�ɑΉ����ĕύX����
     *
     * @param    string    $in_str    �z�X�g���ł�URL�ł��Ȃ�ł��ǂ�
     */
    function adjustHostJbbs($in_str)
    {
        if (preg_match('/jbbs\.shitaraba\.com|jbbs\.livedoor\.com/', $in_str)) {
            $str = preg_replace('/jbbs\.shitaraba\.com|jbbs\.livedoor\.com/', 'jbbs.livedoor.jp', $in_str, 1);
        } else {
            $str = $in_str;
        }
        return $str;
    }

    /**
    * �� http header no cache ���o��
    */
    function header_nocache()
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // ���t���ߋ�
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // ��ɏC������Ă���
        header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache"); // HTTP/1.0
    }

    /**
    * �� http header Content-Type �o��
    */
    function header_content_type()
    {
        header("Content-Type: text/html; charset=Shift_JIS");
    }
    
    /**
     * �����`���̏������ݗ�����V�`���ɕϊ�����
     */
    function transResHistLog()
    {
        global $_conf;

        $rh_dat_php = $_conf['pref_dir'].'/p2_res_hist.dat.php';
        $rh_dat = $_conf['pref_dir'].'/p2_res_hist.dat';

        // �������ݗ������L�^���Ȃ��ݒ�̏ꍇ�͉������Ȃ�
        if ($_conf['res_write_rec'] == 0) {
            return true;
        }

        // p2_res_hist.dat.php�i�V�j ���Ȃ��āAp2_res_hist.dat�i���j ���ǂݍ��݉\�ł�������
        if ((!file_exists($rh_dat_php)) and is_readable($rh_dat)) {
            // �ǂݍ����
            if ($cont = @file_get_contents($rh_dat)) {
                // <>��؂肩��^�u��؂�ɕύX����
                // �܂��^�u��S�ĊO����
                $cont = str_replace("\t", "", $cont);
                // <>���^�u�ɕϊ�����
                $cont = str_replace("<>", "\t", $cont);
                
                // �f�[�^PHP�`���ŕۑ�
                DataPhp::writeDataPhp($cont, $rh_dat_php, $_conf['res_write_perm']);
            }
        }
        return true;
    }

    /**
     * ���O��̃A�N�Z�X�����擾
     */
    function getLastAccessLog($logfile)
    {
        // �ǂݍ����
        if (!$lines = DataPhp::fileDataPhp($logfile)) {
            return false;
        }
        if (!isset($lines[1])) {
            return false;
        }
        $line = rtrim($lines[1]);
        $lar = explode("\t", $line);
        
        $alog['user'] = $lar[6];
        $alog['date'] = $lar[0];
        $alog['ip'] = $lar[1];
        $alog['host'] = $lar[2];
        $alog['ua'] = $lar[3];
        $alog['referer'] = $lar[4];
        
        return $alog;
    }
    
    
    /**
     * ���A�N�Z�X�������O�ɋL�^����
     */
    function recAccessLog($logfile, $maxline = 100)
    {
        global $_conf, $login;

        // ���O�t�@�C���̒��g���擾����
        if ($lines = DataPhp::fileDataPhp($logfile)) {
            // �����s����
            while (sizeof($lines) > $maxline -1) {
                array_pop($lines);
            }
        } else {
            $lines = array();
        }
        $lines = array_map('rtrim', $lines);
        
        // �ϐ��ݒ�
        $date = date('Y/m/d (D) G:i:s');
    
        // HOST���擾
        if (!$remoto_host = $_SERVER['REMOTE_HOST']) {
            $remoto_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
        }
        if ($remoto_host == $_SERVER['REMOTE_ADDR']) {
            $remoto_host = "";
        }

        if (isset($login['user'])) {
            $user = $login['user'];
        } else {
            $user = "";
        }
        
        // �V�������O�s��ݒ�
        $newdata = $date."<>".$_SERVER['REMOTE_ADDR']."<>".$remoto_host."<>".$_SERVER['HTTP_USER_AGENT']."<>".$_SERVER['HTTP_REFERER']."<>".""."<>".$user;
        //$newdata = htmlspecialchars($newdata);


        // �܂��^�u��S�ĊO����
        $newdata = str_replace("\t", "", $newdata);
        // <>���^�u�ɕϊ�����
        $newdata = str_replace("<>", "\t", $newdata);
                
        // �V�����f�[�^����ԏ�ɒǉ�
        @array_unshift($lines, $newdata);
        
        $cont = implode("\n", $lines) . "\n";
        
        // �������ݏ���
        DataPhp::writeDataPhp($cont, $logfile, $_conf['res_write_perm']);

        return true;
    }

    /**
     * ���u���E�U��Safari�n�Ȃ�true��Ԃ�
     */
    function isBrowserSafariGroup()
    {
        if (strstr($_SERVER['HTTP_USER_AGENT'], 'Safari') || strstr($_SERVER['HTTP_USER_AGENT'], 'AppleWebKit') || strstr($_SERVER['HTTP_USER_AGENT'], 'Konqueror')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 2ch�����O�C����ID��PASS�Ǝ������O�C���ݒ��ۑ�����
     */
    function saveIdPw2ch($login2chID, $login2chPW, $autoLogin2ch = '')
    {
        global $_conf;
        
        include_once './md5_crypt.inc.php';
        
        $crypted_login2chPW = md5_encrypt($login2chPW, $_conf['md5_crypt_key']);
    $idpw2ch_cont = <<<EOP
<?php
\$rec_login2chID = '{$login2chID}';
\$rec_login2chPW = '{$crypted_login2chPW}';
\$rec_autoLogin2ch = '{$autoLogin2ch}';
?>
EOP;
        FileCtl::make_datafile($_conf['idpw2ch_php'], $_conf['pass_perm']);    // �t�@�C�����Ȃ���ΐ���
        $fp = @fopen($_conf['idpw2ch_php'], 'wb') or die("p2 Error: {$_conf['idpw2ch_php']} ���X�V�ł��܂���ł���");
        @flock($fp, LOCK_EX);
        fputs($fp, $idpw2ch_cont);
        @flock($fp, LOCK_UN);
        fclose($fp);
        
        return true;
    }

    /**
     * 2ch�����O�C���̕ۑ��ς�ID��PASS�Ǝ������O�C���ݒ��ǂݍ���
     */
    function readIdPw2ch()
    {
        global $_conf;
        
        include_once './md5_crypt.inc.php';
        
        if (!file_exists($_conf['idpw2ch_php'])) {
            return false;
        }
        
        $rec_login2chID = NULL;
        $login2chPW = NULL;
        $rec_autoLogin2ch = NULL;
        
        include $_conf['idpw2ch_php'];

        // �p�X�𕡍���
        if (!empty($rec_login2chPW)) {
            $login2chPW = md5_decrypt($rec_login2chPW, $_conf['md5_crypt_key']);
        }
        
        return array($rec_login2chID, $login2chPW, $rec_autoLogin2ch);
    }
}

?>
