<?php
require_once P2_LIB_DIR . '/FileCtl.php';
require_once P2_LIB_DIR . '/P2Validate.php';

/**
 * ���C�ɔ��Z�b�g����֐�
 *
 * $setfavita �́A0(����), 1(�ǉ�), top, up, down, bottom
 *
 * @access  public
 * @return  boolean
 */
function setFavIta()
{
    global $_conf;

    // {{{ �p�����[�^�̐ݒ�
    
    if (isset($_GET['setfavita'])) {
        $setfavita = $_GET['setfavita'];
    } elseif (isset($_POST['setfavita'])) {
        $setfavita = $_POST['setfavita'];
    }

    $host = isset($_GET['host']) ? $_GET['host'] : NULL;
    $bbs  = isset($_GET['bbs'])  ? $_GET['bbs']  : NULL;

    if (isset($setfavita) && !preg_match('/^[a-zA-Z0-9]+$/', $setfavita)) {
        P2Util::pushInfoHtml('<p>p2 info: �s���Ȉ����ł��isetfavita�j');
        return false;
    }
    if (isset($host) && P2Validate::host($host) || isset($bbs) && P2Validate::bbs($bbs)) {
        P2Util::pushInfoHtml("<p>p2 info: �̎w�肪�ςł�</p>");
        return false;
    }
    
    if (!empty($_POST['url'])) {
        if (preg_match("/http:\/\/(.+)\/([^\/]+)\/([^\/]+\.html?)?/", $_POST['url'], $matches)) {
            $host = preg_replace('{/test/read\.cgi$}', '', $matches[1]);
            $bbs = $matches[2];
        } else {
            P2Util::pushInfoHtml(sprintf("<p>p2 info: �u%s�v�͔�URL�Ƃ��Ė����ł��B</p>", hs($_POST['url'])));
        }
    }
    
    $list = isset($_POST['list']) ? $_POST['list'] : '';
    
    // ���X�g�ŕ��ёւ�
    if (!empty($_POST['submit_listfavita'])) {
        if (!$list) {
            P2Util::pushInfoHtml("<p>p2 info: ���X�g�̎w�肪�ςł�</p>");
            return false;
        }
    } else {
        // �V�K�ǉ� or ������ёւ�
        if (!$host || !$bbs) {
            P2Util::pushInfoHtml("<p>p2 info: �̎w�肪�ςł�</p>");
            return false;
        }
    }
    
    $itaj = isset($_POST['itaj']) ? $_POST['itaj'] : '';
    
    if (!$itaj && isset($_GET['itaj_en'])) {
        $itaj = base64_decode($_GET['itaj_en']);
    } 
    !$itaj and $itaj = $bbs;
    
    // }}}
    
    //================================================
    // ����
    //================================================

    if (false === FileCtl::make_datafile($_conf['favita_path'], $_conf['favita_perm'])) {
        return false;
    }
    
    if (false === $lines = file($_conf['favita_path'])) {
        return false;
    }
    
    $neolines = array();
    $before_line_num = 0;
    
    // �ŏ��ɏd���v�f���������Ă���
    if ($lines) {
        $i = -1;
        $avoided = false;
        foreach ($lines as $l) {
            $i++;
            $l = rtrim($l);
        
            // {{{ ���f�[�^�iver0.6.0�ȉ��j�ڍs�[�u
            if (!preg_match("/^\t/", $l)) {
                $l = "\t" . $l;
            }
            // }}}
        
            $lar = explode("\t", $l);
        
            if (!$avoided and $lar[1] == $host && $lar[2] == $bbs) { // �d�����
                $avoided = true;
                $before_line_num = $i;
                continue;
            } elseif (!$lar[1] || !$lar[2]) { // �s���f�[�^�ihost, bbs�Ȃ��j���A�E�g
                continue;
            } elseif (P2Validate::host($lar[1]) || P2Validate::bbs($lar[2])) {
                continue;
                
            } else {
                $neolines[] = $l;
            }
        }
    }

    // �L�^�f�[�^�ݒ�
    
    // ���X�g�ۂ��ƃ|�X�g���Ďw��
    if (!empty($_POST['submit_listfavita']) && $list) {
        $rec_lines = array();
        foreach (explode(',', $list) as $aList) {
            list($host, $bbs, $itaj_en) = array_map('rawurldecode', explode('@', $aList, 3));
            $itaj = base64_decode($itaj_en);
            $rec_lines[] = "\t{$host}\t{$bbs}\t" . $itaj;
        }
        P2Util::pushInfoHtml("<script language=\"javascript\">if (parent.menu) { parent.menu.location.href='{$_conf['menu_php']}?nr=1'; }</script>");
    
    // ��̃f�[�^���w�肵�đ���
    } elseif ($setfavita and $host && $bbs && $itaj) {
        $newdata = "\t{$host}\t{$bbs}\t{$itaj}";
        require_once P2_LIB_DIR . '/getSetPosLines.func.php';
        $rec_lines = getSetPosLines($neolines, $newdata, $before_line_num, $setfavita);
    
    // ����
    } else {
        $rec_lines = $neolines;
    }

    $cont = '';
    if (!empty($rec_lines)) {
        foreach ($rec_lines as $l) {
            $cont .= $l . "\n";
        }
    }

    // ��������
    if (false === file_put_contents($_conf['favita_path'], $cont, LOCK_EX)) {
        trigger_error("file_put_contents(" . $_conf['favita_path'] . ")", E_USER_WARNING);
        die('Error: cannot write file.');
        return false;
    }
    
    return true;
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
