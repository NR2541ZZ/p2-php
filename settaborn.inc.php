<?php
// p2 - �X���b�h���ځ[��̊֐�

require_once './p2util.class.php';
require_once './filectl.class.php';

/**
 * �X���b�h���ځ[����I���I�t����
 *
 * $set �́A0(����), 1(�ǉ�), 2(�g�O��)
 */
function settaborn($host, $bbs, $key, $set)
{
	global $_conf, $title_msg, $info_msg;
	
	//==================================================================
	// key.idx �ǂݍ���
	//==================================================================
	
	// idxfile�̃p�X�����߂�
	$datdir_host = P2Util::datdirOfHost($host);
	$idxfile = "{$datdir_host}/{$bbs}/{$key}.idx";
	
	// �f�[�^������Ȃ�ǂݍ���
	if (is_readable($idxfile)) {
		$lines = @file($idxfile);
		$l = rtrim($lines[0]);
		$data = explode('<>', $l);
	}
	
	//==================================================================
	// p2_threads_aborn.idx�ɏ�������
	//==================================================================
	
	// p2_threads_aborn.idx �̃p�X�擾
	$datdir_host = P2Util::datdirOfHost($host);
	$taborn_idx = "{$datdir_host}/{$bbs}/p2_threads_aborn.idx";
	
	// p2_threads_aborn.idx ���Ȃ���ΐ���
	FileCtl::make_datafile($taborn_idx, $_conf['p2_perm']);
	
	// p2_threads_aborn.idx �ǂݍ���;
	$taborn_lines= @file($taborn_idx);
	

	if ($taborn_lines) {
		foreach ($taborn_lines as $line) {
			$line = rtrim($line);
			$lar = explode('<>', $line);
			if ($lar[1] == $key) {
				$aborn_attayo = true; // ���ɂ��ځ[�񒆂ł���
				if ($set == 0 or $set == 2) {
					$title_msg_pre = "+ ���ځ[�� �������܂���";
					$info_msg_pre = "+ ���ځ[�� �������܂���";
				}
				continue;
			}
			if (!$lar[1]) { continue; } // key�̂Ȃ����͕̂s���f�[�^
			$neolines[] = $line;
		}
	}
	
	// �V�K�f�[�^�ǉ�
	if ($set == 1 or !$aborn_attayo && $set == 2) {
		$newdata = "$data[0]<>{$key}<><><><><><><><>";
		$neolines ? array_unshift($neolines, $newdata) : $neolines = array($newdata);
		$title_msg_pre = "�� ���ځ[�� ���܂���";
		$info_msg_pre = "�� ���ځ[�� ���܂���";
	}
	
	// ��������
	$fp = @fopen($taborn_idx, "wb") or die("Error: $taborn_idx ���X�V�ł��܂���ł���");
	if ($neolines) {
		@flock($fp, LOCK_EX);
		foreach ($neolines as $l) {
			fputs($fp, $l."\n");
		}
		@flock($fp, LOCK_UN);
	}
	fclose($fp);
	
	$title_msg = $title_msg_pre;
	$info_msg = $info_msg_pre;
	
	return true;
}

?>