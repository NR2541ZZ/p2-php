<?php
/*
    p2 - NG���ځ[��𑀍삷��N���X
    �X�^�e�B�b�N���\�b�h�ŗ��p����
*/
class NgAbornCtl
{
    /**
     * ���ځ[��&NG���[�h�ݒ��ۑ�����
     *
     * @static
     * @access  public
     * @return  boolean
     */
    function saveNgAborns()
    {
        global $ngaborns, $ngaborns_hits;
        global $_conf;
        
        // HIT���Ȃ���΍X�V���Ȃ�
        if (!$GLOBALS['ngaborns_hits']) {
            return true;
        }
        
        foreach ($ngaborns_hits as $code => $v) {
        
            if (!$ngaborns[$code]['data']) {
                continue;
            }
            
            // �X�V���ԂŃ\�[�g����
            usort($ngaborns[$code]['data'], array('NgAbornCtl', 'cmpLastTime'));
        
            $cont = "";
            foreach ($ngaborns[$code]['data'] as $a_ngaborn) {
            
                // �K�v�Ȃ炱���ŌÂ��f�[�^�̓X�L�b�v�i�폜�j����
                if (!empty($a_ngaborn['lasttime']) && $_conf['ngaborn_daylimit']) {
                    if (strtotime($a_ngaborn['lasttime']) < time() - 60 * 60 * 24 * $_conf['ngaborn_daylimit']) {
                        continue;
                    }
                }
                
                if (empty($a_ngaborn['lasttime'])) {
                    $a_ngaborn['lasttime'] = date('Y/m/d G:i');
                }
                
                $cont .= $a_ngaborn['word']."\t".$a_ngaborn['lasttime']."\t".$a_ngaborn['hits']."\n";
            }
            
            // ��������
            $fp = fopen($ngaborns[$code]['file'], 'wb'); // or die("Error: cannot write. ( $ngaborns[$code]['file'] )");
            if (!$fp) {
                return false;
            }
            @flock($fp, LOCK_EX);
            fputs($fp, $cont);
            @flock($fp, LOCK_UN);
            fclose($fp);
        
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

    /**
     * ���ځ[��&NG���[�h�ݒ��ǂݍ���
     *
     * @static
     * @access  public
     * @return  array
     */
    function loadNgAborns()
    {
        $ngaborns = array();
    
        $ngaborns['aborn_res'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_res.txt'); // ���ꂾ���������i���قȂ�
        $ngaborns['aborn_name'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_name.txt');
        $ngaborns['aborn_mail'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_mail.txt');
        $ngaborns['aborn_msg'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_msg.txt');
        $ngaborns['aborn_id'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_id.txt');
        $ngaborns['ng_name'] = NgAbornCtl::readNgAbornFromFile('p2_ng_name.txt');
        $ngaborns['ng_mail'] = NgAbornCtl::readNgAbornFromFile('p2_ng_mail.txt');
        $ngaborns['ng_msg'] = NgAbornCtl::readNgAbornFromFile('p2_ng_msg.txt');
        $ngaborns['ng_id'] = NgAbornCtl::readNgAbornFromFile('p2_ng_id.txt');

        return $ngaborns;
    }

    /**
     * readNgAbornFromFile
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
        if ($lines = @file($array['file'])) {
            $lines = array_map('trim', $lines);
        
            if ($lines) {
                foreach ($lines as $l) {
                    $lar = explode("\t", $l);
                    $ar['word'] = $lar[0]; // �Ώە�����
                    $ar['lasttime'] = isset($lar[1]) ? $lar[1] : null; // �Ō��HIT��������
                    $ar['hits'] = isset($lar[2]) ? intval($lar[2]) : null; // HIT��
                    $array['data'][] = $ar;
                }
            }

        }
        return $array;
    }

}
