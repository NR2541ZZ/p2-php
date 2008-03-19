<?php
/*
	+live - �n�C���C�g���[�h�Ɋւ��鋤�ʏ��� ../showthreadpc.class.php ���ǂݍ��܂��
*/

$isHighlightChain = false;
$isHighlightName = false;
$isHighlightMail = false;
$isHighlightId = false;
$isHighlightMsg = false;
//$highlight_chain_info = array();
//$highlight_msg_info = array();

// �A���n�C���C�g
if ($_conf['live.highlight_chain'] && preg_match_all('/(?:&gt;|��)([1-9][0-9\\-,]*)/', $msg, $matches)) {
	$highlight_chain_nums = array_unique(array_map('intval', split('[-,]+', trim(implode(',', $matches[1]), '-,'))));
	if (array_intersect($highlight_chain_nums, $this->highlight_nums)) {
//		$a_highlight_chain_num = array_shift($highlight_chain_nums);
		$ngaborns_hits['highlight_chain']++;
		$ngaborns_body_hits++;
		$this->highlight_nums[] = $i;
		$isHighlightChain = true;
//		$highlight_chain_info[] = sprintf('&gt;&gt;%d', $a_highlight_chain_num);
	}
}

// �n�C���C�g�l�[���`�F�b�N
if ($this->ngAbornCheck('highlight_name', strip_tags($name)) !== false) {
	$ngaborns_hits['highlight_name']++;
	$ngaborns_head_hits++;
	$this->highlight_nums[] = $i;
	$isHighlightName = true;
}

// �n�C���C�g���[���`�F�b�N
if ($this->ngAbornCheck('highlight_mail', $mail) !== false) {
	$ngaborns_hits['highlight_mail']++;
	$ngaborns_head_hits++;
	$this->highlight_nums[] = $i;
	$isHighlightMail = true;
}

// �n�C���C�gID�`�F�b�N
if ($this->ngAbornCheck('highlight_id', $date_id) !== false) {
	$ngaborns_hits['highlight_id']++;
	$ngaborns_head_hits++;
	$this->highlight_nums[] = $i;
	$isHighlightId = true;
}

// �n�C���C�g���b�Z�[�W�`�F�b�N
$a_highlight_msg = $this->ngAbornCheck('highlight_msg', $msg);
if ($a_highlight_msg !== false) {
	$ngaborns_hits['highlight_msg']++;
	$ngaborns_body_hits++;
	$this->highlight_nums[] = $i;
	$isHighlightMsg = true;
//	$highlight_msg_info[] = sprintf('%s', htmlspecialchars($a_highlight_msg, ENT_QUOTES));
	if (!preg_match("(^<regex(>|:i>).+$)", $a_highlight_msg)) {
		if (preg_match("(^<i>.+$)", $a_highlight_msg)) {
			$a_highlight_msg = preg_replace("(^<i>)", "", $a_highlight_msg);
			// preg_quote()��2�o�C�g�ڂ�0x5B("[")��"�["�Ȃǂ��ϊ�����Ă��܂��̂�
			// UTF-8�ɂ��Ă��琳�K�\���̓��ꕶ�����G�X�P�[�v
			$a_highlight_msg = mb_convert_encoding($a_highlight_msg, 'UTF-8', 'SJIS-win');
			$a_highlight_msg = preg_quote($a_highlight_msg);
			$a_highlight_msg = mb_convert_encoding($a_highlight_msg, 'SJIS-win', 'UTF-8');
			$a_highlight_msg = "<i>" . $a_highlight_msg;
		} else {
			// ��ɓ���
			$a_highlight_msg = mb_convert_encoding($a_highlight_msg, 'UTF-8', 'SJIS-win');
			$a_highlight_msg = preg_quote($a_highlight_msg);
			$a_highlight_msg = mb_convert_encoding($a_highlight_msg, 'SJIS-win', 'UTF-8');
		}
	}
	$this->highlight_msgs[] = $a_highlight_msg;
	$highlight_msgs = array_unique($this->highlight_msgs);
}

?>