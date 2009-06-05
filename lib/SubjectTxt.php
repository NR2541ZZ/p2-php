<?php
/*
$GLOBALS['_SubjectTxt_STORAGE'] = 'apc';      // �vAPC
$GLOBALS['_SubjectTxt_STORAGE'] = 'eaccelerator';    // �veAccelerator

[�d�l] eaccelerator, apc ���ƒ����L���b�V�����Ȃ�
[�d�l] eaccelerator, apc ����modified�����Ȃ�

eaccelerator, apc �ɂ��Ă��p�t�H�[�}���X�͂������ĕς��Ȃ��悤��
*/
class SubjectTxt
{
    var $host;
    var $bbs;
    var $subject_url;
    var $subject_file;
    var $subject_lines;
    
    // 2006/02/27 aki eaccelerator, apc �͔񐄏�
    var $storage; // file, eaccelerator(eAccelerator shm), apc
    
    /**
     * @constructor
     */
    function SubjectTxt($host, $bbs)
    {
        $this->host = $host;
        $this->bbs =  $bbs;
        
        if (isset($GLOBALS['_SubjectTxt_STORAGE'])) {
            if (in_array($GLOBALS['_SubjectTxt_STORAGE'], array('eaccelerator', 'apc'))) {
                $this->storage = $GLOBALS['_SubjectTxt_STORAGE'];
            }
        }
        if (!isset($this->storage)) {
            $this->storage = 'file';
        }
        
        $this->setSubjectFile($this->host, $this->bbs);
        $this->setSubjectUrl($this->host, $this->bbs);
        
        // subject.txt���_�E�����[�h���Z�b�g����
        $this->dlAndSetSubject();
    }
    
    /**
     * @access  private
     * @return  void
     */
    function setSubjectFile($host, $bbs)
    {
        $this->subject_file = P2Util::datDirOfHost($host) . '/' . rawurlencode($bbs) . '/subject.txt';
    }
    
    /**
     * @access  private
     * @return  void
     */
    function setSubjectUrl($host, $bbs)
    {
        //$subject_url = 'http://' . $host . '/' . $bbs . '/subject.txt';
        $subject_url = sprintf(
            'http://%s/%s%s/subject.txt',
            $host,
            P2Util::isHostCha2($host) ? 'cgi-bin/' : '',
            $bbs
        );
        
        // ������΂�livedoor�ړ]�ɑΉ��B�Ǎ����livedoor�Ƃ���B
        $subject_url = P2Util::adjustHostJbbsShitaraba($subject_url);
        
        $this->subject_url = $subject_url;
    }
    
    /**
     * subject.txt���_�E�����[�h���Z�b�g����
     *
     * @access  private
     * @return  boolean  �Z�b�g�ł���� true
     */
    function dlAndSetSubject()
    {
        $lines = array();
        if ($this->storage == 'eaccelerator') {
            $lines = eaccelerator_get("$this->host/$this->bbs");
        } elseif ($this->storage == 'apc') {
            $lines = apc_fetch("$this->host/$this->bbs");
        }
        
        if (!$lines || !empty($_POST['newthread'])) {
            $lines = $this->downloadSubject();
        }
        
        return $this->loadSubjectLines($lines) ? true : false;
    }

    /**
     * subject.txt���_�E�����[�h����
     *
     * @access  public
     * @return  array|null|false  subject.txt�̔z��f�[�^(eaccelerator, apc�p)�A�܂���null��Ԃ��B
     *                            ���s�����ꍇ��false��Ԃ��B
     */
    function downloadSubject()
    {
        global $_conf;

        static $spendDlTime_ = 0; // DL���v���v����
        
        $perm = isset($_conf['dl_perm']) ? $_conf['dl_perm'] : 0606;

        $modified = false;
        
        if ($this->storage == 'file') {
            FileCtl::mkdirFor($this->subject_file); // �f�B���N�g����������΍��

            if (file_exists($this->subject_file)) {
            
                // �t�@�C���L���b�V��������΁ADL�������Ԃ�������
                if ($_conf['dlSubjectTotalLimitTime'] and $spendDlTime_ > $_conf['dlSubjectTotalLimitTime']) {
                    return null;
                }
                
                // �����ɂ���āA�L���b�V����K�p����
                // subject.php ��refresh�w�肪���鎞�́A�L���b�V����K�p���Ȃ�
                if (!(basename($_SERVER['SCRIPT_NAME']) == $_conf['subject_php'] && !empty($_REQUEST['refresh']))) {
                    
                    // �L���b�V���K�p�w�莞�́A���̏�Ŕ�����
                    if (!empty($_GET['norefresh']) || isset($_REQUEST['word'])) {
                        return null;
                        
                    // �V�K�X�����Ď��ȊO�ŁA�L���b�V�����V�N�ȏꍇ��������
                    } elseif (empty($_POST['newthread']) and $this->isSubjectTxtFresh()) {
                        return null;
                    }
                }
                
                $modified = gmdate("D, d M Y H:i:s", filemtime($this->subject_file)) . " GMT";
            
            }
        }

        $dlStartTime = $this->microtimeFloat();
        
        // DL
        require_once 'HTTP/Request.php';
        
        $params = array();
        $params['timeout'] = $_conf['fsockopen_time_limit'];
        if ($_conf['proxy_use']) {
            $params['proxy_host'] = $_conf['proxy_host'];
            $params['proxy_port'] = $_conf['proxy_port'];
        }
        $req = new HTTP_Request($this->subject_url, $params);
        $modified && $req->addHeader('If-Modified-Since', $modified);
        $req->addHeader('User-Agent', sprintf('Monazilla/1.00 (%s/%s)', $_conf['p2uaname'], $_conf['p2version']));
        
        $response = $req->sendRequest();
        
        $error_msg = null;
        if (PEAR::isError($response)) {
            $error_msg = $response->getMessage();
        } else {
            $code = $req->getResponseCode();
            if ($code == 302) {
                // �z�X�g�̈ړ]��ǐ�
                require_once P2_LIB_DIR . '/BbsMap.php';
                $new_host = BbsMap::getCurrentHost($this->host, $this->bbs);
                if ($new_host != $this->host) {
                    $aNewSubjectTxt = new SubjectTxt($new_host, $this->bbs);
                    return $aNewSubjectTxt->downloadSubject();
                }
            }
            if (!($code == 200 || $code == 206 || $code == 304)) {
                //var_dump($req->getResponseHeader());
                $error_msg = $code;
            }
        }
    
        if (!is_null($error_msg) && strlen($error_msg) > 0) {
            $attrs = array();
            if ($_conf['ext_win_target']) {
                $attrs['target'] = $_conf['ext_win_target'];
            }
            $atag = P2View::tagA(
                P2Util::throughIme($this->subject_url),
                hs($this->subject_url),
                $attrs
            );
            $msg_ht = sprintf(
                '<div>Error: %s<br>p2 info - %s �ɐڑ��ł��܂���ł����B</div>',
                hs($error_msg),
                $atag
            );
            P2Util::pushInfoHtml($msg_ht);
            $body = '';
        } else {
            $body = $req->getResponseBody();
        }

        $dlEndTime = $this->microtimeFloat();
        $dlTime = $dlEndTime - $dlStartTime;
        $spendDlTime_ += $dlTime;

        // DL�������� ���� �X�V����Ă�����
        if ($body && $code != '304') {

            // ������� or be.2ch.net �Ȃ�EUC��SJIS�ɕϊ�
            if (P2Util::isHostJbbsShitaraba($this->host) || P2Util::isHostBe2chNet($this->host)) {
                $body = mb_convert_encoding($body, 'SJIS-win', 'eucJP-win');
            }
            
            // eaccelerator or apc�ɕۑ�����ꍇ
            if ($this->storage == 'eaccelerator' || $this->storage == 'apc') {
                $cache_key = "$this->host/$this->bbs";
                $cont = rtrim($body);
                $lines = explode("\n", $cont);
                if ($this->storage == 'eaccelerator') {
                    eaccelerator_lock($cache_key); 
                    eaccelerator_put($cache_key, $lines, $_conf['sb_dl_interval']);
                    eaccelerator_unlock($cache_key);
                } else {
                    apc_store($cache_key, $lines, $_conf['sb_dl_interval']);
                }
                return $lines;
            
            
            // �t�@�C���ɕۑ�����ꍇ
            } else {
                if (false === FileCtl::filePutRename($this->subject_file, $body)) {
                    // �ۑ��Ɏ��s�͂��Ă��A�����̃L���b�V�����ǂݍ��߂�Ȃ�悵�Ƃ��Ă���
                    if (is_readable($this->subject_file)) {
                        return null;
                    } else {
                        die("Error: cannot write file");
                        return false;
                    }
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
        
        return null;
    }
    
    
    /**
     * subject.txt ���V�N�Ȃ� true ��Ԃ�
     *
     * @access  private
     * @return  boolean  �V�N�Ȃ� true�B�����łȂ���� false�B
     */
    function isSubjectTxtFresh()
    {
        global $_conf;

        if (file_exists($this->subject_file)) {
            // �L���b�V���̍X�V���w�莞�Ԉȓ��Ȃ�
            // clearstatcache();
            if (filemtime($this->subject_file) > time() - $_conf['sb_dl_interval']) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * subject.txt ��ǂݍ��݁A�Z�b�g���A��������
     * ��������΁A$this->subject_lines ���Z�b�g�����
     *
     * @access  private
     * @param   string   $lines    eaccelerator, apc�p
     * @return  boolean  ���s����
     */
    function loadSubjectLines($lines = null)
    {
        if (!$lines) {
            if ($this->storage == 'eaccelerator') {
                $this->subject_lines = eaccelerator_get("$this->host/$this->bbs");
            } elseif ($this->storage == 'apc') {
                $this->subject_lines = apc_fetch("$this->host/$this->bbs");
            } elseif ($this->storage == 'file') {
                $this->subject_lines = file($this->subject_file);
            } else {
                return false;
            }
        } else {
            $this->subject_lines = $lines;
        }
        
        // JBBS@������΂Ȃ�d���X���^�C���폜����
        if (P2Util::isHostJbbsShitaraba($this->host)) {
            $this->subject_lines = array_unique($this->subject_lines);
        }
        
        return $this->subject_lines ? true : false;
    }

    /**
     * PHP 5��microtime�����͋[����ȒP�ȃ��\�b�h
     *
     * @access  private
     * @return  float
     */
    function microtimeFloat()
    {
       list($usec, $sec) = explode(' ', microtime());
       return ((float)$usec + (float)$sec);
    }
}
