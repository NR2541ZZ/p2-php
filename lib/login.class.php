<?php
require_once P2_LIB_DIR . '/filectl.class.php';
require_once P2_LIB_DIR . '/session.class.php';

/**
 * p2 - ���O�C���F�؂������N���X
 * 
 * @created  2005/6/14
 */
class Login
{
    var $user;   // ���[�U���i�����I�Ȃ��́j
    var $user_u; // ���[�U���i���[�U�ƒ��ڐG��镔���j
    var $pass_x; // �Í������ꂽ�p�X���[�h

    /**
     * @constructor
     */
    function Login()
    {
        $login_user = $this->setdownLoginUser();
    
        // ���[�U�����w�肳��Ă��Ȃ����
        if (strlen($login_user) == 0) {

            // ���O�C���Ɏ��s������A���O�C����ʂ�\�����ďI������
            require_once P2_LIB_DIR . '/login_first.inc.php';
            printLoginFirst($this);
            exit;
        }

        $this->setUser($login_user);
        $this->pass_x = NULL;
    }

    /**
     * ���[�U�����Z�b�g����
     *
     * @return  void
     * @access  public
     */ 
    function setUser($user)
    {
        $this->user_u = $user;
        $this->user = $user;
    }

    /**
     * ���O�C�����[�U���̎w��𓾂�
     *
     * @access  protected
     * @return  string|false
     */
    function setdownLoginUser()
    {
        $login_user = false;

        // ���[�U������̗D�揇�ʂɉ�����

        // ���O�C���t�H�[������̎w��
        if (!empty($GLOBALS['brazil'])) {
            $add_mail = '.,@-';
        } else {
            $add_mail = '';
        }
        if (isset($_REQUEST['form_login_id']) and preg_match("/^[0-9a-zA-Z_{$add_mail}]+$/", $_REQUEST['form_login_id'])) {
            $login_user = $this->setdownLoginUserWithRequest();

        // GET�����ł̎w��
        } elseif (isset($_REQUEST['user']) and preg_match("/^[0-9a-zA-Z_{$add_mail}]+$/", $_REQUEST['user'])) {
            $login_user = $_REQUEST['user'];

        // Cookie�Ŏw��
        } elseif (isset($_COOKIE['cid']) and ($user = $this->getUserFromCid($_COOKIE['cid'])) !== false) {
            if (preg_match("/^[0-9a-zA-Z_{$add_mail}]+$/", $user)) {
                $login_user = $user;
            }

        // Session�Ŏw��
        } elseif (isset($_SESSION['login_user']) and preg_match("/^[0-9a-zA-Z_{$add_mail}]+$/", $_SESSION['login_user'])) {
            $login_user = $_SESSION['login_user'];
        
        /*
        // Basic�F�؂Ŏw��
        } elseif (isset($_REQUEST['basic']) and !empty($_REQUEST['basic'])) {
        
            if (isset($_SERVER['PHP_AUTH_USER']) && (preg_match("/^[0-9a-zA-Z_{$add_mail}]+$/", $_SERVER['PHP_AUTH_USER']))) {
                $login_user = $_SERVER['PHP_AUTH_USER'];
        
            } else {
                header('WWW-Authenticate: Basic realm="zone"');
                header('HTTP/1.0 401 Unauthorized');
                echo 'Login Failed. ���[�U�F�؂Ɏ��s���܂����B';
                exit;
            }
        */

        }
        
        return $login_user;
    }

    /**
     * REQUEST���烍�O�C�����[�U���̎w��𓾂�
     *
     * @access  private
     * @return  string
     */
    function setdownLoginUserWithRequest()
    {
        return $_REQUEST['form_login_id'];
    }
    
    /**
     * �F�؂��s��
     *
     * @access  public
     * @return  void
     */
    function authorize()
    {
        global $_conf, $_p2session;
        
        // �F�؃`�F�b�N
        if (!$this->authCheck()) {
            // ���O�C�����s
            require_once P2_LIB_DIR . '/login_first.inc.php';
            printLoginFirst($this);
            exit;
        }

        
        // ���O�C��OK�Ȃ�
        
        // {{{ ���O�A�E�g�̎w�肪�����
        
        if (!empty($_REQUEST['logout'])) {
        
            // �Z�b�V�������N���A�i�A�N�e�B�u�A��A�N�e�B�u���킸�j
            Session::unSession();
            
            // �⏕�F�؂��N���A
            $this->clearCookieAuth();
            
            $mobile = &Net_UserAgent_Mobile::singleton();
            
            if (isset($_SERVER['HTTP_X_UP_SUBNO'])) {
                file_exists($_conf['auth_ez_file']) && unlink($_conf['auth_ez_file']);
                
            } elseif ($mobile->isVodafone()) {
                file_exists($_conf['auth_jp_file']) && unlink($_conf['auth_jp_file']);
            
            /* DoCoMo�̓��O�C����ʂ��\�������̂ŁA�⏕�F�؏��������j�����Ȃ�
            } elseif ($mobile->isDoCoMo()) {
                file_exists($_conf['auth_docomo_file']) && unlink($_conf['auth_docomo_file']);
            */
            }
            
            // $user_u_q = empty($_conf['ktai']) ? '' : '?user=' . $this->user_u;

            $url = rtrim(dirname(P2Util::getMyUrl()), '/') . '/'; // . $user_u_q;
            
            header('Location: '.$url);
            exit;
        }
        
        // }}}
        // {{{ �Z�b�V���������p����Ă���Ȃ�A�Z�b�V�����ϐ��̍X�V
        
        if (isset($_p2session)) {
            
            // ���[�U���ƃp�XX���X�V
            $_SESSION['login_user']   = $this->user_u;
            $_SESSION['login_pass_x'] = $this->pass_x;
        }
        
        // }}}
        
        // �v��������΁A�⏕�F�؂�o�^
        $this->registCookie();
        $this->registKtaiId();
        
        // �F�،�̓Z�b�V���������
        session_write_close();
    }

    /**
     * �F�؃��[�U�ݒ�̃t�@�C���𒲂ׂāA�����ȃf�[�^�Ȃ�̂ĂĂ��܂�
     *
     * @access  public
     * @return  void
     */
    function cleanInvalidAuthUserFile()
    {
        global $_conf;
        
        if (@include($_conf['auth_user_file'])) {
            // ���[�U��񂪂Ȃ�������A�t�@�C�����̂ĂĔ�����
            if (empty($rec_login_user_u) || empty($rec_login_pass_x)) {
                unlink($_conf['auth_user_file']);
            }
        }
    }

    /**
     * �F�؂̃`�F�b�N���s��
     *
     * @access  private
     * @return  boolean
     */
    function authCheck()
    {
        global $_conf;
        global $_login_failed_flag;
        global $_p2session;

        $this->cleanInvalidAuthUserFile();
        
        // �F�؃��[�U�ݒ�i�t�@�C���j��ǂݍ��݂ł�����
        if (file_exists($_conf['auth_user_file'])) {
            include $_conf['auth_user_file'];

            // ���[�U�����������A�F�؎��s�Ŕ�����
            if ($this->user_u != $rec_login_user_u) {
                P2Util::pushInfoHtml('<p class="infomsg">p2 error: ���O�C���G���[</p>');
                
                // ���O�C�����s���O���L�^����
                if (!empty($_conf['login_log_rec'])) {
                    $recnum = isset($_conf['login_log_rec_num']) ? intval($_conf['login_log_rec_num']) : 100;
                    P2Util::recAccessLog($_conf['login_failed_log_file'], $recnum);
                }
                
                return false;
            }
            
            // �p�X���[�h�ݒ肪����΁A�Z�b�g����
            if (isset($rec_login_pass_x) && strlen($rec_login_pass_x) > 0) {
                $this->pass_x = $rec_login_pass_x;
            }
        }
        
        // �F�ؐݒ� or �p�X���[�h�L�^���Ȃ������ꍇ�͂����܂�
        if (!$this->pass_x) {

            // �V�K�o�^�łȂ���΃G���[�\��
            if (empty($_POST['submit_new'])) {
                P2Util::pushInfoHtml('<p class="infomsg">p2 error: ���O�C���G���[</p>');
            }
            return false;
        }

        // {{{ �N�b�L�[�F�؃X���[�p�X
        
        if (isset($_COOKIE['cid'])) {
        
            if ($this->checkUserPwWithCid($_COOKIE['cid'])) {
                isset($_p2session) and $_p2session->initSess();
                return true;
                
            // Cookie�F�؂��ʂ�Ȃ����
            } else {
                // �Â��N�b�L�[���N���A���Ă���
                $this->clearCookieAuth();
            }
        }
        
        // }}}
        
        $mobile = &Net_UserAgent_Mobile::singleton();
        if (PEAR::isError($mobile)) {
            trigger_error($mobile->toString(), E_USER_WARNING);
        
        } elseif ($mobile and !$mobile->isNonMobile()) {
        
            // ��EZweb�F�؃X���[�p�X �T�u�X�N���C�oID
            if ($mobile->isEZweb() && isset($_SERVER['HTTP_X_UP_SUBNO']) && file_exists($_conf['auth_ez_file'])) {
                include $_conf['auth_ez_file'];
                if ($_SERVER['HTTP_X_UP_SUBNO'] == $registed_ez) {
                    if (isset($_p2session)) {
                        $_p2session->regenerateId();
                        $_p2session->updateSecure();
                    }
                    return true;
                }
            }
        
            // ��SoftBank(J-PHONE)�F�؃X���[�p�X
            // �p�P�b�g�Ή��@ �v���[�UID�ʒmON�̐ݒ� �[���V���A���ԍ�
            // http://www.dp.j-phone.com/dp/tool_dl/web/useragent.php
            if ($mobile->isVodafone() && ($sn = $mobile->getSerialNumber()) !== NULL) {
                if (file_exists($_conf['auth_jp_file'])) {
                    include $_conf['auth_jp_file'];
                    if ($sn == $registed_jp) {
                        if (isset($_p2session)) {
                            // ������ session_regenerate_id(true) ����Ɛڑ����r�؂ꂽ���Ƀ��O�C����ʂɖ߂����炵���B
                            // �[���F�؂���Ă���Ȃ�A�Z�b�V�����`�F�b�N�܂ōs���Ȃ��͂��Ȃ̂ɕs�v�c�B
                            //$_p2session->regenerateId();
                            $_p2session->updateSecure();
                        }
                        return true;
                    }
                }
            }
        
            // ��DoCoMo UTN�F��
            // ���O�C���t�H�[�����͂���͗��p�����A��p�F�؃����N����̂ݗ��p
            if (empty($_POST['form_login_id'])) {

                if ($mobile->isDoCoMo() && ($sn = $mobile->getSerialNumber()) !== NULL) {
                    if (file_exists($_conf['auth_docomo_file'])) {
                        include $_conf['auth_docomo_file'];
                        if ($sn == $registed_docomo) {
                            if (isset($_p2session)) {
                                // DoCoMo�ŏ������񂾌�ɖ߂����肷��ƍĔF�؂ɂȂ��ĕs��
                                //$_p2session->regenerateId();
                                $_p2session->updateSecure();
                            }
                            return true;
                        }
                    }
                }
            }
        }
        
        // {{{ ���łɃZ�b�V�������o�^����Ă�����A�Z�b�V�����ŔF��
        
        if (isset($_SESSION['login_user']) && isset($_SESSION['login_pass_x'])) {
        
            // �Z�b�V���������p����Ă���Ȃ�A�Z�b�V�����̑Ó����`�F�b�N
            if (isset($_p2session)) {
                if ($msg = $_p2session->getSessionErrorMsg()) {
                    P2Util::pushInfoHtml('<p>p2 error: ' . htmlspecialchars($msg) . '</p>');
                    //$_p2session->unSession();
                    // ���O�C�����s
                    return false;
                }
            }

            if ($this->user_u == $_SESSION['login_user']) {
                if ($_SESSION['login_pass_x'] != $this->pass_x) {
                    $_p2session->unSession();
                    return false;

                } else {
                    return true;
                }
            }
        }
        
        // }}}
        
        // ���t�H�[�����烍�O�C��������
        if (!empty($_POST['submit_member'])) {

            // �t�H�[�����O�C�������Ȃ�
            if ($_POST['form_login_id'] == $this->user_u and sha1($_POST['form_login_pass']) == $this->pass_x) {
                
                // �Â��N�b�L�[���N���A���Ă���
                $this->clearCookieAuth();

                // ���O�C�����O���L�^����
                $this->logLoginSuccess();
                if (isset($_p2session)) {
                    $_p2session->regenerateId();
                    $_p2session->updateSecure();
                }
                return true;
            
            // �t�H�[�����O�C�����s�Ȃ�
            } else {
                P2Util::pushInfoHtml('<p class="infomsg">p2 info: ���O�C���ł��܂���ł����B<br>���[�U�����p�X���[�h���Ⴂ�܂��B</p>');
                $_login_failed_flag = true;
                
                // ���O�C�����s���O���L�^����
                $this->logLoginFailed();
                return false;
            }
        }
    
        /*
        // Basic�F��
        if (!empty($_REQUEST['basic'])) {
            if (isset($_SERVER['PHP_AUTH_USER']) and ($_SERVER['PHP_AUTH_USER'] == $this->user_u) && (sha1($_SERVER['PHP_AUTH_PW']) == $this->pass_x)) {
                
                // �Â��N�b�L�[���N���A���Ă���
                $this->clearCookieAuth();

                // ���O�C�����O���L�^����
                $this->logLoginSuccess();
                
                if (isset($_p2session)) {
                    $_p2session->regenerateId();
                    $_p2session->updateSecure();
                }
                return true;
                
            } else {
                header('WWW-Authenticate: Basic realm="zone"');
                header('HTTP/1.0 401 Unauthorized');
                echo 'Login Failed. ���[�U�F�؂Ɏ��s���܂����B';
                
                // ���O�C�����s���O���L�^����
                $this->logLoginFailed();
                
                exit;
            }
        }
        */
        
        return false;
    }
    
    /**
     * ���O�C�����O���L�^����
     *
     * @access  private
     * @return  boolean|null  ���s����|�������Ȃ��ꍇ
     */
    function logLoginSuccess()
    {
        global $_conf;

        if (!empty($_conf['login_log_rec'])) {
            $recnum = isset($_conf['login_log_rec_num']) ? intval($_conf['login_log_rec_num']) : 100;
            return P2Util::recAccessLog($_conf['login_log_file'], $recnum);
        }
        
        return null;
    }

    /**
     * ���O�C�����s���O���L�^����
     *
     * @access  private
     * @return  boolean|null  ���s����|�������Ȃ��ꍇ
     */
    function logLoginFailed()
    {
        global $_conf;
        
        if (!empty($_conf['login_log_rec'])) {
            $recnum = isset($_conf['login_log_rec_num']) ? intval($_conf['login_log_rec_num']) : 100;
            return P2Util::recAccessLog($_conf['login_failed_log_file'], $recnum, 'txt');
        }
        
        return null;
    }

    /**
     * �g�їp�[��ID�̔F�ؓo�^���Z�b�g����
     *
     * @access  public
     */
    function registKtaiId()
    {
        global $_conf;
        
        $mobile = &Net_UserAgent_Mobile::singleton();
        
        // {{{ �F�ؓo�^���� EZweb
        
        if (!empty($_REQUEST['ctl_regist_ez'])) {
            
            if ($_REQUEST['regist_ez'] == '1') {
                if ($_SERVER['HTTP_X_UP_SUBNO']) {
                    $this->registAuth('registed_ez', $_SERVER['HTTP_X_UP_SUBNO'], $_conf['auth_ez_file']);
                } else {
                    P2Util::pushInfoHtml('<p class="infomsg">�~EZweb�p�T�u�X�N���C�oID�ł̔F�ؓo�^�͂ł��܂���ł���</p>');
                }
            } else {
                $this->registAuthOff($_conf['auth_ez_file']);
            }
    
        // }}}
        // {{{ �F�ؓo�^���� Vodafone
        
        } elseif (!empty($_REQUEST['ctl_regist_jp'])) {
        
            if ($_REQUEST['regist_jp'] == '1') {
                if ($mobile->isVodafone() && ($sn = $mobile->getSerialNumber()) !== NULL) {
                    $this->registAuth('registed_jp', $sn, $_conf['auth_jp_file']);
                } else {
                    P2Util::pushInfoHtml('<p class="infomsg">�~Vodafone�p�ŗLID�ł̔F�ؓo�^�͂ł��܂���ł���</p>');
                }
            } else {
                $this->registAuthOff($_conf['auth_jp_file']);
            }
        
        // }}}
        // {{{ �F�ؓo�^���� DoCoMo
        
        } elseif (!empty($_REQUEST['ctl_regist_docomo'])) {
            if ($_REQUEST['regist_docomo'] == '1') {
                if ($mobile->isDoCoMo() && ($sn = $mobile->getSerialNumber()) !== NULL) {
                    $this->registAuth('registed_docomo', $sn, $_conf['auth_docomo_file']);
                } else {
                    P2Util::pushInfoHtml('<p class="infomsg">�~DoCoMo�p�ŗLID�ł̔F�ؓo�^�͂ł��܂���ł���</p>');
                }
            } else {
                $this->registAuthOff($_conf['auth_docomo_file']);
            }
        }
        
        // }}}
    }

    /**
     * �[��ID��F�؃t�@�C���o�^����
     *
     * @access  private
     * @return  boolean
     */
    function registAuth($key, $sub_id, $auth_file)
    {
        global $_conf;
    
        $cont = <<<EOP
<?php
\${$key}='{$sub_id}';
?>
EOP;
        FileCtl::make_datafile($auth_file, $_conf['pass_perm']);

        if (false === file_put_contents($auth_file, $cont, LOCK_EX)) {
            P2Util::pushInfoHtml("<p>Error: �f�[�^��ۑ��ł��܂���ł����B�F�ؓo�^���s�B</p>");
            return false;
        }
        
        return true;
    }

    /**
     * �[��ID�̔F�؃t�@�C���o�^���O��
     *
     * @access  private
     * @return  boolean
     */
    function registAuthOff($auth_file)
    {
        if (file_exists($auth_file)) {
            return unlink($auth_file);
        }
        return true;
    }

    /**
     * �V�K���[�U���쐬����
     *
     * @access  public
     * @return  boolean
     */
    function makeUser($user_u, $pass)
    {
        global $_conf;
        
        $crypted_login_pass = sha1($pass);
        $auth_user_cont = <<<EOP
<?php
\$rec_login_user_u = '{$user_u}';
\$rec_login_pass_x = '{$crypted_login_pass}';
?>
EOP;
        FileCtl::make_datafile($_conf['auth_user_file'], $_conf['pass_perm']);
        if (false === file_put_contents($_conf['auth_user_file'], $auth_user_cont, LOCK_EX)) {
            P2Util::printSimpleHtml("p2 error: {$_conf['auth_user_file']} ��ۑ��ł��܂���ł����B�F��{$p_str['user']}�o�^���s�B");
            die;
            return false;
        }
        
        return true;
    }

    /**
     * cookie�F�؂�o�^/��������
     *
     * @access  public
     * @return  boolean
     */
    function registCookie()
    {
        $r = true;
        
        if (!empty($_REQUEST['ctl_regist_cookie'])) {
            if ($_REQUEST['regist_cookie'] == '1') {
                $r = $this->setCookieCid($this->user_u, $this->pass_x);
            } else {
                // �N�b�L�[���N���A
                $r = $this->clearCookieAuth();
            }
        }
        return $r;
    }

    /**
     * Cookie�F�؂��N���A����
     *
     * @access  public
     * @return  boolean
     */
     function clearCookieAuth()
     {
        $r = setcookie('cid', '', time() - 3600);
        
        setcookie('p2_user', '', time() - 3600);    //  �p�~�v�f 2005/6/13
        setcookie('p2_pass', '', time() - 3600);    //  �p�~�v�f 2005/6/13
        setcookie('p2_pass_x', '', time() - 3600);  //  �p�~�v�f 2005/6/13
        
        $_COOKIE = array();
        
        return $r;
     }

    /**
     * CID��cookie�ɃZ�b�g����
     *
     * @access  protected
     * @return  boolean
     */
    function setCookieCid($user_u, $pass_x)
    {
        global $_conf;
        
        if ($cid = $this->makeCid($user_u, $pass_x)) {
            $time = time() + 60*60*24 * $_conf['cid_expire_day'];
            return setcookie('cid', $cid, $time);
        }
        return false;
    }
    
    /**
     * ID��PASS�Ǝ��Ԃ�����߂ĈÍ�������Cookie���iCID�j�𐶐��擾����
     *
     * @access  private
     * @return  string|false
     */
    function makeCid($user_u, $pass_x)
    {
        if (is_null($user_u) || is_null($pass_x)) {
            return false;
        }
        
        require_once P2_LIB_DIR . '/md5_crypt.inc.php';
        
        $key = $this->getMd5CryptKey();
        
        $idtime = $user_u . ':' . time(). ':';
        $pw_enc = md5($idtime . $pass_x);
        $str = $idtime . $pw_enc;
        $cid = md5_encrypt($str, $key, 32);
        
        return $cid;
    }

    /**
     * Cookie�iCID�j���烆�[�U���𓾂�
     *
     * @access  private
     * @return  array|false  ��������Δz��A���s�Ȃ� false ��Ԃ�
     */
    function getCidInfo($cid)
    {
        global $_conf;
        
        require_once P2_LIB_DIR . '/md5_crypt.inc.php';
        
        $key = $this->getMd5CryptKey();
        
        $dec = md5_decrypt($cid, $key, 32);
        list($user, $time, $pw_enc) = split(':', $dec, 3);

        // �L������ ����
        if (time() > $time + (86400 * $_conf['cid_expire_day'])) {
            return false; // �����؂�
        } else {
            return array($user, $time, $pw_enc);
        }
    }
    
    /**
     * Cookie���iCID�j����user�𓾂�
     *
     * @access  private
     * @return  string|false
     */
    function getUserFromCid($cid)
    {
        if (!$ar = $this->getCidInfo($cid)) {
            return false;
        }
        
        return $user = $ar[0];
    }
    
    /**
     * Cookie���iCID�j��user, pass���ƍ�����
     *
     * @access  public
     * @return  boolean
     */
    function checkUserPwWithCid($cid)
    {
        global $_conf;
        
        if (is_null($this->user_u) || is_null($this->pass_x) || is_null($cid)) {
            return false;
        }
        
        if (!$ar = $this->getCidInfo($cid)) {
            return false;
        }
        
        $time = $ar[1];
        $pw_enc = $ar[2];
        
        // PW���ƍ�
        if ($pw_enc == md5($this->user_u . ':' . $time . ':' . $this->pass_x)) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * md5_encrypt, md5_decrypt �̂��߂ɃN���v�g�L�[�𓾂�
     *
     * @access  private
     * @return  string
     */
    function getMd5CryptKey()
    {
        //return $_SERVER['SERVER_NAME'] . $_SERVER['HTTP_USER_AGENT'] . $_SERVER['SERVER_SOFTWARE'];
        return $_SERVER['SERVER_NAME'] . $_SERVER['SERVER_SOFTWARE'];
    }

}
