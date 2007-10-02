<?php
// p2 - �������ݗ����̃N���X

/**
 * ���X�L���̃N���X
 */
class ResArticle
{
    var $name;
    var $mail;
    var $daytime;
    var $msg;
    var $ttitle;
    var $host;
    var $bbs;
    var $itaj;
    var $key;
    var $resnum;
    var $order; // �L���ԍ�
}

/**
 * �������݃��O�̃N���X
 */
class ResHist
{
    // �N���X ResArticle �̃I�u�W�F�N�g���i�[����z��
    var $articles = array();
    
    var $resrange; // array('start' => i, 'to' => i, 'nofirst' => bool)
    
    /**
     * @constructor
     */
    function ResHist()
    {
    }
    
    /**
     * �������݃��O�� line ��s���p�[�X���āAResArticle�I�u�W�F�N�g��Ԃ�
     *
     * @access  public
     * @param   array    $aline
     * @return  object ResArticle
     */
    function lineToRes($aline, $order)
    {
        $aResArticle = new ResArticle();

        $resar = explode('<>', rtrim($aline));
        $aResArticle->name  = $resar[0];
        $aResArticle->mail  = $resar[1];
        $aResArticle->daytime = $resar[2];
        $aResArticle->msg   = $resar[3];
        $aResArticle->ttitle = $resar[4];
        $aResArticle->host  = $resar[5];
        $aResArticle->bbs   = $resar[6];
        if (!$aResArticle->itaj = P2Util::getItaName($aResArticle->host, $aResArticle->bbs)) {
            $aResArticle->itaj = $aResArticle->bbs;
        }
        $aResArticle->key   = $resar[7];
        $aResArticle->resnum = isset($resar[8]) ? $resar[8] : null;
        
        $aResArticle->order = $order;
        
        return $aResArticle;
    }
    
    /**
     * ���X�L��HTML��\������ PC�p
     *
     * @access  public
     * @param   array
     * @return  void
     */
    function printArticlesHtml($datlines)
    {
        global $_conf, $STYLE;
        
        $sid_q = defined('SID') ? '&amp;' . strip_tags(SID) : '';
        
        // Pager ����
        if (!require_once 'Pager/Pager.php') {
            P2Util::printSimpleHtml('p2 error: PEAR�� Pager/Pager.php ���C���X�g�[������Ă��܂���');
            die;
        }
        $perPage = 100;
        $params = array(
            'mode'       => 'Jumping',
            'itemData'   => $datlines,
            'perPage'    => $perPage,
            'delta'      => 25,
            'clearIfVoid' => true,
            'prevImg' => "�O��{$perPage}��",
            'nextImg' => "����{$perPage}��",
            //'separator' => '|',
            //'expanded' => true,
            'spacesBeforeSeparator' => 2,
            'spacesAfterSeparator' => 0,
        );

        $pager = &Pager::factory($params);
        $links = $pager->getLinks();
        $data  = $pager->getPageData();

        if ($pager->links) {
            echo "<div>{$pager->links}</div>";
        }
        
        echo '<dl>';
        
        if (isset($_REQUEST['pageID'])) {
            $pageID = max(1, intval($_REQUEST['pageID']));
        } else {
            $pageID = 1;
        }
        
        $n = ($pageID - 1) * $perPage;
        foreach ($data as $aline) {
            $n++;

            $aRes = $this->lineToRes($aline, $n);
            
            $daytime_hs = htmlspecialchars($aRes->daytime, ENT_QUOTES);
            $ttitle_hs = htmlspecialchars(html_entity_decode($aRes->ttitle, ENT_COMPAT, 'Shift_JIS'), ENT_QUOTES);
            
            $href_ht = "";
            if ($aRes->key) {
                if (empty($aRes->resnum) || $aRes->resnum == 1) {
                    $ls_q = '';
                    $footer_q = '#footer';
                } else {
                    $lf = max(1, $aRes->resnum - 0);
                    $ls_q = "&amp;ls={$lf}-";
                    $footer_q = "#r{$lf}";
                }
                $time = time();
                $href_ht = $_conf['read_php'] . "?host=" . $aRes->host . "&amp;bbs=" . $aRes->bbs . "&amp;key=" . $aRes->key . $ls_q . "{$_conf['k_at_a']}&amp;nt={$time}{$footer_q}";
            }
            $info_view_ht = <<<EOP
        <a href="info.php?host={$aRes->host}&amp;bbs={$aRes->bbs}&amp;key={$aRes->key}{$_conf['k_at_a']}" target="_self" onClick="return !openSubWin('info.php?host={$aRes->host}&amp;bbs={$aRes->bbs}&amp;key={$aRes->key}&amp;popup=1{$sid_q}',{$STYLE['info_pop_size']},0,0)">���</a>
EOP;

            $res_ht = "<dt><input name=\"checked_hists[]\" type=\"checkbox\" value=\"{$aRes->order},,,,{$daytime_hs}\"> ";
            
            // �ԍ�
            $res_ht .= "{$aRes->order} �F";
            
            // ���O
            $res_ht .= '<span class="name"><b>' . htmlspecialchars($aRes->name, ENT_QUOTES) . '</b></span> �F';
            
            // ���[��
            if ($aRes->mail) {
                $res_ht .= htmlspecialchars($aRes->mail, ENT_QUOTES) . ' �F';
            }
            
            // ���t��ID
            $res_ht .= "{$daytime_hs}</dt>\n";
            
            // ��
            $res_ht .= "<dd><a href=\"{$_conf['subject_php']}?host={$aRes->host}&amp;bbs={$aRes->bbs}{$_conf['k_at_a']}\" target=\"subject\">" . hs($aRes->itaj) . "</a> / ";
            
            if ($href_ht) {
                $res_ht .= "<a href=\"{$href_ht}\"><b>{$ttitle_hs}</b></a> - {$info_view_ht}\n";
            } else {
                $res_ht .= "<b>{$ttitle_hs}</b>\n";
            }
            
            $res_ht .= "<br><br>";
            
            // ���e
            $res_ht .= "{$aRes->msg}<br><br></dd>\n";

            echo $res_ht;
            flush();
        }
        
        echo '</dl>';
        
        if ($pager->links) {
            echo "<div>{$pager->links}</div>";
        }
    }
    
    /**
     * �g�їp�i�r��HTML�\������
     * �\���͈�($this->resrange)���Z�b�g�����
     *
     * @access  public
     * @param   string  $position  �\���ӏ����ʖ� 'footer', 'header'
     * @param   integer $totalNum  �A�C�e������
     * @return  void
     */
    function showNaviK($position, $totalNum)
    {
        global $_conf;

        // �\��������
        $list_disp_all_num = $totalNum;
        $list_disp_range = $_conf['k_rnum_range'];
        
        $from = isset($_GET['from']) ? $_GET['from'] : null;
        $end  = isset($_GET['end'])  ? $_GET['end']  : null;
        
        if (!empty($from)) {
            $list_disp_from = $from;
            if (!empty($end)) {
                $list_disp_range = max(1, $end - $list_disp_from + 1);
            }
        } else {
            $list_disp_from = 1;
        }
        
        $disp_navi = P2Util::getListNaviRange($list_disp_from, $list_disp_range, $list_disp_all_num);
        
        $this->resrange['start'] = $disp_navi['from'];
        $this->resrange['to'] = $disp_navi['end'];
        $this->resrange['nofirst'] = false;

        $mae_ht = '';
        if ($disp_navi['from'] > 1) {
            if ($position == "footer") {
                $mae_ht = <<<EOP
        <a {$_conf['accesskey']}="{$_conf['k_accesskey']['prev']}" href="read_res_hist.php?from={$disp_navi['mae_from']}{$_conf['k_at_a']}">{$_conf['k_accesskey']['prev']}.�O</a>
EOP;
            } else {
                $mae_ht = <<<EOP
        <a href="read_res_hist.php?from={$disp_navi['mae_from']}{$_conf['k_at_a']}">�O</a>
EOP;
            }
        }
        if ($disp_navi['end'] < $list_disp_all_num) {
            if ($position == "footer") {
                $tugi_ht = <<<EOP
        <a {$_conf['accesskey']}="{$_conf['k_accesskey']['next']}" href="read_res_hist.php?from={$disp_navi['tugi_from']}{$_conf['k_at_a']}">{$_conf['k_accesskey']['next']}.��</a>
EOP;
            } else {
                $tugi_ht = <<<EOP
        <a href="read_res_hist.php?from={$disp_navi['tugi_from']}{$_conf['k_at_a']}">��</a>
EOP;
            }
        }
        
        if (!$disp_navi['all_once']) {
            $list_navi_ht = <<<EOP
        {$disp_navi['range_st']}{$mae_ht} {$tugi_ht}
EOP;
        }

        echo $list_navi_ht;
    }
    
    /**
     * ���X�L����HTML�\������ �g�їp
     *
     * @access  public
     * @param   array
     * @return  void
     */
    function printArticlesHtmlK($datlines)
    {
        global $_conf;
        
        $hr = P2Util::getHrHtmlK();
        
        $n = 0;
        foreach ($datlines as $aline) {
            $n++;
            
            if ($n < $this->resrange['start'] or $n > $this->resrange['to']) {
                continue;
            }
            
            $aRes = $this->lineToRes($aline, $n);
            
            $daytime_hs = htmlspecialchars($aRes->daytime, ENT_QUOTES);
            
            $ttitle = html_entity_decode($aRes->ttitle, ENT_COMPAT, 'Shift_JIS');
            $ttitle_hs = hs($ttitle);
            
            $href_ht = "";
            if ($aRes->key) {
                if (empty($aRes->resnum) || $aRes->resnum == 1) {
                    $ls_q = '';
                    $footer_q = '#footer';
                } else {
                    $lf = max(1, $aRes->resnum - 0);
                    $ls_q = "&amp;ls={$lf}-";
                    $footer_q = "#r{$lf}";
                }
                $time = time();
                $href_ht = $_conf['read_php'] . "?host=" . $aRes->host . "&amp;bbs=" . $aRes->bbs . "&amp;key=" . $aRes->key . $ls_q . "{$_conf['k_at_a']}&amp;nt={$time}={$footer_q}";
            }
            
            $msg_ht = $aRes->msg;
            
            // �傫������
            if (empty($_GET['k_continue'])) {
            
                if ($_conf['ktai_res_size'] && strlen($msg_ht) > $_conf['ktai_res_size']) {
                    $msg_ht = substr($msg_ht, 0, $_conf['ktai_ryaku_size']);
                
                    // ������<br>������Ύ�菜��
                    if (substr($msg_ht, -1) == ">") {
                        $msg_ht = substr($msg_ht, 0, strlen($msg_ht)-1);
                    }
                    if (substr($msg_ht, -1) == "r") {
                        $msg_ht = substr($msg_ht, 0, strlen($msg_ht)-1);
                    }
                    if (substr($msg_ht, -1) == "b") {
                        $msg_ht = substr($msg_ht, 0, strlen($msg_ht)-1);
                    }
                    if (substr($msg_ht, -1) == "<") {
                        $msg_ht = substr($msg_ht, 0, strlen($msg_ht)-1);
                    }
                    
                    $msg_ht = $msg_ht . " <a href=\"read_res_hist?from={$aRes->order}&amp;end={$aRes->order}&amp;k_continue=1{$_conf['k_at_a']}\">��</a>";
                }
            }

            $res_ht = "[$aRes->order]"; // �ԍ�
            $res_ht .= htmlspecialchars($aRes->name, ENT_QUOTES) . ':'; // ���O
            
            // ���[��
            if ($aRes->mail) {
                $res_ht .= htmlspecialchars($aRes->mail, ENT_QUOTES) . ':';
            }
            
            // ���t��ID
            $res_ht .= "{$daytime_hs}<br>\n";
            
            // ��
            $itaj_han_hs = hs($aRes->itaj);
            $res_ht .= "<a href=\"{$_conf['subject_php']}?host={$aRes->host}&amp;bbs={$aRes->bbs}{$_conf['k_at_a']}\">{$itaj_han_hs}</a> / ";
            
            if ($href_ht) {
                $res_ht .= "<a href=\"{$href_ht}\">{$ttitle_hs}</a>\n";
            } else {
                $res_ht .= "{$ttitle_hs}\n";
            }
            
            // �폜
            // $res_ht = "<dt><input name=\"checked_hists[]\" type=\"checkbox\" value=\"{$aRes->order},,,,{$daytime_hs}\"> ";
            $from_q = isset($_GET['from']) ? '&amp;from=' . $_GET['from'] : '';
            $dele_ht = "[<a href=\"read_res_hist.php?checked_hists[]={$aRes->order},,,," . htmlspecialchars(urlencode($aRes->daytime), ENT_QUOTES) . "{$from_q}{$_conf['k_at_a']}\">�폜</a>]";
            $res_ht .= $dele_ht;
            
            $res_ht .= '<br>';
            
            // ���e
            $res_ht .= "{$msg_ht}$hr\n";
            
            if ($_conf['k_save_packet']) {
                $res_ht = mb_convert_kana($res_ht, 'rnsk');
            }
            
            echo $res_ht;
        }
    }
}

