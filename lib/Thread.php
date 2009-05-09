<?php
require_once P2_LIB_DIR . '/FileCtl.php';

/**
 * p2 - �X���b�h�N���X
 */
class Thread
{
    var $ttitle;    // �X���^�C�g�� // idxline[0] // < �� &lt; �������肷��
    var $key;       // �X���b�hID // idxline[1]
    var $length;    // local Dat Bytes(int) // idxline[2]
    var $gotnum;    //�i�l�ɂƂ��Ắj�������X�� // idxline[3]
    var $rescount;  // �X���b�h�̑����X���i���擾�����܂ށj
    var $modified;  // dat��Last-Modified // idxline[4]
    var $readnum;   // ���ǃ��X�� // idxline[5] // MacMoe�ł̓��X�\���ʒu�������Ǝv���ilast res�j
    var $fav;       // ���C�ɓ���(bool�I��) // idxline[6] favlist.idx���Q��
    // name         // idxline[7] �����ł͗��p�����i�����ŗ��p�j
    // mail         // idxline[8] �����ł͗��p�����i�����ŗ��p�j
    // var $newline; // ���̐V�K�擾���X�ԍ� // idxline[9] �p�~�\��B����݊��̂��ߎc���Ă͂���B
    
    // ��host�Ƃ͂������̂́A2ch�O�̏ꍇ�́Ahost�ȉ��̃f�B���N�g���܂Ŋ܂܂�Ă����肷��B
    var $host;      // ex)pc.2ch.net // idxline[10]
    var $bbs;       // ex)mac // idxline[11]
    var $itaj;      // �� ex)�V�Emac
    
    var $datochiok; // DAT�����擾�����������TRUE(1) // idxline[12]
    
    var $torder;    // �X���b�h�V�������ԍ�
    var $unum;      // ���ǁi�V�����X�j��
    
    var $keyidx;    // idx�t�@�C���p�X
    var $keydat;    // ���[�J��dat�t�@�C���p�X
    
    var $isonline;  // �T�[�o�ɂ����true�Bsubject.txt��dat�擾���Ɋm�F���ăZ�b�g�����B
    var $new;       // �V�K�X���Ȃ�true
    
    var $ttitle_hc; // < �� &lt; �ł������肷��̂ŁA�f�R�[�h�����X���^�C�g��
    var $ttitle_hs; // HTML�\���p�ɁAhtmlspecialchars()���ꂽ�X���^�C�g���B���̕ϐ��͂Ȃ��������Bhs($aThread->ttitle_hc)
    var $ttitle_ht; // �X���^�C�g���\���pHTML�R�[�h�B�t�B���^�����O��������Ă����������B
    
    var $dayres;    // ���������̃��X���B�����B
    
    var $dat_type;  // dat�̌`���i2ch�̋��`��dat�i,��؂�j�Ȃ�"2ch_old"�j

    var $ls = '';   // �\�����X�ԍ��̎w��
    
    var $similarity; // �^�C�g���̗ގ���
    
    /**
     * @constructor
     */
    function Thread()
    {
    }

    /**
     * ttitle���Z�b�g����i���ł�ttitle_hc, ttitle_hs, ttitle_ht���j
     *
     * @access  public
     * @return  void
     */
    function setTtitle($ttitle)
    {
        $this->ttitle = $ttitle;
        // < �� &lt; �ł������肷��̂ŁA�܂��f�R�[�h�������̂�

        // 2007/12/21 $this->ttitle ���㏑�����Ă��܂��i$this->ttitle_hc �͂Ȃ����Ă��܂������j
        // ������͍��̂Ƃ���_���B�����Ȃ�<>��؂�� $this->ttitle �𒼐ڋL�^���Ă���ӏ�������B
        $this->ttitle_hc = P2Util::htmlEntityDecodeLite($this->ttitle);
        
        // HTML�\���p�� htmlspecialchars() ��������
        $this->ttitle_hs = htmlspecialchars($this->ttitle_hc, ENT_QUOTES);
        
        $this->ttitle_ht = $this->ttitle_hs;
    }

    /**
     * fav, recent�p�̊g��idx���X�g���烉�C���f�[�^���擾�Z�b�g����
     *
     * @access  public
     * @return  void
     */
    function setThreadInfoFromExtIdxLine($l)
    {
        $la = explode('<>', rtrim($l));
        $this->host = $la[10];
        $this->bbs = $la[11];
        $this->key = $la[1];
        
        if (!$this->ttitle) {
            if ($la[0]) {
                $this->setTtitle(rtrim($la[0]));
            }
        }
        
        /*
        if ($la[6]) {
            $this->fav = $la[6];
        }
        */
    }

    /**
     * ThreadList�̏��ɉ����āA���C���f�[�^���擾�Z�b�g����
     *
     * @return  void
     */
    function setThreadInfoFromLineWithThreadList($l, $aThreadList, $setItaj = true)
    {
        // spmode
        if ($aThreadList->spmode) {
            switch ($aThreadList->spmode) {
                case 'recent':  // ����
                    $this->setThreadInfoFromExtIdxLine($l);
                    if ($setItaj) {
                        if (!$this->itaj = P2Util::getItaName($this->host, $this->bbs)) {
                            $this->itaj = $this->bbs;
                        }
                    }
                    break;
                
                case 'res_hist':// �������ݗ���
                    $this->setThreadInfoFromExtIdxLine($l);
                    if ($setItaj) {
                        if (!$this->itaj = P2Util::getItaName($this->host, $this->bbs)) {
                            $this->itaj = $this->bbs;
                        }
                    }
                    break;
                
                case 'fav':     // ���C��
                    $this->setThreadInfoFromExtIdxLine($l);
                    if ($setItaj) {
                        if (!$this->itaj = P2Util::getItaName($this->host, $this->bbs)) {
                            $this->itaj = $this->bbs;
                        }
                    }
                    break;
                
                case 'palace':  // �X���̓a��
                    $this->setThreadInfoFromExtIdxLine($l);
                    if ($setItaj) {
                        if (!$this->itaj = P2Util::getItaName($this->host, $this->bbs)) {
                            $this->itaj = $this->bbs;
                        }
                    }
                    break;
                    
                // read_new*�ł͕K�v�Ȃ��Ƃ���
                case 'taborn':  // �X���b�h���ځ[��
                    $la = explode('<>', $l);
                    $this->key  = $la[1];
                    $this->host = $aThreadList->host;
                    $this->bbs  = $aThreadList->bbs;
                    break;
                    
                // read_new*�ł͕K�v�Ȃ��Ƃ���
                case 'soko':    // dat�q��
                    $la = explode('<>', $l);
                    $this->key  = $la[1];
                    $this->host = $aThreadList->host;
                    $this->bbs  = $aThreadList->bbs;
                    break;
                
                case 'news':    // �j���[�X�̐���
                    $this->isonline = true;
                    $this->key = $l['key'];
                    $this->setTtitle($l['ttitle']);
                    $this->rescount = $l['rescount'];
                    $this->host = $l['host'];
                    $this->bbs  = $l['bbs'];

                    if ($setItaj) {
                        if (!$this->itaj = P2Util::getItaName($this->host, $this->bbs)) {
                            $this->itaj = $this->bbs;
                        }
                    }
                    break;
            }
        
        // subject (not spmode �܂蕁�ʂ̔�)
        } else {
            $this->setThreadInfoFromSubjectTxtLine($l);
            $this->host = $aThreadList->host;
            $this->bbs  = $aThreadList->bbs;
            // itaj �͏ȗ����Ă���
        }
    }

    /**
     * Set Path info
     *
     * @access  public
     * @return  void
     */
    function setThreadPathInfo($host, $bbs, $key)
    {
        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('setThreadPathInfo()');
        
        if (preg_match('/[<>]/', $host) || preg_match('/[<>]/', $bbs) || preg_match('/[<>]/', $key)) {
            trigger_error(__FUNCTION__, E_USER_WARNING);
            die('Error: ' . __FUNCTION__);
        }
        
        $this->host =   $host;
        $this->bbs =    $bbs;
        $this->key =    $key;
        
        $dat_host_dir = P2Util::datDirOfHost($this->host);
        $idx_host_dir = P2Util::idxDirOfHost($this->host);

        $this->keydat = $dat_host_dir . '/' . $this->bbs . '/' . $this->key . '.dat';
        $this->keyidx = $idx_host_dir . '/' . $this->bbs . '/' . $this->key . '.idx';
        
        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('setThreadPathInfo()');
    }

    /**
     * �X���b�h�������ς݂Ȃ�true��Ԃ�
     *
     * @access  public
     * @return  boolean
     */
    function isKitoku()
    {
        // if (file_exists($this->keyidx)) {
        if ($this->gotnum || $this->readnum || $this->newline > 1) {
            return true;
        }
        return false;
    }

    /**
     * �����X���b�h�f�[�^��key.idx����擾�Z�b�g����
     *
     * @access  public
     */
    function getThreadInfoFromIdx()
    {
        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('getThreadInfoFromIdx');
        
        if (!$lines = @file($this->keyidx)) {
            $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('getThreadInfoFromIdx');
            return false;
        }
        
        $key_line = rtrim($lines[0]);
        $lar = explode('<>', $key_line);
        if (!$this->ttitle) {
            if ($lar[0]) {
                $this->setTtitle(rtrim($lar[0]));
            }
        }
        
        if ($lar[5]) {
            $this->readnum = intval($lar[5]);
        
        // ����݊��[�u�i$lar[9] newline�̔p�~�j
        } elseif ($lar[9]) {
            $this->readnum = $lar[9] - 1;
        }
        
        if ($lar[3]) {
            $this->gotnum = intval($lar[3]);
        
            if ($this->rescount) {
                $this->unum = $this->rescount - $this->readnum;
                // machi bbs ��subject�̍X�V�Ƀf�B���C������悤�Ȃ̂Œ������Ă���
                if ($this->unum < 0) {
                    $this->unum = 0;
                }
            }
        } else {
            $this->gotnum = 0;
        }

        if ($lar[6]) {
            $this->fav = $lar[6];
        }
        
        if (isset($lar[12])) {
            $this->datochiok = $lar[12];
        }
        
        /*
        // ����key.idx�̂��̃J�����͎g�p���Ă��Ȃ��Bdat�T�C�Y�͒��ڃt�@�C���̑傫����ǂݎ���Ē��ׂ�
        if ($lar[2]) {
            $this->length = $lar[2];
        }
        */
        if ($lar[4]) { $this->modified = $lar[4]; }
        
        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('getThreadInfoFromIdx');
        
        return $key_line;
    }
    
    /**
     * ���[�J��DAT�̃t�@�C���T�C�Y���擾�Z�b�g����
     *
     * @access  public
     * @return  integer
     */
    function getDatBytesFromLocalDat()
    {
        clearstatcache();
        return $this->length = intval(@filesize($this->keydat));
    }
    
    /**
     * subject.txt �̈�s����X�������擾���ăZ�b�g����
     *
     * @access  public
     * @return  boolean
     */
    function setThreadInfoFromSubjectTxtLine($l)
    {
        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection(__FUNCTION__ . '()');
        
        if (preg_match('/^(\\d+)\\.(?:dat|cgi)(?:,|<>)(.+) ?(?:\\(|�i)(\\d+)(?:\\)|�j)/', $l, $matches)) {
            $this->isonline = true;
            $this->key = $matches[1];
            $this->setTtitle(rtrim($matches[2]));

            $this->rescount = $matches[3];
            if ($this->gotnum) {
                $this->unum = $this->rescount - $this->readnum;
                // machi bbs ��sage��subject�̍X�V���s���Ȃ������Ȃ̂Œ������Ă���
                if ($this->unum < 0) {
                    $this->unum = 0;
                }
            }
            
            $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection(__FUNCTION__ . '()');
            return true;
        }
        
        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection(__FUNCTION__ . '()');
        return false;
    }

    /**
     * �X���^�C�g�����擾�Z�b�g����
     *
     * @access  public
     * @return  string
     */
    function setTitleFromLocal()
    {
        if (isset($this->ttitle)) {
            return $this->ttitle;
        }
        
        $this->ttitle = null;
        
        if (!empty($this->datlines)) {
            $firstdatline = rtrim($this->datlines[0]);
            $d = $this->explodeDatLine($firstdatline);
            $this->setTtitle($d[4]);
        
        // ���[�J��dat��1�s�ڂ���擾
        } elseif (is_readable($this->keydat) and $fp = fopen($this->keydat, "rb")) {
            $l = fgets($fp, 32800);
            fclose($fp);
            $firstdatline = rtrim($l);
            if (strstr($firstdatline, "<>")) {
                $datline_sepa = "<>";
            } else {
                $datline_sepa = ",";
                $this->dat_type = "2ch_old";
            }
            $d = explode($datline_sepa, $firstdatline);
            $this->setTtitle($d[4]);
            
            // be.2ch.net �Ȃ�EUC��SJIS�ϊ�
            if (P2Util::isHostBe2chNet($this->host)) {
                $ttitle = mb_convert_encoding($this->ttitle, 'SJIS-win', 'eucJP-win');
                $this->setTtitle($ttitle);
            }
        }
        
        return $this->ttitle;
    }

    /**
     * ���X��URL��Ԃ�
     *
     * @access  public
     * @param   boolean  $original  �g�тł�2ch�̃X��URL��Ԃ�
     * @return  string  URL
     */
    function getMotoThread($original = false)
    {
        global $_conf;

        // �g�уJ�X�^�}�C�Y�w��
        if ($_conf['ktai'] && !$original && $_conf['k_motothre_external']) {
            $motothre_url = $this->compileMobile2chUri();
            
        // �܂�BBS
        } elseif (P2Util::isHostMachiBbs($this->host)) {
            // PC
            if (!$_conf['ktai'] || $original) {
                $motothre_url = sprintf(
                    'http://%s/bbs/read.cgi?BBS=%s&KEY=%s',
                    $this->host, rawurlencode($this->bbs), rawurlencode($this->key)
                );
                
            // �g��
            } else {
                $motothre_url = sprintf(
                    'http://%s/bbs/read.cgi?IMODE=TRUE&BBS=%s&KEY=%s',
                    $this->host, rawurlencode($this->bbs), rawurlencode($this->key)
                );
            }

        // �܂��т˂���
        } elseif (P2Util::isHostMachiBbsNet($this->host)) {
            $motothre_url = sprintf(
                'http://%s/test/read.cgi?bbs=%s&key=%s',
                $this->host, rawurlencode($this->bbs), rawurlencode($this->key)
            );

        // JBBS�������
        } elseif (P2Util::isHostJbbsShitaraba($this->host)) {
            $preg = '{(jbbs\\.shitaraba\\.com|jbbs\\.livedoor\\.com|jbbs\\.livedoor\\.jp)}';
            $host_bbs_cgi = preg_replace($preg, '$1/bbs/read.cgi', $this->host);
            $motothre_url = "http://{$host_bbs_cgi}/{$this->bbs}/{$this->key}/{$this->ls}";
            // $motothre_url = "http://{$this->host}/bbs/read.cgi?BBS={$this->bbs}&KEY={$this->key}";
            
        // 2ch�n
        } elseif (P2Util::isHost2chs($this->host)) {
            // PC
            if (!UA::isK() || UA::isIPhoneGroup() || $original) {
                $motothre_url = "http://{$this->host}/test/read.cgi/{$this->bbs}/{$this->key}/{$this->ls}";
                
            // �g��
            } else {
                // BBS PINK
                if (P2Util::isHostBbsPink($this->host)) {
                    // r.i�͂����g���Ă��Ȃ�
                    //$motothre_url = "http://{$this->host}/test/r.i/{$this->bbs}/{$this->key}/{$this->ls}"; 
                    $motothre_url = "http://speedo.ula.cc/test/r.so/{$this->host}/{$this->bbs}/{$this->key}/{$this->ls}?guid=ON";
                    
                // 2ch�ic.2ch�j
                } else {
                    $motothre_url = $this->compileMobile2chUri();
                }
            }
            
        // ���̑�
        } else {
            $motothre_url = "http://{$this->host}/test/read.cgi/{$this->bbs}/{$this->key}/{$this->ls}";
        }
        
        return $motothre_url;
    }

    /**
     * @access  private
     * @return  string  URI
     */
    function compileMobile2chUri()
    {
        global $_conf;
        
        if ($_conf['k_motothre_template']) {
            $template = $_conf['k_motothre_template'];
        } else {
            $template = 'http://c.2ch.net/test/-33!&mail={$mail}&FROM={$FROM}/{$bbs}/{$key}/{$ls}';
        }
        return preg_replace_callback('/{\\$([a-zA-Z0-9]+)}/', array($this, 'compileMobile2chUriCallBack'), $template);
    }

    /**
     * @access  private
     * @return  array
     */
    function getMobile2chUriValues()
    {
        // http://qb5.2ch.net/test/read.cgi/operate/1188907861/38-
        //$aas3 = $_conf['k_use_aas'] ? '3' : '';
        $aas3 = '3';
        $resv = P2Util::getDefaultResValues($this->host, $this->bbs, $this->key);
        $FROM = $resv['FROM'];
        $mail = $resv['mail'];
        
        /*
        $mail_opt = (strlen($mail) == 0) ? '' : "&mail=" . rawurlencode($mail);
        
        //$FROM = str_replace(array('?', '/'), array('�H', '�^'), $FROM);
        $FROM_opt = (strlen($FROM) == 0) ? '' : "&FROM=" . rawurlencode($FROM);
        
        //$motothre_url = "http://{$c2chHost}/test/-3{$aas3}!{$mail_opt}{$FROM_opt}/{$this->bbs}/{$this->key}/{$ls}";
        */
        
        // '?', '/' ���܂܂�Ă����c.2ch�Œʂ�Ȃ��悤���B

        //$c2chHost = 'c.2ch.net';
        
        /*
        //�@2008/02/14 �U�蕪���͕K�v�Ȃ��Ȃ����炵��
        $mobile = &Net_UserAgent_Mobile::singleton();
        if ($mobile->isDoCoMo()) {
            $c2chHost = 'c-docomo.2ch.net';
        } elseif ($mobile->isEZweb()) {
            $c2chHost = 'c-au.2ch.net';
        } else {
            $c2chHost = 'c-others.2ch.net';
        }
        */
        
        // c.2ch��l�w��ɔ�Ή��Ȃ̂ŁA�����n�Ƃ���
        if (substr($this->ls, 0, 1) == 'l') {
            $ls = 'n';
        } else {
            $ls = $this->ls;
            // c.2ch�ł�n���܂܂�Ă�����A�ŐV10���\���ƂȂ��Ă���B2ch��n��>>1��\�����Ȃ��Ƃ����w��B
            $ls = str_replace('n', '', $ls);
        }
        
        return array(
            //'c2chHost' => $c2chHost,
            'host' => $this->host,
            'bbs'  => $this->bbs,
            'key'  => $this->key,
            'ls'   => $ls,
            'aas3' => $aas3,
            'mail' => rawurlencode($mail),
            'FROM' => rawurlencode($FROM)
        );
    }
    
    /**
     * callback of compileMobile2chUri()
     *
     * @access  private
     * @return  string
     */
    function compileMobile2chUriCallBack($m)
    {
        $replaces = $this->getMobile2chUriValues();

        $key = $m[1];
        if (isset($replaces[$key])) {
            return $replaces[$key];
        }
        return $m[0];
    }
    
    /**
     * �����i���X/���j���Z�b�g����
     *
     * @access  public
     * @param   integer  $nowtime  UNIX TIMESTAMP
     * @return  boolean
     */
    function setDayRes($nowtime = null)
    {
        if (!isset($this->key) || !isset($this->rescount)) {
            return false;
        }
        
        if (!$nowtime) {
            $nowtime = time();
        }
        //if (preg_match('/^\d{9,10}$/', $this->key) {
        // 1990�N-
        if (
            631119600 < $this->key && $this->key < time() + 1000
            and $pastsc = $nowtime - $this->key
        ) {
            $this->dayres = $this->rescount / $pastsc * 60 * 60 * 24;
            return true;
        }
        
        return false;
    }

    /**
     * ���X�Ԋu�i����/���X�j���擾����
     *
     * @access  public
     * @return  string
     */
    function getTimePerRes()
    {
        $noresult_st = '-';
    
        if (!isset($this->dayres)) {
            if (!$this->setDayRes()) {
                return $noresult_st;
            }
        }
        
        if ($this->dayres <= 0) {
            return $noresult_st;
            
        } elseif ($this->dayres < 1/365) {
            $spd = 1/365 / $this->dayres;
            $spd_suffix = "�N";
        } elseif ($this->dayres < 1/30.5) {
            $spd = 1/30.5 / $this->dayres;
            $spd_suffix = "����";
        } elseif ($this->dayres < 1) {
            $spd = 1 / $this->dayres;
            $spd_suffix = "��";
        } elseif ($this->dayres < 24) {
            $spd = 24 / $this->dayres;
            $spd_suffix = "����";
        } elseif ($this->dayres < 24*60) {
            $spd = 24*60 / $this->dayres;
            $spd_suffix = "��";
        } elseif ($this->dayres < 24*60*60) {
            $spd = 24*60*60 / $this->dayres;
            $spd_suffix = "�b";
        } else {
            $spd = 1;
            $spd_suffix = "�b�ȉ�";
        }
        if ($spd > 0) {
            $spd_st = sprintf('%01.1f', round($spd, 2)) . $spd_suffix;
        } else {
            $spd_st = $noresult_st;
        }
        return $spd_st;
    }

    /**
     * �X�}�[�g�|�b�v�A�b�v���j���[�̂��߂�JavaScript�R�[�h�𐶐��\������
     *
     * @access  public
     * @return  void
     */
    function showSmartPopUpMenuJs()
    {
        global $_conf, $STYLE;

        $this->spmObjName = "aThread_{$this->bbs}_{$this->key}";
        $ttitle_en = base64_encode($this->ttitle);
        $ttitle_urlen = rawurlencode($ttitle_en);
        $nbxdom = $this->spmObjName . "_numbox.style";
        $nbxar = array("fs"=>"", "fc"=>"", "bc"=>"", "bi"=>"");
        if ($STYLE['respop_fontsize']) {
            $nbxdom_fs = "{$nbxdom}.fontSize = \"{$STYLE['respop_fontsize']}\";";
        }
        if ($STYLE['respop_color']) {
            $nbxdom_c = "{$nbxdom}.color = \"{$STYLE['respop_color']}\";";
        }
        if ($STYLE['respop_bgcolor']) {
            $nbxdom_bc = "{$nbxdom}.backgroundColor = \"{$STYLE['respop_bgcolor']}\";";
        }
        if ($STYLE['respop_background']) {
            $nbxdom_bi = "{$nbxdom}.backgroundImage = \"" . str_replace("\"", "'", $STYLE['respop_background']) . "\";";
        } else {
            $nbxdom_bi = '';
        }

        if ($_conf['flex_spm_target'] == "" || $_conf['flex_spm_target'] == "read") {
            $flex_spm_target = "_self";
        } else {
            $flex_spm_target = $_conf['flex_spm_target'];
        }

        echo <<<EOJS
<script type="text/javascript">
<!--
    // ��ȃX���b�h���{�����I�u�W�F�N�g�Ɋi�[
    var {$this->spmObjName} = new Object();
    {$this->spmObjName}.objName = "{$this->spmObjName}";
    {$this->spmObjName}.host = "{$this->host}";
    {$this->spmObjName}.bbs  = "{$this->bbs}";
    {$this->spmObjName}.key  = "{$this->key}";
    {$this->spmObjName}.rc   = "{$this->rescount}";
    {$this->spmObjName}.ttitle_en = "{$ttitle_urlen}";
    {$this->spmObjName}.spmHeader = "resnum";
    {$this->spmObjName}.spmOption = {
        'spm_confirm':0,
        'spm_kokores':{$_conf['spm_kokores']},
        'enable_bookmark':0,
        'spm_aborn':0,
        'spm_ng':0,
        'enable_am_on_spm':0,
        'enable_fl_on_spm':0
    };
    
    // �X�}�[�g�|�b�v�A�b�v���j���[����
    spmTarget = '{$flex_spm_target}';
    makeSPM({$this->spmObjName});
    // �|�b�v�A�b�v���j���[�w�b�_�̃��X�ԁiinput type="text"�j���C�����C���e�L�X�g�̂悤�Ɍ�����B
    // �u���E�U�ɂ���Ă�DOM�ŕύX�ł��Ȃ��v���p�e�B������̂Ŋ��S�ł͂Ȃ��B�i����Safari�j
    if (({$this->spmObjName}.spmHeader.indexOf("resnum") != -1) && (document.getElementById || document.all)) {
        var {$this->spmObjName}_numbox = p2GetElementById('{$this->spmObjName}_numbox');
        {$nbxdom_fs}
        {$nbxdom_c}
        {$nbxdom_bc}
        {$nbxdom_bi}
    }
//-->
</script>\n
EOJS;
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
