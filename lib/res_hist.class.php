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
    var $articles; // �N���X ResArticle �̃I�u�W�F�N�g���i�[����z��

    var $resrange; // array( 'start' => i, 'to' => i, 'nofirst' => bool )

    /**
     * �R���X�g���N�^
     */
    function ResHist()
    {
        $this->articles = array();
    }

    /**
     * �������݃��O�� lines ���p�[�X���ēǂݍ���
     * �i�S�s�������Ă���̂͂�����Ɩ��ʂ���j
     *
     * @access  public
     * @param   array    $lines
     * @return  boolean  ��������
     */
    function readLines($lines)
    {
        if (!is_array($lines)) {
            trigger_error(__FUNCTION__ . '(), ' . 'illegal argument', E_USER_WARNING);
            return false;
        }

        $n = 1;
        foreach ($lines as $aline) {
            $aResArticle = $this->lineToRes($aline, $n);
            $this->addRes($aResArticle);

            $n++;
        }
        return true;
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
        if (!$aResArticle->itaj  = P2Util::getItaName($aResArticle->host, $aResArticle->bbs)) {
            $aResArticle->itaj = $aResArticle->bbs;
        }
        $aResArticle->key   = $resar[7];
        $aResArticle->resnum = $resar[8];

        $aResArticle->order = $order;

        return $aResArticle;
    }

    /**
     * ���X��ǉ�����
     *
     * @access  private
     * @return  void
     */
    function addRes($aResArticle)
    {
        $this->articles[] = $aResArticle;
    }

    /**
     * ���X�L����\������ PC�p
     *
     * @access  public
     * @return  void
     */
    function showArticles($datlines)
    {
        global $_conf, $STYLE;

        $sid_q = defined('SID') ? '&amp;' . strip_tags(SID) : '';

        // Pager ����
        require_once 'Pager.php';
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

        $pager = & Pager::factory($params);
        $links = $pager->getLinks();
        $data  = $pager->getPageData();

        if ($pager->links) {
            echo "<div>{$pager->links}</div>";
        }

        echo '<dl>';

        $pageID = max(1, intval($_REQUEST['pageID']));
        $n = ($pageID - 1) * $perPage;
        foreach ($data as $aline) {
            $n++;

            $a_res = $this->lineToRes($aline, $n);

            $hd['daytime'] = htmlspecialchars($a_res->daytime, ENT_QUOTES);
            $hd['ttitle'] = htmlspecialchars(html_entity_decode($a_res->ttitle, ENT_COMPAT, 'Shift_JIS'), ENT_QUOTES);
            $hd['itaj'] = htmlspecialchars($a_res->itaj, ENT_QUOTES);

            $href_ht = '';
            if ($a_res->key) {
                if (empty($a_res->resnum) || $a_res->resnum == 1) {
                    $ls_q = '';
                    $footer_q = '#footer';
                } else {
                    $lf = max(1, $a_res->resnum - 0);
                    $ls_q = "&amp;ls={$lf}-";
                    $footer_q = "#r{$lf}";
                }
                $time = time();
                $href_ht = $_conf['read_php'] . "?host=" . $a_res->host . "&amp;bbs=" . $a_res->bbs . "&amp;key=" . $a_res->key . $ls_q . "{$_conf['k_at_a']}&amp;nt={$time}{$footer_q}";
            }
            $info_view_ht = <<<EOP
        <a href="info.php?host={$a_res->host}&amp;bbs={$a_res->bbs}&amp;key={$a_res->key}{$_conf['k_at_a']}" target="_self" onClick="return OpenSubWin('info.php?host={$a_res->host}&amp;bbs={$a_res->bbs}&amp;key={$a_res->key}&amp;popup=1{$sid_q}',{$STYLE['info_pop_size']},0,0)">���</a>
EOP;

            $res_ht = "<dt><input name=\"checked_hists[]\" type=\"checkbox\" value=\"{$a_res->order},,,,{$hd['daytime']}\"> ";
            $res_ht .= "{$a_res->order} �F"; // �ԍ�
            $res_ht .= '<span class="name"><b>' . htmlspecialchars($a_res->name, ENT_QUOTES) . '</b></span> �F'; // ���O
            // ���[��
            if ($a_res->mail) {
                $res_ht .= htmlspecialchars($a_res->mail, ENT_QUOTES) . ' �F';
            }
            $res_ht .= "{$hd['daytime']}</dt>\n"; // ���t��ID
            // ��
            $res_ht .= "<dd><a href=\"{$_conf['subject_php']}?host={$a_res->host}&amp;bbs={$a_res->bbs}{$_conf['k_at_a']}\" target=\"subject\">{$hd['itaj']}</a> / ";
            if ($href_ht) {
                $res_ht .= "<a href=\"{$href_ht}\"><b>{$hd['ttitle']}</b></a> - {$info_view_ht}\n";
            } elseif ($hd['ttitle']) {
                $res_ht .= "<b>{$hd['ttitle']}</b>\n";
            }
            $res_ht .= "<br><br>";
            $res_ht .= "{$a_res->msg}<br><br></dd>\n"; // ���e

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
     * �\���͈͂��Z�b�g�����
     *
     * @access  public
     * @return  void
     */
    function showNaviK($position, $num)
    {
        global $_conf;

        // �\��������
        $list_disp_all_num = $num;
        $list_disp_range = $_conf['k_rnum_range'];

        if (!empty($_GET['from'])) {
            $list_disp_from = $_GET['from'];
            if (!empty($_GET['end'])) {
                $list_disp_range = $_GET['end'] - $list_disp_from + 1;
                if ($list_disp_range < 1) {
                    $list_disp_range = 1;
                }
            }
        } else {
            $list_disp_from = 1;
        }

        $disp_navi = P2Util::getListNaviRange($list_disp_from, $list_disp_range, $list_disp_all_num);

        $this->resrange['start'] = $disp_navi['from'];
        $this->resrange['to'] = $disp_navi['end'];
        $this->resrange['nofirst'] = false;

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
     * ���X�L����HTML�\�����郁�\�b�h �g�їp
     *
     * @access  public
     * @return  void
     */
    function showArticlesK($datlines)
    {
        global $_conf;

        $n = 0;
        foreach ($datlines as $aline) {
            $n++;

            if ($n < $this->resrange['start'] or $n > $this->resrange['to']) {
                continue;
            }

            $a_res = $this->lineToRes($aline, $n);

            $hd['daytime'] = htmlspecialchars($a_res->daytime, ENT_QUOTES);
            $hd['ttitle'] = htmlspecialchars(html_entity_decode($a_res->ttitle, ENT_COMPAT, 'Shift_JIS'), ENT_QUOTES);
            $hd['itaj'] = htmlspecialchars($a_res->itaj, ENT_QUOTES);

            $href_ht = '';
            if ($a_res->key) {
                if (empty($a_res->resnum) || $a_res->resnum == 1) {
                    $ls_q = '';
                    $footer_q = '#footer';
                } else {
                    $lf = max(1, $a_res->resnum - 0);
                    $ls_q = "&amp;ls={$lf}-";
                    $footer_q = "#r{$lf}";
                }
                $time = time();
                $href_ht = $_conf['read_php'] . "?host=" . $a_res->host . "&amp;bbs=" . $a_res->bbs . "&amp;key=" . $a_res->key . $ls_q . "{$_conf['k_at_a']}&amp;nt={$time}={$footer_q}";
            }

            // �傫������
            if (!$_GET['k_continue']) {
                $msg = $a_res->msg;
                if (strlen($msg) > $_conf['ktai_res_size']) {
                    $msg = substr($msg, 0, $_conf['ktai_ryaku_size']);

                    // ������<br>������Ύ�菜��
                    if (substr($msg, -1) == ">") {
                        $msg = substr($msg, 0, strlen($msg)-1);
                    }
                    if (substr($msg, -1) == "r") {
                        $msg = substr($msg, 0, strlen($msg)-1);
                    }
                    if (substr($msg, -1) == "b") {
                        $msg = substr($msg, 0, strlen($msg)-1);
                    }
                    if (substr($msg, -1) == "<") {
                        $msg = substr($msg, 0, strlen($msg)-1);
                    }

                    $msg .= '  ';
                    $a_res->msg = $msg . "<a href=\"read_res_hist?from={$a_res->order}&amp;end={$a_res->order}&amp;k_continue=1{$_conf['k_at_a']}\">��</a>";
                }
            }

            $res_ht = "[$a_res->order]"; // �ԍ�
            $res_ht .= htmlspecialchars($a_res->name, ENT_QUOTES) . ':'; // ���O
            // ���[��
            if ($a_res->mail) {
                $res_ht .= htmlspecialchars($a_res->mail, ENT_QUOTES) . ':';
            }
            $res_ht .= "{$hd['daytime']}<br>\n"; // ���t��ID
            $res_ht .= "<a href=\"{$_conf['subject_php']}?host={$a_res->host}&amp;bbs={$a_res->bbs}{$_conf['k_at_a']}\">{$hd['itaj']}</a> / ";
            if ($href_ht) {
                $res_ht .= "<a href=\"{$href_ht}\">{$hd['ttitle']}</a>\n";
            } elseif ($hd['ttitle']) {
                $res_ht .= "{$hd['ttitle']}\n";
            }

            // �폜
            //$res_ht = "<dt><input name=\"checked_hists[]\" type=\"checkbox\" value=\"{$a_res->order},,,,{$hd['daytime']}\"> ";
            $from_q = isset($_GET['from']) ? '&amp;from=' . $_GET['from'] : '';
            $dele_ht = "[<a href=\"read_res_hist.php?checked_hists[]={$a_res->order},,,," . htmlspecialchars(urlencode($a_res->daytime), ENT_QUOTES) . "{$from_q}{$_conf['k_at_a']}\">�폜</a>]";
            $res_ht .= $dele_ht;

            $res_ht .= '<br>';
            $res_ht .= "{$a_res->msg}<hr>\n"; // ���e

            echo $res_ht;
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
