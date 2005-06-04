<?php
/*
    p2 - StrCtl -- �����񑀍�N���X
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
     * @param string $target �����Ώە�����BSJIS�œ����Ă���B
     */
    function filterMatch($pattern, &$target)
    {
        // �S�p/���p���i������x�j��ʂȂ��}�b�`
        $pattern_han = mb_convert_kana($pattern, 'rnk');
        if ($pattern != $pattern_han) {
            $pattern = $pattern.'|'.$pattern_han;
        }
        $pattern_zen = mb_convert_kana($pattern, 'RNKV');
        if ($pattern != $pattern_zen) {
            $pattern = $pattern.'|'.$pattern_zen;
        }
        
        // HTML�v�f�Ƀ}�b�`�����Ȃ����߂̔ے��ǂ݃p�^�[����t��
        $pattern = '(' . $pattern . ')(?![^<]*>)';

        if (P2_MBREGEX_AVAILABLE == 1) {
            $result = @mb_eregi($pattern, $target);
        } else {
            // UTF-8�ɕϊ����Ă��珈������
            $pattern_utf8 = '/' . mb_convert_encoding($pattern, 'UTF-8', 'SJIS-win') . '/iu';
            $target_utf8 = mb_convert_encoding($target, 'UTF-8', 'SJIS-win');
            $result = @preg_match($pattern_utf8, $target_utf8);
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
        $pattern_han = mb_convert_kana($pattern, 'rnk');
        if ($pattern != $pattern_han) {
            $pattern = $pattern.'|'.$pattern_han;
        }
        $pattern_zen = mb_convert_kana($pattern, 'RNKV');
        if ($pattern != $pattern_zen) {
            $pattern = $pattern.'|'.$pattern_zen;
        }
        
        // HTML�v�f�Ƀ}�b�`�����Ȃ����߂̔ے��ǂ݃p�^�[����t��
        $pattern = '(' . $pattern . ')(?![^<]*>)';

        if (P2_MBREGEX_AVAILABLE == 1) {
            $result = @mb_eregi_replace($pattern, $marker, $target);
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
}

?>
