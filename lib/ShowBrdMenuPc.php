<?php
/**
 * p2 - �{�[�h���j���[��\������ �N���X
 */
class ShowBrdMenuPc
{
    var $cate_id; // �J�e�S���[ID�i�A�Ԑ����j
    
    /**
     * @constructor
     */
    function ShowBrdMenuPc()
    {
        $this->cate_id = 1;
    }
    
    /**
     * ���j���[��HTML�\������
     *
     * @access  public
     * @return  void
     */
    function printBrdMenu($categories)
    {
        global $_conf;

        if (!$categories) {
            return;
        }
        
        $csrfid = P2Util::getCsrfId();
        
        foreach ($categories as $cate) {
            if ($cate->num > 0) {
                echo "<div class=\"menu_cate\">\n";
                echo "    <b><a class=\"menu_cate\" href=\"javascript:void(0);\" onClick=\"showHide('c{$this->cate_id}', 'itas_hide');\" target=\"_self\">{$cate->name}</a></b>\n";
                
                if ($cate->is_open or $cate->ita_match_num) {
                    echo "    <div class=\"itas\" id=\"c{$this->cate_id}\">\n";
                } else {
                    echo "    <div class=\"itas_hide\" id=\"c{$this->cate_id}\">\n";
                }
                
                foreach ($cate->menuitas as $mita) {
                    
                    $add_uri = P2Util::buildQueryUri($_SERVER['SCRIPT_NAME'], array(
                        'host'      => $mita->host,
                        'bbs'       => $mita->bbs,
                        'itaj_en'   => $mita->itaj_en,
                        'setfavita' => '1',
                        'csrfid'    => $csrfid
                    ));
                    $add_atag = P2View::tagA($add_uri, '+', array(
                        'target' => '_self', 'class' => 'fav', 'title' => '���C�ɔɒǉ�'
                    ));
                    
                    $subject_uri = P2Util::buildQueryUri($_conf['subject_php'], array(
                        'host'    => $mita->host,
                        'bbs'     => $mita->bbs,
                        'itaj_en' => $mita->itaj_en
                    ));
                    $subject_atag = P2View::tagA($subject_uri, $mita->itaj_ht, array('title' => '���J��'));
                    
                    echo "        $add_atag $subject_atag<br>\n";
                }
                echo "    </div>\n";
                echo "</div>\n";
            }
            $this->cate_id++;
        }
    }
    
    /**
     * ���C�ɔ�HTML�\������
     *
     * @access  public
     * @return  void
     */
    function printFavItaHtml()
    {
        global $_conf, $matome_i, $STYLE;
        
        // favita�ǂݍ���
        $favitas = array();
        if (file_exists($_conf['favita_path'])) {
            if ($lines = file($_conf['favita_path'])) {
                foreach ($lines as $l) {
                    if (preg_match("/^\t?(.+)\t(.+)\t(.+)\$/", trim($l), $matches)) {
                        $favitas[] = array(
                            'host' => $matches[1],
                            'bbs'  => $matches[2],
                            'itaj' => $matches[3],
                        );
                    }
                }
            }
        }
        
        // ����ۂȂ�
        if (!$favitas) {
            echo <<<EOP
    <div class="menu_cate"><b>���C�ɔ�</b> [<a href="editfavita.php" target="subject">�ҏW</a>]<br>
        <div class="itas" id="c_favita">�i����ہj</div>
    </div>
EOP;
            return;
        }
        
        // �V������\������ꍇ�E�܂Ƃ߂ăv���t�F�b�`
        if ($_conf['enable_menu_new'] && !empty($_GET['shownew'])) {
            if ($_conf['expack.use_pecl_http'] == 1) {
                require_once P2_LIB_DIR . '/P2HttpExt.php';
                P2HttpRequestPool::fetchSubjectTxt($favitas);
                $GLOBALS['expack.subject.multi-threaded-download.done'] = true;
            }
        }
        
        $csrfid = P2Util::getCsrfId();
        
        echo <<<EOP
<div class="menu_cate"><b><a class="menu_cate" href="javascript:void(0);" onClick="showHide('c_favita', 'itas_hide');" target="_self">���C�ɔ�</a></b> [<a href="editfavita.php" target="subject">�ҏW</a>]<br>
    <div class="itas" id="c_favita">
EOP;
        foreach ($favitas as $favita) {
            extract($favita); // $host, $bbs, $itaj

            $itaj_en = base64_encode($itaj);
            
            $uri = P2Util::buildQueryUri($_SERVER['SCRIPT_NAME'], array(
                'host'    => $host,
                'bbs'     => $bbs,
                'setfavita' => '0',
                'csrfid'  => $csrfid
            ));
            $star_atag = P2View::tagA($uri, '��', array(
                'target' => '_self', 'class' => 'fav',
                'title'  => "�u{$itaj}�v�����C�ɔ���O��",
                'onClick' => "return confirmSetFavIta('" . str_replace(array("\\", "'"), array("\\\\", "\\'"), $itaj) . "');"
            ));

            // �V������\������ꍇ
            if ($_conf['enable_menu_new'] && !empty($_GET['shownew'])) {
                $matome_i++;
                
                // $host, $bbs
                $spmode = '';
                $shinchaku_num = 0;
                $_newthre_num  = 0;
                
                include './subject_new.php';    // $shinchaku_num, $_newthre_num ���Z�b�g�����

                $newthre_ht = '';
                if ($_newthre_num) {
                    $newthre_ht = "{$_newthre_num}";
                }
                
                $subject_uri = P2Util::buildQueryUri($_conf['subject_php'], array(
                    'host'    => $host,
                    'bbs'     => $bbs,
                    'itaj_en' => $itaj_en
                ));
                $subject_atag = P2View::tagA($subject_uri, hs($itaj), array(
                    'onClick' => "chMenuColor('{$matome_i}');"
                ));
                
                $read_new_uri = P2Util::buildQueryUri($_conf['read_new_php'], array(
                    'host'    => $host,
                    'bbs'     => $bbs
                ));
                $read_new_attr = array(
                    'target' => 'read',
                    'id' => "un{$matome_i}",
                    'onClick' => "chUnColor('{$matome_i}');"
                );
                if ($shinchaku_num > 0) {
                    $read_new_attr['class'] = 'newres_num';
                } else {
                    $read_new_attr['class'] = 'newres_num_zero';
                }
                $read_new_atag = P2View::tagA($read_new_uri, hs($shinchaku_num), $read_new_attr);
                
                echo <<<EOP
        $star_atag $subject_atag <span id="newthre{$matome_i}" class="newthre_num">{$newthre_ht}</span> ($read_new_atag)<br>
EOP;

            // �V������\�����Ȃ��ꍇ
            } else {

                $subject_uri = P2Util::buildQueryUri($_conf['subject_php'], array(
                    'host'    => $host,
                    'bbs'     => $bbs,
                    'itaj_en' => $itaj_en
                ));
                $subject_atag = P2View::tagA($subject_uri, hs($itaj));
                
                echo "$star_atag $subject_atag<br>";
            }
            
            ob_flush(); flush();
            
        } // foreach
        
        echo "    </div>\n";
        echo "</div>\n";
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
