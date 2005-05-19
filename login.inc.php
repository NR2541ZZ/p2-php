<?php
// p2 ���O�C��

require_once './filectl.class.php';

//=========================================================
// �֐�
//=========================================================

/**
 * �F�؂̃`�F�b�N���s��
 *
 * @return bool
 */
function authCheck()
{
    global $_conf, $login, $_info_msg_ht;

	// �F�؃��[�U�ݒ�i�t�@�C���j��ǂݍ��݂ł�����
	if (file_exists($_conf['auth_user_file'])) {
		include $_conf['auth_user_file'];

	// �F�ؐݒ肪�Ȃ������ꍇ�͂����܂�
	} else {
		return false;
    }

	// EZweb�F�؃X���[�p�X �T�u�X�N���C�oID
	if ($_SERVER['HTTP_X_UP_SUBNO']) {
		if (file_exists($_conf['auth_ez_file'])) {
			include $_conf['auth_ez_file'];
			if ($_SERVER['HTTP_X_UP_SUBNO'] == $registed_ez) {
				return true;
			}
		}
	}
	
	// J-PHONE�F�؃X���[�p�X // �p�P�b�g�Ή��@ �v���[�UID�ʒmON�̐ݒ� �[���V���A���ԍ�
	// http://www.dp.j-phone.com/dp/tool_dl/web/useragent.php
	if (preg_match('{^(J-PHONE|Vodafone|MOT)/([^/]+?/)+?SN(.+?) }', $_SERVER['HTTP_USER_AGENT'], $matches)) {
		if (file_exists($_conf['auth_jp_file'])) {
			include $_conf['auth_jp_file'];
			if ($matches[3] == $registed_jp) {
				return true;
			}
		}
	}

	// �N�b�L�[�F�؃X���[�p�X
	if (($_COOKIE['p2_user'] == $login['user']) && ($_COOKIE['p2_pass'] == $login['pass'])) {
		return true;
	}

	// Basic�F��
	if (!isset($_SERVER['PHP_AUTH_USER']) || !(($_SERVER['PHP_AUTH_USER'] == $login['user']) && (crypt($_SERVER['PHP_AUTH_PW'], $login['pass']) == $login['pass']))) {
		header('WWW-Authenticate: Basic realm="p2"');
		header('HTTP/1.0 401 Unauthorized');
		echo "Login Failed. ���[�U�F�؂��K�v�ł��B";
		exit;
	} else {
        return true;
    }

    // Basic�F�؂łЂ�������̂ł����܂ł͗��Ȃ�
	return false;
}

/**
 * �g�їp�[��ID�̔F�ؓo�^���Z�b�g����
 */
function registKtaiId()
{
	global $_conf, $_info_msg_ht;

	// {{{ �F�ؓo�^���� EZweb
	if (isset($_REQUEST['regist_ez'])) {
		if ($_SERVER['HTTP_X_UP_SUBNO']) {
			if ($_REQUEST['regist_ez'] == "in") {
				regist_auth("registed_ez", $_SERVER['HTTP_X_UP_SUBNO'], $_conf['auth_ez_file']);
			} elseif ($_REQUEST['regist_ez'] == "out") {
				regist_auth_off($_conf['auth_ez_file']);
			}
		} else {
			$_info_msg_ht .= "<p class=\"infomsg\">�~EZweb�p�ŗLID�̔F�ؓo�^�͂ł��܂���ł���</p>\n";
		}
	// }}}
    
	// {{{ �F�ؓo�^���� J-PHONE
	} elseif (isset($_REQUEST['regist_jp'])) {
		if (preg_match('{^(J-PHONE|Vodafone|MOT)/([^/]+?/)+?SN(.+?) }', $_SERVER['HTTP_USER_AGENT'], $matches)) {
			if ($_REQUEST['regist_jp'] == "in") {
				regist_auth("registed_jp", $matches[3], $_conf['auth_jp_file']);
			} elseif ($_REQUEST['regist_jp'] == "out") {
				regist_auth_off($_conf['auth_jp_file']);
			}
		} else {
			$_info_msg_ht .= "<p class=\"infomsg\">�~J-PHONE�p�ŗLID�̔F�ؓo�^�͂ł��܂���ł���</p>\n";
		}
	}
    // }}}

}

/**
 * cookie�F�ؓo�^���Z�b�g����
 */
function registCookie()
{
	global $login;

	if (!empty($_REQUEST['ctl_regist_cookie'])) {
		if ($_REQUEST['regist_cookie'] == '1') {
			setcookie('p2_user', $login['user'], time()+60*60*24*1000);
			setcookie('p2_pass', $login['pass'], time()+60*60*24*1000); //
		} else {
            // �N�b�L�[���N���A
			setcookie ('p2_user', '', time() - 3600);
			setcookie ('p2_pass', '', time() - 3600);
		}
	}
}

/**
 * �[��ID��F�؃t�@�C���o�^����
 */
function regist_auth($keyw, $sub_id, $auth_file)
{
	global $_info_msg_ht, $_conf, $p2error_st;

	$cont = <<<EOP
<?php
\${$keyw}='{$sub_id}';
?>
EOP;
	FileCtl::make_datafile($auth_file, $_conf['pass_perm']);
	$fp = @fopen($auth_file, 'wb');
	if (!$fp) {
		$_info_msg_ht .= "<p>{$p2error_st}: {$auth_file} ��ۑ��ł��܂���ł����B�F�ؓo�^���s�B</p>";
		return false;
	}
	@flock($fp, LOCK_EX);
	fwrite($fp, $cont);
	@flock($fp, LOCK_UN);
	fclose($fp);
	return true;
}

/**
 * �[��ID�̔F�؃t�@�C���o�^���O��
 */
function regist_auth_off($auth_file)
{
	if (file_exists($auth_file)) {
		unlink($auth_file);
	}
	return;
}

?>
