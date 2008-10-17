<?php
require_once 'Net/UserAgent/Mobile.php';

$GLOBALS['_SecureSession_version_id'] = 1; // �Z�b�V�����̃o�[�W�����i�S�Ẳғ��r���Z�b�V�����������j��������������UP�����肷��j

/**
 * IR, UA, �A�N�Z�X���Ԃ̃`�F�b�N�𔺂��A���Z�L���A�ȃZ�b�V�����Ǘ��N���X
 * �قƂ�ǎ����œ����̂ł��܂�C�ɂ����A�ʏ�ʂ� $_SESSION �̒l����舵���΂悢�B
 * �������A$_SESSION[$this->sess_array]�i$_SESSION['_secure_session']�j �͗\���ƂȂ��Ă���B
 *
 * ���p��
 * $_session =& new Session(); // ���R���X�g���N�^�̎��_��PHP�W���Z�b�V�������X�^�[�g����
 * // ���Z�L���A�ȃZ�b�V�����`�F�b�N
 * if ($error_msg = $_session->getSecureSessionErrorMsg()) {
 *     die('Error: ' . $error_msg);
 * }
 *
 * $_SESSION�ւ̃A�N�Z�X���I������́Asession_write_close() ���Ă����Ƃ悢���낤�B
 *
 * ���d�v��
 * php.ini �� session.auto_start = 0 (PHP�̃f�t�H���g�̂܂�) �ɂȂ��Ă��邱�ƁB
 * �����Ȃ��ƂقƂ�ǂ̃Z�b�V�����֘A�̃p�����[�^���X�N���v�g���ŕύX�ł��Ȃ��B
 * .htaccess�ŕύX��������Ă���Ȃ�
 *
 * <IfModule mod_php4.c>
 *    php_flag session.auto_start Off
 * </IfModule>
 *
 * �ł�OK�B
 *
 * �܂����āA�N���X���� SecureSession �ɉ����\��
 */
class Session
{
    var $sess_array = '_secure_session';
    var $_expire_minutes = 120;
    
    /**
     * @constructor
     *
     * �R���X�g���N�^�̎��_�ŁAPHP�̕W���Z�b�V�������X�^�[�g����
     */
    function Session($session_name = NULL, $session_id = NULL)
    {
        $this->setCookieHttpOnly();
        
        session_cache_limiter('none'); // �L���b�V������Ȃ�
        
        if ($session_name) { session_name($session_name); }
        if ($session_id)   { session_id($session_id); }
        
        session_start();
        
        $this->outputAddRewirteSID();
        
        /*
        Expires: Thu, 19 Nov 1981 08:52:00 GMT
        Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0
        Pragma: no-cache
        */
    }
    
    /**
     * �Z�b�V�����̗L�����Ԃ�ݒ肷��i���j
     *
     * @access public
     * @see    checkAcTime()
     */
    function setExpireMinutes($minutes)
    {
        $this->_expire_minutes = $minutes;
    }
    
    /**
     * @access public
     * @return boolean
     */
    function regenerateId()
    {
        //$oldID = session_id();

        // �萔SID���ύX�ɒǐ�����悤���BCookie���L���Ȏ��ASID�͋󕶎�""�ƂȂ�B
        if (!session_regenerate_id(true)) {
            return false;
        }
        //$sessionFile = session_save_path() . "/sess_$oldID";
        //file_exists($sessionFile) && unlink($sessionFile);
        
        return $this->outputAddRewirteSID();
    }
    
    /**
     * @access private
     * @return boolean
     */
    function outputAddRewirteSID()
    {
        global $_conf;
        
        $session_name = session_name();
        if (!ini_get('session.use_trans_sid') and !isset($_COOKIE[$session_name]) || !empty($_conf['disable_cookie'])) {
            return output_add_rewrite_var($session_name, session_id());
        }
        return true;
    }
    
    /**
     * ���Z�L���A�ȃZ�b�V�����Ǘ����J�n����
     *
     * @access  public
     * @return  boolean
     */
    function startSecure()
    {
        // �Z�L���A�Z�b�V�����ϐ����܂��o�^����Ă��Ȃ���΁A����������
        if (!$this->isSecureActive()) {
        
            // �Z�b�V�����Œ�U���isession fixation�j�΍�
            // http://tdiary.ishinao.net/20060825.html#p02
            // ���O�C�������シ���� regenerateId() ����̂��悢�B
            $this->regenerateId();
            
            $this->updateSecure();
            
            // �Z�b�V�����ϐ��̓o�^�Ɏ��s���Ă�����A�G���[
            if (!$this->isSecureActive()) {
                trigger_error(__CLASS__ . '->' . __FUNCTION__ . '() �Z�b�V�����ϐ���o�^�ł��܂���ł����B', E_USER_WARNING);
                die('Error: Session');
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * �Z�L���A�Z�b�V�����ϐ���������/�X�V����
     *
     * @access  public
     * @return  void
     */
    function updateSecure()
    {
        $_SESSION[$this->sess_array] = array();
        
        $_SESSION[$this->sess_array]['actime']     = time();
        $_SESSION[$this->sess_array]['ip']         = $_SERVER['REMOTE_ADDR'];
        $_SESSION[$this->sess_array]['ua']         = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        // $_SESSION[$this->sess_array]['referer'] = $_SERVER['HTTP_REFERER'];
        $_SESSION[$this->sess_array]['version']    = $GLOBALS['_SecureSession_version_id'];
    }
    
    /**
     * �Z�L���A�Z�b�V�������ғ���Ԃł����true��Ԃ�
     *
     * @access  private
     * @return  boolean
     */
    function isSecureActive()
    {
        return isset($_SESSION[$this->sess_array]['actime']);
    }
    
    // ���݊��p�igetSessionErrorMsg() �� getSecureSessionErrorMsg() �ɖ��̕ύX���Ă���j
    function getSessionErrorMsg()
    {
        return $this->getSecureSessionErrorMsg();
    }
    
    /**
     * �Z�L���A�Z�b�V�����̑Ó������`�F�b�N���āA�G���[������΃��b�Z�[�W�𓾂�B
     * �Z�L���A�Z�b�V�����ϐ��̍X�V�������ōs����B
     * 
     * @access  public
     * @return  null|string �G���[������΁A�iunSession()���āj�G���[���b�Z�[�W��Ԃ��B�Ȃ���� null ��Ԃ��B
     */
    function getSecureSessionErrorMsg()
    {
        // �Z�L���A�Z�b�V�����J�n
        $this->startSecure();
        
        $error_msg = '';
        
        if (!$this->isSecureActive()) {
            $error_msg = '�Z�b�V�������@�\���Ă��܂���B';

        } else {
        
            if (!$this->checkAcTime()) {
                $error_msg = '�Z�b�V�����̎��Ԑ؂�ł��B�ēx���O�C���������Ă��������B';
            }
        
            if (!$this->checkVersion()) {
                $error_msg = '�Z�b�V�����̃o�[�W����������������܂���B'
                    .'�i����̓V�X�e���̃o�[�W�����A�b�v�ɂ���āA�ꎞ�I�ɋN���邱�Ƃ̂��錻�ۂł��j';
            }
            
            if (!$this->checkIP()) {
                $error_msg = '�Z�b�V������IP������������܂���B';
            }
            
            if (!$this->checkUA()) {
                $error_msg = '�Z�b�V������UA������������܂���B';
            }
        }
        
        // �G���[������΁A�iunSession()���āj�G���[���b�Z�[�W��Ԃ��B
        if ($error_msg) {
            $this->unSession();
            return $error_msg;
        }
        
        // ���Ȃ���΁A�Z�L���A�Z�b�V�����ϐ����X�V����
        $this->updateSecure();
        
        // �N�G���[��SID��t������ꍇ�́A���� session_regenerate_id() ����A�A�Ə����s��
        // �ߋ��A�N�Z�X5���ȑO�𖳌��ɂ���Ƃ����ł����������A
        /*
        $session_name = session_name();
        if (!isset($_COOKIE[$session_name])) {
            $this->regenerateId();
        }
        */
        
        return null;
    }
    
    /**
     * �Z�b�V�����̃A�N�Z�X���Ԃ��`�F�b�N����
     *
     * @access  private
     * @return  boolean
     */
    function checkAcTime()
    {
        // �ŏI�A�N�Z�X���Ԃ���A��莞�Ԉȏオ�o�߂��Ă����Expire
        if ($_SESSION[$this->sess_array]['actime'] + $this->_expire_minutes * 60 < time()) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * �Z�b�V�����̃o�[�W�������`�F�b�N����
     *
     * @access  private
     * @return  boolean
     */
    function checkVersion()
    {
        if ($_SESSION[$this->sess_array]['version'] == $GLOBALS['_SecureSession_version_id']) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * IP�A�h���X�Ó����`�F�b�N����
     *
     * @access  private
     * @return  boolean
     */
    function checkIP()
    {
        $check_level = 1; // 0�`4 IP�����낱��ς��DoCoMo���l������ƁA1�܂�
        
        $ses_ips = explode('.', $_SESSION[$this->sess_array]['ip']);
        $now_ips = explode('.', $_SERVER['REMOTE_ADDR']);
    
        for ($i = 0; $i++; $i < $check_level) {
            if ($ses_ips[$i] != $now_ips[$i]) {
                return false;
            }
        }
        return true;
    }

    /**
     * UA�ŃZ�b�V�����̑Ó������`�F�b�N����
     *
     * @access  private
     * @return  boolean
     */
    function checkUA()
    {
        // ibisBrowser 219.117.203.9
        // Mozilla/4.0 (compatible; ibisBrowser; ip210.153.84.0; ser0123456789ABCDE) 
        // http://qb5.2ch.net/test/read.cgi/operate/1141521195/748
        if ($_SERVER['REMOTE_ADDR'] == '219.117.203.9') {
            return true;
        }
        
        // {{{ DoCoMo��UTN����UA�㕔���ς��̂ŋ@�햼�Ō��؂���
        
        $mobile = &Net_UserAgent_Mobile::singleton();
        if ($mobile->isDoCoMo()) {
            $mobile_b = &Net_UserAgent_Mobile::factory($_SESSION[$this->sess_array]['ua']);
            if ($mobile_b->getModel() == $mobile->getModel()) {
                return true;
            }
        }
        
        // }}}
        
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        // $offset = 12;
        if (empty($offset)) {
            $offset = strlen($ua);
        }
        if (substr($ua, 0, $offset) == substr($_SESSION[$this->sess_array]['ua'], 0, $offset)) {
            return true;
        }
        return false;
    }

    /**
     * $_SESSION�ŃZ�b�V������j������
     *
     * �Z�b�V�������Ȃ��A�������͐������Ȃ��ꍇ�Ȃǂ�
     * http://jp.php.net/manual/ja/function.session-destroy.php
     *
     * @static
     * @access  public
     * @return  void
     */
    function unSession()
    {
        global $_conf;
        
        // �Z�b�V�����̏�����
        // session_name("something")���g�p���Ă���ꍇ�͓��ɂ����Y��Ȃ��悤��!
        session_start();

        // �Z�b�V�����ϐ���S�ĉ�������
        $_SESSION = array();
        
        // �Z�b�V������ؒf����ɂ̓Z�b�V�����N�b�L�[���폜����B
        $session_name = session_name();
        if (isset($_COOKIE[$session_name])) {
           unset($_COOKIE[$session_name]);
           setcookie($session_name, '', time() - 42000);
        }
        
        // �ŏI�I�ɁA�Z�b�V������j�󂷂�
        if (isset($_conf['session_dir'])) {
            $session_file = $_conf['session_dir'] . '/sess_' . session_id();
            
        } else {
            $session_file = session_save_path() . '/sess_' . session_id();
        }
        
        session_destroy();
        file_exists($session_file) and unlink($session_file);
    }

    /**
     * �Z�b�V������setcookie��HttpOnly���w�肷��
     * http://msdn2.microsoft.com/ja-jp/library/system.web.httpcookie.httponly(VS.80).aspx
     *
     * @access  private
     * @return  void
     */
    function setCookieHttpOnly()
    {
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : null;
        
        // Mac IE�́A����s�ǂ��N�����炵�����ۂ��̂őΏۂ���O���B�i���������Ή������Ă��Ȃ��j
        // Mozilla/4.0 (compatible; MSIE 5.16; Mac_PowerPC)
        if (preg_match('/MSIE \d\\.\d+; Mac/', $ua)) {
            return;
        }
        
        ini_set('session.cookie_httponly', true);
    }
}
