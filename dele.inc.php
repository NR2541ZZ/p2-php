<?php
/*
	p2 - �X���b�h�f�[�^�ADAT���폜����
*/

require_once 'p2util.class.php';

/**
 * ���w�肵���z��keys�̃��O�iidx (,dat)�j���폜���āA���łɗ���������O��
 *
 * ���[�U�����O���폜���鎞�́A�ʏ킱�̊֐����Ă΂��
 *
 * @public
 * @param array $keys �폜�Ώۂ�key���i�[�����z��
 * @return int ���s�������0, �폜�ł�����1, �폜�Ώۂ��Ȃ����2��Ԃ��B
 */
function deleteLogs($host, $bbs, $keys)
{		
	// �w��key�̃��O���폜�i�Ώۂ���̎��j
	if (is_string($keys)) {
		offRecent($host, $bbs, $keys);
		offResHist($host, $bbs, $keys);
		$r = deleteThisKey($host, $bbs, $keys);
	
	// �w��key�z��̃��O���폜
	} elseif (is_array($keys)) {
		$rs = array();
		foreach ($keys as $akey) {
			offRecent($host, $bbs, $akey);
			offResHist($host, $bbs, $akey);
			$rs[] = deleteThisKey($host, $bbs, $akey);
		}
		if (array_search(0, $rs) !== false) {
			$r = 0;
		} elseif (array_search(1, $rs) !== false) {
			$r = 1;
		} elseif (array_search(2, $rs) !== false) {
			$r = 2;
		} else {
			$r = 0;
		}
	}
	return $r;
}

/**
 * ���w�肵���L�[�̃X���b�h���O�iidx (,dat)�j���폜����
 *
 * �ʏ�́A���̊֐��𒼐ڌĂяo�����Ƃ͂Ȃ��BdeleteLogs() ����Ăяo�����B
 *
 * @return int ���s�������0, �폜�ł�����1, �폜�Ώۂ��Ȃ����2��Ԃ��B
 */
function deleteThisKey($host, $bbs, $key)
{
	$datdir_host = P2Util::datdirOfHost($host);
	
	$anidx = "$datdir_host/{$bbs}/{$key}.idx";
	$adat = "$datdir_host/{$bbs}/{$key}.dat";
	
	// File�̍폜����
	// idx�i�l�p�ݒ�j
	if (file_exists($anidx)) {
		if (unlink($anidx)) {
			$deleted_flag = true;
		} else {
			$failed_flag = true;
		}
	}
	
	// dat�̍폜����
	if (file_exists($adat)) {
		if (unlink($adat)) {
			$deleted_flag = true;
		} else {
			$failed_flag = true;
		}
	}
	
	// ���s�������
	if (!empty($failed_flag)) {
		return 0;
	// �폜�ł�����
	} elseif (!empty($deleted_flag)) {
		return 1;
	// �폜�Ώۂ��Ȃ����
	} else {
		return 2;
	}
}


/**
 * �w�肵���L�[���ŋߓǂ񂾃X���ɓ����Ă邩�ǂ������`�F�b�N����
 */
function checkRecent($host, $bbs, $key)
{
	global $_conf;

	$lines = @file($_conf['rct_file']); // �ǂݍ���
	if ($lines) { // �����true
		foreach ($lines as $l) {
			$l = rtrim($l);
			$lar = explode('<>', $l);
			if ($lar[1] == $key && $lar[10] == $host && $lar[11] == $bbs) { // ��������
				return true;
			}
		}
	}
	return false;
}

/**
 * �w�肵���L�[���������ݗ����ɓ����Ă邩�ǂ������`�F�b�N����
 */
function checkResHist($host, $bbs, $key)
{
	global $_conf;
	
	$rh_idx = $_conf['pref_dir']."/p2_res_hist.idx";
	$lines = @file($rh_idx); // �ǂݍ���
	if ($lines) {	// �����true
		foreach ($lines as $l) {
			$l = rtrim($l);
			$lar = explode('<>', $l);
			if ($lar[1] == $key && $lar[10] == $host && $lar[11] == $bbs) { // ��������
				return true;
			}
		}
	}
	return false;
}

/**
 * �w�肵���L�[�̗������폜����
 */
function offRecent($host, $bbs, $key)
{
	global $_conf;

	$lines = @file($_conf['rct_file']); // �ǂݍ���
	if ($lines) { // ����΍폜
		foreach ($lines as $line) {
			$line = rtrim($line);
			$lar = explode('<>', $line);
			if ($lar[1] == $key && $lar[10] == $host && $lar[11] == $bbs) { // �폜
				$done = true;
				continue;
			}
			$neolines[] = $line;
		}
	}

	// ��������
	$fp = @fopen($_conf['rct_file'], "wb") or die("Error: cannot write. ({$_conf['rct_file']})");
	if ($neolines) {
		@flock($fp, LOCK_EX);
		foreach($neolines as $l){
			fputs($fp, $l."\n");
		}
		@flock($fp, LOCK_UN);
	}
	fclose($fp);
	
	if ($done) {
		return 1;
	} else {
		return 2;
	}
}

/**
 * �w�肵���L�[�̏������ݗ������폜����
 */
function offResHist($host, $bbs, $key)
{
	global $_conf;
	
	$rh_idx = $_conf['pref_dir']."/p2_res_hist.idx";
	$lines = @file($rh_idx); // �ǂݍ���
	if ($lines) {	// ����΍폜
		foreach($lines as $l){
			$l = rtrim($l);
			$lar = explode('<>', $l);
			if ($lar[1] == $key && $lar[10] == $host && $lar[11] == $bbs) { // �폜
				$done = true;
				continue;
			}
			$neolines[] = $l;
		}
	}

	// ��������
	$fp = @fopen($rh_idx, "wb") or die("Error: cannot write. ({$rh_idx})");
	if ($neolines) {
		@flock($fp, LOCK_EX);
		foreach ($neolines as $l) {
			fputs($fp, $l."\n");
		}
		@flock($fp, LOCK_UN);
	}
	fclose($fp);
	
	if ($done) {
		return 1;
	} else {
		return 2;
	}
}

?>