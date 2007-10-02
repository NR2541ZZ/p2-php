<?php
/**
 * p2 - �g�їp�C���f�b�N�X��HTML�v�����g����֐�
 *
 * @return  void
 */
function index_print_k()
{
    global $_conf, $_login;

    $menuKLinkHtmls = getMenuKLinkHtmls($_conf['menuKIni']);
    
    $body = '';
    $ptitle = '��޷��rep2';
    
    // ���O�C�����[�U���
    $htm['auth_user']   = "<p>۸޲�հ��: {$_login->user_u} - " . date("Y/m/d (D) G:i:s") . '</p>' . "\n";
    
    // p2���O�C���pURL
    $login_url          = rtrim(dirname(P2Util::getMyUrl()), '/') . '/';
    $login_url_pc       = $login_url . '?b=pc';
    $login_url_pc_hs    = hs($login_url_pc);
    $login_url_k        = $login_url . '?b=k&user=' . $_login->user_u;
    $login_url_k_hs     = hs($login_url_k);
    
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
    
    $body_at    = P2Util::getBodyAttrK();
    $hr         = P2Util::getHrHtmlK();
    
    //=========================================================
    // �g�їp HTML�o��
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
<body{$body_at}>
<h1>{$ptitle}</h1>
EOP;
    P2Util::printInfoHtml();
    
    foreach ($menuKLinkHtmls as $v) {
        echo $v . "<br>\n";
    }
    echo <<<EOP
<br>
<a href="edit_indexmenuk.php{$user_at_q}{$_conf['k_at_a']}">�ƭ�����</a>
{$hr}
{$search_form_htm}
{$hr}

<form id="urlform" method="GET" action="{$_conf['read_php']}" target="read">
	2ch�̽�URL�𒼐ڎw��
	<input id="url_text" type="text" value="" name="url">
	<input type="submit" name="btnG" value="�\��">
</form>

{$hr}
{$htm['auth_user']}

<p>
p2۸޲ݗpURL�i�g�сj<br>
<a href="{$login_url_k_hs}">{$login_url_k_hs}</a><br>
p2۸޲ݗpURL�iPC�j<br>
<a href="{$login_url_pc_hs}">{$login_url_pc_hs}</a>
</p>

{$hr}
{$htm['last_login']}
</body>
</html>
EOP;

}


/**
 * ���j���[���ڂ̃����NHTML�z����擾����
 *
 * @access  public
 * @param   array   $menuKIni  ���j���[���� �W���ݒ�
 * @return  array
 */
function getMenuKLinkHtmls($menuKIni, $noLink = false)
{
    global $_conf;
    
    $menuLinkHtmls = array();
    // ���[�U�ݒ菇���Ń��j���[HTML���擾
    foreach ($_conf['index_menu_k'] as $code) {
        if (isset($menuKIni[$code])) {
            if ($html = _getMenuKLinkHtml($code, $menuKIni, $noLink)) {
                $menuLinkHtmls[$code] = $html;
                unset($menuKIni[$code]);
            }
        }
    }
    if ($menuKIni) {
        foreach ($menuKIni as $code => $menu) {
            if ($html = _getMenuKLinkHtml($code, $menuKIni, $noLink)) {
                $menuLinkHtmls[$code] = $html;
                unset($menuKIni[$code]);
            }
        }
    }
    return $menuLinkHtmls;
}

/**
 * ���j���[���ڂ̃����NHTML���擾����
 *
 * @param   array   $menuKIni  ���j���[���� �W���ݒ�
 * @return  string  HTML
 */
function _getMenuKLinkHtml($code, $menuKIni, $noLink = false)
{
    global $_conf, $_login;
    
    static $accesskey_;
    
    // �����ȃR�[�h�w��Ȃ�
    if (!isset($menuKIni[$code][0]) || !isset($menuKIni[$code][1])) {
        return false;
    }
    
    if (!isset($accesskey_)) {
        $accesskey_ = 0;
    } else {
        $accesskey_++;
    }
    $accesskey = $accesskey_;
    
    if ($_conf['index_menu_k_from1']) {
        $accesskey = $accesskey + 1;
        if ($accesskey == 10) {
            $accesskey = 0;
        }
    }
    if ($accesskey > 9) {
        $accesskey = null;
    }
    
    $href = $menuKIni[$code][0] . '&user=' . $_login->user_u . '&' . UA::getQueryKey() . '=' . UA::getQueryValue();
    $name = $menuKIni[$code][1];
    if (!is_null($accesskey)) {
        $name = $accesskey . '.' . $name;
    }

    if ($noLink) {
        $linkHtml = hs($name);
    } else {
        $accesskeyAt = is_null($accesskey) ? '' : " {$_conf['accesskey']}=\"{$accesskey}\"";
        $linkHtml = "<a $accesskeyAt href=\"" . hs($href) . '">' . hs($name) . "</a>";
    }
    
    // ���� - #.���O
    if ($code == 'res_hist') {
        $newtime = date('gis');
        $name = '#.۸�';
        if ($noLink) {
            $linkHtml .= ' ' . hs($name);
        } else {
            $linkHtml .= " <a {$_conf['accesskey']}=\"#\" href=\"read_res_hist.php?nt={$newtime}{$_conf['k_at_a']}\">" . hs($name) . "</a>";
        }
    }
    
    return $linkHtml;
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
