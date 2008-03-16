/* p2 - ��{JavaScript�t�@�C�� */

////
// �T�u�E�B���h�E���|�b�v�A�b�v����
// @return  false
//
function OpenSubWin(inUrl, inWidth, inHeight, boolS, boolR)
{
	var proparty3rd = "width=" + inWidth + ",height=" + inHeight + ",scrollbars=" + boolS + ",resizable=1";
	SubWin = window.open(inUrl,"",proparty3rd);
	if (boolR == 1) {
		SubWin.resizeTo(inWidth,inHeight);
	}
	SubWin.focus();
	return false;
}

////
// �t���[������HTML�h�L�������g�̃^�C�g�����AWindow(top)�^�C�g���ɃZ�b�g����
// @return  true|null|false
//
function setWinTitle()
{
	if (top == self) {
		return null;
	}
	try {
		top.document.title = self.document.title;
	} catch (e) {
		return false;
	}
	return true;
}

////
// DOM�I�u�W�F�N�g���擾����
//
function p2GetElementById(id)
{
	if (document.getElementById) {
		return (document.getElementById(id));
	} else if (document.all) {
		return (document.all[id]);
	} else if (document.layers) {
		return (document.layers[id]);
	} else {
		return false;
	}
}

////
// XMLHttpRequest �I�u�W�F�N�g���擾����
// @return  object
//
function getXmlHttp()
{
	var objHTTP = null ;
	try {
		objHTTP = new ActiveXObject("Msxml2.XMLHTTP") ; // Mozilla�p
	} catch (e) {
		try {
			objHTTP = new ActiveXObject("Microsoft.XMLHTTP") ; // IE�p
		} catch (oc) {
			objHTTP = null ;
		}
	}
	if (!objHTTP && typeof XMLHttpRequest != "undefined") {
		objHTTP = new XMLHttpRequest(); // ��
	}
	return objHTTP
}

////
// objHTTP ��url��n���āA���ʃe�L�X�g���擾����
//
// @param nc string ������L�[�Ƃ����L���b�V������̂��߂̃N�G���[���ǉ������
//
function getResponseTextHttp(objHTTP, url, nc)
{
	if (nc) {
		var now = new Date();
		url = url + '&' + nc + '=' + now.getTime(); // �L���b�V�����p
	}
	objHTTP.open('GET', url, false);
	objHTTP.send(null);

	if (objHTTP.readyState == 4) {
		if (objHTTP.status == 200) {
			return objHTTP.responseText;
		} else {
			// rt = '<em>HTTP Error:<br />' + req.status + ' ' + req.statusText + '</em>';
		}
	}

	return '';
}

////
// isSafari?
// @return  boolean
//
function isSafari() {
	var ua = navigator.userAgent;
	if (ua.indexOf("Safari") != -1 || ua.indexOf("AppleWebKit") != -1 || ua.indexOf("Konqueror") != -1) {
		return true;
	} else {
		return false;
	}
}

// prototype.js 1.4.0 : string.js : escapeHTML ���������C�i�[��
// IE6 �W�����[�h�΍�ŉ��s�R�[�h�� CR+LF �ɓ���
//  Prototype JavaScript framework, version 1.4.0
//  (c) 2005 Sam Stephenson <sam@conio.net>
//
//  Prototype is freely distributable under the terms of an MIT-style license.
//  For details, see the Prototype web site: http://prototype.conio.net/
//
function escapeHTML(cont)
{
	return document.createElement('div').appendChild(document.createTextNode(cont)).parentNode.innerHTML;
}
