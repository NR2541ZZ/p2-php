<?php
// p2 - �X���b�h ���[�h �N���X

require_once './filectl.class.php';
require_once './p2util.class.php'; // p2�p�̃��[�e�B���e�B�N���X

/**
 * �X���b�h���[�h�N���X
 */
class ThreadRead extends Thread{

    var $datlines; // dat����ǂݍ��񂾃��C�����i�[����z��
    
    var $resrange; // array( 'start' => i, 'to' => i, 'nofirst' => bool )
    
    var $onbytes; // �T�[�o����擾����dat�T�C�Y
    var $diedat; // �T�[�o����dat�擾���悤�Ƃ��Ăł��Ȃ���������true���Z�b�g�����
    var $onthefly; // ���[�J����dat�ۑ����Ȃ��I���U�t���C�ǂݍ��݂Ȃ�true

    var $idcount; // �z��Bkey �� ID�L��, value �� ID�o����
    
    var $getdat_error_msg_ht; // dat�擾�Ɏ��s�������ɕ\������郁�b�Z�[�W�iHTML�j

    /**
     * �R���X�g���N�^
     */
    function ThreadRead()
    {
        $this->getdat_error_msg_ht = "";
    }

    /**
     * DAT���_�E�����[�h����
     */
    function downloadDat()
    {
        global $_conf, $uaMona, $SID2ch;
        
        // �܂�BBS
        if (P2Util::isHostMachiBbs($this->host)) {
            require_once 'read_machibbs.inc.php';
            machiDownload();
        // JBBS@�������
        } elseif (P2Util::isHostJbbsShitaraba($this->host)) {
            include_once 'read_shitaraba.inc.php';
            shitarabaDownload();
        
        // 2ch�n
        } else {
            $this->getDatBytesFromLocalDat(); // $aThread->length ��set

            // 2ch bbspink���ǂ�
            if (P2Util::isHost2chs($this->host) and $_GET['maru']) {
                // ���O�C�����ĂȂ���� or ���O�C����A24���Ԉȏ�o�߂��Ă����玩���ă��O�C��
                if ((!file_exists($_conf['sid2ch_php']) or $_REQUEST['relogin2ch']) or (@filemtime($sid2ch_php) < time() - 60*60*24)) {
                    include_once './login2ch.inc.php';
                    if (!login2ch()) {
                        $this->getdat_error_msg_ht .= $this->get2chDatError();
                        $this->diedat = true;
                        return false;
                    }
                }

                include $_conf['sid2ch_php'];
                $this->downloadDat2chMaru();

            // 2ch�̉ߋ����O�q�ɓǂ�
            } elseif ($_GET['kakolog'] && $_GET['kakoget']) {
                if ($_GET['kakoget'] == 1) {
                    $ext = '.dat.gz';
                } elseif ($_GET['kakoget'] == 2) {
                    $ext = '.dat';
                }
                $this->downloadDat2chKako(urldecode($_GET['kakolog']), $ext);
                
            // 2ch or 2ch�݊�
            } else {
                // DAT������DL����
                $this->downloadDat2ch($this->length);
            }

        }
    
    }
    
    /**
     * �W�����@�� 2ch�݊�DAT �������_�E�����[�h����
     *
     * @return mix �擾�ł������A�X�V���Ȃ������ꍇ��true��Ԃ�
     */
    function downloadDat2ch($from_bytes)
    {
        global $_conf, $_info_msg_ht;
        global $debug, $prof;
    
        if (!($this->host && $this->bbs && $this->key)) {
            return false;
        }

        $from_bytes = intval($from_bytes);
        
        if ($from_bytes == 0) {
            $mode = "wb";
            $zero_read = true;
        } else {
            $mode = "a";
            $from_bytes = $from_bytes-1;
        }
        
        $method = "GET";
        if (!$uaMona) {$uaMona = "Monazilla/1.00";}
        $p2ua = $uaMona." (".$_conf['p2name']."/".$_conf['p2version'].")";
        
        $url = "http://" . $this->host . "/{$this->bbs}/dat/{$this->key}.dat";
        //$url="http://news2.2ch.net/test/read.cgi?bbs=newsplus&key=1038486598";

        $purl = parse_url($url); //URL����
        if (isset($purl['query'])) { //�N�G���[
            $purl['query'] = "?".$purl['query'];
        } else {
            $purl['query'] = "";
        }

        //�v���L�V
        if ($_conf['proxy_use']) {
            $send_host = $_conf['proxy_host'];
            $send_port = $_conf['proxy_port'];
            $send_path = $url;
        } else {
            $send_host = $purl['host'];
            $send_port = $purl['port'];
            $send_path = $purl['path'].$purl['query'];
        }
        
        if (!$send_port) {$send_port = 80;}    // �f�t�H���g��80
            
        $request = $method." ".$send_path." HTTP/1.0\r\n";
        $request .= "Host: ".$purl['host']."\r\n";
        $request .= "Accept: */*\r\n";
        //$request .= "Accept-Charset: Shift_JIS\r\n";
        //$request .= "Accept-Encoding: gzip, deflate\r\n";
        $request .= "Accept-Language: ja, en\r\n";
        $request .= "User-Agent: ".$p2ua."\r\n";
        if (!$zero_read) {$request .= "Range: bytes={$from_bytes}-\r\n";}
        $request .= "Referer: http://{$purl['host']}/{$this->bbs}/\r\n";

        if ($this->modified) {
            $request .= "If-Modified-Since: ".$this->modified."\r\n";
        }
        
        // Basic�F�ؗp�̃w�b�_
        if (isset($purl['user']) && isset($purl['pass'])) {
            $request .= "Authorization: Basic ".base64_encode($purl['user'].":".$purl['pass'])."\r\n";
        }

        $request .= "Connection: Close\r\n";
    
        $request .= "\r\n";
        
        /* WEB�T�[�o�֐ڑ� */
        $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
        if (!$fp) {
            $url_t = P2Util::throughIme($url);
            $_info_msg_ht .= "<p>�T�[�o�ڑ��G���[: {$errstr} ({$errno})<br>p2 info: <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$url}</a> �ɐڑ��ł��܂���ł����B</p>";
            $this->diedat = true;
            return false;
        }
        $wr = "";
        fputs($fp, $request);

        while (!feof($fp)) {

            if ($start_here) {

                if ($code=="200" || $code=="206") {
                    
                    while (!feof($fp)) {
                        $wr .= fread($fp, 4096);
                    }
                    
                    // �����̉��s�ł��ځ[��`�F�b�N
                    if (!$zero_read) {
                        if(substr($wr, 0, 1)!="\n"){
                            //echo "���ځ[�񌟏o";
                            fclose ($fp);
                            unset($this->onbytes);
                            unset($this->modified);
                            return $this->downloadDat2ch(0); //���ځ[�񌟏o�B�S����蒼���B
                        }
                        $wr = substr($wr, 1);
                    }
                    FileCtl::make_datafile($this->keydat, $_conf['dat_perm']);
                    $fdat = fopen($this->keydat, $mode);
                    @flock($fdat, LOCK_EX);
                    fwrite($fdat, $wr);
                    @flock($fdat, LOCK_UN);
                    fclose ($fdat);
                    //echo $wr."<br>";// for debug
                    
                    $debug && $prof->enterSection("dat_size_check");
                    // �擾��T�C�Y�`�F�b�N
                    if ($zero_read == false && $this->onbytes) {
                        $this->getDatBytesFromLocalDat(); // $aThread->length ��set
                        if ($this->onbytes != $this->length) {
                            fclose($fp);
                            unset($this->onbytes);
                            unset($this->modified);
                            $_info_msg_ht .= "p2 info: $this->onbytes/$this->length �t�@�C���T�C�Y���ςȂ̂ŁAdat���Ď擾<br>";
                            $debug && $prof->leaveSection("dat_size_check");
                            return $this->downloadDat2ch(0); //dat�T�C�Y�͕s���B�S����蒼���B
                        
                        // �T�C�Y�������Ȃ炻�̂܂�
                        } elseif ($this->onbytes == $this->length) {
                            fclose($fp);
                            $this->isonline = true;
                            $debug && $prof->leaveSection("dat_size_check");
                            return true;
                        }
                    }
                    $debug && $prof->leaveSection("dat_size_check");
                
                // �X���b�h���Ȃ��Ɣ��f
                } else {
                    fclose ($fp);
                    $this->downloadDat2chNotFound();
                    return false;
                }
                
            } else {
                $l = fgets($fp, 32800);
                if (preg_match("/^HTTP\/1\.\d (\d+) (.+)\r\n/", $l, $matches)) { // ex) HTTP/1.1 304 Not Modified
                    $code = $matches[1];
                    
                    if ($code=="200" || $code=="206") { // Partial Content
                        ;
                    } elseif ($code == "304") { // Not Modified
                        fclose($fp);
                        $this->isonline = true;
                        return "304 Not Modified";
                    } elseif ($code == "416") { // Requested Range Not Satisfiable                
                        //echo "���ځ[�񌟏o";
                        fclose($fp);
                        unset($this->onbytes);
                        unset($this->modified);
                        return $this->downloadDat2ch(0); // ���ځ[�񌟏o�B�S����蒼���B
                    } else {
                        fclose($fp);
                        $this->downloadDat2chNotFound();
                        return false;
                    }
                }
                
                if ($zero_read) {
                    if (preg_match("/^Content-Length: ([0-9]+)/", $l, $matches)) {
                        $this->onbytes = $matches[1];
                    }
                } else {

                    if (preg_match("/^Content-Range: bytes ([^\/]+)\/([0-9]+)/", $l, $matches)) {
                        $this->onbytes = $matches[2];
                    }
                    
                }
                    
                if (preg_match("/^Last-Modified: (.+)\r\n/", $l, $matches)) {
                    //echo $matches[1]."<br>"; //debug
                    $this->modified = $matches[1];
            
                } elseif ($l == "\r\n") {
                    $start_here = true;
                }
            }    
        }
        fclose($fp);
        $this->isonline = true;
        return true;
    }
    
    /**
     * 2ch DAT���_�E�����[�h�ł��Ȃ������Ƃ��ɌĂяo�����
     *
     * @access protected
     */
    function downloadDat2chNotFound()
    {
        // 2ch, bbspink �Ȃ�read.cgi�Ŋm�F
        if (P2Util::isHost2chs($this->host)) {
            $this->getdat_error_msg_ht .= $this->get2chDatError();
        }
        $this->diedat = true;
        return false;
    }
    
    /**
     * 2ch���p DAT���_�E�����[�h����
     */
    function downloadDat2chMaru()
    {
        global $_conf, $uaMona, $SID2ch, $_info_msg_ht;

        if (!($this->host && $this->bbs && $this->key)) {return false;}
        
        unset($datgz_attayo, $start_here, $isGzip, $done_gunzip, $marudatlines, $code);
        
        $method = "GET";
        $p2ua = $uaMona." (".$_conf['p2name']."/".$_conf['p2version'].")";
        
        //  GET /test/offlaw.cgi?bbs=��&key=�X���b�h�ԍ�&sid=�Z�b�V����ID HTTP/1.1
        $SID2ch = urlencode($SID2ch);
        $url = "http://" . $this->host . "/test/offlaw.cgi/{$this->bbs}/{$this->key}/?raw=0.0&sid={$SID2ch}";

        $purl = parse_url($url); // URL����
        if (isset($purl['query'])) { // �N�G���[
            $purl['query'] = "?".$purl['query'];
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
            $send_path = $purl['path'].$purl['query'];
        }
        
        if (!$send_port){$send_port = 80;}//�f�t�H���g��80

        $request = $method." ".$send_path." HTTP/1.0\r\n";
        $request .= "Host: ".$purl['host']."\r\n";
        $request .= "Accept-Encoding: gzip, deflate\r\n";
        //$request .= "Accept-Language: ja, en\r\n";
        $request .= "User-Agent: ".$p2ua."\r\n";
        //$request .= "X-2ch-UA: ".$_conf['p2name']."/".$_conf['p2version']."\r\n";
        //$request .= "Range: bytes={$from_bytes}-\r\n";
        $request .= "Connection: Close\r\n";
        if($modified){$request .= "If-Modified-Since: $modified\r\n";}
        $request .= "\r\n";
        
        /* WEB�T�[�o�֐ڑ� */
        $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
        if (!$fp) {
            $url_t = P2Util::throughIme($url);
            $_info_msg_ht .= "<p>�T�[�o�ڑ��G���[: {$errstr} ({$errno})<br>p2 info - <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$url}</a> �ɐڑ��ł��܂���ł����B</p>";
            $this->diedat = true;
            return false;
        }
        
        fputs($fp, $request);
        $body = "";
        while (!feof($fp)) {

            if ($start_here) {

                if ($code == "200") {

                    while (!feof($fp)) {
                        $body .= fread($fp, 4096);
                    }

                    
                    if ($isGzip) {
                        $gztempfile = $this->keydat.".gz";
                        FileCtl::mkdir_for($gztempfile);
                        $ftemp = fopen($gztempfile, "wb") or die("Error: {$gztempfile} ���X�V�ł��܂���ł���");
                        @flock($ftemp, LOCK_EX);
                        fwrite($ftemp, $body);
                        @flock($ftemp, LOCK_UN);
                        fclose ($ftemp);
                        if (extension_loaded('zlib')) {
                            $body = FileCtl::get_gzfile_contents($gztempfile);
                        } else {
                            // ���ɑ��݂���Ȃ�ꎞ�o�b�N�A�b�v�ޔ�
                            if (file_exists($this->keydat)) {
                                if (file_exists($this->keydat.".bak")) {
                                    unlink($this->keydat.".bak");
                                }
                                rename($this->keydat, $this->keydat.".bak");
                            }
                            $rcode = 1;
                            system("gzip -d $gztempfile", $rcode); // ��
                            if ($rcode != 0) {
                                if (file_exists($this->keydat.".bak")) {
                                    if (file_exists($this->keydat)) {
                                        unlink($this->keydat);
                                    }
                                    rename($this->keydat.".bak", $this->keydat); // ���s�Ȃ�o�b�N�A�b�v�߂�
                                }
                                $this->getdat_error_msg_ht .= "<p>p2 info - 2�����˂�ߋ����O�q�ɂ���̃X���b�h��荞�݂́APHP��<a href=\"http://www.php.net/manual/ja/ref.zlib.php\">zlib�g�����W���[��</a>���Ȃ����Asystem��gzip�R�}���h���g�p�\�łȂ���΂ł��܂���B</p>";
                                // gztemp�t�@�C�����̂Ă�
                                if (file_exists($gztempfile)) { unlink($gztempfile); }
                                $this->diedat = true;
                                return false;
                            } else {
                                if (file_exists($this->keydat.".bak")) { unlink($this->keydat.".bak"); }
                                $done_gunzip = true;
                            }

                        }
                        // temp�t�@�C�����̂Ă�
                        if (file_exists($gztempfile)) { unlink($gztempfile); }
                    }
                    
                    if (!$done_gunzip) {
                        FileCtl::make_datafile($this->keydat, $_conf['dat_perm']);
                        $fdat = fopen($this->keydat, "wb");
                        @flock($fdat, LOCK_EX);
                        fwrite($fdat, $body);
                        @flock($fdat, LOCK_UN);
                        fclose ($fdat);
                    }
                    
                    // �N���[�j���O =====
                    $marudatlines = @file($this->keydat);
                    if ($marudatlines) {
                        $firstline = array_shift($marudatlines);
                        if (!strstr($firstline, "+OK")) { // �`�����N�Ƃ�
                            $secondline = array_shift($marudatlines);
                        }
                        FileCtl::make_datafile($this->keydat, $_conf['dat_perm']);
                        $fdat = fopen($this->keydat, "wb");
                        @flock($fdat, LOCK_EX);
                        foreach ($marudatlines as $aline) {
                            if ($chunked) { // �`�����N�G���R�[�f�B���O���~�����Ƃ���(HTTP 1.0�ł��̂�)
                                fwrite($fdat, $aline);
                            } else {
                                fwrite($fdat, $aline);
                            }
                        }
                        @flock($fdat, LOCK_UN);
                        fclose ($fdat);
                    }
                    
                // dat.gz�͂Ȃ������Ɣ��f
                } else {
                    fclose($fp);
                    return $this->downloadDat2chMaruNotFound();
                }
                
            } else {
                $l = fgets($fp,128000);
                //echo $l."<br>";// for debug
                if (preg_match("/^HTTP\/1\.\d (\d+) (.+)\r\n/", $l, $matches)) { // ex) HTTP/1.1 304 Not Modified
                    $code = $matches[1];
                    
                    if ($code == "200") {
                        ;
                    } elseif ($code == "304") {
                        fclose($fp);
                        $this->isonline = true;
                        return "304 Not Modified";
                    } else {
                        fclose($fp);
                        return $this->downloadDat2chMaruNotFound();
                    }
                
                } elseif (preg_match("/^Content-Encoding: (x-)?gzip/", $l, $matches)) {
                    $isGzip = true;
                } elseif (preg_match("/^Last-Modified: (.+)\r\n/", $l, $matches)) {
                    $lastmodified = $matches[1];
                } elseif (preg_match("/^Content-Length: ([0-9]+)/", $l, $matches)) {
                    $onbytes = $matches[1];
                } elseif (preg_match("/^Transfer-Encoding: (.+)\r\n/", $l, $matches)) { // Transfer-Encoding: chunked
                    $t_enco = $matches[1];
                    if ($t_enco == "chunked") {
                        $chunked = true;
                    }
                } elseif ($l == "\r\n") {
                    $start_here = true;
                }
            }
            
        }
        fclose ($fp);
        $this->isonline = true;
        return true;
    }
    
    /**
     * ��ID�ł̎擾���ł��Ȃ������Ƃ��ɌĂяo�����
     */
    function downloadDat2chMaruNotFound()
    {
        if (empty($_REQUEST['relogin2ch'])) {
            $_REQUEST['relogin2ch'] = true;
            return $this->downloadDat();
        } else {
            $remarutori_ht = "<a href=\"{$_conf['read_php']}?host={$this->host}&amp;bbs={$this->bbs}&amp;key={$this->key}&amp;ls={$this->ls}&amp;maru=true&amp;relogin2ch=true\">�Ď擾�����݂�</a>";
            $this->getdat_error_msg_ht = "<p>p2 info - ��ID�ł̃X���b�h�擾�Ɏ��s���܂����B[{$remarutori_ht}]</p>";
            $this->diedat = true;
            return false;
        }
    }
    
    /**
     * 2ch�̉ߋ����O�q�ɂ���dat.gz���_�E�����[�h���𓀂���
     */
    function downloadDat2chKako($uri, $ext)
    {
        global $_conf, $_info_msg_ht;

        $url = $uri.$ext;
    
        $method = "GET";
        if (!$httpua) {
            $httpua = "Monazilla/1.00 (".$_conf['p2name']."/".$_conf['p2version'].")";
        }
        
        $purl = parse_url($url); // URL����
        if (isset($purl['query'])) { // �N�G���[
            $purl['query'] = "?".$purl['query'];
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
            $send_path = $purl['path'].$purl['query'];
        }
        if (!$send_port) {$send_port = 80;}//�f�t�H���g��80
    
        $request = $method." ".$send_path." HTTP/1.0\r\n";
        $request .= "Host: ".$purl['host']."\r\n";
        $request .= "User-Agent: ".$httpua."\r\n";
        $request .= "Connection: Close\r\n";
        //$request .= "Accept-Encoding: gzip\r\n";
        if ($modified) {
            $request .= "If-Modified-Since: $modified\r\n";
        }
        $request .= "\r\n";
    
        /* WEB�T�[�o�֐ڑ� */
        $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
        if (!$fp) {
            $url_t = P2Util::throughIme($url);
            echo "<p>�T�[�o�ڑ��G���[: $errstr ($errno)<br>p2 info - <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>$url</a> �ɐڑ��ł��܂���ł����B</p>";
            $this->diedat = true;
            return false;
        }

        fputs($fp, $request);
        $body = "";
        while (!feof($fp)) {
        
            if ($start_here) {
            
                if ($code == "200") {
                    
                    while (!feof($fp)) {
                        $body .= fread($fp, 4096);
                    }
                    
                    if ($isGzip) {
                        $gztempfile = $this->keydat.".gz";
                        FileCtl::mkdir_for($gztempfile);
                        $ftemp = fopen($gztempfile, "wb") or die("Error: $gztempfile ���X�V�ł��܂���ł���");
                        @flock($ftemp, LOCK_EX);
                        fwrite($ftemp, $body);
                        @flock($ftemp, LOCK_UN);
                        fclose($ftemp);
                        if (extension_loaded('zlib')) {
                            $body = FileCtl::get_gzfile_contents($gztempfile);
                        } else {
                            // ���ɑ��݂���Ȃ�ꎞ�o�b�N�A�b�v�ޔ�
                            if (file_exists($this->keydat)) {
                                if (file_exists($this->keydat.".bak")) { unlink($this->keydat.".bak"); }
                                rename($this->keydat, $this->keydat.".bak");
                            }
                            $rcode = 1;
                            system("gzip -d $gztempfile", $rcode); // ��
                            if ($rcode != 0) {
                                if (file_exists($this->keydat.".bak")) {
                                    if (file_exists($this->keydat)) {
                                        unlink($this->keydat);
                                    }
                                    // ���s�Ȃ�o�b�N�A�b�v�߂�
                                    rename($this->keydat.".bak", $this->keydat);
                                }
                                $this->getdat_error_msg_ht = "<p>p2 info - 2�����˂�ߋ����O�q�ɂ���̃X���b�h��荞�݂́APHP��<a href=\"http://www.php.net/manual/ja/ref.zlib.php\">zlib�g�����W���[��</a>���Ȃ����Asystem��gzip�R�}���h���g�p�\�łȂ���΂ł��܂���B</p>";
                                // gztemp�t�@�C�����̂Ă�
                                if (file_exists($gztempfile)) { unlink($gztempfile); }
                                $this->diedat = true;
                                return false;
                            } else {
                                if (file_exists($this->keydat.".bak")) { unlink($this->keydat.".bak"); }
                                $done_gunzip = true;
                            }

                        }
                        if (file_exists($gztempfile)) { unlink($gztempfile); } // temp�t�@�C�����̂Ă�
                    }

                    if (!$done_gunzip) {
                        FileCtl::make_datafile($this->keydat, $_conf['dat_perm']);
                        $fdat = fopen($this->keydat, "wb");
                        @flock($fdat, LOCK_EX);
                        fwrite($fdat, $body);
                        @flock($fdat, LOCK_UN);
                        fclose($fdat);
                    }
                    
                } else { // �Ȃ������Ɣ��f
                    fclose($fp);
                    return $this->downloadDat2chKakoNotFound($uri, $ext);
                
                }

            } else {
                $l = fgets($fp,128000);
                if (preg_match("/^HTTP\/1\.\d (\d+) (.+)\r\n/", $l, $matches)) { // ex) HTTP/1.1 304 Not Modified
                    $code = $matches[1];
                    
                    if ($code == "200") {
                        ;
                    } elseif ($code == "304") {
                        fclose($fp);
                        $this->isonline = true;
                        return "304 Not Modified";
                    } else {
                        fclose($fp);
                        return $this->downloadDat2chKakoNotFound($uri, $ext);
                    }

                } elseif (preg_match("/^Content-Encoding: (x-)?gzip/", $l, $matches)) {
                    $isGzip = true;
                } elseif (preg_match("/^Last-Modified: (.+)\r\n/", $l, $matches)) {
                    $lastmodified = $matches[1];
                } elseif (preg_match("/^Content-Length: ([0-9]+)/", $l, $matches)) {
                    $onbytes = $matches[1];
                } elseif ($l == "\r\n") {
                    $start_here = true;
                }
            }
            
        }
        fclose($fp);
        $this->isonline = true;
        return true;
    }
    
    /**
     * �ߋ����O���擾�ł��Ȃ������Ƃ��ɌĂяo�����
     *
     * @private
     */
    function downloadDat2chKakoNotFound($uri, $ext)
    {
        global $_conf;
                    
        if ($ext == ".dat.gz") {
            //.dat.gz���Ȃ�������.dat�ł�����x
            return $this->downloadDat2chKako($uri, ".dat");
        }
        if ($_GET['kakolog']) {
            $kakolog_ht = "<p><a href=\"{$_GET['kakolog']}.html\"{$_conf['bbs_win_target_at']}>{$_GET['kakolog']}.html</a></p>";
        }
        $this->getdat_error_msg_ht = "<p>p2 info - 2�����˂�ߋ����O�q�ɂ���̃X���b�h��荞�݂Ɏ��s���܂����B</p>";
        $this->getdat_error_msg_ht .= $kakolog_ht;
        $this->diedat = true;
        return false;

    }
    
    /**
     * �� 2ch��dat���擾�ł��Ȃ�����������Ԃ�
     *
     * @private
     * @return string �G���[���b�Z�[�W�i�������킩��Ȃ��ꍇ�͋�ŕԂ��j
     */
    function get2chDatError()
    {
        global $_conf, $_info_msg_ht;
    
        $read_url = "http://{$this->host}/test/read.cgi/{$this->bbs}/{$this->key}";
        
        // {{{ �� read.cgi ����HTML���擾
        $read_response_html = "";
        include_once './wap.class.php';
        $wap_ua =& new UserAgent();
        $wap_ua->setAgent($_conf['p2name']."/".$_conf['p2version']); // �����́A"Monazilla/" �������NG
        $wap_ua->setTimeout($_conf['fsockopen_time_limit']);
        $wap_req =& new Request();
        $wap_req->setUrl($read_url);
        if ($_conf['proxy_use']) {
            $wap_req->setProxy($_conf['proxy_host'], $_conf['proxy_port']);
        }
        $wap_res = $wap_ua->request($wap_req);
        
        if ($wap_res->is_error()) {
            $url_t = P2Util::throughIme($wap_req->url);
            $_info_msg_ht .= "<div>Error: {$wap_res->code} {$wap_res->message}<br>";
            $_info_msg_ht .= "p2 info: <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$wap_req->url}</a> �ɐڑ��ł��܂���ł����B</div>";
        } else {
            $read_response_html = $wap_res->content;
        }
        unset($wap_ua, $wap_req, $wap_res);
        // }}}

        // ���擾����HTML�i$read_response_html�j����͂��āA������������
        
        $dat_response_status = "";
        $dat_response_msg = "";

        $kakosoko_match = "/���̃X���b�h�͉ߋ����O�q�ɂɊi.{1,2}����Ă��܂�/";
        
        $naidesu_match = "/<title>����Ȕ�or�X���b�h�Ȃ��ł��B<\/title>/";
        $error3939_match = "{<title>�Q�����˂� error 3939</title>}";    // �ߋ����O�q�ɂ�html���̎��i���ɂ����邩���A�悭�m��Ȃ��j
        
        //<a href="http://qb5.2ch.net/sec2chd/kako/1091/10916/1091634596.html">
        //<a href="../../../../mac/kako/1004/10046/1004680972.html">
        //$kakohtml_match = "{<a href=\"\.\./\.\./\.\./\.\./([^/]+/kako/\d+(/\d+)?/(\d+)).html\">}";
        $kakohtml_match = "{/([^/]+/kako/\d+(/\d+)?/(\d+)).html\">}";
        $waithtml_match = "/html�������̂�҂��Ă���悤�ł��B/";
        
        //
        // <title>�����̃X���b�h�͉ߋ����O�q�ɂ�
        //
        if (preg_match($kakosoko_match, $read_response_html, $matches)) {
            $dat_response_status = "���̃X���b�h�͉ߋ����O�q�ɂɊi�[����Ă��܂��B";
            //if (file_exists($_conf['idpw2ch_php']) || file_exists($_conf['sid2ch_php'])) {
                $marutori_ht = "<a href=\"{$_conf['read_php']}?host={$this->host}&amp;bbs={$this->bbs}&amp;key={$this->key}&amp;ls={$this->ls}&amp;maru=true\">��ID��p2�Ɏ�荞��</a>";
            //} else {
            //    $marutori_ht = "<a href=\"login2ch.php\" target=\"subject\">��ID���O�C��</a>";
            //}
            $dat_response_msg = "<p>2ch info - ���̃X���b�h�͉ߋ����O�q�ɂɊi�[����Ă��܂��B [{$marutori_ht}]</p>";
        
        //    
        // <title>������Ȕ�or�X���b�h�Ȃ��ł��Bor error 3939
        //
        } elseif (preg_match($naidesu_match, $read_response_html, $matches) || preg_match($error3939_match, $read_response_html, $matches)) {
        
            if (preg_match($kakohtml_match, $read_response_html, $matches)) {
                $dat_response_status = "����! �ߋ����O�q�ɂŁAhtml�����ꂽ�X���b�h�𔭌����܂����B";
                $kakolog_uri = "http://{$this->host}/{$matches[1]}";
                $kakolog_url_en = urlencode($kakolog_uri);
                $read_kako_url = "{$_conf['read_php']}?host={$this->host}&amp;bbs={$this->bbs}&amp;key={$this->key}&amp;ls={$this->ls}&amp;kakolog={$kakolog_url_en}&amp;kakoget=1";
                $dat_response_msg = "<p>2ch info - ����! �ߋ����O�q�ɂŁA<a href=\"{$kakolog_uri}.html\"{$_conf['bbs_win_target_at']}>�X���b�h {$matches[3]}.html</a> �𔭌����܂����B [<a href=\"{$read_kako_url}\">p2�Ɏ�荞��œǂ�</a>]</p>";
                
            } elseif (preg_match($waithtml_match, $read_response_html, $matches)) {
                $dat_response_status = "����! �X���b�h��html�������̂�҂��Ă���悤�ł��B";
                $marutori_ht = "<a href=\"{$_conf['read_php']}?host={$this->host}&amp;bbs={$this->bbs}&amp;key={$this->key}&amp;ls={$this->ls}&amp;maru=true\">��ID��p2�Ɏ�荞��</a>";
                $dat_response_msg = "<p>2ch info - ����! �X���b�h��html�������̂�҂��Ă���悤�ł��B [{$marutori_ht}]</p>";
                
            } else {
                if ($_GET['kakolog']) {
                    $dat_response_status = "����Ȕ�or�X���b�h�Ȃ��ł��B";
                    $kako_html_url = urldecode($_GET['kakolog']).".html";
                    $read_kako_url = "{$_conf['read_php']}?host={$this->host}&amp;bbs={$this->bbs}&amp;key={$this->key}&amp;ls={$this->ls}&amp;kakolog={$_GET['kakolog']}&amp;kakoget=1";
                    $dat_response_msg = "<p>2ch info - ����Ȕ�or�X���b�h�Ȃ��ł��B</p>";
                    $dat_response_msg .= "<p><a href=\"{$kako_html_url}\"{$_conf['bbs_win_target_at']}>{$kako_html_url}</a> [<a href=\"{$read_kako_url}\">p2�Ƀ��O����荞��œǂ�</a>]</p>";
                } else {                
                    $dat_response_status = "����Ȕ�or�X���b�h�Ȃ��ł��B";
                    $dat_response_msg = "<p>2ch info - ����Ȕ�or�X���b�h�Ȃ��ł��B</p>";
                }
            }
            
        // ������������Ȃ��ꍇ�ł��A�Ƃ肠�����ߋ����O��荞�݂̃����N���ێ����Ă���B�Ǝv���B���܂�o���Ă��Ȃ� 2005/2/27 aki
        } elseif ($_GET['kakolog']) {
            $dat_response_status = "";
            $kako_html_url = urldecode($_GET['kakolog']).".html";
            $read_kako_url = "{$_conf['read_php']}?host={$this->host}&amp;bbs={$this->bbs}&amp;key={$this->key}&amp;ls={$this->ls}&amp;kakolog={$_GET['kakolog']}&amp;kakoget=1";
            $dat_response_msg = "<p><a href=\"{$kako_html_url}\"{$_conf['bbs_win_target_at']}>{$kako_html_url}</a> [<a href=\"{$read_kako_url}\">p2�Ƀ��O����荞��œǂ�</a>]</p>";
        
        }
        
        return $dat_response_msg;
    }
    
    /**
     * >>1�݂̂��v���r���[����
     */
    function previewOne()
    {
        global $_conf, $ptitle_ht, $_info_msg_ht;

        if (!($this->host && $this->bbs && $this->key)) { return false; }
        
        // ���[�J��dat����擾
        if (is_readable($this->keydat)) {
            $fd = fopen($this->keydat, "rb");
            $first_line = fgets($fd, 32800);
            fclose ($fd);
            
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
        }
        
        // ���[�J��dat�Ȃ���΃I�����C������
        if (!$first_line) {
        
            $method = "GET";
            $url = "http://" . $this->host . "/{$this->bbs}/dat/{$this->key}.dat";
            
            $purl = parse_url($url); // URL����
            if (isset($purl['query'])) { // �N�G���[
                $purl['query'] = "?".$purl['query'];
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
                $send_path = $purl['path'].$purl['query'];
            }
            
            if (!$send_port) {$send_port = 80;} // �f�t�H���g��80
    
            $request = $method." ".$send_path." HTTP/1.0\r\n";
            $request .= "Host: ".$purl['host']."\r\n";
            $request .= "User-Agent: Monazilla/1.00 (".$_conf['p2name']."/".$_conf['p2version'].")"."\r\n";
            // $request .= "Range: bytes={$from_bytes}-\r\n";
    
            // Basic�F�ؗp�̃w�b�_
            if (isset($purl['user']) && isset($purl['pass'])) {
                $request .= "Authorization: Basic ".base64_encode($purl['user'].":".$purl['pass'])."\r\n";
            }
            
            $request .= "Connection: Close\r\n";
            $request .= "\r\n";
            
            // WEB�T�[�o�֐ڑ�
            $fp = fsockopen($send_host, $send_port, $errno, $errstr, $_conf['fsockopen_time_limit']);
            if (!$fp) {
                $url_t = P2Util::throughIme($url);
                $_info_msg_ht .= "<p>�T�[�o�ڑ��G���[: $errstr ($errno)<br>p2 info - <a href=\"{$url_t}\"{$_conf['ext_win_target_at']}>{$url}</a> �ɐڑ��ł��܂���ł����B</p>";
                $this->diedat = true;
                return false;
            }
            
            fputs($fp, $request);
        
            while (!feof($fp)) {

                if ($start_here) {
                
                    if ($code == "200") {
                        $first_line = fgets($fp, 32800);
                        break;
                    } else {
                        fclose($fp);
                        return $this->previewOneNotFound();
                    }
                } else {
                    $l = fgets($fp,32800);
                    //echo $l."<br>";// for debug
                    if (preg_match("/^HTTP\/1\.\d (\d+) (.+)\r\n/", $l, $matches)) { // ex) HTTP/1.1 304 Not Modified
                        $code = $matches[1];
                        
                        if ($code == "200") {
                            ;
                        } else {
                            fclose($fp);
                            return $this->previewOneNotFound();
                        }

                    } elseif (preg_match("/^Content-Length: ([0-9]+)/", $l, $matches)) {
                        $onbytes = $matches[1];
                    } elseif ($l == "\r\n") {
                        $start_here = true;
                    }
                }
                
            }
            fclose($fp);
            
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
            
            $this->onthefly = true;
            
        } else {
            // �֋X��
            if (!$this->readnum) {
                $this->readnum = 1;
            }
        }

        $this->onthefly && $body .= "<div><span class=\"onthefly\">on the fly</span></div>";
        $body .= "<dl>";
        
        include_once './showthread.class.php'; // HTML�\���N���X
        include_once './showthreadpc.class.php'; // HTML�\���N���X
        $aShowThread =& new ShowThreadPc($this);
        $body .= $aShowThread->transRes($first_line, 1); // 1��\��
        unset($aShowThread);
        
        $body .= "</dl>\n";
        return $body;
    }
    
    /**
     * >>1���v���r���[�ŃX���b�h�f�[�^��������Ȃ������Ƃ��ɌĂяo�����
     */
    function previewOneNotFound()
    {
        // 2ch, bbspink �Ȃ�read.cgi�Ŋm�F
        if (P2Util::isHost2chs($this->host)) {
            $this->getdat_error_msg_ht = $this->get2chDatError();
        }
        $this->diedat = true;
        return false;
    }
    
    /**
     * $ls�𕪉�����start��to��nofirst�����߂�
     */
    function lsToPoint()
    {
        global $_conf;

        $to = false;
        
        // n���܂�ł���ꍇ�́A>>1��\�����Ȃ��i$nofirst�j
        if (strstr($this->ls, 'n')) {
            $nofirst = true;
            $this->ls = preg_replace("/n/", "", $this->ls);
        }

        // �͈͎w��ŕ���
        $n = explode('-', $this->ls);
        // �͈͎w�肪�Ȃ����
        if (sizeof($n) == 1) {
            // l�w�肪�����
            if (substr($n[0], 0, 1) == "l") {
                $ln = intval(substr($n[0], 1));
                if ($_conf['ktai']) {
                    if ($ln > $_conf['k_rnum_range']) {
                        $ln = $_conf['k_rnum_range'];
                    }
                }
                $start = $this->rescount - $ln;
                if ($start < 1) {
                    $start = 1;
                }
                $to = $this->rescount;
            // all�w��Ȃ�
            } elseif ($this->ls == "all") {
                $start = 1;
                $to = $this->rescount;
            
            } else {
                // ���X�Ԏw��
                if (intval($this->ls) > 0) {
                    $this->ls = intval($this->ls);
                    $start = $this->ls;
                    $to = $this->ls;
                    $nofirst = true;
                // �w�肪�Ȃ� or �s���ȏꍇ�́Aall�Ɠ����\���ɂ���
                } else {
                    $start = 1;
                    $to = $this->rescount;
                }
            }
        // �͈͎w�肪�����
        } else {
            if (!$start = intval($n[0])) {
                $start = 1;
            }
            if (!$to = intval($n[1])) {
                $to = $this->rescount;
            }
        }
        
        // �܂Ƃߓǂ݂̕\��������
        if (isset($GLOBALS['rnum_all_range']) and $GLOBALS['rnum_all_range'] > 0) {
            if ($start + $GLOBALS['rnum_all_range'] <= $to) {
                $to = $start + $GLOBALS['rnum_all_range'];
            }
            $GLOBALS['rnum_all_range'] = $GLOBALS['rnum_all_range'] - ($to - $start);
            $all_end = true;
        
        } else {
            // �g�їp�̕\��������
            if ($_conf['ktai']) {
                if ($start + $_conf['k_rnum_range'] <= $to) {
                    $to = $start + $_conf['k_rnum_range'];
                }
            }
        }

        $this->resrange = array('start'=>$start,'to'=>$to,'nofirst'=>$nofirst);
        return $this->resrange;
    }
    
    /**
     * Dat��ǂݍ���
     * $this->datlines �� set ����
     */
    function readDat()
    {
        global $_conf;
        
        if (file_exists($this->keydat)) {
            if ($this->datlines = @file($this->keydat)) {
            
                // be.2ch.net �Ȃ�EUC��SJIS�ϊ�
                // �O�̂���SJIS��UTF-8�������R�[�h����̌��ɓ���Ă���
                // �E�E�E���A�������������^�C�g���̃X���b�h�Ō딻�肪�������̂ŁA�w�肵�Ă���
                if (P2Util::isHostBe2chNet($this->host)) {
                    //mb_convert_variables('SJIS-win', 'eucJP-win,SJIS-win,UTF-8', $this->datlines);
                    mb_convert_variables('SJIS-win', 'eucJP-win', $this->datlines);
                }
                
                if (!strstr($this->datlines[0], "<>")) {
                    $this->dat_type = "2ch_old";
                }
            }
        } else {
            return false;
        }
        $this->rescount = sizeof($this->datlines);
        
        if ($_conf['flex_idpopup']) {
            $this->setIdCount($this->datlines);
        }
        
        return $this->datlines;
    }

    /**
     * ��̃X�����ł�ID�o�������Z�b�g����
     */
    function setIdCount($lines)
    {
        if ($lines) {
            foreach ($lines as $line) {
                $lar = explode('<>', $line);
                if (preg_match('|ID: ?([0-9a-zA-Z/.+]{8,10})|', $lar[2], $matches)) {
                    $id = $matches[1];
                    $this->idcount[$id]++;
                }
            }
        }
        return;
    }
    

    /**
     * datline��explode����
     */
    function explodeDatLine($aline)
    {
        $aline = rtrim($aline);

        if ($this->dat_type == "2ch_old") {
            $parts = explode(',', $aline);
        } else {
            $parts = explode('<>', $aline);
        }
        
        return $parts;
    }

}

?>
