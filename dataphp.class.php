<?php

/*
	�f�[�^�t�@�C����Web���璼�ڃA�N�Z�X����Ă������݂��Ȃ��悤��php�`���̃t�@�C���Ńf�[�^����舵���N���X
	�t�@�C���̕ۑ��`���́A�ȉ��̂悤�Ȋ����B
	
	���Hphp �^*
	�f�[�^
	*�^ �H��

*/

class DataPhp{

	/**
	 * ���f�[�^php�`���̃t�@�C����ǂݍ���
	 */
	function getDataPhpCont($data_php)
	{
		if (!$cont = @file_get_contents($data_php)) {
			// �ǂݍ��݃G���[�Ȃ�false�A����ۂȂ�""��Ԃ�
			return $cont;
			
		} else {
			// �擪���Ɩ������폜
			$cont = preg_replace("{<\?php /\*\n(.*)\n\*/ \?>.*}s", "$1", $cont);
			// �A���G�X�P�[�v����
			$cont = DataPhp::unescapeDataPhp($cont);

			return $cont;
		}
	}
	
	/**
	 * ���f�[�^php�`���̃t�@�C�������C���œǂݍ���
	 *
	 * ������̃A���G�X�P�[�v���s��
	 */
	function fileDataPhp($data_php)
	{
		if (!$cont = DataPhp::getDataPhpCont($data_php)) {
			// �ǂݍ��݃G���[�Ȃ�false�A����ۂȂ��z���Ԃ�
			if ($cont === false) {
				return false;
			} else {
				return array();
			}
		} else {

			// �s�f�[�^�ɕϊ�
			$lines = array();
			while (strlen($cont) > 0) {
				if (preg_match("{(.*?\n)(.*)}s", $cont, $matches)) {
					$lines[] = $matches[1];
					$cont = $matches[2];
				} else {
					$lines[] = $cont;
					break;
				}
			}
			
			/*
			if ($lines) {
				// �����̋�s�͓��ʂɍ폜����
				$count = count($lines);
				if (rtrim($lines[$count-1]) == "") {
					array_pop($lines);
				}
			}
			*/
			
			//var_dump($lines);
			return $lines;
		}
	}

	/**
	 * �f�[�^php�`���̃t�@�C���Ƀf�[�^���L�^����
	 *
	 * ������̃G�X�P�[�v���s��
	 * @param srting $cont �L�^����f�[�^������B
	 */
	function writeDataPhp($cont, $data_php, $perm = 0606)
	{
		// &<>/ �� &xxx; �ɃG�X�P�[�v����
		$cont = DataPhp::escapeDataPhp($cont);
		
		// �擪���Ɩ�����ǉ�
		$cont = '<?php /*'."\n".$cont."\n".'*/ ?>';
		
		// �t�@�C�����Ȃ���ΐ���
		FileCtl::make_datafile($data_php, $perm);
		// ��������
		$fp = @fopen($data_php, 'wb') or die("Error: {$data_php} ���X�V�ł��܂���ł���");
		@flock($fp, LOCK_EX);
		fputs($fp, $cont);
		@flock($fp, LOCK_UN);
		fclose($fp);
		
		return true;
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
		$str = str_replace('&lt;', '<', $str);
		$str = str_replace('&gt;', '>', $str);
		$str = str_replace('&frasl;', '/', $str);
		$str = str_replace('&amp;', '&', $str);	
		return $str;
	}

}
?>