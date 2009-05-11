<?php
require_once P2_LIB_DIR . '/index.funcs.php';

/**
 * p2 - �g�їp�C���f�b�N�X��HTML�v�����g����֐�
 *
 * @return  void
 */
function index_print_k()
{
    global $_conf, $_login;

    $menuKLinkHtmls = getIndexMenuKLinkHtmls(getIndexMenuKIni());
    
    $body = '';
    $ptitle = '��޷��rep2';
    
    // ���O�C�����[�U���
    $htm['auth_user']   = '<div>۸޲�հ��: ' . hs($_login->user_u) . ' - ' . date('Y/m/d (D) G:i:s') . '</div>' . "\n";
    
    // p2���O�C���pURL
    $login_url          = rtrim(dirname(P2Util::getMyUrl()), '/') . '/';
    $login_url_pc       = $login_url . '?b=pc';
    $login_url_k        = $login_url . '?b=k&user=' . $_login->user_u;
    
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

    $edit_indexmenuk_atag = P2View::tagA(
        P2Util::buildQueryUri('edit_indexmenuk.php',
            array(
                'user' => $_login->user_u,
                UA::getQueryKey() => UA::getQueryValue()
            )
        ),
        hs('�ƭ�����')
    );
    
    /*
    $rss_k_atag = '';
    if ($_conf['enable_rss']) {
        $rss_k_atag = P2View::tagA(
            P2Util::buildQueryUri($_conf['menu_k_php'],
                array(
                    'view' => 'rss',
                    'user' => $_login->user_u,
                    UA::getQueryKey() => UA::getQueryValue()
                )
            ),
            hs('RSS')
        );
    }
    */
    
    require_once P2_LIB_DIR . '/BrdCtl.php';
    $search_form_htm = BrdCtl::getMenuKSearchFormHtml($_conf['menu_k_php']);
    
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
echo <<<EOP
    <title>{$ptitle}</title>
</head>
<body{$body_at}>
<h1>{$ptitle}</h1>
EOP;
    P2Util::printInfoHtml();
    
    foreach ($menuKLinkHtmls as $v) {
        echo $v . "<br>\n";
    }
    ?>
<br>
<?php echo $edit_indexmenuk_atag; ?>
<?php echo $hr; ?>
<?php echo $search_form_htm; ?>
<?php echo $hr; ?>

<form id="urlform" method="GET" action="<?php eh($_conf['read_php']); ?>" target="read">
	2ch�̽�URL�𒼐ڎw��
	<input id="url_text" type="text" value="" name="url">
	<?php echo P2View::getInputHiddenKTag(); ?>
	<input type="submit" name="btnG" value="�\��">
</form>

<?php echo $hr; ?>
<?php echo $htm['auth_user']; ?>
<br>

<div>
p2۸޲ݗpURL�i�g�сj<br>
<a href="<?php eh($login_url_k); ?>"><?php eh($login_url_k); ?></a><br>
p2۸޲ݗpURL�iPC�j<br>
<a href="<?php eh($login_url_pc); ?>"><?php eh($login_url_pc); ?></a>
</div>

<?php echo $hr; ?>
<?php echo $htm['last_login']; ?>
</body>
</html>
<?php
}


//============================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//============================================================================
/**
 * ���j���[���ڂ̃����NHTML���擾����
 *
 * @param   array   $menuKIni  ���j���[���� �W���ݒ�
 * @param   boolean $noLink    �����N�����Ȃ��̂Ȃ�true
 * @return  string  HTML
 */
function _getMenuKLinkHtml($code, $menuKIni, $noLink = false)
{
    global $_conf, $_login;
    
    static $accesskey_ = null;
    
    // �����ȃR�[�h�w��Ȃ�
    if (!isset($menuKIni[$code][0]) || !isset($menuKIni[$code][1])) {
        return false;
    }
    
    if (is_null($accesskey_)) {
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
        $accesskeyAt = is_null($accesskey) ? '' : " {$_conf['accesskey_for_k']}=\"{$accesskey}\"";
        $linkHtml = "<a $accesskeyAt href=\"" . hs($href) . '">' . hs($name) . "</a>";
    }
    
    // ���� - #.���O
    if ($code == 'res_hist') {
        $name = '#.۸�';
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
        $linkHtml .= ' ' . $logHt;
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
