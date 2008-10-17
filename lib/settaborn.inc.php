<?php
require_once P2_LIB_DIR . '/filectl.class.php';

/**
 * �X���b�h���ځ[����I���I�t����֐�
 *
 * $set �́A0(����), 1(�ǉ�), 2(�g�O��)
 *
 * @access  public
 * @return  boolean  ���s����
 */
function settaborn($host, $bbs, $key, $set)
{
    global $_conf, $title_msg, $info_msg;

    // {{{ key.idx �ǂݍ���
    
    $idx_host_dir = P2Util::idxDirOfHost($host);
    $idxfile = "{$idx_host_dir}/{$bbs}/{$key}.idx";
    
    $data[0] = null;
    
    // �f�[�^������Ȃ�ǂݍ���
    if (file_exists($idxfile)) {
        $lines = file($idxfile);
        $l = rtrim($lines[0]);
        $data = explode('<>', $l);
    }
    
    // }}}

    // p2_threads_aborn.idx �̃p�X�擾
    $taborn_idx = P2Util::getThreadAbornFile($host, $bbs);
    
    if (false === FileCtl::make_datafile($taborn_idx, $_conf['p2_perm'])) {
        return false;
    }
    
    if (false === $taborn_lines = file($taborn_idx)) {
        return false;
    }
    
    $neolines = array();
    
    $aborn_attayo = false;
    $title_msg_pre = '';
    $info_msg_pre = '';
    
    if ($taborn_lines) {
        foreach ($taborn_lines as $line) {
            $line = rtrim($line);
            $lar = explode('<>', $line);
            if ($lar[1] == $key) {
                $aborn_attayo = true; // ���ɂ��ځ[�񒆂ł���
                if ($set == 0 or $set == 2) {
                    $title_msg_pre = "+ ���ځ[�� �������܂���";
                    $info_msg_pre = "+ ���ځ[�� �������܂���";
                }
                continue;
            }
            if (!$lar[1]) { continue; } // key�̂Ȃ����͕̂s���f�[�^
            $neolines[] = $line;
        }
    }
    
    // �V�K�f�[�^�ǉ�
    if ($set == 1 or !$aborn_attayo && $set == 2) {
        $newdata = "$data[0]<>{$key}<><><><><><><><>";
        $neolines ? array_unshift($neolines, $newdata) : $neolines = array($newdata);
        $title_msg_pre = "�� ���ځ[�� ���܂���";
        $info_msg_pre = "�� ���ځ[�� ���܂���";
    }
    
    // ��������
    $cont = '';
    if ($neolines) {
        foreach ($neolines as $l) {
            $cont .= $l . "\n";
        }
    }
    if (false === file_put_contents($taborn_idx, $cont, LOCK_EX)) {
        p2die('cannot write file.');
        return false;
    }
    
    $GLOBALS['title_msg'] = $title_msg_pre;
    $GLOBALS['info_msg'] = $info_msg_pre;
    
    return true;
}

