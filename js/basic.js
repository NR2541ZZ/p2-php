/* p2 - ��{JavaScript�t�@�C�� */

// �T�u�E�B���h�E���|�b�v�A�b�v����
// @return  true
function openSubWin(inUrl, inWidth, inHeight, boolS, boolR)
{
	var proparty3rd = "width=" + inWidth + ",height=" + inHeight + ",scrollbars=" + boolS + ",resizable=1";
	SubWin = window.open(inUrl,"",proparty3rd);
	if (boolR == 1) {
		SubWin.resizeTo(inWidth,inHeight);
	}
	SubWin.focus();
	return true;
}

// �t���[������HTML�h�L�������g�̃^�C�g�����AWindow(top)�^�C�g���ɃZ�b�g����
// @return  true|null|false
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

// DOM�I�u�W�F�N�g���擾����
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

// XMLHttpRequest �I�u�W�F�N�g���擾����
// @return  object
function getXmlHttp()
{
	var xmlHttpObj = null ;
	try {
		xmlHttpObj = new ActiveXObject("Msxml2.XMLHTTP") ; // Mozilla�p
	} catch (e) {
		try {
			xmlHttpObj = new ActiveXObject("Microsoft.XMLHTTP") ; // IE�p
		} catch (oc) {
			xmlHttpObj = null ;
		}
	}
	if (!xmlHttpObj && typeof XMLHttpRequest != "undefined") {
		xmlHttpObj = new XMLHttpRequest(); // ��
	}
	return xmlHttpObj;
}

// xmlHttpObj ��url��n���āA���ʃe�L�X�g���擾����B
// @param nc string|false|void �w�肷��Ƃ�����L�[�Ƃ����L���b�V������̂��߂̃_�~�[�N�G���[���ǉ������
function getResponseTextHttp(xmlHttpObj, url, nc, async, func)
{
	if (nc) {
		var now = new Date();
		url = url + '&' + nc + '=' + now.getTime(); // �L���b�V�����p
	}
	xmlHttpObj.open('GET', url, async);
	xmlHttpObj.send(null);
	
	if (async) {
		if (func) {
			if (isSafari()) {
				xmlHttpObj.onload = function(){ func(xmlHttpObj); }
			} else {
				xmlHttpObj.onreadystatechange = function() {
					if (xmlHttpObj.readyState == 4 && xmlHttpObj.status == 200) {
						func(xmlHttpObj);
					}
				}
			}
			return;
		}
		return;
	} else {
		if (xmlHttpObj.readyState == 4) {
			if (xmlHttpObj.status == 200) {
				return xmlHttpObj.responseText.replace(/^<\?xml .+?\?>\n?/, '');
			} else {
				// rt = '<em>HTTP Error:<br />' + req.status + ' ' + req.statusText + '</em>';
			}
		}
	}
	return false;
}

// Browser isSafari?
// @return  boolean
function isSafari(getVersion) {
	var ua = navigator.userAgent;
	if (ua.indexOf('Safari') != -1 || ua.indexOf('AppleWebKit') != -1 || ua.indexOf('Konqueror') != -1) {
		if (getVersion) {
			if (ua.match("Version/([0-9.]+)")) {
				return RegExp.$1;
			}
		}
		return true;
	}
	return false;
}

// Browser isIPhoneGroup?
// @return  boolean
function isIPhoneGroup()
{
	var ua = navigator.userAgent;
	if (ua.indexOf('iPhone') != -1 || ua.indexOf('iPod') != -1) {
		return true;
	}
	return false;
}

// Browser isChrome?
// @return  boolean
function isChrome() {
	return (navigator.userAgent.indexOf('Chrome') != -1);
}

/**
 * @return  object
 */
function getDocumentBodyIE()
{
	return (document.compatMode=='CSS1Compat') ? document.documentElement : document.body;
}

// @return  void
function addLoadEvent(func) {
	var oldonload = window.onload;
	
	if (typeof window.onload != 'function') {
		window.onload = func;
	} else {
		window.onload = function() {
			oldonload();
			func();
		}
	}
}
