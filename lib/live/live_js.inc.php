<?php
/*
	+live - �I�[�g�X�N���[��&�I�[�g�����[�h ./live_header.inc.php ���ǂݍ��܂��
*/

echo <<<xmht
<script type="text/javascript">
<!--

// XMLHttpRequest
function getIndex(getFile) {
	xmlhttp = new XMLHttpRequest();
	if (xmlhttp) {
		xmlhttp.onreadystatechange = check;
		xmlhttp.open('GET', getFile, true);
		xmlhttp.send(null);
	}
}

function check() {
	if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
		document.getElementById("live_view").innerHTML = xmlhttp.responseText;
	}
}

// �I�[�g�X�N���[��
var speed = {$_conf['live.scroll_speed']}; // ���x�imax 1�j
var move = {$_conf['live.scroll_move']}; // ���炩����on-off�imax 1�j

var ascr;

function ascroll() {
	window.scrollBy(0, move); // �X�N���[������
	ascr = setTimeout("ascroll()", speed);
}

// �I�[�g�����[�h
var arel;

function areload() {
	arel = setInterval("getIndex('./read.php?host={$aThread->host}&bbs={$aThread->bbs}&key={$aThread->key}&live=1')", {$reload_time});
}

// �J�n
function startlive() {
	if (ascr) clearTimeout(ascr);
	if (arel) clearInterval(arel);
	getIndex('./read.php?host={$aThread->host}&bbs={$aThread->bbs}&key={$aThread->key}&live=1');
	ascroll();
	areload();
}

// ��~
function stoplive() {
	if (ascr) clearTimeout(ascr);
	if (arel) clearInterval(arel);
}

// -->
</script>\n
xmht;

?>