<?php
/**
 * �t�@�C���𑀍삷��N���X
 * �C���X�^���X����炸�ɃN���X���\�b�h�ŗ��p����
 */
class FileCtl{
    
    /**
     * �������ݗp�̃t�@�C�����Ȃ���ΐ������ăp�[�~�b�V�����𒲐�����
     */
    function make_datafile($file, $perm = 0606)
    {
        // �O�̂��߂Ƀf�t�H���g�␳���Ă���
        if (empty($perm)) {
            $perm = 0606;
        }
        
        if (!file_exists($file)) {
            // �e�f�B���N�g����������΍��
            FileCtl::mkdir_for($file) or die("Error: cannot make parent dirs. ( $file )");
            touch($file) or die("Error: cannot touch. ( $file )");
            chmod($file, $perm);
        } else {
            if (!is_writable($file)) {
                $cont = @file_get_contents($file);
                unlink($file);
                if (FileCtl::file_write_contents($file, $cont) === false) {
                    die('Error: cannot write.');
                }
                chmod($file, $perm);
            }
        }
        return true;
    }
    
    /**
     * �e�f�B���N�g�����Ȃ���ΐ������ăp�[�~�b�V�����𒲐�����
     */
    function mkdir_for($apath)
    {
        global $_conf;
        
        $dir_limit = 50; // �e�K�w����鐧����
        
        $perm = (!empty($_conf['data_dir_perm'])) ? $_conf['data_dir_perm'] : 0707;

        if (!$parentdir = dirname($apath)) {
            die("Error: cannot mkdir. ( {$parentdir} )<br>�e�f�B���N�g�����󔒂ł��B");
        }
        $i = 1;
        if (!is_dir($parentdir)) {
            if ($i > $dir_limit) {
                die("Error: cannot mkdir. ( {$parentdir} )<br>�K�w���オ��߂����̂ŁA�X�g�b�v���܂����B");
            }
            FileCtl::mkdir_for($parentdir);
            mkdir($parentdir, $perm) or die("Error: cannot mkdir. ( {$parentdir} )");
            chmod($parentdir, $perm);
            $i++;
        }
        return true;
    }
    
    /**
     * gz�t�@�C���̒��g���擾����
     */
    function get_gzfile_contents($filepath)
    {
        if (is_readable($filepath)) {
            ob_start();
            readgzfile($filepath);
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        } else {
            return false;
        }
    }
    
    /**
     * ��������t�@�C���ɏ�������
     * �iPHP5��file_put_contents�̑�֓I�����j
     *
     * ����function�́APHP License �Ɋ�Â��AAidan Lister�� <aidan@php.net> �ɂ��A
     * PHP_Compat �� file_put_contents.php �̃R�[�h�����ɁA�Ǝ��̕ύX�iflock() �Ȃǁj�����������̂ł��B
     * This product includes PHP, freely available from <http://www.php.net/>
     */
    function file_write_contents($filename, &$cont, $flags = null, $resource_context = null)
    {
        // If $cont is an array, convert it to a string
        if (is_array($cont)) {
            $content = implode('', $cont);
        } else {
            $content =& $cont;
        }
        
        /*
        shift_jis�̕������r������������肷��ƁAstring�ł͂Ȃ����f����邱�Ƃ�����H
        // If we don't have a string, throw an error
        if (!is_string($content)) {
            trigger_error('file_write_contents() '.$filename.', The 2nd parameter should be either a string or an array', E_USER_WARNING);
            return false;
        }
        */
        
        // Get the length of date to write
        $length = strlen($content);
        
        // Check what mode we are using
        $mode = ($flags & FILE_APPEND) ?
                    $mode = 'ab' :
                    $mode = 'wb';
        
        // Check if we're using the include path
        $use_inc_path = ($flags & FILE_USE_INCLUDE_PATH) ?
                    true :
                    false;
        
        // Open the file for writing
        if (($fh = @fopen($filename, $mode, $use_inc_path)) === false) {
            trigger_error('file_write_contents() '.$filename.', failed to open stream: Permission denied', E_USER_WARNING);
            return false;
        }
        
        @flock($fh, LOCK_EX);
        $last = ignore_user_abort(1);
        
        if ($mode == 'wb') {
            ftruncate($fh, 0);
        }
        
        // Write to the file
        $bytes = 0;
        if (($bytes = @fwrite($fh, $content)) === false) {
            $errormsg = sprintf('file_write_contents() Failed to write %d bytes to %s',
                            $length,
                            $filename);
            trigger_error($errormsg, E_USER_WARNING);
            ignore_user_abort($last);
            return false;
        }
        
        ignore_user_abort($last);
        @flock($fh, LOCK_UN);
        fclose($fh);
        
        if ($bytes != $length) {
            $errormsg = sprintf('file_put_contents() Only %d of %d bytes written, possibly out of free disk space.',
                            $bytes,
                            $length);
            trigger_error($errormsg, E_USER_WARNING);
            return false;
        }
        
        return $bytes;
    }
}
?>
