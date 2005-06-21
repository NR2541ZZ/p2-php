<?php

/*
define(P2_SUBJECT_TXT_STORAGE, 'eashm');

[�d�l] shm���ƒ����L���b�V�����Ȃ�
[�d�l] shm����modified�����Ȃ�

shm�ɂ��Ă��p�t�H�[�}���X�͂قƂ�Ǖς��Ȃ�
*/

/**
 * SubjectTxt�N���X
 */
class SubjectTxt{
    
    var $host;
    var $bbs;
    var $subject_file;
    var $subject_url;
    var $subject_lines;
    var $storage; // file, eashm(eAccelerator shm)
    
    /**
     * �R���X�g���N�^
     */
    function SubjectTxt($host, $bbs)
    {
        $this->host = $host;
        $this->bbs =  $bbs;
        if (defined('P2_SUBJECT_TXT_STORAGE') && P2_SUBJECT_TXT_STORAGE == 'eashm') {
            $this->storage = P2_SUBJECT_TXT_STORAGE;
        } else {
            $this->storage = 'file';
        }
        
        $this->subject_file = P2Util::datDirOfHost($this->host) . '/' . $this->bbs . '/subject.txt';
        
        $this->subject_url = "http://".$this->host.'/'.$this->bbs."/subject.txt";

        // ������΂�livedoor�ړ]�ɑΉ��B�Ǎ����livedoor�Ƃ���B
        $this->subject_url = P2Util::adjustHostJbbs($this->subject_url);
        
        // subject.txt���_�E�����[�h���Z�b�g����
        $this->dlAndSetSubject();
    }

    /**
     * subject.txt���_�E�����[�h���Z�b�g����
     */
    function dlAndSetSubject()
    {
        if ($this->storage == 'eashm') {
            $cont = eaccelerator_get("$this->host/$this->bbs");
        } else {
            $cont = '';
        }
        if (!$cont || !empty($_POST['newthread'])) {
            $cont = $this->downloadSubject();
        }
        $this->setSubjectLines($cont);
        
        return true;
    }

    /**
     * subject.txt���_�E�����[�h����
     */
    function &downloadSubject()
    {
        global $_conf, $_info_msg_ht;

        $perm = (isset($_conf['dl_perm'])) ? $_conf['dl_perm'] : 0606;

        if ($this->storage == 'file') {
            FileCtl::mkdir_for($this->subject_file); // �f�B���N�g����������΍��
        
            if (file_exists($this->subject_file)) {
                if ($_GET['norefresh'] || isset($_REQUEST['word'])) {
                    return;    // �X�V���Ȃ��ꍇ�́A���̏�Ŕ����Ă��܂�
                } elseif (empty($_POST['newthread']) and $this->isSubjectTxtFresh()) {
                    return;    // �V�K�X�����Ď��łȂ��A�X�V���V�����ꍇ��������
                }
                $modified = gmdate("D, d M Y H:i:s", filemtime($this->subject_file))." GMT";
            } else {
                $modified = false;
            }
        }
        
        if (extension_loaded('zlib') and strstr($this->subject_url, ".2ch.net")) {
            $headers = "Accept-Encoding: gzip\r\n";
        }

        // ��DL
        include_once (P2_LIBRARY_DIR . '/wap.class.php');
        $wap_ua =& new UserAgent();
        $wap_ua->setAgent('Monazilla/1.00 (' . $_conf['p2name'] . '/' . $_conf['p2version'] . ')');
        $wap_ua->setTimeout($_conf['fsockopen_time_limit']);
        $wap_req =& new Request();
        $wap_req->setUrl($this->subject_url);
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
            $body = '';
        } else {
            $body = $wap_res->content;
        }
        
        // �� DL�������� ���� �X�V����Ă�����
        if ($wap_res->is_success() && $wap_res->code != "304") {
            
            // gzip���𓀂���
            if ($wap_res->headers['Content-Encoding'] == 'gzip') {
                $body = substr($body, 10);
                $body = gzinflate($body);
            }
        
            // ������� or be.2ch.net �Ȃ�EUC��SJIS�ɕϊ�
            if (P2Util::isHostJbbsShitaraba($this->host) || P2Util::isHostBe2chNet($this->host)) {
                $body = mb_convert_encoding($body, 'SJIS-win', 'eucJP-win');
            }
            
            // eashm�ɕۑ�����ꍇ
            if ($this->storage == 'eashm') {
                $eacc_key = "$this->host/$this->bbs";
                eaccelerator_lock($eacc_key); 
                //echo $body;
                eaccelerator_put($eacc_key, $body, $_conf['sb_dl_interval']);
                eaccelerator_unlock($eacc_key); 
            
            // �t�@�C���ɕۑ�����ꍇ
            } else {
                if (FileCtl::file_write_contents($this->subject_file, $body) === false) {
                    die("Error: cannot write file");
                }
                chmod($this->subject_file, $perm);
            }
        } else {
            // touch���邱�ƂōX�V�C���^�[�o���������̂ŁA���΂炭�ă`�F�b�N����Ȃ��Ȃ�
            // �i�ύX���Ȃ��̂ɏC�����Ԃ��X�V����̂́A�����C���i�܂Ȃ����A�����ł͓��ɖ��Ȃ����낤�j
            if ($this->storage == 'file') {
                touch($this->subject_file);
            }
        }
        
        return $body;
    }
    
    
    /**
     * subject.txt ���V�N�Ȃ� true ��Ԃ�
     */
    function isSubjectTxtFresh()
    {
        global $_conf;

        // �L���b�V��������ꍇ
        if (file_exists($this->subject_file)) {
            // �L���b�V���̍X�V���w�莞�Ԉȓ��Ȃ�
            // clearstatcache();
            if (@filemtime($this->subject_file) > time() - $_conf['sb_dl_interval']) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * subject.txt ��ǂݍ���
     */
    function setSubjectLines($cont = '')
    {
        if ($this->storage == 'eashm') {
            if (!$cont) {
                $cont = eaccelerator_get("$this->host/$this->bbs");
            }
            $this->subject_lines = explode("\n", $cont);
        
        } elseif ($this->storage == 'file') {
            if (extension_loaded('zlib') and strstr($this->host, '.2ch.net')) {
                $this->subject_lines = @gzfile($this->subject_file);    // ����͂��̂����O�� 2005/6/5
            } else {
                $this->subject_lines = @file($this->subject_file);
            }
        }
        
        // JBBS@������΂Ȃ�d���X���^�C���폜����
        if (P2Util::isHostJbbsShitaraba($this->host)) {
            $this->subject_lines = array_unique($this->subject_lines);
        }
        
        /*
        // be.2ch.net �Ȃ�EUC��SJIS�ϊ�
        if (P2Util::isHostBe2chNet($this->host)) {
            $this->subject_lines = array_map(create_function('$str', 'return mb_convert_encoding($str, "SJIS-win", "eucJP-win");'), $this->subject_lines);
        }
        */
        
        if ($this->subject_lines) {
            return true;
        } else {
            return false;
        }
    }

}

?>
