<?php
/*
	p2 -  �g�їp�C���f�b�N�X�v�����g�֐�
*/

require_once './p2util.class.php';	// p2�p�̃��[�e�B���e�B�N���X

/**
* �g�їp�C���f�b�N�X�v�����g
*/
function index_print_k()
{
	global $_conf, $login;
	global $_info_msg_ht;
	
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
			$p_htm['log'] = array_map('htmlspecialchars', $log);
			$p_htm['last_login'] =<<<EOP
�O���۸޲ݏ�� - {$p_htm['log']['date']}<br>
հ��: {$p_htm['log']['user']}<br>
IP: {$p_htm['log']['ip']}<br>
HOST: {$p_htm['log']['host']}<br>
UA: {$p_htm['log']['ua']}<br>
REFERER: {$p_htm['log']['referer']}
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
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<title>{$ptitle}</title>
</head>
<body>
<h1>{$ptitle}</h1>
{$_info_msg_ht}
<ol>
	<li><a {$_conf['accesskey']}="1" href="subject.php?spmode=fav&amp;sb_view=shinchaku{$_conf['k_at_a']}">���C�ɽڂ̐V��</a></li>
	<li><a {$_conf['accesskey']}="2" href="subject.php?spmode=fav{$_conf['k_at_a']}">���C�ɽڂ̑S��</a></li>
	<li><a {$_conf['accesskey']}="3" href="menu_k.php?view=favita{$_conf['k_at_a']}">���C�ɔ�</a></li>
	<li><a {$_conf['accesskey']}="4" href="menu_k.php?view=cate{$_conf['k_at_a']}">��ؽ�</a></li>	
	<li><a {$_conf['accesskey']}="5" href="subject.php?spmode=recent&amp;sb_view=shinchaku{$_conf['k_at_a']}">�ŋߓǂ񂾽ڂ̐V��</a></li>
	<li><a {$_conf['accesskey']}="6" href="subject.php?spmode=recent{$_conf['k_at_a']}">�ŋߓǂ񂾽ڂ̑S��</a></li>
	<li><a {$_conf['accesskey']}="7" href="subject.php?spmode=res_hist{$_conf['k_at_a']}">��������</a> <a href="read_res_hist.php?nt={$newtime}{$_conf['k_at_a']}">۸�</a></li>
	<li><a {$_conf['accesskey']}="8" href="subject.php?spmode=palace&amp;norefresh=true{$_conf['k_at_a']}">�ڂ̓a��</a></li>
	<li><a {$_conf['accesskey']}="9" href="setting.php{$_conf['k_at_q']}">�ݒ�</a></li>	
</ol>
<hr>
{$autho_user_ht}
{$p_htm['last_login']}
</body>
</html>
EOP;

}
?>