<?php
/*
	+live - �����p�X���b�h�\�� A-Type ../showthreadpc.class.php ���ǂݍ��܂��
*/

// �I�[�g�����[�h�̔ŐV�����X�擪�ɖڈ󃉃C����}��
$live_newline = "<table id=\"r{$i}\" cellspacing=\"2\" cellpadding=\"0\" style=\"border-top: {$STYLE['live_b_n']}; {$highlight_res}\" width=\"100%\"><tr>";
$live_oldline = "<table id=\"r{$i}\" cellspacing=\"2\" cellpadding=\"0\" style=\"border-top: {$STYLE['live_b_l']}; {$highlight_res}\" width=\"100%\"><tr>";
// 
$live_td = "<td style=\"color:{$STYLE['read_color']}; font-size:{$STYLE['live_font-size']};\" width=\"250px\" valign=\"top\">";
// �V�����X�̔ԍ��F
$live_newnum = "<span class=\"spmSW\"{$spmeh}><b style=\"color:{$STYLE['read_newres_color']};\">{$i}</b></span>";
$live_oldnum = "<span class=\"spmSW\"{$spmeh}>{$i}</span>";

// �e�[�u���J�n �` �ԍ�
if ($this->thread->onthefly) {
	// �ԍ� (�I���U�t���C)
	$GLOBALS['newres_to_show_flag'] = true;
	$tores .= "{$live_oldline}{$live_td}<span class=\"ontheflyresorder spmSW\"{$spmeh}>{$i}</span>";
} elseif ($i == 1) {
	// �ԍ� (1)
	if ($this->thread->readnum > 1) {
		$tores .= "{$live_oldline}{$live_td}{$live_oldnum}";
	} else {
		$tores .= "{$live_oldline}{$live_td}{$live_newnum}";
	}
} elseif ($i == $this->thread->readnum +1) {
	// �ԍ� (�擪�V�����X)
	$GLOBALS['newres_to_show_flag'] = true;
	if ($nldr_ylr_d) {
		$tores .= "{$live_newline}{$live_td}{$live_newnum}";
	} else {
		$tores .= "{$live_oldline}{$live_td}{$live_newnum}";
	}
} elseif ($i > $this->thread->readnum) {
	// �ԍ� (�㑱�V�����X)
	$tores .= "{$live_oldline}{$live_td}{$live_newnum}";
} elseif ($_conf['expack.spm.enabled']) {
	// �ԍ� (SPM)
	$tores .= "{$live_oldline}{$live_td}{$live_oldnum}";
} else {
	// �ԍ�
	$tores .= "{$live_oldline}{$live_td}{$i}";
}

// ���O
$tores .= "&nbsp;<span class=\"name\"><b>{$name}</b></span>";

// ID
$tores .= "{$date_id}";

if ($this->am_side_of_id) {
	$tores .= ' ' . $this->activeMona->getMona($res_id);
}

// ���[��
$tores .= "&nbsp;{$mail}";

// �d�� & ���X�{�^�� & ��Q�ƃ��X�|�b�v�A�b�v
$stall_20 = "</td><td width=\"22px\" style=\" border-left: {$STYLE['live_b_s']};\">&nbsp;{$ref_res_pp}</td>";
$stall_30 = "</td><td width=\"32px\" style=\" border-left: {$STYLE['live_b_s']};\">&nbsp;{$res_button}</td>";
$stall_50 = "</td><td width=\"49px\" style=\" border-left: {$STYLE['live_b_s']};\">&nbsp;{$ref_res_pp}{$res_button}</td>";
if ($nldr_ylr_d) {
	if ($_conf['live.res_button'] == 2) {
		$tores .= "$stall_20";
	}
	if ($_conf['live.res_button'] <= 1) {
		if ($_conf['live.ref_res']) {
			$tores .= "$stall_50";
		} else {
			$tores .= "$stall_30";
		}
	}
} else {
	$tores .= "$stall_20";
}

// ���e
$tores .= "<td width=\"4px\">&nbsp;</td><td {$res_dblclc} width=\"\" id=\"{$res_id}\"{$automona_class} style=\"color:{$STYLE['read_color']}; font-size: {$STYLE['read_fontsize']};\">{$msg}�@</td>";

// �e�[�u���I��
$tores .= "</tr></table>\n";

// ���X�|�b�v�A�b�v�p���p
$tores .= $rpop;

?>