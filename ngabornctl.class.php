<?php
/*
	p2 - NG���ځ[��𑀍삷��N���X
*/
class NgAbornCtl{

	/**
	 * ���ځ[��&NG���[�h�ݒ��ۑ�����
	 */
	function saveNgAborns()
	{
		global $ngaborns, $ngaborns_hits;
		global $_conf;

		// HIT�������̂ݍX�V����
		if ($GLOBALS['ngaborns_hits']) {
			foreach ($ngaborns_hits as $code => $v) {
		
				if ($ngaborns[$code]['data']) {
				
					// �X�V���ԂŃ\�[�g����
					usort($ngaborns[$code]['data'], array('NgAbornCtl', 'cmpLastTime'));
				
					$cont = "";
					foreach ($ngaborns[$code]['data'] as $a_ngaborn) {
					
						// �K�v�Ȃ炱���ŌÂ��f�[�^�̓X�L�b�v�i�폜�j����
						if (!empty($a_ngaborn['lasttime']) && $_conf['ngaborn_daylimit']) {
							if (strtotime($a_ngaborn['lasttime']) < time() - 60 * 60 * 24 * $_conf['ngaborn_daylimit']) {
								continue;
							}
						}
						
						if (empty($a_ngaborn['lasttime'])) {
							$a_ngaborn['lasttime'] = date('Y/m/d G:i');
						}
						
						$cont .= $a_ngaborn['word']."\t".$a_ngaborn['lasttime']."\t".$a_ngaborn['hits']."\n";
					} // foreach
				
					/*
					echo "<pre>";
					echo $cont;
					echo "</pre>";
					*/
				
					// ��������
				
					$fp = @fopen($ngaborns[$code]['file'], 'wb');	// or die("Error: cannot write. ( $ngaborns[$code]['file'] )");
					if ($fp) {
						@flock($fp, LOCK_EX);
						fputs($fp, $cont);
						@flock($fp, LOCK_UN);
						fclose($fp);
					}
				

				} // if
			
			} // foreach
		}
		return true;
	}

	/**
	 * NG���ځ[��HIT�L�^���X�V���ԂŃ\�[�g����
	 *
	 * @private
	 */
	function cmpLastTime($a, $b)
	{
		if (empty($a['lasttime']) || empty($b['lasttime'])) {
			return strcmp($a['lasttime'], $b['lasttime']);
		}
		return (strtotime($a['lasttime']) < strtotime($b['lasttime'])) ? 1 : -1;
	}

	/**
	 * ���ځ[��&NG���[�h�ݒ��ǂݍ���
	 */
	function loadNgAborns()
	{
		$ngaborns = array();
	
		$ngaborns['aborn_res'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_res.txt');	// ���ꂾ���������i���قȂ�
		$ngaborns['aborn_name'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_name.txt');
		$ngaborns['aborn_mail'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_mail.txt');
		$ngaborns['aborn_msg'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_msg.txt');
		$ngaborns['aborn_id'] = NgAbornCtl::readNgAbornFromFile('p2_aborn_id.txt');
		$ngaborns['ng_name'] = NgAbornCtl::readNgAbornFromFile('p2_ng_name.txt');
		$ngaborns['ng_mail'] = NgAbornCtl::readNgAbornFromFile('p2_ng_mail.txt');
		$ngaborns['ng_msg'] = NgAbornCtl::readNgAbornFromFile('p2_ng_msg.txt');
		$ngaborns['ng_id'] = NgAbornCtl::readNgAbornFromFile('p2_ng_id.txt');

		return $ngaborns;
	}

	/**
	 * loadNgAbornFromFile
	 *
	 * @private
	 */
	function readNgAbornFromFile($filename)
	{
		global $_conf;
	
		$lines = array();
		$array['file'] = $_conf['pref_dir'].'/'.$filename;
		if ($lines = @file($array['file'])) {
			$lines = array_map('trim', $lines);
		
			if ($lines) {
				foreach ($lines as $l) {
					$lar = explode("\t", $l);
					$ar['word'] = $lar[0];	// �Ώە�����
					$ar['lasttime'] = $lar[1];	// �Ō��HIT��������
					$ar['hits'] = intval($lar[2]);	// HIT��
					$array['data'][] = $ar;
				}
			}

		}
		return $array;

	}

}
?>