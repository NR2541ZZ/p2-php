<?php
/*
    p2 -  ���C�ɓ���ҏW
*/

include_once './conf/conf.inc.php';
require_once P2_LIBRARY_DIR . '/filectl.class.php';

$_login->authorize(); // ���[�U�F��

//================================================================
// ����ȑO�u����
//================================================================

// ���C�ɔ̒ǉ��E�폜�A���ёւ�
if (isset($_GET['setfavita']) or isset($_POST['setfavita']) or isset($_POST['submit_setfavita'])) {
    include_once (P2_LIBRARY_DIR . '/setfavita.inc.php');
    setFavIta();
}
// ���C�ɔ̃z�X�g�𓯊�
if (isset($_GET['syncfavita']) or isset($_POST['syncfavita'])) {
    include_once P2_LIBRARY_DIR . '/BbsMap.class.php';
    BbsMap::syncBrd($_conf['favita_path']);
}

// �v�����g�p�ϐ� ======================================================

// ���C�ɔǉ��t�H�[��
$add_favita_form_ht = <<<EOFORM
<form method="POST" action="{$_SERVER['PHP_SELF']}" accept-charset="{$_conf['accept_charset']}" target="_self">
    <input type="hidden" name="detect_hint" value="����">
    <p>
        {$_conf['k_input_ht']}
        ��URL: <input type="text" id="url" name="url" value="http://" size="48">
        ��: <input type="text" id="itaj" name="itaj" value="" size="16">
        <input type="hidden" id="setfavita" name="setfavita" value="1">
        <input type="submit" name="submit" value="�V�K�ǉ�">
    </p>
</form>\n
EOFORM;

// ���C�ɔ����t�H�[��
$sync_favita_form_ht = <<<EOFORM
<form method="POST" action="{$_SERVER['PHP_SELF']}" target="_self">
    <p>
        {$_conf['k_input_ht']}
        <input type="hidden" id="syncfavita" name="syncfavita" value="1">
        <input type="submit" name="submit" value="���X�g�ƃz�X�g�𓯊�����">�i�̃z�X�g�ړ]�ɑΉ����܂��j
    </p>
</form>\n
EOFORM;

//================================================================
// �w�b�_
//================================================================
P2Util::header_nocache();
P2Util::header_content_type();
if ($_conf['doctype']) { echo $_conf['doctype']; }
echo <<<EOP
<html lang="ja">
<head>
    {$_conf['meta_charset_ht']}
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>p2 - ���C�ɔ̕��ёւ�</title>
<script type="text/javascript" src="js/yui/YAHOO.js" ></script>
<script type="text/javascript" src="js/yui/log.js" ></script>
<script type="text/javascript" src="js/yui/event.js" ></script>
<script type="text/javascript" src="js/yui/dom.js"></script>

<script type="text/javascript" src="js/yui/dragdrop.js" ></script>
		<script type="text/javascript" src="js/yui/ygDDOnTop.js" ></script>
		<script type="text/javascript" src="js/yui/ygDDSwap.js" ></script>
		<script type="text/javascript" src="js/yui/ygDDMy.js" ></script>
		<script type="text/javascript" src="js/yui/ygDDMy2.js" ></script>
		<script type="text/javascript" src="js/yui/ygDDList.js" ></script>
		<script type="text/javascript" src="js/yui/ygDDPlayer.js" ></script>
EOP;

@include("./style/style_css.inc");
@include("./style/editfavita_css.inc");

echo '</head><body>'."\n";

echo $_info_msg_ht;
$_info_msg_ht = '';

//================================================================
// ���C������HTML�\��
//================================================================

//================================================================
// ���C�ɔ�
//================================================================

// favita�t�@�C�����Ȃ���ΐ���
FileCtl::make_datafile($_conf['favita_path'], $_conf['favita_perm']);
// favita�ǂݍ���
$lines = file($_conf['favita_path']);
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
            $okini_itas[$id]['itaj_view']  = htmlspecialchars($itaj);
            $okini_itas[$id]['itaj_ht']    = "&amp;itaj_en=" . $itaj_en;
            $okini_itas[$id]['value']      = $host . "@" . $bbs . "@" . $itaj_en;

            $i++;
        }
    }
}

// PC�p
if (empty($_conf['ktai']) and !empty($lines)) {
?>
<script type="text/javascript">
	// var gLogger = new ygLogger("test_noimpl.php");
	var dd = []
	var gVarObj = new Object();
    
	function dragDropInit() {
		var i = 0;
		var id = '';
        for (j = 0; j < <?php echo count($lines); ?>; ++j) {
            id = "li" + j;
			dd[i++] = new ygDDList(id);
            //gVarObj[id] = '<?php echo $host . "@" . $bbs . "@" . $itaj_en; ?>';
		}
        <?php
        foreach ($okini_itas as $k => $v) {
            echo "gVarObj['{$k}'] = '{$v['host']}@{$v['bbs']}@{$v['itaj_en']}';";
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

// PC�p
if (empty($_conf['ktai'])) {
    $onclick = " onClick='if (parent.menu) { parent.menu.location.href=\"{$_conf['menu_php']}?nr=1\"; }'";
    $m_php = $_SERVER['PHP_SELF'];
    
// �g�їp
} else {
    $onclick = '';
    $m_php = 'menu_k.php?view=favita&amp;nr=1' . $_conf['k_at_a'] . '&amp;nt=' . time();
}

echo <<<EOP
<div><b>���C�ɔ̕ҏW</b> [<a href="{$m_php}"{$onclick}>���j���[���X�V</a>]</div>
EOP;

echo $add_favita_form_ht;
echo '<hr>';


// PC�iNetFront�����O�j
if (empty($_conf['ktai']) && !P2Util::isNetFront()) {

    if ($lines) {
        $script_enable_html .= <<<EOP
���C�ɔ̕��ёւ��i�h���b�O�A���h�h���b�v�j
<div class="itas">
<form id="form" name="form" method="post" action="{$_SERVER['PHP_SELF']}" accept-charset="{$_conf['accept_charset']}" target="_self">
        
<table border="0">
<tr>
<td class="italist" id="ddrange">

<ul id="italist"><li id="hidden6" class="sortList" style="visibility:hidden;">Hidden</li>
EOP;
        if (is_array($okini_itas)) {
            foreach ($okini_itas as $k => $v) {
                $script_enable_html .= '<li id="' . $k . '" class="sortList">' . $v['itaj_view'] . '</li>';
            }
        }

    }

    $script_enable_html .= <<<EOP
<li id="hidden1" style="visibility:hidden;">Hidden</li></ul>

</td>
</tr>
</table>

<input type="hidden" name="list">

<input type="submit" value="���ɖ߂�">
<input type="submit" name="submit_setfavita" value="�ύX��K�p����" onClick="submitApply();">

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

//================================================================
// NOSCRIPT����HTML�\��
//================================================================
if ($lines) {
    // PC�iNetFront�����O�j
    if (empty($_conf['ktai']) && !P2Util::isNetFront()) {
        echo '<noscript>';
    }
    echo '���C�ɔ̕��ёւ�';
    echo '<table>';
    
    foreach ($lines as $l) {
        if (preg_match('/^\t?(.+?)\t(.+?)\t(.+?)$/', rtrim($l), $matches)) {
            $itaj       = rtrim($matches[3]);
            $itaj_en    = rawurlencode(base64_encode($itaj));
            $host       = $matches[1];
            $bbs        = $matches[2];
            $itaj_view  = htmlspecialchars($itaj, ENT_QUOTES);
            $itaj_q     = '&amp;itaj_en=' . $itaj_en;
            echo <<<EOP
            <tr>
            <td><a href="{$_conf['subject_php']}?host={$host}&amp;bbs={$bbs}{$_conf['k_at_a']}">{$itaj_view}</a></td>
            <td>[ <a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=top{$_conf['k_at_a']}" title="��ԏ�Ɉړ�">��</a></td>
            <td><a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=up{$_conf['k_at_a']}" title="���Ɉړ�">��</a></td>
            <td><a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=down{$_conf['k_at_a']}" title="����Ɉړ�">��</a></td>
            <td><a class="te" href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}{$itaj_q}&amp;setfavita=bottom{$_conf['k_at_a']}" title="��ԉ��Ɉړ�">��</a> ]</td>
            <td>[<a href="{$_SERVER['PHP_SELF']}?host={$host}&amp;bbs={$bbs}&amp;setfavita=0{$_conf['k_at_a']}">�폜</a>]</td>
            </tr>
EOP;
        }
    }
    
    echo "</table>";
    // PC�iNetFront�����O�j
    if (empty($_conf['ktai']) && !P2Util::isNetFront()) {
        echo '</noscript>';
    }
}

// PC
if (empty($_conf['ktai'])) {
    echo '<hr>';
    echo $sync_favita_form_ht;
}

//================================================================
// �t�b�^HTML�\��
//================================================================
if ($_conf['ktai']) {
    echo '<hr>' . $_conf['k_to_index_ht'];
}

echo '</body></html>';

?>
