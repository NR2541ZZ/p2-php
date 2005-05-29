<?php
/*
    p2 - for read_new.php, read_new_k.php
*/

require_once (P2_LIBRARY_DIR . '/filectl.class.php');

//===============================================
// �֐�
//===============================================
/**
 * �V���܂Ƃߓǂ݂̃L���b�V�����c��
 *
 * register_shutdown_function() ����Ă΂��B�i���΃p�X�̃t�@�C���͈����Ȃ��H�j
 */
function saveMatomeCache()
{
    global $_conf;
    
    if (!empty($GLOBALS['pref_dir_realpath_failed_msg'])) {
        return false;
    }
    
    if (!empty($GLOBALS['matome_naipo'])) {
        return true;
    }
    
    // ���[�e�[�V����
    $max = $_conf['matome_cache_max'];
    $i = $max;
    while ($i >= 0) {
        $di = ($i == 0) ? '' : '.'.$i;
        $tfile = $_conf['matome_cache_path'].$di.$_conf['matome_cache_ext'];
        $next = $i + 1;
        $nfile = $_conf['matome_cache_path'].'.'.$next.$_conf['matome_cache_ext'];
        if (file_exists($tfile)) {
            if ($i == $max) {
                unlink($tfile);
            } else {
                rename($tfile, $nfile);
            }
        }
        $i--;
    }
    
    // �V�K�L�^
    $file = $_conf['matome_cache_path'].$_conf['matome_cache_ext'];
    //echo "<!-- {$file} -->";

    FileCtl::make_datafile($file, $_conf['p2_perm']);
    if (FileCtl::file_write_contents($file, $GLOBALS['read_new_html']) === false) {
        die('Error: cannot write file.');
    }
    
    return true;
}

/**
 * �V���܂Ƃߓǂ݂̃L���b�V�����擾
 */
function getMatomeCache($num = '')
{
    global $_conf;
    
    $dnum = ($num) ? '.'.$num : '';
    $file = $_conf['matome_cache_path'].$dnum.$_conf['matome_cache_ext'];
    
    $cont = @file_get_contents($file);
    
    if (strlen($cont) > 0) {
        return $cont;
    } else {
        return false;
    }
}

?>
