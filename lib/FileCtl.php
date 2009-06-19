<?php
/**
 * �t�@�C���𑀍삷��N���X
 * static���\�b�h�ŗ��p����
 */
class FileCtl
{
    /**
     * �t�@�C�����Ȃ���ΐ������A�������݌������Ȃ���΃p�[�~�b�V�����𒲐�����
     * �i���Ƀt�@�C��������A�������݌���������ꍇ�́A�������Ȃ��B��modified�̍X�V�����Ȃ��j
     *
     * @static
     * @access  public
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
            if (!FileCtl::mkdirFor($file)) {
                $die and die("Error: $me -> FileCtl::mkdirFor() failed.");
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
                if (false === $cont = file_get_contents($file)) {
                    $die and die("Error: $me -> file_get_contents() failed.");
                    return false;
                }
                unlink($file);
                if (false === file_put_contents($file, $cont, LOCK_EX)) {
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
     * PHP 5.0.0 �ȏ�ł���΁Amkdir() �� recursive �p�����[�^���ǉ�����Ă���B  
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
     * @param   integer  $rtimes  �ċA�Ăяo������Ă��錻�݉�
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
            $die and die('Error: FileCtl::_mkdirR()');
            return false;
        }
        
        if (!mkdir($dir, $perm)) {
            trigger_error("$me -> mkdir failed, $dir", E_USER_WARNING);
            $die and die('Error: mkdir()');
            return false;
        }
        chmod($dir, $perm);

        return true;
    }
    
    /**
     * �w�肵���p�X�̐e�f�B���N�g�����Ȃ���΁i�ċA�I�Ɂj�������āA�p�[�~�b�V�����̒������s��
     * mkdirR()��dirname()���đ����Ă��邾���Ȃ̂ŁA���̃��\�b�h�͂Ȃ��Ă�������������Ȃ��B 
     *
     * @static
     * @access  public
     * @return  boolean
     */
    function mkdirFor($apath)
    {
        return FileCtl::mkdirR(dirname($apath));
    }
    
    // {{{ file_read_lines()

    /**
     * �t�@�C���S�̂�ǂݍ���Ŕz��Ɋi�[����
     * �G���[�}���t���� @file() �̑�p
     *
     * @param string $filename
     * @param int $flags
     * @param resource $context
     */
    static public function file_read_lines($filename, $flags = 0, $context = null)
    {
        if (!is_readable($filename)) {
            return false;
        }
        $lines = file($filename, $flags, $context);
        if (($flags & FILE_IGNORE_NEW_LINES) && $lines &&
            strlen($lines[0]) && substr($lines[0], -1) == "\r")
        {
            $lines = array_map(create_function('$l', 'return rtrim($l, "\\r");'), $lines);
            if ($flags & FILE_SKIP_EMPTY_LINES) {
                $lines = array_filter($lines, 'strlen');
            }
        }
        return $lines;
    }

    // }}}
    
    /**
     * gz�t�@�C���̒��g���擾����
     *
     * @static
     * @access  public
     * @return  string|false
     */
    function getGzFileContents($filepath)
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
     * Windows�ł͏㏑���� rename() �ŃG���[���o��悤�Ȃ̂ŁA���̃G���[���������rename()
     * ���������Aunlink() �� rename() �̊Ԃň�u�̊Ԃ��󂭂̂Ŋ��S�ł͂Ȃ��B
     * �Q�l http://ns1.php.gr.jp/pipermail/php-users/2005-October/027827.html
     *
     * @return  boolean
     */
    function rename($src_file, $dest_file)
    {
        $win = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? true : false;
        
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
     * @static
     * @access  public
     * @param   string   $tmp_dir  �ꎞ�ۑ��f�B���N�g��
     * @return  boolean  ���s���� �i�������ɏ������݃o�C�g����Ԃ��Ӗ����ĂقƂ�ǂȂ��C������j
     */
    function filePutRename($file, $cont, $tmp_dir = null)
    {
        if (strlen($file) == 0) {
            trigger_error(__CLASS__ . '::' . __FUNCTION__ . '(), file is null', E_USER_WARNING);
            return false;
        }
        
        $win = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? true : false;
        
        // �ꎞ�t�@�C���p�X�����߂�
        $prefix = 'rename_';
        
        // �ꎞ�f�B���N�g���̖����w�肪����ꍇ
        if ($tmp_dir) { // strlen($tmp_dir) > 0 �Ƃ��ׂ��Ƃ��낾���A�ނ���"0"���Ȃ��Ƃ������Ƃɂ��Ă݂�
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
                    // ����œ���͂��邪�Anull�w��ł����v���ȁB2007/01/22 Win�ł�NG?���m�F
                    $tmp_dir = null;
                }
            }
        }

        $write_file = $win ? $file : tempnam($tmp_dir, $prefix);
        
        if (false === $r = file_put_contents($write_file, $cont, LOCK_EX)) {
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
     * @static
     * @access  public
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
                $child = FileCtl::scandirR($filename);
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
    function garbageCollection($targetDir, $lifeTime, $prefix = '', $suffix = '', $recursive = FALSE)
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
