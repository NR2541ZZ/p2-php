<?php
/*
    p2 - for read_new.php, read_new_k.php
*/

require_once P2_LIB_DIR . '/FileCtl.php';

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
    
    if (!empty($GLOBALS['_is_matome_shinchaku_naipo'])) {
        return true;
    }
    
    // ���[�e�[�V����
    $max = $_conf['matome_cache_max'];
    $i = $max;
    while ($i >= 0) {
        $di = ($i == 0) ? '' : '.' . $i;
        $tfile = $_conf['matome_cache_path'] . $di.$_conf['matome_cache_ext'];
        $next = $i + 1;
        $nfile = $_conf['matome_cache_path'] . '.' . $next.$_conf['matome_cache_ext'];
        if (file_exists($tfile)) {
            if ($i == $max) {
                unlink($tfile);
            } else {
                if (strstr(PHP_OS, 'WIN') and file_exists($nfile)) {
                    unlink($nfile);
                }
                rename($tfile, $nfile);
            }
        }
        $i--;
    }
    
    // �V�K�L�^
    $file = $_conf['matome_cache_path'] . $_conf['matome_cache_ext'];
    //echo "<!-- {$file} -->";

    FileCtl::make_datafile($file, $_conf['p2_perm']);
    if (false === file_put_contents($file, $GLOBALS['_read_new_html'], LOCK_EX)) {
        trigger_error(sprintf('file_put_contents(%s)', $file), E_USER_WARNING);
        die('Error: cannot write file.');
        return false;
    }
    
    return true;
}

/**
 * �V���܂Ƃߓǂ݂̃L���b�V�����擾����
 *
 * @return string|null|false
 */
function getMatomeCache($num = '')
{
    global $_conf;
    
    $dnum = $num ? '.' . $num : '';
    $file = $_conf['matome_cache_path'] . $dnum . $_conf['matome_cache_ext'];
    
    if (file_exists($file)) {
        $cont = file_get_contents($file);
    } else {
        return null;
    }
    
    if (strlen($cont) > 0) {
        return $cont;
    } else {
        return false;
    }
}

