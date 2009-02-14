<?php
// �Ⴆ�΁A�N�G���[�� b=k �Ȃ� isK() ��true�ƂȂ�̂ŁA�g�ь����\���ɂ����肷��

// {{{ ���̃N���X�ł̂ݗ��p����O���[�o���ϐ��i_UA_*�j
// over PHP5�Ɍ���ł���Ȃ�v���C�x�[�g�ȃN���X�ϐ��ɂ������Ƃ���̂���

// @see getQueryKey()
$GLOBALS['_UA_query_key'] = 'b';

// @see setPCQuery() // b=pc
$GLOBALS['_UA_PC_query'] = 'pc';

// @see setMobileQuery() // b=k
$GLOBALS['_UA_mobile_query'] = 'k';

// @see setIPhoneGroupQuery() // b=i
$GLOBALS['_UA_iphonegroup_query'] = 'i';

$GLOBALS['_UA_force_mode'] = null;

// }}}

// [todo] enableJS() �� enableAjax() ���~��������

/**
 * static���\�b�h�ŗ��p����
 */
class UA
{
    /**
     * �����I�Ƀ��[�h�ipc, k�j���w�肷��
     * �i�N�G���[���Z�b�g����킯�ł͂Ȃ��j
     */
    function setForceMode($v)
    {
        $GLOBALS['_UA_force_mode'] = $v;
    }
    
    /**
     * UA��PC�i�񃂃o�C���j�Ȃ�true��Ԃ�
     * iPhone���܂�ł��邪�A������܂܂Ȃ��Ȃ�\�������邱�Ƃɒ��ӁB
     * ���݁AiPhone��setForceMode()��isMobileByQuery()�������Ă���B�i���͎�߂Łj
     *
     * @static
     * @access  public
     * @return  boolean
     */
    function isPC($ua = null)
    {
        return !UA::isMobile($ua);
    }
    
    /**
     * isMobile() �̃G�C���A�X�ɂȂ��Ă���
     *
     * [plan] �g��isK()�ƁA���o�C��isMobile()�́A�ʂ̂��̂Ƃ��ċ�ʂ��������������ȁB�iisMobile()��isK()���܂ނ��̂Ƃ��āj
     * �g�сF��ʂ��������B�y�[�W�̕\���e�ʂɐ���������B�����̃A�N�Z�X�L�[���g���B
     * ���o�C���F�g�тƓ�������ʂ������߂����A�t���u���E�U�ŁAJavaScript���g����B
     */
    function isK($ua = null)
    {
        return UA::isMobile($ua);
    }
    
    /**
     * UA���g�ѕ\���ΏۂȂ�true��Ԃ�
     * isK()�ƈӖ�����ʂ���\�肪����̂ŁA����܂ł̊Ԃ͎g��Ȃ��ł����i�����_�A�g���Ă��Ȃ��j
     * �iisMobileByQuery()�Ȃǂ͎g���Ă��邪�j
     * isM()�ɂ������C���B
     *
     * @static
     * @access  public
     * @params  string  $ua  UA���w�肷��Ȃ�
     * @return  boolean
     */
    function isMobile($ua = null)
    {
        static $cache_;

        // �����w�肪�����
        if (isset($GLOBALS['_UA_force_mode'])) {
            // �����̓L���b�V�����Ȃ�
            return ($GLOBALS['_UA_force_mode'] == $GLOBALS['_UA_mobile_query']);
        }
        
        // ������UA�����w��Ȃ�A�N�G���[�w����Q��
        if (is_null($ua)) {
            if (UA::getQueryValue()) {
                return UA::isMobileByQuery();
            }
        }
        
        // ������UA�����w��Ȃ�A�L���b�V���L��
        if (is_null($ua) and isset($cache_)) {
            return $cache_;
        }
        
        $isMobile = false;
        if ($nuam = &UA::getNet_UserAgent_Mobile($ua)) {
            if (!$nuam->isNonMobile()) {
                $isMobile = true;
            }
        }
        
        /*
        // NetFront�n�i�܂�PSP�j�����o�C����
        if (!$isMobile) {
            $isMobile = UA::isNetFront($ua);
        }
        
        // Nintendo DS�����o�C����
        if (!$isMobile) {
            $isMobile = UA::isNintendoDS($ua);
        }
        */
        
        // ������UA�����w��Ȃ�A�L���b�V���ۑ�
        if (is_null($ua)) {
            $cache_ = $isMobile;
        }
        
        return $isMobile;
    }
    
    /**
     * �N�G���[��PC���w�肵�Ă���Ȃ�true��Ԃ�
     *
     * @static
     * @access  private
     * @return  boolean
     */
    function isPCByQuery()
    {
        $qv = UA::getQueryValue();
        if (isset($qv) && $qv == UA::getPCQuery()) {
            return true;
        }
        return false;
    }
    
    /**
     * �N�G���[���g�т��w�肵�Ă���Ȃ�true��Ԃ�
     *
     * @static
     * @access  private
     * @return  boolean
     */
    function isMobileByQuery()
    {
        $qv = UA::getQueryValue();
        if (isset($qv) && $qv == UA::getMobileQuery()) {
            return true;
        }
        return false;
    }
    
    /**
     * �N�G���[��IPhoneGroup���w�肵�Ă���Ȃ�true��Ԃ�
     *
     * @static
     * @access  private
     * @return  boolean
     */
    function isIPhoneGroupByQuery()
    {
        $qv = UA::getQueryValue();
        if (isset($qv) && $qv == UA::getIPhoneGroupQuery()) {
            return true;
        }
        return false;
    }
    
    /**
     * �\�����[�h�w��p�̃N�G���[�l���擾����
     *
     * @static
     * @access  public
     * @return  string|null
     */
    function getQueryValue($key = null)
    {
        if (is_null($key)) {
            if (!$key = UA::getQueryKey()) {
                return null;
            }
        }
        
        $r = null;
        if (isset($_REQUEST[$key])) {
            if (preg_match('/^\\w+$/', $_REQUEST[$key])) {
                $r = $_REQUEST[$key];
            }
        }
        return $r;
    }
    
    /**
     * @static
     * @access  public
     * @return  string
     */
    function getQueryKey()
    {
        return $GLOBALS['_UA_query_key'];
    }
    
    /**
     * @static
     * @access  public
     * @param   string  $pc  default is 'pc'
     * @return  void
     */
    function setPCQuery($pc)
    {
        $GLOBALS['_UA_PC_query'] = $pc;
    }
    
    /**
     * @static
     * @access  public
     * @return  string
     */
    function getPCQuery()
    {
        return $GLOBALS['_UA_PC_query'];
    }
    
    /**
     * @static
     * @access  public
     * @param   string  $k  default is 'k'
     * @return  void
     */
    function setMobileQuery($k)
    {
        $GLOBALS['_UA_mobile_query'] = $k;
    }
    
    /**
     * @static
     * @access  public
     * @return  string
     */
    function getMobileQuery()
    {
        return $GLOBALS['_UA_mobile_query'];
    }
    
    /**
     * @static
     * @access  public
     * @param   string  $i  default is 'i'
     * @return  void
     */
    function setIPhoneGroupQuery($i)
    {
        $GLOBALS['_UA_iphonegroup_query'] = $i;
    }
    
    /**
     * @static
     * @access  public
     * @return  string
     */
    function getIPhoneGroupQuery()
    {
        return $GLOBALS['_UA_iphonegroup_query'];
    }
    
    /**
     * Net_UserAgent_Mobile::singleton() �̌��ʂ��擾����B
     * REAR Error �� false �ɕϊ������B
     *
     * @static
     * @access  public
     * @param   string  $ua
     * @return  Net_UserAgent_Mobile|false
     */
    function getNet_UserAgent_Mobile($ua = null)
    {
        static $cache_;
        
        if (is_null($ua) and isset($cache_)) {
            return $cache_;
        }
        
        require_once 'Net/UserAgent/Mobile.php';
        
        if (!is_null($ua)) {
            $nuam = &Net_UserAgent_Mobile::factory($ua);
        } else {
            $nuam = &Net_UserAgent_Mobile::singleton();
        }
        
        if (PEAR::isError($nuam)) {
            trigger_error($nuam->toString, E_USER_WARNING);
            $return = false;
            
        } elseif (!$nuam) {
            $return = false; // null
        
        } else {
            $return = $nuam;
        }
        
        if (is_null($ua)) {
            $cache_ = $return;
        }
        
        return $return;
    }
    
    /**
     * UA��NetFront�i�g�сAPDA�APSP�j�Ȃ�true��Ԃ�
     *
     * @static
     * @access  public
     * @param   string   $ua  UA���w�肷��Ȃ�
     * @return  boolean
     */
    function isNetFront($ua = null)
    {
        if (is_null($ua) and isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        }
        
        if (preg_match('/(NetFront|AVEFront\/|AVE-Front\/)/', $ua)) {
            return true;
        }
        if (UA::isPSP()) {
            return true;
        }
        return false;
    }
    
    /**
     * UA��PSP�Ȃ�true��Ԃ��BNetFront�n�炵���B
     *
     * @static
     * @access  public
     * @param   string   $ua  UA���w�肷��Ȃ�
     * @return  boolean
     */
    function isPSP($ua = null)
    {
        if (is_null($ua) and isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        }
        
        // Mozilla/4.0 (PSP (PlayStation Portable); 2.00) 
        if (preg_match('/PlayStation Portable/', $ua)) {
            return true;
        }
        return false;
    }
    
    /**
     * UA��Nintendo DS�Ȃ�true��Ԃ��B
     *
     * @static
     * @access  public
     * @param   string   $ua  UA���w�肷��Ȃ�
     * @return  boolean
     */
    function isNintendoDS($ua = null)
    {
        if (is_null($ua) and isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        }
        
        // Mozilla/4.0 (compatible; MSIE 6.0; Nitro) Opera 8.5 [ja]
        if (preg_match('/ Nitro/', $ua)) {
            return true;
        }
        return false;
    }
    
    /**
     * 2008/10/25 isIPhoneGroup()�ɉ��������̂Ŕp�~�\��
     */
    function isIPhones($ua = null)
    {
        return UA::isIPhoneGroup($ua);
    }
    
    /**
     * UA��iPhone, iPod touch�Ȃ�true��Ԃ��B
     *
     * @static
     * @access  public
     * @param   string   $ua  UA���w�肷��Ȃ�
     * @return  boolean
     */
    function isIPhoneGroup($ua = null)
    {
        // �����w�肪����΃`�F�b�N
        if (isset($GLOBALS['_UA_force_mode'])) {
            // �ڍs�̕֋X��A���͂���߂Ă���
            // return ($GLOBALS['_UA_force_mode'] == $GLOBALS['_UA_iphonegroup_query']);
            if ($GLOBALS['_UA_force_mode'] == $GLOBALS['_UA_iphonegroup_query']) {
                return true;
            }
        }
        
        // UA�̈��������w��Ȃ�A
        if (is_null($ua)) {
            // �N�G���[�w����Q��
            if (UA::getQueryValue()) {
                //// ����݊���Ab=k�ł�iPhone�Ƃ݂Ȃ����Ƃ������B
                //if (!UA::isMobileByQuery()) {
                    return UA::isIPhoneGroupByQuery();
                //}
            }
            // �N���C�A���g��UA�Ŕ���
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                $ua = $_SERVER['HTTP_USER_AGENT'];
            }
        }

        // iPhone
        // Mozilla/5.0 (iPhone; U; CPU like Mac OS X; en) AppleWebKit/420+ (KHTML, like Gecko) Version/3.0 Mobile/1A543a Safari/419.3

        // iPod touch
        // Mozilla/5.0 (iPod; U; CPU like Mac OS X; ja-jp) AppleWebKit/420.1 (KHTML, like Gecko) Version/3.0 Mobile/3A110a Safari/419.3
        if (preg_match('/(iPhone|iPod)/', $ua)) {
            return true;
        }
        return false;
    }
    
    /**
     * UA��Safari�n�Ȃ� true ��Ԃ�
     *
     * @static
     * @access  public
     * @param   string   $ua  UA���w�肷��Ȃ�
     * @return  boolean
     */
    function isSafariGroup($ua = null)
    {
        if (is_null($ua) and isset($_SERVER['HTTP_USER_AGENT'])) {
            $ua = $_SERVER['HTTP_USER_AGENT'];
        }
        
        return (boolean)preg_match('/Safari|AppleWebKit|Konqueror/', $ua);
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
