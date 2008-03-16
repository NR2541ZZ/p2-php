<?php
/**
 * Thumbnailer_Imlib2
 * PHP Versions 4 and 5
 */

require_once dirname(__FILE__) . '/Common.php';

// {{{ Thumbnailer_Imlib2

/**
 * Image manipulation class which uses imlib2 php extension.
 */
class Thumbnailer_Imlib2 extends Thumbnailer_Common
{
    // {{{ save()

    /**
     * Convert and save.
     *
     * @access public
     * @param string $source
     * @param string $thumbnail
     * @param array $size
     * @return boolean
     * @throws PEAR_Error
     */
    function save($source, $thumbnail, $size)
    {
        $dst = &$this->_convert($source, $size);
        $err = 0;
        // �T���l�C����ۑ�
        if ($this->_png) {
            imlib2_image_set_format($dst, 'png');
            $result = imlib2_save_image($dst, $thumbnail, $err);
        } else {
            imlib2_image_set_format($dst, 'jpeg');
            $result = imlib2_save_image($dst, $thumbnail, $err, $this->_quality);
        }
        imlib2_free_image($dst);
        if (!$result) {
            $retval = &PEAR::raiseError("Failed to create a thumbnail. ({$thumbnail}:{$err})");
        } else {
            $retval = true;
        }
        return $retval;
    }

    // }}}
    // {{{ capture()

    /**
     * Convert and capture.
     *
     * imlib2_dump_image() �̏o�͂��L���v�`�����悤�Ƃ���Ƃ��܂������Ȃ��̂�
     * ��������ꎞ�t�@�C���ɏ����o�����f�[�^��ǂݍ���
     *
     * @access public
     * @param string $source
     * @param array $size
     * @return string
     * @throws PEAR_Error
     */
    function capture($source, $size)
    {
        $dst = &$this->_convert($source, $size);
        $err = 0;
        // �T���l�C�����쐬
        $tempfile = $this->_tempnam();
        if ($this->_png) {
            imlib2_image_set_format($dst, 'png');
            $result = imlib2_save_image($dst, $tempfile, $err);
        } else {
            imlib2_image_set_format($dst, 'jpeg');
            $result = imlib2_save_image($dst, $tempfile, $err, $this->_quality);
        }
        imlib2_free_image($dst);
        if (!$result) {
            $retval = &PEAR::raiseError("Failed to create a thumbnail. ({$thumbnail}:{$err})");
        } else {
            $retval = file_get_contents($tempfile);
        }
        return $retval;
    }

    // }}}
    // {{{ output()

    /**
     * Convert and output.
     *
     * @access public
     * @param string $source
     * @param string $name
     * @param array $size
     * @return boolean
     * @throws PEAR_Error
     */
    function output($source, $name, $size)
    {
        $dst = &$this->_convert($source, $size);
        $err = 0;
        // �T���l�C�����o��
        $this->_httpHeader($name);
        if ($this->_png) {
            imlib2_image_set_format($dst, 'png');
            $result = imlib2_dump_image($dst, $err);
        } else {
            imlib2_image_set_format($dst, 'jpeg');
            $result = imlib2_dump_image($dst, $err, $this->_quality);
        }
        imlib2_free_image($dst);
        if (!$result) {
            $retval = &PEAR::raiseError("Failed to create a thumbnail. ({$name}:{$err})");
        } else {
            $retval = true;
        }
        return $retval;
    }

    // }}}
    // {{{ _convert()

    /**
     * Image conversion abstraction.
     *
     * @access protected
     * @param string $source
     * @param array $size
     * @return resource Unknown (imlib2?)
     */
    function _convert($source, $size)
    {
        extract($size);
        $err = 0;
        // �\�[�X�̃C���[�W�X�g���[�����擾
        $src = imlib2_load_image($source, $err);
        if ($err) {
            $error = &PEAR::raiseError("Failed to load the image. ({$source}:{$err})");
            return $error;
        }
        // �T���l�C���̃C���[�W�X�g���[�����쐬
        $dst = imlib2_create_image($tw, $th);
        imlib2_image_fill_rectangle($dst, 0, 0, $tw, $th, $this->_bgcolor[0], $this->_bgcolor[1], $this->_bgcolor[2], $this->_bgcolor[3]);
        // �\�[�X���T���l�C���ɃR�s�[
        /* imlib_blend_image_onto_image(int dstimg, int srcimg, int malpha, int srcx, int srcy, int srcw, int srch,
            int dstx, int dsty, int dstw, int dsth, char dither, char blend, char alias) */
        imlib2_blend_image_onto_image($dst, $src, 255, $sx, $sy, $sw, $sh, 0, 0, $tw, $th, false, true, $this->_resampling);
        imlib2_free_image($src);
        // ��]
        if ($this->_rotation) {
            imlib2_image_orientate($dst, $this->_rotation / 90);
        }
        return $dst;
    }

    // }}}
}

// }}}

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