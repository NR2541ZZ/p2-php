<?php
// p2 - �X���b�h��\������ �N���X

class ShowThread{

	var $thread; // �X���b�h�I�u�W�F�N�g
	
	
	/**
	 * �R���X�g���N�^
	 */
	function ShowThread($aThread)
	{
		$this->thread = $aThread;
	}

	/**
	 * BE�v���t�@�C�������N�ϊ�
	 */
	function replaceBeId($date_id)
	{
		global $_conf;
		
		$beid_replace = "<a href=\"http://be.2ch.net/test/p.php?i=\$1&u=d:http://{$this->thread->host}/{$this->thread->bbs}/{$this->thread->key}/\"{$_conf['ext_win_target']}>Lv.\$2</a>";		
		
		//<BE:23457986:1>
		$be_match = '|<BE:(\d+):(\d+)>|i';
		if (preg_match($be_match, $date_id)) {
			$date_id = preg_replace($be_match, $beid_replace, $date_id);
		
		} else {
		
			$beid_replace = "<a href=\"http://be.2ch.net/test/p.php?i=\$1&u=d:http://{$this->thread->host}/{$this->thread->bbs}/{$this->thread->key}/\"{$_conf['ext_win_target']}>?\$2</a>";
			$date_id = preg_replace('|BE: ?(\d+)-(#*)|i', $beid_replace, $date_id);
		}
		
		return $date_id;
	}

	/**
	 * NG���ځ[��`�F�b�N
	 */
	function ngAbornCheck($code, $resfield)
	{
		global $ngaborns;
		
		if (is_array($ngaborns[$code]['data'])) {
			foreach ($ngaborns[$code]['data'] as $k => $v) {
				if (@strstr($resfield, $ngaborns[$code]['data'][$k]['word'])) {
					$ngaborns[$code]['data'][$k]['lasttime'] = date('Y/m/d G:i');	// HIT���Ԃ��X�V
					$ngaborns[$code]['data'][$k]['hits']++;	// HIT�񐔂��X�V
					return $ngaborns[$code]['data'][$k]['word'];
				}
			}
		}
		return false;
	}

	/**
	 * ���背�X�̓������ځ[��`�F�b�N
	 */
	function abornResCheck($host, $bbs, $key, $resnum)
	{
		global $ngaborns;
		
		$target = $host . '/' . $bbs . '/' . $key . '/' . $resnum;
		
		if ($ngaborns['aborn_res']['data']) {
			foreach ($ngaborns['aborn_res']['data'] as $k => $v) {
				if ($ngaborns['aborn_res']['data'][$k]['word'] == $target) {
					$ngaborns['aborn_res']['data'][$k]['lasttime'] = date('Y/m/d G:i');	// HIT���Ԃ��X�V
					$ngaborns['aborn_res']['data'][$k]['hits']++;	// HIT�񐔂��X�V
					return true;
				}
			}
		}
		return false;
	}

}
?>