/* p2 - �������݃t�H�[���pJavaScript */

////
// hukkatuPostForm
//
function hukkatuPostForm(host, bbs, key) {
	var chost = getCookie('post_host');
	var cbbs = getCookie('post_bbs');
	var ckey = getCookie('post_key');
	if (host == chost && bbs == cbbs && key == ckey) {
		var message = getCookie('post_msg');
		if (!message) {
			return;
		}
		var obj = document.getElementById('MESSAGE');
		obj.value = message;
	}
}

////
// getCookie
//
function getCookie(cn) {
	get_data = document.cookie;
	cv = new Array();
	gd = get_data.split(";");
	for (i in gd) {
		a = gd[i].split("=");
		a[0] = a[0].replace(" ","");
		cv[a[0]] = a[1];
	}
	if (cv[cn]) {
		return unescape(cv[cn]);
	} else {
		return "";
	}
}

////
// setCookie
//
function setCookie(cn, val, sec) {

	// �N�b�L�[�̗L������
	if (!sec) {
		sec = 1000*60*60*24*30; // 30����
	}

	val = escape(val);

	ex = new Date();
	ex = new Date(ex.getTime() + (1000 * sec));
	y = ex.getYear(); if (y < 1900) y += 1900;
	hms = ex.getHours() + ":" + ex.getMinutes() + ":" + ex.getSeconds();
	p = String(ex).split(" ");
	ex = p[0] + ", " + p[2] + "-" + p[1] + "-" + y + " " + hms + " GMT;";
	document.cookie = cn + "=" + val +"; expires=" + ex;
}

////
// getDataPostForm
//
function getDataPostForm(host, bbs, key)
{
	var from = document.getElementById('FROM').value;
	var mail = document.getElementById('mail').value;
	var message = document.getElementById('MESSAGE').value;
	var subject; if (subject = document.getElementById('subject')) { subject = subject.value; }
	var data = {'host':host, 'bbs':bbs, 'key':key, 'from':from, 'mail':mail, 'message':message};
	return data;
}

////
// �������݃t�H�[���̓��e�������ۑ�����
// @return  true|null
//
g_coming_auto_save_post_form = false;	// �A������}���̂��߂̓��쒆�t���O
g_timer_auto_save_post_form = null;

function autoSavePostForm(host, bbs, key)
{
	var timer_micro = 1.5 * 1000;	// �A�������}�����鎞�ԁi�}�C�N���b�j

	if (g_coming_auto_save_post_form) {
		if (g_timer_auto_save_post_form) {
			clearTimeout(g_timer_auto_save_post_form);
		}
	} else {
		var message = document.getElementById('MESSAGE').value;
		if (!message || message.length > 1900) {
			return null;
		}
		//comingAutoSavePostForm(host, bbs, key);
		g_coming_auto_save_post_form = true;
	}
	g_timer_auto_save_post_form = setTimeout("comingAutoSavePostForm('" + host + "', '" + bbs + "', '" + key + "')", timer_micro);

	return true;
}

////
// autoSavePostForm �̘A�������}�����Ȃ�����s���s��
//
// @return  void
//
function comingAutoSavePostForm(host, bbs, key)
{
	g_coming_auto_save_post_form = false;
	autoSavePostFormCookie(host, bbs, key);
}

////
// autoSavePostFormCookie
// @return  true|null
//
function autoSavePostFormCookie(host, bbs, key)
{
	var data = getDataPostForm(host, bbs, key);
	if (!data['message']) {
		return null
	}
	setCookie('post_msg', data['message']);
	setCookie('post_host', data['host']);
	setCookie('post_bbs', data['bbs']);
	setCookie('post_key', data['key']);
	//blinkStatusPostForm('save cokkie');
	return true;
}

/* ajax�͂�߂�cookie�𗘗p���邱�Ƃɂ���
////
// autoSavePostFormAjax
// @return  boolean|null
//
function autoSavePostFormAjax(host, bbs, key)
{
	var data = getDataPostForm(host, bbs, key);
	if (!data['message']) {
		return null
	}

	var postdata = 'hint=' + encodeURI('����') + '&cmd=auto_save_post_form';
	for (var k in data) {
		postdata += '&' + k + '=' + encodeURI(data[k]);
	}

	var objHTTP = getXmlHttp();
	if (!objHTTP) {
		return false;
	}

	var url = 'httpcmd.php';
	var now = new Date();
	//url = url + '&' + 'nc' + '=' + now.getTime(); // �L���b�V�����p
	objHTTP.open('POST', url, true);
	if (isSafari()) {
	    objHTTP.onload = function(){ checkResultAutoSavePostForm(objHTTP); }
	} else {
	    objHTTP.onreadystatechange = function() {
	        if (objHTTP.readyState == 4 && objHTTP.status == 200) {
	            checkResultAutoSavePostForm(objHTTP);
	        }
	    }
	}
	objHTTP.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
	objHTTP.send(postdata);

	return true;
}

////
// checkResultAutoSavePostForm
//
// @return  void
//
function checkResultAutoSavePostForm(objHTTP)
{
	var res = objHTTP.responseText.replace(/^<\?xml .+?\?>\n?/, '');
	blinkStatusPostForm(res);
}
*/

////
// blinkStatusPostForm
//
// @return  void
//
function blinkStatusPostForm(str)
{
	var timer_micro = 0.3*1000;
	setStatusPostForm(str);
	var timer_id = setTimeout("setStatusPostForm('')", timer_micro);
}

////
// setStatusPostForm
//
// @return  void
//
function setStatusPostForm(str)
{
	var status = document.getElementById('status_post_form');
	status.innerHTML = str;
}

////
// textarea�̍��������C�u���߂���
//
// @return  void
//
g_coming_adjust_textarea_rows = new Array();	// �A������}���̂��߂̓��쒆�t���O
g_adjust_textarea_objs = new Array();
g_adjust_textarea_orgs = new Array();
g_adjust_textarea_timers = new Array();
function adjustTextareaRows(obj, plus)
{
	var limit_rows = 40;		// ����\�ȍő�s��
	var timer_micro = 1 * 1000;	// �A�������}�����鎞�ԁi�}�C�N���b�j

	if (obj.rows > limit_rows) {
		return;
	}

	g_adjust_textarea_objs[obj.id] = obj;
	if (!g_adjust_textarea_orgs[obj.id]) {
		g_adjust_textarea_orgs[obj.id] = obj.rows;
	}
	if (g_coming_adjust_textarea_rows[obj.id]) {
		if (g_adjust_textarea_timers[obj.id]) {
			clearTimeout(g_adjust_textarea_timers[obj.id]);
		}
	} else {
		comingAdjustTextareaRows(obj.id, plus);
		g_coming_adjust_textarea_rows[obj.id] = true;
	}

	g_adjust_textarea_timers[obj.id] = setTimeout("comingAdjustTextareaRows('" + obj.id + "', " + plus + ")", timer_micro);
}

////
// doAdjustTextareaRows �����s����
//
// @return  void
//
function comingAdjustTextareaRows(id, plus)
{
	g_coming_adjust_textarea_rows[id] = false;
	var obj = g_adjust_textarea_objs[id];
	doAdjustTextareaRows(obj, plus);
	//g_adjust_textarea_objs[id] = null;
	//blinkStatusPostForm('adjust');
}

////
// textarea�̍��������C�u���߂���B�����������B
//
// @return  void
//
function doAdjustTextareaRows(obj, plus)
{
	var do_scroll = true;

	var brlen = null;
	if (obj.wrap) {
		if (obj.wrap == 'virtual' || obj.wrap == 'soft') {
			brlen = obj.cols;
		}
	}
	var my_len = countLines(obj.value, brlen);
	var my_rows = my_len + plus;
	var move_height = 0;
	var scroll_rows = 14;

	//blinkStatusPostForm(obj.rows + ' ' + my_rows);

	if (obj.rows < my_rows) {
		move_height = (my_rows - obj.rows) * scroll_rows;
	} else if (obj.rows > my_rows) {
		move_height = (my_rows - obj.rows) * -scroll_rows;
	}
	if (move_height != 0) {
		if (do_scroll && move_height < 0) {
			window.scrollBy(0, move_height);
		}
		if (my_rows > g_adjust_textarea_orgs[obj.id]) {
			obj.rows = my_rows;
		} else {
			obj.rows = g_adjust_textarea_orgs[obj.id];
		}
		if (do_scroll && move_height > 0) {
			window.scrollBy(0, move_height);
		}
	}
}

////
// \n �����s�Ƃ��čs���𐔂���
//
// @param integer brlen ���s���镶�����B���w��Ȃ當�����ŉ��s���Ȃ�
//
function countLines(str, brlen)
{
	var lines = str.split("\n");
	var count = lines.length;
	var aLen = 0;
	for (var i = 0; i < lines.length; i++) {
		aLen = jstrlen(lines[i]);
		if (brlen) {
			var adjust =  1.15; // �P��P�ʂ̐܂�Ԃ��ɑΉ����Ă��Ȃ��̂ŃA�o�E�g����
			if ((aLen * adjust) > brlen) {
				count = count + Math.floor((aLen * adjust) / brlen);
			}
		}
	}
	return count;
}

////
// ��������o�C�g���Ő�����
//
function jstrlen(str) {
	var len = 0;
	str = escape(str);
	for (var i = 0; i < str.length; i++, len++) {
		if (str.charAt(i) == "%") {
			if (str.charAt(++i) == "u") {
				i += 3;
				len++;
			}
			i++;
		}
	}
	return len;
}

////
// (�Ώۂ�disable�łȂ����) �t�H�[�J�X�����킹��
//
function setFocus(ID) {
	var obj = document.getElementById(ID);
	if (obj) {
		if (obj.disabled != true) {
			obj.focus();
		}
	}
}

////
// sage�`�F�b�N�ɍ��킹�āA���[�����̓��e������������
//
function mailSage() {
	var cbsage = document.getElementById('sage');
	if (cbsage) {
		var mailran = document.getElementById('mail');
		if (mailran) {
			if (cbsage.checked == true) {
				mailran.value = "sage";
			} else {
				if (mailran.value == "sage") {
					mailran.value = "";
				}
			}
		}
	}
}

////
// ���[�����̓��e�ɉ����āAsage�`�F�b�N��ON OFF����
//
function checkSage() {
	var mailran = document.getElementById('mail');
	if (mailran) {
		var cbsage = document.getElementById('sage');
		if (cbsage) {
			if (mailran.value == "sage") {
				cbsage.checked = true;
			} else {
				cbsage.checked = false;
			}
		}
	}
}

/*
// �����œǂݍ��ނ��Ƃɂ����̂ŁA�g��Ȃ�

////
// �O��̏������ݓ��e�𕜋A����
//
function loadLastPosted(from, mail, message) {
	var fromObj = document.getElementById('FROM');
	if (fromObj) {
		fromObj.value = from;
	}
	var mailObj = document.getElementById('mail');
	if (mailObj) {
		mailObj.value = mail;
	}
	var messageObj = document.getElementById('MESSAGE');
	if (messageObj) {
		messageObj.value = message;
	}
	checkSage();
}
*/

////
// inputConstant
//
function inputConstant(obj) {
	var msg = p2GetElementById('MESSAGE')
	if (msg) {
		cur = msg.value;
		add = obj.options[obj.selectedIndex].value;
		obj.options[0].selected = true;
		msg.value = cur+add;
		msg.focus();
	}
}

////
// �������݃{�^���̗L���E������؂�ւ���
//
function switchBlockSubmit(onoff) {
	var kakiko_submit = document.getElementById('submit');
	if (kakiko_submit) {
		kakiko_submit.disabled = onoff;
	}
	var submit_beres = document.getElementById('submit_beres');
	if (submit_beres) {
		submit_beres.disabled = onoff;
	}
}

////
// ��^����}������
//
function inputConstant(obj) {
	var msg = document.getElementById('MESSAGE');
	msg.value = msg.value + obj.options[obj.selectedIndex].value;
	msg.focus();
	obj.options[0].selected = true;
}

////
// �������ݓ��e�����؂���
//
function validateAll(doValidateMsg, doValidateSage) {
	var block_submit = document.getElementById('block_submit');
	if (block_submit && block_submit.checked) {
		alert('�������݃u���b�N��');
		return false;
	}
	if (doValidateMsg && !validateMsg()) {
		return false;
	}
	if (doValidateSage && !validateSage()) {
		return false;
	}
	return true;
}

////
// �{������łȂ������؂���
//
function validateMsg() {
	if (document.getElementById('MESSAGE').value.length == 0) {
		alert('�{��������܂���B');
		return false;
	}
	return true;
}

////
// sage�Ă��邩���؂���
//
function validateSage() {
	if (document.getElementById('mail').value.indexOf('sage') == -1) {
		if (window.confirm('sage�Ă܂����H')) {
			return true;
		} else {
			return false;
		}
	}
	return true;
}
