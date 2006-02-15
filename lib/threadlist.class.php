<?php
/**
 * p2 - ThreadList �N���X
 */
class ThreadList{

    var $threads;   // �N���XThread�̃I�u�W�F�N�g���i�[����z��
    var $num;       // �i�[���ꂽThread�I�u�W�F�N�g�̐�
    var $host;      // ex)pc.2ch.net
    var $bbs;       // ex)mac
    var $itaj;      // �� ex)�V�Emac��
    var $itaj_hd;   // HTML�\���p�ɁA���� htmlspecialchars() ��������
    var $spmode;    // ���ʔȊO�̃X�y�V�������[�h
    var $ptitle;    // �y�[�W�^�C�g��
    
    /**
     * �R���X�g���N�^
     */
    function ThreadList()
    {
        $this->num = 0;
    }
    
    //==============================================
    function setSpMode($name)
    {
        global $_conf;
        
        if ($name == "recent") {
            $this->spmode = $name;
            $this->ptitle = $_conf['ktai'] ? "�ŋߓǂ񂾽�" : "�ŋߓǂ񂾃X��";
        } elseif ($name == "res_hist") {
            $this->spmode = $name;
            $this->ptitle = "�������ݗ���";
        } elseif ($name == "fav") {
            $this->spmode = $name;
            $this->ptitle = $_conf['ktai'] ? "���C�ɽ�" : "���C�ɃX��";
        } elseif ($name == "taborn") {
            $this->spmode = $name;
            $this->ptitle = $_conf['ktai'] ? "$this->itaj (���ݒ�)" : "$this->itaj (���ځ[��)";
        } elseif ($name == "soko") {
            $this->spmode = $name;
            $this->ptitle = "$this->itaj (dat�q��)";
        } elseif ($name == "palace") {
            $this->spmode = $name;
            $this->ptitle = $_conf['ktai'] ? "�ڂ̓a��" : "�X���̓a��";
        } elseif ($name == "news") {
            $this->spmode = $name;
            $this->ptitle = $_conf['ktai'] ? "ƭ������" : "�j���[�X�`�F�b�N";
        }
    }
    
    /**
     * �� �����I�ɔ��ihost, bbs, ���j���Z�b�g����
     */
    function setIta($host, $bbs, $itaj = "")
    {
        $this->host = $host;
        $this->bbs = $bbs;
        $this->setItaj($itaj);
        
        return true;
    }
    
    /**
     * �������Z�b�g����
     */
    function setItaj($itaj)
    {
        if ($itaj) {
            $this->itaj = $itaj;
        } else {
            $this->itaj = $this->bbs;
        }
        $this->itaj_hd = htmlspecialchars($this->itaj, ENT_QUOTES);
        $this->ptitle = $this->itaj;
        
        return true;
    }
    
    /**
     * �� readList ���\�b�h
     */
    function readList()
    {
        global $_conf, $_info_msg_ht;
        
        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('readList()');
        
        if ($this->spmode) {
        
            // ���[�J���̗����t�@�C�� �ǂݍ���
            if ($this->spmode == "recent") {
                if ($lines = @file($_conf['rct_file'])) {
                    //$_info_msg_ht = "<p>�����͋���ۂł�</p>";
                    //return false;
                }
            
            // ���[�J���̏������ݗ����t�@�C�� �ǂݍ���
            } elseif ($this->spmode == "res_hist") {
                $rh_idx = $_conf['pref_dir']."/p2_res_hist.idx";
                if ($lines = @file($rh_idx)) {
                    //$_info_msg_ht = "<p>�������ݗ����͋���ۂł�</p>";
                    //return false;
                }
            
            //���[�J���̂��C�Ƀt�@�C�� �ǂݍ���
            } elseif ($this->spmode == "fav") {
                if ($lines = @file($_conf['favlist_file'])) {
                    //$_info_msg_ht = "<p>���C�ɃX���͋���ۂł�</p>";
                    //return false;
                }
            
            // �j���[�X�n�T�u�W�F�N�g�ǂݍ���
            } elseif ($this->spmode == "news") {
            
                unset($news);
                $news[] = array(host=>"news2.2ch.net", bbs=>"newsplus"); // �j���[�X����+
                $news[] = array(host=>"news2.2ch.net", bbs=>"liveplus"); // �j���[�X����
                $news[] = array(host=>"book.2ch.net", bbs=>"bizplus"); // �r�W�l�X�j���[�X����+
                $news[] = array(host=>"live2.2ch.net", bbs=>"news"); // �j���[�X����
                $news[] = array(host=>"news3.2ch.net", bbs=>"news2"); // �j���[�X�c�_
                
                foreach ($news as $n) {
                    
                    require_once (P2_LIBRARY_DIR . '/SubjectTxt.class.php');
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
            } elseif ($this->spmode == "taborn") {
                $dat_host_dir = P2Util::datDirOfHost($this->host);
                $lines = @file($dat_host_dir."/".$this->bbs."/p2_threads_aborn.idx");
            
            // ��spmode��dat�q�ɂ̏ꍇ ======================
            } elseif ($this->spmode == "soko") {

                $dat_host_dir = P2Util::datDirOfHost($this->host);
                $idx_host_dir = P2Util::idxDirOfHost($this->host);
            
                $dat_bbs_dir = $dat_host_dir."/".$this->bbs;
                $idx_bbs_dir = $idx_host_dir."/".$this->bbs;
                
                $dat_pattern = '/([0-9]+)\.dat$/';
                $idx_pattern = '/([0-9]+)\.idx$/';
                
                $lines = array();
                
                $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('dat'); //
                // ��dat���O�f�B���N�g���𑖍����ČǗ�dat��idx�t�� =================
                if ($cdir = dir($dat_bbs_dir)) { // or die ("���O�f�B���N�g�����Ȃ���I");
                    // �f�B���N�g������
                    while ($entry = $cdir->read()) {
                        if (preg_match($dat_pattern, $entry, $matches)) {
                            $theidx = $idx_bbs_dir."/".$matches[1].".idx";
                            if (!file_exists($theidx)) {
                                if ($datlines = @file($dat_bbs_dir."/".$entry)) {
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
                $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('dat');//
                
                $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('idx');//
                // {{{ idx���O�f�B���N�g���𑖍�����idx���𒊏o���ă��X�g��
                if ($cdir = dir($idx_bbs_dir)) { // or die ("���O�f�B���N�g�����Ȃ���I");
                    // �f�B���N�g������
                    while ($entry = $cdir->read()) {
                        if (preg_match($idx_pattern, $entry)) {
                            $idl = @file($idx_bbs_dir."/".$entry);
                            array_push($lines, $idl[0]);
                        }
                    }
                    $cdir->close();
                }
                // }}}
                $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('idx');//
            
            // ���X���̓a���̏ꍇ  // p2_palace.idx �ǂݍ���
            } elseif ($this->spmode == "palace") {
                $palace_idx = $_conf['pref_dir']. '/p2_palace.idx';
                if ($lines = @file($palace_idx)) {
                    // $_info_msg_ht = "<p>�a���͂����ǂ��ł�</p>";
                    // return false;
                }
            }
        
        // ���I�����C����� subject.txt ��ǂݍ��ށispmode�łȂ��ꍇ�j
        } else {
            require_once (P2_LIBRARY_DIR . '/SubjectTxt.class.php');
            $aSubjectTxt =& new SubjectTxt($this->host, $this->bbs);
            $lines =& $aSubjectTxt->subject_lines;
            
        }
        
        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('readList()');
        
        return $lines;
    }
    
    /**
     * �� addThread ���\�b�h
     */
    function addThread(&$aThread)
    {
        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('addThread()');
        
        $this->threads[] =& $aThread;
        $this->num++;
        
        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('addThread()');
        
        return $this->num;
    }

}

?>
