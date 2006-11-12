<?php
/**
 * SJIS�̂��߂̃N���X�B�X�^�e�B�b�N���\�b�h�ŗ��p����B
 * SJIS������̖��������Ă���̂��C���J�b�g����B
 *
 * @author aki
 * @since  2006/10/02
 */
class StrSjis{

    /**
     * �Q�l�f�[�^
     * SJIS 2�o�C�g�̑�1�o�C�g�͈� 129�`159�A224�`239�i0x81�`0x9F�A0xE0�`0xEF�j
     * SJIS 2�o�C�g�̑�2�o�C�g�͈� 64�`126�A128�`252�i0x40�`0x7E�A0x80�`0xFC�j�i��1�o�C�g�͈͂����Ă���j
     * SJIS �p����(ASCII) 33�`126�i0x21�`0x7E�j 32 ��
     * SJIS ���p�J�i161�`223�i0xA1�`0xDF�j(��2�o�C�g�̈�) 
     */
    
    /*
    // SJIS�����������������O�̑�1�o�C�g�����őł�������邩�ǂ����̖ڎ��p�e�X�g�R�[�h
    // ���ł�������邪�A�����݂̂̃`�F�b�N�ł͕s���B�擪���珇��2�o�C�g�̑g�𒲂ׂ�K�v������B
    for ($i = 0; $i <= 255; $i++) {
        if (StrSjis::isSjis1stByte($i)) {
            for ($j = 0; $j <= 255; $j++) {
                if (StrSjis::isSjisCrasherCode($j)) {
                    echo $i . ' '. pack('C*', $i) . pack('C*', $j) . "<br><br>";
                }
            }
        }
    }
    */

    /**
     * SJIS������̖������A���o�C�g�ł���΁A�^�O������v���ƂȂ�̂ŃJ�b�g����B
     *
     * @access  public
     * @return  string
     */
    function fixSjis($str)
    {
        if (strlen($str) == 0) {
            return;
        }

        $un = unpack('C*', $str);
    
        $after_sjis1st = false;
        $after_crasher = false;
        foreach ($un as $v) {
            if ($after_sjis1st) {
                $after_sjis1st = false;
                $after_crasher = false;
            } else {
                if (StrSjis::isSjis1stByte($v)) {
                    $after_sjis1st = true;
                    $after_crasher = true;
                } elseif (StrSjis::isSjisCrasherCode($v)) {
                    $after_crasher = true;
                } else {
                    $after_crasher = false;
                }
            }
        }
    
        if ($after_crasher) {
            $str = substr($str, 0, -1);
        }
        return $str;
    
        /*
        // �����݂̂��`�F�b�N���邽�߂̃R�[�h�B����ł͕s���B
        if (StrSjis::isSjisCrasherCode($un[$count]) && !StrSjis::isSjis1stByte($un[$count-1])) {
            $str = substr($str, 0, -1);
            return $str;
        }
        */
    }

    /**
     * SJIS�Ŗ����ɂ���Ɓi�����J�n�^�O�Ƃ������āj������������\���̂���R�[�h�͈̔́i10�i���j
     * ��1�o�C�g�͈͂����łȂ���2�o�C�g�͈͂ł�������������R�[�h�͂���
     * 129-159 224-252 �i�ڎ��Œ��ׂ��j
     * �ڎ��p�e�X�g�R�[�h
     * for ($i = 0; $i <= 255; $i++) {
     *    echo $i . ': '. pack('C*', $i) . "<br><br>";
     * }
     * �i�Q�l SJIS 2�o�C�g�R�[�h�͈͂̂�����1�o�C�g�R�[�h�ɓ��Ă͂܂�Ȃ��̂� 128-160 224-252�j
     *
     * @return  boolean  �R�[�h�ԍ������������͈͂ł���� true ��Ԃ�
     */
    function isSjisCrasherCode($int)
    {
        if (129 <= $int && $int <= 159 or 224 <= $int && $int <= 252) {
            return true;
        }
        return false;
    }

    /**
     * SJIS 2�o�C�g�̑�1�o�C�g�͈͂��ǂ����𒲂ׂ� 129�`159�A224�`239�i0x81�`0x9F�A0xE0�`0xEF�j
     *
     * @return  boolean  �R�[�h�ԍ�����1�o�C�g�͈͂ł���� true ��Ԃ�
     */
    function isSjis1stByte($int)
    {
        if (129 <= $int && $int <= 159 or 224 <= $int && $int <= 239) {
            return true;
        }
        return false;
    }

}
?>