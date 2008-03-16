<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=Shift_JIS">
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    <title>tGrep<?php if (strlen($htm['query']) > 0) { echo ' - ', $htm['query']; } ?></title>
    <?php echo $htm['mobile_css']; ?>
</head>
<body>

<h1 id="top" name="top">tGrep for rep2</h1>

<!-- Search Form -->
<form action="<?php echo $htm['php_self']; ?>" method="get">
<?php echo $_conf['detect_hint_input_xht']; ?>
<input name="Q" <?php echo $htm['search_attr']; ?> />
<?php echo $_conf['k_input_xht']; ?>
<input type="submit" value="����">
</form>
<hr>

<?php if (!$query) { ?>
<?php
if ($_conf['expack.tgrep.quicksearch']) {
    include_once P2EX_LIBRARY_DIR . '/tgrep/menu_quick.inc.php';
    echo "<hr>\n";
}
if ($_conf['expack.tgrep.recent_num'] > 0) {
    include_once P2EX_LIBRARY_DIR . '/tgrep/menu_recent.inc.php';
    echo "<hr>\n";
}
?>
<!-- HowTo -->
<h4>�d�l</h4>
<ul>
<li>��ܰ�ނͽ�߰���؂��3�܂Ŏw��ł�����ׂĂ��܂ނ��̂����o����܂��</li>
<li>2�ڈȍ~�̷�ܰ�ނœ���&quot;-&quot;������Ƥ������܂܂Ȃ����̂����o����܂��</li>
<li>&quot; �܂��� &#39; �ň͂܂ꂽ�����ͽ�߰��������Ă��Ă���̷�ܰ�ނƂ��Ĉ����܂��</li>
<li>��ܰ�ނ̑S�p���p��啶���������͖�������܂��</li>
<li>�ް��ް��̍X�V�p�x��3���Ԃ�1��Ťڽ���������������͍X�V���_�ł̒l�ł��</li>
</uL>
<?php } ?>
<?php if ($errors) { ?>
<!-- Errors -->
<h4>�װ</h4>
<ul><?php foreach ($errors as $error) { ?><li><?php echo htmlspecialchars($error, ENT_QUOTES); ?></li><?php } ?></ul>
<?php } ?>

<?php if (!$errors && $profile) { ?>
<!-- Result and Filter -->
<p>
<?php if ($htm['category'] && isset($profile['categories'][$htm['category']])) { ?>
<b><?php echo htmlspecialchars($profile['categories'][$htm['category']]->name, ENT_QUOTES); ?></b>����<b><?php echo $htm['query']; ?></b>������:<?php echo $htm['hits']; ?>hit!(all:<?php echo $htm['allhits']; ?>)
<?php } else { ?>
<b><?php echo $htm['query']; ?></b>�Ō���:<?php echo $htm['hits']; ?>hit!
<?php } ?>
</p>
<form action="<?php echo $htm['php_self']; ?>" method="get">
<input type="hidden" name="Q" value="<?php echo $htm['query']; ?>">
<select name="C">
<option value="">�ú�؂�I��</option>
<?php foreach ($profile['categories'] as $c) { ?><option value="<?php echo $c->id; ?>"<?php if ($c->id == $htm['category']) { echo ' selected'; } ?>><?php echo mb_convert_kana(htmlspecialchars($c->name, ENT_QUOTES), 'rnsk'); ?> (<?php echo $c->hits; ?>)</option><?php } ?>
</select>
<input type="submit" value="�i��">
</form>
<hr>
<?php } ?>

<?php if ($threads) { ?>
<!-- ThreadList and Pager -->
<div><a href="#bottom" <?php echo $_conf['accesskey']; ?>="8" align="right" title="����">8.��</a></div>
<?php
include_once P2_LIBRARY_DIR . '/thread.class.php';
foreach ($threads as $o => $t) {
    $new = '';
    $turl = sprintf('%s?host=%s&amp;bbs=%s&amp;key=%d', $_conf['read_php'], $t->host, $t->bbs, $t->tkey);
    $burl = sprintf('%s?host=%s&amp;bbs=%s&amp;itaj_en=%s&amp;word=%s', $_conf['subject_php'], $t->host, $t->bbs, urlencode(base64_encode($t->ita)), $htm['query_en']);
    $aThread = new Thread;
    $aThread->setThreadPathInfo($t->host, $t->bbs, $t->tkey);
    if ($aThread->getThreadInfoFromIdx() && $aThread->isKitoku()) {
        $rnum = max($t->resnum, $aThread->readnum);
        $nnum = max(0, $rnum - $aThread->readnum);
    } else {
        $rnum = $t->resnum;
        $nnum = '';
    }
    if (!empty($_conf['k_save_packet'])) {
        $ttitle = mb_convert_kana($t->title, 'rnsk');
        $itaj = mb_convert_kana($t->ita, 'rnsk');
    } else {
        $ttitle = $t->title;
        $itaj = $t->ita;
    }
?>
<p><?php echo $o; ?>.<a href="<?php echo $turl; ?>"><?php echo $ttitle; ?></a><br>
<small><?php echo date('y/m/d ', $t->tkey); ?><a href="<?php echo $burl; ?>"><?php echo $itaj; ?>(<?php echo $profile['boards'][$t->bid]->hits; ?>)</a></small></p>
<?php } ?>
<div><a href="#top" <?php echo $_conf['accesskey']; ?>="2" align="right" title="���">2.��</a></div>
<?php if ($htm['pager']) { ?>
<hr>
<div><?php echo $htm['pager']; ?></div>
<?php } ?>
<?php } ?>
<hr>
<p id="bottom" name="bottom">
<a <?php echo $_conf['accesskey']; ?>="0" href="index.php">0.TOP</a>
<?php if ($query) { ?>
<a <?php echo $_conf['accesskey']; ?>="5" href="tgrepc.php">5.tGrep</a>
<?php if ($_conf['expack.tgrep.quicksearch']) { ?>
<a <?php echo $_conf['accesskey']; ?>="9" href="tgrepctl.php?file=quick&amp;query=<?php echo $htm['query_en']; ?>">9.<?php echo $htm['query']; ?>���ꔭ�����ɒǉ�</a>
<?php } ?>
<?php } ?>
</p>
</body>
</html>
