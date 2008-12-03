<?php
require_once P2_LIB_DIR . '/FileCtl.php';

/**
 * p2 - ThreadRead �N���X
 */
class ThreadRead extends Thread
{
    var $datlines;  // dat����ǂݍ��񂾃��C�����i�[����z��
    
    var $resrange;  // array('start' => i, 'to' => i, 'nofirst' => bool)
    var $resrange_multi = array();
    var $resrange_readnum;
    var $resrange_multi_exists_next;
    
    var $onbytes;   // �T�[�o����擾����dat�T�C�Y
    var $diedat;    // �T�[�o����dat�擾���悤�Ƃ��Ăł��Ȃ���������true���Z�b�g�����
    var $onthefly;  // ���[�J����dat�ۑ����Ȃ��I���U�t���C�ǂݍ��݂Ȃ�true

    var $idcount;   // �z��Bkey �� ID�L��, value �� ID�o����
    var $rrescount; // arary key �� �Q�Ɛ惌�X�ԍ�, value �͎Q�ƌ��̃��X
    
    var $getdat_error_msg_ht = ''; // dat�擾�Ɏ��s�������ɕ\������郁�b�Z�[�W�iHTML�j
    
    var $old_host;  // �z�X�g�ړ]���o���A�ړ]�O�̃z�X�g��ێ�����

    /**
     * @constructor
     */
    function ThreadRead()
    {
    }

    /**
     * DAT���_�E�����[�h�ۑ�����
     *
     * @access  public
     * @return  boolean
     */
    function downloadDat()
    {
        global $_conf;
        
        // �܂�BBS
        if (P2Util::isHostMachiBbs($this->host)) {
            require_once P2_LIB_DIR . '/read_machibbs.inc.php';
            machiDownload();
            
        // JBBS@�������
        } elseif (P2Util::isHostJbbsShitaraba($this->host)) {
            require_once P2_LIB_DIR . '/read_shitaraba.inc.php';
            shitarabaDownload();
        
        // 2ch�n
        } else {
            $this->getDatBytesFromLocalDat(); // $aThread->length ��set

            // 2ch bbspink���ǂ�
            if (P2Util::isHost2chs($this->host) && !empty($_GET['maru'])) {
                
                // ���O�C�����ĂȂ���� or ���O�C����A24���Ԉȏ�o�߂��Ă����玩���ă��O�C��
                if ((!file_exists($_conf['sid2ch_php']) or !empty($_REQUEST['relogin2ch'])) or (filemtime($_conf['sid2ch_php']) < time() - 60*60*24)) {
                    require_once P2_LIB_DIR . '/login2ch.inc.php';
                    if (!login2ch()) {
                        $this->getdat_error_msg_ht .= $this->get2chDatError();
                        $this->diedat = true;
                        return false;
                    }
                }

                $this->downloadDat2chMaru();

            // 2ch�̉ߋ����O�q�ɓǂ�
            } elseif (!empty($_GET['kakolog']) && !empty($_GET['kakoget'])) {
                if ($_GET['kakoget'] == 1) {
                    $ext = '.dat.gz';
                } elseif ($_GET['kakoget'] == 2) {
                    $ext = '.dat';
                }
                $this->downloadDat2chKako($_GET['kakolog'], $ext);
                
            // 2ch or 2ch�݊�
            } else {
                // DAT������DL����
                $this->downloadDat2ch($this->length);
            }

        }
        
        return true;
    }
    
    /**
     * HTTP�w�b�_���X�|���X��ǂݍ���
     *
     * @access  private
     * @param   resource  $fp  fsockopen �ŊJ�����t�@�C���|�C���^
     * @return  array|false
     */
    function freadHttpHeader($fp)
    {
        $h = array();
        
        while (!feof($fp)) {
            $l = fgets($fp, 8192);
            
            // ex) HTTP/1.1 304 Not Modified
            if (preg_match("|HTTP/1\.\d (\d+) (.+)\r\n|", $l, $matches)) {
                $h['code']      = $matches[1];
                $h['message']   = $matches[2];
                $h['HTTP']      = rtrim($l);
            }
            
            if (preg_match('/^(.+?): (.+)\r\n/', $l, $matches)) {
                $h['headers'][$matches[1]] = $matches[2];
                
            } elseif ($l == "\r\n") {
                if (!isset($h['code'])) {
                    return false;
                }
                return $h;
            }
        }
        
        return false;
    }
    
    /**
     * HTTP�w�b�_���X�|���X�̎擾�G���[�� P2Util::pushInfoHtml() ����
     *
     * @access  private
     * @return  void
     */
    function _pushInfoHtmlFreadHttpHeaderError($url)
    {
        global $_conf;

        P2Util::pushInfoHtml(
            sprintf(
                '<p>p2 info: %s ����w�b�_���X�|���X���擾�ł��܂���ł����B</p>',
                P2View::tagA(P2Util::throughIme($url), hs($url), array('target' => $_conf['ext_win_target']))
            )
        );
    }
    
    /**
     * HTTP�w�b�_���X�|���X����t�@�C���T�C�Y���擾����
     *
     * @access  private
     * @param   array    $headers
     * @param   boolean  $zero_read
     * @return  integer|false
     */
    function getOnbytesFromHeader($headers, $zero_read = true)
    {
        if ($zero_read) {
            if (isset($headers['Content-Length'])) {
                if (preg_match("/^([0-9]+)/", $headers['Content-Length'], $matches)) {
                    return $onbytes = $matches[1];
                }
            }
            
        } else {
            if (isset($headers['Content-Range'])) {
                if (preg_match("/^bytes ([^\/]+)\/([0-9]+)/", $headers['Content-Range'], $matches)) {
                    return $onbytes = $matches[2];
                }
            }
        }
        
        return false;
    }
    
    /**
     * �W�����@�� 2ch�݊� DAT �������_�E�����[�h����
     *
     * @access  private
     * @return  true|string|false  �擾�ł������A�X�V���Ȃ������ꍇ��true�i�܂���"304 Not Modified"�j��Ԃ�
     */
    function downloadDat2ch($from_bytes)
    {
        global $_conf;
        global $debug;
    
        if (!($this->host && $this->bbs && $this->key)) {
            return false;
        }

        $from_bytes = intval($from_bytes);
        
        if ($from_bytes == 0) {
            $zero_read = true;
        } else {
            $zero_read = false;
            $from_bytes = $from_bytes - 1;
        }
        
        $method = "GET";
        $uaMona = "Monazilla/1.00";
        
        $p2ua = $uaMona . ' (' . $_conf['p2uaname'] . '/' . $_conf['p2version'] . ')';
        
        $url = 'http://' . $this->host . "/{$this->bbs}/dat/{$this->key}.dat";
        //$url="http://news2.2ch.net/test/read.cgi?bbs=newsplus&key=1038486598";

        $purl = parse_url($url);
        
        if (isset($purl['query'])) {
            $purl['query'] = "?" . $purl['query'];
        } else {
            $purl['query'] = "";
        }

        // �v���L�V
        if ($_conf['proxy_use']) {
            $send_host = $_conf['proxy_host'];
            $send_port = $_conf['proxy_port'];
            $send_path = $url;
        } else {
            $send_host = $purl['host'];
            $send_port = isset($purl['port']) ? $purl['port'] : null;
            $send_path = $purl['path'] . $purl['query'];
        }
        
        !$send_port and $send_port = 80;
        
        $request = $method . " " . $send_path . " HTTP/1.0\r\n";
        $request .= "Host: " . $purl['host'] . "\r\n";
        $request .= "Accept: */*\r\n";
        //$request .= "Accept-Charset: Shift_JIS\r\n";
        //$request .= "Accept-Encoding: gzip, deflate\r\n";
        $request .= "Accept-Language: ja, en\r\n";
        $request .= "User-Agent: " . $p2ua . "\r\n";
        if (!$zero_read) {
            $request .= "Range: bytes={$from_bytes}-\r\n";
        }
        $request .= "Referer: http://{$purl['host']}/{$this->bbs}/\r\n";
        if ($this->modified) {
            $request .= "If-Modified-Since: " . $this->modified . "\r\n";
        }
        
        // Basic�F�ؗp�̃w�b�_
        if (isset($purl['user']) && isset($purl['pass'])) {
            $request .= "Authorization: Basic " . base64_encode($purl['user'] . ":" . $purl['pass']) . "\r\n";
        }

        $request .= "Connection: Close\r\n";
        $request .= "\r\n";
        
        // WEB�T�[�o�֐ڑ�
        $fp = @fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
        if (!$fp) {
            P2Util::pushInfoHtml(
                sprintf(
                    '<p>�T�[�o�ڑ��G���[: %s (%s)<br>p2 info - %s �ɐڑ��ł��܂���ł����B</div>',
                    hs($errstr), hs($errno),
                    P2View::tagA(P2Util::throughIme($url), hs($url), array('target' => $_conf['ext_win_target']))
                )
            );
            
            $this->diedat = true;
            return false;
        }
        
        // HTTP���N�G�X�g���M
        fputs($fp, $request);
        
        // HTTP�w�b�_���X�|���X���擾����
        $h = $this->freadHttpHeader($fp);
        if ($h === false) {
            fclose($fp);
            $this->_pushInfoHtmlFreadHttpHeaderError($url);
            $this->diedat = true;
            return false;
        }
        
        // {{{ HTTP�R�[�h���`�F�b�N
        
        $code = $h['code'];
        
        // 206 Partial Content
        if ($code == "200" || $code == "206") {
            // OK�B�������Ȃ�
        
        // Found
        } elseif ($code == "302") {
        
            // �z�X�g�̈ړ]��ǐ�
            require_once P2_LIB_DIR . '/BbsMap.php';
            $new_host = BbsMap::getCurrentHost($this->host, $this->bbs);
            if ($new_host != $this->host) {
                fclose($fp);
                $this->old_host = $this->host;
                $this->host = $new_host;
                return $this->downloadDat2ch($from_bytes);
                
            } else {
                fclose($fp);
                
                // 2007/06/11 302�̎��ɁAUA��Monazilla�ɂ��Ȃ���DAT�A�N�Z�X�����݂��203���A���Ă��āA
                // body����'�ߋ����O ��'�Ƃ���΁A���������Ƃ݂Ȃ����Ƃɂ���B
                // �d�l�̊m�؂����Ă��Ȃ��̂ŁA���̂悤�Ȕ��f�ł悢�̂��͂����肵�Ȃ��B
                // 203 Non-Authoritative Information
                // �ߋ����O ��
                /*
��������W���B�B�B<><>2007/06/10(��) 13:29:51.68 0<> http://mlb.yahoo.co.jp/headlines/?a=2279 <br> ����큄������������������������������������������� <>������탁�W���[���i���� ����c�_14001��
1001, 131428 (�����X��, �T�C�Y)<><>1181480550000000 (�ŏI�X�V)<><div style="color:navy;font-size:smaller;">|<br />| ����<br />|</div><>
�P�O�O�P<><>Over 1000 Thread<> ���̃X���b�h�͂P�O�O�O�𒴂��܂����B <br> ���������Ȃ��̂ŁA�V�����X���b�h�𗧂ĂĂ��������ł��B�B�B  <>
�ߋ����O ��<><>[�ߋ����O]<><div style="color:red;text-align:center;">�� ���̃X���b�h�͉ߋ����O�q�ɂɊi�[����Ă��܂�</div><hr /><br />IE�����ʂ̃u���E�U�Ō���ꍇ http://tubo.80.kg/tubo_and_maru.html<br />��p�̃u���E�U�Ō���ꍇ http://www.monazilla.org/<br /><br />�Q�����˂� Viewer ���g���ƁA�����ɓǂ߂܂��B http://2ch.tora3.net/<br /><div style="color:navy;">���� Viewer(�ʏ́�) �̔���ŁA�Q�����˂�͐ݔ��𑝋����Ă��܂��B<br />�������ꂽ��A�V�����T�[�o�𓊓��ł���Ƃ������ł��B</div><br />�悭�킩��Ȃ��ꍇ�̓\�t�g�E�F�A��Go http://pc11.2ch.net/software/<br /><br />�����^�| ( http://find.2ch.net/faq/faq2.php#c1 ) �������Ă���΁A50�����^�|�ŕ\���ł��܂��B<br />�@�@�@�@�����炩�� �� http://find.2ch.net/index.php?STR=dat:http://ex23.2ch.net/test/read.cgi/morningcoffee/1181449791/<br /><br /><hr /><>
                */
                $params = array();
                $params['timeout'] = $_conf['fsockopen_time_limit'];
                if ($_conf['proxy_use']) {
                    $params['proxy_host'] = $_conf['proxy_host'];
                    $params['proxy_port'] = $_conf['proxy_port'];
                }
                $req = new HTTP_Request($url, $params);
                $req->setMethod('GET');
                $err = $req->sendRequest(true);
                
                if (PEAR::isError($err)) {
                    //var_dump('error');
                    
                } else {
                    // ���X�|���X�R�[�h������
                    if ('203' == $req->getResponseCode()) {
                        $body2 = $req->getResponseBody();
                        $reason = null;
                        if (preg_match('/�ߋ����O ��/', $body2)) {
                            $reason = 'datochi';
                        }
                        $this->downloadDat2chNotFound($reason);
                        return false;
                    }
                }
                
                $this->downloadDat2chNotFound();
                return false;
            }
        
        // Not Modified
        } elseif ($code == "304") {
            fclose($fp);
            $this->isonline = true;
            return "304 Not Modified";
        
        // Requested Range Not Satisfiable
        } elseif ($code == "416") {
            //echo "���ځ[�񌟏o";
            fclose($fp);
            unset($this->onbytes);
            unset($this->modified);
            return $this->downloadDat2ch(0); // ���ځ[������o�����̂őS����蒼���B
            
        // �\�����Ȃ�HTTP�R�[�h�B�X���b�h���Ȃ��Ɣ��f
        } else {
            fclose($fp);
            $this->downloadDat2chNotFound();
            return false;
        }
        
        // }}}
        
        $r = $this->getOnbytesFromHeader($h['headers'], $zero_read);
        if ($r !== false) {
            $this->onbytes = $r;
        }
        
        if (isset($h['headers']['Last-Modified'])) {
            $this->modified = $h['headers']['Last-Modified'];
        }
        
        // body��ǂ�
        $body = '';
        while (!feof($fp)) {
            $body .= fread($fp, 8192);
        }
        fclose($fp);
        
        // �����̉��s�ł��ځ[����`�F�b�N����
        if (!$zero_read) {
            if (substr($body, 0, 1) != "\n") {
                //echo "���ځ[�񌟏o";
                unset($this->onbytes);
                unset($this->modified);
                return $this->downloadDat2ch(0); // ���ځ[������o�����̂őS����蒼���B
            }
            $body = substr($body, 1);
        }
        
        FileCtl::make_datafile($this->keydat, $_conf['dat_perm']);

        $rsc = $zero_read ? LOCK_EX : FILE_APPEND | LOCK_EX;
        
        if (false === file_put_contents($this->keydat, $body, $rsc)) {
            trigger_error("file_put_contents(" . $this->keydat . ")", E_USER_WARNING);
            die('Error: cannot write file. downloadDat2ch()');
            return false;
        }
        
        // {{{ �擾��T�C�Y�`�F�b�N
        
        $debug && $GLOBALS['profiler']->enterSection('dat_size_check');
        if ($zero_read == false && $this->onbytes) {
            $this->getDatBytesFromLocalDat(); // $aThread->length ��set
            if ($this->onbytes != $this->length) {
                $onbytes = $this->onbytes;
                unset($this->onbytes);
                unset($this->modified);
                P2Util::pushInfoHtml("p2 info: $onbytes/$this->length �t�@�C���T�C�Y���ςȂ̂ŁAdat���Ď擾���܂���<br>");
                $debug && $GLOBALS['profiler']->leaveSection('dat_size_check');
                return $this->downloadDat2ch(0); // dat�T�C�Y�͕s���B�S����蒼���B
            }
        }
        $debug && $GLOBALS['profiler']->leaveSection('dat_size_check');
        
        // }}}
        
        $this->isonline = true;
        return true;
        
        /*
        ���ځ[�񌟏o�R��ɂ���
        
        0. p2���ǂݍ��� 
        1. ���X�����ځ[�񂳂�� 
        2. (���ځ[�񂳂ꂽ���X-���ځ[��e�L�X�g)�ƑS�����T�C�Y�̃��X���������܂�� 
        3. p2���ǂݍ��� 

        0-1-2-3���A���S�ɘA���������ɂ��ځ[�񌟏o�R��͂��肤��B 
        */
    }
    
    /**
     * 2ch DAT���_�E�����[�h�ł��Ȃ������Ƃ��ɌĂяo�����
     *
     * @access  private
     * @param   string|null  $reason
     * @return  void
     */
    function downloadDat2chNotFound($reason = null)
    {
        // 2ch, bbspink �Ȃ�read.cgi�Ŋm�F
        if (P2Util::isHost2chs($this->host)) {
            $this->getdat_error_msg_ht .= $this->get2chDatError($reason);
        }
        $this->diedat = true;
    }
    
    /**
     * 2ch���p DAT���_�E�����[�h����
     *
     * @access  private
     * @return  true|string|false  �擾�ł������A�X�V���Ȃ������ꍇ��true�i�܂���"304 Not Modified"�j��Ԃ�
     */
    function downloadDat2chMaru()
    {
        global $_conf;
        
        if (!($this->host && $this->bbs && $this->key && $this->keydat)) {
            return false;
        }
        
        include $_conf['sid2ch_php']; // $uaMona, $SID2ch ���Z�b�g����� @see login2ch.inc.php
        if (!$uaMona || !$SID2ch) {
            return false;
        }
        
        $method = 'GET';
        $p2ua = $uaMona . ' (' . $_conf['p2uaname'] . '/' . $_conf['p2version'] . ')';
        
        //  GET /test/offlaw.cgi?bbs=��&key=�X���b�h�ԍ�&sid=�Z�b�V����ID HTTP/1.1
        $SID2ch = urlencode($SID2ch);
        $url = 'http://' . $this->host . "/test/offlaw.cgi/{$this->bbs}/{$this->key}/?raw=0.0&sid={$SID2ch}";

        $purl = parse_url($url);
        
        if (isset($purl['query'])) {
            $purl['query'] = '?' . $purl['query'];
        } else {
            $purl['query'] = '';
        }
        
        // �v���L�V
        if ($_conf['proxy_use']) {
            $send_host = $_conf['proxy_host'];
            $send_port = $_conf['proxy_port'];
            $send_path = $url;
        } else {
            $send_host = $purl['host'];
            $send_port = isset($purl['port']) ? $purl['port'] : null;
            $send_path = $purl['path'] . $purl['query'];
        }
        
        !$send_port and $send_port = 80; // �f�t�H���g��80

        $request = $method . " " . $send_path . " HTTP/1.0" . "\r\n";
        $request .= "Host: " . $purl['host'] . "\r\n";
        $request .= "Accept-Encoding: gzip, deflate" . "\r\n";
        //$request .= "Accept-Language: ja, en" . "\r\n";
        $request .= "User-Agent: " . $p2ua . "\r\n";
        //$request .= "X-2ch-UA: " . $_conf['p2uaname'] . "/" . $_conf['p2version'] . "\r\n";
        //$request .= "Range: bytes={$from_bytes}-" . "\r\n";
        $request .= "Connection: Close" . "\r\n";
        /*
        if ($modified) {
            $request .= "If-Modified-Since: $modified" . "\r\n";
        }
        */
        $request .= "\r\n";
        
        // WEB�T�[�o�֐ڑ�
        $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
        if (!$fp) {
            P2Util::pushInfoHtml(
                sprintf(
                    '<p>�T�[�o�ڑ��G���[: %s (%s)<br>p2 info - %s �ɐڑ��ł��܂���ł����B</div>',
                    hs($errstr), hs($errno),
                    P2View::tagA(P2Util::throughIme($url), hs($url), array('target' => $_conf['ext_win_target']))
                )
            );
            
            $this->diedat = true;
            return false;
        }
        
        // HTTP���N�G�X�g���M
        fputs($fp, $request);

        // HTTP�w�b�_���X�|���X���擾����
        $h = $this->freadHttpHeader($fp);
        if ($h === false) {
            fclose($fp);
            $this->_pushInfoHtmlFreadHttpHeaderError($url);
            $this->diedat = true;
            return false;
        }
        
        // {{{ HTTP�R�[�h���`�F�b�N
        
        $code = $h['code'];
        
        // Partial Content
        if ($code == "200") {
            // OK�B�������Ȃ�
        
        // Found
        } elseif ($code == "304") {
            fclose($fp);
            //$this->isonline = true;
            return "304 Not Modified";
            
        // �\�����Ȃ�HTTP�R�[�h�B�Ȃ������Ɣ��f����
        } else {
            fclose($fp);
            return $this->downloadDat2chMaruNotFound();
        }
        
        // }}}
        
        $isGzip = false;
        if (isset($h['headers']['Content-Encoding'])) {
            if (preg_match("/^(x-)?gzip/", $h['headers']['Content-Encoding'], $matches)) {
                $isGzip = true;
            }
        }
        if (isset($h['headers']['Last-Modified'])) {
            $lastmodified = $h['headers']['Last-Modified'];
        }
        if (isset($h['headers']['Content-Length'])) {
            if (preg_match("/^([0-9]+)/", $h['headers']['Content-Length'], $matches)) {
                $onbytes = $h['headers']['Content-Length'];
            }
        }
        // Transfer-Encoding: chunked
        $chunked = false;
        if (isset($h['headers']['Transfer-Encoding'])) {
            if ($h['headers']['Transfer-Encoding'] == "chunked") {
                $chunked = true;
            }
        }
        
        // body��ǂ�
        $body = '';
        while (!feof($fp)) {
            $body .= fread($fp, 8192);
        }
        fclose($fp);
        
        $done_gunzip = false;
        
        // gzip���k�Ȃ�
        if ($isGzip) {
            // gzip temp�t�@�C���ɕۑ�
            $gztempfile = $this->keydat . ".gz";
            FileCtl::mkdirFor($gztempfile);
            if (file_put_contents($gztempfile, $body, LOCK_EX) === false) {
                die("Error: cannot write file. downloadDat2chMaru()");
                return false;
            }
            
            // PHP�ŉ𓀓ǂݍ���
            if (extension_loaded('zlib')) {
                $body = FileCtl::getGzFileContents($gztempfile);
            // �R�}���h���C���ŉ�
            } else {
                // ���ɑ��݂���Ȃ�ꎞdat���o�b�N�A�b�v�ޔ�
                if (file_exists($this->keydat)) {
                    if (file_exists($this->keydat . ".bak")) {
                        unlink($this->keydat . ".bak");
                    }
                    rename($this->keydat, $this->keydat . ".bak");
                }
                $rcode = 1;
                // �𓀂���
                system("gzip -d $gztempfile", $rcode);
                // �𓀎��s�Ȃ�o�b�N�A�b�v��߂�
                if ($rcode != 0) {
                    if (file_exists($this->keydat . ".bak")) {
                        file_exists($this->keydat) and unlink($this->keydat);
                        rename($this->keydat . ".bak", $this->keydat);
                    }
                    $this->getdat_error_msg_ht .= "<p>p2 info - 2�����˂�ߋ����O�q�ɂ���̃X���b�h��荞�݂́APHP��<a href=\"http://www.php.net/manual/ja/ref.zlib.php\">zlib�g�����W���[��</a>���Ȃ����Asystem��gzip�R�}���h���g�p�\�łȂ���΂ł��܂���B</p>";
                    // gztemp�t�@�C�����̂Ă�
                    file_exists($gztempfile) and unlink($gztempfile);
                    
                    $this->diedat = true;
                    return false;
                    
                // �𓀐����Ȃ�
                } else {
                    file_exists($this->keydat . ".bak") and unlink($this->keydat . ".bak");
                    
                    $done_gunzip = true;
                }

            }
            // gzip temp�t�@�C�����̂Ă�
            file_exists($gztempfile) and unlink($gztempfile);
        }
        
        /*
        // -ERR �����@�����܂��� 
        if (preg_match('/^-ERR/', $body)) {
            return $this->downloadDat2chMaruNotFound();
        }
        */

        // -ERR �ߋ����O�q�ɂŔ��� ../operate/kako/1107/11073/1107376477.dat
        if (preg_match('{^-ERR �ߋ����O�q�ɂŔ��� \\.\\.([/a-z0-9]+)\\.dat}', $body, $m)) {
            $kakolog = 'http://' . $this->host . $m[1];
            return $this->downloadDat2chKako($kakolog, '.dat');
        }

        if (!$done_gunzip) {
            FileCtl::make_datafile($this->keydat, $_conf['dat_perm']);
            if (file_put_contents($this->keydat, $body, LOCK_EX) === false) {
                die("Error: cannot write file. downloadDat2chMaru()");
                return false;
            }
        }
        
        // �N���[�j���O
        $marudatlines = @file($this->keydat);
        if ($marudatlines) {
            $firstline = array_shift($marudatlines);
            // �`�����N�Ƃ�
            if (!strstr($firstline, "+OK")) {
                $secondline = array_shift($marudatlines);
            }
            $cont = '';
            foreach ($marudatlines as $aline) {
                // �`�����N�G���R�[�f�B���O���~�����Ƃ���(HTTP 1.0�ł��̂�)
                // http://jp.php.net/manual/ja/function.fsockopen.php#36703
                if ($chunked) {
                    $cont .= $aline;
                } else {
                    $cont .= $aline;
                }
            }
            FileCtl::make_datafile($this->keydat, $_conf['dat_perm']);
            if (file_put_contents($this->keydat, $cont, LOCK_EX) === false) {
                die("Error: cannot write file. downloadDat2chMaru()");
                return false;
            }
        }

        //$this->isonline = true;
        //$this->datochiok = 1;
        return true;
    }
    
    /**
     * ��ID�ł̎擾���ł��Ȃ������Ƃ��ɌĂяo�����
     *
     * @access  private
     * @return  boolean
     */
    function downloadDat2chMaruNotFound()
    {
        global $_conf;
        
        // �ă`�������W���܂��Ȃ�A�ă`�������W����BSID���ύX����Ă��܂��Ă���ꍇ�����鎞�̂��߂̎����`�������W�B
        if (empty($_REQUEST['relogin2ch'])) {
            $_REQUEST['relogin2ch'] = true;
            return $this->downloadDat();
        }
        
        $remarutori_atag = P2View::tagA(
            P2Util::buildQueryUri($_conf['read_php'],
                array(
                    'host' => $this->host,
                    'bbs'  => $this->bbs,
                    'key'  => $this->key,
                    'ls'   => $this->ls,
                    'maru' => 'true',
                    'relogin2ch' => '1',
                    UA::getQueryKey() => UA::getQueryValue()
                )
            ),
            '�Ď擾�����݂�'
        );
        $this->getdat_error_msg_ht .= "<p>p2 info - ��ID�ł̃X���b�h�擾�Ɏ��s���܂����B[{$remarutori_atag}]</p>";
        $this->diedat = true;
        return false;
    }
    
    /**
     * 2ch�̉ߋ����O�q�ɂ���dat.gz���_�E�����[�h���𓀂���
     *
     * @access  private
     * @return  true|string|false  �擾�ł������A�X�V���Ȃ������ꍇ��true�i�܂���"304 Not Modified"�j��Ԃ�
     */
    function downloadDat2chKako($uri, $ext)
    {
        global $_conf;

        $url = $uri . $ext;
    
        $method = "GET";
        if (!$httpua) {
            $httpua = "Monazilla/1.00 (" . $_conf['p2uaname'] . "/" . $_conf['p2version'] . ")";
        }
        
        $purl = parse_url($url);
        
        // �N�G���[
        if (isset($purl['query'])) {
            $purl['query'] = "?" . $purl['query'];
        } else {
            $purl['query'] = "";
        }
    
        // �v���L�V
        if ($_conf['proxy_use']) {
            $send_host = $_conf['proxy_host'];
            $send_port = $_conf['proxy_port'];
            $send_path = $url;
        } else {
            $send_host = $purl['host'];
            $send_port = $purl['port'];
            $send_path = $purl['path'] . $purl['query'];
        }
        
        // �f�t�H���g��80
        if (!$send_port) {
            $send_port = 80;
        }
    
        $request = $method . " " . $send_path . " HTTP/1.0\r\n";
        $request .= "Host: " . $purl['host'] . "\r\n";
        $request .= "User-Agent: " . $httpua . "\r\n";
        $request .= "Connection: Close\r\n";
        //$request .= "Accept-Encoding: gzip\r\n";
        /*
        if ($modified) {
            $request .= "If-Modified-Since: $modified\r\n";
        }
        */
        $request .= "\r\n";
    
        // WEB�T�[�o�֐ڑ�
        $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
        if (!$fp) {
            P2Util::pushInfoHtml(
                sprintf(
                    '<p>�T�[�o�ڑ��G���[: %s (%s)<br>p2 info - %s �ɐڑ��ł��܂���ł����B</div>',
                    hs($errstr), hs($errno),
                    P2View::tagA(P2Util::throughIme($url), hs($url), array('target' => $_conf['ext_win_target']))
                )
            );
            
            $this->diedat = true;
            return false;
        }
        
        // HTTP���N�G�X�g���M
        fputs($fp, $request);
        
        // HTTP�w�b�_���X�|���X���擾����
        $h = $this->freadHttpHeader($fp);
        if ($h === false) {
            fclose($fp);
            $this->_pushInfoHtmlFreadHttpHeaderError($url);
            $this->diedat = true;
            return false;
        }
        
        // {{{ HTTP�R�[�h���`�F�b�N
        
        $code = $h['code'];
        
        // Partial Content
        if ($code == "200") {
            // OK�B�������Ȃ�
        
        // Not Modified
        } elseif ($code == "304") {
            fclose($fp);
            //$this->isonline = true;
            return "304 Not Modified";
        
        // �\�����Ȃ�HTTP�R�[�h�B�Ȃ������Ɣ��f
        } else {
            fclose($fp);
            $this->downloadDat2chKakoNotFound($uri, $ext);
            return false;
        }
        
        // }}}

        if (isset($h['headers']['Last-Modified'])) {
            $lastmodified = $h['headers']['Last-Modified'];
        }
        
        if (isset($h['headers']['Content-Length'])) {
            if (preg_match("/^([0-9]+)/", $h['headers']['Content-Length'], $matches)) {
                $onbytes = $h['headers']['Content-Length'];
            }
        }
        if (isset($h['headers']['Content-Encoding'])) {
            if (preg_match("/^(x-)?gzip/", $h['headers']['Content-Encoding'], $matches)) {
                $isGzip = true;
            }
        }

        // body��ǂ�
        $body = '';
        while (!feof($fp)) {
            $body .= fread($fp, 8192);
        }
        fclose($fp);
        
        $done_gunzip = false;
        
        if ($isGzip) {
            $gztempfile = $this->keydat . ".gz";
            FileCtl::mkdirFor($gztempfile);
            if (file_put_contents($gztempfile, $body, LOCK_EX) === false) {
                die("Error: cannot write file. downloadDat2chKako()");
                return false;
            }
            if (extension_loaded('zlib')) {
                $body = FileCtl::getGzFileContents($gztempfile);
            } else {
                // ���ɑ��݂���Ȃ�ꎞ�o�b�N�A�b�v�ޔ�
                if (file_exists($this->keydat)) {
                    if (file_exists($this->keydat . ".bak")) {
                        unlink($this->keydat . ".bak");
                    }
                    rename($this->keydat, $this->keydat . ".bak");
                }
                $rcode = 1;
                // ��
                system("gzip -d $gztempfile", $rcode);
                if ($rcode != 0) {
                    if (file_exists($this->keydat . ".bak")) {
                        if (file_exists($this->keydat)) {
                            unlink($this->keydat);
                        }
                        // ���s�Ȃ�o�b�N�A�b�v�߂�
                        rename($this->keydat . ".bak", $this->keydat);
                    }
                    $this->getdat_error_msg_ht .= "<p>p2 info - 2�����˂�ߋ����O�q�ɂ���̃X���b�h��荞�݂́APHP��<a href=\"http://www.php.net/manual/ja/ref.zlib.php\">zlib�g�����W���[��</a>���Ȃ����Asystem��gzip�R�}���h���g�p�\�łȂ���΂ł��܂���B</p>";
                    // gztemp�t�@�C�����̂Ă�
                    file_exists($gztempfile) and unlink($gztempfile);
                    $this->diedat = true;
                    return false;
                    
                } else {
                    if (file_exists($this->keydat . ".bak")) {
                        unlink($this->keydat . ".bak");
                    }
                    $done_gunzip = true;
                }

            }
            // temp�t�@�C�����̂Ă�
            file_exists($gztempfile) and unlink($gztempfile);
        }

        if (!$done_gunzip) {
            FileCtl::make_datafile($this->keydat, $_conf['dat_perm']);
            if (false === file_put_contents($this->keydat, $body, LOCK_EX)) {
                die("Error: cannot write file. downloadDat2chKako()");
                return false;
            }
        }

        //$this->isonline = true;
        return true;
    }
    
    /**
     * �ߋ����O���擾�ł��Ȃ������Ƃ��ɌĂяo�����
     *
     * @access  private
     * @return  void
     */
    function downloadDat2chKakoNotFound($uri, $ext)
    {
        global $_conf;
        
        if ($ext == '.dat.gz') {
            //.dat.gz���Ȃ�������.dat�ł�����x
            return $this->downloadDat2chKako($uri, '.dat');
        }
        
        $kakolog_ht = '';
        if (!empty($_GET['kakolog'])) {
            $kakolog_uri = "{$_GET['kakolog']}.html";
            $atag = P2View::tagA($kakolog_uri,
                hs($kakolog_uri),
                array('target' => $_conf['bbs_win_target'])
            );
            $kakolog_ht = "<p>{$atag}</p>";
        }
        $this->getdat_error_msg_ht .= "<p>p2 info - 2�����˂�ߋ����O�q�ɂ���̃X���b�h��荞�݂Ɏ��s���܂����B</p>";
        $this->getdat_error_msg_ht .= $kakolog_ht;
        $this->diedat = true;
    }
    
    /**
     * 2ch��dat���擾�ł��Ȃ�����������Ԃ�
     *
     * @access  private
     * @param   string|null  $reason
     * @return  string  �G���[���b�Z�[�WHTML�i�������킩��Ȃ��ꍇ�͋�ŕԂ��j
     */
    function get2chDatError($reason = null)
    {
        global $_conf;
        
        // �z�X�g�ړ]���o�ŕύX�����z�X�g�����ɖ߂�
        if (!empty($this->old_host)) {
            $this->host = $this->old_host;
            $this->old_host = null;
        }
        
        $read_url = "http://{$this->host}/test/read.cgi/{$this->bbs}/{$this->key}/";
        
        // {{{ read.cgi ����HTML���擾
        
        $read_response_html = '';
        
        if (!$reason) {
            require_once P2_LIB_DIR . '/wap.class.php';
            $wap_ua = new WapUserAgent;
            $wap_ua->setAgent($_conf['p2uaname'] . '/' . $_conf['p2version']); // �����́A"Monazilla/" �������NG
            $wap_ua->setTimeout($_conf['fsockopen_time_limit']);
            $wap_req = new WapRequest;
            $wap_req->setUrl($read_url);
            if ($_conf['proxy_use']) {
                $wap_req->setProxy($_conf['proxy_host'], $_conf['proxy_port']);
            }
            $wap_res = $wap_ua->request($wap_req);
        
            if (!$wap_res or !$wap_res->is_success()) {
                $atag = P2View::tagA(P2Util::throughIme($wap_req->url), hs($wap_req->url), array('target' => $_conf['ext_win_target']));
                $msg = sprintf(
                    '<div>Error: %s %s<br>p2 info - %s �ɐڑ��ł��܂���ł����B</div>',
                    hs($wap_res->code),
                    hs($wap_res->message),
                    $atag
                );
                P2Util::pushInfoHtml($msg);

            } else {
                $read_response_html = $wap_res->content;
            }
            unset($wap_ua, $wap_req, $wap_res);
        }
        
        // }}}
        // {{{ �擾����HTML�i$read_response_html�j����͂��āA������������
        
        $dat_response_status = '';
        $dat_response_msg_ht = '';

        $kakosoko_match = "/���̃X���b�h�͉ߋ����O�q�ɂɊi.{1,2}����Ă��܂�/";
        
        $naidesu_match = "{<title>����Ȕ�or�X���b�h�Ȃ��ł��B</title>}";
        $error3939_match = "{<title>�Q�����˂� error 3939</title>}"; // �ߋ����O�q�ɂ�html���̎��i���ɂ����邩���A�悭�m��Ȃ��j
        
        //<a href="http://qb5.2ch.net/sec2chd/kako/1091/10916/1091634596.html">
        //<a href="../../../../mac/kako/1004/10046/1004680972.html">
        //$kakohtml_match = "{<a href=\"\.\./\.\./\.\./\.\./([^/]+/kako/\d+(/\d+)?/(\d+)).html\">}";
        $kakohtml_match = "{/([^/]+/kako/\d+(/\d+)?/(\d+)).html\">}";
        $waithtml_match = "/html�������̂�҂��Ă���悤�ł��B/";
        
        //
        // <title>�����̃X���b�h�͉ߋ����O�q�ɂ�
        //
        if ($reason == 'datochi' or preg_match($kakosoko_match, $read_response_html, $matches)) {
            $dat_response_status = "���̃X���b�h�͉ߋ����O�q�ɂɊi�[����Ă��܂��B";
            $marutori_ht = '';
            //if (file_exists($_conf['idpw2ch_php']) || file_exists($_conf['sid2ch_php'])) {
                
                $marutori_ht = sprintf(' [%s]',
                    P2View::tagA(
                        P2Util::buildQueryUri($_conf['read_php'],
                            array(
                                'host' => $this->host,
                                'bbs'  => $this->bbs,
                                'key'  => $this->key,
                                'ls'   => $this->ls,
                                'maru' => 'true',
                                UA::getQueryKey() => UA::getQueryValue()
                            )
                        ),
                        hs('��ID��p2�Ɏ�荞��')
                    )
                );
                
            //} else {
            //    $marutori_ht = "<a href=\"login2ch.php?b={$_conf['b']}\" target=\"subject\">��ID���O�C��</a>";
            //}
            $dat_response_msg_ht = "<p>2ch info - ���̃X���b�h�͉ߋ����O�q�ɂɊi�[����Ă��܂��B {$marutori_ht}</p>";
        
        //    
        // <title>������Ȕ�or�X���b�h�Ȃ��ł��Bor error 3939
        //
        } elseif (preg_match($naidesu_match, $read_response_html, $matches) || preg_match($error3939_match, $read_response_html, $matches)) {
        
            if (preg_match($kakohtml_match, $read_response_html, $matches)) {
                $dat_response_status = "����! �ߋ����O�q�ɂŁAhtml�����ꂽ�X���b�h�𔭌����܂����B";
                $kakolog_uri = "http://{$this->host}/{$matches[1]}";

                $read_kako_url = P2Util::buildQueryUri($_conf['read_php'],
                    array(
                        'host' => $this->host,
                        'bbs'  => $this->bbs,
                        'key'  => $this->key,
                        'ls'   => $this->ls,
                        'kakolog' => $kakolog_uri,
                        'kakoget' => '1',
                        UA::getQueryKey() => UA::getQueryValue()
                    )
                );

                $soko_atag = P2View::tagA($kakolog_uri . '.html',
                    '�X���b�h ' . $matches[3] . '.html',
                    array('target' => $_conf['bbs_win_target'])
                );
                
                $dat_response_msg_ht = sprintf(
                    '<p>2ch info - ����! �ߋ����O�q�ɂŁA%s �𔭌����܂����B [<a href="%s">p2�Ɏ�荞��œǂ�</a>]</p>',
                    $soko_atag,
                    hs($read_kako_url)
                );
                
            } elseif (preg_match($waithtml_match, $read_response_html, $matches)) {
                $dat_response_status = "����! �X���b�h��html�������̂�҂��Ă���悤�ł��B";

                $marutori_atag = P2View::tagA(
                    P2Util::buildQueryUri($_conf['read_php'],
                        array(
                            'host' => $this->host,
                            'bbs'  => $this->bbs,
                            'key'  => $this->key,
                            'ls'   => $this->ls,
                            'maru' => 'true',
                            UA::getQueryKey() => UA::getQueryValue()
                        )
                    ),
                    hs('��ID��p2�Ɏ�荞��')
                );
                $marutori_ht = " [$marutori_atag]";
                
                $dat_response_msg_ht = "<p>2ch info - ����! �X���b�h��html�������̂�҂��Ă���悤�ł��B {$marutori_ht}</p>";
                
            } else {
                if (!empty($_GET['kakolog'])) {
                    $dat_response_status = "����Ȕ�or�X���b�h�Ȃ��ł��B";
                    
                    $kako_html_url = $_GET['kakolog'] . ".html";
                    $read_kako_url = P2Util::buildQueryUri($_conf['read_php'],
                        array(
                            'host' => $this->host,
                            'bbs'  => $this->bbs,
                            'key'  => $this->key,
                            'ls'   => $this->ls,
                            'kakolog' => $_GET['kakolog'],
                            'kakoget' => '1',
                            UA::getQueryKey() => UA::getQueryValue()
                        )
                    );

                    $attrs = array();
                    if ($_conf['bbs_win_target']) {
                        $attrs['target'] = $_conf['bbs_win_target'];
                    }
                    $read_kako_atag  = P2View::tagA($kako_html_url, null, $attrs);
                    $read_by_p2_atag = P2View::tagA($read_kako_url, 'p2�Ƀ��O����荞��œǂ�');
                    
                    $dat_response_msg_ht = "<p>2ch info - ����Ȕ�or�X���b�h�Ȃ��ł��B</p>";
                    $dat_response_msg_ht .= "<p>$read_kako_atag [$read_by_p2_atag]</p>";
                    
                } else {
                    $dat_response_status = "����Ȕ�or�X���b�h�Ȃ��ł��B";
                    $dat_response_msg_ht = "<p>2ch info - ����Ȕ�or�X���b�h�Ȃ��ł��B</p>";
                }
            }
        
        // ������������Ȃ��ꍇ�ł��A�Ƃ肠�����ߋ����O��荞�݂̃����N���ێ����Ă���B�Ǝv���B���܂�o���Ă��Ȃ� 2005/2/27 aki
        } elseif (!empty($_GET['kakolog'])) {
            $dat_response_status = '';
            
            $kako_html_url = $_GET['kakolog'] . '.html';
            $read_kako_url = P2Util::buildQueryUri($_conf['read_php'],
                array(
                    'host' => $this->host,
                    'bbs'  => $this->bbs,
                    'key'  => $this->key,
                    'ls'   => $this->ls,
                    'kakolog' => $_GET['kakolog'],
                    'kakoget' => '1',
                    UA::getQueryKey() => UA::getQueryValue()
                )
            );
            $attrs = array();
            if ($_conf['bbs_win_target']) {
                $attrs['target'] = $_conf['bbs_win_target'];
            }
            $read_kako_atag  = P2View::tagA($kako_html_url, null, $attrs);
            $read_by_p2_atag = P2View::tagA($read_kako_url, 'p2�Ƀ��O����荞��œǂ�');
            
            $dat_response_msg_ht = "<p>$read_kako_atag [$read_by_p2_atag]</p>";
        }
        
        // }}}
        
        return $dat_response_msg_ht;
    }
    
    /**
     * >>1�݂̂��v���r���[�\�����邽�߂�HTML���擾����i�I���U�t���C�ɑΉ��j
     *
     * @access  public
     * @return  string|false
     */
    function previewOne()
    {
        global $_conf, $ptitle_ht;

        if (!($this->host && $this->bbs && $this->key)) {
            return false;
        }
        
        $first_line = '';
        
        // ���[�J��dat����擾
        if (is_readable($this->keydat)) {
            $fd = fopen($this->keydat, "rb");
            $first_line = fgets($fd, 32800);
            fclose($fd);
        }
        
        if ($first_line) {
        
            // be.2ch.net �Ȃ�EUC��SJIS�ϊ�
            if (P2Util::isHostBe2chNet($this->host)) {
                $first_line = mb_convert_encoding($first_line, 'SJIS-win', 'eucJP-win');
            }
            
            $first_datline = rtrim($first_line);
            if (strstr($first_datline, "<>")) {
                $datline_sepa = "<>";
            } else {
                $datline_sepa = ",";
                $this->dat_type = "2ch_old";
            }
            $d = explode($datline_sepa, $first_datline);
            $this->setTtitle($d[4]);
            
            // �֋X��
            if (!$this->readnum) {
                $this->readnum = 1;
            }
        }
        
        // ���[�J��dat�Ȃ���΃I�����C������
        if (!$first_line) {
        
            $method = "GET";
            $url = "http://" . $this->host . "/{$this->bbs}/dat/{$this->key}.dat";
            
            $purl = parse_url($url);
            
            if (isset($purl['query'])) {
                $purl['query'] = "?" . $purl['query'];
            } else {
                $purl['query'] = "";
            }
            
            // �v���L�V
            if ($_conf['proxy_use']) {
                $send_host = $_conf['proxy_host'];
                $send_port = $_conf['proxy_port'];
                $send_path = $url;
            } else {
                $send_host = $purl['host'];
                $send_port = geti($purl['port']);
                $send_path = $purl['path'] . $purl['query'];
            }
            
            // �f�t�H���g��80
            !$send_port and $send_port = 80;
    
            $request = $method . " " . $send_path . " HTTP/1.0\r\n";
            $request .= "Host: " . $purl['host'] . "\r\n";
            $request .= "User-Agent: Monazilla/1.00 (" . $_conf['p2uaname'] . "/" . $_conf['p2version'] . ")" . "\r\n";
            // $request .= "Range: bytes={$from_bytes}-\r\n";
    
            // Basic�F�ؗp�̃w�b�_
            if (isset($purl['user']) && isset($purl['pass'])) {
                $request .= "Authorization: Basic " . base64_encode($purl['user'] . ":" . $purl['pass']) . "\r\n";
            }
            
            $request .= "Connection: Close\r\n";
            $request .= "\r\n";
            
            // WEB�T�[�o�֐ڑ�
            $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
            if (!$fp) {
                P2Util::pushInfoHtml(
                    sprintf(
                        '<p>�T�[�o�ڑ��G���[: %s (%s)<br>p2 info - %s �ɐڑ��ł��܂���ł����B</p>',
                        $errstr, $errno,
                        P2View::tagA(P2Util::throughIme($url), hs($url), array('target' => $_conf['ext_win_target']))
                    )
                );
                
                $this->diedat = true;
                return false;
            }
            
            // HTTP���N�G�X�g���M
            fputs($fp, $request);
            
            // HTTP�w�b�_���X�|���X���擾����
            $h = $this->freadHttpHeader($fp);
            if ($h === false) {
                fclose($fp);
                $this->_pushInfoHtmlFreadHttpHeaderError($url);
                $this->diedat = true;
                return false;
            }
            
            // {{{ HTTP�R�[�h���`�F�b�N
            
            $code = $h['code'];
            
            // Partial Content
            if ($code == "200") {
                // OK�B�������Ȃ�
            
            // �\�����Ȃ�HTTP�R�[�h�B�Ȃ������Ɣ��f����
            } else {
                fclose($fp);
                $this->previewOneNotFound();
                return false;
            }
            
            // }}}

            if (isset($h['headers']['Content-Length'])) {
                if (preg_match("/^([0-9]+)/", $h['headers']['Content-Length'], $matches)) {
                    $onbytes = $h['headers']['Content-Length'];
                }
            }

            // body����s�ڂ����ǂ�
            $first_line = fgets($fp, 32800);
            fclose($fp);
            
            // be.2ch.net �Ȃ�EUC��SJIS�ϊ�
            if (P2Util::isHostBe2chNet($this->host)) {
                $first_line = mb_convert_encoding($first_line, 'SJIS-win', 'eucJP-win');
            }
            
            $first_datline = rtrim($first_line);
            if (strstr($first_datline, '<>')) {
                $datline_sepa = '<>';
            } else {
                $datline_sepa = ',';
                $this->dat_type = '2ch_old';
            }
            $d = explode($datline_sepa, $first_datline);
            $this->setTtitle($d[4]);
            
            $this->onthefly = true;
        }
        
        // �����ɂ̓I���U�t���C�ł͂Ȃ����A�l�ɂƂ��Ắi���ǋL�^������Ȃ��Ƃ����Ӗ��Łj�I���U�t���C
        if (!$this->isKitoku()) {
            $this->onthefly = true;
        }
        
        $body = '';
        if (!empty($this->onthefly)) {
            // PC
            if (empty($GLOBALS['_conf']['ktai'])) {
                $body .= "<div><span class=\"onthefly\">�v���r���[</span></div>";
            // �g��
            } else {
                $body .= "<div><font size=\"-1\" color=\"#00aa00\">����ޭ�</font></div>";
            }
        }
        
        empty($GLOBALS['_conf']['ktai']) and $body .= "<dl>";
        
        require_once P2_LIB_DIR . '/ShowThread.php';
        
        // PC
        if (!$GLOBALS['_conf']['ktai']) {
            require_once P2_LIB_DIR . '/ShowThreadPc.php';
            $aShowThread = new ShowThreadPc($this);
        // �g��
        } else {
            require_once P2_LIB_DIR . '/ShowThreadK.php';
            $aShowThread = new ShowThreadK($this);
        }
        
        $body .= $aShowThread->transRes($first_line, 1); // 1��\��
        unset($aShowThread);
        
        empty($GLOBALS['_conf']['ktai']) and $body .= "</dl>\n";
        
        return $body;
    }
    
    /**
     * >>1���v���r���[�ŃX���b�h�f�[�^��������Ȃ������Ƃ��ɌĂяo�����
     *
     * @access  private
     * @return  void
     */
    function previewOneNotFound()
    {
        // 2ch, bbspink �Ȃ�read.cgi�Ŋm�F
        if (P2Util::isHost2chs($this->host)) {
            $this->getdat_error_msg_ht .= $this->get2chDatError();
        }
        $this->diedat = true;
    }
    
    /**
     * @access  private
     * @return  array
     */
    function getStartToFromLs($ls, &$nofirst)
    {
        // �͈͎w��ŕ���
        $lr = explode('-', $ls);
        
        // �͈͎w�肪�����
        if (sizeof($lr) > 1) {
            if (!$start = intval($lr[0])) {
                $start = 1;
            }
            if (!$to = intval($lr[1])) {
                $to = $this->rescount;
            }
        
        // �͈͎w�肪�Ȃ����
        } else {
        
            // ���X�Ԏw��
            if (intval($ls) > 0) {
                $start = intval($ls);
                $to = intval($ls);
                $nofirst = true;
            
            // �w�肪�Ȃ� or �s���ȏꍇ�́Aall�Ɠ����\���ɂ���
            } else {
                $start = 1;
                $to = $this->rescount;
            }
        }
        
        // ���]
        if ($start > $to) {
            $start_t = $start;
            $start = $to;
            $to = $start_t;
        }
        
        return array($start, $to);
    }
    
    /**
     * @access  public
     * @return  boolean
     */
    function inResrangeMulti($num)
    {
        foreach ($this->resrange_multi as $ls) {
            if ($ls['start'] <= $num and $num <= $ls['to']) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * @access  private
     * @return  integer
     */
    function countResrangeMulti($nofirst = false)
    {
        $c = array();
        foreach ($this->resrange_multi as $ls) {
            for ($i = $ls['start']; $i <= $ls['to']; $i++) {
                $c[$i] = true;
            }
        }
        return count($c);
    }
    
    /**
     * $ls�𕪉����� start �� to �� nofirst �����߂ăZ�b�g����
     *
     * @access  public
     * @return  void
     */
    function lsToPoint()
    {
        global $_conf;

        $to = false;
        $nofirst = false;
        
        /*
        if (!empty($_GET['onlyone'])) {
            $this->ls = '1';
        }
        */
        
        $this->ls = str_replace(' ', '+', $this->ls);
        
        if ($this->ls != 'all') {
            $this->ls = preg_replace('/[^0-9,\-\+ln]/', '', $this->ls);
        }
        $ls = $this->ls;
        
        // n���܂�ł���ꍇ�́A>>1��\�����Ȃ��i$nofirst�j
        if (strstr($ls, 'n')) {
            $nofirst = true;
            $ls = preg_replace("/n/", "", $ls);
        }
        
        // l�w�肪����΁i�ŋ�N���̎w��j
        if (substr($ls, 0, 1) == "l") {
            $ln = intval(substr($ls, 1));
            if ($_conf['ktai']) {
                if ($ln > $_conf['k_rnum_range']) {
                    $ln = $_conf['k_rnum_range'];
                }
            }
            $start = $this->rescount - $ln + 1;
            if ($start < 1) {
                $start = 1;
            }
            $to = $this->rescount;
            
        // all�w��Ȃ�
        } elseif ($ls == "all") {
            $start = 1;
            $to = $this->rescount;
            
        } else {
            
            $lss = preg_split('/[,+ ]/', $ls, -1, PREG_SPLIT_NO_EMPTY);
            
            // �}���`�w��Ȃ�
            if (sizeof($lss) > 1) {
                $nofirst = true;
                
                foreach ($lss as $v) {
                    list($start_t, $to_t) = $this->getStartToFromLs($v, $dummy_nofirst);
                    
                    $this->resrange_multi[] = array('start' => $start_t, 'to' => $to_t);
                    
                    if (empty($start) || $start > $start_t) {
                        $start = $start_t;
                    }
                    if (empty($to) || $to < $to_t) {
                        $to = $to_t;
                    }
                }
            
            // ���ʎw��Ȃ�
            } else {
                list($start, $to) = $this->getStartToFromLs($ls, $nofirst);
            }
        }
        
        // �V���܂Ƃߓǂ݂̕\��������
        if (isset($GLOBALS['rnum_all_range']) and $GLOBALS['rnum_all_range'] > 0) {
        
            /*
            ���g�т̐V���܂Ƃߓǂ݂��A����������ŏI��������ɁA�́u����or�X�V�v������

            ���~�b�g < �X���̕\���͈�
            �����~�b�g�́@0
            �X���̕\���͈͂��I����O�Ƀ��~�b�g������
            ������

            ���~�b�g > �X���̕\���͈�
            �����~�b�g�� +
            ���~�b�g�����c���Ă���ԂɁA�X���̕\���͈͂��I����
            ���X�V

            ���~�b�g = �X���̕\���͈�
            �����~�b�g�� 0
            �X���̕\���͈͒��x�Ń��~�b�g����������
            ������? �X�V?
            �����̏ꍇ���X�V�̏ꍇ������B���������̂��߁A
            ���̃X���̎c��V���������邩�ǂ������s���Ŕ���ł��Ȃ��B
            */
            
            // ���~�b�g���X���̕\���͈͂�菬�����ꍇ�́A�X���̕\���͈͂����~�b�g�ɍ��킹��
            $limit_to = $start + $GLOBALS['rnum_all_range'] - 1;
            
            if ($limit_to < $to) {
                $to = $limit_to;
            
            // �X���̕\���͈͒��x�Ń��~�b�g�����������ꍇ
            } elseif ($limit_to == $to) {
                $GLOBALS['_is_eq_limit_to_and_to'] = true;
            }
            
            // ���̃��~�b�g�́A����̃X���̕\���͈͕������炵����
            $GLOBALS['rnum_all_range'] = $GLOBALS['rnum_all_range'] - ($to - $start) -1;
            
            //print_r("$start, $to, {$GLOBALS['rnum_all_range']}");
        
        } else {
            // �g�їp�̕\��������
            if ($_conf['ktai']) {
                /*
                if ($start + $_conf['k_rnum_range'] -1 <= $to) {
                    $to = $start + $_conf['k_rnum_range'] -1;
                }
                */
                
                // �}���`���̌g�ѕ\���������͕ʏ���
                if (!$this->resrange_multi) {
                    // ��X���ł́A�O����܂݁A����+1�ƂȂ�̂ŁA1���܂�����
                    if ($start + $_conf['k_rnum_range'] <= $to) {
                        $to = $start + $_conf['k_rnum_range'];
                    }
                }
                
                // �t�B���^�����O���́A�S���X�K�p�ƂȂ�i$filter_range �ŕʓr���������j
                if (isset($GLOBALS['word'])) {
                    $start = 1;
                    $to = $this->rescount;
                    $nofirst = false;
                }
            }
        }
        
        if ($this->resrange_multi) {
            $page = isset($_REQUEST['page']) ? max(1, intval($_REQUEST['page'])) : 1;
            $reach = $page * $GLOBALS['_conf']['k_rnum_range'];
            if ($reach < $this->countResrangeMulti()) {
                $this->resrange_multi_exists_next = true;
            }
        } else {
            $this->resrange_readnum = $to;
        }
        
        $this->resrange = array('start' => $start, 'to' => $to, 'nofirst' => $nofirst);
    }
    
    /**
     * Dat��ǂݍ���
     * $this->datlines �� set ����
     *
     * @access  public
     * @return  boolean  ���s����
     */
    function readDat()
    {
        global $_conf;
        
        if (!file_exists($this->keydat)) {
            return false;
        }
        
        if ($this->datlines = file($this->keydat)) {

            // be.2ch.net �Ȃ�EUC��SJIS�ϊ�
            // �O�̂���SJIS��UTF-8�������R�[�h����̌��ɓ���Ă���
            // �E�E�E���A�������������^�C�g���̃X���b�h�Ō딻�肪�������̂ŁA�w�肵�Ă���
            if (P2Util::isHostBe2chNet($this->host)) {
                //mb_convert_variables('SJIS-win', 'eucJP-win,SJIS-win,UTF-8', $this->datlines);
                mb_convert_variables('SJIS-win', 'eucJP-win', $this->datlines);
            }

            if (!strstr($this->datlines[0], '<>')) {
                $this->dat_type = "2ch_old";
            }
        }
        
        $this->rescount = sizeof($this->datlines);
        
        if ($_conf['flex_idpopup']) {
            $this->setIdCount($this->datlines);
        }
        
        return true;
    }

    /**
     * ��̃X�����ł�ID�o�������Z�b�g����
     *
     * @access  private
     * @param   array    $lines
     * @return  void
     */
    function setIdCount($lines)
    {
        if (!is_array($lines)) {
            return;
        }
        foreach ($lines as $k => $line) {
            $lar = explode('<>', $line);
            if (preg_match('|ID: ?([0-9a-zA-Z/.+]{8,10})|', $lar[2], $matches)) {
                $id = $matches[1];
                if (isset($this->idcount[$id])) {
                    $this->idcount[$id]++;
                } else {
                    $this->idcount[$id] = 1;
                }
            }
            
            /*
            $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('set_rrescount');
            
            // �t�Q�Ƃ̂��߂̈��p���X�ԍ��擾�i�������x��2,3�����ɂȂ�c�j
            if ($n = $this->getQuoteResNumName($lar[0])) {
                if (isset($this->rrescount[$k])) {
                    $this->rrescount[$k][] = $n;
                } else {
                    $this->rrescount[$k] = array($n);
                }
            }
            
            if ($nums = $this->getQuoteResNumsMsg($lar[3])) {
                if (isset($this->rrescount[$k])) {
                    $this->rrescount[$k] = $nums;
                } else {
                    $this->rrescount[$k] = $nums;
                }
            }
            
            $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('set_rrescount');
            */
        }
    }
    
    /**
     * ���O�ɂ�����p���X�ԍ����擾����
     *
     * @access  private
     * @param   string  $name�i���t�H�[�}�b�g�j
     * @return  integer|false
     */
    function getQuoteResNumName($name)
    {
        // �g���b�v������
        $name = preg_replace("/(��.*)/", "", $name, 1);
        
        if (preg_match("/[0-9]+/", $name, $m)) {
            return (int) $m[0];
        }
        return false;
    }
    
    /**
     * ���b�Z�[�W�ɂ�����p���X�ԍ����擾����
     *
     * @access  private
     * @param   string  $msg�i���t�H�[�}�b�g�j
     * @return  array|false
     */
    function getQuoteResNumsMsg($msg)
    {
        $quote_res_nums = array();
        
        // >>1�̃����N������
        // <a href="../test/read.cgi/accuse/1001506967/1" target="_blank">&gt;&gt;1</a>
        $msg = preg_replace('{<[Aa] .+?>(&gt;&gt;[1-9][\\d\\-]*)</[Aa]>}', '$1', $msg);

        if (preg_match_all('/(?:&gt;|��)+ ?([1-9](?:[0-9\\- ,=.]|�A)*)/', $msg, $out, PREG_PATTERN_ORDER)) {

            foreach ($out[1] as $numberq) {
                
                if (preg_match_all('/[1-9]\\d*/', $numberq, $matches, PREG_PATTERN_ORDER)) {
                    
                    // $matches[0] �̓p�^�[���S�̂Ƀ}�b�`����������̔z��
                    foreach ($matches[0] as $a_quote_res_num) {
                        $quote_res_nums[] = $a_quote_res_num;
                     }
                }
            }
        }
        return array_unique($quote_res_nums);
    }
    
    /**
     * datline��explode����
     *
     * @access  public
     * @param   string  $aline
     * @return  array
     */
    function explodeDatLine($aline)
    {
        global $_conf;
        
        if (!$aline = rtrim($aline)) {
            return array();
        }
        
        $stripped = false;
        if ($_conf['strip_tags_trusted_dat'] || !P2Util::isTrustedHost($this->host)) {
            require_once P2_LIB_DIR . '/HTML/StripTags.php';
            $HTML_StripTags = new HTML_StripTags;
            $aline = $HTML_StripTags->cleanup($aline);
            $stripped = true;
        }
        
        if ($this->dat_type == '2ch_old') {
            $parts = explode(',', $aline);
        } else {
            $parts = explode('<>', $aline);
        }
        
        if (!$stripped && P2Util::isHost2chs($this->host)) {
            // iframe ���폜�B2ch�����퉻���ĕK�v�Ȃ��Ȃ����炱�̃R�[�h�͊O�������B2005/05/19
            isset($parts[3]) and $parts[3] = preg_replace('{<(iframe|script)( .*?)?>.*?</\\1>}i', '', $parts[3]);
        }
        
        return $parts;
    }
}
