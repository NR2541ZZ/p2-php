<?php
$GLOBALS['_ngaborns_head_hits'] = 0;

/*
    p2 - NG���ځ[��𑀍삷��N���X
    �X�^�e�B�b�N���\�b�h�ŗ��p����
    
    $_ngaborns[$ngcode] => array(
        'file' =>
        'data' => array(
            array(
                'word' =>
                'hits' =>
                ...
            ),
            array(
                'word' =>
                'hits' =>
                ...
            ),
            ...
        ),
    )
*/
class NgAbornCtl
{
    /**
     * ���ځ[��&NG���[�h�ݒ��ǂݍ���
     * // �ҏWUI�������̂ŁA�ݒ�t�@�C���͈ꖇ�̃V���A���C�Y�f�[�^�ł��悢�Ƃ���
     *
     * @static
     * @access  public
     * @return  array
     */
    function loadNgAborns()
    {
        global $_ngaborns;
        
        $_ngaborns = array();

        // aborn_res �����}�b�`�`�F�b�N�̎d�����Ⴄ abornResCheck()
        $_ngaborns['aborn_res']  = NgAbornCtl::readNgAbornFromFile('p2_aborn_res.txt');
        $_ngaborns['aborn_name'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_name.txt');
        $_ngaborns['aborn_mail'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_mail.txt');
        $_ngaborns['aborn_msg']  = NgAbornCtl::readNgAbornFromFile('p2_aborn_msg.txt');
        $_ngaborns['aborn_id']   = NgAbornCtl::readNgAbornFromFile('p2_aborn_id.txt');
        $_ngaborns['ng_name']    = NgAbornCtl::readNgAbornFromFile('p2_ng_name.txt');
        $_ngaborns['ng_mail']    = NgAbornCtl::readNgAbornFromFile('p2_ng_mail.txt');
        $_ngaborns['ng_msg']     = NgAbornCtl::readNgAbornFromFile('p2_ng_msg.txt');
        $_ngaborns['ng_id']      = NgAbornCtl::readNgAbornFromFile('p2_ng_id.txt');

        $GLOBALS['_P2_NGABORN_LOADED'] = true;
        
        return $_ngaborns;
    }

    /**
     * �f�[�^�t�@�C������NG���ځ[��f�[�^��ǂݍ���
     *
     * @static
     * @access  private
     * @return  array
     */
    function readNgAbornFromFile($filename)
    {
        global $_conf;

        $lines = array();
        $array['file'] = $_conf['pref_dir'] . '/' . $filename;
        if (file_exists($array['file']) and $lines = file($array['file'])) {
            foreach ($lines as $l) {
                $lar = explode("\t", trim($l));
                if (strlen($lar[0]) == 0) {
                    continue;
                }
                
                $ar = array(
                    'cond' => $lar[0], // ��������
                    'word' => $lar[0], // �Ώە�����
                    'lasttime' => null, // �Ō��HIT��������
                    'hits' => 0, // HIT��
                );
                isset($lar[1]) && $ar['lasttime'] = $lar[1];
                isset($lar[2]) && $ar['hits'] = (int) $lar[2];
                if ($filename == 'p2_aborn_res.txt') {
                    continue;
                }

                // ����
                if (preg_match('!<bbs>(.+?)</bbs>!', $ar['word'], $matches)) {
                    $ar['bbs'] = explode(',', $matches[1]);
                }
                $ar['word'] = preg_replace('!<bbs>(.*)</bbs>!', '', $ar['word']);

                // �^�C�g������
                if (preg_match('!<title>(.+?)</title>!', $ar['word'], $matches)) {
                    $ar['title'] = $matches[1];
                }
                $ar['word'] = preg_replace('!<title>(.*)</title>!', '', $ar['word']);

                // ���K�\��
                if (preg_match('/^<(mb_ereg|preg_match|regex)(:[imsxeADSUXu]+)?>(.+)$/', $ar['word'], $matches)) {
                    // �}�b�`���O�֐��ƃp�^�[����ݒ�
                    if ($matches[1] == 'regex') {
                        if (P2_MBREGEX_AVAILABLE) {
                            $ar['regex'] = 'mb_ereg';
                            $ar['word'] = $matches[3];
                        } else {
                            $ar['regex'] = 'preg_match';
                            $ar['word'] = '/' . str_replace('/', '\\/', $matches[3]) . '/';
                        }
                    } else {
                        $ar['regex'] = $matches[1];
                        $ar['word'] = $matches[3];
                    }
                    // �啶���������𖳎�
                    if ($matches[2] && strstr($matches[2], 'i')) {
                        if ($ar['regex'] == 'mb_ereg') {
                            $ar['regex'] = 'mb_eregi';
                        } else {
                            $ar['word'] .= 'i';
                        }
                    }
                // �啶���������𖳎�
                } elseif (preg_match('/^<i>(.+)$/', $ar['word'], $matches)) {
                    $ar['word'] = $matches[1];
                    $ar['ignorecase'] = true;
                }
                
                $array['data'][] = $ar;
            }
        }
        return $array;
    }
    
    /**
     * ���X�̂��ځ[����܂Ƃ߂ă`�F�b�N����i���O�A���[���A���t�A���b�Z�[�W�j
     *
     * @access  public
     * @return  string|false  �}�b�`������}�b�`������B�}�b�`���Ȃ����false
     */
    function checkAborns($name, $mail, $date_id, $msg, $bbs, $ttitle_hc)
    {
        if (false !== ($match_word = NgAbornCtl::ngAbornCheck('aborn_name', strip_tags($name), $bbs, $ttitle_hc))) {
            return $match_word;
        }
        if (false !== ($match_word = NgAbornCtl::ngAbornCheck('aborn_mail', $mail, $bbs, $ttitle_hc))) {
            return $match_word;
        }
        if (false !== ($match_word = NgAbornCtl::ngAbornCheck('aborn_id', $date_id, $bbs, $ttitle_hc))) {
            return $match_word;
        }
        if (false !== ($match_word = NgAbornCtl::ngAbornCheck('aborn_msg', $msg, $bbs, $ttitle_hc))) {
            return $match_word;
        }
        return false;
    }
    
    /**
     * NG���ځ[����`�F�b�N����
     *
     * @access  public
     * @return  string|false  �}�b�`������}�b�`������B�}�b�`���Ȃ����false
     */
    function ngAbornCheck($code, $subject, $bbs, $ttitle_hc)
    {
        global $_ngaborns;

        $GLOBALS['debug'] && $GLOBALS['profiler']->enterSection('ngAbornCheck()');
        
        $match_word = null;

        if (isset($_ngaborns[$code]['data']) && is_array($_ngaborns[$code]['data'])) {
            foreach ($_ngaborns[$code]['data'] as $k => $v) {
            
                if (strlen($v['word']) == 0) {
                    continue;
                }
                
                // �`�F�b�N
                if ((strlen($bbs) > 0) and isset($v['bbs']) && in_array($bbs, $v['bbs']) == false) {
                    continue;
                }

                // �^�C�g���`�F�b�N
                if ((strlen($ttitle_hc) > 0) and isset($v['title']) && stristr($ttitle_hc, $v['title']) === false) {
                    continue;
                }
                
                // ���[�h�`�F�b�N
                // ���K�\��
                if (!empty($v['regex'])) {
                    $re_method = $v['regex'];
                    /*if (@$re_method($v['word'], $subject, $matches)) {
                        NgAbornCtl::ngAbornUpdate($code, $k);
                        $match_word = htmlspecialchars($matches[0], ENT_QUOTES);
                        break;
                    }*/
                     if (@$re_method($v['word'], $subject)) {
                        NgAbornCtl::ngAbornUpdate($code, $k);
                        $match_word = $v['word'];
                        break;
                    }
               // �啶���������𖳎�(1)
                } elseif (!empty($v['ignorecase'])) {
                    if (stristr($subject, $v['word'])) {
                        NgAbornCtl::ngAbornUpdate($code, $k);
                        $match_word = $v['word'];
                        break;
                    }
                // �P���ɕ����񂪊܂܂�邩�ǂ������`�F�b�N
                } else {
                    if (strstr($subject, $v['word'])) {
                        NgAbornCtl::ngAbornUpdate($code, $k);
                        $match_word = $v['word'];
                        break;
                    }
                }
            }
        }
        
        $GLOBALS['debug'] && $GLOBALS['profiler']->leaveSection('ngAbornCheck()');
        
        return is_null($match_word) ? false : $match_word;
    }
    
    /**
     * ���背�X�̓������ځ[����`�F�b�N����
     *
     * @access  public
     * @return  boolean  �}�b�`������true
     */
    function abornResCheck($host, $bbs, $key, $resnum)
    {
        global $_ngaborns;

        $target = $host . '/' . $bbs . '/' . $key . '/' . $resnum;
        
        if (isset($_ngaborns['aborn_res']['data']) && is_array($_ngaborns['aborn_res']['data'])) {
            foreach ($_ngaborns['aborn_res']['data'] as $k => $v) {
                if ($_ngaborns['aborn_res']['data'][$k]['word'] == $target) {
                    NgAbornCtl::ngAbornUpdate('aborn_res', $k);
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * NG/���ځ`������Ɖ񐔂��X�V
     *
     * @access  private
     * @return  void
     */
    function ngAbornUpdate($ngcode, $k)
    {
        global $_ngaborns;

        if (isset($_ngaborns[$ngcode]['data'][$k])) {
            $v =& $_ngaborns[$ngcode]['data'][$k];
            $v['lasttime'] = date('Y/m/d G:i'); // HIT���Ԃ��X�V
            if (empty($v['hits'])) {
                $v['hits'] = 1; // ��HIT
            } else {
                $v['hits']++; // HIT�񐔂��X�V
            }
        }
        
        NgAbornCtl::countNgAbornsHits($ngcode);
    }
    
    /**
     * $GLOBALS['ngaborn_hits'] �iHIT�̍X�V�`�F�b�N�ɗp�ӂ����j �ƁA
     * $GLOBALS['_ngaborns_head_hits']�iread_new ��HTML id�p�ɗp�ӂ����j���J�E���g�X�V����
     *
     * @static
     * @public
     * @return  integer
     */
    function countNgAbornsHits($ngcode)
    {
        if (!isset($GLOBALS['ngaborns_hits'])) {
            NgAbornCtl::initNgAbornsHits();
        }
        
        if ($ngcode != 'ng_msg') {
            $GLOBALS['_ngaborns_head_hits']++;
        }
        
        return ++$GLOBALS['ngaborns_hits'][$ngcode];
    }
    
    /**
     * @access  private
     * @return  void
     */
    function initNgAbornsHits()
    {
        $GLOBALS['ngaborns_hits'] = array(
            'aborn_res'  => 0,
            'aborn_name' => 0,
            'aborn_mail' => 0,
            'aborn_msg'  => 0,
            'aborn_id'   => 0,
            'ng_name'    => 0,
            'ng_mail'    => 0,
            'ng_msg'     => 0,
            'ng_id'      => 0
        );
    }
    
    /**
     * ���ځ[��&NG���[�h�ݒ��ۑ�����
     *
     * @static
     * @access  public
     * @return  boolean
     */
    function saveNgAborns()
    {
        global $_ngaborns;
        global $_conf;
        
        // HIT���Ȃ���΍X�V���Ȃ�
        if (empty($GLOBALS['ngaborns_hits'])) {
            return true;
        }
        
        // HIT�������̂����X�V
        foreach ($GLOBALS['ngaborns_hits'] as $ngcode => $v) {
        
            // �ݒ�f�[�^���Ȃ��Ȃ甲����
            if (empty($_ngaborns[$ngcode]['data'])) {
                continue;
            }
            
            // �X�V���ԂŃ\�[�g����
            usort($_ngaborns[$ngcode]['data'], array('NgAbornCtl', 'cmpLastTime'));
        
            $cont = "";
            foreach ($_ngaborns[$ngcode]['data'] as $a_ngaborn) {
            
                // �K�v�Ȃ炱���ŌÂ��f�[�^�̓X�L�b�v�i�폜�j����
                if (!empty($a_ngaborn['lasttime']) && $_conf['ngaborn_daylimit']) {
                    
                    // 2007/03/12 �f�[�^�� '--' �������Ă���P�[�X���������̂Łi�����o���O����悤�ɂȂ�ΊO�������j
                    if ($a_ngaborn['lasttime'] != '--') {
                        if (strtotime($a_ngaborn['lasttime']) < time() - 60 * 60 * 24 * $_conf['ngaborn_daylimit']) {
                            continue;
                        }
                    }
                }
                
                if (empty($a_ngaborn['lasttime'])) {
                    $a_ngaborn['lasttime'] = date('Y/m/d G:i');
                }
                
                $cont .= $a_ngaborn['cond'] . "\t" . $a_ngaborn['lasttime'] . "\t" . $a_ngaborn['hits'] . "\n";
            }
            
            // ��������
            if (false === file_put_contents($_ngaborns[$ngcode]['file'], $cont, LOCK_EX)) {
                return false;
            }
        
        } // foreach
        
        return true;
    }

    /**
     * NG���ځ[��HIT�L�^���X�V���ԂŃ\�[�g����
     *
     * @static
     * @access  private
     * @return  integer
     */
    function cmpLastTime($a, $b)
    {
        if (empty($a['lasttime']) || empty($b['lasttime'])) {
            return strcmp($a['lasttime'], $b['lasttime']);
        }
        return (strtotime($a['lasttime']) < strtotime($b['lasttime'])) ? 1 : -1;
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
