<?php
/*
    p2 - NG���ځ[��𑀍삷��N���X
*/
class NgAbornCtl
{
    /**
     * ���ځ[��&NG���[�h�ݒ��ۑ�����
     */
    function saveNgAborns()
    {
        global $ngaborns, $ngaborns_hits;
        global $_conf;

        // HIT�������̂ݍX�V����
        if ($GLOBALS['ngaborns_hits']) {
            foreach ($ngaborns_hits as $code => $v) {

                if ($ngaborns[$code]['data']) {

                    // �X�V���ԂŃ\�[�g����
                    usort($ngaborns[$code]['data'], array('NgAbornCtl', 'cmpLastTime'));

                    $cont = '';
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

                        $cont .= $a_ngaborn['cond'] . "\t" . $a_ngaborn['lasttime'] . "\t" . $a_ngaborn['hits'] . "\n";
                    } // foreach

                    /*
                    echo "<pre>";
                    echo $cont;
                    echo "</pre>";
                    */

                    // ��������

                    $fp = @fopen($ngaborns[$code]['file'], 'wb'); // or die("Error: cannot write. ( $ngaborns[$code]['file'] )");
                    if ($fp) {
                        @flock($fp, LOCK_EX);
                        fputs($fp, $cont);
                        @flock($fp, LOCK_UN);
                        fclose($fp);
                    }


                } // if

            } // foreach
        }
        return true;
    }

    /**
     * NG���ځ[��HIT�L�^���X�V���ԂŃ\�[�g����
     *
     * @access  private
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
     * @access  private
     */
    function readNgAbornFromFile($filename)
    {
        global $_conf;

        $lines = array();
        $array['file'] = $_conf['pref_dir'].'/'.$filename;
        if ($lines = @file($array['file'])) {
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
