/* p2 - �X�}�[�g�|�b�v�A�b�v���j���[JavaScript�t�@�C�� */

var spmResNum   = new Number(); // �|�b�v�A�b�v�ŎQ�Ƃ��郌�X�ԍ�
var spmBlockID  = new String(); // �t�H���g�ύX�ŎQ�Ƃ���ID
var spmSelected = new String(); // �I�𕶎�����ꎞ�I�ɕۑ�
var spmTarget   = new String(); // �t�B���^�����O���ʂ��J���E�C���h�E

// �X�}�[�g�|�b�v�A�b�v���j���[�𐶐��o�͂���
// @access  public
// @return  void
function makeSPM(aThread)
{
	var thread_id = aThread.objName;
	var a_tag    = "<a href=\"#\" onclick=\"return !spmOpenSubWin(" + thread_id + ",";
	var numbox   = "";
	if (document.getElementById || document.all) {
		//numbox = "<input disabled type=\"text\" id=\"" + thread_id + "_numbox\" class=\"numbox\" size=\"4\" value=\"0\">";
		numbox = "<span id=\"" + thread_id + "_numbox\" class=\"numbox\"> </span>";
	}

	//�|�b�v�A�b�v���j���[�𐶐�

	document.writeln("<div id=\"" + thread_id + "_spm\" class=\"spm\"" + makeOnPopUp(thread_id+"_spm", false) + ">");
	
	//�w�b�_
	if (aThread.spmHeader != "") {
		document.writeln("<p>" + aThread.spmHeader.replace("resnum", numbox) + "</p>");
	}
	
	// ����Ƀ��X
	var baseOptionKoreRes = '';
	if ((typeof(gIsReadNew) == 'boolean') && gIsReadNew) {
		baseOptionKoreRes = 'from_read_new=1&';
	}
	if (aThread.spmOption['spm_kokores'] && aThread.spmOption['spm_kokores'] == 1) {
		document.writeln(getSpmLinkTag(thread_id, 'post_form.php', baseOptionKoreRes, '����Ƀ��X', thread_id + '_kore_res'));
		document.writeln(getSpmLinkTag(thread_id, 'post_form.php', baseOptionKoreRes + 'inyou=1', '���p���ă��X', thread_id + '_kore_res1'));
	} else if (aThread.spmOption['spm_kokores'] && aThread.spmOption['spm_kokores'] == 2) {
		document.writeln(getSpmLinkTag(thread_id, 'post_form.php', baseOptionKoreRes + 'inyou=2', '����Ƀ��X', thread_id + '_kore_res2'));
		document.writeln(getSpmLinkTag(thread_id, 'post_form.php', baseOptionKoreRes + 'inyou=3', '���p���ă��X', thread_id + '_kore_res3'));
	}
	
	//������
	if (aThread.spmOption['enable_bookmark']) {
		document.writeln(a_tag + "'info_sp.php','mode=readhere');\">�����܂œǂ�</a>");
	}
	//���ځ[�񃏁[�h
	if (aThread.spmOption['spm_aborn']) {
		document.writeln(a_tag + "'info_sp.php','mode=aborn_res');\">���ځ[�񂷂�</a>");
		document.writeln("<a href=\"javascript:void(0);\"" + makeOnPopUp(thread_id+"_ab", true) + ">���ځ[�񃏁[�h</a>");
	}
	//NG���[�h
	if (aThread.spmOption['spm_ng']) {
		document.writeln("<a href=\"javascript:void(0);\"" + makeOnPopUp(thread_id+"_ng", true) + ">NG���[�h</a>");
	}
	//�A�N�e�B�u���i�[
	if (aThread.spmOption['enable_am_on_spm']) {
		document.writeln("<a href=\"javascript:void(0);\"" + makeOnPopUp(thread_id+"_ds", true) + ">�t�H���g�ݒ�</a>");
	} else if (aThread.spmOption['enable_am_on_spm'] == 2) {
		makeDynamicStyleSPM(thread_id+"_ds", false);
	}
	
	/*
	//�t�B���^�����O
	if (aThread.spmOption['enable_fl_on_spm']) {
		document.writeln("<a href=\"javascript:void(0);\"" + makeOnPopUp(thread_id+"_fl", true) + ">�t�B���^�\��</a>");
	}
	*/

	// �������O���t�B���^�\��
	document.writeln('<a id="' + thread_id + '_same_name" href="#" target="' + spmTarget + '">�������O</a>');
	
	// �t�Q��
	document.writeln('<a id="' + thread_id + '_ref_res" href="' + getSpmFilterUrl(aThread, 'rres', 'on') + '" target="' + spmTarget + '">�t�Q��</a>');

	document.writeln("</div>");

	///�T�u���j���[�𐶐�

	//���ځ[�񃏁[�h�E�T�u���j���[
	if (aThread.spmOption['spm_aborn']) {
		makeAbornSPM(thread_id+"_ab", a_tag);
	}
	//NG���[�h�E�T�u���j���[
	if (aThread.spmOption['spm_ng']) {
		makeAbornSPM(thread_id+"_ng", a_tag);
	}
	//�t�H���g�ݒ�E�T�u���j���[
	if (aThread.spmOption['enable_am_on_spm']) {
		makeDynamicStyleSPM(thread_id+"_ds", true);
	}
	//�t�B���^�����O�E�T�u���j���[
	if (aThread.spmOption['enable_fl_on_spm']) {
		makeFilterSPM(thread_id+"_fl", thread_id);
	}
}

// �X�}�[�g�|�b�v�A�b�v���j���[���|�b�v�A�b�v�\������
// @access  public
// @return  void
function showSPM(aThread, resnum, resid, event, obj)
{
	var ls_q = gExistWord ? resnum + '-n' : resnum;
	
	obj.href = "read.php?bbs=" + aThread.bbs + "&key=" + aThread.key + "&host=" + aThread.host + '&offline=1&ls=' + ls_q;
	
	spmResNum  = resnum;
	spmBlockID = resid;
	if (navigator.userAgent.indexOf("Gecko") != -1) {
		spmSelected = window.getSelection();
	}
	if (aThread.spmHeader.indexOf("resnum") != -1 && (document.getElementById || document.all)) {
		//p2GetElementById(aThread.objName + "_numbox").value = resnum;
		p2GetElementById(aThread.objName + "_numbox").innerHTML = resnum;
	}
	
	var spm_same_name = p2GetElementById(aThread.objName + "_same_name");
	if (spm_same_name) {
		spm_same_name.href = getSpmFilterUrl(aThread, 'name', 'on');
	}
	var spm_ref_res = p2GetElementById(aThread.objName + "_ref_res");
	if (spm_ref_res) {
		spm_ref_res.href = getSpmFilterUrl(aThread, 'rres', 'on');
	}
	var kore_res = p2GetElementById(aThread.objName + "_kore_res");
	if (kore_res) {
		kore_res.href = getSpmOpenSubUrl(aThread, 'post_form.php', '');
	}
	var kore_res1 = p2GetElementById(aThread.objName + "_kore_res1");
	if (kore_res1) {
		kore_res1.href = getSpmOpenSubUrl(aThread, 'post_form.php', 'inyou=1');
	}
	var kore_res2 = p2GetElementById(aThread.objName + "_kore_res2");
	if (kore_res2) {
		kore_res2.href = getSpmOpenSubUrl(aThread, 'post_form.php', 'inyou=2');
	}
	var kore_res3 = p2GetElementById(aThread.objName + "_kore_res3");
	if (kore_res3) {
		kore_res3.href = getSpmOpenSubUrl(aThread, 'post_form.php', 'inyou=3');
	}
	
	showResPopUp(aThread.objName + "_spm", event);
}


/* ==================== �o������ ====================
 * <a href="javascript:void(0);" onclick="foo()">��
 * <a href="javascript:void(foo());">�Ɠ����B
 * JavaScript��URI�𐶐�����Ƃ��A&��&amp;�Ƃ��Ă͂����Ȃ��B
 * ================================================== */


// @access  private only this file
// @return  string
function getSpmLinkTag(thread_id, path, option, text, a_id)
{
	return '<a id="' + a_id + '" href="#" onclick="return !spmOpenSubWin(' + thread_id + ",'" + path + "','" + option + "');\">" + text + "</a>";
}

// �}�E�X�I�[�o�[/�A�E�g���Ɏ��s�����X�N���v�g�𐶐�����
// @access  private only this file
// @return  string
function makeOnPopUp(popup_id, isSubMenu)
{
	//�x������
	var spmPopUpDelay = "delaySec=(0.3*1000);";
	if (isSubMenu) {
		spmPopUpDelay = "delaySec=0;";
	}
	//���[���I�[�o�[
	var spmPopUpEvent  = " onmouseover=\"" + spmPopUpDelay + "showResPopUp('" + popup_id + "',event,true);\"";
	//���[���A�E�g
		spmPopUpEvent += " onmouseout=\""  + spmPopUpDelay + "hideResPopUp('" + popup_id + "');\"";
	return spmPopUpEvent;
}


// ���ځ[��/NG�T�u���j���[�𐶐��o�͂���
// @access  private only this file
// @return  void
function makeAbornSPM(menu_id, a_tag)
{
	var mode = "aborn";
	if (menu_id.substr(menu_id.lastIndexOf("_"), 3) == "_ng") {
		mode = "ng";
	}
	document.writeln("<div id=\"" + menu_id + "\" class=\"spm\"" + makeOnPopUp(menu_id, true) + ">");
	document.writeln(a_tag + "'info_sp.php','mode=" + mode + "_name'));\">���O</a>");
	document.writeln(a_tag + "'info_sp.php','mode=" + mode + "_mail'));\">���[��</a>");
	document.writeln(a_tag + "'info_sp.php','mode=" + mode + "_msg'));\">�{��</a>");
	document.writeln(a_tag + "'info_sp.php','mode=" + mode + "_id'));\">ID</a>");
	document.writeln("</div>");
}


// �t�H���g�ݒ�T�u���j���[�𐶐��o�͂���
// @access  private only this file
// @return  void
function makeDynamicStyleSPM(menu_id, isSubMenu)
{
	var spmActiveMona  = "<div class=\"spmMona\">�@�i";
		spmActiveMona += "<a href=\"javascript:void(spmDynamicStyle('12px'));\">�L</a>";
		spmActiveMona += "<a href=\"javascript:void(spmDynamicStyle('14px'));\">��</a>";
		spmActiveMona += "<a href=\"javascript:void(spmDynamicStyle('16px'));\">�M</a>";
		spmActiveMona += "�j</div>";
	if (isSubMenu) {
		document.writeln("<div id=\"" + menu_id + "\" class=\"spm\"" + makeOnPopUp(menu_id, true) + ">");
		document.writeln(spmActiveMona);
		document.writeln("<a href=\"javascript:void(spmDynamicStyle('normal'));\">�W���t�H���g</a>");
		document.writeln("<a href=\"javascript:void(spmDynamicStyle('monospace'));\">�����t�H���g</a>");
		document.writeln("<a href=\"javascript:void(spmDynamicStyle('larger'));\">�傫��</a>");
		document.writeln("<a href=\"javascript:void(spmDynamicStyle('smaller'));\">������</a>");
		document.writeln("<a href=\"javascript:void(spmDynamicStyle('rewind'));\">���ɖ߂�</a>");
		document.writeln("</div>");
	} else {
		document.writeln(spmActiveMona);
	}
}


// �t�B���^�����O�T�u���j���[�𐶐�����
// @access  private only this file
// @return  void
function makeFilterSPM(menu_id, thread_id)
{
	var filter_anchor = "<a href=\"javascript:void(spmOpenFilter(" + thread_id;
	document.writeln("<div id=\"" + menu_id + "\" class=\"spm\"" + makeOnPopUp(menu_id, true) + ">");
	document.writeln(filter_anchor + ",'name','on'));\">�������O</a>");
	document.writeln(filter_anchor + ",'mail','on'));\">�������[��</a>");
	//document.writeln(filter_anchor + ",'date','on'));\">�������t</a>");
	//document.writeln(filter_anchor + ",'id','on'));\">����ID</a>");
	//document.writeln(filter_anchor + ",'name','off'));\">�قȂ閼�O</a>");
	//document.writeln(filter_anchor + ",'mail','off'));\">�قȂ郁�[��</a>");
	//document.writeln(filter_anchor + ",'date','off'));\">�قȂ���t</a>");
	//document.writeln(filter_anchor + ",'id','off'));\">�قȂ�ID</a>");
	//document.writeln(filter_anchor + ",'rres','on'));\">�t�Q��</a>");
	document.writeln("</div>");
}

// URI�̏��������A�|�b�v�A�b�v�E�C���h�E���J��
// @access  private only this file
// @return  boolean
function spmOpenSubWin(aThread, path, option)
{
	var width  = 650; //�|�b�v�A�b�v�E�C���h�E�̕�
	var height = 350; //�|�b�v�A�b�v�E�C���h�E�̍���
	var boolScrl = 1; //�X�N���[���o�[��\���ioff:0, on:1�j
	var boolResize = 0; //�������T�C�Y�ioff:0, on:1�j
	if (path == "info_sp.php") {
		width  = 480;
		height = 240;
		boolScrl = 0;
	} else if (path == "post_form.php" && aThread.spmOption['spm_kokores'] && aThread.spmOption['spm_kokores'] == 2) {
		//height = 450;
	} else if (path == "tentori.php") {
		width  = 450;
		height = 150;
	}
	
	var url = getSpmOpenSubUrl(aThread, path, option);
	return openSubWin(url, width, height, boolScrl, boolResize);
}

// �|�b�v�A�b�v�E�B���h�E���J�����߂�URI��Ԃ�
// @access  private only this file
// @return  string
function getSpmOpenSubUrl(aThread, path, option)
{
	var popup = 1;    //�|�b�v�A�b�v�E�C���h�E���ۂ��ino:0, yes:1, yes&�^�C�}�[�ŕ���:2�j
	if (path == "info_sp.php") {
		if (!aThread.spmOption['spm_confirm']) {
			popup = 2; //������,���ځ[��/NG���[�h�o�^�̊m�F�����Ȃ��Ƃ�
		}
		if (option.indexOf("_msg") != -1 && spmSelected != '') {
			option += "&selected_string=" + encodeURIComponent(spmSelected);
		}
	} else if (path == "tentori.php") {
		popup = 2;
	}
	
	var url = path + "?host=" + aThread.host + "&bbs=" + aThread.bbs + "&key=" + aThread.key;
	url += "&rescount=" + aThread.rc + "&ttitle_en=" + aThread.ttitle_en;
	url += "&resnum=" + spmResNum + "&popup=" + popup;
	if (option != "") {
		url += "&" + option;
	}
	return url;
}

// �t�B���^�����O�pURL��Ԃ�
// @access  private only this file
// @return  string
function getSpmFilterUrl(aThread, field, match)
{
	var url = "read_filter.php?bbs=" + aThread.bbs + "&key=" + aThread.key + "&host=" + aThread.host;
	url += "&rescount=" + aThread.rc + "&ttitle_en=" + aThread.ttitle_en + "&resnum=" + spmResNum;
	url += "&ls=all&field=" + field + "&method=just&match=" + match + "&offline=1";
	return url;
}

// URI�̏��������A�t�B���^�����O���ʂ�\������
// @access  private only this file
// @return  string
function spmOpenFilter(aThread, field, match)
{
	var url = getSpmFilterUrl(aThread, field, match);
	
	switch (spmTarget) {
		case "_self":
			location.href = url;
			break;
		case "_parent":
			parent.location.href = url;
			break;
		case "_top":
			top.location.href = url;
			break;
		case "_blank":
			window.open(url, "", "");
			break;
		default:
			if (parent.spmTarget.location.href) {
				parent.spmTarget.location.href = url;
			} else {
				window.open(url, spmTarget, "")
			}
	}
	
	return true;
}

// �ΏۃI�u�W�F�N�g��ݒ肵�A������ς���
// @access  private only this file
// @return  boolean
function spmDynamicStyle(mode)
{
	var dsTarget     = p2GetElementById(spmBlockID);
	var dsFontSize   = dsTarget.style.fontSize;
	var dsLineHeight = dsTarget.style.lineHeight;
	var isAutoMona   = false;
	if (dsTarget.hasAttribute("class") && dsTarget.getAttribute("class") == "AutoMona") {
		isAutoMona   = true;
	}
	var isPopUp      = false;
	if (spmBlockID.charAt(0) == "q") {
		isPopUp      = true;
	}
	//�Đݒ�
	if (dsFontSize.length   < 1) {
		if (isAutoMona) {
			dsFontSize = "14px";
		} else if (isPopUp) {
			dsFontSize = am_respop_fontSize;
		} else {
			dsFontSize = am_read_fontSize;
		}
	}
	if (dsLineHeight.length < 1) {
		if (isAutoMona) {
			dsLineHeight = "100%";
		} else if (isPopUp) {
			dsLineHeight = am_respop_lineHeight;
		} else {
			dsLineHeight = am_read_lineHeight;
		}
	}
	//����
	switch (mode) {
		//�A�N�e�B�u���i�[
		case "16px":
		case "14px":
		case "12px":
			activeMona(spmBlockID, mode);
			return true;
		//���̃t�H���g�T�C�Y�ɖ߂�
		case "rewind":
			if (isQuoteBlock) {
				dsTarget.style.fontSize   = am_respop_fontSize;
				dsTarget.style.lineHeight = am_respop_lineHeight;
			} else {
				dsTarget.style.fontSize   = am_read_fontSize;
				dsTarget.style.lineHeight = am_read_lineHeight;
			}
			//���������W���t�H���g�ɂ���
		//�W���t�H���g�ɂ���
		case "normal":
			dsTarget.style.fontFamily = am_fontFamily;
			dsTarget.style.whiteSpace = "normal";
			return true;
		//�����t�H���g�ɂ���
		case "monospace":
			dsTarget.style.fontFamily = "monospace";
			dsTarget.style.whiteSpace = "pre";
			return true;
		//�t�H���g�T�C�Y��ς���
		case "larger":
		case "smaller":
			var newFontSize    = new Number;
			var curFontSize    = new Number(dsFontSize.match(/^\d+/));
			var FontSizeUnit   = new String(dsFontSize.match(/\D+$/));
			var newLineHeight  = new Number;
			var curLineHeight  = new Number(dsLineHeight.match(/^\d+/));
			var LineHeightUnit = new String(dsLineHeight.match(/\D+$/));
			if (mode == "larger") {
				newFontSize   = curFontSize   * 1.25;
				newLineHeight = curLineHeight * 1.25;
			} else {
				newFontSize   = curFontSize   * 0.8;
				newLineHeight = curLineHeight * 0.8;
			}
			if (LineHeightUnit == "%") {
				newLineHeight = curLineHeight;
			}
			dsTarget.style.fontSize   = newFontSize.toString()   + FontSizeUnit;
			dsTarget.style.lineHeight = newLineHeight.toString() + LineHeightUnit;
			return true;
		//...
		default:
			return false;
	}
}
