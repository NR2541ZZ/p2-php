<?php
!defined('FILE_USE_INCLUDE_PATH')  and define('FILE_USE_INCLUDE_PATH', 1);
!defined('FILE_APPEND')            and define('FILE_APPEND', 8);

/**
 * �t�@�C���𑀍삷��N���X
 * �C���X�^���X����炸�ɃX�^�e�B�b�N���\�b�h�ŗ��p����
 */
class FileCtl
{
    /**
     * �t�@�C�����Ȃ���ΐ������A�������݌������Ȃ���΃p�[�~�b�V�����𒲐�����
     * �i���Ƀt�@�C��������A�������݌���������ꍇ�́A�������Ȃ��B��modified�̍X�V�����Ȃ��j
     *
     * @param   boolean  $die  true �Ȃ�A�G���[�ł������ɏI������
     * @return  boolean  ��肪�Ȃ����true
     */
    function make_datafile($file, $perm = 0606, $die = true)
    {
        $me = __CLASS__ . "::" . __FUNCTION__ . "()";

        // �����`�F�b�N
        if (strlen($file) == 0) {
            trigger_error("$me, file is null", E_USER_WARNING);
            return false;
        }
        if (empty($perm)) {
            trigger_error("$me, empty perm. ( $file )", E_USER_WARNING);
            $die and die("Error: $me, empty perm");
            return false;
        }

        // �t�@�C�����Ȃ����
        if (!file_exists($file)) {
            if (!FileCtl::mkdir_for($file)) {
                $die and die("Error: $me -> FileCtl::mkdir_for() failed.");
                return false;
            }
            if (!touch($file)) {
                $die and die("Error: $me -> touch() failed.");
                return false;
            }
            chmod($file, $perm);

        // �t�@�C���������
        } else {
            if (!is_writable($file)) {
                $cont = file_get_contents($file);
                if ($cont === false) {
                    $die and die("Error: $me -> file_get_contents() failed.");
                    return false;
                }
                unlink($file);
                if (file_put_contents($file, $cont, LOCK_EX) === false) {
                    // ���Y����: $file �� null�̎��Afile_put_contents() ��false��Ԃ���waring�͏o���Ȃ��̂Œ���
                    // �����ł� $file �͖񑩂���Ă��邪�c
                    $die and die("Error: $me -> file_put_contents() failed.");
                    return false;
                }
                chmod($file, $perm);
            }
        }
        return true;
    }

    /**
     * �w��f�B���N�g�����Ȃ���΁i�ċA�I�Ɂj�������āA�p�[�~�b�V�����̒������s��
     *
     * @access  public
     * @param   integer  $perm  �p�[�~�b�V���� ex) 0707
     * @param   boolean  $die   true �Ȃ�A�G���[�����������_�ŁA��������die����
     * @return  boolean  ���s���ہB�����Ƀf�B���N�g�������݂��Ă��鎞��true��Ԃ��B
     */
    function mkdirR($dir, $perm = null, $die = true)
    {
        return FileCtl::_mkdirR($dir, $perm, $die, 0);
    }

    /**
     * mkdirR() �̎��������s��
     *
     * @access  private
     * @parama  integer  $rtimes  �ċA�Ăяo������Ă��錻�݉�
     * @return  boolean
     */
    function _mkdirR($dir, $perm = null, $die = true, $rtimes = 0)
    {
        global $_conf;

        $me = __CLASS__ . "::" . __FUNCTION__ . "()";

        // �����G���[
        if (strlen($dir) == 0) {
            trigger_error("$me cannot mkdir. no dirname", E_USER_WARNING);
            $die and die('Error');
        }

        // ���Ƀf�B���N�g�������݂��Ă��鎞�́A���̂܂܂�OK
        if (is_dir($dir)) {
            return true;
        }

        if (empty($perm)) {
            $perm = empty($_conf['data_dir_perm']) ? 0707 : $_conf['data_dir_perm'];
        }

        $dir_limit = 50; // �e�K�w����鐧����

        // �ċA���߃G���[
        if ($rtimes > $dir_limit) {
            trigger_error("$me cannot mkdir. ($dir) too match up dir! I'm very tired.", E_USER_WARNING);
            $die and die('Error');
            return false;
        }

        // �e�����ɍċA���s
        if (!FileCtl::_mkdirR(dirname($dir), $perm, $die, ++$rtimes)) {
            $die and die('Error');
            return false;
        }

        if (!mkdir($dir, $perm)) {
            trigger_error("$me -> mkdir failed, $dir", E_USER_WARNING);
            $die and die('Error');
            return false;
        }
        chmod($dir, $perm);

        return true;
    }

    /**
     * �w�肵���p�X�̐e�f�B���N�g�����Ȃ���΁i�ċA�I�Ɂj�������āA�p�[�~�b�V�����̒������s��
     *
     * @return  boolean
     */
    function mkdir_for($apath)
    {
        return FileCtl::mkdirR(dirname($apath));
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
        }

        return false;
    }

    /**
     * ��������t�@�C���ɏ������ށi�r���I���b�N�𗘗p�j
     * �iPHP5��file_put_contents�̑�֓I�����j
     * ��file_put_contents() �����b�N�w��ł���悤�ɂȂ��Ă���̂ŁA���̃��\�b�h�͂����g��Ȃ�����
     *
     * ����function�́APHP License �Ɋ�Â��AAidan Lister�� <aidan@php.net> �ɂ��A
     * PHP_Compat �� file_put_contents.php �̃R�[�h�����ɁA�Ǝ��̕ύX�iflock() �Ȃǁj�����������̂ł��B
     * This product includes PHP, freely available from <http://www.php.net/>
     *
     * @return  integer|false  �����Ȃ�A�������񂾓��e�̃o�C�g���B���s�Ȃ� false ��Ԃ��B
     */
    function file_write_contents($filename, $content, $flags = null, $resource_context = null)
    {
        // If $cont is an array, convert it to a string
        if (is_array($content)) {
            $content = implode('', $content);
        }

        /*
        // is_string() �ł̓_���Bshift_jis�̕������r������������肷��ƁAstring�ł͂Ȃ��Ɣ��f����邱�Ƃ�����悤���B
        // If we don't have a string, throw an error
        if (!is_scalar($content)) {
            trigger_error('file_write_contents() '.$filename.', The 2nd parameter should be either a string or an array', E_USER_WARNING);
            return false;
        }
        */

        // Get the length of date to write
        $length = strlen($content);

        // Check what mode we are using
        $file_append = ($flags & FILE_APPEND) ? true : false;
        $mode = $file_append ? 'ab' : 'ab';

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
        // �������ݏ������̓��[�U�ɂ���~�����𖳎�����
        $last = ignore_user_abort(1);

        // Write to the file
        $bytes = 0;

        if (!$file_append) {
            ftruncate($fh, 0);
        }

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
            $errormsg = sprintf('file_write_contents() Only %d of %d bytes written, possibly out of free disk space.',
                            $bytes,
                            $length);
            trigger_error($errormsg, E_USER_WARNING);
            return false;
        }

        return $bytes;
    }

    /**
     * Windows�ł͏㏑���� rename() �ŃG���[���o��悤�Ȃ̂ŁA���̃G���[���������rename()
     * ���������Aunlink() �� rename() �̊Ԃň�u�̊Ԃ��󂭂̂Ŋ��S�ł͂Ȃ��B
     * �Q�l http://ns1.php.gr.jp/pipermail/php-users/2005-October/027827.html
     *
     * @return  boolean
     */
    function rename($src_file, $dest_file)
    {
        $win = (strncasecmp(PHP_OS, 'WIN', 3) == 0) ? true : false;

        if ($win) {
            if (file_exists($dest_file) and is_writable($dest_file) and unlink($dest_file)) {
                return rename($src_file, $dest_file);
            } else {
                return false;
            }
        }
        return rename($src_file, $dest_file);
    }

    /**
     * �������ݒ��̕s���S�ȃt�@�C�����e���ǂݎ���邱�Ƃ̂Ȃ��悤�ɁA�ꎞ�t�@�C���ɏ�������ł��烊�l�[������
     * ���������AWindows�̏ꍇ�́A�㏑��rename���s���S�ƂȂ�̂Œ��ڏ������ނ��ƂƂ���
     *
     * @param   string   $tmp_dir  �ꎞ�ۑ��f�B���N�g��
     * @return  boolean  ���s���� �i�������ɏ������݃o�C�g����Ԃ��Ӗ����ĂقƂ�ǂȂ��C������j
     */
    function filePutRename($file, $cont, $tmp_dir = null)
    {
        if (strlen($file) == 0) {
            trigger_error(__CLASS__ . '::' . __FUNCTION__ . '(), file is null', E_USER_WARNING);
            return false;
        }

        $win = (strncasecmp(PHP_OS, 'WIN', 3) == 0) ? true : false;

        // �ꎞ�t�@�C���p�X�����߂�
        $prefix = 'rename_';

        // �ꎞ�f�B���N�g���̖����w�肪����ꍇ
        if ($tmp_dir) { // strlen($tmp_dir) > 0 �Ƃ��ׂ��Ƃ��낾���A�ނ���0�͂Ȃ��Ƃ������Ƃɂ��Ă݂�
            if (!is_dir($tmp_dir)) {
                trigger_error(__FUNCTION__ . "() -> is_dir($tmp_dir) failed.", E_USER_WARNING);
                return false;
            }

        } else {
            if (isset($GLOBALS['_conf']['tmp_dir'])) {
                $tmp_dir = $GLOBALS['_conf']['tmp_dir'];
                if (!is_dir($tmp_dir)) {
                    FileCtl::mkdirR($tmp_dir);
                }
            } else {
                // 2006/10/05 php_get_tmpdir() �� might be only in CVS
                if (function_exists('php_get_tmpdir')) {
                    $tmp_dir = php_get_tmpdir();
                } else {
                    // ����œ���͂��邪�Anull�w��ł����v���ȁB
                    $tmp_dir = null;
                }
            }
        }

        $tmp_file = tempnam($tmp_dir, $prefix);

        $write_file = $win ? $file : $tmp_file;

        $r = file_put_contents($write_file, $cont, LOCK_EX);
        if ($r === false) {
            return false;
        }
        if (!$win) {
            if (!rename($write_file, $file)) {
                return false;
            }
        }
        return true;
    }

    // {{{ scandirR()

    /**
     * �ċA�I�Ƀf�B���N�g���𑖍�����
     *
     * ���X�g���t�@�C���ƃf�B���N�g���ɕ����ĕԂ��B���ꂻ��̃��X�g�͒P���Ȕz��
     *
     * @return  array|false
     */
    function scandirR($dir)
    {
        $dir = realpath($dir);
        $list = array('files' => array(), 'dirs' => array());
        $files = scandir($dir);
        if ($files === false) {
            return false;
        }
        foreach ($files as $filename) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $filename = $dir . DIRECTORY_SEPARATOR . $filename;
            if (is_dir($filename)) {
                $child = P2Util::scandirR($filename);
                if ($child) {
                    $list['dirs'] = array_merge($list['dirs'], $child['dirs']);
                    $list['files'] = array_merge($list['files'], $child['files']);
                }
                $list['dirs'][] = $filename;
            } else {
                $list['files'][] = $filename;
            }
        }
        return $list;
    }

    // }}}
    // {{{ garbageCollection()

    /**
     * ������ЂƂ̃K�x�R��
     *
     * $targetDir����ŏI�X�V���$lifeTime�b�ȏソ�����t�@�C�����폜
     *
     * @access  public
     * @param   string   $targetDir  �K�[�x�b�W�R���N�V�����Ώۃf�B���N�g��
     * @param   integer  $lifeTime   �t�@�C���̗L�������i�b�j
     * @param   string   $prefix     �Ώۃt�@�C�����̐ړ����i�I�v�V�����j
     * @param   string   $suffix     �Ώۃt�@�C�����̐ڔ����i�I�v�V�����j
     * @param   boolean  $recurive   �ċA�I�ɃK�[�x�b�W�R���N�V�������邩�ۂ��i�f�t�H���g�ł�FALSE�j
     * @return  array|false    �폜�ɐ��������t�@�C���Ǝ��s�����t�@�C����ʁX�ɋL�^�����񎟌��̔z��
     */
    function garbageCollection($targetDir, $lifeTime, $prefix = '', $suffix = '', $recursive = false)
    {
        $result = array('successed' => array(), 'failed' => array(), 'skipped' => array());
        $expire = time() - $lifeTime;
        //�t�@�C�����X�g�擾
        if ($recursive) {
            $list = FileCtl::scandirR($targetDir);
            if ($list === false) {
                return false;
            }
            $files = &$list['files'];
        } else {
            $list = scandir($targetDir);
            $files = array();
            $targetDir = realpath($targetDir) . DIRECTORY_SEPARATOR;
            foreach ($list as $filename) {
                if ($filename == '.' || $filename == '..') { continue; }
                $files[] = $targetDir . $filename;
            }
        }
        //�����p�^�[���ݒ�i$prefix��$suffix�ɃX���b�V�����܂܂Ȃ��悤�Ɂj
        if ($prefix || $suffix) {
            $prefix = (is_array($prefix)) ? implode('|', array_map('preg_quote', $prefix)) : preg_quote($prefix);
            $suffix = (is_array($suffix)) ? implode('|', array_map('preg_quote', $suffix)) : preg_quote($suffix);
            $pattern = '/^' . $prefix . '.+' . $suffix . '$/';
        } else {
            $pattern = '';
        }
        //�K�x�R���J�n
        foreach ($files as $filename) {
            if ($pattern && !preg_match($pattern, basename($filename))) {
                //$result['skipped'][] = $filename;
                continue;
            }
            if (filemtime($filename) < $expire) {
                if (@unlink($filename)) {
                    $result['successed'][] = $filename;
                } else {
                    $result['failed'][] = $filename;
                }
            }
        }
        return $result;
    }

    // }}}
}

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
