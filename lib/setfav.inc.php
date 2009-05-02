<?php
/*
    p2 - ���C�ɃX���֌W�̏����X�N���v�g

    ���C�ɃX���̒ǉ��폜��A�����ύX�ŌĂ΂��
    
    2005/03/10
    �X���b�h�\�����̕��׌y����ړI�Ƃ��āA�i�����������ׂł͂Ȃ��C�����邪�j
    �X���b�h.idx�ł����C�ɃX�����������ƂƂ���B
    subject�ł��C�ɃX���ꗗ�\�� �� favlist.idx ���Q��
    �X���b�h�\�����̂��C�ɃX���\�� �� �X���b�h.idx ���Q��
*/

require_once P2_LIB_DIR . '/FileCtl.php';
require_once P2_LIB_DIR . '/P2Validate.php';

/**
 * ���C�ɃX�����Z�b�g����֐�
 *
 * $setfav �́A'0'(����), '1'(�ǉ�), 'top', 'up', 'down', 'bottom'
 *
 * @access  public
 * @return  boolean
 */
function setFav($host, $bbs, $key, $setfav)
{
    global $_conf;

    if (P2Validate::host($host) || P2Validate::bbs($bbs) || P2Validate::key($key)) {
        return false;
    }

    // �X���b�h.idx �L�^
    $data = _setFavToKeyIdx($host, $bbs, $key, $setfav);

    $newlines = array();
    $before_line_num = 0;

    if (false === FileCtl::make_datafile($_conf['favlist_file'], $_conf['favlist_perm'])) {
        return false;
    }

    if (false === $favlines = file($_conf['favlist_file'])) {
        return false;
    }
    
    // �ŏ��ɏd���v�f���폜���Ă���
    if (!empty($favlines)) {
        $i = -1;
        foreach ($favlines as $line) {
            $i++;
            $line = rtrim($line);
            $lar = explode('<>', $line);
            // �d�����
            if ($lar[1] == $key && $lar[11] == $bbs) {
                $before_line_num = $i; // �ړ��O�̍s�ԍ����Z�b�g
                continue;
            // key�̂Ȃ����͕̂s���f�[�^�Ȃ̂ŃX�L�b�v
            } elseif (!$lar[1]) {
                continue;
            } elseif (P2Validate::host($lar[10]) || P2Validate::bbs($lar[11]) || P2Validate::key($lar[1])) {
                continue;
            } else {
                $newlines[] = $line;
            }
        }
    }

    if (!empty($GLOBALS['brazil'])) {
        //$newlines = _removeLargeFavlistData($newlines);
    }
    
    // �L�^�f�[�^�ݒ�
    if ($setfav) {
        $newdata = implode('<>', array(
            geti($data[0]), $key, geti($data[2]), geti($data[3]), geti($data[4]), geti($data[5]),
            1, geti($data[7]), geti($data[8]), geti($data[9]), $host, $bbs
        ));
        require_once P2_LIB_DIR . '/getSetPosLines.func.php';
        $rec_lines = getSetPosLines($newlines, $newdata, $before_line_num, $setfav);
    } else {
        $rec_lines = $newlines;
    }
    
    if (false === file_put_contents(
            $_conf['favlist_file'],
            $rec_lines ? implode("\n", $rec_lines) . "\n" : '',
            LOCK_EX
        )
    ) {
        trigger_error(
            sprintf('file_put_contents(%s)', $_conf['favlist_file']),
            E_USER_WARNING
        );
        return false;
    }

    // ���C�ɃX�����L
    _setFavRank($host, $bbs, $key, $setfav, geti($data[0]));
    
    return true;
}

/**
 * ���C�ɃX���̓o�^�����߃f�[�^���폜
 *
 * @return  void
 */
function _removeLargeFavlistData($newlines, $max = 500)
{
    if ($removelines = array_slice($newlines, $max)) {
        for ($i = 0; $i < count($removelines); $i++) {
            $d = explode('<>', $removelines[$i]);
            _setFavToKeyIdx($d[10], $d[11], $d[1], '0');
        }
        
        // �������O�p
        if (count($removelines) > 1) {
            trigger_error(
                sprintf("%s() %d", __FUNCTION__, count($newlines)),
                E_USER_WARNING
            );
        }
    }
    
    return array_slice($newlines, 0, $max);
}

/**
 * @return  array  �ǂݍ���key�f�[�^
 */
function _setFavToKeyIdx($host, $bbs, $key, $setfav)
{
    $idxfile = P2Util::getKeyIdxFilePath($host, $bbs, $key);
    
    // FileCtl::mkdirFor($idxfile);

    $data = array();
    
    // ����idx�f�[�^������Ȃ�ǂݍ���
    if (file_exists($idxfile) and $lines = file($idxfile)) {
        $l = rtrim($lines[0]);
        $data = explode('<>', $l);
    }

    // �X���b�h.idx �L�^
    if ($setfav == '0' || $setfav == '1') {
    
        // ���C�ɃX������O�������ʁAidx�̈Ӗ����Ȃ��Ȃ�΍폜����
        if ($setfav == '0' and (empty($data[3]) && empty($data[4]) && geti($data[9]) <= 1)) {
            @unlink($idxfile);
        } else {
            P2Util::recKeyIdx($idxfile, array(
                geti($data[0]), $key, geti($data[2]), geti($data[3]), geti($data[4]),
                geti($data[5]), $setfav, geti($data[7]), geti($data[8]), geti($data[9]),
                $host, $bbs, geti($data[12])
            ));
        }
    }
    
    return $data;
}

/**
 * ���C�ɃX�����L
 *
 * @return  boolean|null
 */
function _setFavRank($host, $bbs, $key, $setfav, $ttitle)
{
    global $_conf;
    
    if (!$_conf['join_favrank']) {
        return null;
    }
    
    if ($setfav == '0') {
        $act = 'out';
    } elseif ($setfav == '1') {
        $act = 'add';
    } else {
        return null;
    }
    
    return _postFavRank(array(
        'host' => $host, 'bbs' => $bbs, 'key' => $key,
        'ttitle' => $ttitle,
        'ita' => P2Util::getItaName($host, $bbs),
        'act' => $act
    ));
}

/**
 * ���C�ɃX�����L�Ń|�X�g����֐�
 *
 * @return  boolean
 */
function _postFavRank($post)
{
    global $_conf;

    $method = "POST";
    $httpua = "Monazilla/1.00 (" . $_conf['p2uaname'] . "/" . $_conf['p2version'] . ")";
    
    $URL = parse_url($_conf['favrank_url']);
    if (isset($URL['query'])) {
        $URL['query'] = "?" . $URL['query'];
    } else {
        $URL['query'] = "";
    }

    // �v���L�V
    if ($_conf['proxy_use']) {
        $send_host = $_conf['proxy_host'];
        $send_port = $_conf['proxy_port'];
        $send_path = $url;
    } else {
        $send_host = $URL['host'];
        $send_port = $URL['port'];
        $send_path = $URL['path'].$URL['query'];
    }
    
    if (!$send_port) { $send_port = 80; }
    
    $request = "$method $send_path HTTP/1.0" . "\r\n";
    $request .= "Host: " . $URL['host'] . "\r\n";
    $request .= "User-Agent: " . $httpua . "\r\n";
    $request .= "Connection: Close\r\n";

    // POST�̎��̓w�b�_��ǉ����Ė�����URL�G���R�[�h�����f�[�^��Y�t
    if (strtoupper($method) == "POST") {
        while (list($name, $value) = each($post)) {
            $POST[] = $name . "=" . urlencode($value);
        }
        $postdata = implode("&", $POST);
        $request .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $request .= "Content-Length: " . strlen($postdata) . "\r\n";
        $request .= "\r\n";
        $request .= $postdata;
    } else {
        $request .= "\r\n";
    }

    $fp = fsockopen($send_host, $send_port, $errno, $errstr, 3);
    if (!$fp) {
        //echo "�T�[�o�ڑ��G���[: $errstr ($errno)<br>\n";
        //echo "p2 info: " . hs($_conf['favrank_url']) . " �ɐڑ��ł��܂���ł����B<br>";
        return false;
    }

    fputs($fp, $request);
    fclose($fp);
    
    return true;
    //return $body;
}
