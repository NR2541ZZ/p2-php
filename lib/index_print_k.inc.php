<?php
/**
 * p2 - �g�їp�C���f�b�N�X��HTML�v�����g����֐�
 */
function index_print_k()
{
    global $_conf, $_login, $_info_msg_ht;

    $newtime = date('gis');
    
    $body = "";
    $ptitle = "rep2��޲�";
    
    // ���O�C�����[�U���
    $htm['auth_user'] = "<p>۸޲�հ��: {$_login->user_u} - " . date("Y/m/d (D) G:i:s") . "</p>\n";
    
    // p2���O�C���pURL
    $login_url = rtrim(dirname(P2Util::getMyUrl()), '/') . '/';
    $login_url_pc = $login_url . '?b=pc';
    $login_url_pc_hs = hs($login_url_pc);
    $login_url_k = $login_url . '?b=k&user=' . $_login->user_u;
    $login_url_k_hs = hs($login_url_k);
    
    // �O��̃��O�C�����
    if ($_conf['login_log_rec'] && $_conf['last_login_log_show']) {
        if (($log = P2Util::getLastAccessLog($_conf['login_log_file'])) !== false) {
            $log_hd = array_map('htmlspecialchars', $log);
            $htm['last_login'] = <<<EOP
<font color="#888888">
�O���۸޲ݏ�� - {$log_hd['date']}<br>
հ��:   {$log_hd['user']}<br>
IP:     {$log_hd['ip']}<br>
HOST:   {$log_hd['host']}<br>
UA:     {$log_hd['ua']}<br>
REFERER: {$log_hd['referer']}
</font>
EOP;
        }
    }
    
    // �Â��Z�b�V����ID���L���b�V������Ă��邱�Ƃ��l�����āA���[�U����t�����Ă���
    // �i���t�@�����l�����āA���Ȃ��ق��������ꍇ������̂Œ��Ӂj
    $user_at_a = '&amp;user=' . $_login->user_u;
    $user_at_q = '?user=' . $_login->user_u;
    
    require_once P2_LIB_DIR . '/brdctl.class.php';
    $search_form_htm = BrdCtl::getMenuKSearchFormHtml('menu_k.php');

    //=========================================================
    // �g�їp HTML �v�����g
    //=========================================================
    P2Util::header_nocache();
    echo $_conf['doctype'];
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

<a {$_conf['accesskey']}="1" href="subject.php?spmode=recent&amp;sb_view=shinchaku{$_conf['k_at_a']}{$user_at_a}">1.�ŋߓǂ񂾽ڂ̐V��</a><br>
<a {$_conf['accesskey']}="2" href="subject.php?spmode=recent{$_conf['k_at_a']}{$user_at_a}">2.�ŋߓǂ񂾽ڂ̑S��</a><br>
<a {$_conf['accesskey']}="3" href="subject.php?spmode=fav&amp;sb_view=shinchaku{$_conf['k_at_a']}{$user_at_a}">3.���C�ɽڂ̐V��</a><br>
<a {$_conf['accesskey']}="4" href="subject.php?spmode=fav{$_conf['k_at_a']}{$user_at_a}">4.���C�ɽڂ̑S��</a><br>
<a {$_conf['accesskey']}="5" href="menu_k.php?view=favita{$_conf['k_at_a']}{$user_at_a}">5.���C�ɔ�</a><br>
<a {$_conf['accesskey']}="6" href="menu_k.php?view=cate{$_conf['k_at_a']}{$user_at_a}">6.��ؽ�</a><br>
<a {$_conf['accesskey']}="7" href="subject.php?spmode=res_hist{$_conf['k_at_a']}{$user_at_a}">7.��������</a> <a {$_conf['accesskey']}="#" href="read_res_hist.php?nt={$newtime}{$_conf['k_at_a']}">#.۸�</a><br>
<a {$_conf['accesskey']}="8" href="subject.php?spmode=palace&amp;norefresh=1{$_conf['k_at_a']}{$user_at_a}">8.�ڂ̓a��</a><br>
<a {$_conf['accesskey']}="9" href="setting.php?dummy=1{$user_at_a}{$_conf['k_at_a']}">9.۸޲݊Ǘ�</a><br>
<a {$_conf['accesskey']}="0" href="editpref.php?dummy=1{$user_at_a}{$_conf['k_at_a']}">0.�ݒ�Ǘ�</a><br>

<hr>
{$search_form_htm}
<hr>

<form id="urlform" method="GET" action="{$_conf['read_php']}" target="read">
		2ch�̽�URL�𒼐ڎw��
		<input id="url_text" type="text" value="" name="url">
		<input type="submit" name="btnG" value="�\��">
</form>

<hr>
{$htm['auth_user']}

<p>
p2۸޲ݗpURL�i�g�сj<br>
<a href="{$login_url_k_hs}">{$login_url_k_hs}</a><br>
p2۸޲ݗpURL�iPC�j<br>
<a href="{$login_url_pc_hs}">{$login_url_pc_hs}</a>
</p>

<hr>
{$htm['last_login']}
</body>
</html>
EOP;

}

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
