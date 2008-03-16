<?php
// p2 - �g�єŃ��X�t�B���^�����O

require_once './conf/conf.inc.php';

$_login->authorize(); // ���[�U�F��

/**
 * �X���b�h���
 */
$host = $_GET['host'];
$bbs  = $_GET['bbs'];
$key  = $_GET['key'];
$ttitle = base64_decode($_GET['ttitle_en']);
$ttitle_back = (isset($_SERVER['HTTP_REFERER']))
    ? '<a href="' . htmlspecialchars($_SERVER['HTTP_REFERER'], ENT_QUOTES) . '" title="�߂�">' . $ttitle . '</a>'
    : $ttitle;

/**
 * �O��t�B���^�l�ǂݍ���
 */
require_once P2_LIBRARY_DIR . '/filectl.class.php';

$cachefile = $_conf['pref_dir'] . '/p2_res_filter.txt';

if (file_exists($cachefile) and $res_filter_cont = file_get_contents($cachefile)) {
    $res_filter = unserialize($res_filter_cont);
}

$field = array('hole' => '', 'msg' => '', 'name' => '', 'mail' => '', 'date' => '', 'id' => '', 'beid' => '', 'belv' => '');
$match = array('on' => '', 'off' => '');
$method = array('and' => '', 'or' => '', 'just' => '', 'regex' => '', 'similar' => '');

$field[$res_filter['field']]   = ' selected';
$match[$res_filter['match']]   = ' selected';
$method[$res_filter['method']] = ' selected';

/**
 * �����t�H�[����\��
 */
P2Util::header_nocache();
echo $_conf['doctype'];
echo <<<EOF
<html>
<head>
<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
<title>p2 - �ړ�����</title>
</head>
<body{$_conf['k_colors']}>
<p>{$ttitle_back}</p>
<hr>
<form id="header" method="get" action="{$_conf['read_php']}" accept-charset="{$_conf['accept_charset']}">
{$_conf['detect_hint_input_ht']}
<input type="hidden" name="host" value="{$host}">
<input type="hidden" name="bbs" value="{$bbs}">
<input type="hidden" name="key" value="{$key}">
<input type="hidden" name="ls" value="all">
<input type="hidden" name="offline" value="1">
<div>
<input id="word" name="word"><br>
<input type="submit" name="s1" value="����">
</div>
<hr>
<div>
������߼�݁F<br>
<select id="field" name="field">
<option value="hole"{$field['hole']}>�S��</option>
<option value="msg"{$field['msg']}>ү����</option>
<option value="name"{$field['name']}>���O</option>
<option value="mail"{$field['mail']}>Ұ�</option>
<option value="date"{$field['date']}>���t</option>
<option value="id"{$field['id']}>ID</option>
<!-- <option value="belv"{$field['belv']}>�߲��</option> -->
</select>��<select id="method" name="method">
<option value="or"{$method['or']}>�����ꂩ</option>
<option value="and"{$method['and']}>���ׂ�</option>
<option value="just"{$method['just']}>���̂܂�</option>
<option value="regex"{$method['regex']}>���K�\��</option>
</select>��<select id="match" name="match">
<option value="on"{$match['on']}>�܂�</option>
<option value="off"{$match['off']}>�܂܂Ȃ�</option>
</select><br>
<input type="submit" name="s2" value="����">
</div>
{$_conf['k_input_ht']}
</form>
<hr>{$_conf['k_to_index_ht']}
</body>
</html>
EOF;

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
