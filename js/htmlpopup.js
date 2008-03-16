/*
	p2 - HTML���|�b�v�A�b�v���邽�߂�JavaScript
*/

//showHtmlDelaySec = 0.2 * 1000; // HTML�\���f�B���C�^�C���B�}�C�N���b�B

gShowHtmlTimerID = 0;
gNodePopup = false;	// iframe���i�[����div�v�f
gNodeClose = false; // �~���i�[����div�v�f
tUrl = ""; // URL�e���|�����ϐ�
gUrl = ""; // URL�O���[�o���ϐ�

// �u���E�U��ʁi�X�N���[����j�̃}�E�X�� X, Y���W
gMouseX = 0;
gMouseY = 0;

/**
 * getDocumentBodyIE
 */
function getDocumentBodyIE()
{
	return (document.compatMode=='CSS1Compat') ? document.documentElement : document.body;
}

/**
 * HTML�v�A�b�v��\������
 * �����̈��p���X�Ԃ�(p)�� onMouseover �ŌĂяo�����
 *
 * @access public
 */
function showHtmlPopUp(url,ev,showHtmlDelaySec)
{
	if (!document.createElement) { return; } // DOM��Ή��Ȃ甲����

	// �܂� onLoad ����Ă��Ȃ��A�R���e�i���Ȃ���΁A������
	if (!gIsPageLoaded && !document.getElementById('popUpContainer')) {
		return;
	}

	showHtmlDelaySec = showHtmlDelaySec * 1000;

	if (!gNodePopup || url != gUrl) {
		tUrl = url;

		// IE�p
		if (document.all) {
			// ���݂̃}�E�X�ʒu��X, Y���W
			var body = getDocumentBodyIE();
			gMouseX = body.scrollLeft + event.clientX;
			gMouseY = body.scrollTop  + event.clientY;

		} else {
			// pageX, pageY - �u���E�U��ʁi�X�N���[����j�̃}�E�X�� X, Y���W�BIE�͔�T�|�[�g
			gMouseX = ev.pageX;
			gMouseY = ev.pageY;
		}

		// HTML�\���f�B���C�^�C�}�[
		gShowHtmlTimerID = setTimeout("showHtmlPopUpDo()", showHtmlDelaySec);
	}
}

/**
 * showHtmlPopUpDo() ���痘�p�����
 *
 * @return integer
 */
function getCloseTop(win_bottom)
{
	var close_top_adjust = 16;

	close_top = Math.min(win_bottom - close_top_adjust, gMouseY + close_top_adjust);
	if (close_top >= win_bottom - close_top_adjust) {
		close_top = gMouseY - close_top_adjust - 12;
	}
	return close_top;
}

/**
 * HTML�|�b�v�A�b�v�̎��s
 */
function showHtmlPopUpDo()
{
	// ���炩���ߊ�����HTML�|�b�v�A�b�v����Ă���
	hideHtmlPopUp();

	gUrl = tUrl;
	var popup_x_adjust = 7;			// popup(iframe)��x���ʒu����
	var closebox_width = 18;		// �~�̉���
	var adjust_for_scrollbar = 22;	// �X�N���[���o�[���l�����ď��������ڂɔ�����

	if (gUrl.indexOf("kanban.php?") != -1) { popup_x_adjust += 23; }

	if (!gNodePopup) {
		gNodePopup = document.createElement('div');
		gNodePopup.setAttribute('id', "iframespace");

		gNodeClose = document.createElement('div');
		gNodeClose.setAttribute('id', "closebox");
		//gNodeClose.setAttribute('onMouseover', "hideHtmlPopUp()");

		var closeX = gMouseX + popup_x_adjust - closebox_width;

		// IE�p
		if (document.all) {
			var body = getDocumentBodyIE();

			gNodePopup.style.pixelLeft  = gMouseX + popup_x_adjust;	// �|�b�v�A�b�v�ʒu iframe��X���W
			gNodePopup.style.pixelTop  = body.scrollTop;	// �|�b�v�A�b�v�ʒu iframe��Y���W
			gNodeClose.style.pixelLeft  = closeX; 		// �|�b�v�A�b�v�ʒu �~��X���W

			// �|�b�v�A�b�v�ʒu �~��Y���W
			var close_top = getCloseTop(body.scrollTop + body.clientHeight);
			gNodeClose.style.pixelTop = close_top;

			var iframe_width = body.clientWidth - gNodePopup.style.pixelLeft - adjust_for_scrollbar;
			var iframe_height = body.clientHeight - adjust_for_scrollbar;

		// DOM�Ή��p�iMozilla�j
		} else if (document.getElementById) {

			gNodePopup.style.left = (gMouseX + popup_x_adjust) + "px"; 	// �|�b�v�A�b�v�ʒu iframe��X���W
			gNodePopup.style.top  = window.pageYOffset;		// �|�b�v�A�b�v�ʒu iframe��Y���W
			gNodeClose.style.left = closeX + "px"; 			// �|�b�v�A�b�v�ʒu �~��X���W

			// �|�b�v�A�b�v�ʒu �~��Y���W
			var close_top = getCloseTop(window.pageYOffset + window.innerHeight);
			gNodeClose.style.top = close_top + "px";

			var iframe_width = window.innerWidth - (gMouseX + popup_x_adjust) - adjust_for_scrollbar;
			var iframe_height = window.innerHeight - adjust_for_scrollbar;
		}

		pageMargin = "";
		// �摜�̏ꍇ�̓}�[�W�����[���ɂ���
		if (gUrl.match(/(jpg|jpeg|gif|png)$/)) {
			pageMargin = ' marginheight="0" marginwidth="0" hspace="0" vspace="0"';
		}
		gNodePopup.innerHTML = "<iframe src=\""+gUrl+"\" frameborder=\"1\" border=\"1\" style=\"background-color:#fff;\" width=" + iframe_width + " height=" + iframe_height + pageMargin +">&nbsp;</iframe>";

		gNodeClose.innerHTML = "<b onMouseover=\"hideHtmlPopUp()\">�~</b>";

		var popUpContainer = document.getElementById("popUpContainer");
		if (popUpContainer) {
			popUpContainer.appendChild(gNodePopup);
			popUpContainer.appendChild(gNodeClose);
		} else {
			document.body.appendChild(gNodePopup);
			document.body.appendChild(gNodeClose);
		}
	}
}

/**
 * HTML�|�b�v�A�b�v���\���ɂ���
 *
 * @access public
 */
function hideHtmlPopUp()
{
	if (!document.createElement) { return; } // DOM��Ή��Ȃ甲����

	if (gShowHtmlTimerID) { clearTimeout(gShowHtmlTimerID); } // HTML�\���f�B���C�^�C�}�[����������
	if (gNodePopup) {
		gNodePopup.style.visibility = "hidden";
		gNodePopup.parentNode.removeChild(gNodePopup);
		gNodePopup = false;
	}
	if (gNodeClose) {
		gNodeClose.style.visibility = "hidden";
		gNodeClose.parentNode.removeChild(gNodeClose);
		gNodeClose = false;
	}
}

/**
 * HTML�\���^�C�}�[����������
 *
 * (p)�� onMouseout �ŌĂяo�����
 */
function offHtmlPopUp()
{
	// HTML�\���f�B���C�^�C�}�[������Ή������Ă���
	if (gShowHtmlTimerID) {
		clearTimeout(gShowHtmlTimerID);
	}
}
