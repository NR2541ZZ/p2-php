<?php
require_once P2_LIB_DIR . '/filectl.class.php';

/**
 * �X���b�h���ځ[��𕡐��ꊇ��������֐�
 *
 * @access  public
 * @return  boolean  ���s����
 */
function settaborn_off($host, $bbs, $taborn_off_keys)
{
    if (!$taborn_off_keys) {
        return;
    }

    // p2_threads_aborn.idx �̃p�X�擾
    $taborn_idx = P2Util::getThreadAbornFile($host, $bbs);
    
    // p2_threads_aborn.idx ���Ȃ����
    if (!file_exists($taborn_idx)) {
        p2die("���ځ[�񃊃X�g��������܂���ł����B");
        return false;
    }
    
    // p2_threads_aborn.idx �ǂݍ���
    if (false === $taborn_lines = file($taborn_idx)) {
        return false;
    }
    
    // �w��key���폜
    foreach ($taborn_off_keys as $val) {
        
        $neolines = array();
        
        if ($taborn_lines) {
            foreach ($taborn_lines as $line) {
                $line = rtrim($line);
                $lar = explode('<>', $line);
                if ($lar[1] == $val) { // key����
                    // echo "key:{$val} �̃X���b�h�����ځ[��������܂����B<br>";
                    $kaijo_attayo = true;
                    continue;
                }
                if (!$lar[1]) { continue; } // key�̂Ȃ����͕̂s���f�[�^
                $neolines[] = $line;
            }
        }
        
        $taborn_lines = $neolines;
    }
    
    // ��������
    
    if (file_exists($taborn_idx)) {
        copy($taborn_idx, $taborn_idx . '.bak'); // �O�̂��߃o�b�N�A�b�v
    }

    $cont = '';
    if (is_array($taborn_lines)) {
        foreach ($taborn_lines as $l) {
            $cont .= $l."\n";
        }
    }
    if (false === file_put_contents($taborn_idx, $cont, LOCK_EX)) {
        p2die('Error: cannot write file.');
        return false;
    }

    /*
    if (!$kaijo_attayo) {
        // echo "�w�肳�ꂽ�X���b�h�͊��ɂ��ځ[�񃊃X�g�ɍڂ��Ă��Ȃ��悤�ł��B";
    } else {
        // echo "���ځ[������A�������܂����B";
    }
    */

    return true;
}

