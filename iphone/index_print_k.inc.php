<?php
require_once P2_LIB_DIR . '/index.funcs.php';

/**
 * p2 - �g�їp�C���f�b�N�X��HTML�v�����g����֐�
 *
 * @access  public
 * @return  void
 */
function index_print_k()
{
    global $_conf, $_login;

    $menuKLinkHtmls = getIndexMenuKLinkHtmls(getIndexMenuKIni());
    
    $ptitle = $_conf['p2name'] . 'iPhone';
    
    // ���O�C�����[�U���
    $auth_user_ht   = sprintf(
        '<p>۸޲�հ��: %s - %s</p>',
        hs($_login->user_u), date('Y/m/d (D) G:i:s') 
    );
    
    // p2���O�C���pURL
    $login_url = rtrim(dirname(P2Util::getMyUrl()), '/') . '/';
    $login_url_pc = P2Util::buildQueryUri($login_url,
        array(
            UA::getQueryKey() => 'pc'
        )
    );
    $login_url_k = P2Util::buildQueryUri($login_url,
        array(
            UA::getQueryKey() => 'k',
            'user' => $_login->user_u
        )
    );

    // �O��̃��O�C�����
    if ($_conf['login_log_rec'] && $_conf['last_login_log_show']) {
        if (false !== $log = P2Util::getLastAccessLog($_conf['login_log_file'])) {
            $log_hs = array_map('htmlspecialchars', $log);
            $htm['last_login'] = <<<EOP
<font color="#888888">
�O���۸޲ݏ�� - {$log_hs['date']}<br>
հ��:   {$log_hs['user']}<br>
IP:     {$log_hs['ip']}<br>
HOST:   {$log_hs['host']}<br>
UA:     {$log_hs['ua']}<br>
REFERER: {$log_hs['referer']}
</font>
EOP;
        }
    }
    
    // �Â��Z�b�V����ID���L���b�V������Ă��邱�Ƃ��l�����āA���[�U����t�����Ă���
    // �i���t�@�����l�����āA���Ȃ��ق��������ꍇ������̂Œ��Ӂj
    $narabikae_uri = P2Util::buildQueryUri('edit_indexmenui.php',
        array(
            'user' => $_login->user_u,
            UA::getQueryKey() => UA::getQueryValue()
        )
    );
    
    require_once P2_LIB_DIR . '/BrdCtl.php';
    $search_form_htm = BrdCtl::getMenuKSearchFormHtml('menu_i.php');

    $body_at    = P2View::getBodyAttrK();
    $hr         = P2View::getHrHtmlK();

    //=========================================================
    // �g�їp HTML�o��
    //=========================================================
    P2Util::headerNoCache();
    P2View::printDoctypeTag();
    ?>
<html>
<head>
<?php
    P2View::printExtraHeadersHtml();
?>
<script type="text/javascript"> 
<!-- 
window.onload = function() { 
setTimeout(scrollTo, 100, 0, 1); 
} 
// --> 
</script> 
<style type="text/css" media="screen">@import "./iui/iui.css";</style>
    <title><?php eh($ptitle); ?></title>
</head>
<body>
    <div class="toolbar">
<h1 id="pageTitle"><?php eh($ptitle); ?></h1>
    <a class="button" href="<?php eh($narabikae_uri); ?>">����</a>
    </div>
    <ul id="home">
    <li class="group">���j���[</li>
<?php

P2Util::printInfoHtml();

foreach ($menuKLinkHtmls as $v) {
    ?><li><?php echo $v; ?></li><?php
}

?>
<li class="group">����</li>
<?php echo $search_form_htm; ?>
</ul>
<br>
</body>
</html>
<?php
}
/*

{$hr}
{$auth_user_ht}

{$hr}
{$htm['last_login']}
*/

//============================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//============================================================================
/**
 * ���j���[���ڂ̃����NHTML���擾����
 *
 * @access  private
 * @param   array   $menuKIni  ���j���[���� �W���ݒ�
 * @param   boolean $noLink    �����N�����Ȃ��̂Ȃ�true
 * @return  string  HTML
 */
function _getMenuKLinkHtml($code, $menuKIni, $noLink = false)
{
    global $_conf, $_login;
    
    static $accesskey_ = 0;
    
    // �����ȃR�[�h�w��Ȃ�
    if (!isset($menuKIni[$code][0]) || !isset($menuKIni[$code][1])) {
        return false;
    }
    $accesskey = ++$accesskey_;
    
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
    /*if (!is_null($accesskey)) {
        $name = $accesskey . '.' . $name;
    }*/

    if ($noLink) {
        $linkHtml = hs($name);
    } else {
        $accesskeyAt = is_null($accesskey) ? '' : " {$_conf['accesskey_for_k']}=\"{$accesskey}\"";
        $linkHtml = "<a href=\"" . hs($href) . '">' . hs($name) . "</a>";
    }
    
    // ���� - #.���O
    if ($code == 'res_hist') {
        $name = '���O';
        if ($noLink) {
            $logHt = hs($name);
        } else {
            $newtime = date('gis');
            $logHt = P2View::tagA(
                P2Util::buildQueryUri('read_res_hist.php',
                    array(
                        'nt' => $newtime,
                        UA::getQueryKey() => UA::getQueryValue()
                    )
                ),
                hs($name),
                array($_conf['accesskey_for_k'] => '#')
            );
        }
        $linkHtml .= ' </li><li>' . $logHt ;
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
