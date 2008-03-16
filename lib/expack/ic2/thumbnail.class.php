<?php
/**
 * rep2expack - ImageCache2
 */

require_once P2EX_LIBRARY_DIR . '/ic2/findexec.inc.php';
require_once P2EX_LIBRARY_DIR . '/ic2/loadconfig.inc.php';
require_once P2EX_LIBRARY_DIR . '/ic2/database.class.php';
require_once P2EX_LIBRARY_DIR . '/ic2/db_images.class.php';

define('IC2_THUMB_SIZE_DEFAULT', 1);
define('IC2_THUMB_SIZE_PC',      1);
define('IC2_THUMB_SIZE_MOBILE',  2);
define('IC2_THUMB_SIZE_INTERMD', 3);

class ThumbNailer
{
    // {{{ properties

    var $db;            // @var object  PEAR DB_{phptype}�̃C���X�^���X
    var $ini;           // @var array   ImageCache2�̐ݒ�
    var $mode;          // @var int     �T���l�C���̎��
    var $cachedir;      // @var string  ImageCache2�̃L���b�V���ۑ��f�B���N�g��
    var $sourcedir;     // @var string  �\�[�X�ۑ��f�B���N�g��
    var $thumbdir;      // @var string  �T���l�C���ۑ��f�B���N�g��
    var $driver;        // @var string  �C���[�W�h���C�o�̎��
    var $epeg;          // @var bool    Epeg�����p�\���ۂ�
    var $magick;        // @var string  ImageMagick�̃p�X
    var $magick6;       // @var bool    ImageMagick6�ȏォ�ۂ�
    var $max_width;     // @var int     �T���l�C���̍ő啝
    var $max_height;    // @var int     �T���l�C���̍ő卂��
    var $type;          // @var string  �T���l�C���̉摜�`���iJPEG��PNG�j
    var $quality;       // @var int     �T���l�C���̕i��
    var $bgcolor;       // @var mixed   �T���l�C���̔w�i�F
    var $resize;        // @var bolean  �摜�����T�C�Y���邩�ۂ�
    var $rotate;        // @var int     �摜����]����p�x�i��]���Ȃ��Ƃ�0�j
    var $trim;          // @var bolean  �摜���g���~���O���邩�ۂ�
    var $coord;         // @var array   �摜���g���~���O����͈́i�g���~���O���Ȃ��Ƃ�false�j
    var $found;         // @var array   IC2DB_Images�ŃN�G���𑗐M��������
    var $dynamic;       // @var bool    ���I�������邩�ۂ��itrue�̂Ƃ����ʂ��t�@�C���ɕۑ����Ȃ��j
    var $intermd;       // @var string  ���I�����ɗ��p���钆�ԃC���[�W�̃p�X�i�\�[�X���璼�ڐ�������Ƃ�false�j
    var $buf;           // @var string  ���I���������摜�f�[�^
    // @var array $default_options,    ���I�������̃I�v�V����
    var $default_options = array(
        'quality' => null,
        'rotate'  => 0,
        'trim'    => false,
        'intermd' => false,
    );
    // @var array $mimemap, MIME�^�C�v�Ɗg���q�̑Ή��\
    var $mimemap = array('image/jpeg' => '.jpg', 'image/png' => '.png', 'image/gif' => '.gif');

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     *
     * @access public
     */
    function ThumbNailer($mode = IC2_THUMB_SIZE_DEFAULT, $dynamic_options = null)
    {
        if (is_array($dynamic_options) && count($dynamic_options) > 0) {
            $options = array_merge($this->default_options, $dynamic_options);
            $this->dynamic = true;
            $this->intermd = $options['intermd'];
        } else {
            $options = $this->default_options;
            $this->dynamic = false;
            $this->intermd = false;
        }

        // �ݒ�
        $this->ini = ic2_loadconfig();

        // �f�[�^�x�[�X�ɐڑ�
        $icdb = &new IC2DB_Images;
        $this->db = &$icdb->getDatabaseConnection();
        if (DB::isError($this->db)) {
            $this->error($this->db->getMessage());
        }

        // �T���l�C�����[�h����
        switch ($mode) {
            case IC2_THUMB_SIZE_INTERMD:
                $this->mode = IC2_THUMB_SIZE_INTERMD;
                $setting = $this->ini['Thumb3'];
                break;
            case IC2_THUMB_SIZE_MOBILE:
                $this->mode = IC2_THUMB_SIZE_MOBILE;
                $setting = $this->ini['Thumb2'];
                break;
            case IC2_THUMB_SIZE_PC:
            default:
                $this->mode = IC2_THUMB_SIZE_PC;
                $setting = $this->ini['Thumb1'];
        }

        // �C���[�W�h���C�o����
        $driver = strtolower($this->ini['General']['driver']);
        $this->driver = $driver;
        $this->magick6 = false;
        switch ($driver) {
            case 'imagemagick6': // ImageMagick6 �� convert �R�}���h
                $this->driver = 'imagemagick';
                $this->magick6 = true;
            case 'imagemagick': // ImageMagick �� convert �R�}���h
                $searchpath = $this->ini['General']['magick'];
                if (!findexec('convert', $searchpath)) {
                    $this->error('ImageMagick���g���܂���B');
                }
                if ($searchpath) {
                    $this->magick = $searchpath . DIRECTORY_SEPARATOR . 'convert';
                } else {
                    $this->magick = 'convert';
                }
                break;
            case 'gd': // PHP �� GD �g���@�\
                if (!function_exists('imagerotate') && $options['rotate'] != 0) {
                    $this->error('imagerotate�֐����g���܂���B');
                }
                break;
            case 'imagick': // PHP �� ImageMagick �g���@�\
                if (!extension_loaded('imagick')) {
                    $this->error('imagick�G�N�X�e���V�������g���܂���B');
                }
                break;
            case 'imlib2': // PHP �� Imlib2 �g���@�\
                if (!extension_loaded('imlib2')) {
                    $this->error('imlib2�G�N�X�e���V�������g���܂���B');
                }
                break;
            default:
                $this->error('�����ȃC���[�W�h���C�o�ł��B');
        }

        $this->epeg = ($this->ini['General']['epeg'] && extension_loaded('epeg')) ? true : false;

        // �f�B���N�g���ݒ�
        $this->cachedir   = $this->ini['General']['cachedir'];
        $this->sourcedir  = $this->cachedir . '/' . $this->ini['Source']['name'];
        $this->thumbdir   = $this->cachedir . '/' . $setting['name'];

        // �T���l�C���̉摜�`���E���E�����E��]�p�x�E�i���ݒ�
        $rotate = (int) $options['rotate'];
        if (abs($rotate) < 4) {
            $rotate = $rotate * 90;
        }
        $rotate = ($rotate < 0) ? ($rotate % 360) + 360 : $rotate % 360;
        $this->rotate = ($rotate % 90 == 0) ? $rotate : 0;
        if ($this->rotate % 180 == 90) {
            $this->max_width  = (int) $setting['height'];
            $this->max_height = (int) $setting['width'];
        } else {
            $this->max_width  = (int) $setting['width'];
            $this->max_height = (int) $setting['height'];
        }
        if (is_null($options['quality'])) {
            $this->quality = (int) $setting['quality'];
        } else {
            $this->quality = (int) $options['quality'];
        }
        if (0 < $this->quality && $this->quality <= 100) {
            $this->type = '.jpg';
        } else {
            $this->type = '.png';
            $this->quality = 0;
        }
        $this->trim = (bool) $options['trim'];

        // �T���l�C���̔w�i�F�ݒ�
        if (preg_match('/^#?([0-9A-F]{2})([0-9A-F]{2})([0-9A-F]{2})$/i', // RGB�e�F2����16�i��
                       $this->ini['General']['bgcolor'], $c)) {
            $r = hexdec($c[1]);
            $g = hexdec($c[2]);
            $b = hexdec($c[3]);
        } elseif (preg_match('/^#?([0-9A-F])([0-9A-F])([0-9A-F])$/i', // RGB�e�F1����16�i��
                  $this->ini['General']['bgcolor'], $c)) {
            $r = hexdec($c[1] . $c[1]);
            $g = hexdec($c[2] . $c[2]);
            $b = hexdec($c[3] . $c[3]);
        } elseif (preg_match('/^(\d{1,3}),(\d{1,3}),(\d{1,3})$/', // RGB�e�F1�`3����10�i��
                  $this->ini['General']['bgcolor'], $c)) {
            $r = max(0, min(intval($c[1]), 255));
            $g = max(0, min(intval($c[2]), 255));
            $b = max(0, min(intval($c[3]), 255));
        } else {
            $r = null;
            $g = null;
            $b = null;
        }
        $this->_bgcolor($r, $g, $b);
    }

    // }}}
    // {{{ convert method

    /**
     * �T���l�C�����쐬
     *
     * @access  public
     * @return  string|bool|PEAR_Error
     *          �T���l�C���𐶐��E�ۑ��ɐ��������Ƃ��A�T���l�C���̃p�X
     *          �e���|�����E�T���l�C���̐����ɐ��������Ƃ��Atrue
     *          ���s�����Ƃ� PEAR_Error
     */
    function &convert($size, $md5, $mime, $width, $height, $force = false)
    {
        // �摜
        if (!empty($this->intermd) && file_exists($this->intermd)) {
            $src    = realpath($this->intermd);
            $csize  = getimagesize($this->intermd);
            $width  = $csize[0];
            $height = $csize[1];
        } else {
            $src = $this->srcPath($size, $md5, $mime, true);
        }
        $thumbURL = $this->thumbPath($size, $md5, $mime);
        $thumb = $this->thumbPath($size, $md5, $mime, true);
        if ($src == false) {
            $error = &PEAR::raiseError("������MIME�^�C�v�B({$mime})");
            return $error;
        } elseif (!file_exists($src)) {
            $error = &PEAR::raiseError("�\�[�X�摜���L���b�V������Ă��܂���B({$src})");
            return $error;
        }
        if (!$force && !$this->dynamic && file_exists($thumb)) {
            return $thumbURL;
        }
        $thumbdir = dirname($thumb);
        if (!is_dir($thumbdir) && !@mkdir($thumbdir)) {
            $error = &PEAR::raiseError("�f�B���N�g�����쐬�ł��܂���ł����B({$thumbdir})");
            return $error;
        }

        // �T�C�Y������l�ȉ��ŉ�]�Ȃ��A�摜�`���������Ȃ�΂��̂܂܃R�s�[
        // --- �g�тŕ\���ł��Ȃ����Ƃ�����̂ŕ���A�����ƃT���l�C��������
        $_size = $this->calc($width, $height);
        /*if ($this->resize == false && $this->rotate == 0 && $this->type == $this->mimemap[$mime]) {
            if (@copy($src, $thumb)) {
                return $thumbURL;
            } else {
                $error = &PEAR::raiseError("�摜���R�s�[�ł��܂���ł����B({$src} -&gt; {$thumb})");
                return $error;
            }
        }*/

        // Epeg�ŃT���l�C�����쐬
        if ($mime == 'image/jpeg' && $this->type == '.jpg' && $this->epeg && !$this->rotate && !$this->trim) {
            $dst = ($this->dynamic) ? '' : $thumb;
            $result = epeg_thumbnail_create($src, $dst, $this->max_width, $this->max_height, $this->quality);
            if ($result == false) {
                $error = &PEAR::raiseError("�T���l�C�����쐬�ł��܂���ł����B({$src} -&gt; {$dst})");
                return $error;
            }
            if ($this->dynamic) {
                $this->buf = $result;
            }
            return $thumbURL;
        }

        // �C���[�W�h���C�o�ɃT���l�C���쐬������������
        switch ($this->driver) {
            case 'imagemagick':
                $_srcsize = sprintf('%dx%d', $width, $height);
                if ($this->rotate % 180 == 90) {
                    $_thumbsize = vsprintf('%2$dx%1$d!', explode('x', $_size));
                } else {
                    $_thumbsize = $_size . '!';
                }
                if ($this->dynamic) {
                    $result = &$this->_magickCapture($src, $_srcsize, $_thumbsize);
                } else {
                    $result = &$this->_magickSave($src, $thumb, $_srcsize, $_thumbsize);
                }
                break;
            case 'gd':
            case 'imagick':
            case 'imlib2':
            //case 'magickwand':
                $size = array();
                list($size['tw'], $size['th']) = explode('x', $_size);
                if (is_array($this->coord)) {
                    $size['sx'] = $this->coord['x'][0];
                    $size['sy'] = $this->coord['y'][0];
                    $size['sw'] = $this->coord['x'][1];
                    $size['sh'] = $this->coord['y'][1];
                } else {
                    $size['sx'] = 0;
                    $size['sy'] = 0;
                    $size['sw'] = $width;
                    $size['sh'] = $height;
                }
                if ($this->dynamic) {
                    $result = &$this->{'_'.$this->driver.'Capture'}($src, $size);
                } else {
                    $result = &$this->{'_'.$this->driver.'Save'}($src, $thumb, $size);
                }
                break;
            default:
                $this->error('�����ȃC���[�W�h���C�o�ł��B');
        }

        if (PEAR::isError($result)) {
            return $result;
        }
        return $thumbURL;
    }

    // }}}
    // {{{ image manipulation methods using gd php extension

    /**
     * gd �G�N�X�e���V�����ŕϊ��A�C���[�W���\�[�X��Ԃ�
     *
     * @access private
     * @return resource gd
     */
    function &_gdConvert($source, $size)
    {
        extract($size);
        // �\�[�X�̃C���[�W�X�g���[�����擾
        $ext = strrchr($source, '.');
        switch ($ext) {
            case '.jpg': $src = imagecreatefromjpeg($source); break;
            case '.png': $src = imagecreatefrompng($source); break;
            case '.gif': $src = imagecreatefromgif($source); break;
        }
        if (!is_resource($src)) {
            $error = &PEAR::raiseError("�摜�̓ǂݍ��݂Ɏ��s���܂����B({$source})");
            return $error;
        }
        // �T���l�C���̃C���[�W�X�g���[�����쐬
        $dst = imagecreatetruecolor($tw, $th);
        if (!is_null($this->bgcolor)) {
            $bg = imagecolorallocate($dst, $this->bgcolor[0], $this->bgcolor[1], $this->bgcolor[2]);
            imagefill($dst, 0, 0, $bg);
        }
        // �\�[�X���T���l�C���ɃR�s�[
        if ($this->resize) {
            imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $tw, $th, $sw, $sh);
        } else {
            imagecopy($dst, $src, 0, 0, $sx, $sy, $sw, $sh);
        }
        imagedestroy($src);
        // ��]
        if ($this->rotate) {
            $degrees = ($this->rotate == 90) ? -90 : (($this->rotate == 270) ? 90: $this->rotate);
            $tmp = imagerotate($dst, $degrees, $bg);
            imagedestroy($dst);
            return $tmp;
        }
        return $dst;
    }

    /**
     * gd �G�N�X�e���V�����ŕϊ��A�t�@�C���ɏo��
     *
     * @access private
     * @return boolean | object PEAR_Error
     */
    function &_gdSave($source, $thumbnail, $size)
    {
        $dst = &$this->_gdConvert($source, $size);
        // �T���l�C����ۑ�
        if ($this->type == '.png') {
            $result = imagepng($dst, $thumbnail);
        } else {
            $result = imagejpeg($dst, $thumbnail, $this->quality);
        }
        imagedestroy($dst);
        if (!$result) {
            $errmsg = "�T���l�C���̍쐬�Ɏ��s���܂����B({$thumbnail})";
            $retval = &PEAR::raiseError($errmsg);
        } else {
            $retval = true;
        }
        return $retval;
    }

    /**
     * gd �G�N�X�e���V�����ŕϊ��A�o�b�t�@�ɕۑ�
     *
     * @access private
     * @return boolean | object PEAR_Error
     */
    function &_gdCapture($source, $size)
    {
        $dst = &$this->_gdConvert($source, $size);
        // �T���l�C�����쐬
        ob_start();
        if ($this->type == '.png') {
            $result = imagepng($dst);
        } else {
            $result = imagejpeg($dst, '', $this->quality);
        }
        $this->buf = ob_get_clean();
        imagedestroy($dst);
        if (!$result) {
            $errmsg = "�T���l�C���̍쐬�Ɏ��s���܂����B({$thumbnail})";
            $retval = &PEAR::raiseError($errmsg);
        } else {
            $retval = true;
        }
        return $retval;
    }

    /**
     * gd �G�N�X�e���V�����ŕϊ��A���ڏo��
     *
     * @access private
     * @return boolean | object PEAR_Error
     */
    function &_gdOutput($source, $thumbnail, $size)
    {
        $dst = &$this->_gdConvert($source, $size);
        // �T���l�C�����o��
        $name = 'filename="' . basename($thumbnail) . '"';
        if ($this->type == '.png') {
            header('Content-Type: image/png; ' . $name);
            header('Content-Disposition: inline; ' . $name);
            $result = imagepng($dst);
        } else {
            header('Content-Type: image/jpeg; ' . $name);
            header('Content-Disposition: inline; ' . $name);
            $result = imagejpeg($dst, '', $this->quality);
        }
        imagedestroy($dst);
        if (!$result) {
            $errmsg = "�T���l�C���̍쐬�Ɏ��s���܂����B({$thumbnail})";
            $retval = &PEAR::raiseError($errmsg);
        } else {
            $retval = true;
        }
        return $retval;
    }

    // }}}
    // {{{ image manipulation methods using imlib2 php extension

    /**
     * imlib2 �G�N�X�e���V�����ŕϊ��A�C���[�W���\�[�X��Ԃ�
     *
     * @access private
     * @return resource Unknown (imlib2?)
     */
    function &_imlib2Convert($source, $size)
    {
        extract($size);
        $err = 0;
        // �\�[�X�̃C���[�W�X�g���[�����擾
        $src = imlib2_load_image($source, $err);
        if ($err) {
            $error = &PEAR::raiseError("�摜�̓ǂݍ��݂Ɏ��s���܂����B({$source}:{$err})");
            return $error;
        }
        // �T���l�C���̃C���[�W�X�g���[�����쐬
        $dst = imlib2_create_image($tw, $th);
        if (!is_null($this->bgcolor)) {
            list($r, $g, $b) = $this->bgcolor;
            imlib2_image_fill_rectangle($dst, 0, 0, $tw, $th, $r, $g, $b, 255);
        }
        // �\�[�X���T���l�C���ɃR�s�[
        /* imlib_blend_image_onto_image(int dstimg, int srcimg, int malpha, int srcx, int srcy, int srcw, int srch,
            int dstx, int dsty, int dstw, int dsth, char dither, char blend, char alias) */
        imlib2_blend_image_onto_image($dst, $src, 255, $sx, $sy, $sw, $sh, 0, 0, $tw, $th, false, true, $this->resize);
        imlib2_free_image($src);
        // ��]
        if ($this->rotate) {
            imlib2_image_orientate($dst, $this->rotate / 90);
        }
        return $dst;
    }

    /**
     * imlib2 �G�N�X�e���V�����ŕϊ��A�t�@�C���ɏo��
     *
     * @access private
     * @return boolean | object PEAR_Error
     */
    function &_imlib2Save($source, $thumbnail, $size)
    {
        $dst = &$this->_imlib2Convert($source, $size);
        $err = 0;
        // �T���l�C����ۑ�
        if ($this->type == '.png') {
            imlib2_image_set_format($dst, 'png');
            $result = imlib2_save_image($dst, $thumbnail, $err);
        } else {
            imlib2_image_set_format($dst, 'jpeg');
            $result = imlib2_save_image($dst, $thumbnail, $err, $this->quality);
        }
        imlib2_free_image($dst);
        if (!$result) {
            $errmsg = "�T���l�C���̍쐬�Ɏ��s���܂����B({$thumbnail}:{$err})";
            $retval = &PEAR::raiseError($errmsg);
        } else {
            $retval = true;
        }
        return $retval;
    }

    /**
     * imlib2 �G�N�X�e���V�����ŕϊ��A�o�b�t�@�ɕۑ�
     *
     * imlib2_dump_image() �̏o�͂��L���v�`�����悤�Ƃ���Ƃ��܂������Ȃ��̂�
     * ��������ꎞ�t�@�C���ɏ����o�����f�[�^��ǂݍ���
     *
     * @access private
     * @return boolean | object PEAR_Error
     */
    function &_imlib2Capture($source, $size)
    {
        $dst = &$this->_imlib2Convert($source, $size);
        $err = 0;
        // �T���l�C�����쐬
        $tempfile = $this->_tempnam();
        if ($this->type == '.png') {
            imlib2_image_set_format($dst, 'png');
            $result = imlib2_save_image($dst, $tempfile, $err);
        } else {
            imlib2_image_set_format($dst, 'jpeg');
            $result = imlib2_save_image($dst, $tempfile, $err, $this->quality);
        }
        imlib2_free_image($dst);
        if (!$result) {
            $errmsg = "�T���l�C���̍쐬�Ɏ��s���܂����B({$thumbnail}:{$err})";
            $retval = &PEAR::raiseError($errmsg);
        } else {
            $this->buf = file_get_contents($tempfile);
            $retval = true;
        }
        return $retval;
    }

    /**
     * imlib2 �G�N�X�e���V�����ŕϊ��A���ڏo��
     *
     * @access private
     * @return boolean | object PEAR_Error
     */
    function &_imlib2Output($source, $thumbnail, $size)
    {
        $dst = &$this->_imlib2Convert($source, $size);
        $err = 0;
        // �T���l�C�����o��
        $name = 'filename="' . basename($thumbnail) . '"';
        if ($this->type == '.png') {
            header('Content-Type: image/png; ' . $name);
            header('Content-Disposition: inline; ' . $name);
            imlib2_image_set_format($dst, 'png');
            $result = imlib2_dump_image($dst, $err);
        } else {
            header('Content-Type: image/jpeg; ' . $name);
            header('Content-Disposition: inline; ' . $name);
            imlib2_image_set_format($dst, 'jpeg');
            $result = imlib2_dump_image($dst, $err, $this->quality);
        }
        imlib2_free_image($dst);
        if (!$result) {
            $errmsg = "�T���l�C���̍쐬�Ɏ��s���܂����B({$thumbnail}:{$err})";
            $retval = &PEAR::raiseError($errmsg);
        } else {
            $retval = true;
        }
        return $retval;
    }

    // }}}
    // {{{ image manipulation methods using imagick extension

    /**
     * imagick �G�N�X�e���V�����ŕϊ��A�C���[�W���\�[�X��Ԃ�
     *
     * @access private
     * @return resource imagick handle
     */
    function &_imagickConvert($source, $size)
    {
        extract($size);
        // �\�[�X�̃C���[�W�X�g���[�����擾
        $src = imagick_readimage($source);
        if (!is_resource($src) || imagick_iserror($src)) {
            if (is_resource($src)) {
                $reason = imagick_failedreason($src);
                $detail = imagick_faileddescription($src);
                imagick_destroyhandle($src);
            }
            $error = &PEAR::raiseError("�摜�̓ǂݍ��݂Ɏ��s���܂����B({$source}:{$reason}:{$detail})");
            return $error;
        }
        // �T���l�C���̃C���[�W�X�g���[�����쐬
        $bg = (!is_null($this->bgcolor)) ? $this->bgcolor : 'rgb(0,0,0)';
        $dst = imagick_getcanvas($bg, $tw, $th);
        // �\�[�X�����T�C�Y���A�T���l�C���ɃR�s�[
        if ($sx != 0 || $sy != 0) {
            imagick_crop($src, $sx, $sy, $sw, $sh);
        }
        if ($this->resize) {
            imagick_scale($src, $tw, $th, '!');
        }
        imagick_composite($dst, IMAGICK_COMPOSITE_OP_ATOP, $src, 0, 0);
        imagick_destroyhandle($src);
        // ��]
        if ($this->rotate) {
            imagick_rotate($dst, $this->rotate);
        }
        return $dst;
    }

    /**
     * imagick �G�N�X�e���V�����ŕϊ��A�t�@�C���ɏo��
     *
     * @access private
     * @return boolean | object PEAR_Error
     */
    function &_imagickSave($source, $thumbnail, $size)
    {
        $dst = &$this->_imagickConvert($source, $size);
        // �T���l�C����ۑ�
        if ($this->quality > 0) {
            imagick_setcompressionquality($dst, $this->quality);
        }
        $prefix = (($this->type == '.png') ? 'png' : 'jpeg') . ':';
        $result = imagick_writeimage($dst, $prefix.$thumbnail);
        if (!$result) {
            $reason = imagick_failedreason($dst);
            $detail = imagick_faileddescription($dst);
            $errmsg = "�T���l�C���̍쐬�Ɏ��s���܂����B({$thumbnail}:{$reason}:{$detail})";
            $retval = &PEAR::raiseError($errmsg);
        } else {
            $retval = true;
        }
        imagick_destroyhandle($dst);
        return $retval;
    }

    /**
     * imagick �G�N�X�e���V�����ŕϊ��A�o�b�t�@�ɕۑ�
     *
     * imagick_image2blob() �ł͂��܂������Ȃ��̂�
     * ��������ꎞ�t�@�C���ɏ����o�����f�[�^��ǂݍ���
     *
     * @access private
     * @return boolean | object PEAR_Error
     */
    function &_imagickCapture($source, $size)
    {
        $dst = &$this->_imagickConvert($source, $size);
        // �T���l�C�����쐬
        if ($this->quality > 0) {
            imagick_setcompressionquality($dst, $this->quality);
        }
        $prefix = (($this->type == '.png') ? 'png' : 'jpeg') . ':';
        $tempfile = $this->_tempnam();
        $result = imagick_writeimage($dst, $prefix.$tempfile);
        if (!$result) {
            $reason = imagick_failedreason($dst);
            $detail = imagick_faileddescription($dst);
            $errmsg = "�T���l�C���̍쐬�Ɏ��s���܂����B({$thumbnail}:{$reason}:{$detail})";
            $retval = &PEAR::raiseError($errmsg);
        } else {
            $this->buf = file_get_contents($tempfile);
            $retval = true;
        }
        imagick_destroyhandle($dst);
        return $retval;
    }

    /**
     * imagick �G�N�X�e���V�����ŕϊ��A���ڏo��
     *
     * imagick_image2blob() �ł͂��܂������Ȃ��̂�
     * ��������ꎞ�t�@�C���ɏ����o���Areadfile() ����
     *
     * @access private
     * @return boolean | object PEAR_Error
     */
    function &_imagickOutput($source, $thumbnail, $size)
    {
        $dst = &$this->_imagickConvert($source, $size);
        // �T���l�C�����o��
        if ($this->quality) {
            imagick_setcompressionquality($dst, $this->quality);
        }
        $prefix = (($this->type == '.png') ? 'png' : 'jpeg') . ':';
        $tempfile = $this->_tempnam();
        $result = imagick_writeimage($dst, $prefix.$tempfile);
        if (!$result) {
            $reason = imagick_failedreason($dst);
            $detail = imagick_faileddescription($dst);
            $errmsg = "�T���l�C���̍쐬�Ɏ��s���܂����B({$thumbnail}:{$reason}:{$detail})";
            $retval = &PEAR::raiseError($errmsg);
        } else {
            $name = 'filename="' . basename($thumbnail) . '"';
            if ($this->type == '.png') {
                header('Content-Type: image/png; ' . $name);
                header('Content-Disposition: inline; ' . $name);
            } else {
                header('Content-Type: image/jpeg; ' . $name);
                header('Content-Disposition: inline; ' . $name);
            }
            readfile($tempfile);
            $retval = true;
        }
        imagick_destroyhandle($dst);
        return $retval;
    }

    // }}}
    // {{{ image manipulation methods using ImageMagick's convert command

    /**
     * ImageMagick�̃R�}���h����
     *
     * @access private
     * @return string
     */
    function _magickCommand($source, $thumbnail, $srcsize, $thumbsize)
    {
        $command = $this->magick;

        // ���̃T�C�Y���w��
        $command .= sprintf(' -size %s', escapeshellarg($srcsize));

        // �����t���[������Ȃ�摜��������Ȃ��Ƃ�
        if (preg_match('/\.gif$/', $source)) {
            $command .= ' +adjoin';
            $source .= '[0]';
        }

        // �N���b�v���ăp�C�v
        if (is_array($this->coord)) {
            $x = $this->coord['x'];
            $y = $this->coord['y'];
            $command .= sprintf(" -crop '%dx%d+%d+%d'", $x[1], $y[1], $x[0], $y[0]);
            $command .= sprintf(' %s', escapeshellarg($source));
            $command .= ' - | ' . $this->magick;
            $command .= sprintf(" -size '%dx%d'", $x[1], $y[1]);
            $source = '-';
        }

        // ���ߕ����̔w�i�F��C�ӂ̐F�ɂ���̂͂߂�ǂ��������Ȃ̂ŕۗ�
        /*if (!is_null($this->bgcolor)) {
            $command .= sprintf(' -background %s', escapeshellarg($this->bgcolor));
        }*/
        // ��]
        if ($this->rotate) {
            $command .= sprintf(' -rotate %d', $this->rotate);
        }

        // �T���l�C���̃T�C�Y���w��E���^�f�[�^�͏���
        if ($this->magick6) {
            if ($this->resize) {
                $command .= sprintf(' -thumbnail %s', escapeshellarg($thumbsize));
            } else {
                $command .= ' -strip';
            }
        } else {
            if ($this->resize) {
                $command .= sprintf(' -scale %s', escapeshellarg($thumbsize));
            }
            $command .= " +profile '*'";
        }
        // �T���l�C���̉摜�`��
        $command .= sprintf(' -format %s', (($this->type == '.png') ? 'PNG' : 'JPEG'));
        // �T���l�C���̕i��
        if ($this->quality) {
            $command .= sprintf(' -quality %d', $this->quality);
        }

        // ���̉摜�̃p�X���w��
        $command .= sprintf(' %s', ((!$source || $source == '-') ? '-' : escapeshellarg($source)));
        // �T���l�C���̏o�͐���w��
        $command .= sprintf(' %s', ((!$thumbnail || $thumbnail == '-') ? '-' : escapeshellarg($thumbnail)));

        return $command;
    }

    /**
     * ImageMagick�ŕϊ��A�t�@�C���ɏo��
     *
     * @access private
     * @return boolean | object PEAR_Error
     */
    function &_magickSave($source, $thumbnail, $srcsize, $thumbsize)
    {
        $command = $this->_magickCommand($source, $thumbnail, $srcsize, $thumbsize);
        @exec($command, $results, $status);
        if ($status != 0) {
            $errmsg = "convert failed. ( $command . )\n";
            while (!is_null($errstr = array_shift($results))) {
                if ($errstr === '') { break; }
                $errmsg .= $errstr . "\n";
            }
            $retval = &PEAR::raiseError($errmsg);
        } else {
            $retval = true;
        }
        return $retval;
    }

    /**
     * ImageMagick�ŕϊ��A�o�b�t�@�ɕۑ�
     *
     * @access private
     * @return boolean | object PEAR_Error
     */
    function &_magickCapture($source, $srcsize, $thumbsize)
    {
        $command = $this->_magickCommand($source, '-', $srcsize, $thumbsize);
        ob_start();
        @passthru($command, $status);
        $this->buf = ob_get_clean();
        if ($status != 0) {
            $errmsg = "convert failed. ( $command . )\n";
            $retval = &PEAR::raiseError($errmsg);
        } else {
            $retval = true;
        }
        return $retval;
    }

    /**
     * ImageMagick�ŕϊ��A���ڏo��
     *
     * @access private
     * @return boolean | object PEAR_Error
     */
    function &_magickOutput($source, $thumbnail, $srcsize, $thumbsize)
    {
        $command = $this->_magickCommand($source, '-', $srcsize, $thumbsize);
        $name = 'filename="' . basename($thumbnail) . '"';
        if ($this->type == '.png') {
            header('Content-Type: image/png; ' . $name);
            header('Content-Disposition: inline; ' . $name);
        } else {
            header('Content-Type: image/jpeg; ' . $name);
            header('Content-Disposition: inline; ' . $name);
        }
        @passthru($command, $status);
        if ($status != 0) {
            $errmsg = "convert failed. ( $command . )\n";
            $retval = &PEAR::raiseError($errmsg);
        } else {
            $retval = true;
        }
        return $retval;
    }

    // }}}
    // {{{ public utility methods

    /**
     * �T���l�C���T�C�Y�v�Z
     *
     * @access public
     */
    function calc($width, $height)
    {
        // �f�t�H���g�l�E�t���O��ݒ�
        $t_width  = $width;
        $t_height = $height;
        $this->resize = false;
        $this->coord   = false;
        // �\�[�X���T���l�C���̍ő�T�C�Y��菬�����Ƃ��A�\�[�X�̑傫�������̂܂ܕԂ�
        if ($width <= $this->max_width && $height <= $this->max_height) {
            // ���T�C�Y�E�g���~���O�Ƃ��ɖ���
            return ($width . 'x' . $height);
        }
        // �c���ǂ���ɍ��킹�邩�𔻒�i�ő�T�C�Y��艡�� = �����ɍ��킹��j
        if (($width / $height) >= ($this->max_width / $this->max_height)) {
            // ���ɍ��킹��
            $main = $width;
            $sub  = $height;
            $max_main = $this->max_width;
            $max_sub  = $this->max_height;
            $t_main = &$t_width;  // $t_main��$t_sub���T���l�C���T�C�Y��
            $t_sub  = &$t_height; // ���t�@�����X�ɂ��Ă���̂���
            $c_main = 'x';
            $c_sub  = 'y';
        } else {
            // �c�ɍ��킹��
            $main = $height;
            $sub  = $width;
            $max_main = $this->max_height;
            $max_sub  = $this->max_width;
            $t_main = &$t_height;
            $t_sub  = &$t_width;
            $c_main = 'y';
            $c_sub  = 'x';
        }
        // �T���l�C���T�C�Y�ƕϊ��t���O������
        $t_main = $max_main;
        if ($this->trim) {
            // �g���~���O����
            $this->coord = array($c_main => array(0, $main), $c_sub => array(0, $sub));
            $ratio = $t_sub / $max_sub;
            if ($ratio <= 1) {
                // �\�[�X���T���l�C���̍ő�T�C�Y��菬�����Ƃ��A�k�������Ƀg���~���O
                // $t_main == $max_main, $t_sub == $sub
                // ceil($sub * ($t_main / $t_sub)) = ceil($sub * $t_main / $sub) = $t_main = $max_main
                $c_length = $max_main;
            } elseif ($ratio < 1.05) {
                // �k�������ɂ߂ď������Ƃ��A�掿�򉻂�����邽�߂ɏk�������Ƀg���~���O
                $this->coord[$c_sub][0] = floor(($t_sub - $max_sub) / 2);
                $t_sub = $max_sub;
                $c_length = $max_main;
            } else {
                // �T���l�C���T�C�Y�����ς��Ɏ��܂�悤�ɏk�����g���~���O
                $this->resize = true;
                $t_sub = $max_sub;
                $c_length = ceil($sub * ($t_main / $t_sub));
            }
            $this->coord[$c_main] = array(floor(($main - $c_length) / 2), $c_length);
        } else {
            // �A�X�y�N�g����ێ������܂܏k�����A�g���~���O�͂��Ȃ�
            $this->resize = true;
            $t_sub = round($max_main * ($sub / $main));
        }
        // �T���l�C���T�C�Y��Ԃ�
        return ($t_width . 'x' . $t_height);
    }

    /**
     * �\�[�X�摜�̃p�X���擾
     *
     * @access public
     */
    function srcPath($size, $md5, $mime, $FSFullPath = false)
    {
        $directory = $this->getSubDir($this->sourcedir, $size, $md5, $mime, $FSFullPath);
        if (!$directory) {
            return false;
        }

        $basename = $size . '_' . $md5 . $this->mimemap[$mime];

        return $directory . ($FSFullPath ? DIRECTORY_SEPARATOR : '/') . $basename;
    }

    /**
     * �T���l�C���̃p�X���擾
     *
     * @access public
     */
    function thumbPath($size, $md5, $mime, $FSFullPath = false)
    {
        $directory = $this->getSubDir($this->thumbdir, $size, $md5, $mime, $FSFullPath);
        if (!$directory) {
            return false;
        }

        $basename = $size . '_' . $md5;
        if ($this->rotate) {
            $basename .= '_' . str_pad($this->rotate, 3, 0, STR_PAD_LEFT);
        }
        if ($this->trim) {
            $basename .= '_tr';
        }
        $basename .= $this->type;

        return $directory . ($FSFullPath ? DIRECTORY_SEPARATOR : '/') . $basename;
    }

    /**
     * �摜���ۑ������T�u�f�B���N�g���̃p�X���擾
     *
     * @access public
     */
    function getSubDir($basedir, $size, $md5, $mime, $FSFullPath = false)
    {
        if (!is_dir($basedir)) {
            return false;
        }

        $dirID = $this->dirID($size, $md5, $mime);

        if ($FSFullPath) {
            $directory = realpath($basedir) . DIRECTORY_SEPARATOR . $dirID;
        } else {
            $directory = $basedir . '/' . $dirID;
        }

        return $directory;
    }

    /**
     * �摜1000�����ƂɃC���N�������g����f�B���N�g��ID���擾
     *
     * @access public
     */
    function dirID($size = null, $md5 = null, $mime = null)
    {
        if ($size && $md5 && $mime) {
            $icdb = &new IC2DB_Images;
            $icdb->whereAddQUoted('size', '=', $size);
            $icdb->whereAddQuoted('md5',  '=', $md5);
            $icdb->whereAddQUoted('mime', '=', $mime);
            $icdb->orderByArray(array('id' => 'ASC'));
            if ($icdb->find(true)) {
                $this->found = $icdb->toArray();
                return str_pad(ceil($icdb->id / 1000), 5, 0, STR_PAD_LEFT);
            }
        }
        $sql = 'SELECT MAX(' . $this->db->quoteIdentifier('id') . ') + 1 FROM '
             . $this->db->quoteIdentifier($this->ini['General']['table']) . ';';
        $nextid = &$this->db->getOne($sql);
        if (DB::isError($nextid) || !$nextid) {
            $nextid = 1;
        }
        return str_pad(ceil($nextid / 1000), 5, 0, STR_PAD_LEFT);
    }

    // }}
    // {{{ private utility methods

    /**
     * �w�i�F��ݒ�
     *
     * @access private
     */
    function _bgcolor($r, $g, $b)
    {
        if (is_null($r) || is_null($g) || is_null($b)) {
            $this->bgcolor = null;
            return;
        }
        switch ($this->driver) {
            case 'gd':
            case 'imlib2':
                $this->bgcolor = array($r, $g, $b);
                break;
            case 'imagick':
            case 'imagemagick':
                $this->bgcolor = sprintf('rgb(%d,%d,%d)', $r, $g, $b);
                break;
            default:
                $this->bgcolor = sprintf('%d,%d,%d', $r, $g, $b);
        }
    }

    /**
     * �ꎞ�t�@�C���̃p�X��Ԃ�
     * �쐬�����ꎞ�t�@�C���͏I�����Ɏ����ō폜�����
     *
     * @access private
     */
    function _tempnam()
    {
        $tmp = tempnam(realpath($this->cachedir), sprintf('dump_%s_', date('ymdhis')));
        register_shutdown_function(create_function('', '@unlink("'.addslashes($tmp).'");'));
        return $tmp;
    }

    // }}}
    // {{{ error method

    /**
     * �G���[���b�Z�[�W��\�����ďI��
     *
     * @access public
     */
    function error($message = '')
    {
        echo <<<EOF
<html>
<head><title>ImageCache::Error</title></head>
<body>
<p>{$message}</p>
</body>
</html>
EOF;
        exit;
    }

    // }}
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
