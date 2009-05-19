<?php
/**
 * rep2 - �X�^�C���ݒ�
 * �X�L���Ǝ��X�^�C����K�p
 */

/*
conf/conf_user_style.php�������̓X�L����
$MYSTYLE['�J�e�S��<>�Z���N�^<>�v���p�e�B'] = "�l";
��������
$MYSTYLE['�J�e�S��']['�Z���N�^']['�v���p�e�B'] = "�l";
�̏����Őݒ��ς��������ڂ��w�肷��B

�J�e�S�����̖�����'!'������ƁA�l�� !important �����B

�O�҂̏�����mystyle_css��include���ꂽ�Ƃ��ɑ������z��ɕϊ������̂�
�n�߂����҂̏����ŏ������ق����������ǂ��B

��1:$MYSTYLE['read<>.thread_title<>font-size'] = "36px";
    $MYSTYLE['read<>.thread_title<>border-bottom'] = "6px double #808080";
��2:$MYSTYLE['subject']['a:link.thre_title_new']['color'] = "#0000FF";
    $MYSTYLE['subject']['a:hover.thre_title_new']['color'] = "#FF0000";
    $MYSTYLE['subject']['a:link.thre_title_new']['text-decoration] = "underline";

����Ȕz��̃L�[�Ƃ���
'�J�e�S��'='*' �� ���ׂĂ� *_css.php �� !important ���œǂݍ��܂��
'�J�e�S��'='all' �� ���ׂĂ� *_css.php �œǂݍ��܂��
'�J�e�S��'='base' �� style_css.php �ɓǂݍ��܂��
'�Z���N�^'='sb_td' �� subject�e�[�u���i������j���܂Ƃ߂Đݒ�
'�Z���N�^'='sb_td1' �� subject�e�[�u���i���j���܂Ƃ߂Đݒ�
'�Z���N�^'='@import' �� �l��URL�Ƃ݂Ȃ��A@import url('�l'); �Ƃ���
������B

�X�^�C���ݒ�̗D�揇�ʂ�
$MYSTYLE['*'] ������ $MYSTYLE �� $MYSTYLE['all'] �� $STYLE ���� $MYSTYLE['base'] �� $STYLE['��{']
�������A�l�� !important �������ꍇ�͂��ꂪ�ŗD��œK�p�����B
$MYSTYLE['*'] ����� !important ���̒l��JavaScript�ł̕ύX�������Ȃ��̂Œ��ӁI�i�u���E�U�ˑ��H�j
*/

// {{{ ������

$MYSTYLE = parse_mystyle($MYSTYLE);

// }}}
// {{{ parse_mystyle()

/**
 * ���`����$MYSTYLE�𑽎����z��ɕϊ�����
 *
 * @param   array   $MYSTYLE
 * @return  array
 */
function parse_mystyle($MYSTYLE)
{
    $unused = array();

    foreach ($MYSTYLE as $key => $value) {
        if (is_string($value) && strstr($key, '<>')) {
            list($category, $selector, $property) = explode('<>', $key);
            if ($category == '*') {
                $category = 'all!';
            }
            $MYSTYLE[$category][$selector][$property] = $value;
            if (substr($category, -1) == '!') {
                $category = substr($category, 0, -1);
                if (!isset($MYSTYLE[$category])) {
                    $MYSTYLE[$category] = array();
                }
            }
            $unused[] = $key;

        } elseif ($key == '*') {
            if (isset($MYSTYLE['all!']) && is_array($MYSTYLE['all!'])) {
                $MYSTYLE['all!'] = array_merge_recursive($MYSTYLE['all!'], $value);
            } else {
                $MYSTYLE['all!'] = $value;
            }
            if (!isset($MYSTYLE['all'])) {
                $MYSTYLE['all'] = array();
            }
            $unused[] = '*';

        } elseif (substr($key, -1) == '!') {
            $category = substr($key, 0, -1);
            if (!isset($MYSTYLE[$category])) {
                $MYSTYLE[$category] = array();
            }
        }
    }

    foreach ($unused as $key) {
        unset($MYSTYLE[$key]);
    }

    return $MYSTYLE;
}

// }}}

/**
 * @access  public
 * @return  void    CSS�o��
 */
function printMystyleCssByFileName($filename)
{
    global $MYSTYLE;
    
    if (isset($MYSTYLE) && is_array($MYSTYLE)) {
        $category = str_replace('_css.inc', '', basename($filename));
        if (isset($MYSTYLE[$category])) {
            printMyStyleCssByCategory($category);
        }
    }
}

// {{{ printMyStyleCssByCategory() - �J�e�S���[���w�肵�āA$MYSTYLE����CSS��\������

/**
 * @access  public
 * @return  void    CSS�o��
 */
function printMyStyleCssByCategory($category)
{
    echo getMyStyleCss($category);
}

// }}}
// {{{ getMyStyleCss() - $MYSTYLE��CSS�̏����ɕϊ�

/**
 * @return  string
 */
function getMyStyleCss($category)
{
    global $MYSTYLE;
    static $done = array();

    $css = '';

    if (is_array($category)) {
        // {{{ $category ���z��̂Ƃ�

        foreach ($category as $acat) {
            $css .= getMyStyleCss($acat);
        }

        // }}}
    } elseif (is_string($category)) {
        // {{{ $category ��������̂Ƃ�

        // ����
        if ($category == 'style') {
            $css .= getMyStyleCss('base');
        }
        if (!empty($done[$category])) {
            return '';
        }
        $done[$category] = true;

        if ($category != 'all') {
            $css .= getMyStyleCss('all');
        }

        // �X�^�C���V�[�g�ɕϊ�
        if (isset($MYSTYLE[$category]) && is_array($MYSTYLE[$category])) {
            $css .= mystyle_extract($MYSTYLE[$category], false);
        }
        $category .= '!';
        if (isset($MYSTYLE[$category]) && is_array($MYSTYLE[$category])) {
            $css .= mystyle_extract($MYSTYLE[$category], true);
        }

        // }}}
    }

    return $css;
}

// }}}
// {{{ mystyle_extract()

/**
 *�X�^�C���V�[�g�̒l��W�J����
 *
 * @param   array   $style
 * @param   bool    $important
 */
function mystyle_extract($style, $important = false)
{
    $css = "\n";

    foreach ($style as $selector => $properties) {
        if (is_int($selector)) {
            $styles = (is_array($properties)) ? $properties : array($properties);
            foreach ($styles as $style) {
                $css .= $styles . "\n";
            }
        } elseif($selector == '@import') {
            $urls = (is_array($properties)) ? $properties : array($properties);
            foreach ($urls as $url) {
                if (strpos($url, 'http://') === false &&
                    strpos($url, 'https://') === false &&
                    strpos($url, '?') === false)
                {
                    $url .= '?' . $GLOBALS['_conf']['p2_version_id'];
                }
                $css .= "@import url('" . str_replace("'", "''", $url) . "');\n";
            }
        } else {
            $suffix = ($important) ? " !important;\n" : ";\n";
            $selector = mystyle_selector($selector);
            $css .= $selector . " {\n";
            foreach ($properties as $property => $value) {
                if (strpos($property, 'font-family') !== false) {
                    $value = '"' . p2_correct_css_fontfamily($value) . '"';
                } elseif (strpos($property, 'color') !== false) {
                    $value = p2_correct_css_color($value);
                } elseif (strpos($property, 'background') !== false) {
                    $value = "url('" . str_replace("'", "''", $value) . "')";
                }
                $css .= $property . ': ' . $value . $suffix;
            }
            $css .= "}\n";
        }
    }

    return $css;
}

// }}}
// {{{ mystyle_selector()

/**
 * ����ȃZ���N�^���`�F�b�N
 *
 * @param   string  $selector
 * @return  string
 */
function mystyle_selector($selector)
{
    if ($selector == 'sb_td') {
        return 'td.t, td.te, td.tu, td.tn, td.tc, td.to, td.tl, td.ti, td.ts';
    } elseif ($selector == 'sb_td1') {
        return 'td.t2, td.te2, td.tu2, td.tn2, td.tc2, td.to2, td.tl2, td.ti2, td.ts2';
    }
    return $selector;
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
