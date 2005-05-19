<?php
// p2 -  ���C�ɔ̓���

require_once './brdctl.class.php';
require_once './filectl.class.php';

//================================================
// �ǂݍ���
//================================================
//favita_path�t�@�C�����Ȃ���ΏI��
if (!file_exists($_conf['favita_path'])) {
    return;
}

//favita_path�ǂݍ���;
$lines = @file($_conf['favita_path']);

//board�ǂݍ���
$_current = BrdCtl::read_brds();

//================================================
// ����
//================================================

//���X�g��P���z��ɕϊ�
$current = array();
foreach ($_current as $brdmenu) {
    foreach ($brdmenu->categories as $category) {
        foreach ($category->menuitas as $ita) {
            $current[] = "\t{$ita->host}\t{$ita->bbs}\t{$ita->itaj}\n";
        }
    }
}

// ���f�[�^�̓���
// 2ch/bbspink�̏ꍇ�A���X�g�ƌ��f�[�^��bbs�i���j�ŏƍ����āA���X�g�f�[�^�Ō��f�[�^���㏑������B
$neolines = array();
foreach ($lines as $line) {
    $data = explode("\t", rtrim($line));
    if (preg_match('/^\w+\.(2ch\.net|bbspink\.com)$/', $data[1], $matches)) {
        $grep_pattern = '/^\t\w+\.' . preg_quote($matches[1], '/') . '\t' . preg_quote($data[2], '/') . '\t/';
    } else {
        $neolines[] = $line;
        continue;
    }
    if ($findline = preg_grep($grep_pattern, $current)) {
        // itaj�͌��f�[�^��D��B$findline�͍ŏ��Ɍ����������̂𗘗p�B
        if ($data[3]) {
            $newdata = explode("\t", rtrim(array_shift($findline)));
            $neolines[] = "\t{$newdata[1]}\t{$newdata[2]}\t{$data[3]}\n";
        } else {
            $neolines[] = $findline[0];
        }
    } else {
        $neolines[] = $line;
    }
}

//================================================
// �X�V������΁A��������
//================================================
if (serialize($lines) != serialize($neolines)) {

    $cont = '';
    foreach ($neolines as $l) {
        $cont .= $l;
    }
    if (FileCtl::file_write_contents($_conf['favita_path'], $cont) === false) {
        die('Error: cannot write file.');
    }
    
    $sync_ok = true;
} else {
    $sync_ok = false;
}

?>
