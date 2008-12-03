<?php
/**
 * p2 - �����񑀍�N���X
 * static���\�b�h�ŗ��p����
 */
class StrCtl
{
    /**
     * �t�H�[�����瑗���Ă������[�h���}�b�`�֐��ɓK��������
     *
     * @static
     * @access  public
     * @return  string  $word_fm  �K���p�^�[���BSJIS�ŕԂ��B
     */
    function wordForMatch($word, $method = 'regex')
    {
        $word_fm = $word;
        
        // �u���̂܂܁v�łȂ���΁A�S�p�󔒂𔼊p�󔒂ɋ���
        if ($method != 'just') {
            $word_fm = mb_convert_kana($word_fm, 's');
        }
        
        $word_fm = trim($word_fm);
        $word_fm = htmlspecialchars($word_fm, ENT_NOQUOTES);
        
        if (in_array($method, array('and', 'or', 'just'))) {
            // preg_quote()��2�o�C�g�ڂ�0x5B("[")��"�["�Ȃǂ��ϊ�����Ă��܂��̂�
            // UTF-8�ɂ��Ă��琳�K�\���̓��ꕶ�����G�X�P�[�v
            $word_fm = mb_convert_encoding($word_fm, 'UTF-8', 'SJIS-win');
            if (P2_MBREGEX_AVAILABLE == 1) {
                $word_fm = preg_quote($word_fm);
            } else {
                $word_fm = preg_quote($word_fm, '/');
            }
            $word_fm = mb_convert_encoding($word_fm, 'SJIS-win', 'UTF-8');
            
        // ���Aregex�i���K�\���j�Ȃ�
        } else {
            $word_fm = str_replace('/', '\/', $word_fm);
            
            $tmp_pattern = '/' . mb_convert_encoding($word_fm, 'UTF-8', 'SJIS-win') . '/u';
            if (false === @preg_match($tmp_pattern, '.')) {
                P2Util::pushInfoHtml(
                    sprintf(
                        'p2 warning: �t�B���^���̐��K�\���Ɍ�肪����܂� "%s"',
                        hs($word_fm)
                    )
                );
                $word_fm = '';
            }
            
            if (P2_MBREGEX_AVAILABLE == 0) {
                $word_fm = str_replace('/', '\/', $word_fm);
            }
            // �����̃��C���h�J�[�h�͏������Ă��܂�
            $word_fm = rtrim($word_fm, '.+*');
        }
        return $word_fm;
    }

    /**
     * �}���`�o�C�g�Ή��Ő��K�\���}�b�`���肷��
     *
     * @static
     * @access  public
     * @param   string  $pattern    �}�b�`������BSJIS�œ����Ă���B
     * @param   string  $targetHtml �����Ώە�����BSJIS�œ����Ă���BHTML
     * @param   int     $zenhan     1:�S�p/���p�̋�ʂ����S�ɂȂ���
     *                             �i������I���ɂ���ƁA�������̎g�p�ʂ��{���炢�ɂȂ�B���x���S�͂���قǂł��Ȃ��j
     *                              2:�S�p/���p�� ������x ��ʂȂ��}�b�`
     *                              0:�S�p/���p�� ��ʂ��Ȃ�
     * @return  string|false      �}�b�`������}�b�`��������A�}�b�`���Ȃ�������false��Ԃ�
     */
    function filterMatch($pattern, $targetHtml, $zenhan = 1)
    {
        global $res_filter;
        
        if ($res_filter['method'] == 'regex') {
            $pattern = StrCtl::replaceRegexAnyChar($pattern);
        }
        
        // ID�t�B���^�����O���́A�S�p���p����ɋ�ʂ��Ȃ�
        if ($res_filter['field'] == 'id' && $res_filter['method'] == 'just') {
            $zenhan = 0;
        }
        
        if ($zenhan == 1) {
            // �S�p/���p�� ���S�� ��ʂȂ��}�b�`
            $pattern    = StrCtl::getPatternToHan($pattern);
            $targetHtml = StrCtl::getPatternToHan($targetHtml, true);
        
        } elseif ($zenhan == 2) {
            // �S�p/���p�� ������x ��ʂȂ��}�b�`
            $pattern = StrCtl::getPatternZenHan($pattern); // ���K�\���p�^�[��
        }
        
        // HTML�v�f�Ƀ}�b�`�����Ȃ����߂̔ے��ǂ݃p�^�[����t��
        $pattern = $pattern . '(?![^<]*>)';

        if (P2_MBREGEX_AVAILABLE == 1) {
            $result = mb_eregi($pattern, $targetHtml, $matches);    // None|Error:FALSE
        } else {
            // UTF-8�ɕϊ����Ă��珈������
            $pattern = str_replace('/', '\/', $pattern);
            $pattern_utf8 = '/' . mb_convert_encoding($pattern, 'UTF-8', 'SJIS-win') . '/iu';
            $target_utf8 = mb_convert_encoding($targetHtml, 'UTF-8', 'SJIS-win');
            $result = preg_match($pattern_utf8, $target_utf8, $matches);    // None:0, Error:FALSE
            //$result = mb_convert_encoding($result, 'SJIS-win', 'UTF-8');
        }
        
        if (!$result) {
            return false;
        }
        return $matches[0];
    }
    
    /**
     * �}���`�o�C�g�Ή���HTML���̌��������}�[�L���O����
     *
     * @static
     * @access  public
     * @param   string  $pattern    �}�b�`������BSJIS�œ����Ă���B���炩����htmlspecialchars()����Ă��邱�ƁB
     * @param   string  $targetHtml �u���Ώە�����BSJIS�œ����Ă���BHTML�B
     * @return  string  HTML
     */
    function filterMarking($pattern, $targetHtml, $marker = '<b class="filtering">\\0</b>')
    {
        global $res_filter;
        
        if ($res_filter['method'] == 'regex') {
            $pattern = StrCtl::replaceRegexAnyChar($pattern);
        }
        
        // �S�p/���p���i������x�j��ʂȂ��}�b�`
        $pattern = StrCtl::getPatternZenHan($pattern); // ���K�\���p�^�[��

        // HTML�v�f�Ƀ}�b�`�����Ȃ����߂̔ے��ǂ݃p�^�[����t��
        $pattern = $pattern . '(?![^<]*>)';

        $result = false;
        if (P2_MBREGEX_AVAILABLE == 1) {
            $result = mb_eregi_replace($pattern, $marker, $targetHtml);    // Error => FALSE
        } else {
            // UTF-8�ɕϊ����Ă��珈������
            $pattern = str_replace('/', '\/', $pattern);
            $pattern_utf8 = '/' . mb_convert_encoding($pattern, 'UTF-8', 'SJIS-win') . '/iu';
            $target_utf8 = mb_convert_encoding($targetHtml, 'UTF-8', 'SJIS-win');
            $result = preg_replace($pattern_utf8, $marker, $target_utf8);
            $result = mb_convert_encoding($result, 'SJIS-win', 'UTF-8');
        }

        if ($result === false) {
            return $targetHtml;
        }
        return $result;
    }
    
    /**
     * ���K�\�����́u.�v���i�^�O���܂܂Ȃ��悤�Ɂj�u������
     *
     * @static
     * @access  private
     * @param   string  $regex
     * @return  string
     */
    function replaceRegexAnyChar($regex, $replace = '[^<>]')
    {
        static $cache_;
        
        // �ꉞ�L���b�V�����Ă���
        if (isset($cache_[$regex])) {
            return $cache_[$regex];
        }
        
        $len = strlen($regex);
        $new = '';
        $esc = false;
        $cls = false;

        for ($i = 0; $i < $len; $i++) {
            $c = $regex[$i];

            if ($c == '\\') {
                $esc = !$esc;
                $new .= '\\';
                continue;
            }

            switch ($c) {
            case '.':
                if (!$esc && !$cls) {
                    $new .= $replace;
                } else {
                    $new .= '.';
                }
                break;

            case '[':
                if (!$esc && !$cls) {
                    $cls = true;
                }
                $new .= '[';
                break;

            case ']':
                if (!$esc && $cls) {
                    $cls = false;
                }
                $new .= ']';
                break;

            default:
                $new .= $c;
            }

            $esc = false;
        }
        
        $cache_[$regex] = $new;
        return $new;
    }

    /**
     * �S�p/���p���i������x�j��ʂȂ��p�b�`���邽�߂̐��K�\���p�^�[���𓾂�
     *
     * @static
     * @access  private
     * @return  string
     */
    function getPatternZenHan($pattern)
    {
        $petterns = array();
        
        $pattern_han = StrCtl::getPatternToHan($pattern);
        if ($pattern != $pattern_han) {
            $petterns[] = $pattern_han;
        }
        $pattern_zen = StrCtl::getPatternToZen($pattern);
        if ($pattern != $pattern_zen) {
            $petterns[] = $pattern_zen;
        }
        if ($petterns) {
            $pattern = '(?:' . implode('|', array_merge(array($pattern), $petterns)) . ')';
        }

        return $pattern;
    }

    /**
     * �i�p�^�[���j������𔼊p�ɂ���
     *
     * @static
     * @access  private
     * @return  string
     */
    function getPatternToHan($pattern, $no_escape = false)
    {
        $kigou = StrCtl::getKigouPattern($no_escape);
        
        // ����
        //$pattern = str_replace($kigou['zen'], $kigou['han'], $pattern);

        if (P2_MBREGEX_AVAILABLE == 1) {

            foreach ($kigou['zen'] as $k => $v) {
        
                $word_fm = $kigou['zen'][$k];
                
                /*
                // preg_quote()��2�o�C�g�ڂ�0x5B("[")��"�["�Ȃǂ��ϊ�����Ă��܂��̂�
                // UTF-8�ɂ��Ă��琳�K�\���̓��ꕶ�����G�X�P�[�v
                $word_fm = mb_convert_encoding($word_fm, 'UTF-8', 'SJIS-win');
                $word_fm = preg_quote($word_fm);
                $word_fm = mb_convert_encoding($word_fm, 'SJIS-win', 'UTF-8');
                */
                
                $pattern = mb_ereg_replace($word_fm, $kigou['han'][$k], $pattern);
            }
        }
        
        //echo $pattern;
        $pattern = mb_convert_kana($pattern, 'rnk');
        
        
        
        return $pattern;
    }
    
    /**
     * �i�p�^�[���j�������S�p�ɂ���
     *
     * @static
     * @access  private
     * @return  string
     */
    function getPatternToZen($pattern, $no_escape = false)
    {
        $kigou = StrCtl::getKigouPattern($no_escape);
        
        // ����
        // $pattern = str_replace($kigou['han'], $kigou['zen'], $pattern);
        
        if (P2_MBREGEX_AVAILABLE == 1) {
            foreach ($kigou['zen'] as $k => $v) {
        
                $word_fm = $kigou['han'][$k];
                
                
                // preg_quote()��2�o�C�g�ڂ�0x5B("[")��"�["�Ȃǂ��ϊ�����Ă��܂��̂�
                // UTF-8�ɂ��Ă��琳�K�\���̓��ꕶ�����G�X�P�[�v
                $word_fm = mb_convert_encoding($word_fm, 'UTF-8', 'SJIS-win');
                $word_fm = preg_quote($word_fm);
                $word_fm = mb_convert_encoding($word_fm, 'SJIS-win', 'UTF-8');
                
                
                $pattern = mb_ereg_replace($word_fm, $kigou['zen'][$k], $pattern);
            }
        }
        
        $pattern = mb_convert_kana($pattern, 'RNKV');
        
        return $pattern;
    }
    
    /**
     * �S�p/���p�̋L���p�^�[���𓾂�
     *
     * @static
     * @access  private
     * @return  string
     */
    function getKigouPattern($no_escape = false)
    {
        $kigou['zen'] = array(
            '�M', '�i', '�j', '�H', '��', '��', '��', '��', '��',   '��', '�I',
            '��', '�{', '��',  '��', '�`', '�b', '�o', '�p', '�Q'
        );
        $kigou['han'] = array(
            '`',  '\(', '\)', '\?', '#',  '\$', '%',  '@',  '&lt;', '&gt;', '\!',
            '\*', '\+', '&amp;', '=', '~', '\|', '\{', '\}', '_'
        );
        
        // NG ---- $ < 
        // str_replace ��ʂ������ɁA����������̉���B�B
        //$kigou['zen'] = array('�M', '�i', '�j', '�H', '��', '��', '��', '��', '�I',   '��', '�{', '��');
        //$kigou['han'] = array('`',  '\(', '\)', '\?', '#',  '%',  '@',  '&gt;', '\!', '\*', '\+', '&amp;');

        if ($no_escape) {
            $kigou['han'] = array_map(create_function('$str', 'return ltrim($str, "\\\");'), $kigou['han']);
            /*
            foreach ($kigou['han'] as $k => $v) {
                $kigou['han'][$k] = ltrim($v, '\\');
            }
            */
        }
        
        return $kigou;
    }
}
