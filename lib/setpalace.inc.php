<?php
require_once P2_LIB_DIR . '/filectl.class.php';

/**
 * �X����a������ɃZ�b�g����֐�
 *
 * $set �́A0(����), 1(�ǉ�), top, up, down, bottom
 *
 * @access  public
 * @return  boolean
 */
function setPal($host, $bbs, $key, $set)
{
    global $_conf;

    // key.idx �̃p�X�����߂�
    $idx_host_dir   = P2Util::idxDirOfHost($host);
    $idxfile        = $idx_host_dir . '/' . $bbs . '/' . $key . '.idx';

    // ���� key.idx �f�[�^������Ȃ�ǂݍ���
    if (file_exists($idxfile) and $lines = file($idxfile)) {
        $l = rtrim($lines[0]);
        $data = explode('<>', $l);
    }

    // p2_palace.idx�ɏ�������
    $palace_idx = $_conf['pref_dir'] . '/p2_palace.idx';

    if (false === FileCtl::make_datafile($palace_idx, $_conf['palace_perm'])) {
        return false;
    }

    if (false === $pallines = file($palace_idx)) {
        return false;
    }
    
    $neolines = array();
    $before_line_num = 0;
    
    // {{{ �ŏ��ɏd���v�f���폜���Ă���
    
    if (!empty($pallines)) {
        $i = -1;
        foreach ($pallines as $l) {
            $i++;
            $l = rtrim($l);
            $lar = explode('<>', $l);
            // �d�����
            if ($lar[1] == $key && $lar[11] == $bbs) {
                $before_line_num = $i;    // �ړ��O�̍s�ԍ����Z�b�g
                continue;
            // key�̂Ȃ����͕̂s���f�[�^�Ȃ̂ŃX�L�b�v
            } elseif (!$lar[1]) {
                continue;
            } else {
                $neolines[] = $l;
            }
        }
    }
    
    // }}}
    
    // �V�K�f�[�^�ݒ�
    if ($set) {
        $newdata = "$data[0]<>{$key}<>$data[2]<>$data[3]<>$data[4]<>$data[5]<>$data[6]<>$data[7]<>$data[8]<>$data[9]<>{$host}<>{$bbs}";
        require_once P2_LIB_DIR . '/getsetposlines.inc.php';
        $rec_lines = getSetPosLines($neolines, $newdata, $before_line_num, $set);
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
    if (false === FileCtl::filePutRename($palace_idx, $cont)) {
        $errmsg = sprintf('p2 error: %s(), FileCtl::filePutRename() failed.', __FUNCTION__);
        trigger_error($errmsg, E_USER_WARNING);
        return false;
    }
    
    return true;
}

