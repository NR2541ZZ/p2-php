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

delaySec = 0.3 * 1000;	//���X�|�b�v�A�b�v���\���ɂ���x�����ԁB�b�B
zNum=0;

//==============================================================
// POPS -- ResPopUp �I�u�W�F�N�g���i�[����z��B
// �z�� POPS �̗v�f�����A���ݐ����Ă��� ResPopUp �I�u�W�F�N�g�̐��ƂȂ�B
//==============================================================
POPS = new Array(); 

theResPopCtl = new ResPopCtl();

//==============================================================
// showResPopUp -- ���X�|�b�v�A�b�v��\������֐�
// ���p���X�Ԃ� onMouseover �ŌĂяo�����
//==============================================================

function showResPopUp(divID,ev){
	if( divID.indexOf("-") != -1 ){return;} //�A��(>>1-100)�͔�Ή�
	aResPopUp = theResPopCtl.getResPopUp(divID);
	if(aResPopUp){
	
		/*
		//�ĕ\������ zIndex ����------------------------
		// �������Ȃ������Ғʂ�̓�������Ă���Ȃ��B
		// IE��Mozilla�ŋ������Ⴄ�B����Ĕ�A�N�e�B�u�B
		aResPopUp.zNum=zNum;
		aResPopUp.popOBJ.style.zIndex=aResPopUp.zNum;
		//----------------------------------------
		*/
		
	} else {
		zNum++;
		theResPopCtl.addResPopUp(divID); //�V�����|�b�v�A�b�v��ǉ�
	}
	if (aResPopUp.timerID) {clearTimeout(aResPopUp.timerID);} //��\���^�C�}�[������

	aResPopUp.showResPopUp(ev);
}

//==============================================================
// hideResPopUp -- ���X�|�b�v�A�b�v���\���^�C�}�[����֐�
// ���p���X�Ԃ��� onMouseout �ŌĂяo�����
//==============================================================

function hideResPopUp(divID){
	if (divID.indexOf("-") != -1) {return;} //�A��(>>1-100)�͔�Ή�
	aResPopUp = theResPopCtl.getResPopUp(divID);
	if (aResPopUp) {
		aResPopUp.hideResPopUp();
	}
}

//==============================================================
// hideResPopUp2 -- ���X�|�b�v�A�b�v���\���ɂ���֐�
//==============================================================

function hideResPopUp2(divID){
	aResPopUp = theResPopCtl.getResPopUp(divID);
	aResPopUp.hideResPopUp2();
}


//==============================================================
// ResPopCtl  -- �I�u�W�F�N�g�f�[�^���R���g���[������N���X
//==============================================================

function ResPopCtl(){

	//==================================================
	// �z�� POPS �ɐV�K ResPopUp �I�u�W�F�N�g ��ǉ�����֐�
	//==================================================
	function ResPopCtl_addResPopUp(divID){
		aResPopUp = new ResPopUp(divID);
		//POPS.push(aResPopUp); Array.push ��IE5.5�������Ή��Ȃ̂ő�֏���
		POPS[POPS.length] = aResPopUp;
	}
	ResPopCtl.prototype.addResPopUp = ResPopCtl_addResPopUp;
	
	//==================================================
	// �z�� POPS ���� �w��� ResPopUp �I�u�W�F�N�g ���폜����֐�
	//==================================================
	function ResPopCtl_rmResPopUp(divID){
		for (i = 0; i < POPS.length; i++) {
	    	if(POPS[i].divID == divID){
				//POPS.splice(i, 1); Array.splice ��IE5.5�������Ή��Ȃ̂ő�֏���
				
				POPS2 = new Array();
				for(j=0; j < POPS.length; j++){
					if(j != i){
						POPS2[POPS2.length]=POPS[j];
					}
				}
				POPS=POPS2;
				
				return true;
			}
		}
		return false;
	}
	ResPopCtl.prototype.rmResPopUp = ResPopCtl_rmResPopUp;

	//==================================================
	// �z�� POPS �Ŏw�� divID �� ResPopUp �I�u�W�F�N�g��Ԃ��֐�
	//==================================================
	function ResPopCtl_getResPopUp(divID){
		for (i = 0; i < POPS.length; i++) {
	    	if(POPS[i].divID == divID){
				return POPS[i];
			}
		}
		return false;
	}
	ResPopCtl.prototype.getResPopUp = ResPopCtl_getResPopUp;
	
	return this;
}


//==============================================================
// ResPopUp -- ���X�|�b�v�A�b�v�N���X
//==============================================================

function ResPopUp(divID) {
    this.divID = divID;
	this.zNum = zNum;
	this.timerID = 0;
	 if (document.all) { // IE�p
		this.popOBJ = document.all[this.divID];
	} else if (document.getElementById) { // DOM�Ή��p�iMozilla�j
		this.popOBJ = document.getElementById(this.divID);
	}
	
	//==================================================
	// showResPopUp -- ���X�|�b�v�A�b�v��\������֐�
	//==================================================
	function ResPopUp_showResPopUp(ev) {
		var x_adjust = 10; // x���ʒu����
		var y_adjust = -68; // y���ʒu����
		if (this.popOBJ.style.visibility != "visible") {
			this.popOBJ.style.zIndex = this.zNum;
			if (document.all) { // IE�p
				var body = (document.compatMode=='CSS1Compat') ? document.documentElement : document.body;
				x = body.scrollLeft + event.clientX; // ���݂̃}�E�X�ʒu��X���W
				y = body.scrollTop + event.clientY; // ���݂̃}�E�X�ʒu��Y���W
				this.popOBJ.style.pixelLeft  = x + x_adjust; //�|�b�v�A�b�v�ʒu
				this.popOBJ.style.pixelTop  = y + y_adjust;
				
				if( (this.popOBJ.offsetTop + this.popOBJ.offsetHeight) > (body.scrollTop + body.clientHeight) ){
					this.popOBJ.style.pixelTop = body.scrollTop + body.clientHeight - this.popOBJ.offsetHeight -20;
				}
				if (this.popOBJ.offsetTop < body.scrollTop) {
					this.popOBJ.style.pixelTop = body.scrollTop -2;
				}
				
			} else if (document.getElementById) { // DOM�Ή��p�iMozilla�j
				x = ev.pageX; // ���݂̃}�E�X�ʒu��X���W
				y = ev.pageY; // ���݂̃}�E�X�ʒu��Y���W
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
			this.popOBJ.style.visibility = "visible"; //���X�|�b�v�A�b�v�\��
		}
    }
	ResPopUp.prototype.showResPopUp = ResPopUp_showResPopUp;
	
	//==================================================
	// hideResPopUp -- ���X�|�b�v�A�b�v���\���^�C�}�[����֐�
	//==================================================
	function ResPopUp_hideResPopUp(){
		this.timerID = setTimeout("hideResPopUp2('"+this.divID+"')", delaySec); //��莞�ԕ\�����������
	}
	ResPopUp.prototype.hideResPopUp = ResPopUp_hideResPopUp;

	//==================================================
	// hideResPopUp2 -- ���X�|�b�v�A�b�v���\���ɂ���֐�
	//==================================================
	function ResPopUp_hideResPopUp2(){

		for(i=0; i < POPS.length; i++){
		
			if(this.zNum < POPS[i].zNum){
				//clearTimeout(this.timerID); //�^�C�}�[������
				this.timerID = setTimeout("hideResPopUp('"+this.divID+"')", delaySec); //��莞�ԕ\�����������
				return;
			}
		}
		
		this.popOBJ.style.visibility = "hidden"; //���X�|�b�v�A�b�v��\��
		//clearTimeout(this.timerID); //�^�C�}�[������
		theResPopCtl.rmResPopUp(this.divID);
	}
	ResPopUp.prototype.hideResPopUp2 = ResPopUp_hideResPopUp2;
		
	return this;
}
