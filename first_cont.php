<?php
// rep2 -  �X���b�h�\�������̏����\��
// �t���[��3������ʁA�E������

include_once './conf/conf.inc.php';  // ��{�ݒ�

// {{{ �X���w��t�H�[��

$explanation = '�������X���b�h��URL����͂��ĉ������B��Fhttp://pc.2ch.net/test/read.cgi/mac/1034199997/';

// $defurl = getLastReadTreadUrl();

$onClick_ht = <<<EOP
var url_v=document.forms["urlform"].elements["url_text"].value;
if(url_v=="" || url_v=="{$ini_url_text}"){
	alert("{$explanation}");
	return false;
}
EOP;
$htm['urlform'] = <<<EOP
	<form id="urlform" method="GET" action="{$_conf['read_php']}" target="read">
			�X��URL�𒼐ڎw��
			<input id="url_text" type="text" value="{$defurl}" name="url" size="62">
			<input type="submit" name="btnG" value="�\��" onClick='{$onClick_ht}'>
	</form>\n
EOP;

// }}}

//=============================================================
// HTML�v�����g
//=============================================================
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html lang="ja">
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>rep2</title>
EOP;

@include("./style/style_css.inc"); // ��{�X�^�C���V�[�g �Ǎ�

echo <<<EOP
</head>
<body>
<br>
<div class="container">
    {$htm['urlform']}
    <hr>
	<h1><img src="img/rep2.gif" alt="rep2" width="131" height="63"></h1>
</div>
</body>
</html>
EOP;

?>
