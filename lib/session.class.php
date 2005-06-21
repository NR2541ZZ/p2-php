<?php

require_once 'Net/UserAgent/Mobile.php';

$_session_version = 1; // �Z�b�V�����̃o�[�W�����i�S�Ẳғ��r���Z�b�V�����������j��������������UP�����肷��j

/**
 * Session Class
 */
class Session{

    /**
     * �R���X�g���N�^
     */
    function Session($sname = NULL, $sid = NULL)
    {
        // session_cache_limiter('public'); // �L���b�V���L��
        if ($sname) { session_name($sname); } // �Z�b�V�����̖��O���Z�b�g
        if ($sid)   { session_id($sid); }
        session_start(); // �Z�b�V�����J�n
    }

    /**
     * �܂��Z�b�V�������o�^����Ă��Ȃ���΁A�o�^������
     */
    function autoBegin()
    {
        // �Z�b�V�������n�܂��Ă��Ȃ�������A�Z�b�V�����X�^�[�g
        if (!isset($_SESSION['actime'])) {
        
            // �Z�b�V�����ϐ����Z�b�g���ăX�^�[�g
            $this->begin();
        
            // �Z�b�V�����o�^�Ɏ��s������A�N���A����
            if (!isset($_SESSION['actime'])) {
                $_info_msg_ht .= '<p>Error: �Z�b�V������o�^�ł��܂���ł����B</p>';
                return false;
            }
        }    
        return true;
    }
    
    /**
     * �Z�b�V�����n�߂ɕϐ����Z�b�g����
     */
    function begin()
    {
        global $_session_version;
        
        // ������
        $_SESSION = array();
    
        $_SESSION['actime']     = time();
        $_SESSION['ip']         = $_SERVER['REMOTE_ADDR'];
        $_SESSION['ua']         = $_SERVER['HTTP_USER_AGENT'];
        // $_SESSION['referer'] = $_SERVER['HTTP_REFERER'];
        $_SESSION['version']    = $_session_version;
        
        return true;
    }
    
    /**
     * �Z�b�V�����̑Ó������`�F�b�N����
     */
    function checkSession()
    {
        global $_info_msg_ht;
        
        if (!isset($_SESSION['actime'])) {
            $_info_msg_ht .= '<p>Error�F�Z�b�V�������@�\���Ă��܂���B</p>';
            return false;

        } else {
        
            if (!$this->checkAcTime()) {
                $_info_msg_ht .= '<p>Error: �Z�b�V�����̎��Ԑ؂�ł��B�ēx���O�C���������Ă��������B</p>';
                return false;
            }
        
            if (!$this->checkVersion()) {
                $_info_msg_ht .= '<p>Error�F�Z�b�V�����̃o�[�W����������������܂���B'
                    .'�i����̓V�X�e���̃o�[�W�����A�b�v�ɂ���āA�ꎞ�I�ɋN���邱�Ƃ̂��錻�ۂł��j</p>';
                return false;
            }
            
            if (!$this->checkIP()) {
                $_info_msg_ht .= '<p>Error�F�Z�b�V������IP������������܂���B</p>';
                return false;
            }
            
            if (!$this->checkUA()) {
                $_info_msg_ht .= '<p>Error�F�Z�b�V������UA������������܂���B</p>';
                return false;
            }
        }
        
        // ���Ȃ���΁A�A�N�Z�X���Ԃ��X�V����
        $_SESSION['actime'] = time();
        
        // �N�G���[��SID��t������ꍇ�́A���� session_regenerate_id() ����A�A�Ə����s��
        // �ߋ��A�N�Z�X5���ȑO�𖳌��ɂ���Ƃ����ł����������A
        /*
        $sname = session_name();
        if (!$_COOKIE[$sname]) {
            $oldID = session_id();
            session_regenerate_id();
            unlink(session_save_path() . "/sess_$oldID");
        }
        */
        
        return true;
    }
    
    /**
     * �Z�b�V�����̃A�N�Z�X���Ԃ��`�F�b�N����
     */
    function checkAcTime($minutes = 30)
    {
        // �ŏI�A�N�Z�X���Ԃ���A��莞�Ԉȏオ�o�߂��Ă����Expire
        if ($_SESSION['actime'] + $minutes * 60 < time()) {
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * �Z�b�V�����̃o�[�W�������`�F�b�N����
     */
    function checkVersion()
    {
        global $_session_version;
        
        if ($_SESSION['version'] == $_session_version) {
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * IP�A�h���X�Ó����`�F�b�N����
     * @return bool
     */
    function checkIP()
    {
        $check_level = 1; // 0�`4 DoCoMo���l������ƁA1�܂�
        
        $ses_ips = explode('.', $_SESSION['ip']);
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
     */
    function checkUA()
    {
        // DoCoMo��UTN����UA�㕔���ς��̂ŋ@�햼�Ō��؂���
        $mobile = &Net_UserAgent_Mobile::singleton();
        if ($mobile->isDoCoMo()) {
            $mobile_b = &Net_UserAgent_Mobile::factory($_SESSION['ua']);
            if ($mobile_b->getModel() == $mobile->getModel()) {
                return true;
            }
        }
        
        // $offset = 12;
        if (empty($offset)) {
            $offset = strlen($_SERVER['HTTP_USER_AGENT']);
        }
        if (substr($_SERVER['HTTP_USER_AGENT'], 0, $offset) == substr($_SESSION['ua'], 0, $offset)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * $_SESSION�ŃZ�b�V������j������
     *
     * �Z�b�V�������Ȃ��A�������͐������Ȃ��ꍇ�Ȃǂ�
     * http://jp.php.net/manual/ja/function.session-destroy.php
     */
    function unSession()
    {
        // �Z�b�V�����̏�����
        // session_name("something")���g�p���Ă���ꍇ�͓��ɂ����Y��Ȃ��悤��!
        @session_start();

        // �Z�b�V�����ϐ���S�ĉ�������
        $_SESSION = array();
        
        // �Z�b�V������ؒf����ɂ̓Z�b�V�����N�b�L�[���폜����B
        // Note: �Z�b�V������񂾂��łȂ��Z�b�V������j�󂷂�B
        if (isset($_COOKIE[session_name()])) {
           setcookie(session_name(), '', time() - 42000);
        }
        
        // �ŏI�I�ɁA�Z�b�V������j�󂷂�
        session_destroy();
    
        return;
    }

}

?>
