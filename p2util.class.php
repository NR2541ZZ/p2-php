<?php

require_once './filectl_class.inc';

/**
* p2 - p2�p�̃��[�e�B���e�B�N���X
* �C���X�^���X����炸�ɃN���X���\�b�h�ŗ��p����
* 	
* @create  2004/07/15
*/
class P2Util{

	/**
	 * �t�@�C�����_�E�����[�h���ĕۑ�����
	 */
	function fileDownload($url, $localfile, $disp_error = 1)
	{
		global $_conf, $_info_msg_ht, $ext_win_target, $fsockopen_time_limit, $proxy;

		$perm = (isset($_conf['dl_perm'])) ? $_conf['dl_perm'] : 0606;
	
		if (file_exists($localfile)) {
			$modified = gmdate("D, d M Y H:i:s", filemtime($localfile))." GMT";
		} else {
			$modified = false;
		}

		// DL
		include_once("./wap.inc");
		$wap_ua = new UserAgent;
		$wap_ua->setTimeout($fsockopen_time_limit);
		$wap_req = new Request;
		$wap_req->setUrl($url);
		$wap_req->setModified($modified);
		if ($proxy['use']) {
			$wap_req->setProxy($proxy['host'], $proxy['port']);
		}
		$wap_res = $wap_ua->request($wap_req);
	
		if ($wap_res->is_error() && $disp_error) {
			$url_t = P2Util::throughIme($wap_req->url);
			$_info_msg_ht .= "<div>Error: {$wap_res->code} {$wap_res->message}<br>";
			$_info_msg_ht .= "p2 info: <a href=\"{$url_t}\"{$ext_win_target}>{$wap_req->url}</a> �ɐڑ��ł��܂���ł����B</div>";
		}
	
		// �X�V����Ă�����
		if ($wap_res->is_success() && $wap_res->code != "304") {
			$fdat = fopen($localfile, "wb") or die("Error: {$localfile} ���X�V�ł��܂���ł���");
			fwrite($fdat, $wap_res->content);
			fclose($fdat);
			chmod($localfile, $perm);
		}

		return $wap_res;
	}

	/**
	 * subject.txt���_�E�����[�h���ĕۑ�����
	 */
	function subjectDownload($url, $subjectfile)
	{
		global $_conf, $datdir, $_info_msg_ht, $ext_win_target, $fsockopen_time_limit, $proxy;

		$perm = (isset($_conf['dl_perm'])) ? $_conf['dl_perm'] : 0606;
	
		if (file_exists($subjectfile)) {
			if ($_GET['norefresh'] or isset($_GET['submit_kensaku']) || isset($_POST['submit_kensaku'])) {
				return;	// �X�V���Ȃ��ꍇ�́A���̏�Ŕ����Ă��܂�
			} elseif ((!$_POST['newthread']) and P2Util::isSubjectFresh($subjectfile)) {
				return;	// �V�K�X�����Ď��łȂ��A�X�V���V�����ꍇ��������
			}
			$modified = gmdate("D, d M Y H:i:s", filemtime($subjectfile))." GMT";
		} else {
			$modified = false;
		}

		if (extension_loaded('zlib') and strstr($url, ".2ch.net")){
			$headers = "Accept-Encoding: gzip\r\n";
		}

		// ������΂�livedoor�ړ]�ɑΉ��B�Ǎ����livedoor�Ƃ���B
		$url = P2Util::adjustHostJbbs($url);

		//DL
		include_once("./wap.inc");
		$wap_ua = new UserAgent;
		$wap_ua->setAgent("Monazilla/1.00 (".$_conf['p2name']."/".$_conf['p2version'].")");
		$wap_ua->setTimeout($fsockopen_time_limit);
		$wap_req = new Request;
		$wap_req->setUrl($url);
		$wap_req->setModified($modified);
		$wap_req->setHeaders($headers);
		if($proxy['use']){
			$wap_req->setProxy($proxy['host'], $proxy['port']);
		}
		$wap_res = $wap_ua->request($wap_req);
	
		if ($wap_res->is_error()) {
			$url_t = P2Util::throughIme($wap_req->url);
			$_info_msg_ht .= "<div>Error: {$wap_res->code} {$wap_res->message}<br>";
			$_info_msg_ht .= "p2 info: <a href=\"{$url_t}\"{$ext_win_target}>{$wap_req->url}</a> �ɐڑ��ł��܂���ł����B</div>";
		} else {
			$body = $wap_res->content;
		}
	
		// �� DL�������� ���� �X�V����Ă�����
		if ($wap_res->is_success() && $wap_res->code != "304") {
		
			// ������΂Ȃ�EUC��SJIS�ɕϊ�
			if (strstr($subjectfile, $datdir."/jbbs.shitaraba.com") || strstr($subjectfile, $datdir."/jbbs.livedoor.com") || strstr($subjectfile, $datdir."/jbbs.livedoor.jp")) {
				include_once("./strctl_class.inc");
				$body = StrCtl::p2EUCtoSJIS($body);
			}
		
			$fp = @fopen($subjectfile,"wb") or die("Error: {$subjectfile} ���X�V�ł��܂���ł���");
			fwrite($fp, $body);
			fclose($fp);
			chmod($subjectfile, $perm);
		}
	
		return $wap_res;
	}

	/**
	 * �� subject.txt ���V�N�Ȃ� true ��Ԃ�
	 */
	function isSubjectFresh($subjectfile)
	{
		global $_conf;
		if (file_exists($subjectfile)) {	// �L���b�V��������ꍇ
			// �L���b�V���̍X�V���w�莞�Ԉȓ��Ȃ�
			if (@filemtime($subjectfile) > time() - $_conf['sb_dl_interval']) {
				return true;
			}
		}
		return false;
	}

	/**
	 * �Q�[�g��ʂ����߂�URL�ϊ�
	 */
	function throughIme($url)
	{
		global $_conf, $p2ime_url;
	
		if ($_conf['through_ime'] == "2ch") {
			$purl = parse_url($url);
			$url_r = $purl['scheme'] . "://ime.nu/" . $purl['host'] . $purl['path'];
		} elseif ($_conf['through_ime'] == "p2" || $_conf['through_ime'] == "p2pm") {
			$url_r = $p2ime_url . "?url=" . $url;
		} elseif ($_conf['through_ime'] == "p2m") {
			$url_r = $p2ime_url . "?m=1&amp;url=" . $url;
		} else {
			$url_r = $url;
		}
		return $url_r;
	}

	/**
	 * �� host �� 2ch or bbspink �Ȃ� true ��Ԃ�
	 */
	function isHost2chs($host)
	{
		if (preg_match("/\.(2ch\.net|bbspink\.com)/", $host)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * �� host �� be.2ch.net �Ȃ� true ��Ԃ�
	 */
	function isHostBe2chNet($host)
	{
		if (preg_match("/^be\.2ch\.net/", $host)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * �� host �� bbspink �Ȃ� true ��Ԃ�
	 */
	function isHostBbsPink($host)
	{
		if (preg_match("/\.bbspink\.com/", $host)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * �� host �� machibbs �Ȃ� true ��Ԃ�
	 */
	function isHostMachiBbs($host)
	{
		if (preg_match("/\.(machibbs\.com|machi\.to)/", $host)) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * �� host �� machibbs.net �܂��r�˂��� �Ȃ� true ��Ԃ�
	 */
	function isHostMachiBbsNet($host)
	{
		if (preg_match("/\.(machibbs\.net)/", $host)) {
			return true;
		} else {
			return false;
		}
	}
		
	/**
	 * �� host �� JBBS@������� �Ȃ� true ��Ԃ�
	 */
	function isHostJbbsShitaraba($in_host)
	{
		if (preg_match("/jbbs\.shitaraba\.com|jbbs\.livedoor\.com|jbbs\.livedoor\.jp/", $in_host)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * ��JBBS@������΂̃z�X�g���ύX�ɑΉ����ĕύX����
	 *
	 * @param	string	$in_str	�z�X�g���ł�URL�ł��Ȃ�ł��ǂ�
	 */
	function adjustHostJbbs($in_str)
	{
		if (preg_match("/jbbs\.shitaraba\.com|jbbs\.livedoor\.com/", $in_str)) {
			$str = preg_replace("/jbbs\.shitaraba\.com|jbbs\.livedoor\.com/", "jbbs.livedoor.jp", $in_str, 1);
		} else {
			$str = $in_str;
		}
		return $str;
	}


	/**
	 * ���f�[�^php�`���̃t�@�C����ǂݍ���
	 */
	function fileDataPhp($data_php)
	{
		if (!$lines = @file($data_php)) {
			return $lines;
		
		} else {
			// �ŏ��̍s��php�̊J�n�s�Ȃ̂Ŕ�΂�
			@array_shift($lines);
			// �Ō�̍s��php�̕����^�O�Ȃ̂ŃJ�b�g����
			@array_pop($lines);
			return $lines;
		}
	}

	/**
	 * ���f�[�^php�`���̃f�[�^���G�X�P�[�v����
	 */
	function escapeDataPhp($str)
	{
		// &<>/ �� &xxx; �̃G�X�P�[�v������
		$str = str_replace("&", "&amp;", $str);	
		$str = str_replace("<", "&lt;", $str);
		$str = str_replace(">", "&gt;", $str);
		$str = str_replace("/", "&frasl;", $str);
		return $str;
	}

	/**
	 * ���f�[�^php�`���̃f�[�^���A���G�X�P�[�v����
	 */
	function unescapeDataPhp($str)
	{
		// &<>/ �� &xxx; �̃G�X�P�[�v�����ɖ߂�
		$str = str_replace("&lt;", "<", $str);
		$str = str_replace("&gt;", ">", $str);
		$str = str_replace("&frasl;", "/", $str);
		$str = str_replace("&amp;", "&", $str);	
		return $str;
	}
	
	/**
	 * �����`���̏������ݗ�����V�`���ɕϊ�����
	 */
	function transResHistLog()
	{
		global $prefdir, $res_write_rec, $res_write_perm;

		$rh_dat_php = $prefdir."/p2_res_hist.dat.php";
		$rh_dat = $prefdir."/p2_res_hist.dat";

		// �������ݗ������L�^���Ȃ��ݒ�̏ꍇ�͉������Ȃ�
		if ($res_write_rec == 0) {
			return true;
		}

		// p2_res_hist.dat.php�i�V�j ���Ȃ��āAp2_res_hist.dat�i���j ���ǂݍ��݉\�ł�������		
		if ((!file_exists($rh_dat_php)) and is_readable($rh_dat)) {
			// �ǂݍ����
			if($cont = FileCtl::get_file_contents($rh_dat)) {
				// <>��؂肩��^�u��؂�ɕύX����
				// �܂��^�u��S�ĊO����
				$cont = str_replace("\t", "", $cont);
				// <>���^�u�ɕϊ�����
				$cont = str_replace("<>", "\t", $cont);
				
				// &<>/ �� &xxx; �ɃG�X�P�[�v����
				$cont = P2Util::escapeDataPhp($cont);
				
				// �擪���Ɩ�����ǉ�
				$cont = "<?php /*\n".$cont."*/ ?>\n";
				
				// p2_res_hist.dat.php �Ƃ��ĕۑ�
				FileCtl::make_datafile($rh_dat_php, $res_write_perm);
				// ��������
				$fp = @fopen($rh_dat_php, "wb") or die("Error: {$rh_dat_php} ���X�V�ł��܂���ł���");
				flock($fp, LOCK_EX);
				fputs($fp, $cont);
				flock($fp, LOCK_UN);
				fclose($fp);
			}
		}
		return true;
	}

	/**
	 * ���O��̃A�N�Z�X�����擾
	 */
	function getLastAccessLog($logfile)
	{
		// �ǂݍ����
		if (!$lines = P2Util::fileDataPhp($logfile)) {
			return false;
		}
		if (!isset($lines[1])) {
			return false;
		}
		$line = P2Util::unescapeDataPhp($lines[1]);
		$lar = explode("\t", $line);
		
		$alog['user'] = $lar[6];
		$alog['date'] = $lar[0];
		$alog['ip'] = $lar[1];
		$alog['host'] = $lar[2];
		$alog['ua'] = $lar[3];
		$alog['referer'] = $lar[4];
		
		return $alog;
	}
	
	
	/**
	 * ���A�N�Z�X�������O�ɋL�^����
	 */
	function recAccessLog($logfile, $maxline="100")
	{
		global $res_write_perm, $login;
		
		// �ϐ��ݒ�
		$date = date("Y/m/d (D) G:i:s");
	
		// HOST���擾
		if (!$remoto_host = $_SERVER['REMOTE_HOST']) {
			$remoto_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		}
		if ($remoto_host == $_SERVER['REMOTE_ADDR']) {
			$remoto_host = "";
		}

		if (isset($login['user'])) {
			$user = $login['user'];
		} else {
			$user = "";
		}
		
		// �V�������O�s��ݒ�
		$newdata = $date."<>".$_SERVER['REMOTE_ADDR']."<>".$remoto_host."<>".$_SERVER['HTTP_USER_AGENT']."<>".$_SERVER['HTTP_REFERER']."<>".""."<>".$user."\n";
		//$newdata = htmlspecialchars($newdata);


		// �܂��^�u��S�ĊO����
		$newdata = str_replace("\t", "", $newdata);
		// <>���^�u�ɕϊ�����
		$newdata = str_replace("<>", "\t", $newdata);
				
		// &<>/ �� &xxx; �ɃG�X�P�[�v����
		$newdata = P2Util::escapeDataPhp($newdata);

		//���������ݏ���
		FileCtl::make_datafile($logfile, $res_write_perm); // �Ȃ���ΐ���

		// ���O�t�@�C���̒��g���擾����
		if (!$lines = P2Util::fileDataPhp($logfile)) {
			$lines = array();
		} else {
			// �����s����
			while (sizeof($lines) > $maxline -1) {
				array_pop($lines);
			}
		}

		// �V�����f�[�^����ԏ�ɒǉ�
		@array_unshift($lines, $newdata);
		// �擪����ǉ�
		@array_unshift($lines, "<?php /*\n");
		// ������ǉ�
		@array_push($lines, "*/ ?>\n");

		// ��������
		$fp = @fopen($logfile, "wb") or die("Error: {$logfile} ���X�V�ł��܂���ł���");
		flock($fp, LOCK_EX);
		$i = 1;
		foreach ($lines as $l) {
			fputs($fp, $l);
		}
		flock($fp, LOCK_UN);
		fclose($fp);

		return true;
	}

	/**
	 * ���u���E�U��Safari�n�Ȃ�true��Ԃ�
	 */
	function isBrowserSafariGroup()
	{
		if (strstr($_SERVER['HTTP_USER_AGENT'], 'Safari') || strstr($_SERVER['HTTP_USER_AGENT'], 'AppleWebKit') || strstr($_SERVER['HTTP_USER_AGENT'], 'Konqueror')) {
			return true;
		} else {
			return false;
		}
	}
}

?>
