<?php
// rep2 -  �X���b�h�\�������̏����\��(PC�p)
// �t���[��3������ʁA�E������

require_once './conf/conf.inc.php';

// {{{ �X���w��t�H�[��

$explanation = '�������X���b�h��URL����͂��ĉ������B��Fhttp://pc.2ch.net/test/read.cgi/mac/1034199997/';



$ini_url_text = '';
$defurl = '';
// $defurl = getLastReadTreadUrl();

$onClick_ht = <<<EOP
var url_v = document.forms["urlform"].elements["url_text"].value;
if (url_v == "" || url_v == "{$ini_url_text}") {
	alert("{$explanation}");
	return false;
}
EOP;
$htm['urlform'] = <<<EOP
	<form id="urlform" method="GET" action="{$_conf['read_php']}" target="read">
			2ch�̃X��URL�𒼐ڎw��
			<input id="url_text" type="text" value="{$defurl}" name="url" size="62">
			<input type="submit" name="btnG" value="�\��" onClick='{$onClick_ht}'>
	</form>\n
EOP;

// }}}

$hsBookmarkletUrl = hs("javascript:location='" . dirname(P2Util::getMyUrl()) . "/" . $_conf['read_php'] . "?url='+escape(location);");

//=============================================================
// HTML�v�����g
//=============================================================
echo $_conf['doctype'];
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>rep2</title>
EOP;

include_once './style/style_css.inc';

echo <<<EOP
</head>
<body id="first_cont">
<br>
<div class="container">
    {$htm['urlform']}
    <hr>
    <ul>
        <li><a href="http://akid.s17.xrea.com/p2puki/pukiwiki.php?Bookmarklet" target="_blank">�u�b�N�}�[�N���b�g</a> �u<a href="{$hsBookmarkletUrl}">p2�œǂ�</a>�v</li>
    </ul>
</div>
</body>
</html>
EOP;

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
