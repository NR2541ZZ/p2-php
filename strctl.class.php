<?php
/*
	p2 - StrCtl -- �����񑀍�N���X
*/

if (!extension_loaded('mbstring')) {
	include_once './jcode/jcode_wrapper.php';
}

class StrCtl{

	function p2SJIStoEUC($str)
	{
		if (extension_loaded('mbstring')) {
			$str = mb_convert_encoding($str, 'EUC-JP', 'SJIS-win');
		} else {
			$str = jcode_convert_encoding($str, 'euc', 'sjis');
		}
		return $str;
	}

	function p2EUCtoSJIS($str)
	{
		if (extension_loaded('mbstring')) {
			$str = mb_convert_encoding($str, 'SJIS-win', 'EUC-JP');
		} else {
			$str = jcode_convert_encoding($str, 'sjis', 'euc');
		}
		return $str;
	}

	function p2SJIStoUTF($str)
	{
		if (extension_loaded('mbstring')) {
			$str = mb_convert_encoding($str, 'UTF-8', 'SJIS-win');
		} else {
			$str = jcode_convert_encoding($str, 'utf8', 'sjis');
		}
		return $str;
	}

	function p2UTFtoSJIS($str)
	{
		if (extension_loaded('mbstring')) {
			$str = mb_convert_encoding($str, 'SJIS-win', 'UTF-8');
		} else {
			$str = jcode_convert_encoding($str, 'sjis', 'utf8');
		}
		return $str;
	}


	/**
	 * �t�H�[�����瑗���Ă������[�h���}�b�`�֐��ɓK��������
	 *
	 * @return string $word_fm �K���p�^�[���BSJIS�ŕԂ��B
	 */
	function wordForMatch($word, $method = '')
	{
		$word_fm = $word;
		if ($method != 'just') {
			if (P2_MBREGEX_AVAILABLE == 1) {
				$word_fm = mb_ereg_replace('�@', ' ', $word_fm);
			} else {
				$word_fm = str_replace('�@', ' ', $word_fm);
			}
		}
		$word_fm = trim($word);
		$word_fm = htmlspecialchars($word_fm, ENT_NOQUOTES);
		if (in_array($method, array('and', 'or', 'just'))) {
			// preg_quote()��2�o�C�g�ڂ�0x5B("[")��"�["�Ȃǂ��ϊ�����Ă��܂��̂�
			// UTF-8�ɂ��Ă��琳�K�\���̓��ꕶ�����G�X�P�[�v
			$word_fm = mb_convert_encoding($word_fm, 'UTF-8', 'SJIS-win');
			if (P2_MBREGEX_AVAILABLE == 1) {
				$word_fm = preg_quote($word_fm);
			} else {
				$word_fm = preg_quote($word_fm, '/');
			}
			$word_fm = mb_convert_encoding($word_fm, 'SJIS-win', 'UTF-8');
		} else {
			if (P2_MBREGEX_AVAILABLE == 0) {
				$word_fm = str_replace('/', '\/', $word_fm);
			}
		}
		return $word_fm;
	}

	/**
	 * �}���`�o�C�g�Ή��̐��K�\���}�b�`���\�b�h
	 *
	 * @param string $pattern �}�b�`������BSJIS�œ����Ă���B
	 * @param string $target �����Ώە�����BSJIS�œ����Ă���B
	 */
	function filterMatch($pattern, &$target)
	{	
		// HTML�v�f�Ƀ}�b�`�����Ȃ����߂̔ے��ǂ݃p�^�[����t��
		$pattern = '(' . $pattern . ')(?![^<]*>)';

		if (P2_MBREGEX_AVAILABLE == 1) {
			$result = @mb_eregi($pattern, $target);
		} else {
			// UTF-8�ɕϊ����Ă��珈������
			$pattern_utf8 = '/' . mb_convert_encoding($pattern, 'UTF-8', 'SJIS-win') . '/iu';
			$target_utf8 = mb_convert_encoding($target, 'UTF-8', 'SJIS-win');
			$result = @preg_match($pattern_utf8, $target_utf8);
			//$result = mb_convert_encoding($result, 'SJIS-win', 'UTF-8');
		}
		if ($result) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * �}���`�o�C�g�Ή��̃}�[�L���O
	 *
	 * @param string $pattern �}�b�`������BSJIS�œ����Ă���B
	 * @param string $target �u���Ώە�����BSJIS�œ����Ă���B
	 */
	function filterMarking($pattern, &$target, $marker = '<b class="filtering">\\1</b>')
	{
		// HTML�v�f�Ƀ}�b�`�����Ȃ����߂̔ے��ǂ݃p�^�[����t��
		$pattern = '(' . $pattern . ')(?![^<]*>)';

		if (P2_MBREGEX_AVAILABLE == 1) {
			$result = @mb_eregi_replace($pattern, $marker, $target);
		} else {
			// UTF-8�ɕϊ����Ă��珈������
			$pattern_utf8 = '/' . mb_convert_encoding($pattern, 'UTF-8', 'SJIS-win') . '/iu';
			$target_utf8 = mb_convert_encoding($target, 'UTF-8', 'SJIS-win');
			$result = @preg_replace($pattern_utf8, $marker, $target_utf8);
			$result = mb_convert_encoding($result, 'SJIS-win', 'UTF-8');
		}

		if (!$result) {
			return $target;
		}
		return $result;
	}
}

?>