function setFocus(ID){
	if (obj = document.getElementById(ID)) {
		if (obj.disabled != true) {
			obj.focus();
		}
	}
}

// sage�`�F�b�N�ɍ��킹�āA���[�����̓��e������������
function mailSage(){
	if (cbsage = document.getElementById('sage')) {
		if (mailran = document.getElementById('mail')) {
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

// ���[�����̓��e�ɉ����āAsage�`�F�b�N��ON OFF����
function checkSage(){
	if (mailran = document.getElementById('mail')) {
		if (cbsage = document.getElementById('sage')) {
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

// �O��̏������ݓ��e�𕜋A����
function loadLastPosted(from, mail, message){
	if (fromran = document.getElementById('FROM')) {
		fromran.value = from;
	}
	if (mailran = document.getElementById('mail')) {
		mailran.value = mail;
	}
	if (messageran = document.getElementById('MESSAGE')) {
		messageran.value = message;
	}
	checkSage();
}
*/
