<?php
/*
    p2 - StrCtl -- �����񑀍�N���X
    
    �N���X���\�b�h�ŗ��p����
*/
class StrCtl{

    /**
     * �t�H�[�����瑗���Ă������[�h���}�b�`�֐��ɓK��������
     *
     * @return string $word_fm �K���p�^�[���BSJIS�ŕԂ��B
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
            if (P2_MBREGEX_AVAILABLE == 0) {
                $word_fm = str_replace('/', '\/', $word_fm);
            }
        }
        return $word_fm;
    }

    /**
     * �}���`�o�C�g�Ή��Ő��K�\���}�b�`����
     *
     * @param string $pattern �}�b�`������BSJIS�œ����Ă���B
     * @param string $target  �����Ώە�����BSJIS�œ����Ă���B
     * @param string $zenhan  �S�p/���p�̋�ʂ����S�ɂȂ����i������I���ɂ���ƁA�������̎g�p�ʂ��{���炢�ɂȂ�B���x���S�͂���قǂł��Ȃ��j
     */
    function filterMatch($pattern, $target, $zenhan = true)
    {
        if ($zenhan) {
            // �S�p/���p�� ���S�� ��ʂȂ��}�b�`
            $pattern = StrCtl::getPatternToHan($pattern);
            $target = StrCtl::getPatternToHan($target, true);
            
        } else {
            // �S�p/���p�� ������x ��ʂȂ��}�b�`
            $pattern = StrCtl::getPatternZenHan($pattern);
        }
        
        // HTML�v�f�Ƀ}�b�`�����Ȃ����߂̔ے��ǂ݃p�^�[����t��
        $pattern = '(' . $pattern . ')(?![^<]*>)';

        if (P2_MBREGEX_AVAILABLE == 1) {
            $result = @mb_eregi($pattern, $target);    // None|Error:FALSE
        } else {
            // UTF-8�ɕϊ����Ă��珈������
            $pattern_utf8 = '/' . mb_convert_encoding($pattern, 'UTF-8', 'SJIS-win') . '/iu';
            $target_utf8 = mb_convert_encoding($target, 'UTF-8', 'SJIS-win');
            $result = @preg_match($pattern_utf8, $target_utf8);    // None:0, Error:FALSE
            //$result = mb_convert_encoding($result, 'SJIS-win', 'UTF-8');
        }
        
        if (!$result) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * �}���`�o�C�g�Ή��Ń}�[�L���O����
     *
     * @param string $pattern �}�b�`������BSJIS�œ����Ă���B
     * @param string $target �u���Ώە�����BSJIS�œ����Ă���B
     */
    function filterMarking($pattern, &$target, $marker = '<b class="filtering">\\1</b>')
    {
        // �S�p/���p���i������x�j��ʂȂ��}�b�`
        $pattern = StrCtl::getPatternZenHan($pattern);

        // HTML�v�f�Ƀ}�b�`�����Ȃ����߂̔ے��ǂ݃p�^�[����t��
        $pattern = '(' . $pattern . ')(?![^<]*>)';

        if (P2_MBREGEX_AVAILABLE == 1) {
            $result = @mb_eregi_replace($pattern, $marker, $target);    // Error:FALSE
        } else {
            // UTF-8�ɕϊ����Ă��珈������
            $pattern_utf8 = '/' . mb_convert_encoding($pattern, 'UTF-8', 'SJIS-win') . '/iu';
            $target_utf8 = mb_convert_encoding($target, 'UTF-8', 'SJIS-win');
            $result = @preg_replace($pattern_utf8, $marker, $target_utf8);
            $result = mb_convert_encoding($result, 'SJIS-win', 'UTF-8');
        }

        if ($result === FALSE) {
            return $target;
        }
        return $result;
    }
    
    /**
     * �S�p/���p���i������x�j��ʂȂ��p�b�`���邽�߂̐��K�\���p�^�[���𓾂�
     */
    function getPatternZenHan($pattern)
    {
        $pattern_han = StrCtl::getPatternToHan($pattern);
        
        if ($pattern != $pattern_han) {
            $pattern = $pattern.'|'.$pattern_han;
        }
        $pattern_zen = StrCtl::getPatternToZen($pattern);
        
        if ($pattern != $pattern_zen) {
            $pattern = $pattern.'|'.$pattern_zen;
        }
        
        return $pattern;
    }
    
    /**
     * �i�p�^�[���j������𔼊p�ɂ���
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
     */
    function getKigouPattern($no_escape = false)
    {
        $kigou['zen'] = array('�M', '�i', '�j', '�H', '��', '��', '��', '��', '��',   '��', '�I',   '��', '�{', '��',  '��', '�`', '�b', '�o', '�p', '�Q');
        $kigou['han'] = array('`',  '\(', '\)', '\?', '#',  '\$', '%',  '@',  '&lt;', '&gt;', '\!', '\*', '\+', '&amp;', '=', '~', '\|', '\{', '\}', '_');
        
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

?>
