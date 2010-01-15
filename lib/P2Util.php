<?php
require_once P2_LIB_DIR . '/DataPhp.php';

/**
 * p2�p�̃��[�e�B���e�B�N���X
 * static���\�b�h�ŗ��p����
 * 
 * @created  2004/07/15
 */
class P2Util
{
    /**
     * �|�[�g�ԍ���������z�X�g�����擾����
     *
     * @return  string|null
     */
    function getMyHost()
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            return null;
        }
        return preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST']);
    }
    
    /**
     * @access  public
     * @return  string
     */
    function getCookieDomain()
    {
        return '';
    }

    /**
     * @access  private
     * @return  string
     */
    function encodeCookieName($key)
    {
        // �z��w��p�ɁA[]�������̂܂܎c���āAURL�G���R�[�h��������
        return $key_urlen = preg_replace_callback(
            '/[^\\[\\]]+/',
            create_function('$m', 'return rawurlencode($m[0]);'),
            $key
        );
    }
    
    /**
     * setcookie() �ł́Aau�ŕK�v��max age���ݒ肳��Ȃ��̂ŁA������𗘗p����
     *
     * @access  public
     * @return  boolean
     */
    function setCookie($key, $value = '', $expires = null, $path = '', $domain = null, $secure = false, $httponly = true)
    {
        if (is_null($domain)) {
            $domain = P2Util::getCookieDomain();
        }
        is_null($expires) and $expires = time() + 60 * 60 * 24 * 365;
        
        
        if (headers_sent()) {
            return false;
        }
        

        // Mac IE�́A����s�ǂ��N�����炵�����ۂ��̂ŁAhttponly�̑Ώۂ���O���B�i���������Ή������Ă��Ȃ��j
        // MAC IE5.1  Mozilla/4.0 (compatible; MSIE 5.16; Mac_PowerPC)
        if (preg_match('/MSIE \d\\.\d+; Mac/', geti($_SERVER['HTTP_USER_AGENT']))) {
            $httponly = false;
        }
        
        // setcookie($key, $value, $expires, $path, $domain, $secure = false, $httponly = true);
        /*
        if (is_array($name)) { 
            list($k, $v) = each($name); 
            $name = $k . '[' . $v . ']'; 
        }
        */
        if ($expires) {
            $maxage = $expires - time();
        }
        
        header(
            'Set-Cookie: '. P2Util::encodeCookieName($key) . '=' . rawurlencode($value) 
                 . (empty($domain) ? '' : '; Domain=' . $domain) 
                 . (empty($expires) ? '' : '; expires=' . gmdate('D, d-M-Y H:i:s', $expires) . ' GMT')
                 . (empty($maxage) ? '' : '; Max-Age=' . $maxage) 
                 . (empty($path) ? '' : '; Path=' . $path) 
                 . (!$secure ? '' : '; Secure') 
                 . (!$httponly ? '' : '; HttpOnly'),
             $replace = false
        );
        
        return true;
    }
    
    /**
     * �N�b�L�[����������B�ϐ� $_COOKIE ���B
     *
     * @access  public
     * @param   string  $key  key, k1[k2]
     * @return  boolean
     */
    function unsetCookie($key, $path = '', $domain = null)
    {
        if (is_null($domain)) {
            $domain = P2Util::getCookieDomain();
        }
        
        
        // �z���setcookie()���鎞�́A�L�[�������PHP�̔z��̏ꍇ�̂悤�ɁA'' �� "" �ŃN�H�[�g���Ȃ��B
        // �����̓L�[������Ƃ��ĔF������Ă��܂��B['hoge']�ł͂Ȃ��A[hoge]�Ǝw�肷��B
        // setcookie()�ŁA�ꎞ�L�[��[]�ň͂܂Ȃ��悤�ɂ���B�i�����ȏ����ƂȂ�B�j k1[k2] �Ƃ����\�L�Ŏw�肷��B
        // setcookie()�ł͔z����܂Ƃ߂č폜���邱�Ƃ͂ł��Ȃ��B 
        // k1 �̎w��� k1[k2] �͏����Ȃ��̂ŁA���̃��\�b�h�őΉ����Ă���B
        
        // $key���z��Ƃ��Ďw�肳��Ă����Ȃ�
        $cakey = null; // $_COOKIE�p�̃L�[
        if (preg_match('/\]$/', $key)) {
            // �ŏ��̃L�[��[]�ň͂�
            $cakey = preg_replace('/^([^\[]+)/', '[$1]', $key);
            // []�̃L�[��''�ň͂�
            $cakey = preg_replace('/\[([^\[\]]+)\]/', "['$1']", $cakey);
            //var_dump($cakey);
        }
        
        // �Ώ�Cookie�l���z��ł���΍ċA�������s��
        $cArray = null;
        if ($cakey) {
            eval("isset(\$_COOKIE{$cakey}) && is_array(\$_COOKIE{$cakey}) and \$cArray = \$_COOKIE{$cakey};");
        } else {
            if (isset($_COOKIE[$key]) && is_array($_COOKIE[$key])) {
                $cArray = $_COOKIE[$key];
            }
        }
        if (is_array($cArray)) {
            foreach ($cArray as $k => $v) {
                $keyr = "{$key}[{$k}]";
                if (!P2Util::unsetCookie($keyr, $path, $domain)) {
                    return false;
                }
            }
        }
        
        if (is_array($cArray) or setcookie("$key", '', time() - 3600, $path, $domain)) {
            if ($cakey) {
                eval("unset(\$_COOKIE{$cakey});");
            } else {
                unset($_COOKIE[$key]);
            }
            return true;
        }
        return false;
    }
    
    /**
     * �e�ʂ̒P�ʂ��o�C�g�\������K�X�ϊ����ĕ\������
     *
     * @param   integer  $size  bytes
     * @return  string
     */
    function getTranslatedUnitFileSize($size, $unit = null)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $k = 1024;
        foreach ($units as $u) {
            $reUnit = $u;
            if ($reUnit == $unit) {
                break;
            }
            if ($size < $k) {
                break;
            }
            $size = $size / $k;
        }
        return ceil($size) . '' . $reUnit;
    }
    
    /**
     * @access  public
     * @return  string
     */
    function getP2UA($withMonazilla = true)
    {
        global $_conf;
        
        $p2ua = $_conf['p2uaname'] . '/' . $_conf['p2version'];
        if ($withMonazilla) {
            $p2ua = 'Monazilla/1.00' . ' (' . $p2ua . ')';
        }
        return $p2ua;
    }
    
    /**
     * @return  string|null
     */
    function getSkinSetting()
    {
        global $_conf;
        
        if (UA::isK() || !$_conf['enable_skin']) {
            return null;
        }
        if (file_exists($_conf['skin_setting_path'])) {
            return $skinname = rtrim(file_get_contents($_conf['skin_setting_path']));
        }
        return null;
    }
    
    /**
     * @return  string
     */
    function getSkinFilePathBySkinName($skinname)
    {
        return P2_SKIN_DIR . '/' . rawurlencode($skinname) . '.php';
    }
    
    /**
     * 2ch�̃g���b�v�𐶐�����
     *
     * @return  string
     */
    function mkTrip($key, $length = 10)
    {
        $salt = substr($key . 'H.', 1, 2);
        $salt = preg_replace('/[^\.-z]/', '.', $salt);
        $salt = strtr($salt, ':;<=>?@[\\]^_`', 'ABCDEFGabcdef');

        return substr(crypt($key, $salt), -$length);
    }
    
    /**
     * @access  public
     * @see http://developer.emnet.ne.jp/useragent.html
     * @return  string|null
     */
    function getEMnetID()
    {
        if (array_key_exists('HTTP_X_EM_UID', $_SERVER)) {
            return $_SERVER['HTTP_X_EM_UID'];
        }
        return null;
    }
    
    /**
     * @access  public
     * @return  string|null
     */
    function getSoftBankID()
    {
        if (array_key_exists('HTTP_X_JPHONE_UID', $_SERVER)) {
            return $_SERVER['HTTP_X_JPHONE_UID'];
        }
        return null;
    }
    
    /**
     * @access  public
     * @return  string|null
     */
    function getSoftBankPcSiteBrowserSN()
    {
        // 2009/06/20 Net_UserAgent_Mobile��pcsitebrowser�����m���Ȃ�
        // Mozilla/4.08 (911T;SoftBank;SN354018011067091) NetFront/3.3
        // http://creation.mb.softbank.jp/terminal/index.html
        if (!isset($_SERVER['HTTP_USER_AGENT'])) {
            return null;
        }
        // SN�Ɏg���镶���͖��m�F������
        if (preg_match('{SoftBank;SN([0-9a-zA-Z]+)}', $_SERVER['HTTP_USER_AGENT'], $m)) {
            return $m[1];
        }
        return null;
    }
    
    /**
     * @access  public
     * @return  string
     */
    function getThreadAbornFile($host, $bbs)
    {
        return $taborn_file = P2Util::idxDirOfHostBbs($host, $bbs) . 'p2_threads_aborn.idx';
    }
    
    /**
     * @access  public
     * @return  array
     */
    function getThreadAbornKeys($host, $bbs)
    {
        $taborn_file = P2Util::getThreadAbornFile($host, $bbs);

        //$ta_num = 0;
        $ta_keys = array();
        if ($tabornlines = @file($taborn_file)) {
            //$ta_num = sizeof($tabornlines);
            foreach ($tabornlines as $l) {
                $data = explode('<>', rtrim($l));
                if ($data[1]) {
                    $ta_keys[$data[1]] = true;
                }
            }
        }
        return $ta_keys;
    }

    /**
     * ���C�ɃX���̃��X�g�f�[�^���擾����
     *
     * @return  array  �L�[ $host/$bbs/$key
     */
    /*
    function getFavListData()
    {
        global $_conf;
    
        if (!file_exists($_conf['favlist_file'])) {
            return array();
        }
        if (false === $favlines = file($_conf['favlist_file'])) {
            return false;
        }
    
        $fav_keys = array();
        if (is_array($favlines)) {
            foreach ($favlines as $l) {
                $data = explode('<>', rtrim($l));
                $key  = $data[1];
                $host = $data[10];
                $bbs  = $data[11];
                $hbk  = "$host/$bbs/$key";
                $fav_keys[$hbk] = true;
            }
        }
        return $fav_keys;
    }
    */
    
    /**
     * html_entity_decode() �͌��\�d���̂ő�ցA�A���������Ɣ������炢�̏�������
     * html_entity_decode($str, ENT_COMPAT, 'Shift_JIS')
     *
     * @access  private
     * @return  string
     */
    function htmlEntityDecodeLite($str)
    {
        return str_replace(
            array('&lt;', '&gt;', '&amp;', '&quot;'),
            array('<'   , '>'   , '&'    , '"'     ),
            $str
        );
    }
    
    /**
     * @access  private
     * @return  string
     */
    function getSamba24CacheFile($host, $bbs)
    {
        return P2Util::datDirOfHostBbs($host, $bbs) . 'samba24.txt';
    }
    
    /**
     * @access  public
     * @return  integer|false
     */
    function getSamba24TimeCache($host, $bbs)
    {
        $cacheTime = 60*60*24*3;
        $sambaFile = P2Util::getSamba24CacheFile($host, $bbs);
        if (file_exists($sambaFile) && filemtime($sambaFile) > time() - $cacheTime) {
            if (false === $cont = file_get_contents($sambaFile)) {
                return false;
            }
            return (int)$cont;
        }
        if (false !== $r = P2Util::getSamba24Time($host, $bbs)) {
            file_put_contents($sambaFile, $r, LOCK_EX);
        }
        return $r;
    }
    
    /**
     * @access  private
     * @return  integer|false
     */
    function getSamba24Time($host, $bbs)
    {
        // http://pc11.2ch.net/software/
        $url = sprintf('http://%s/%s/index.html', $host, $bbs);
        
        $cachefile = P2Util::cacheFileForDL($url);

        $r = P2Util::fileDownload($url, $cachefile, array('disp_error' => true, 'use_tmp_file' => true));
        if (!$r->is_success()) {
            return false;
        }
        
        // <br><a href="http://www.2ch.net/">�Q�����˂�</a> BBS.CGI - 2007/11/14 (SpeedyCGI) +<a href="http://bbq.uso800.net/">BBQ</a> +BBM +Rock54/54M +Samba24=30 +ByeSaru=ON<br> �y�[�W�̂����܂�����B�B��</body></html>
        //$lines = preg_split("/\n/", trim($html));
        if (!$lines = file($cachefile)) {
            return false;
        }
        $count = count($lines);
        $lasti = $count - 1;
        if (preg_match('/ \\+Samba24=(\\d+) /', $lines[$lasti], $m)) {
            return (int)$m[1];
        }
        return 0;
    }
    
    /**
     * http_build_query() �ƈقȂ�Arawurlencode���w��ł���
     * @static
     * @access  public
     * @param   array   $opts  array('encode' => 'rawurlencode', 'separator' => '&')
     * @return  string
     */
    function buildQuery($array, $opts = array())
    {
        $encode    = array_key_exists('encode', $opts)    ? $opts['encode']    : 'rawurlencode';
        $separator = empty($opts['separator']) ? '&' : $opts['separator'];
        
        $newar = array();
        foreach ($array as $k => $v) {
            if (is_null($v)) {
                continue;
            }
            $ve = $encode ? $encode($v) : $v;
            $newar[] = $k . '=' . $ve;
        }
        return implode($separator, $newar);
    }
    
    /**
     * @static
     * @access  public
     * @param   string  $uri
     * @param   array   $qs
     * @return  string
     */
    function buildQueryUri($uri, $qs, $opts = array())
    {
        if ($q = P2Util::buildQuery($qs, $opts)) {
            $separator = empty($opts['separator']) ? '&' : $opts['separator'];
            $mark = (strpos($uri, '?') === false) ? '?': $separator;
            $uri .= $mark . $q;
        }
        return $uri;
    }
    
    /**
     * @static
     * @access  public
     * @return  array
     */
    function getDefaultResValues($host, $bbs, $key)
    {
        static $cache_ = array();
        global $_conf;
        
        // �������L���b�V���i����قǂł��Ȃ����ǁj
        $ckey = md5(serialize(array($host, $bbs, $key)));
        if (array_key_exists($key, $cache_)) {
            return $cache_[$ckey];
        }

        $key_idx = P2Util::idxDirOfHostBbs($host, $bbs) . $key . '.idx';
        
        // key.idx���疼�O�ƃ��[����Ǎ���
        $FROM = null;
        $mail = null;
        if (file_exists($key_idx) and $lines = file($key_idx)) {
            $line = explode('<>', rtrim($lines[0]));
            $FROM = $line[7];
            $mail = $line[8];
        }

        // �󔒂̓��[�U�ݒ�l�ɕϊ�
        $FROM = ($FROM == '') ? $_conf['my_FROM'] : $FROM;
        $mail = ($mail == '') ? $_conf['my_mail'] : $mail;

        // 'P2NULL' �͋󔒂ɕϊ�
        $FROM = ($FROM == 'P2NULL') ? '' : $FROM;
        $mail = ($mail == 'P2NULL') ? '' : $mail;

        $MESSAGE = null;
        $subject = null;

        // �O���POST���s������ΌĂяo��
        $failed_post_file = P2Util::getFailedPostFilePath($host, $bbs, $key);
        if ($cont_srd = DataPhp::getDataPhpCont($failed_post_file)) {
            $last_posted = unserialize($cont_srd);

            $FROM    = $last_posted['FROM'];
            $mail    = $last_posted['mail'];
            $MESSAGE = $last_posted['MESSAGE'];
            $subject = $last_posted['subject'];
        }
        
        $cache_[$ckey] = array(
            'FROM'    => $FROM,
            'mail'    => $mail,
            'MESSAGE' => $MESSAGE,
            'subject' => $subject
        );
        return $cache_[$ckey];
    }
    
    /**
     * conf_user �Ƀf�[�^���Z�b�g�L�^����
     * k_use_aas, maru_kakiko
     *
     * @return  true|null|false
     */
    function setConfUser($k, $v)
    {
        global $_conf;
    
        // validate
        if ($k == 'k_use_aas') {
            if ($v != 0 && $v != 1) {
                return null;
            }
        }
    
        if (false === P2Util::updateArraySrdFile(array($k => $v), $_conf['conf_user_file'])) {
            return false;
        }
        $_conf[$k] = $v;
    
        return true;
    }

    /**
     * �t�@�C�����_�E�����[�h�ۑ�����
     *
     * @access  public
     * @param   $options  array('disp_error' => true, 'use_tmp_file' => false, 'modified' = null)
     * @return  WapResponse|false
     */
    function fileDownload($url, $localfile, $options = array())
    {
        global $_conf;
        
        $me = __CLASS__ . '::' . __FUNCTION__ . '()';
        
        $disp_error   = isset($options['disp_error'])   ? $options['disp_error']   : true;
        $use_tmp_file = isset($options['use_tmp_file']) ? $options['use_tmp_file'] : false;
        $modified     = isset($options['modified'])     ? $options['modified']     : null;
        
        if (strlen($localfile) == 0) {
            trigger_error("$me, localfile is null", E_USER_WARNING);
            return false;
        }
        
        $perm = isset($_conf['dl_perm']) ? $_conf['dl_perm'] : 0606;
        
        // {{{ modified�̎w��
        
        // �w��Ȃ��inull�j�Ȃ�A�t�@�C���̍X�V����
        if (is_null($modified) && file_exists($localfile)) {
            $modified = gmdate("D, d M Y H:i:s", filemtime($localfile)) . " GMT";
        // UNIX TIME
        } elseif (is_numeric($modified)) {
            $modified = gmdate("D, d M Y H:i:s", $modified) . " GMT";
        // ���t���ԕ�����
        } elseif (is_string($modified)) {
            // $modified �͂��̂܂�
        } else {
            // modified �w�b�_�͂Ȃ�
            $modified = false;
        }
        
        // }}}
        
        // DL
        require_once P2_LIB_DIR . '/wap.class.php';
        $wap_ua = new WapUserAgent;
        $wap_ua->setTimeout($_conf['fsockopen_time_limit']);
        
        $wap_req = new WapRequest;
        $wap_req->setUrl($url);
        $modified and $wap_req->setModified($modified);
        if ($_conf['proxy_use']) {
            $wap_req->setProxy($_conf['proxy_host'], $_conf['proxy_port']);
        }
        
        $wap_res = $wap_ua->request($wap_req);
        
        if (!$wap_res or !$wap_res->is_success() && $disp_error) {
            $url_t = P2Util::throughIme($wap_req->url);
            $atag = P2View::tagA($url_t, hs($wap_req->url), array('target' => $_conf['ext_win_target']));
            $msgHtml = sprintf(
                '<div>Error: %s %s<br>p2 info - %s �ɐڑ��ł��܂���ł����B</div>',
                hs($wap_res->code),
                hs($wap_res->message),
                $atag
            );
            P2Util::pushInfoHtml($msgHtml);
        }
        
        // �X�V����Ă�����t�@�C���ɕۑ�
        if ($wap_res->is_success() && $wap_res->code != '304') {
        
            if ($use_tmp_file) {
                if (!is_dir($_conf['tmp_dir'])) {
                    if (!FileCtl::mkdirR($_conf['tmp_dir'])) {
                        die("Error: $me, cannot mkdir.");
                        return false;
                    }
                }
                if (false === FileCtl::filePutRename($localfile, $wap_res->content)) {
                    trigger_error("$me, FileCtl::filePutRename() return false. " . $localfile, E_USER_WARNING);
                    die("Error:  $me, cannot write file.");
                    return false;
                }
            } else {
                if (false === file_put_contents($localfile, $wap_res->content, LOCK_EX)) {
                    die("Error:  $me, cannot write file.");
                    return false;
                }
            }
            chmod($localfile, $perm);
        }

        return $wap_res;
    }

    /**
     * �f�B���N�g���ɏ������݌������Ȃ���Β��ӂ�\���Z�b�g����
     *
     * @access  public
     * @return  void    P2Util::pushInfoHtml()
     */
    function checkDirWritable($aDir)
    {
        global $_conf;
        
        $msg_ht = '';
        
        // �}���`���[�U���[�h���́A��񃁃b�Z�[�W��}�����Ă���B
        
        if (!is_dir($aDir)) {
            /*
            $msg_ht .= '<p class="infomsg">';
            $msg_ht .= '����: �f�[�^�ۑ��p�f�B���N�g��������܂���B<br>';
            $msg_ht .= $aDir."<br>";
            */
            if (is_dir(dirname(realpath($aDir))) && is_writable(dirname(realpath($aDir)))) {
                //$msg_ht .= "�f�B���N�g���̎����쐬�����݂܂�...<br>";
                if (mkdir($aDir, $_conf['data_dir_perm'])) {
                    //$msg_ht .= "�f�B���N�g���̎����쐬���������܂����B";
                    chmod($aDir, $_conf['data_dir_perm']);
                } else {
                    //$msg_ht .= "�f�B���N�g���������쐬�ł��܂���ł����B<br>�蓮�Ńf�B���N�g�����쐬���A�p�[�~�b�V������ݒ肵�ĉ������B";
                }
            } else {
                    //$msg_ht .= "�f�B���N�g�����쐬���A�p�[�~�b�V������ݒ肵�ĉ������B";
            }
            //$msg_ht .= '</p>';
            
        } elseif (!is_writable($aDir)) {
            $msg_ht .= '<p class="infomsg">����: �f�[�^�ۑ��p�f�B���N�g���ɏ������݌���������܂���B<br>';
            //$msg_ht .= $aDir . '<br>';
            $msg_ht .= '�f�B���N�g���̃p�[�~�b�V�������������ĉ������B</p>';
        }
        
        $msg_ht and P2Util::pushInfoHtml($msg_ht);
    }

    /**
     * @access  public
     * @return  void    P2Util::pushInfoHtml()
     */
    function checkDirsWritable($dirs)
    {
        $checked_dirs = array();
        foreach ($dirs as $dir) {
            if (!in_array($dir, $checked_dirs)) {
                P2Util::checkDirWritable($dir);
                $checked_dirs[] = $dir;
            }
        }
    }
    
    /**
     * �_�E�����[�hURL����L���b�V���t�@�C���p�X��Ԃ�
     *
     * @access  public
     * @return  string|false
     */
    function cacheFileForDL($url)
    {
        global $_conf;

        if (!$parsed = parse_url($url)) {
            return false;
        }

        $save_uri = $parsed['host'];
        $save_uri .= isset($parsed['port']) ? ':' . $parsed['port'] : ''; 
        $save_uri .= $parsed['path'] ? $parsed['path'] : ''; 
        $save_uri .= isset($parsed['query']) ? '?' . $parsed['query'] : '';
        
        $save_uri = str_replace('%2F', '/', rawurlencode($save_uri));
        $save_uri = preg_replace('|\.+/|', '', $save_uri);
        
        $save_uri = rtrim($save_uri, '/');
        
        $cachefile = $_conf['cache_dir'] . '/' . $save_uri;

        FileCtl::mkdirFor($cachefile);
        
        return $cachefile;
    }

    /**
     * host��bbs��������擾����
     *
     * @access  public
     * @return  string|null
     */
    function getItaName($host, $bbs)
    {
        global $_conf, $ita_names;
        
        $id = $host . '/' . $bbs;
        
        if (isset($ita_names[$id])) {
            return $ita_names[$id];
        }

        $p2_setting_txt = P2Util::idxDirOfHostBbs($host, $bbs) . 'p2_setting.txt';
        
        if (file_exists($p2_setting_txt)) {

            $p2_setting_cont = file_get_contents($p2_setting_txt);

            if ($p2_setting_cont) {
                $p2_setting = unserialize($p2_setting_cont);
                if (isset($p2_setting['itaj'])) {
                    $ita_names[$id] = $p2_setting['itaj'];
                    return $ita_names[$id];
                }
            }
        }

        // ��Long�̎擾
        if (!isset($p2_setting['itaj'])) {
            require_once P2_LIB_DIR . '/BbsMap.php';
            $itaj = BbsMap::getBbsName($host, $bbs);
            if ($itaj != $bbs) {
                $ita_names[$id] = $p2_setting['itaj'] = $itaj;
                
                FileCtl::make_datafile($p2_setting_txt, $_conf['p2_perm']);
                $p2_setting_cont = serialize($p2_setting);
                if (false === FileCtl::filePutRename($p2_setting_txt, $p2_setting_cont)) {
                    die("Error: {$p2_setting_txt} ���X�V�ł��܂���ł���");
                }
                return $ita_names[$id];
            }
        }
        
        return null;
    }

    /**
     * host����dat�̕ۑ��f�B���N�g����Ԃ�
     *
     * @access  public
     * @return  string
     */
    function datDirOfHost($host, $dir_sep = false)
    {
        // �O�̂��߂Ɉ����̌^���`�F�b�N
        if (!is_bool($dir_sep)) {
            $emsg = sprintf('Error: %s - invalid $dir_sep', __FUNCTION__);
            trigger_error($emsg, E_USER_WARNING);
            die($emsg);
        }
        return P2Util::_p2DirOfHost($GLOBALS['_conf']['dat_dir'], $host, $dir_sep);
    }
    
    /**
     * host����idx�̕ۑ��f�B���N�g����Ԃ�
     *
     * @access  public
     * @return  string
     */
    function idxDirOfHost($host, $dir_sep = false)
    {
        // �O�̂��߂Ɉ����̌^���`�F�b�N
        if (!is_bool($dir_sep)) {
            $emsg = sprintf('Error: %s - invalid $dir_sep', __FUNCTION__);
            trigger_error($emsg, E_USER_WARNING);
            die($emsg);
        }
        return P2Util::_p2DirOfHost($GLOBALS['_conf']['idx_dir'], $host, $dir_sep);
    }
    
    // {{{ _p2DirOfHost()

    /**
     * host����rep2�̊e��f�[�^�ۑ��f�B���N�g����Ԃ�
     *
     * @access  private
     * @param   string  $base_dir
     * @param   string  $host
     * @param   bool    $dir_sep
     * @return  string
     */
    function _p2DirOfHost($base_dir, $host, $dir_sep = true)
    {
        static $hostDirs_ = array();
        
        $key = $base_dir . DIRECTORY_SEPARATOR . $host;
        if (array_key_exists($key, $hostDirs_)) {
            if ($dir_sep) {
                return $hostDirs_[$key] . DIRECTORY_SEPARATOR;
            }
            return $hostDirs_[$key];
        }

        $host = P2Util::normalizeHostName($host);

        // 2channel or bbspink
        if (P2Util::isHost2chs($host)) {
            $host_dir = $base_dir . DIRECTORY_SEPARATOR . '2channel';

        // machibbs.com
        } elseif (P2Util::isHostMachiBbs($host)) {
            $host_dir = $base_dir . DIRECTORY_SEPARATOR . 'machibbs.com';

        // jbbs.livedoor.jp (livedoor �����^���f����)
        } elseif (P2Util::isHostJbbsShitaraba($host)) {
            /*
            if (DIRECTORY_SEPARATOR == '/') {
                $host_dir = $base_dir . DIRECTORY_SEPARATOR . $host;
            } else {
                $host_dir = $base_dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $host);
            }
            */
            $host_dir = $base_dir . DIRECTORY_SEPARATOR . P2Util::escapeDirPath($host);

        // livedoor �����^���f���ȊO�ŃX���b�V�����̕������܂ނƂ�
        } elseif (preg_match('/[^0-9A-Za-z.\\-_]/', $host)) {
            $host_dir = $base_dir . DIRECTORY_SEPARATOR . P2Util::escapeDirPath($host);
            /*
            if (DIRECTORY_SEPARATOR == '/') {
                $old_host_dir = $base_dir . DIRECTORY_SEPARATOR . $host;
            } else {
                $old_host_dir = $base_dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $host);
            }
            if (is_dir($old_host_dir)) {
                rename($old_host_dir, $host_dir);
                clearstatcache();
            }
            */

        // ���̑�
        } else {
            $host_dir = $base_dir . DIRECTORY_SEPARATOR . P2Util::escapeDirPath($host);
        }

        // �L���b�V������
        $hostDirs_[$key] = $host_dir;

        // �f�B���N�g����؂蕶����ǉ�
        if ($dir_sep) {
            $host_dir .= DIRECTORY_SEPARATOR;
        }

        return $host_dir;
    }

    // }}}
    // {{{ datDirOfHostBbs()

    /**
     * host,bbs����dat�̕ۑ��f�B���N�g����Ԃ�
     * �f�t�H���g�Ńf�B���N�g����؂蕶����ǉ�����
     *
     * @access  public
     * @param string $host
     * @param string $bbs
     * @param bool $dir_sep
     * @return string
     */
    function datDirOfHostBbs($host, $bbs, $dir_sep = true)
    {
        $dir = P2Util::datDirOfHost($host, true) . $bbs;
        if ($dir_sep) {
            $dir .= DIRECTORY_SEPARATOR;
        }
        return $dir;
    }

    // }}}
    // {{{ idxDirOfHostBbs()

    /**
     * host,bbs����idx�̕ۑ��f�B���N�g����Ԃ�
     * �f�t�H���g�Ńf�B���N�g����؂蕶����ǉ�����
     *
     * @access  public
     * @param string $host
     * @param string $bbs
     * @param bool $dir_sep
     * @return string
     * @see P2Util::_p2DirOfHost()
     */
    function idxDirOfHostBbs($host, $bbs, $dir_sep = true)
    {
        $dir = P2Util::idxDirOfHost($host, true) . $bbs;
        if ($dir_sep) {
            $dir .= DIRECTORY_SEPARATOR;
        }
        return $dir;
    }

    // }}}
    
    /**
     * @access  public
     * @return  string
     */
    function getKeyIdxFilePath($host, $bbs, $key)
    {
        return P2Util::idxDirOfHostBbs($host, $bbs) . $key . '.idx';
    }
    
    /**
     * host����srd�̕ۑ��f�B���N�g����Ԃ�
     *
     * @access  public
     * @return  string
     */
    function srdDirOfHost($host)
    {
		return P2Util::_p2DirOfHost($GLOBALS['_conf']['srd_dir'], $host, $dir_sep);
    }
    
    /**
     * @access  public
     * @return  string
     */
    function escapeDirPath($dir_path)
    {
        // �{����rawurlencode()�ɂ��������A����݊������c�����ߍT���Ă���
        //$dir_path = str_replace('%2F', '/', rawurlencode($dir_path));
        $dir_path = preg_replace('|\.+/|', '', $dir_path);
        $dir_path = preg_replace('|:+//|', '', $dir_path); // mkdir()���u://�v���J�����g�f�B���N�g���ł���Ƃ݂Ȃ��H
        return $dir_path;
    }

    /**
     * failed_post_file �̃p�X���擾����
     *
     * @access  public
     * @return  string
     */
    function getFailedPostFilePath($host, $bbs, $key = false)
    {
        // ���X
        if ($key) {
            $filename = $key . '.failed.data.php';
        // �X������
        } else {
            $filename = 'failed.data.php';
        }
        return $failed_post_file = P2Util::idxDirOfHostBbs($host, $bbs) . $filename;
    }

    /**
     * ���X�g�̃i�r�͈͂��擾����
     *
     * @access  public
     * @return  array
     */
    function getListNaviRange($disp_from, $disp_range, $disp_all_num)
    {
        $disp_end = 0;
        $disp_navi = array();
        $disp_navi['all_once'] = false;
        
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
            $disp_navi['from'] = max(1, $disp_navi['from']);
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
        $disp_navi['mae_from'] = max(1, $disp_navi['mae_from']);
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
     * key.idx �� data ���L�^����
     *
     * @access  public
     * @param   array   $data   �v�f�̏��ԂɈӖ�����B
     */
    function recKeyIdx($keyidx, $data)
    {
        global $_conf;
        
        // ��{�͔z��Ŏ󂯎��
        if (is_array($data)) {
            $cont = implode('<>', $data);
        // ����݊��p��string����t
        } else {
            $cont = rtrim($data);
        }
        
        $cont = $cont . "\n";
        
        FileCtl::make_datafile($keyidx, $_conf['key_perm']);
        
        if (false === file_put_contents($keyidx, $cont, LOCK_EX)) {
            trigger_error("file_put_contents(" . $keyidx . ")", E_USER_WARNING);
            die("Error: cannot write file. recKeyIdx()");
            return false;
        }

        return true;
    }

    /**
     * ���p�Q�[�g��ʂ����߂�URL�ϊ����s��
     *
     * @access  public
     * @return  string
     */
    function throughIme($url)
    {
        global $_conf;
        
        // p2ime�́Aenc, m, url �̈����������Œ肳��Ă���̂Œ���
        
        // [wish] 2ch�Ɍ��炸�A
        // http://machi.to/bbs/link.cgi?URL=http://hokkaido.machibbs.com/bbs/read.cgi/hokkaidou/1244990327/
        // �̂悤�Ȃ��ꂼ���BBS�ł�ime�ɑΉ��������Ƃ���B���炩���߈�����bbs��ʂ��󂯎��K�v������B
        if ($_conf['through_ime'] == '2ch') {
            $purl = parse_url($url);
            $url_r = $purl['scheme'] . '://ime.nu/' . $purl['host'] . $purl['path'];
            
        } elseif ($_conf['through_ime'] == 'p2' || $_conf['through_ime'] == 'p2pm') {
            $url_r = $_conf['p2ime_url'] . '?enc=1&url=' . rawurlencode($url);
            
        } elseif ($_conf['through_ime'] == 'p2m') {
            $url_r = $_conf['p2ime_url'] . '?enc=1&m=1&url=' . rawurlencode($url);
            
        } else {
            $url_r = $url;
        }
        
        return $url_r;
    }
    
    // {{{ normalizeHostName()

    /**
     * host�𐳋K������
     *
     * @access  public
     * @param   string  $host
     * @return  string
     */
    function normalizeHostName($host)
    {
        $host = trim($host, '/');
        if (false !== $sp = strpos($host, '/')) {
            return strtolower(substr($host, 0, $sp)) . substr($host, $sp);
        }
        return strtolower($host);
    }

    // }}}
    
    /**
     * host �� ��������A���P�[�g http://find.2ch.net/enq/ �Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  boolean
     */
    function isHostKossoriEnq($host)
    {
        if (preg_match('{^find\\.2ch\\.net/enq}', $host)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * host �� �M���ł���f���T�C�g�Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  boolean
     */
    function isTrustedHost($host)
    {
        return (
            P2Util::isHost2chs($host) 
            || P2Util::isHostBbsPink($host) 
            || P2Util::isHostMachiBbs($host)
            || P2Util::isHostJbbsShitaraba($host)
        );
    }
    
    /**
     * host �� 2ch or bbspink �Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  boolean
     */
    function isHost2chs($host)
    {
        // find.2ch.net�i��������A���P�[�g�j�͏���
        if (P2Util::isHostFind2ch($host)) {
            return false;
        }
        return (bool)preg_match('/\\.(2ch\\.net|bbspink\\.com)$/', $host);
    }
    
    /**
     * host �� 2ch �Ȃ� true ��Ԃ��ibbspink, find.2ch�͊܂܂Ȃ��j
     *
     * @access  public
     * @return  boolean
     */
    function isHost2ch($host)
    {
        // find.2ch.net�i��������A���P�[�g�j�͏���
        if (P2Util::isHostFind2ch($host)) {
            return false;
        }
        return (bool)preg_match('/\\.(2ch\\.net)$/', $host);
    }
    
    /**
     * host �� find.2ch.net�i��������A���P�[�g�j �Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  boolean
     */
    function isHostFind2ch($host)
    {
        // find.2ch.net�i��������A���P�[�g�j�͏���
        return (bool)preg_match('{^find\\.2ch\\.net}', $host);
    }
    
    /**
     * host �� be.2ch.net �Ȃ� true ��Ԃ�
     *
     * 2006/07/27 ����͂����Â����\�b�h�B
     * 2ch�̔ړ]�ɉ����āAbbs���܂߂Ĕ��肵�Ȃ��Ă͂Ȃ�Ȃ��Ȃ����̂ŁAisBbsBe2chNet()�𗘗p����B
     * Be�̔ړ]�ŁA2ch�ɂ�EUC�̔͂Ȃ��Ȃ����悤��
     *
     * @access  public
     * @return  boolean
     * @see     isBbsBe2chNet()
     */
    function isHostBe2chNet($host)
    {
        return (bool)preg_match('/^be\\.2ch\\.net$/', $host);
    }
    
    /**
     * bbs�i�j �� be.2ch �Ȃ� true ��Ԃ�
     *
     * @since   2006/07/27
     * @access  public
     * @return  boolean
     */
    function isBbsBe2chNet($host, $bbs)
    {
        if (P2Util::isHostBe2chNet($host)) {
            return true;
        }
        // [todo] bbs���Ŕ��f���Ă��邪�ASETTING.TXT �� BBS_BE_ID=1 �Ŕ��f�����ق����悢���낤
        $be_bbs = array('be', 'nandemo', 'argue');
        if (P2Util::isHost2ch($host) && in_array($bbs, $be_bbs)) {
            return true;
        }
        return false;
    }
    
    /**
     * host �� bbspink �Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  boolean
     */
    function isHostBbsPink($host)
    {
        return (bool)preg_match('/\\.bbspink\\.com$/', $host);
    }
    
    /**
     * host �� vip2ch.com �Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  boolean
     */
    function isHostVip2ch($host)
    {
        return (bool)preg_match('/\\.(vip2ch\\.com)$/', $host);
    }

    /**
     * host �� machibbs �Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  boolean
     */
    function isHostMachiBbs($host)
    {
        return (bool)preg_match('/\\.(machibbs\\.com|machi\\.to)$/', $host);
    }

    /**
     * host �� machibbs.net �܂��r�˂��� �Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  booean
     */
    function isHostMachiBbsNet($host)
    {
        return (bool)preg_match('/\\.(machibbs\\.net)$/', $host);
    }
    
    /**
     * host �� JBBS@������� �Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  booean
     */
    function isHostJbbsShitaraba($host)
    {
        return (bool)preg_match('/^(jbbs\\.shitaraba\\.com|jbbs\\.livedoor\\.com|jbbs\\.livedoor\\.jp)/', $host);
    }

    /**
     * JBBS@������΂̃z�X�g���ύX�ɑΉ����ĕϊ�����
     *
     * @access  public
     * @param   string    $str    �z�X�g���ł�URL�ł��Ȃ�ł��ǂ�
     * @return  string
     */
    function adjustHostJbbsShitaraba($str)
    {
        return preg_replace('/jbbs\\.shitaraba\\.com|jbbs\\.livedoor\\.com/', 'jbbs.livedoor.jp', $str, 1);
    }

    /**
     * host �� cha2.com �Ȃ� true ��Ԃ�
     *
     * @access  public
     * @return  boolean
     */
    function isHostCha2($host)
    {
        return (bool)preg_match('/^(cha2\\.net)$/', $host);
    }

    /**
     * http header no cache ���o�͂���
     *
     * @access  public
     * @return  void
     */
    function headerNoCache()
    {
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");    // ���t���ߋ�
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // ��ɏC������Ă���
        header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache"); // HTTP/1.0
    }

    /**
     * http header Content-Type �o�͂���i�p�~�\�聨ini_set()�Ɂj
     *
     * @access  public
     * @return  void
     */
    function header_content_type()
    {
        header("Content-Type: text/html; charset=Shift_JIS");
    }

    /**
     * �f�[�^PHP�`���iTAB�j�̏������ݗ�����dat�`���iTAB�j�ɕϊ�����
     *
     * �ŏ��́Adat�`���i<>�j�������̂��A�f�[�^PHP�`���iTAB�j�ɂȂ�A�����Ă܂� v1.6.0 ��dat�`���i<>�j�ɖ߂���
     *
     * @access  public
     */
    function transResHistLogPhpToDat()
    {
        global $_conf;

        // �������ݗ������L�^���Ȃ��ݒ�̏ꍇ�͉������Ȃ�
        if ($_conf['res_write_rec'] == 0) {
            return true;
        }

        if (is_readable($_conf['p2_res_hist_dat_php'])) {
            require_once P2_LIB_DIR . '/DataPhp.php';
            if ($cont = DataPhp::getDataPhpCont($_conf['p2_res_hist_dat_php'])) {
                // �^�u��؂肩��<>��؂�ɕύX����
                $cont = str_replace("\t", "<>", $cont);

                // p2_res_hist.dat ������΁A���O��ς��ăo�b�N�A�b�v�B�i�����v��Ȃ��j
                if (file_exists($_conf['p2_res_hist_dat'])) {
                    $bak_file = $_conf['p2_res_hist_dat'] . '.bak';
                    if (strstr(PHP_OS, 'WIN') and file_exists($bak_file)) {
                        unlink($bak_file);
                    }
                    rename($_conf['p2_res_hist_dat'], $bak_file);
                }
                
                // �ۑ�
                FileCtl::make_datafile($_conf['p2_res_hist_dat'], $_conf['res_write_perm']);
                if (false === file_put_contents($_conf['p2_res_hist_dat'], $cont, LOCK_EX)) {
                    trigger_error("file_put_contents(" . $_conf['p2_res_hist_dat'] . ")", E_USER_WARNING);
                }
                
                // p2_res_hist.dat.php �𖼑O��ς��ăo�b�N�A�b�v�B�i�����v��Ȃ��j
                $bak_file = $_conf['p2_res_hist_dat_php'] . '.bak';
                if (strstr(PHP_OS, 'WIN') and file_exists($bak_file)) {
                    unlink($bak_file);
                }
                rename($_conf['p2_res_hist_dat_php'], $bak_file);
            }
        }
        
        return true;
    }

    /**
     * dat�`���i<>�j�̏������ݗ������f�[�^PHP�`���iTAB�j�ɕϊ�����
     * �����݂͗��p���Ă��Ȃ�
     *
     * @access  public
     * @return  boolean
     */
    function transResHistLogDatToPhp()
    {
        global $_conf;

        // �������ݗ������L�^���Ȃ��ݒ�̏ꍇ�͉������Ȃ�
        if ($_conf['res_write_rec'] == 0) {
            return true;
        }

        // p2_res_hist.dat.php ���Ȃ��āAp2_res_hist.dat ���ǂݍ��݉\�ł�������
        if ((!file_exists($_conf['p2_res_hist_dat_php'])) and is_readable($_conf['p2_res_hist_dat'])) {
            if ($cont = file_get_contents($_conf['p2_res_hist_dat'])) {
                // <>��؂肩��^�u��؂�ɕύX����
                // �܂��^�u��S�ĊO����
                $cont = str_replace("\t", '', $cont);
                // <>���^�u�ɕϊ�����
                $cont = str_replace('<>', "\t", $cont);
                
                // �f�[�^PHP�`���ŕۑ�
                if (!DataPhp::writeDataPhp($_conf['p2_res_hist_dat_php'], $cont, $_conf['res_write_perm'])) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * �O��̃A�N�Z�X�i���O�C���j�����擾����
     *
     * @access  public
     * @return  array
     */
    function getLastAccessLog($logfile)
    {
        if (!$lines = DataPhp::fileDataPhp($logfile)) {
            return false;
        }
        if (!isset($lines[1])) {
            return false;
        }
        $line = rtrim($lines[1]);
        $lar = explode("\t", $line);
        
        $alog['user']    = $lar[6];
        $alog['date']    = $lar[0];
        $alog['ip']      = $lar[1];
        $alog['host']    = $lar[2];
        $alog['ua']      = $lar[3];
        $alog['referer'] = $lar[4];
        
        return $alog;
    }
    
    
    /**
     * �A�N�Z�X�i���O�C���j�������O�ɋL�^����
     *
     * @access  public
     * @return  boolean
     */
    function recAccessLog($logfile, $maxline = 100, $format = 'dataphp')
    {
        global $_conf, $_login;
        
        // ���O�t�@�C���̒��g���擾����
        $lines = array();
        if (file_exists($logfile)) {
            if ($format == 'dataphp') {
                $lines = DataPhp::fileDataPhp($logfile);
            } else {
                $lines = file($logfile);
            }
        }
        
        if ($lines) {
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

        $user = isset($_login->user_u) ? $_login->user_u : "";
        
        // �V�������O�s��ݒ�
        $newdata = implode('<>', array(
            $date, $_SERVER['REMOTE_ADDR'], P2Util::getRemoteHost(),
            geti($_SERVER['HTTP_USER_AGENT']), geti($_SERVER['HTTP_REFERER']), '', $user
        ));
        //$newdata = htmlspecialchars($newdata, ENT_QUOTES);

        // �܂��^�u��S�ĊO����
        $newdata = str_replace("\t", "", $newdata);
        // <>���^�u�ɕϊ�����
        $newdata = str_replace("<>", "\t", $newdata);

        // �V�����f�[�^����ԏ�ɒǉ�
        @array_unshift($lines, $newdata);
        
        $cont = implode("\n", $lines) . "\n";

        FileCtl::make_datafile($logfile, $_conf['p2_perm']);
        
        // �������ݏ���
        if ($format == 'dataphp') {
            if (!DataPhp::writeDataPhp($logfile, $cont, $_conf['p2_perm'])) {
                return false;
            }
        } else {
            if (false === file_put_contents($logfile, $cont, LOCK_EX)) {
                trigger_error("file_put_contents(" . $logfile . ")", E_USER_WARNING);
                return false;
            }
        }
        
        return true;
    }

    /**
     * 2ch�����O�C����ID��PASS�Ǝ������O�C���ݒ��ۑ�����
     *
     * @access  public
     * @return  boolean
     */
    function saveIdPw2ch($login2chID, $login2chPW, $autoLogin2ch = 0)
    {
        global $_conf;
        
        require_once P2_LIB_DIR . '/md5_crypt.funcs.php';
        
        // �O�̂��߁A�����ł��s���ȕ�����͒e���Ă���
        require_once P2_LIB_DIR . '/P2Validate.php';
        
        // 2ch ID (���A�h)
        if ($login2chID and $errmsg = P2Validate::mail($login2chID)) {
            //P2Util::pushInfoHtml('<p>p2 error: �g�p�ł��Ȃ�ID�����񂪊܂܂�Ă��܂�</p>');
            trigger_error($errmsg, E_USER_WARNING);
            return false;;
        }

        // ���m�ȋ�������͕s��
        if ($login2chPW and $errmsg = P2Validate::login2chPW($login2chPW)) {
            //P2Util::pushInfoHtml('<p>p2 error: �g�p�ł��Ȃ��p�X���[�h�����񂪊܂܂�Ă��܂�</p>');
            trigger_error($errmsg, E_USER_WARNING);
            return false;;
        }
        
        $autoLogin2ch = intval($autoLogin2ch);
        
        $crypted_login2chPW = md5_encrypt($login2chPW, P2Util::getMd5CryptPass());
        $idpw2ch_cont = <<<EOP
<?php
\$rec_login2chID = '{$login2chID}';
\$rec_login2chPW = '{$crypted_login2chPW}';
\$rec_autoLogin2ch = '{$autoLogin2ch}';
?>
EOP;
        FileCtl::make_datafile($_conf['idpw2ch_php'], $_conf['pass_perm']);
        if (false === file_put_contents($_conf['idpw2ch_php'], $idpw2ch_cont, LOCK_EX)) {
            p2die('�f�[�^���X�V�ł��܂���ł���');
            return false;
        }
        
        return true;
    }

    /**
     * 2ch�����O�C���̕ۑ��ς�ID��PASS�Ǝ������O�C���ݒ��ǂݍ���
     *
     * @access  public
     * @return  array|false
     */
    function readIdPw2ch()
    {
        global $_conf;
        
        require_once P2_LIB_DIR . '/md5_crypt.funcs.php';
        
        if (!file_exists($_conf['idpw2ch_php'])) {
            return false;
        }
        
        $rec_login2chID   = null;
        $login2chPW       = null;
        $rec_autoLogin2ch = null;
        
        include $_conf['idpw2ch_php'];

        // �p�X�𕡍���
        if (!is_null($rec_login2chPW)) {
            $login2chPW = md5_decrypt($rec_login2chPW, P2Util::getMd5CryptPass());
        }
        
        return array($rec_login2chID, $login2chPW, $rec_autoLogin2ch);
    }
    
    /**
     * md5_encrypt, md5_decrypt �̂��߂� password(salt) �𓾂�
     * �i2ch�����O�C����PASS�ۑ��ɗ��p���Ă���j
     *
     * @static
     * @access  private
     * @return  string
     */
    function getMd5CryptPass()
    {
        global $_login;
        
        return md5($_login->user . $_SERVER['SERVER_SOFTWARE']);
    }
    
    /**
     * @static
     * @access  public
     * @return  string
     */
    function getCsrfId()
    {
        global $_login;
        
        // docomo��utf��UA���ς�����Ⴄ�̂ŁAUA�͊O���Ă��܂���
        // return md5($_login->user . $_login->pass_x . geti($_SERVER['HTTP_USER_AGENT']));
        return md5($_login->user . $_login->pass_x);
    }
    
    /**
     * 403 Fobbiden��HTML�o�͂���
     * 2007/01/20 ���ӁFEZweb�ł́A403�y�[�W�Ŗ{�����\������Ȃ����Ƃ��m�F�����B
     *
     * @access  public
     * @return  void
     */
    function print403Html($msg_html = '', $die = true)
    {
        header('HTTP/1.0 403 Forbidden');
        ?>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <title>403 Forbidden</title>
</head>
<body>
    <h1>403 Forbidden</h1>
    <p><?php echo $msg_html; ?></p>
</body>
</html>
<?php
        // IE�f�t�H���g��403���b�Z�[�W��\�������Ȃ��悤�ɗe�ʂ��҂����߃_�~�[�X�y�[�X���o�͂���
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT']: null;
        if (strstr($ua, 'MSIE')) {
            for ($i = 0; $i < 512; $i++) {
                echo ' ';
            }
        }
        
        $die and die;
    }
    
    // {{{ session_gc()

    /**
     * �Z�b�V�����t�@�C���̃K�[�x�b�W�R���N�V����
     *
     * session.save_path�̃p�X�̐[����2���傫���ꍇ�A�K�[�x�b�W�R���N�V�����͍s���Ȃ�����
     * �����ŃK�[�x�b�W�R���N�V�������Ȃ��Ƃ����Ȃ��B
     *
     * @access  public
     * @return  void
     *
     * @link http://jp.php.net/manual/ja/ref.session.php#ini.session.save-path
     */
    function session_gc()
    {
        global $_conf;

        if (session_module_name() != 'files') {
            return;
        }

        $d = (int)ini_get('session.gc_divisor');
        $p = (int)ini_get('session.gc_probability');
        mt_srand();
        if (mt_rand(1, $d) <= $p) {
            $m = (int)ini_get('session.gc_maxlifetime');
            FileCtl::garbageCollection($_conf['session_dir'], $m);
        }
    }

    // }}}

    /**
     * Web�y�[�W���擾����
     *
     * �����Ƃ݂Ȃ��R�[�h
     * 200 OK
     * 206 Partial Content
     *
     * �X�V���Ȃ����null��Ԃ�
     * 304 Not Modified
     *
     * @static
     * @access  public
     * @param   string  $url     URL
     * @param   integer $code    ���X�|���X�R�[�h
     * @param   integer $timeout �ڑ��^�C���A�E�g���ԕb
     * @param   array   $headers �ǉ��w�b�_�i�t�B�[���h�L�[ => �t�B�[���h�l�j
     * @return  string|null|false     ����������y�[�W���e|304|���s
     */
    function getWebPage($url, &$code, &$error_msg, $timeout = 15, $headers = array())
    {
        // ���� &$code = null �͋��o�[�W������PHP�ł͕s��
        
        require_once 'HTTP/Request.php';
    
        $params = array('timeout' => $timeout);
        
        if (!empty($GLOBALS['_conf']['proxy_use'])) {
            $params['proxy_host'] = $GLOBALS['_conf']['proxy_host'];
            $params['proxy_port'] = $GLOBALS['_conf']['proxy_port'];
        }
        
        $req = new HTTP_Request($url, $params);
        
        // If-Modified-Since => gmdate('D, d M Y H:i:s', time()) . ' GMT';
        
        if ($headers) {
            foreach ($headers as $k => $v) {
                $req->addHeader($k, $v);
            }
        }

        $response = $req->sendRequest();

        if (PEAR::isError($response)) {
            $error_msg = $response->getMessage();
        } else {
            $code = $req->getResponseCode();
            // �����Ƃ݂Ȃ��R�[�h
            if ($code == 200 || $code == 206) {
                return $req->getResponseBody();
            // �X�V���Ȃ����null��Ԃ�
            } elseif ($code == 304) {
                // 304�̎��́A$req->getResponseBody() �͋󕶎�""�ƂȂ�
                return null;
            } else {
                //var_dump($req->getResponseHeader());
                $error_msg = $code;
            }
        }
        
        return false;
    }

    /**
     * �g�т̌ŗL�[��ID���ABBM�ɋK������Ă��邩�ǂ�����₢���킹��
     *
     * http://qb5.2ch.net/test/read.cgi/operate/1093340433/99
     * http://qb5.2ch.net/test/read.cgi/operate/1093340433/241
     * my $AHOST = "$NOWTIME.$$.c.$FORM{'bbs'}.$FORM{'key'}.A.B.C.D.E.$idnotane.bbm.2ch.net"; 
     *
     * @static
     * @access  public
     * @return  boolean
     */
    function isKIDBurnedByBBM($sn, $bbs = null, $key = null)
    {
        if (!$sn) {
            trigger_error(sprintf('%s(): no $sn', __FUNCTION__), E_USER_WARNING);
            return false;
        }
        
        $kid = P2Util::getKidForBBM($sn);
    
        //$bbm_host = 'niku.2ch.net';
        
        !$bbs and $bbs = 'd';
        !$key and $key = 'e';
        
        $query_host = time() . ".b.c.{$bbs}.{$key}.A.B.C.D.E." . $kid . '.bbm.2ch.net';
    
        // �₢���킹�����s
        $result_addr = gethostbyname($query_host);
        /* var_dump($query_addr, $result_addr); */
        if ($result_addr == '127.0.0.2') {
            return TRUE; // BBM�ɏĂ���Ă���
        }
        return FALSE; // BBM�ɏĂ���Ă��Ȃ�
    }
    
    /**
     * �g�т̌ŗL�[��ID��BBM�p�ɐ��K������
     *
     * http://qb5.2ch.net/test/read.cgi/operate/1093340433/99
     * http://qb5.2ch.net/test/read.cgi/operate/1093340433/241
     *
     * @static
     * @access  private
     * @param   string  $kid  �g�ьŗL�[��ID
     * @return  string
     */
    function getKidForBBM($kid)
    {
        // http://qb5.2ch.net/test/read.cgi/operate/1208685863/808
        // �EBBM�o�^���u��7������BBM�o�^�����͂��ꂽ��(�Ⴆ��AbcD123)�A 
        // AbcD123-0110000 �� BBM �ɓo�^����悤�ɂ���
        $kid = P2Util::getKidForImodeID($kid);
        
        // �A���_�[�X�R�A�́A�n�C�t���ɕϊ�����
        // http://qb5.2ch.net/test/read.cgi/operate/1093340433/84
        $kid = str_replace('_', '-', $kid);
        
        $kid = preg_replace('/\.ezweb\.ne\.jp$/' , '', $kid);

        return $kid;
    }
    
    /**
     * @static
     * @access  private
     * @param   string  $kid  �g�ьŗL�[��ID
     * @return  string
     */
    function getKidForImodeID($kid)
    {
        if (preg_match('/^[0-9A-Za-z]{7}$/', $kid)) {
            $kid = $kid . '-' . strtr(
                $kid,
                '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz',
                '00000000000000000000000000000000000011111111111111111111111111'
            );
        }
        return $kid;
    }
    
    /**
     * ���݂�URL���擾����iGET�N�G���[�͂Ȃ��j
     *
     * @see  http://ns1.php.gr.jp/pipermail/php-users/2003-June/016472.html
     *
     * @static
     * @access  public
     * @return  string
     */
    function getMyUrl()
    {
        $s = empty($_SERVER['HTTPS']) ? '' : 's';
        // �|�[�g�ԍ����w�肵�����́A$_SERVER['HTTP_HOST'] �Ƀ|�[�g�ԍ��܂Ŋ܂܂��悤��
        $url = "http{$s}://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'];
        
        return $url;
    }
    
    /**
     * �V���v����HTML��\������
     * �i�P�Ƀe�L�X�g�����𑗂��au�Ȃǂ́A�\�����Ă���Ȃ��̂Łj
     *
     * @static
     * @access  public
     * @return  void
     */
    function printSimpleHtml($body_html)
    {
        echo "<html><body>{$body_html}</body></html>";
    }
    
    /**
     * �t�@�C�����w�肵�āA�V���A���C�Y���ꂽ�z��f�[�^���}�[�W�X�V����i�����̃f�[�^�ɏ㏑���}�[�W����j
     *
     * @static
     * @access  public
     * @param   array    $data
     * @param   string   $file
     * @return  boolean
     */
    function updateArraySrdFile($data, $file)
    {
        // �����̃f�[�^���}�[�W�擾
        if (file_exists($file)) {
            if ($cont = file_get_contents($file)) {
                $array = unserialize($cont);
                if (is_array($array)) {
                    $data = array_merge($array, $data);
                }
            }
        }
        
        // �}�[�W�X�V�Ȃ̂ŏ㏑���f�[�^������ۂ̎��͉������Ȃ�
        if (empty($data) || !is_array($data)) {
            return false;
        }

        if (false === file_put_contents($file, serialize($data), LOCK_EX)) {
            trigger_error("file_put_contents(" . hs($file) . ")", E_USER_WARNING);
            return false;
        }
        return true;
    }
    
    /**
     * 2006/11/24 $_info_msg_ht �𒼐ڎQ�Ƃ���̂͂�߂Ă��̃��\�b�h��ʂ�
     *
     * @static
     * @access  public
     * @return  void
     */
    function pushInfoHtml($html)
    {
        global $_info_msg_ht;
        
        if (!isset($_info_msg_ht)) {
            $_info_msg_ht = $html;
        } else {
            $_info_msg_ht .= $html;
        }
    }
    
    /**
     * @static
     * @access  public
     * @return  void
     */
    function printInfoHtml()
    {
        global $_info_msg_ht, $_conf;
        
        if (!isset($_info_msg_ht)) {
            return;
        }
        
        if (UA::isK() && $_conf['k_save_packet']) {
            echo mb_convert_kana($_info_msg_ht, 'rnsk');
        } else {
            echo $_info_msg_ht;
        }
        
        $_info_msg_ht = '';
    }
    
    /**
     * @static
     * @access  public
     * @return  string|null
     */
    function getInfoHtml()
    {
        global $_info_msg_ht;
        
        if (!isset($_info_msg_ht)) {
            return null;
        }
        
        $info_msg_ht = $_info_msg_ht;
        $_info_msg_ht = '';
        
        return $info_msg_ht;
    }

    /**
     * �O������̕ϐ��iGET, POST, [COOKIE]�j���擾����
     *
     * @static
     * @access  public
     * @param   string|array  $key      �擾�Ώۂ̃L�[
     * @param   mixed         $alt      �l�� !isset() �̏ꍇ�̑�֒l
     * @param   array|string  $methods  �擾�Ώۃ��\�b�h�i�z��Ȃ�O��D��j
     * @return  string|array  �L�[���z��Ŏw�肳��Ă���΁A�z��ŕԂ�
     */
    function getReq($key, $alt = null, $methods = array('GET', 'POST'))
    {
        if (is_array($key)) {
            $req = array_flip($key);
            foreach ($req as $k => $v) {
                $req[$k] = $alt;
            }
        } else {
            $req = $alt;
        }
        
        if (!is_array($methods)) {
            $methods = array($methods);
        } else {
            $methods = array_reverse($methods);
        }
        
        foreach ($methods as $method) {
            $globalsName = '_' . $method;
            if (is_array($key)) {
                foreach ($key as $v) {
                    isset($GLOBALS[$globalsName][$v]) and $req[$v] = $GLOBALS[$globalsName][$v];
                }
            } else {
                isset($GLOBALS[$globalsName][$key]) and $req = $GLOBALS[$globalsName][$key];
            }
        }
        
        return $req;
    }

    /**
     * �i�A�N�Z�X���[�U�́j�����[�g�z�X�g���擾����
     *
     * @param   string  $empty  gethostbyaddr() ��IP��Ԃ������̎��̑�֕����B
     * @return  string
     */
    function getRemoteHost($empty = '')
    {
        // gethostbyaddr() �́A�������s�X�N���v�g���ł��L���b�V�����Ȃ��悤�Ȃ̂ŃL���b�V������
        static $gethostbyaddr_ = null;
        
        if (isset($_SERVER['REMOTE_HOST'])) {
            return $_SERVER['REMOTE_HOST'];
        }
        
        if (php_sapi_name() == 'cli') {
            return 'cli';
        }
        
        if (is_null($gethostbyaddr_)) {
            require_once P2_LIB_DIR . '/HostCheck.php';
            $gethostbyaddr_ = HostCheck::cachedGetHostByAddr($_SERVER['REMOTE_ADDR']);
        }
        
        return ($gethostbyaddr_ == $_SERVER['REMOTE_ADDR']) ? $empty : $gethostbyaddr_;
    }
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
