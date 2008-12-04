<?php
/**
 * rep2 - �X�^�C���ݒ�
 * �X�L���Ǝ��X�^�C����K�p
 */

/*
conf/conf_user_style.php�������̓X�L����
$MYSTYLE['�J�e�S��<>�v�f<>�v���p�e�B'] = "�l";
��������
$MYSTYLE['�J�e�S��']['�v�f']['�v���p�e�B'] = "�l";
�̏����Őݒ��ς��������ڂ��w�肷��B

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
'�J�e�S��'='subject', '�v�f'='sb_td' �� subject�e�[�u���i������j���܂Ƃ߂Đݒ�
'�J�e�S��'='subject', '�v�f'='sb_td1' �� subject�e�[�u���i���j���܂Ƃ߂Đݒ�
������B

�X�^�C���ݒ�̗D�揇�ʂ�
$MYSTYLE['*'] ������ $MYSTYLE �� $MYSTYLE['all'] �� $STYLE ���� $MYSTYLE['base'] �� $STYLE['��{']
�������A�l�� !important �������ꍇ�͂��ꂪ�ŗD��œK�p�����B
$MYSTYLE['*'] ����� !important ���̒l��JavaScript�ł̕ύX�������Ȃ��̂Œ��ӁI�i�u���E�U�ˑ��H�j
*/

// {{{ ������

$MYSTYLE = parse_mystyle($MYSTYLE);
$MYSTYLE_DONE = array();
if (!isset($MYSTYLE['*'])) {
    $MYSTYLE_DONE['*'] = TRUE;
}
if (!isset($MYSTYLE['all'])) {
    $MYSTYLE_DONE['all'] = TRUE;
}

// }}}
// {{{ parse_mystyle() - $MYSTYLE�𑽎����z��ɕϊ�

function parse_mystyle($MYSTYLE)
{
    if (isset($MYSTYLE) && is_array($MYSTYLE)) {
        foreach ($MYSTYLE as $key => $value) {
            if (is_string($value) && strstr($key, '<>')) {
                list($category, $element, $property) = explode('<>', $key);
                $MYSTYLE[$category][$element][$property] = $value;
                unset($MYSTYLE[$key]);
            }
        }
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
    global $MYSTYLE, $MYSTYLE_DONE;
    
    $stylesheet = "\n";
    $suffix = '';
    
    if (is_array($category)) {
        // {{{ $category ���z��̂Ƃ�
        foreach ($category as $acat) {
            $stylesheet .= getMyStyleCss($acat, $important);
        }
        // }}}
    } elseif (is_string($category)) {
        // {{{ $category ��������̂Ƃ�
        
        // ����
        if ($category == 'style') {
            $stylesheet .= getMyStyleCss('base');
        }
        if (!empty($MYSTYLE_DONE[$category])) {
            return '';
        }
        $MYSTYLE_DONE[$category] = TRUE;
        
        // ���ʂ�$MYSTYLE�̏���
        if ($category == '*') {
            $suffix = ' !important';
        } else {
            if ($category != 'all') {
                $stylesheet .= getMyStyleCss('all');
            }
            $stylesheet .= getMyStyleCss('*');
            $suffix = '';
        }
        
        // �X�^�C���V�[�g�ɕϊ�
        if (isset($MYSTYLE[$category]) && is_array($MYSTYLE[$category])) {
            foreach ($MYSTYLE[$category] as $element => $properties) {
                $element = mystyle_spelement($category, $element);
                $stylesheet .= $element . " {\n";
                foreach ($properties as $property => $value) {
                    $stylesheet .= "\t" . $property . ": " . $value . $suffix . ";\n";
                }
                $stylesheet .= "}\n";
            }
        }
        // }}}
    }
    return $stylesheet;
}

// }}}
// {{{ mystyle_spelement() - ����ȗv�f�̃L�[���`�F�b�N

/**
 * @return  string
 */
function mystyle_spelement($category, $element)
{
    if ($category == 'subject' && $element == 'sb_td') {
        $element = 'td.t, td.te, td.tu, td.tn, td.tc, td.to, td.tl, td.ti, td.ts';
    } elseif ($category == 'subject' && $element == 'sb_td1') {
        $element = 'td.t2, td.te2, td.tu2, td.tn2, td.tc2, td.to2, td.tl2, td.ti2, td.ts2';
    }
    return $element;
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
