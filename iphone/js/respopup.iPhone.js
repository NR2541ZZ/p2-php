/* p2 - ���p���X�Ԃ��|�b�v�A�b�v���邽�߂�JavaScript */

/*
document.open;
document.writeln('<style type="text/css" media="all">');
document.writeln('<!--');
document.writeln('.respopup{visibility: hidden;}');
document.writeln('-->');
document.writeln('</style>');
document.close;
*/

//=============================
// �ݒ�
//=============================
cDelayShowSec = 0.1 * 1000;	// ���X�|�b�v�A�b�v��\������x�����ԁB
cDelayHideSec = 0.2 * 1000;	// ���X�|�b�v�A�b�v���\���ɂ���x������
cSpmDelayHideSec = 0.1 * 1000;	// ���X�|�b�v�A�b�v���\���ɂ���x�����ԁispm�j

//=============================
// �����ϐ�
//=============================
// gPOPS -- ResPopUp �I�u�W�F�N�g���i�[����z��B
// �z�� gPOPS �̗v�f�����A���ݐ����Ă��� ResPopUp �I�u�W�F�N�g�̐��ƂȂ�B
gPOPS = new Array(); 

gShowTimerIds = new Object();
gHideTimerIds = new Object();

gOnPopSpaceId = null;

zNum = 0;

//=============================
// �X�^�e�B�b�N���\�b�h��`
//=============================

// ResPopUp �I�u�W�F�N�g����舵��
var ResPopUpManager = {

	// �z�� gPOPS �ɐV�K ResPopUp �I�u�W�F�N�g ��ǉ�����
	// @return  ResPopUp
	addResPopUp: function (popId) {
		var aResPopUp = new ResPopUp(popId);
		// gPOPS.push(aResPopUp); Array.push ��IE5.5�������Ή��Ȃ̂ő�֏���
		return gPOPS[gPOPS.length] = aResPopUp;
	},

	// �z�� gPOPS ���� �w��� ResPopUp �I�u�W�F�N�g ���폜����
	// @return  boolean
	rmResPopUp: function (popId) {
		for (i = 0; i < gPOPS.length; i++) {
	    	if (gPOPS[i].popId == popId) {
				gPOPS = arraySplice(gPOPS, i);
				return true;
			}
		}
		return false;
	},

	// �z�� gPOPS �Ŏw�� popId �� ResPopUp �I�u�W�F�N�g��Ԃ�
	// @return  ResPopUp|false
	getResPopUp: function (popId) {
		for (i = 0; i < gPOPS.length; i++) {
	    	if (gPOPS[i].popId == popId) {
				return gPOPS[i];
			}
		}
		return false;
	}
}

//=============================
// �N���X��`
//=============================

// �N���X ���X�|�b�v�A�b�v�i���O�� ResPopup �ɂ������C����[Uu]�j
function ResPopUp(popId)
{
    this.popId = popId;
	this.zNum = zNum;
	this.hideTimerID = 0;
	
	// IE�p
	if (document.all) {
		this.popOBJ = document.all[this.popId];
	// DOM�Ή��p�iMozilla�j
	} else if (document.getElementById) {
		this.popOBJ = document.getElementById(this.popId);
	}
}

ResPopUp.prototype = {
	
	// ���X�|�b�v�A�b�v�̈ʒu���Z�b�g����
	// @return  void
	setPosResPopUp: function (x, y)
	{
		var x_adjust = 10;	// x���ʒu����
		var y_adjust = -68;	// y���ʒu����
	
		if (this.isModeSpm()) {
			x_adjust = 0;
			y_adjust = -10;
		}
	
		if (document.all) { // IE�p
			var body = (document.compatMode=='CSS1Compat') ? document.documentElement : document.body;
			//x = body.scrollLeft + event.clientX; // ���݂̃}�E�X�ʒu��X���W
			//y = body.scrollTop + event.clientY; // ���݂̃}�E�X�ʒu��Y���W
			this.popOBJ.style.pixelLeft  = x + x_adjust; //�|�b�v�A�b�v�ʒu
			this.popOBJ.style.pixelTop  = y + y_adjust;
		
			if (this.popOBJ.offsetTop + this.popOBJ.offsetHeight > body.scrollTop + body.clientHeight) {
				this.popOBJ.style.pixelTop = body.scrollTop + body.clientHeight - this.popOBJ.offsetHeight -20;
			}
			if (this.popOBJ.offsetTop < body.scrollTop) {
				this.popOBJ.style.pixelTop = body.scrollTop -2;
			}
		
		} else if (document.getElementById) { // DOM�Ή��p�iMozilla�j
			//x = ev.pageX; // ���݂̃}�E�X�ʒu��X���W
			//y = ev.pageY; // ���݂̃}�E�X�ʒu��Y���W
			this.popOBJ.style.left = x + x_adjust + "px"; //�|�b�v�A�b�v�ʒu
			this.popOBJ.style.top = y + y_adjust + "px";
			//alert(window.pageYOffset);
			//alert(this.popOBJ.offsetTop);
		
			if ((this.popOBJ.offsetTop + this.popOBJ.offsetHeight) > (window.pageYOffset + window.innerHeight)) {
				this.popOBJ.style.top = window.pageYOffset + window.innerHeight - this.popOBJ.offsetHeight -20 + "px";
			}
			if (this.popOBJ.offsetTop < window.pageYOffset) {
				this.popOBJ.style.top = window.pageYOffset -2 + "px";
			}
		}
	},

	// ���X�|�b�v�A�b�v��\������
	// @return  void
	showResPopUp: function (x, y)
	{
		if (this.popOBJ.style.visibility == "visible") {
			return;
		}
	
		this.popOBJ.style.zIndex = this.zNum;
		this.setPosResPopUp(x, y);
	
		// �X�}�[�g�|�b�v�A�b�v���j���[�̂Ƃ�
		var mode = this.popId.charAt(0);
		if (mode == "p" || mode == "a" || mode == "n") {
			this.opacity = 0.88;
		} else {
			this.opacity = 1;
		}
	
		this.popOBJ.style.visibility = "visible"; // ���X�|�b�v�A�b�v�\��
		/*
		this.popOBJ.ondblclick = function () {
			this.style.visibility = "hidden";
			ResPopUpManager.rmResPopUp(this.id);
		}
		*/
		this.popOBJ.onmouseout = function () {
			hideResPopUp(this.id)
		}
	
	},

	// ���X�|�b�v�A�b�v���\���^�C�}�[����
	// @return  void
	hideResPopUp: function ()
	{
		var delaySec = cDelayHideSec;
		
		if (this.isModeSpm()) {
			delaySec = cSpmDelayHideSec;
		}
		
		// ��莞�ԕ\�����������
		this.hideTimerID = setTimeout("doHideResPopUp('" + this.popId + "')", delaySec);
	},

	// ���X�|�b�v�A�b�v���\���ɂ��� ���ԑ҂�
	// @return  void
	doHideResPopUp: function ()
	{
		if (!this.isModeSpm()) {
			for (i = 0; i < gPOPS.length; i++) {
				// �������\�����ʂ̍����̂�����΁A�����̂�x������
				if (this.zNum < gPOPS[i].zNum) {
					//clearTimeout(this.hideTimerID); // �^�C�}�[������
					// ��莞�ԕ\�����������
					this.hideTimerID = setTimeout("hideResPopUp('" + this.popId + "')", cDelayHideSec);
					return;
				}
			}
		}
		this.nowHideResPopUp();
	},

	// ���X�|�b�v�A�b�v���\���ɂ��� ��
	// @return  void
	nowHideResPopUp: function ()
	{
		var me = this;
		gHideTimerIds[me.popId] = true;
		if (!gHideTimerIds[me.popId]) {
			//this.setOpacity(1, true, 0.15);
			return;
		} else {
			delete gHideTimerIds[me.popId];
			me.popOBJ.style.visibility = "hidden"; // ���X�|�b�v�A�b�v��\��
			// clearTimeout(this.hideTimerID); // �^�C�}�[������
			ResPopUpManager.rmResPopUp(me.popId);
		}
	},
	
	// �X�}�[�g�|�b�v�A�b�v���j���[�Ȃ� true
	// @return  boolean
	isModeSpm: function ()
	{
		// popId
		// q{resnum}of{datkey}
		// aThread_{$this->bbs}_{$this->key}
		// p,n�͕s��
		
		var mode = this.popId.charAt(0);
		if (mode == "p" || mode == "a" || mode == "n") {
			return true;
		}
		return false;
	}
}

//=============================
// �֐���`
//=============================
/**
 * arraySplice
 *
 * anArray.splice(i, 1); Array.splice ��IE5.5�������Ή��Ȃ̂ő�֏���
 * @return array
 */
function arraySplice(anArray, i)
{
	var newArray = new Array();
	
	for (j = 0; j < anArray.length; j++) {
		if (j != i) {
			newArray[newArray.length] = anArray[j];
		}
	}
	return newArray;
}

/**
 * ���X�|�b�v�A�b�v��\���^�C�}�[����
 *
 * ���p���X�Ԃ� onMouseover �ŌĂяo�����
 * [memo] ��������event�I�u�W�F�N�g�ɂ��������悢���낤���B
 *
 * @param  boolean  onPopSpace  �|�b�v�A�b�v�X�y�[�X�ւ�onmouseover�ł̌Ăяo���Ȃ�B�d���Ăяo������̂��߁B
 */
function showResPopUp(popId, ev, onPopSpace)
{
	if (popId.indexOf("-") != -1) { return; } // �A�� (>>1-100) �͔�Ή��Ȃ̂Ŕ�����
	
	if (document.all) { // IE�p
		var body = (document.compatMode=='CSS1Compat') ? document.documentElement : document.body;
		var x = body.scrollLeft + event.clientX; // ���݂̃}�E�X�ʒu��X���W
		var y = body.scrollTop + event.clientY; // ���݂̃}�E�X�ʒu��Y���W
	} else if (document.getElementById) { // DOM�Ή��p�iMozilla�j
		var x = 0; // ���݂̃}�E�X�ʒu��X���W ev.pageX 070828 ���͂�����o��悤��������
		var y = ev.pageY+20; // ���݂̃}�E�X�ʒu��Y���W
	} else {
		return;
	}
	
	var aResPopUp = ResPopUpManager.getResPopUp(popId);
	if (aResPopUp) {
		delete gHideTimerIds[popId];
		if (aResPopUp.hideTimerID) { clearTimeout(aResPopUp.hideTimerID); } // ��\���^�C�}�[������

		if (onPopSpace) {
			if (gOnPopSpaceId == popId) {
				return;
			} else {
				gOnPopSpaceId = popId;
			}
		}
		
		// �ĕ\������ zIndex ����
		if (aResPopUp.zNum < zNum) {
			aResPopUp.zNum = ++zNum;
			aResPopUp.popOBJ.style.zIndex = aResPopUp.zNum;
		}
		
		if (!onPopSpace) {
			// Safari�ł͍����Ń}�E�X�I�[�o�[�A�}�E�X�A�E�g���������ă}�E�X�ɂ��Ă��Ă��܂��i���Ȏd�l���j
			if (!isSafari()) {
				aResPopUp.setPosResPopUp(x,y);
			}
		}
		
		return;
	}
	
	// doShowResPopUp(popId, ev);
	
	aShowTimer = new Object();
	aShowTimer.x = x;
	aShowTimer.y = y;
	
	// ��莞�Ԃ�����\������
	aShowTimer.timerID = setTimeout("doShowResPopUp('" + popId + "')", cDelayShowSec);
	
	gShowTimerIds[popId] = aShowTimer;
	//alert(gShowTimerIds[popId].timerID);
}

/**
 * ���X�|�b�v�A�b�v��\������
 */
function doShowResPopUp(popId)
{
	var x = gShowTimerIds[popId].x;
	var y = gShowTimerIds[popId].y;
	var aResPopUp = ResPopUpManager.getResPopUp(popId);
	if (aResPopUp) {
		if (aResPopUp.hideTimerID) { clearTimeout(aResPopUp.hideTimerID); } // ��\���^�C�}�[������
		/*
		// �ĕ\������ zIndex ����
		if (aResPopUp.zNum < zNum) {
			aResPopUp.zNum = ++zNum;
			aResPopUp.popOBJ.style.zIndex = aResPopUp.zNum;
		}
		*/
		return;
	}
	
	zNum++;
	aResPopUp = ResPopUpManager.addResPopUp(popId); // �V�����|�b�v�A�b�v��ǉ�

	aResPopUp.showResPopUp(x, y);
}

/**
 * ���X�|�b�v�A�b�v���\���^�C�}�[����
 *
 * ���p���X�Ԃ��� onMouseout �ŌĂяo�����
 */
function hideResPopUp(popId)
{
	if (popId.indexOf("-") != -1) { return; } // �A�� (>>1-100) �͔�Ή��Ȃ̂Ŕ�����
	
	if (gShowTimerIds[popId].timerID) { clearTimeout(gShowTimerIds[popId].timerID); } // �\���^�C�}�[������
	
	var aResPopUp = ResPopUpManager.getResPopUp(popId);
	if (aResPopUp) {
		aResPopUp.hideResPopUp();
	}
}

/**
 * ���X�|�b�v�A�b�v���\���ɂ���
 */
function doHideResPopUp(popId)
{
	var aResPopUp = ResPopUpManager.getResPopUp(popId);
	if (aResPopUp) {
		aResPopUp.doHideResPopUp();
	}
}


