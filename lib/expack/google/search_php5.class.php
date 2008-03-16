<?php
/**
 * rep2expack - search 2ch using Google Web APIs
 */

require_once 'PEAR.php';
require_once dirname(__FILE__) . '/search.class.php';

class GoogleSearch_PHP5 extends GoogleSearch_Common
{
    // {{{ constructor

    /**
     * �R���X�g���N�^
     *
     * @return void
     * @access public
     */
    public function __construct()
    {
    }

    // }}}
    // {{{ init()

    /**
     * SOAP�N���C�A���g�̃C���X�^���X�𐶐�����
     *
     * @param string $wsdl  Google Search WSDL�t�@�C���̃p�X
     * @param string $key   Google Web APIs �̃��C�Z���X�L�[
     * @return boolean
     * @access public
     */
    public function init($wsdl, $key)
    {
        if (!file_exists($wsdl)) {
            return PEAR::raiseError('GoogleSearch.wsdl not found.');
        }
        if (!extension_loaded('soap')) {
            return PEAR::raiseError('SOAP extension not loaded.');
        }

        $this->setConf($wsdl, $key);

        try {
            $this->soapClient = &new SoapClient($wsdl, $this->options);
        } catch (SoapFault $e) {
            $errfmt = 'SOAP Fault: (faultcode: %s; faultstring: %s;)';
            $errmsg = sprintf($errfmt, $e->faultcode, $e->faultstring);
            return PEAR::raiseError($errmsg);
        }

        return true;
    }

    // }}}
    // {{{ doSearch()

    /**
     * ���������s����
     *
     * @param string  $q  �����L�[���[�h
     * @param integer $start  �������ʂ��擾����ʒu
     * @param integer $maxResults  �������ʂ��擾����ő吔
     * @return object ��������
     * @access public
     */
    public function doSearch($q, $maxResults = 10, $start = 0)
    {
        $params = $this->prepareParams($q, $maxResults, $start);
        try {
            $result = call_user_func_array(array($this->soapClient, 'doGoogleSearch'), $params);
        } catch (SoapFault $e) {
            $errfmt = 'SOAP Fault: (faultcode: %s; faultstring: %s;)';
            $errmsg = sprintf($errfmt, $e->faultcode, $e->faultstring);
            return PEAR::raiseError($errmsg);
        }
        return $result;
    }

    // }}}
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
