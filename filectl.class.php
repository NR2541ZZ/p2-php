<?php
// FileCtl -- �t�@�C���𑀍삷��N���X

class FileCtl{
	
	/**
	 * �������ݗp�̃t�@�C�����Ȃ���ΐ������ăp�[�~�b�V�����𒲐�����
	 */
	function make_datafile($file, $perm = 0606)
	{
		// �O�̂��߂Ƀf�t�H���g�␳���Ă���
		if (empty($perm)) {
			$perm = 0606;
		}

		if (!file_exists($file)) {
			FileCtl::mkdir_for($file) or die("Error: cannot make parent dirs. ( $file )"); // �e�f�B���N�g����������΍��
			touch($file) or die("Error: cannot touch. ( $file )");
			chmod($file, $perm);
		} else {
			if (!is_writable($file)) {
				$cont = @file_get_contents($file);
				unlink($file);
				touch($file);
				//��������
				$fp = @fopen($file, "wb") or die("Error: cannot write. ( $file )");
				fputs($fp, $cont);
				fclose($fp);
				chmod($file, $perm);
			}		
		}
		return true;
	}

	/**
	 * �e�f�B���N�g�����Ȃ���ΐ������ăp�[�~�b�V�����𒲐�����
	 */
	function mkdir_for($apath)
	{
		global $_conf;
		
		$dir_limit = 50;	// �e�K�w����鐧����
		
		$perm = (!empty($_conf['data_dir_perm'])) ? $_conf['data_dir_perm'] : 0707;

		if (!$parentdir = dirname($apath)) {
			die("Error: cannot mkdir. ( {$parentdir} )<br>�e�f�B���N�g�����󔒂ł��B");
		}
		$i = 1;
		if (!is_dir($parentdir)) {
			if ($i > $dir_limit) {
				die("Error: cannot mkdir. ( {$parentdir} )<br>�K�w���オ��߂����̂ŁA�X�g�b�v���܂����B");
			}
			FileCtl::mkdir_for($parentdir);
			mkdir($parentdir, $perm) or die("Error: cannot mkdir. ( {$parentdir} )");
			chmod($parentdir, $perm);
			$i++;
		}
		return true;
	}
	
	/**
	 * gz�t�@�C���̒��g���擾����
	 */
	function get_gzfile_contents($filepath)
	{
		if(is_readable($filepath)){
			ob_start();
	    	readgzfile($filepath);
	    	$contents = ob_get_contents();
	   		ob_end_clean();
	    	return $contents;
		}else{
			return false;
		}
	}

}
?>
