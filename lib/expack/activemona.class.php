<?php
/**
 * rep2expack - �A�N�e�B�u���i�[�E�N���X
 */
class ActiveMona
{
    // ���i�[�t�H���g�\���X�C�b�`
    var $mona = '<img src="img/aa.png" width="19" height="12" alt="" class="aMonaSW" onclick="activeMona(\'%s\')">';
    //var $mona = '<img src="img/mona.png" width="39" height="12" alt="�i�L�́M�j class="aMonaSW" onclick="activeMona(\'%s\')"">';

    // ���K�\��
    var $re;

    // AA �ɂ悭�g����p�f�B���O
    var $regexA = '�@{4}|(?: �@){2}';

    // �r��
    // [\u2500-\u257F]
    //var $regexB = '[\\x{849F}-\\x{84BE}]{5}';
    var $regexB = '[��-��]{5}';

    // Latin-1,�S�p�X�y�[�X�Ƌ�Ǔ_,�Ђ炪��,�J�^�J�i,���p�E�S�p�` �ȊO�̓���������3�A������p�^�[��
    // Unicode �� [^\x00-\x7F\x{2010}-\x{203B}\x{3000}-\x{3002}\x{3040}-\x{309F}\x{30A0}-\x{30FF}\x{FF00}-\x{FFEF}]
    // ���x�[�X�� SJIS �ɍ�蒼���Ă��邪�A�኱�̈Ⴂ������B
    var $regexC = '([^\\x00-\\x7F\\xA1-\\xDF�@�A�B�C�D�F�G�O-���[�`�E�c���I�H�����������{�^��])\\1\\1';

    /**
     * �R���X�g���N�^�iPHP4�j
     */
    function ActiveMona()
    {
        $this->__construct();
    }

    /**
     * �R���X�g���N�^�iPHP5�j
     */
    function __construct()
    {
        $this->re = '(?:' . $this->regexA . '|' . $this->regexB . '|' . $this->regexC . ')';
    }

    /**
     * �V���O���g��
     */
    function &singleton()
    {
        static $aMona = null;
        if (is_null($aMona)) {
            $aMona = new ActiveMona($config);
        }
        return $aMona;
    }

    /**
     * ���i�[�t�H���g�\���X�C�b�`�𐶐�
     */
    function getMona($id)
    {
        return sprintf($this->mona, $id);
    }

    /**
     * AA����
     */
    function detectAA($msg)
    {
        if (mb_ereg($this->re, $msg)) {
            return true;
        }
        return false;
    }

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
