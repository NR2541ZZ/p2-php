<?php
/**
 * p2 - 2ch �� SETTING.TXT �������N���X
 * http://news19.2ch.net/newsplus/SETTING.TXT
 *
 * @created  2006/02/27
 */
class SettingTxt{
    
    var $host;
    var $bbs;
    var $url;           // SETTING.TXT ��URL
    var $setting_txt;   // SETTING.TXT ���[�J���ۑ��t�@�C���p�X
    var $setting_cache; // p2_kb_setting.srd $this->setting_array �� serialize() �����f�[�^�t�@�C��
    var $setting_array = array(); // SETTING.TXT���p�[�X�����A�z�z��
    var $cache_interval;
    
    /**
     * @constructor
     */
    function SettingTxt($host, $bbs)
    {
        $this->cache_interval = 60 * 60 * 12; // �L���b�V����12���ԗL��
        
        $this->host = $host;
        $this->bbs =  $bbs;
        
        $dat_bbs_dir = P2Util::datDirOfHost($this->host) . '/' . $this->bbs;
        $this->setting_txt = $dat_bbs_dir . '/SETTING.TXT';
        $this->setting_cache = $dat_bbs_dir . '/p2_kb_setting.srd';
        
        $this->url = "http://" . $this->host . '/' . $this->bbs . "/SETTING.TXT";
        //$this->url = P2Util::adjustHostJbbs($this->url); // ������΂�livedoor�ړ]�ɑΉ��B�Ǎ����livedoor�Ƃ���B
        
        // SETTING.TXT ���_�E�����[�h���Z�b�g����
        $this->dlAndSetData();
    }

    /**
     * SETTING.TXT ���_�E�����[�h���Z�b�g����
     *
     * @access  private
     * @return  boolean  �Z�b�g�ł���� true
     */
    function dlAndSetData()
    {
        $this->downloadSettingTxt();
        
        if ($this->setSettingArray()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * SETTING.TXT ���_�E�����[�h���āA�p�[�X���āA�L���b�V������
     *
     * @access  public
     * @return  boolean  ���s����
     */
    function downloadSettingTxt()
    {
        global $_conf;

        $perm = !empty($_conf['dl_perm']) ? $_conf['dl_perm'] : 0606;

        FileCtl::mkdirFor($this->setting_txt); // �f�B���N�g����������΍��
    
        if (file_exists($this->setting_cache) && file_exists($this->setting_txt)) {
            // �X�V���Ȃ��ꍇ�́A���̏�Ŕ����Ă��܂�
            if (!empty($_GET['norefresh']) || isset($_REQUEST['word'])) {
                return true;
            // �L���b�V�����V�����ꍇ��������
            } elseif ($this->isCacheFresh()) {
                return true;
            }
            $modified = gmdate("D, d M Y H:i:s", filemtime($this->setting_txt)) . " GMT";
        } else {
            $modified = false;
        }

        // DL
        require_once "HTTP/Request.php";
        
        $params = array();
        $params['timeout'] = $_conf['fsockopen_time_limit'];
        if ($_conf['proxy_use']) {
            $params['proxy_host'] = $_conf['proxy_host'];
            $params['proxy_port'] = $_conf['proxy_port'];
        }
        $req =& new HTTP_Request($this->url, $params);
        $modified && $req->addHeader("If-Modified-Since", $modified);
        $req->addHeader('User-Agent', 'Monazilla/1.00 (' . $_conf['p2name'] . '/' . $_conf['p2version'] . ')');
    
        $response = $req->sendRequest();

        if (PEAR::isError($response)) {
            $error_msg = $response->getMessage();
        } else {
            $code = $req->getResponseCode();
            
            if ($code == 302) {
                // �z�X�g�̈ړ]��ǐ�
                require_once P2_LIB_DIR . '/BbsMap.class.php';
                $new_host = BbsMap::getCurrentHost($this->host, $this->bbs);
                if ($new_host != $this->host) {
                    $aNewSettingTxt = &new SettingTxt($new_host, $this->bbs);
                    $body = $aNewSettingTxt->downloadSettingTxt();
                    return true;
                }
            }
            
            if (!($code == 200 || $code == 206 || $code == 304)) {
                //var_dump($req->getResponseHeader());
                $error_msg = $code;
            }
        }
        
        // DL�G���[
        if (isset($error_msg) && strlen($error_msg) > 0) {
            $url_t = P2Util::throughIme($this->url);
            P2Util::pushInfoHtml("<div>Error: {$error_msg}<br>"
                                . "p2 info: <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$this->url}</a> �ɐڑ��ł��܂���ł����B</div>");
            touch($this->setting_txt); // DL���s�����ꍇ�� touch
            return false;
            
        }
        
        $body = $req->getResponseBody();

        // DL�������� ���� �X�V����Ă�����ۑ�
        if ($body && $code != "304") {
        
            // ������� or be.2ch.net �Ȃ�EUC��SJIS�ɕϊ�
            if (P2Util::isHostJbbsShitaraba($this->host) || P2Util::isHostBe2chNet($this->host)) {
                $body = mb_convert_encoding($body, 'SJIS-win', 'eucJP-win');
            }
            
            if (FileCtl::filePutRename($this->setting_txt, $body) === false) {
                die("Error: cannot write file");
            }
            chmod($this->setting_txt, $perm);
            
            // �p�[�X���ăL���b�V����ۑ�����
            if (!$this->cacheParsedSettingTxt()) {
                return false;
            }
            
        } else {
            // touch���邱�ƂōX�V�C���^�[�o���������̂ŁA���΂炭�ă`�F�b�N����Ȃ��Ȃ�
            touch($this->setting_txt);
        }
        
        return true;
    }
    
    
    /**
     * �L���b�V�����V�N�Ȃ� true ��Ԃ�
     *
     * @acccess  private
     * @return   boolean
     */
    function isCacheFresh()
    {
        if (file_exists($this->setting_cache)) {
            // �L���b�V���̍X�V���w�莞�Ԉȓ��Ȃ�
            // clearstatcache();
            if (filemtime($this->setting_cache) > time() - $this->cache_interval) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * SETTING.TXT ���p�[�X���ăL���b�V���ۑ�����
     *
     * ��������΁A$this->setting_array ���Z�b�g�����
     *
     * @acccess  private
     * @return   boolean  ���s����
     */
    function cacheParsedSettingTxt()
    {
        global $_conf;
        
        $this->setting_array = array();
        
        if (!$lines = file($this->setting_txt)) {
            return false;
        }
        
        foreach ($lines as $line) {
            if (strstr($line, '=')) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                $this->setting_array[$key] = $value;
            }
        }
        $this->setting_array['p2version'] = $_conf['p2version'];
        
        // �p�[�X�L���b�V���t�@�C����ۑ�����
        if (FileCtl::filePutRename($this->setting_cache, serialize($this->setting_array)) === false) {
            return false;
        }
        
        return true;
    }
    
    /**
     * SETTING.TXT �̃p�[�X�f�[�^��ǂݍ���
     *
     * ��������΁A$this->setting_array ���Z�b�g�����
     *
     * @access  private
     * @return  boolean  ���s����
     */
    function setSettingArray()
    {
        global $_conf;

        if (!file_exists($this->setting_cache)) {
            return false;
        }

        $this->setting_array = unserialize(file_get_contents($this->setting_cache));
        
        /*
        if ($this->setting_array['p2version'] != $_conf['p2version']) {
            unlink($this->setting_cache);
            unlink($this->setting_txt);
        }
        */
        
        if (!empty($this->setting_array)) {
            return true;
        } else {
            return false;
        }
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
