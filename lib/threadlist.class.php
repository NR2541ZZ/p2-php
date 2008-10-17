<?php
/**
 * p2 - ThreadList �N���X
 */
class ThreadList
{
    var $threads;   // �N���XThread�̃I�u�W�F�N�g���i�[����z��
    var $num = 0;   // �i�[���ꂽThread�I�u�W�F�N�g�̐�
    var $host;      // ex)pc.2ch.net
    var $bbs;       // ex)mac
    var $itaj;      // �� ex)�V�Emac��
    var $itaj_hs;   // HTML�\���p�ɁA���� htmlspecialchars() �������́B����͔p�~�������B
    var $spmode;    // ���ʔȊO�̃X�y�V�������[�h
    var $ptitle;    // �y�[�W�^�C�g��
    
    /**
     * @constructor
     */
    function ThreadList()
    {
    }
    
    /**
     * @access  public
     * @return  void
     */
    function setSpMode($spmode)
    {
        global $_conf;
        
        if ($spmode == "recent") {
            $this->spmode = $spmode;
            $this->ptitle = $_conf['ktai'] ? "�ŋߓǂ񂾽�" : "�ŋߓǂ񂾃X��";
            
        } elseif ($spmode == "res_hist") {
            $this->spmode = $spmode;
            $this->ptitle = "�������ݗ���";
            
        } elseif ($spmode == "fav") {
            $this->spmode = $spmode;
            $this->ptitle = $_conf['ktai'] ? "���C�ɽ�" : "���C�ɃX��";
            
        } elseif ($spmode == "taborn") {
            $this->spmode = $spmode;
            $this->ptitle = $_conf['ktai'] ? "$this->itaj (���ݒ�)" : "$this->itaj (���ځ[��)";
            
        } elseif ($spmode == "soko") {
            $this->spmode = $spmode;
            $this->ptitle = "$this->itaj (dat�q��)";
            
        } elseif ($spmode == "palace") {
            $this->spmode = $spmode;
            $this->ptitle = $_conf['ktai'] ? "�ڂ̓a��" : "�X���̓a��";
            
        } elseif ($spmode == "news") {
            $this->spmode = $spmode;
            $this->ptitle = $_conf['ktai'] ? "ƭ������" : "�j���[�X�`�F�b�N";
        
        } else {
            trigger_error(__FUNCTION__, E_USER_WARNING);
            die('Error: ' . __FUNCTION__);
        }
    }
    
    /**
     * �����I�ɔ��ihost, bbs, ���j���Z�b�g����
     *
     * @access  public
     * @return  void
     */
    function setIta($host, $bbs, $itaj = "")
    {
        if (preg_match('/[<>]/', $host) || preg_match('/[<>]/', $bbs)) {
            trigger_error(__FUNCTION__, E_USER_WARNING);
            die('Error: ' . __FUNCTION__);
        }
        $this->host = $host;
        $this->bbs  = $bbs;
        $this->setItaj($itaj);
    }
    
    /**
     * �����Z�b�g����
     *
     * @access  private
     * @return  void
     */
    function setItaj($itaj)
    {
        $this->itaj = $itaj ? $itaj : $this->bbs;
        
        $this->itaj_hs = htmlspecialchars($this->itaj, ENT_QUOTES);
        $this->ptitle = $this->itaj;
    }
    
    /**
     * readList
     *
     * @access  public
     * @return  array
     */
    function readList()
    {
        global $_conf;
        
        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('readList()');
        
        $lines = array();
        
        // spmode�̏ꍇ
        if ($this->spmode) {
        
            // ���[�J���̗����t�@�C�� �ǂݍ���
            if ($this->spmode == "recent") {
                file_exists($_conf['rct_file']) and $lines = file($_conf['rct_file']);
            
            // ���[�J���̏������ݗ����t�@�C�� �ǂݍ���
            } elseif ($this->spmode == "res_hist") {
                $rh_idx = $_conf['pref_dir'] . "/p2_res_hist.idx";
                file_exists($rh_idx) and $lines = file($rh_idx);
            
            // ���[�J���̂��C�Ƀt�@�C�� �ǂݍ���
            } elseif ($this->spmode == "fav") {
                file_exists($_conf['favlist_file']) and $lines = file($_conf['favlist_file']);
            
            // �j���[�X�n�T�u�W�F�N�g�ǂݍ���
            } elseif ($this->spmode == "news") {
            
                unset($news);
                $news[] = array(host=>"news2.2ch.net", bbs=>"newsplus"); // �j���[�X����+
                $news[] = array(host=>"news2.2ch.net", bbs=>"liveplus"); // �j���[�X����
                $news[] = array(host=>"book.2ch.net", bbs=>"bizplus");   // �r�W�l�X�j���[�X����+
                $news[] = array(host=>"live2.2ch.net", bbs=>"news");     // �j���[�X����
                $news[] = array(host=>"news3.2ch.net", bbs=>"news2");    // �j���[�X�c�_
                
                foreach ($news as $n) {
                    
                    require_once P2_LIB_DIR . '/SubjectTxt.php';
                    $aSubjectTxt =& new SubjectTxt($n['host'], $n['bbs']);
                    
                    if (is_array($aSubjectTxt->subject_lines)) {
                        foreach ($aSubjectTxt->subject_lines as $l) {
                            if (preg_match("/^([0-9]+)\.(dat|cgi)(,|<>)(.+) ?(\(|�i)([0-9]+)(\)|�j)/", $l, $matches)) {
                                //$this->isonline = true;
                                unset($al);
                                $al['key'] = $matches[1];
                                $al['ttitle'] = rtrim($matches[4]);
                                $al['rescount'] = $matches[6];
                                $al['host'] = $n['host'];
                                $al['bbs'] = $n['bbs'];
                                $lines[] = $al;
                            }
                        }
                    }
                }
        
            // p2_threads_aborn.idx �ǂݍ���
            } elseif ($this->spmode == 'taborn') {
                $file = P2Util::getThreadAbornFile($this->host, $this->bbs);
                if (file_exists($file)) {
                    $lines = file($file);
                }
            
            // {{{ spmode��dat�q�ɂ̏ꍇ @todo �y�[�W���O�p�ɐ��𐧌��ł��邵����
            
            } elseif ($this->spmode == "soko") {

                $dat_host_dir = P2Util::datDirOfHost($this->host);
                $idx_host_dir = P2Util::idxDirOfHost($this->host);
            
                $dat_bbs_dir = $dat_host_dir."/".$this->bbs;
                $idx_bbs_dir = $idx_host_dir."/".$this->bbs;
                
                $dat_pattern = '/([0-9]+)\.dat$/';
                $idx_pattern = '/([0-9]+)\.idx$/';
                
                // {{{ dat���O�f�B���N�g���𑖍����ČǗ�dat��idx�t������
                
                $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('dat');
                
                if ($cdir = dir($dat_bbs_dir)) { // or die ("���O�f�B���N�g�����Ȃ���I");
                    while ($entry = $cdir->read()) {
                        if (preg_match($dat_pattern, $entry, $matches)) {
                            $theidx = $idx_bbs_dir . "/" . $matches[1] . ".idx";
                            if (!file_exists($theidx)) {
                                if ($datlines = file($dat_bbs_dir . "/" . $entry)) {
                                    $firstdatline = rtrim($datlines[0]);
                                    if (strstr($firstdatline, "<>")) {
                                        $datline_sepa = "<>";
                                    } else {
                                        $datline_sepa = ",";
                                    }
                                    $d = explode($datline_sepa, $firstdatline);
                                    $atitle = $d[4];
                                    $gotnum = sizeof($datlines);
                                    $readnum = $gotnum;
                                    $anewline = $readnum + 1;
                                    $data = array($atitle, $matches[1], '', $gotnum, '',
                                                $readnum, '', '', '', $anewline,
                                                '', '', '');
                                    P2Util::recKeyIdx($theidx, $data);
                                }
                            }
                            // array_push($lines, $idl[0]);
                        }
                    }
                    $cdir->close();
                }
                
                $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('dat');
                
                // }}}
                // {{{ idx���O�f�B���N�g���𑖍�����idx���𒊏o���ă��X�g��
                
                // �I�����C�����q�ɂ��܂Ƃ߂Ē��o���Ă���B�I�����C�����O���̂� subject.php �ōs���Ă���B
                
                $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('idx');
                
                if ($cdir = dir($idx_bbs_dir)) { // or die ("���O�f�B���N�g�����Ȃ���I");
                    $limit = 1000; // �ЂƂ܂��ȈՐ���
                    $i = 0;
                    while ($entry = $cdir->read()) {
                        if (preg_match($idx_pattern, $entry)) {
                            $idl = file($idx_bbs_dir . "/" . $entry);
                            array_push($lines, $idl[0]);
                            $i++;
                            if ($i >= $limit) {
                                P2Util::pushInfoHtml("<p>p2 info: idx���O�����A�\�������\���ł���{$limit}�����I�[�o�[���Ă��܂��B</p>");
                                break;
                            }
                        }
                    }
                    $cdir->close();
                }
                
                $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('idx');

                // }}}
            
            // }}}
            
            // �X���̓a���̏ꍇ  // p2_palace.idx �ǂݍ���
            } elseif ($this->spmode == "palace") {
                $palace_idx = $_conf['pref_dir']. '/p2_palace.idx';
                file_exists($palace_idx) and $lines = file($palace_idx);
            }
        
        // �I�����C����� subject.txt ��ǂݍ��ށispmode�łȂ��ꍇ�j
        } else {
            require_once P2_LIB_DIR . '/SubjectTxt.php';
            $aSubjectTxt =& new SubjectTxt($this->host, $this->bbs);
            $lines = $aSubjectTxt->subject_lines;
            
        }
        
        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('readList()');
        
        return $lines;
    }
    
    /**
     * @access  public
     * @return  integer
     */
    function addThread($aThread)
    {
        $this->threads[] = $aThread;
        $this->num++;
        
        return $this->num;
    }

}
