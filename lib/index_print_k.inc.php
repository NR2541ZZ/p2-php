<?php
/*
	p2 -  �g�їp�C���f�b�N�X�v�����g�֐�
*/

/**
* �g�їp�C���f�b�N�X�v�����g
*/
function index_print_k()
{
	global $_conf, $login, $_info_msg_ht;
	
	$p_htm = array();
	
	$newtime = date('gis');
	
	$body = "";
	$autho_user_ht = "";
	$ptitle = "��޷��p2";
	
	// �F�؃��[�U���
	$autho_user_ht = "";
	if ($login['use']) {
		$autho_user_ht = "<p>۸޲�հ��: {$login['user']} - ".date("Y/m/d (D) G:i:s")."</p>\n";
	}
	
	// �O��̃��O�C�����
	if ($_conf['login_log_rec'] && $_conf['last_login_log_show']) {
		if (($log = P2Util::getLastAccessLog($_conf['login_log_file'])) !== false) {
			$log_hd = array_map('htmlspecialchars', $log);
			$p_htm['last_login'] =<<<EOP
�O���۸޲ݏ�� - {$log_hd['date']}<br>
հ��: {$log_hd['user']}<br>
IP: {$log_hd['ip']}<br>
HOST: {$log_hd['host']}<br>
UA: {$log_hd['ua']}<br>
REFERER: {$log_hd['referer']}
EOP;
		}
	}
	
	//=========================================================
	// �g�їp HTML �v�����g
	//=========================================================
	P2Util::header_content_type();
	if ($_conf['doctype']) {
		echo $_conf['doctype'];
	}
	echo <<<EOP
<html>
<head>
	{$_conf['meta_charset_ht']}
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<title>{$ptitle}</title>
</head>
<body>
<h1>{$ptitle}</h1>
{$_info_msg_ht}

<a {$_conf['accesskey']}="1" href="subject.php?spmode=fav&amp;sb_view=shinchaku{$_conf['k_at_a']}">1.���C�ɽڂ̐V��</a><br>
<a {$_conf['accesskey']}="2" href="subject.php?spmode=fav{$_conf['k_at_a']}">2.���C�ɽڂ̑S��</a><br>
<a {$_conf['accesskey']}="3" href="menu_k.php?view=favita{$_conf['k_at_a']}">3.���C�ɔ�</a><br>
<a {$_conf['accesskey']}="4" href="menu_k.php?view=cate{$_conf['k_at_a']}">4.��ؽ�</a><br>
<a {$_conf['accesskey']}="5" href="subject.php?spmode=recent&amp;sb_view=shinchaku{$_conf['k_at_a']}">5.�ŋߓǂ񂾽ڂ̐V��</a><br>
<a {$_conf['accesskey']}="6" href="subject.php?spmode=recent{$_conf['k_at_a']}">6.�ŋߓǂ񂾽ڂ̑S��</a><br>
<a {$_conf['accesskey']}="7" href="subject.php?spmode=res_hist{$_conf['k_at_a']}">7.��������</a> <a href="read_res_hist.php?nt={$newtime}{$_conf['k_at_a']}">۸�</a><br>
<a {$_conf['accesskey']}="8" href="subject.php?spmode=palace&amp;norefresh=true{$_conf['k_at_a']}">8.�ڂ̓a��</a><br>
<a {$_conf['accesskey']}="9" href="setting.php?dummy=1{$user_at_a}{$_conf['k_at_a']}">9.۸޲݊Ǘ�</a><br>
<a {$_conf['accesskey']}="0" href="editpref.php?dummy=1{$user_at_a}{$_conf['k_at_a']}">0.�ݒ�Ǘ�</a><br>

<hr>
{$autho_user_ht}
{$p_htm['last_login']}
</body>
</html>
EOP;

}
?>
