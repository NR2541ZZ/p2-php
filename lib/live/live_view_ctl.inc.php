<?php
/*
	+live - �X���b�h�\���Ɋւ��鋤�ʏ��� ../showthreadpc.class.php ���ǂݍ��܂��
*/

// �X�����Ă���̓����ɂ�鏈��
$thr_birth = date("U", $this->thread->key);

if ($_conf['live.time_lag'] != 0) {
	$thr_time_lag = $_conf['live.time_lag'] * 86400;
} else {
	$thr_time_lag = 365 * 86400;
}

// �I�[�g�����[�h�ݒ�l+����
if (!preg_match("({$this->thread->bbs})", $_conf['live.default_reload'])
&& (preg_match("({$this->thread->bbs}|{$this->thread->host})", $_conf['live.reload']) || $_conf['live.reload'] == all)
&& (date("U") < $thr_birth + $thr_time_lag)) {
	$nldr_ylr_d = true;
} else {
	$nldr_ylr_d = false;
}

// �n�C���C�g�\���^�C�v
if ($_conf['live.highlight_area'] == 1) {
	if ($isHighlightMsg || $isHighlightName || $isHighlightMail || $isHighlightId) {
		$highlight_res = "background-color: {$STYLE['live_highlight']}";
	} elseif ($isHighlightChain) {
		$highlight_res = "background-color: {$STYLE['live_highlight_chain']}";
	}
}

// ���O
// �f�t�H���g�̖������̕\��
// showthreadpc.class.php�ɂ�

// ���t��ID
// ID�t�B���^
if ($_conf['flex_idpopup'] == 1 && $id && $this->thread->idcount[$id] > 1) {
	$date_id = preg_replace_callback('|ID: ?([0-9A-Za-z/.+]{8,11})|', array($this, 'idfilter_callback'), $date_id);
}
// �g��ID ����p2ID �t���u���E�UID �̋���
if ($_conf['live.id_b']) {
	if (!preg_match("(ID:)", $date_id)) { // ID���������\���L��̔�
		$date_id = preg_replace('(((O|P|Q)$)(?![^<]*>))', '<b class="mail">$1</b>', $date_id);
	} else {
		$date_id = preg_replace('((ID: ?)([0-9A-Za-z/.+]{10}|[0-9A-Za-z/.+]{8}|\\?\\?\\?)?(O|P|Q)(?=[^0-9A-Za-z/.+]|$)(?![^<]*>))', '$1$2<b class="mail">$3</b>', $date_id);
	}
}
// ���t�̒Z�k
if (preg_match("([0-2][0-9]{3}/[0-1][0-9]/[0-3][0-9])", $date_id)) {
	if ($nldr_ylr_d) { // �I�[�g�����[�h/�X�N���[���̏ꍇ���t��S�폜
		$date_id = preg_replace("([0-2][0-9]{3}/[0-1][0-9]/[0-3][0-9]\(..\))", "", $date_id);
	} else { // ��L�ȊO�͔N����2����
		if (preg_match("(class=\"ngword)", $date_id)) { // NGID�̎�
			$date_id = preg_replace("(([0-2][0-9])([0-9]{2}/[0-1][0-9]/[0-3][0-9]\(..\)))", "$2", $date_id);
		} else {
			$date_id = preg_replace("(([0-2][0-9])([0-9]{2}/[0-1][0-9]/[0-3][0-9]\(..\)))", "$2", $date_id);
		}
	}
}

// ���[��
if ($mail) {
	// �I�[�g�����[�h/�X�N���[���̏ꍇ
	if ($_conf['live.mail_sage'] 
	&& ($nldr_ylr_d)) {
		// sage �� �� ��
		if (preg_match("(^(\s|�@)*sage(\s|�@)*$)", $mail)) {
			if ($STYLE['read_mail_sage_color']) {
				$mail = "<span class=\"sage\" title=\"{$mail}\">��</span>";
			} elseif ($STYLE['read_mail_color']) {
				$mail = "<span class=\"mail\" title=\"{$mail}\">��</span>";
			} else {
				$mail = "<span title=\"{$mail}\">��</span>";
			}
		// sage �ȊO�� �� ��
		} else {
			$mail = "<span class=\"mail\" title=\"{$mail}\">��</span>";
		}
	// �m�[�}������
	} elseif (preg_match("(^(\s|�@)*sage(\s|�@)*$)", $mail)
	&& $STYLE['read_mail_sage_color']) {
		$mail = "<span class=\"sage\">{$mail}</span>";
	} elseif ($STYLE['read_mail_color']) {
		$mail = "<span class=\"mail\">{$mail}</span>";
	} else {
		$mail = "{$mail}";
	}
}

// ��Q�ƃ��X�|�b�v�A�b�v
if ($_conf['live.ref_res']) {
	$url_res = "read.php?bbs={$this->thread->bbs}&key={$this->thread->key}&host={$this->thread->host}&ls=all&offline=1&field=msg&word=%28%3E%7C%81%84%29%28%5Cd%2B%2C%29*{$i}%5CD&method=regex&match=on&submit_filter=%83t%83B%83%8B%83%5E%95%5C%8E%A6";
	$ref_res_pp ="<a href=\"{$url_res}\" onmouseover=\"showHtmlPopUp('{$url_res},renzokupop=true',event,1)\" onmouseout=\"offHtmlPopUp()\" title=\"{$i} �ւ̃��X��\��\"><img src=\"img/pop.png\" alt=\"P\" width=\"12\" height=\"12\"></a>&nbsp;";
}

// ���X�̕��@
if ($nldr_ylr_d) {
	$ttitle_en_q ="&amp;ttitle_en=".rawurlencode(base64_encode($this->thread->ttitle));
	// ���e���_�u���N���b�N
	if ($_conf['live.res_button'] >= 1) {
		$res_dblclc = "ondblclick=\"window.parent.livepost.location.href='live_post_form.php?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;resnum={$i}{$ttitle_en_q}&amp;inyou=1'\" title=\"{$i} �Ƀ��X (double click)\"";
	}
	// ���X�{�^��
	if ($_conf['live.res_button'] <= 1) {
		$res_button = "<a href=\"live_post_form.php?host={$this->thread->host}&amp;bbs={$this->thread->bbs}&amp;key={$this->thread->key}&amp;resnum={$i}{$ttitle_en_q}&amp;inyou=1\" target=\"livepost\" title=\"{$i} �Ƀ��X\"><img src =\"./img/re.png\" alt=\"Re:\" width=\"22\" height=\"12\"></a>";
	} 
}

// ���e
// �\���؋l�ߏ���
if ($_conf['live.msg_a']
&& (preg_match("(<br>)", $msg))){
	$msg = mb_ereg_replace("(^\s((\s|�@)*<br>(\s|�@)*)+|((\s|�@)*<br>(\s|�@)*)+\s$)", " ", $msg);	// �����A�����̑S���s������
	$msg = mb_ereg_replace("(((\s|�@)*<br>(\s|�@)*){3,})", " <br>  <br> ", $msg);					// 3�A�ȏ�̉��s��2�A��
	if (mb_ereg_match("(((\s|�@)*.(\s|�@)*<br>){2,})", $msg)) {
		$msg = mb_ereg_replace("((\s|�@)*<br>(\s|�@)*)", " ", $msg);								// 3�s�ȏ��1�����u���̉��s��������s���폜
	}
}
// �I�[�g�����[�h�̏ꍇ�̕\���؋l�ߏ���
if ($_conf['live.msg_b']
&& ($nldr_ylr_d)) {
	$msg = mb_convert_kana($msg, 'rnas');								// �S�p�̉p���A�L���A�X�y�[�X�𔼊p��
	if (!preg_match ("(tp:/|ps:/|res/)", $msg)) {
		$msg = mb_ereg_replace("((\s|�@)*<br>(\s|�@)*)", " ", $msg);	// �S���s�����������p�X�y�[�X�ɁB���e�ɊO�������N��ʐ����ꗗ���܂ޏꍇ�͑ΏۊO
	}
	$msg = mb_ereg_replace("(\s{2,})", " ", $msg);						// �A���X�y�[�X��1��
}

?>