<?php

include_once './conf.inc.php';  // ��{�ݒ�
require_once './filectl.class.php';
require_once './crypt_xor.inc.php';

/**
 * ��2ch�Ƀ��O�C������
 */
function login2ch()
{
	global $_conf, $_info_msg_ht;

	// 2ch��ID, PW�ݒ��ǂݍ���
	if ($array = P2Util::readIdPw2ch()) {
		list($login2chID, $login2chPW, $autoLogin2ch) = $array;

	} else {
		$_info_msg_ht .= "<p>p2 Error: ���O�C���̂��߂�ID�ƃp�X���[�h��o�^���ĉ������B[<a href=\"login2ch.php\" target=\"subject\">2ch���O�C���Ǘ�</a>]</p>";
		return false;
	}

	$auth2ch_url = "https://2chv.tora3.net/futen.cgi";
	$postf = "ID=".$login2chID."&PW=".$login2chPW;
	$x_2ch_ua = "X-2ch-UA: ".$_conf['p2name']."/".$_conf['p2version'];
	$dolib2ch = "DOLIB/1.00";
	$tempfile = $_conf['pref_dir']."/p2temp.php";
	
	// �O�̂��߂��炩����temp�t�@�C�����������Ă���
	if (file_exists($tempfile)) { unlink($tempfile); }

	$curl_msg = "";
	
	// �R�}���hCURL�D��
	if (empty($_conf['precede_phpcurl'])) {
		if (!$r = getAuth2chWithCommandCurl($login2chID, $login2chPW, $tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch)) {
			$curl_msg .= "�usystem��curl�R�}���h�v�Ŏ��s���s�B";
			if (!extension_loaded('curl')) {
					$curl_msg .= "�uPHP��curl�v�͎g���Ȃ��悤�ł�";
			} elseif (!$r = getAuth2chWithPhpCurl($tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch, $postf)) {
				$curl_msg .= "�uPHP��curl�v�Ŏ��s���s�B";
			}
		}
	
	// PHP CURL�D��
	} else {
		if (!extension_loaded('curl')) {
			$curl_msg .= "�uPHP��curl�v�͎g���Ȃ��悤�ł�";
		} elseif (!$r = getAuth2chWithPhpCurl($tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch, $postf)) {
			$curl_msg .= "�uPHP��curl�v�Ŏ��s���s�B";
		}
		
		if (empty($r)) {
			if (!$r = getAuth2chWithCommandCurl($login2chID, $login2chPW, $tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch)) {
				$curl_msg .= "�usystem��curl�R�}���h�v�Ŏ��s���s�B";
			}
		}
	}
	
	/*
	��PHP �� fsockopen �� https �ɑΉ����Ă��Ȃ��̂Ŗ����Ȃ̂�
	$post = array("ID"=>$login2chID, "PW"=>$login2chPW);
	$headers = $x_2ch_ua."\r\n";
	echo $r = getHttpContents($auth2ch_url, "", "POST", $headers, $post, $dolib2ch);
	*/
	
	// �ڑ����s�Ȃ��
	if (empty($r)) {
		if (file_exists($_conf['idpw2ch_php'])) { unlink($_conf['idpw2ch_php']); }
		if (file_exists($_conf['sid2ch_php'])) { unlink($_conf['sid2ch_php']); }
		
		$_info_msg_ht .= "<p>p2 info: 2�����˂�ւ́�ID���O�C�����s���ɂ́Asystem��curl�R�}���h���g�p�\�ł��邩�APHP��<a href=\"http://www.php.net/manual/ja/ref.curl.php\">CURL�֐�</a>���L���ł���K�v������܂��B</p>";

		$_info_msg_ht .= "<p>p2 Error: 2ch���O�C�������Ɏ��s���܂����B{$curl_msg}</p>";
		return false;
	}
	
	
	
	// temp�t�@�C���͂����Ɏ̂Ă�
	if (file_exists($tempfile)) { unlink($tempfile); }
	
	$r = rtrim($r);
	
	// ����
	if (preg_match('/SESSION-ID=(.+?):(.+)/', $r, $matches)) {
		$uaMona = $matches[1];
		$SID2ch = $matches[1].':'.$matches[2];
	} else {
		if (file_exists($_conf['sid2ch_php'])) { unlink($_conf['sid2ch_php']); }
		$_info_msg_ht .= "<p>p2 Error: ���O�C���ڑ��Ɏ��s���܂����B</p>";
		return false;
	}
	
	// �F�؏ƍ����s�Ȃ�
	if ($uaMona == "ERROR") {
		if (file_exists($_conf['idpw2ch_php'])) { unlink($_conf['idpw2ch_php']); }
		if (file_exists($_conf['sid2ch_php'])) { unlink($_conf['sid2ch_php']); }
		$_info_msg_ht .= "<p>p2 Error: SESSION-ID�̎擾�Ɏ��s���܂����BID�ƃp�X���[�h���m�F�̏�A���O�C���������ĉ������B</p>";
		return false;
	}

	//echo $r;//
	
	// SID�̋L�^�ێ� =======================
	$cont = <<<EOP
<?php
\$uaMona='{$uaMona}';
\$SID2ch='{$SID2ch}';
?>
EOP;
	FileCtl::make_datafile($_conf['sid2ch_php'], $_conf['pass_perm']); // $_conf['sid2ch_php'] ���Ȃ���ΐ���
	$fp = @fopen($_conf['sid2ch_php'], "wb");
	if (!$fp) {
		$_info_msg_ht .= "<p>p2 Error: {$_conf['sid2ch_php']} ��ۑ��ł��܂���ł����B���O�C���o�^���s�B</p>";
		return false;
	}
	@flock($fp, LOCK_EX);
	fwrite($fp, $cont);
	@flock($fp, LOCK_UN);
	fclose($fp);

	return $SID2ch;
}


/**
 * ��system�R�}���h��curl�����s���āA2ch���O�C����SID�𓾂�
 */
function getAuth2chWithCommandCurl($login2chID, $login2chPW, $tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch)
{
	global $_conf;

	$curlrtn = 1;
	
	// proxy�̐ݒ�
	if ($_conf['proxy_use']) {
		$with_proxy = " -x ".$_conf['proxy_host'].":".$_conf['proxy_port'];
	} else {
		$with_proxy = "";
	}
	
	// ���usystem�R�}���h��curl�v�i�ؖ������؂���j�����s
	$curlcmd = "curl -H \"{$x_2ch_ua}\" -A {$dolib2ch} -d ID={$login2chID} -d PW={$login2chPW} -o {$tempfile}{$with_proxy} {$auth2ch_url}";
	system($curlcmd, $curlrtn);
	
	// ���usystem�R�}���h��curl�v�i�ؖ������؂���j�Ŗ����������Ȃ�A�i�ؖ������؂Ȃ��j�ōă`�������W
	if ($curlrtn != 0) {
		$curlcmd = "curl -H \"{$x_2ch_ua}\" -A {$dolib2ch} -d ID={$login2chID} -d PW={$login2chPW} -o {$tempfile}{$with_proxy} -k {$auth2ch_url}";
		system($curlcmd, $curlrtn);
	}
	
	if ($curlrtn == 0) {
		if ($r = @file_get_contents($tempfile)) {
			return $r;
		}
	}
	
	return false;
}

/**
 * ��PHP��curl��2ch���O�C����SID�𓾂�
 */
function getAuth2chWithPhpCurl($tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch, $postf)
{
	global $_conf;
	
	// ��PHP��CURL���g����Ȃ�A����Ń`�������W
	if (extension_loaded('curl')) {
		// ���uPHP��curl�v�i�ؖ������؂���j�Ŏ��s
		execAuth2chWithPhpCurl($tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch, $postf, true);
		// ���uPHP��curl�v�i�ؖ������؂���j�Ŗ����Ȃ�A�uPHP��curl�v�i�ؖ������؂Ȃ��j�ōă`�������W
		if (!@file_get_contents($tempfile)) {
			execAuth2chWithPhpCurl($tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch, $postf, false);
		}
		if ($r = @file_get_contents($tempfile)) {
			return $r;
		}
	
	}
	
	return false;
}

/**
 * ��PHP��curl�����s����
 */
function execAuth2chWithPhpCurl($tempfile, $auth2ch_url, $x_2ch_ua, $dolib2ch, $postf, $withk = false)
{
	global $_conf;
	
	$ch = curl_init();
	$fp = fopen($tempfile, 'wb');
	@flock($fp, LOCK_EX);
	curl_setopt($ch, CURLOPT_FILE, $fp);
	curl_setopt($ch, CURLOPT_URL, $auth2ch_url);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array($x_2ch_ua));
	curl_setopt($ch, CURLOPT_USERAGENT, $dolib2ch);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postf);
	// �ؖ����̌��؂����Ȃ��Ȃ�
	if ($withk) {
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	}
	// �v���L�V�̐ݒ�
	if ($_conf['proxy_use']) {
		curl_setopt($ch, CURLOPT_PROXY, $_conf['proxy_host'].':'.$_conf['proxy_port']);
	}
	curl_exec($ch);
	curl_close($ch);
	@flock($fp, LOCK_UN);
	fclose($fp);
	
	return;
}
?>