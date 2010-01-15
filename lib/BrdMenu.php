<?php
/**
 * p2 - �{�[�h���j���[�N���X for menu.php
 */
class BrdMenu
{
    var $categories;    // �N���X BrdMenuCate �̃I�u�W�F�N�g���i�[����z��
    var $num = 0;       // �i�[���ꂽ BrdMenuCate �I�u�W�F�N�g�̐�
    var $format;        // html�`�����Abrd�`����("html", "brd")
    var $cate_match;    // �J�e�S���[�}�b�`�`��
    var $ita_match;     // �}�b�`�`��
    
    /**
     * @constructor
     */
    function BrdMenu()
    {
    }
    
    /**
     * �J�e�S���[��ǉ�����
     *
     * @access  public
     * @return  void
     */
    function addBrdMenuCate($aBrdMenuCate)
    {
        $this->categories[] = $aBrdMenuCate;
        $this->num++;
    }
    
    /**
     * �p�^�[���}�b�`�̌`����o�^����
     *
     * @access  public
     * @return  void
     */
    function setBrdMatch($brdName)
    {
        // html�`��
        if (preg_match("/(html?|cgi)$/", $brdName)) {
            $this->format = "html";
            $this->cate_match = "/<B>(.+)<\/B><BR>.*$/i";
            $this->ita_match = "/^<A HREF=\"?(http:\/\/(.+)\/([^\/]+)\/([^\/]+\.html?)?)\"?( target=\"?_blank\"?)?>(.+)<\/A>(<br>)?$/i";
        // brd�`��
        } else {
            $this->format = "brd";
            $this->cate_match = "/^(.+)\t([0-9])$/";
            $this->ita_match = "/^\t?(.+)\t(.+)\t(.+)$/";
        }
    }

    /**
     * �f�[�^��ǂݍ���ŁA�J�e�S���Ɣ�o�^����
     *
     * @access  public
     * @return  void
     */
    function setBrdList($data)
    {
        global $_conf;
        
        if (empty($data)) {
            return;
        }

        // ���OURL���X�g
        $not_bbs_list = array('http://members.tripod.co.jp/Backy/del_2ch/');
        
        foreach ($data as $v) {
            $v = rtrim($v);
            
            // �J�e�S����T��
            if (preg_match($this->cate_match, $v, $matches)) {
                $aBrdMenuCate = new BrdMenuCate($matches[1]);
                if ($this->format == 'brd') {
                    $aBrdMenuCate->is_open = (bool) $matches[2];
                }
                $this->addBrdMenuCate($aBrdMenuCate);
                
            // ��T��
            } elseif (preg_match($this->ita_match, $v, $matches)) {
                // html�`���Ȃ珜�OURL���O��
                if ($this->format == 'html') {
                    foreach ($not_bbs_list as $not_a_bbs) {
                        if ($not_a_bbs == $matches[1]) { continue 2; }
                    }
                }
                $aBrdMenuIta = new BrdMenuIta;
                
                // html�`��
                if ($this->format == 'html') {
                    $aBrdMenuIta->host = $matches[2];
                    $aBrdMenuIta->bbs  = $matches[3];
                    $itaj_match = $matches[6];
                // brd�`��
                } else {
                    $aBrdMenuIta->host = $matches[1];
                    $aBrdMenuIta->bbs  = $matches[2];
                    $itaj_match = $matches[3];
                }
                $aBrdMenuIta->setItaj(rtrim($itaj_match));
                
                // {{{ �����}�b�`

                // and����
                if ($GLOBALS['words_fm']) {
                
                    $no_match = false;
                    
                    foreach ($GLOBALS['words_fm'] as $word_fm_ao) {
                        $target = $aBrdMenuIta->itaj . "\t" . $aBrdMenuIta->bbs;
                        if (false === StrCtl::filterMatch($word_fm_ao, $target)) {
                            $no_match = true;
                        }
                    }
                    
                    if (!$no_match) {
                        $this->categories[$this->num-1]->ita_match_num++;
                        if (isset($GLOBALS['ita_mikke']['num'])) {
                            $GLOBALS['ita_mikke']['num']++;
                        } else {
                            $GLOBALS['ita_mikke']['num'] = 1;
                        }
                        $GLOBALS['ita_mikke']['host'] = $aBrdMenuIta->host;
                        $GLOBALS['ita_mikke']['bbs']  = $aBrdMenuIta->bbs;
                        $GLOBALS['ita_mikke']['itaj_en'] = $aBrdMenuIta->itaj_en;

                        // �}�[�L���O
                        if ($_conf['ktai'] && is_string($_conf['k_filter_marker'])) {
                            $aBrdMenuIta->itaj_ht = StrCtl::filterMarking($GLOBALS['word_fm'], $aBrdMenuIta->itaj, $_conf['k_filter_marker']);
                        } else {
                            $aBrdMenuIta->itaj_ht = StrCtl::filterMarking($GLOBALS['word_fm'], $aBrdMenuIta->itaj);
                        }
                        
                        // �}�b�`�}�[�L���O�Ȃ���΁ibbs�Ń}�b�`�����Ƃ��j�A�S���}�[�L���O
                        if ($aBrdMenuIta->itaj_ht == $aBrdMenuIta->itaj) {
                            $aBrdMenuIta->itaj_ht = '<b class="filtering">' . $aBrdMenuIta->itaj_ht . '</b>';
                        }
                        
                    // ������������Ȃ��āA����Ɍg�т̎�
                    } else {
                        if ($_conf['ktai']) {
                            continue;
                        }
                    }
                }
                
                // }}}

                if ($this->num) {
                    $this->categories[$this->num-1]->addBrdMenuIta($aBrdMenuIta);
                }
            }
        } // foreach
    }

    /**
     * brd�t�@�C���𐶐�����
     *
     * @access  public
     * @return  string|false  ���������琶������brd�t�@�C���̃p�X��Ԃ�
     */
    function makeBrdFile($cachefile)
    {
        global $_conf;
        
        $cont = '';
        $data = file($cachefile);
        $this->setBrdMatch($cachefile); // �p�^�[���}�b�`�`����o�^
        $this->setBrdList($data);       // �J�e�S���[�Ɣ��Z�b�g
        if ($this->categories) {
            foreach ($this->categories as $cate) {
                if ($cate->num > 0) {
                    $cont .= $cate->name . "\t0\n";
                    foreach ($cate->menuitas as $mita) {
                        $cont .= "\t{$mita->host}\t{$mita->bbs}\t{$mita->itaj}\n";
                    }
                }
            }
        }

        if (!$cont) {
            // 2008/07/14 �Ȃ������ł���ȏ������K�v�������̂��s���B�R�����g�A�E�g���Ă݂�B
            //if (strlen($GLOBALS['word']) > 0) {
                P2Util::pushInfoHtml(
                    sprintf(
                        "<p>p2 error: %s ������j���[�𐶐����邱�Ƃ͂ł��܂���ł����B</p>\n",
                        hs($cachefile)
                    )
                );
            //}
            unlink($cachefile);
            return false;
        }
        
        $p2brdfile = BrdCtl::getP2BrdFile($cachefile);
        if (false === FileCtl::make_datafile($p2brdfile, $_conf['p2_perm'])) {
            return false;
        }
        
        if (false === FileCtl::filePutRename($p2brdfile, $cont)) {
            die(sprintf('p2 error: %s ���X�V�ł��܂���ł���', hs($p2brdfile)));
            return false;
        }
        
        return $p2brdfile;
    }
}

/**
 * �{�[�h���j���[�J�e�S���[�N���X
 */
class BrdMenuCate
{
    var $name;                      // �J�e�S���[�̖��O
    var $menuitas       = array();  // �N���XBrdMenuIta�̃I�u�W�F�N�g���i�[����z��
    var $num            = 0;        // �i�[���ꂽBrdMenuIta�I�u�W�F�N�g�̐�
    var $is_open        = false;    // �J���(bool)
    var $ita_match_num  = 0;        // �����Ƀq�b�g�����̐�
    
    /**
     * @constructor
     */
    function BrdMenuCate($name)
    {
        $this->name = $name;
    }
    
    /**
     * ��ǉ�����
     *
     * @access  public
     * @return  void
     */
    function addBrdMenuIta($aBrdMenuIta)
    {
        $this->menuitas[] = $aBrdMenuIta;
        $this->num++;
    }
}

/**
 * �{�[�h���j���[�N���X
 */
class BrdMenuIta
{
    var $host;
    var $bbs;
    var $itaj;    // ��
    var $itaj_en;    // �����G���R�[�h��������
    var $itaj_ht;    // HTML�ŏo�͂�����i�t�B���^�����O�������́j
    
    /**
     * @access  public
     * @return  void
     */
    function setItaj($itaj)
    {
        $this->itaj = $itaj;
        $this->itaj_en = base64_encode($this->itaj);
        $this->itaj_ht = htmlspecialchars($this->itaj, ENT_QUOTES);
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
