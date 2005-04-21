<?php
// p2 - �X���b�h �N���X

require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X
require_once './filectl.class.php';

/**
 * ���X���b�h�N���X
 */
class Thread{

	var $ttitle; // �X���^�C�g�� // idxline[0] // < �� &lt; �������肷��
	var $key; // �X���b�hID // idxline[1]
	var $length; // local Dat Bytes(int) // idxline[2]
	var $gotnum; //�i�l�ɂƂ��Ắj�������X�� // idxline[3]
	var $rescount; // �X���b�h�̑����X���i���擾�����܂ށj
	var $modified; // dat��Last-Modified // idxline[4]
	var $readnum; // ���ǃ��X��	// idxline[5] // MacMoe�ł̓��X�\���ʒu�������Ǝv���ilast res�j
	var $fav; //���C�ɓ���(bool�I��) // idxline[6] favlist.idx���Q��
	// name // �����ł͗��p���� idxline[7]�i�����ŗ��p�j
	// mail // �����ł͗��p���� idxline[8]�i�����ŗ��p�j
	// var $newline; // ���̐V�K�擾���X�ԍ� // idxline[9] �p�~�\��B���݊��̂��ߎc���Ă͂���B
	
	// ��host�Ƃ͂������̂́A2ch�O�̏ꍇ�́Ahost�ȉ��̃f�B���N�g���܂Ŋ܂܂�Ă����肷��B
	var $host; // ex)pc.2ch.net // idxline[10]
	var $bbs; // ex)mac // idxline[11]
	var $itaj; // �� ex)�V�Emac
	
	var $torder; // �X���b�h�V�������ԍ�
	var $unum; // ���ǁi�V�����X�j��
	
	var $keyidx;	// idx�t�@�C���p�X
	var $keydat;	// ���[�J��dat�t�@�C���p�X
	
	var $isonline; // �T�[�o�ɂ����true�Bsubject.txt��dat�擾���Ɋm�F���ăZ�b�g�����B
	var $new; // �V�K�X���Ȃ�true
	
	var $ttitle_hc;	// < �� &lt; �ł������肷��̂ŁA�f�R�[�h�����X���^�C�g��
	var $ttitle_hd;	// HTML�\���p�ɁA�G���R�[�h���ꂽ�X���^�C�g��
	var $ttitle_ht;	// �X���^�C�g���\���pHTML�R�[�h�B�t�B���^�����O��������Ă�������B
	
	var $dayres; // ���������̃��X���B�����B
	
	var $dat_type; // dat�̌`���i2ch�̋��`��dat�i,��؂�j�Ȃ�"2ch_old"�j
	
	/**
	 * �R���X�g���N�^
	 */
	function Thread()
	{
	}

	/**
	 * ttitle���Z�b�g����i���ł�ttitle_hc, ttitle_hd, ttitle_ht���j
	 */
	function setTtitle($ttitle)
	{
		$this->ttitle = $ttitle;
		// < �� &lt; �ł������肷��̂ŁA�܂��f�R�[�h�������̂�
		$this->ttitle_hc = html_entity_decode($this->ttitle, ENT_COMPAT, 'Shift_JIS');	
		// HTML�\���p�� htmlspecialchars() ��������
		$this->ttitle_hd = htmlspecialchars($this->ttitle_hc);
		$this->ttitle_ht = $this->ttitle_hd;
	}
	
	/**
	 * fav, recent�p�̊g��idx���X�g���烉�C���f�[�^���擾����
	 */

	function getThreadInfoFromExtIdxLine($l)
	{
		$la = explode('<>', rtrim($l));
		$this->host = $la[10];
		$this->bbs = $la[11];
		$this->key = $la[1];
		
		if (!$this->ttitle) {
			if ($la[0]) {
				$this->setTtitle(rtrim($la[0]));
			}
		}
		
		/*
		if ($la[6]) {
			$this->fav = $la[6];
		}
		*/
	}

	/**
	 * �� Set Path info
	 */
	function setThreadPathInfo($host, $bbs, $key)
	{	
		$this->host = $host;
		$this->bbs = $bbs;
		$this->key = $key;
		
		$datdir_host = P2Util::datdirOfHost($this->host);
		$this->keyidx = "{$datdir_host}/{$this->bbs}/{$this->key}.idx";
		$this->keydat = "{$datdir_host}/{$this->bbs}/{$this->key}.dat";
	}

	/**
	 * ���X���b�h�������ς݂Ȃ�true��Ԃ�
	 */
	function isKitoku()
	{
		// if (file_exists($this->keyidx)) {
		if ($this->gotnum || $this->readnum || $this->newline > 1) {
			return true;
		}
		return false;
	}

	/**
	 * �������X���b�h�f�[�^��key.idx����擾����
	 */
	function getThreadInfoFromIdx()
	{
		if (!$lines = @file($this->keyidx)) {
			return false;
		}
		
		$key_line = rtrim($lines[0]);
		$lar = explode('<>', $key_line);
		if (!$this->ttitle) {
			if ($lar[0]) {
				$this->setTtitle(rtrim($lar[0]));
			}
		}
		
		if ($lar[5]) {
			$this->readnum = $lar[5];
		
		// ���݊��[�u�i$lar[9] newline�̔p�~�j
		} elseif ($lar[9]) {
			$this->readnum = $lar[9] -1;
		}
		
		if ($lar[3]) {
			$this->gotnum = $lar[3];
		
			if ($this->rescount) {
				$this->unum = $this->rescount - $this->readnum;
				// machi bbs ��subject�̍X�V�Ƀf�B���C������悤�Ȃ̂Œ������Ă���
				if ($this->unum < 0) {
					$this->unum = 0;
				}
			}
		}

		if ($lar[6]) {
			$this->fav = $lar[6];
		}
		
		/*
		// ����key.idx�̂��̃J�����͎g�p���Ă��Ȃ��Bdat�T�C�Y�͒��ڃt�@�C���̑傫����ǂݎ���Ē��ׂ�
		if ($lar[2]) {
			$this->length = $lar[2];
		}
		*/
		if ($lar[4]) { $this->modified = $lar[4]; }
		
		return $key_line; 
	}
	
	/**
	 * �����[�J��DAT�̃t�@�C���T�C�Y���擾����
	 */
	function getDatBytesFromLocalDat()
	{
		clearstatcache();
		if ($this->length = @filesize($this->keydat)) {
			return $this->length;
		} else {
			return false;
		}
	}
	
	/**
	 * �� subject.txt �̈�s����X�������擾����
	 */
	function getThreadInfoFromSubjectTxtLine($l)
	{
		if (preg_match("/^([0-9]+)\.(dat|cgi)(,|<>)(.+) ?(\(|�i)([0-9]+)(\)|�j)/", $l, $matches)) {
			$this->isonline = true;
			$this->key = $matches[1];
			$this->setTtitle(rtrim($matches[4]));
		
			// be.2ch.net �Ȃ�EUC��SJIS�ϊ�
			if (P2Util::isHostBe2chNet($this->host)) {
				$ttitle = mb_convert_encoding($this->ttitle, 'SJIS-win', 'EUC-JP');
				$this->setTtitle($ttitle);
			}
		
			$this->rescount = $matches[6];
			if ($this->readnum) {
				$this->unum = $this->rescount - $this->readnum;
				// machi bbs ��sage��subject�̍X�V���s���Ȃ������Ȃ̂Œ������Ă���
				if ($this->unum < 0) {
					$this->unum = 0;
				}
			}
			return TRUE;
		}
		return FALSE;
	}

	/**
	 * ���X���^�C�g���擾���\�b�h
	 */
	function setTitleFromLocal()
	{
		if (!isset($this->ttitle)) {
		
			if ($this->datlines) {
				$firstdatline = rtrim($this->datlines[0]);
				$d = $this->explodeDatLine($firstdatline);
				$this->setTtitle($d[4]);
			
			// ���[�J��dat��1�s�ڂ���擾
			} elseif (is_readable($this->keydat)) {
				$fd = fopen($this->keydat, "rb");
				$l = fgets($fd, 32800);
				fclose($fd);
				$firstdatline = rtrim($l);
				if (strstr($firstdatline, "<>")) {
					$datline_sepa = "<>";
				} else {
					$datline_sepa = ",";
					$this->dat_type = "2ch_old";
				}
				$d = explode($datline_sepa, $firstdatline);
				$this->setTtitle($d[4]);
				
				// be.2ch.net �Ȃ�EUC��SJIS�ϊ�
				if (P2Util::isHostBe2chNet($this->host)) {
					$ttitle = mb_convert_encoding($this->ttitle, 'SJIS-win', 'EUC-JP');
					$this->setTtitle($ttitle);
				}
			}
			
		}
		
		return $this->ttitle;
	}

	/**
	 * �����X��URL��Ԃ�
	 */
	function getMotoThread($ls = "")
	{
		global $_conf;

		if (P2Util::isHostMachiBbs($this->host)) {
			$motothre_url = "http://{$this->host}/bbs/read.pl?BBS={$this->bbs}&KEY={$this->key}";
		} elseif (P2Util::isHostMachiBbsNet($this->host)) {
			$motothre_url = "http://{$this->host}/test/read.cgi?bbs={$this->bbs}&key={$this->key}";	
		} elseif (P2Util::isHostJbbsShitaraba($this->host)) {
			$host_bbs_cgi = preg_replace('{(jbbs\.shitaraba\.com|jbbs\.livedoor\.com|jbbs\.livedoor\.jp)}', '$1/bbs/read.cgi', $this->host);
			$motothre_url = "http://{$host_bbs_cgi}/{$this->bbs}/{$this->key}/{$ls}";
			//$motothre_url = "http://{$this->host}/bbs/read.cgi?BBS={$this->bbs}&KEY={$this->key}";
		} elseif (P2Util::isHost2chs($this->host)) {
			if ($_conf['ktai']) {
				if (P2Util::isHostBbsPink($this->host)) {
					$motothre_url = "http://{$this->host}/test/r.i/{$this->bbs}/{$this->key}/{$ls}";
				} else {
					$motothre_url = "http://c.2ch.net/test/-/{$this->bbs}/{$this->key}/{$ls}";
				}
			} else {
				$motothre_url = "http://{$this->host}/test/read.cgi/{$this->bbs}/{$this->key}/{$ls}";
			}
		} else {
			$motothre_url = "http://{$this->host}/test/read.cgi/{$this->bbs}/{$this->key}/{$ls}";
		}
		
		return $motothre_url;
	}
	
	/**
	 * �������i���X/���j���Z�b�g����
	 */
	function setDayRes($nowtime = false)
	{
		if (!isset($this->key) || !isset($this->rescount)) {
			return false;
		}
		
		if (!$nowtime) {
			$nowtime = time();
		}
		if ($pastsc = $nowtime - $this->key) {
			$this->dayres = $this->rescount / $pastsc * 60 * 60 * 24;
			return true;
		}
		return false;
	}


	/**
	 * �����X�Ԋu�i����/���X�j���擾����
	 */
	function getTimePerRes()
	{
		$noresult_st = "-";
	
		if (!isset($this->dayres)) {
			if (!$this->setDayRes(time())) {
				return $noresult_st;
			}
		}
		
		if ($this->dayres <= 0) {
			return $noresult_st;
			
		} elseif ($this->dayres < 1/365) {
			$spd = 1/365 / $this->dayres;
			$spd_suffix = "�N";
		} elseif ($this->dayres < 1/30.5) {
			$spd = 1/30.5 / $this->dayres;
			$spd_suffix = "����";
		} elseif ($this->dayres < 1) {
			$spd = 1 / $this->dayres;
			$spd_suffix = "��";
		} elseif ($this->dayres < 24) {
			$spd = 24 / $this->dayres;
			$spd_suffix = "����";
		} elseif ($this->dayres < 24*60) {
			$spd = 24*60 / $this->dayres;
			$spd_suffix = "��";
		} elseif ($this->dayres < 24*60*60) {
			$spd = 24*60*60 / $this->dayres;
			$spd_suffix = "�b";
		} else {
			$spd = 1;
			$spd_suffix = "�b�ȉ�";
		}
		if ($spd > 0) {
			$spd_st = sprintf("%01.1f", @round($spd, 2)) . $spd_suffix;
		} else {
			$spd_st = "-";
		}
		return $spd_st;
	}

}
?>