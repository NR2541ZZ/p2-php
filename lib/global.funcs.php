<?php
// �O���[�o���֐�

/**
 * htmlspecialchars() �̕ʖ��݂����Ȃ���
 *
 * @param   string  $alt  �l����('')�̂Ƃ��̑�֕�����
 * @return  string
 */
function hs($str, $alt = '', $quoteStyle = ENT_QUOTES)
{
    return (isset($str) && strlen($str) > 0) ? htmlspecialchars($str, $quoteStyle) : $alt;
}

/**
 * notice �̗}�������Ă���� hs()
 * �Q�ƂŒl���󂯎��̂̓C�}�C�`�����A�������Ȃ����notice�̗}�����ł��Ȃ�
 *
 * @param   &string  $str  ������ϐ��̎Q��
 * @return  string
 */
function hsi(&$str, $alt = '', $quoteStyle = ENT_QUOTES)
{
    return (isset($str) && strlen($str) > 0) ? htmlspecialchars($str, $quoteStyle) : $alt;
}

/**
 * echo hs()
 *
 * @return  void
 */
function eh($str, $alt = '', $quoteStyle = ENT_QUOTES)
{
    echo hs($str, $alt, $quoteStyle);
}

/**
 * echo hs() �inotice��}������j
 *
 * @param   &string  $str  ������ϐ��̎Q��
 * @return  void
 */
function ehi(&$str, $alt = '', $quoteStyle = ENT_QUOTES)
{
    echo hs($str, $alt, $quoteStyle);
}

/**
 * ���݂��Ȃ��ϐ��� notice ���o�����ƂȂ��A�ϐ��̒l���擾����
 *
 * ���̊֐��Ŕz��̒��g���擾���悤�Ƃ���ƁA�z�񂻂̂��̂��쐬���Ă��܂����Ƃ�����̂ɒ��ӁB
 * �܂� $hoge �����݂��Ȃ����ɁAgeti($hoge['huga']) �Ƃ���ƁA $hoge �� array('huga' => null) �ƂȂ��Ă��܂��B
 *
 * @return  mixed
 */
function geti(&$var, $alt = null)
{
    return isset($var) ? $var : $alt;
}

/**
 * ���s��t���ĕ�������o�͂���Bcli(\n)��web(<br>)�ŏo�͂��ω�����B
 * �����̕�����͕�����邱�Ƃ��\�B�������Ȃ���Ή��s�������o�͂���B
 *
 * @return  void
 */
function echoln()
{
    $n = (php_sapi_name() == 'cli') ? "\n" : '<br>';
    
    if ($args = func_get_args()) {
        foreach ($args as $v) {
            echo $v . $n;
        }
    } else {
        echo $n;
    }
}


//=================================================================
// p2����
//=================================================================

// {{{ p2_correct_css_fonts()

/**
 * �X�^�C���V�[�g�̃t�H���g�w��𒲐�����
 *
 * @param string|array $fonts
 * @return string
 */
function p2_correct_css_fontfamily($fonts)
{
    if (is_string($fonts)) {
        $fonts = preg_split('/(["\'])?\\s*,\\s*(?(1)\\1)/', trim($fonts, " \t\"'"));
    } elseif (!is_array($fonts)) {
        return '';
    }
    $fonts = '"' . implode('","', $fonts) . '"';
    $fonts = preg_replace('/"(serif|sans-serif|cursive|fantasy|monospace)"/', '\\1', $fonts);
    return trim($fonts, '"');
}

// }}}
// {{{ p2_correct_css_color()

/**
 * �X�^�C���V�[�g�̐F�w��𒲐�����
 *
 * @param   string $color
 * @return  string
 */
function p2_correct_css_color($color)
{
    return preg_replace('/^#([0-9A-F])([0-9A-F])([0-9A-F])$/i', '#\\1\\1\\2\\2\\3\\3', $color);
}

// }}}
// {{{ p2_escape_css_url()

/**
 * �X�^�C���V�[�g��URL���G�X�P�[�v����
 *
 * CSS�œ��ɈӖ��̂���g�[�N���ł���󔒕����A�V���O���N�H�[�g�A
 * �_�u���N�H�[�g�A���ʁA�o�b�N�X���b�V����URL�G���R�[�h����
 *
 * @param   string $url
 * @return  string
 */
function p2_escape_css_url($url)
{
    if (strpos($url, chr(0)) !== false) {
        return '';
    }
    return str_replace(array( "\t",  "\n",  "\r",   ' ',   '"',   "'",   '(',   ')',  '\\'),
                       array('%09', '%0A', '%0D', '%20', '%22', '%27', '%28', '%29', '%5C'),
                       $url);
}

// }}}

/**
 * p2 error ���b�Z�[�W��\�����ďI��
 *
 * @param   string  $err    �G���[�T�v
 * @param   string  $msg    �ڍׂȐ���
 * @param   boolean $hs     �ڍׂȐ�����HTML�G�X�P�[�v����Ȃ�true
 * @return  void
 */
function p2die($err, $msg = null, $hs = false)
{
    echo '<html><head><title>p2 error</title></head><body>';
    printf('<h4>p2 error: %s</h4>', htmlspecialchars($err, ENT_QUOTES));
    if ($msg !== null) {
        if ($hs) {
            printf('<p>%s</p>', nl2br(htmlspecialchars($msg, ENT_QUOTES)));
        } else {
            echo $msg;
        }
    }
    P2Util::printInfoHtml();
    echo '</body></html>';
    
    exit;
}
