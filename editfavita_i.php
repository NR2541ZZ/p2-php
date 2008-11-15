<?php
/*
    p2 -  ���C�ɓ���ҏW
    iPhone�p�ɕύX
*/

require_once './conf/conf.inc.php';
require_once './iphone/conf.inc.php';

require_once P2_LIB_DIR . '/filectl.class.php';

require_once P2_LIB_DIR . '/UA.php';

$_login->authorize(); // ���[�U�F��

// {{{ ����ȑO����

// ���C�ɔ̒ǉ��E�폜�A���ёւ�
if (isset($_GET['setfavita']) or isset($_POST['setfavita']) or isset($_POST['submit_listfavita'])) {

    if (!isset($_REQUEST['csrfid']) or $_REQUEST['csrfid'] != P2Util::getCsrfId()) {
        p2die('�s���ȃN�G���[�ł��iCSRF�΍�j');
    }
    
    require_once P2_LIB_DIR . '/setfavita.inc.php';
    setFavIta();
}
// ���C�ɔ̃z�X�g�𓯊�
if (isset($_GET['syncfavita']) or isset($_POST['syncfavita'])) {
    require_once P2_LIB_DIR . '/BbsMap.class.php';
    BbsMap::syncBrd($_conf['favita_path']);
}

// }}}

// ���ёւ���JavaScript�g�������H
if ($_conf['ktai'] or UA::isNetFront() or !empty($_POST['sortNoJs']) || !empty($_GET['sortNoJs']) or isset($_GET['setfavita'])) {
    $sortNoJs = true;
} else {
    $sortNoJs = false;
}

$csrfid = P2Util::getCsrfId();
//================================================================
// �w�b�_HTML�\��
//================================================================
P2Util::headerNoCache();
P2View::printDoctypeTag();
?>
<html lang="ja">
<head>
<?php
P2View::printExtraHeadersHtml();
echo <<<EOP
    <style type="text/css" media="screen">@import "./iui/iui.css";</style>
    <title>p2 - ���C�ɔ̕��ёւ�</title>
<script type="text/javascript" src="js/yui/YAHOO.js" ></script>
<script type="text/javascript" src="js/yui/log.js" ></script>
<script type="text/javascript" src="js/yui/event.js" ></script>
<script type="text/javascript" src="js/yui/dom.js"></script>
<script type="text/javascript"> 
<!-- 
window.onload = function() { 
setTimeout(scrollTo, 100, 0, 1); 
} 
// --> 
</script> 
<script type="text/javascript" src="js/yui/dragdrop.js" ></script>
<script type="text/javascript" src="js/yui/ygDDOnTop.js" ></script>
<script type="text/javascript" src="js/yui/ygDDSwap.js" ></script>
<script type="text/javascript" src="js/yui/ygDDMy.js" ></script>
<script type="text/javascript" src="js/yui/ygDDMy2.js" ></script>
<script type="text/javascript" src="js/yui/ygDDList.js" ></script>
<script type="text/javascript" src="js/yui/ygDDPlayer.js" ></script>
EOP;

//include_once './style/style_css.inc';
//include_once './style/editfavita_css.inc';

echo <<<EOP
</head>
<body>
<div class="toolbar">
<h1>���C�ɔҏW</h1>
<a class="button" id="backbutton" href="iphone.php">TOP</a>
</div>
EOP;

P2Util::printInfoHtml();

//=====================================================================
// ���C������HTML�\��
//=====================================================================

// ���C�ɔ����擾
FileCtl::make_datafile($_conf['favita_path'], $_conf['favita_perm']);
$lines = file($_conf['favita_path']);
$okini_itas = _getOkiniItasFromLines($lines);


if (!$sortNoJs and !empty($lines)) {
?>
<script type="text/javascript">
	// var gLogger = new ygLogger("test_noimpl.php");
	var dd = []
	var gVarObj = new Object(); // ���C�ɔ̃f�[�^���X�g
    
	function dragDropInit() {
		var i = 0;
		var id = '';
        for (j = 0; j < <?php echo count($lines); ?>; ++j) {
            id = "li" + j;
			dd[i++] = new ygDDList(id);
		}
        <?php
        foreach ($okini_itas as $k => $v) {
            echo "gVarObj['{$k}'] = '{$v['encValue']}';\n";
        }
        ?>
		dd[i++] = new ygDDListBoundary("hidden1");

	    YAHOO.util.DDM.mode = 0; // 0:Point, :Intersect
	}

	YAHOO.util.Event.addListener(window, "load", dragDropInit);
	// YAHOO.util.DDM.useCache = false;


function makeOptionList()
{
    var values = [];
	var elem = document.getElementById('italist');
    var childs = elem.childNodes;
    for (var i = 0; i < childs.length; i++) {
        if (childs[i].tagName == 'LI' && childs[i].style.visibility != 'hidden' && childs[i].style.display != 'none') {
            values[i] = gVarObj[childs[i].id];
            //alert(values[i]);
        }
    }
    
    var val = "";
    for (var j = 0; j < values.length; j++) {
        if (val > "") {
            val += ",";
        }
        if (values[j] > "") {
			val += values[j];
		}
    }
    //alert(val);
    
    return val;
}

function submitApply()
{
    document.form['list'].value = makeOptionList();
    //alert(document.form['list'].value);
    //document.form.submit();
}
</script>
<?php
}


    $menu_href = 'menu_i.php?view=favita&amp;nr=1' . $_conf['k_at_a'] . '&amp;nt=' . time();
    $onclick = '';
}



// {{{ ���C�ɔǉ��t�H�[�� HTML�\��

$sortNoJsInputHtml = $sortNoJs ? '<input type="hidden" id="sortNoJs" name="sortNoJs" value="1">' : '';

echo  <<<EOFORM
<ul><li class="group">���C�ɔ̐V�K�ǉ�</li></ul>
<div class="panel">
<form method="POST" action="{$_SERVER['SCRIPT_NAME']}" accept-charset="{$_conf['accept_charset']}" target="_self">
    <input type="hidden" name="detect_hint" value="����">
    {$_conf['k_input_ht']}
    ��URL: <input type="text" id="url" name="url" value="http://" size="25"><br>
    ��: <input type="text" id="itaj" name="itaj" value="" size="16"><br>
    <input type="hidden" id="setfavita" name="setfavita" value="1">
    {$sortNoJsInputHtml}
    <input type="submit" name="submit" value="�V�K�ǉ�">
    <div>���͗�<br>��URL: http://news19.2ch.net/newsplus/<br>��: �j���[�X����+�j</div>
</form>
</div>
EOFORM;

// }}}


// JavaScript�\�[�g�p
if (!$sortNoJs) {

    if ($lines) {
        $script_enable_html = <<<EOP
<ul><li class="group">���C�ɔ̕��ёւ�</li></ul> �i<a href="{$_SERVER['SCRIPT_NAME']}?sortNoJs=1">��JavaScript�ł͂�����</a>�j<br>
�i�h���b�O�A���h�h���b�v����ŕ��ёւ����ł��܂��B�A�C�e����g�O��D&D����ƍ폜�ł��܂��B�u�ύX��K�p����v�{�^���Ō��肵�܂��j
<div class="itas">
<form id="form" name="form" method="post" action="{$_SERVER['SCRIPT_NAME']}" accept-charset="{$_conf['accept_charset']}" target="_self">
        
<table border="0">
<tr>
<td class="italist" id="ddrange">

<ul id="italist">
<li id="hidden6" class="sortList" style="visibility:hidden;">Hidden</li>
EOP;
        if (is_array($okini_itas)) {
            foreach ($okini_itas as $k => $v) {
                $script_enable_html .= '<li id="' . $k . '" class="sortList"><b style="width:120pt;">' . hs($v['itaj']) . '</b> ' . hs($v['host']) . '/' . hs($v['bbs']) . '</li>';
            }
        }
    }

    $script_enable_html .= <<<EOP
<li id="hidden1" style="visibility:hidden;">Hidden</li></ul>

</td>
</tr>
</table>

<input type="hidden" name="list">
<input type="hidden" name="csrfid" value="{$csrfid}">
<input type="submit" value="���ɖ߂�">
<input type="submit" name="submit_listfavita" value="�ύX��K�p����" onClick="submitApply();">

</div>
</form>
EOP;

    $regex = array('/"/', '/\n/');
    $replace = array('\"', null);
    $out = preg_replace($regex, $replace, $script_enable_html);

    echo <<<EOP
<script language="Javascript"> <!-- 
document.write("{$out}"); 
//--></script>
EOP;

}


// {{{ ��JavaScript�̃\�[�g HTML��\��

if ($lines) {

    // JavaScript�\�[�g�Ȃ� <noscript>
    if (!$sortNoJs) {
        ?><noscript><?php
    }
    
    // PC�iNetFront�ȊO�j�Ȃ�
    if (!$_conf['ktai'] && !UA::isNetFront()) {
        $linkDD = '�i<a href="' . $_SERVER['SCRIPT_NAME'] . '">JavaScript�ł͂�����</a>�j';
    } else {
        $linkDD = '';
    }
    
    echo"<ul><li class=\"group\">���C�ɔ̕��ёւ�</li></ul> {$linkDD}";
    echo '<div class="panel"><table width="100%">';
    foreach ($lines as $l) {
        if (preg_match('/^\t?(.+?)\t(.+?)\t(.+?)$/', rtrim($l), $matches)) {
            $itaj       = rtrim($matches[3]);
            $itaj_en    = rawurlencode(base64_encode($itaj));
            $host       = $matches[1];
            $bbs        = $matches[2];
            $itaj_hs    = htmlspecialchars($itaj, ENT_QUOTES);
            $itaj_q     = '&amp;itaj_en=' . $itaj_en;
            echo <<<EOP
            <tr>
            <th><a href="{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$_conf['k_at_a']}" title="{$host}/{$bbs}">{$itaj_hs}</a></th>
            <td>[ <a class="te" href="{$_SERVER['SCRIPT_NAME']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=top{$_conf['k_at_a']}" title="��ԏ�Ɉړ�">��</a></td>
            <td><a class="te" href="{$_SERVER['SCRIPT_NAME']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=up{$_conf['k_at_a']}" title="���Ɉړ�">��</a></td>
            <td><a class="te" href="{$_SERVER['SCRIPT_NAME']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=down{$_conf['k_at_a']}" title="����Ɉړ�">��</a></td>
            <td><a class="te" href="{$_SERVER['SCRIPT_NAME']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=bottom{$_conf['k_at_a']}" title="��ԉ��Ɉړ�">��</a> ]</td>
            <td width="10px"><a href="{$_SERVER['SCRIPT_NAME']}?host={$host}&amp;bbs={$bbs}&amp;setfavita=0{$_conf['k_at_a']}">��</a></td>
            </tr>
EOP;
        }
    }
    ?></table><?php
    
    // JavaScript�\�[�g�Ȃ� <noscript>
    if (!$sortNoJs) {
        ?></noscript><?php
    }
}

// }}}


/*
// PC�p ���C�ɔ����t�H�[�� HTML�\��
if (!$_conf['ktai']) {
    echo '<hr>';

    echo <<<EOFORM
<form method="POST" action="{$_SERVER['SCRIPT_NAME']}" target="_self">
    <p>
        {$_conf['k_input_ht']}
        <input type="hidden" id="syncfavita" name="syncfavita" value="1">
        <input type="submit" name="submit" value="���X�g�ƃz�X�g�𓯊�����">�i�̃z�X�g�ړ]�ɑΉ����܂��B�ʏ�͎����œ��������̂ŁA���̑���͓��ɕK�v����܂���j
    </p>
</form>\n
EOFORM;
}
*/

// �t�b�^HTML��\������

echo <<<EOP
<br>[<a href="{$menu_href}"{$onclick}>���C�ɓ���ꗗ��\��</a>]</div>
</body></html>
EOP;


exit;


//====================================================================
// �֐��i���̃t�@�C�����ł̂ݗ��p�j
//====================================================================
/**
 * ���C�Ƀ��X�g����A���C�ɔf�[�^���擾����
 *
 * @param   array  $lines
 * @return  array  assoc
 */
function _getOkiniItasFromLines($lines)
{
    $okini_itas = array();
    $i = 0;
    if (is_array($lines)) {
        foreach ($lines as $l) {
            if (preg_match("/^\t?(.+?)\t(.+?)\t(.+?)$/", rtrim($l), $matches)) {
                $id = "li{$i}";
                $okini_itas[$id]['itaj']       = $itaj = rtrim($matches[3]);
                $okini_itas[$id]['itaj_en']    = $itaj_en = base64_encode($itaj);
                $okini_itas[$id]['host']       = $host = $matches[1];
                $okini_itas[$id]['bbs']        = $bbs = $matches[2];
                // rawurlencode ���Ă���̂́A�f���~�^(,@)���G�X�P�[�v���邽��
                $okini_itas[$id]['encValue']      = rawurlencode($host) . "@" . rawurlencode($bbs) . "@" . rawurlencode($itaj_en);

                $i++;
            }
        }
    }
    return $okini_itas;
}
