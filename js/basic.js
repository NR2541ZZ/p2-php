/* p2 - ��{JavaScript�t�@�C�� */

// �T�u�E�B���h�E���|�b�v�A�b�v����
function OpenSubWin(inUrl, inWidth, inHeight, boolS, boolR){
	var proparty3rd = "width=" + inWidth + ",height=" + inHeight + ",scrollbars=" + boolS + ",resizable=1";
	SubWin = window.open(inUrl,"",proparty3rd);
	if (boolR == 1){
		SubWin.resizeTo(inWidth,inHeight);
	}
	SubWin.focus();
	return false;
}

// HTML�h�L�������g�̃^�C�g�����Z�b�g����֐�
function setWinTitle()
{
	if (top != self) { top.document.title = self.document.title; }
}

// p2GetElementById -- DOM�I�u�W�F�N�g���擾
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

// XMLHttpRequest �I�u�W�F�N�g���擾
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
