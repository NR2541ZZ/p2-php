<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */

// $search_path������s�t�@�C��$command����������
// ������΃p�X���G�X�P�[�v���ĕԂ��i$escape���U�Ȃ炻�̂܂ܕԂ��j
// ������Ȃ����FALSE��Ԃ�
function findexec($command, $search_path = '', $escape = TRUE)
{
    // Windows���A���̑���OS��
    if (substr(PHP_OS, 0, 3) == 'WIN') {
        if (strtolower(strrchr($command, '.')) != '.exe') {
            $command .= '.exe';
        }
        $check = function_exists('is_executable') ? 'is_executable' : 'file_exists';
    } else {
        $check = 'is_executable';
    }
    // $search_path����̂Ƃ��͊��ϐ�PATH���猟������
    if ($search_path == '') {
        $search_dirs = explode(PATH_SEPARATOR, getenv('PATH'));
    } else {
        $search_dirs = explode(PATH_SEPARATOR, $search_path);
    }
    // ����
    foreach ($search_dirs as $path) {
        $path = realpath($path);
        if ($path === FALSE || !is_dir($path)) {
            continue;
        }
        if ($check($path . DIRECTORY_SEPARATOR . $command)) {
            return ($escape ? escapeshellarg($command) : $command);
        }
    }
    // ������Ȃ�����
    return FALSE;
}

?>
