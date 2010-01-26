<?php
/**
 * p2 - �{�[�h���j���[��HTML�\������N���X(�g��)
 */
class ShowBrdMenuK
{
    var $cate_id = 1; // �J�e�S���[ID
    
    /**
     * @constructor
     */
    function ShowBrdMenuK()
    {
    }

    /**
     * ���j���[�J�e�S����HTML�\������ for �g��
     *
     * @access  public
     * @return  void
     */
    function printCate($categories)
    {
        global $_conf, $list_navi_ht;

        if (!$categories) {
            return;
        }
        
        // �\��������
        if (isset($_GET['from'])) {
            $list_disp_from = intval($_GET['from']);
        } else {
            $list_disp_from = 1;
        }
        $list_disp_all_num = sizeof($categories);
        $disp_navi = P2Util::getListNaviRange($list_disp_from, $_conf['k_sb_disp_range'], $list_disp_all_num);
    
        if ($disp_navi['from'] > 1) {
            $mae_ht = <<<EOP
<a href="{$_conf['menu_k_php']}?view=cate&amp;from={$disp_navi['mae_from']}&amp;nr=1{$_conf['k_at_a']}" {$_conf['accesskey_for_k']}="{$_conf['k_accesskey']['prev']}">{$_conf['k_accesskey']['prev']}.�O</a>
EOP;
        } else {
            $mae_ht = '';
        }
        
        if ($disp_navi['end'] < $list_disp_all_num) {
            $tugi_ht = <<<EOP
<a href="{$_conf['menu_k_php']}?view=cate&amp;from={$disp_navi['tugi_from']}&amp;nr=1{$_conf['k_at_a']}" {$_conf['accesskey_for_k']}="{$_conf['k_accesskey']['next']}">{$_conf['k_accesskey']['next']}.��</a>
EOP;
        } else {
            $tugi_ht = '';
        }
        
        if (!$disp_navi['all_once']) {
            $list_navi_ht = <<<EOP
{$disp_navi['range_st']}{$mae_ht} {$tugi_ht}<br>
EOP;
        } else {
            $list_navi_ht = '';
        }
        
        if (UA::isIPhoneGroup()) {
            ?><ul id="home"><li class="group">�ꗗ</li><?php
        }
        foreach ($categories as $cate) {
            if ($this->cate_id >= $disp_navi['from'] and $this->cate_id <= $disp_navi['end']) {
                echo "<a href=\"{$_conf['menu_k_php']}?cateid={$this->cate_id}&amp;nr=1{$_conf['k_at_a']}\">{$cate->name}</a>($cate->num)<br>\n"; // $this->cate_id
            }
            $this->cate_id++;
        }
        if (UA::isIPhoneGroup()) {
            ?></ul><?php
        }
    }

    /**
     * ���j���[�J�e�S���̔�HTML�\������ for �g��
     *
     * @access  public
     * @return  void
     */
    function printIta($categories)
    {
        global $_conf, $list_navi_ht;

        if (!$categories) {
            return;
        }
        
        $csrfid = P2Util::getCsrfId();
        $hr = P2View::getHrHtmlK();
        
        $list_navi_ht = '';
        
        // �\��������
        if (isset($_GET['from'])) {
            $list_disp_from = intval($_GET['from']);
        } else {
            $list_disp_from = 1;
        }
        
        foreach ($categories as $cate) {
            if ($cate->num and $this->cate_id == $_GET['cateid']) {
                
                if (!UA::isIPhoneGroup()) {
                    echo "{$cate->name}<hr>\n";
                }

                $list_disp_all_num = $cate->num;
                $disp_navi = P2Util::getListNaviRange($list_disp_from, $_conf['k_sb_disp_range'], $list_disp_all_num);
                
                if ($disp_navi['from'] > 1) {
                    $mae_ht = <<<EOP
<a href="{$_conf['menu_k_php']}?cateid={$this->cate_id}&amp;from={$disp_navi['mae_from']}&amp;nr=1{$_conf['k_at_a']}">�O</a>
EOP;
                } else {
                    $mae_ht = '';
                }
                
                if ($disp_navi['end'] < $list_disp_all_num) {
                    $tugi_ht = <<<EOP
<a href="{$_conf['menu_k_php']}?cateid={$this->cate_id}&amp;from={$disp_navi['tugi_from']}&amp;nr=1{$_conf['k_at_a']}">��</a>
EOP;
                } else {
                    $tugi_ht = '';
                }
                
                if (!$disp_navi['all_once']) {
                    $list_navi_ht = <<<EOP
{$disp_navi['range_st']}{$mae_ht} {$tugi_ht}<br>
EOP;
                }

                if (UA::isIPhoneGroup()) {
                    echo '<ul>';
                    echo '<li class="group">�ꗗ</li>';
                }
                
                $i = 0;
                foreach ($cate->menuitas as $mita) {
                    $i++;
                    
                    $subject_attr = array();
                    $access_num_st = '';
                    
                    if ($i <= 9) {
                        $access_num_st = "$i.";
                        $subject_attr[$_conf['accesskey_for_k']] = $i;
                    }
                    
                    // ���v�����g
                    if ($i >= $disp_navi['from'] and $i <= $disp_navi['end']) {
                        
                        $uri = UriUtil::buildQueryUri($_SERVER['SCRIPT_NAME'], array(
                            'host'    => $mita->host,
                            'bbs'     => $mita->bbs,
                            'itaj_en' => $mita->itaj_en,
                            'setfavita' => '1',
                            'csrfid'  => $csrfid,
                            'view'    => 'favita',
                            UA::getQueryKey() => UA::getQueryValue()
                        ));
                        $add_atag = P2View::tagA($uri, '+');
                        
                        $uri = UriUtil::buildQueryUri($_conf['subject_php'], array(
                            'host'    => $mita->host,
                            'bbs'     => $mita->bbs,
                            'itaj_en' => $mita->itaj_en,
                            UA::getQueryKey() => UA::getQueryValue()
                        ));
                        $subject_atag = P2View::tagA($uri, "{$access_num_st}{$mita->itaj_ht}", $subject_attr);
                        
                        echo $add_atag . ' ' . $subject_atag . "<br>\n";
                    }
                }
            
            }
            $this->cate_id++;
        }
        if (UA::isIPhoneGroup()) {
            ?></ul><?php
        }
    }

    /**
     * ������������HTML�\������ for �g��
     *
     * @access  public
     * @return  void
     */
    function printItaSearch($categories)
    {
        global $_conf;
        global $list_navi_ht;
    
        if (!$categories) {
            return;
        }
        
        // {{{ �\��������
        
        $list_disp_from = empty($_GET['from']) ? 1 : intval($_GET['from']);
        
        $list_disp_all_num = $GLOBALS['ita_mikke']['num']; //
        $disp_navi = P2Util::getListNaviRange($list_disp_from, $_conf['k_sb_disp_range'], $list_disp_all_num);
        
        $detect_hint_q = 'detect_hint=' . urlencode('����');
        $word_q = '&amp;word=' . rawurlencode($GLOBALS['word']);
        
        if ($disp_navi['from'] > 1) {
            $mae_ht = <<<EOP
<a href="{$_conf['menu_k_php']}?w{$detect_hint_q}{$word_q}&amp;from={$disp_navi['mae_from']}&amp;nr=1{$_conf['k_at_a']}">�O</a>
EOP;
        } else {
            $mae_ht = '';
        }
        
        if ($disp_navi['end'] < $list_disp_all_num) {
            $tugi_ht = <<<EOP
<a href="{$_conf['menu_k_php']}?{$detect_hint_q}{$word_q}&amp;from={$disp_navi['tugi_from']}&amp;nr=1{$_conf['k_at_a']}">��</a>
EOP;
        } else {
            $tugi_ht = '';
        }
        
        if (!$disp_navi['all_once']) {
            $list_navi_ht = <<<EOP
{$disp_navi['range_st']}{$mae_ht} {$tugi_ht}<br>
EOP;
        } else {
            $list_navi_ht = '';
        }
        
        // }}}
        
        if (UA::isIPhoneGroup()) {
            ?><ul><?php
        }
        foreach ($categories as $cate) {
            
            if ($cate->num > 0) {
                $t = false;
                foreach ($cate->menuitas as $mita) {
                    
                    $GLOBALS['menu_show_ita_num']++;
                    if (
                        $GLOBALS['menu_show_ita_num'] >= $disp_navi['from']
                        and $GLOBALS['menu_show_ita_num'] <= $disp_navi['end']
                    ) {

                        if (!$t) {
                            echo "<b>{$cate->name}</b><br>\n";
                        }
                        $t = true;
                        
                        $uri = UriUtil::buildQueryUri($_conf['subject_php'], array(
                            'host' => $mita->host,
                            'bbs'  => $mita->bbs,
                            'itaj_en' => $mita->itaj_en,
                            UA::getQueryKey() => UA::getQueryValue()
                        ));
                        $atag = P2View::tagA($uri, $mita->itaj_ht);
                        
                        if (UA::isIPhoneGroup()) {
                            echo "<li>{$atag}</li>\n";
                        } else {
                            echo '&nbsp;' . $atag . "<br>\n";
                        }
                    }
                }

            }
            $this->cate_id++;
        }
        if (UA::isIPhoneGroup()) {
            ?></ul><?php
        }
    }

    /**
     * ���C�ɔ�HTML�\������ for �g��
     *
     * @access  public
     * @return  void
     */
    function printFavItaHtml()
    {
        global $_conf;
        
        $csrfid = P2Util::getCsrfId();
        $hr = P2View::getHrHtmlK();
        
        $show_flag = false;
        
        if (file_exists($_conf['favita_path']) and $lines = file($_conf['favita_path'])) {
            echo '���C�ɔ� [<a href="editfavita.php?b=k">�ҏW</a>]' . $hr;
            $i = 0;
            foreach ($lines as $l) {
                $i++;
                $l = rtrim($l);
                if (preg_match("/^\t?(.+)\t(.+)\t(.+)$/", $l, $matches)) {
                    $itaj = rtrim($matches[3]);
                    $attr = array();
                    $key_num_st = '';

                    if ($i <= 9) {
                        $attr[$_conf['accesskey_for_k']] = $i;
                        $key_num_st = "$i.";
                    }

                    $atag = P2View::tagA(
                        UriUtil::buildQueryUri($_conf['subject_php'],
                            array(
                                'host' => $matches[1],
                                'bbs'  => $matches[2],
                                'itaj_en' => base64_encode($itaj),
                                UA::getQueryKey() => UA::getQueryValue()
                            )
                        ),
                        UA::isIPhoneGroup() ? hs($itaj) : hs("$key_num_st$itaj"),
                        $attr
                    );

                    if (UA::isIPhoneGroup()) {
                        echo '<li>' . $atag . '</li>';
                    } else {
                        echo $atag . '<br>';
                    }

                    //  [<a href="{$_SERVER['SCRIPT_NAME']}?host={$matches[1]}&amp;bbs={$matches[2]}&amp;setfavita=0&amp;csrfid={$csrfid}&amp;view=favita{$_conf['k_at_a']}">��</a>]
                    $show_flag = true;
                }
            }
            if (UA::isIPhoneGroup()) {
                ?></ul><?php
            }
        }
        
        if (!$show_flag) {
            ?><p>���C�ɔ͂܂��Ȃ��悤��</p><?php
        }
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
