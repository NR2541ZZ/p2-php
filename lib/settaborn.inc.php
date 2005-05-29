<?php
// p2 - �X���b�h���ځ[��̊֐�

require_once (P2_LIBRARY_DIR . '/filectl.class.php');

/**
 * �X���b�h���ځ[����I���I�t����
 *
 * $set �́A0(����), 1(�ǉ�), 2(�g�O��)
 */
function settaborn($host, $bbs, $key, $set)
{
    global $_conf, $title_msg, $info_msg;
    
    //==================================================================
    // key.idx �ǂݍ���
    //==================================================================
    
    // idxfile�̃p�X�����߂�
    $idx_host_dir = P2Util::idxDirOfHost($host);
    $idxfile = "{$idx_host_dir}/{$bbs}/{$key}.idx";
    
    // �f�[�^������Ȃ�ǂݍ���
    if (is_readable($idxfile)) {
        $lines = @file($idxfile);
        $l = rtrim($lines[0]);
        $data = explode('<>', $l);
    }
    
    //==================================================================
    // p2_threads_aborn.idx�ɏ�������
    //==================================================================
    
    // p2_threads_aborn.idx �̃p�X�擾
    $idx_host_dir = P2Util::idxDirOfHost($host);
    $taborn_idx = "{$idx_host_dir}/{$bbs}/p2_threads_aborn.idx";
    
    // p2_threads_aborn.idx ���Ȃ���ΐ���
    FileCtl::make_datafile($taborn_idx, $_conf['p2_perm']);
    
    // p2_threads_aborn.idx �ǂݍ���;
    $taborn_lines= @file($taborn_idx);
    
    $neolines = array();
    
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
    if (!empty($neolines)) {
        foreach ($neolines as $l) {
            $cont .= $l."\n";
        }
    }
    if (FileCtl::file_write_contents($taborn_idx, $cont) === false) {
        die('Error: cannot write file.');
    }
    
    $GLOBALS['title_msg'] = $title_msg_pre;
    $GLOBALS['info_msg'] = $info_msg_pre;
    
    return true;
}

?>
