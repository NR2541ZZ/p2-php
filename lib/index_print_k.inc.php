<?php
/**
 * rep2 - �g�їp�C���f�b�N�X��HTML�v�����g����֐�
 */
function index_print_k()
{
    global $_conf, $_login;

    $newtime = date('gis');

    $body = '';
    $ptitle = "rep2��޲�";

    // �F�؃��[�U���
    $htm['auth_user'] = "<p>۸޲�հ��: {$_login->user_u} - " . date("Y/m/d (D) G:i:s") . "</p>\n";

    // �O��̃��O�C�����
    if ($_conf['login_log_rec'] && $_conf['last_login_log_show']) {
        if (($log = P2Util::getLastAccessLog($_conf['login_log_file'])) !== false) {
            $log_hd = array_map('htmlspecialchars', $log);
            $htm['last_login'] = <<<EOP
�O���۸޲ݏ�� - {$log_hd['date']}<br>
հ��:   {$log_hd['user']}<br>
IP:     {$log_hd['ip']}<br>
HOST:   {$log_hd['host']}<br>
UA:     {$log_hd['ua']}<br>
REFERER: {$log_hd['referer']}
EOP;
        }
    }

    // �Â��Z�b�V����ID���L���b�V������Ă��邱�Ƃ��l�����āA���[�U����t�����Ă���
    // �i���t�@�����l�����āA���Ȃ��ق��������ꍇ������̂Œ��Ӂj
    $user_at_a = '&amp;user='.$_login->user_u;
    $user_at_q = '?user='.$_login->user_u;

    $rss_k_ht = '';
    $iv2_k_ht = '';
    if ($_conf['expack.rss.enabled']) {
        $rss_k_ht = "#.<a {$_conf['accesskey']}=\"#\" href=\"menu_k.php?view=rss{$m_rss_set_a}{$_conf['k_at_a']}\">RSS</a><br>";
    }
    if ($_conf['expack.ic2.enabled'] == 2 || $_conf['expack.ic2.enabled'] == 3) {
        $iv2_k_ht = "%.<a href=\"iv2.php{$_conf['k_at_q']}\">�摜������ꗗ</a><br>";
    }

    require_once 'brdctl.class.php';
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
<body{$_conf['k_colors']}>
<h1>{$ptitle}</h1>
EOP;

    P2Util::printInfoHtml();

    echo <<<EOP
<a {$_conf['accesskey']}="1" href="subject.php?spmode=fav&amp;sb_view=shinchaku{$_conf['k_at_a']}{$user_at_a}">1.���C�ɽڂ̐V��</a><br>
<a {$_conf['accesskey']}="2" href="subject.php?spmode=fav{$_conf['k_at_a']}{$user_at_a}">2.���C�ɽڂ̑S��</a><br>
<a {$_conf['accesskey']}="3" href="menu_k.php?view=favita{$_conf['k_at_a']}{$user_at_a}">3.���C�ɔ�</a><br>
<a {$_conf['accesskey']}="4" href="menu_k.php?view=cate{$_conf['k_at_a']}{$user_at_a}">4.��ؽ�</a><br>
<a {$_conf['accesskey']}="5" href="subject.php?spmode=recent&amp;sb_view=shinchaku{$_conf['k_at_a']}{$user_at_a}">5.�ŋߓǂ񂾽ڂ̐V��</a><br>
<a {$_conf['accesskey']}="6" href="subject.php?spmode=recent{$_conf['k_at_a']}{$user_at_a}">6.�ŋߓǂ񂾽ڂ̑S��</a><br>
<a {$_conf['accesskey']}="7" href="subject.php?spmode=res_hist{$_conf['k_at_a']}{$user_at_a}">7.��������</a> <a {$_conf['accesskey']}="#" href="read_res_hist.php?nt={$newtime}{$_conf['k_at_a']}">#.۸�</a><br>
<a {$_conf['accesskey']}="8" href="subject.php?spmode=palace&amp;norefresh=1{$_conf['k_at_a']}{$user_at_a}">8.�ڂ̓a��</a><br>
<a {$_conf['accesskey']}="9" href="setting.php?dummy=1{$user_at_a}{$_conf['k_at_a']}">9.۸޲݊Ǘ�</a><br>
<a {$_conf['accesskey']}="0" href="editpref.php?dummy=1{$user_at_a}{$_conf['k_at_a']}">0.�ݒ�Ǘ�</a><br>
{$rss_k_ht}
?.<a href="tgrepc.php{$_conf['k_at_q']}">��������</a><br>
{$iv2_k_ht}

<hr>
{$search_form_htm}
<hr>

<form id="urlform" method="GET" action="{$_conf['read_php']}" target="read">
��URL�𒼐ڎw��
<input id="url_text" type="text" value="" name="url">
<input type="submit" name="btnG" value="�\��">
</form>

<hr>
{$htm['auth_user']}

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
