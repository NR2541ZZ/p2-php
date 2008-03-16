<?php
/**
 * rep2expack - search 2ch using Google Web APIs
 */

// {{{ class GoogleSearch

/**
 * Google�����N���X�𐶐�����N���X
 *
 * �t�@�N�g���p�^�[�����g���Ă݂�B
 */
class GoogleSearch
{
    // {{{ factory()

    /**
     * PHP�̃o�[�W�����ɉ�����SOAP�N���C�A���g�@�\�𗘗p����N���X��I������
     *
     * @param string $wsdl  Google Search WSDL�t�@�C���̃p�X
     * @param string $key   Google Key
     * @return object
     * @access public
     */
    function &factory($wsdl, $key)
    {
        global $_conf;
        if (extension_loaded('soap') && empty($_conf['expack.google.force_pear'])) {
            require_once dirname(__FILE__) . '/search_php5.class.php';
            $google = &new GoogleSearch_PHP5();
        } else {
            require_once dirname(__FILE__) . '/search_php4.class.php';
            $google = &new GoogleSearch_PHP4();
        }
        $available = $google->init($wsdl, $key);
        if (PEAR::isError($available)) {
            return $available;
        }
        return $google;
    }

    // }}}
}

// }}}
// {{{ class GoogleSearch_Common

/**
 * Google Web APIs �𗘗p���Č�������N���X
 *
 * SOAP�̎g������PHP4��PHP5�őS���قȂ�̂ŁA
 * ���̃N���X���p�����Ă��ꂼ��ɑΉ������N���X�����B
 */
class GoogleSearch_Common
{
    // {{{ properties

    /**
     * Google Search WSDL�t�@�C���̃p�X
     *
     * @var string
     * @access protected
     */
    var $wsdl;

    /**
     * Google Web APIs �̃��C�Z���X�L�[
     *
     * @var string
     * @access protected
     */
    var $key;

    /**
     * SOAP�̃��\�b�h���ĂԂƂ��̃I�v�V����
     *
     * @var array
     * @access protected
     *
     * @link http://jp.php.net/manual/ja/function.soap-soapclient-call.php
     * @see PEAR's SOAP/Client.php SOAP_Client::call()
     */
    var $options;

    /**
     * ���ۂ�Google��������N���X�̃C���X�^���X
     *
     * @var object
     * @access protected
     */
    var $soapClient;

    // }}}
    // {{{ constructor

    /**
     * �R���X�g���N�^
     *
     * @return void
     * @access public
     */
    function GoogleSearch()
    {
    }

    // }}}
    // {{{ setConf

    /**
     * �ݒ�̏�����
     *
     * @param string $wsdl  Google Search WSDL�t�@�C���̃p�X
     * @param string $key   Google Web APIs �̃��C�Z���X�L�[
     * @return void
     * @access public
     */
    function setConf($wsdl, $key)
    {
        $this->wsdl = $wsdl;
        $this->key  = $key;
        $this->options = array('namespace' => 'urn:GoogleSearch', 'trace' => 0);
    }

    // }}}
    // {{{ prepareParams()

    /**
     * Google�ɑ��M����l����������
     *
     * @param string  $q  �����L�[���[�h
     * @param integer $start  �������ʂ��擾����ʒu
     * @param integer $maxResults  �������ʂ��擾����ő吔
     * @return array
     * @access public
     */
    function prepareParams($q, $maxResults = 10, $start = 0)
    {
        //$q = mb_encode_numericentity($q, array(0x80, 0xFFFF, 0, 0xFFFF), 'UTF-8');
        // �����p�����[�^
        // <!-- note, ie and oe are ignored by server; all traffic is UTF-8. -->
        // <message name="doGoogleSearch">
        return array(
            'key'   => $this->key,  // <part name="key"        type="xsd:string"/>
            'q'     => $q,          // <part name="q"          type="xsd:string"/>
            'start' => $start,      // <part name="start"      type="xsd:int"/>
            'maxResults' => $maxResults, // <part name="maxResults" type="xsd:int"/>
            'filter'    => false,   // <part name="filter"     type="xsd:boolean"/>
            'restrict' => '',       // <part name="restrict"   type="xsd:string"/>
            'safeSearch' => false,  // <part name="safeSearch" type="xsd:boolean"/>
            'lr' => '',             // <part name="lr"         type="xsd:string"/>
            'ie' => 'utf-8',        // <part name="ie"         type="xsd:string"/>
            'oe' => 'utf-8'         // <part name="oe"         type="xsd:string"/>
        );
        // </message>
    }

    // }}}
    // {{{ init()

    /**
     * SOAP�N���C�A���g�̃C���X�^���X�𐶐�����
     *
     * ���̃N���X�ł̓C���^�[�t�F�[�X�̒񋟂̂�
     *
     * @param string $wsdl  Google Search WSDL�t�@�C���̃p�X
     * @param string $key   Google Web APIs �̃��C�Z���X�L�[
     * @return boolean
     * @access public
     */
    function init($wsdl, $key)
    {
        return PEAR::raiseError('class GoogleSearch_Common must be inherited.');
    }

    // }}}
    // {{{ doSearch()

    /**
     * ���������s����
     *
     * ���̃N���X�ł̓C���^�[�t�F�[�X�̒񋟂̂�
     *
     * @param string  $q  �����L�[���[�h
     * @param integer $start  �������ʂ��擾����ʒu
     * @param integer $maxResults  �������ʂ��擾����ő吔
     * @return object
     * @access public
     */
    function doSearch($q, $maxResults, $start)
    {
        return PEAR::raiseError('class GoogleSearch_Common must be inherited.');
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
