/* p2 - HTML���|�b�v�A�b�v���邽�߂�JavaScript */

//showHtmlDelaySec = 0.2 * 1000; //HTML�\���f�B���C�^�C���B�}�C�N���b�B

showHtmlTimerID=0;
node_div=false;
node_close=false;
tUrl=""; //URL�e���|�����ϐ�
gUrl=""; //URL�O���[�o���ϐ�
gX=0;
gY=0;
ecX=0;
ecY=0;

//==============================================================
// showHtmlPopUp -- HTML�v�A�b�v��\������֐�
// ���p���X�Ԃ� onMouseover �ŌĂяo�����
//==============================================================

function showHtmlPopUp(url,ev,showHtmlDelaySec){
	if(! document.createElement){return;} //DOM��Ή�
	
	showHtmlDelaySec = showHtmlDelaySec * 1000;

	if(! node_div || url!=gUrl){
		tUrl=url;
		gX=ev.pageX;
		gY=ev.pageY;
		if(document.all){ //IE
			ecX = event.clientX;
			ecY = event.clientY;
		}
		showHtmlTimerID = setTimeout("showHtmlPopUpDo()", showHtmlDelaySec); //HTML�\���f�B���C�^�C�}�[
	}
}

function showHtmlPopUpDo(){

	hideHtmlPopUp();

	gUrl=tUrl;
	var x_adjust=7; //x���ʒu����
	var y_adjust=-46;//y���ʒu����
	var closebox_width=18;
	
	if(! node_div){
		node_div=document.createElement('div');
		node_div.setAttribute('id', "iframespace");

		node_close=document.createElement('div');
		node_close.setAttribute('id', "closebox");
		//node_close.setAttribute('onMouseover', "hideHtmlPopUp()");

		if(document.all){ //IE�p
			var body = (document.compatMode=='CSS1Compat') ? document.documentElement : document.body;
			gX = body.scrollLeft+ecX; //���݂̃}�E�X�ʒu��X���W
			gY = body.scrollTop+ecY; //���݂̃}�E�X�ʒu��Y���W
			node_div.style.pixelLeft  = gX + x_adjust; //�|�b�v�A�b�v�ʒu
			node_div.style.pixelTop  = body.scrollTop; //gY + y_adjust;
			var cX = gX + x_adjust - closebox_width;
			node_close.style.pixelLeft  = cX; //�|�b�v�A�b�v�ʒu
			node_close.style.pixelTop  = body.scrollTop; //gY + y_adjust;
			var yokohaba = body.clientWidth - node_div.style.pixelLeft -20; //�������t
			var tatehaba = body.clientHeight -20;
			
		}else if(document.getElementById){ //DOM�Ή��p�iMozilla�j
			node_div.style.left = gX + x_adjust + "px"; //�|�b�v�A�b�v�ʒu
			node_div.style.top = window.pageYOffset + "px"; //gY + y_adjust + "px";
			var cX = gX + x_adjust - closebox_width;
			node_close.style.left = cX + "px"; //�|�b�v�A�b�v�ʒu
			node_close.style.top = window.pageYOffset + "px"; //gY + y_adjust + "px";
			var yokohaba = window.innerWidth - gX - x_adjust -20; //�������t
			var tatehaba = window.innerHeight -20;
		}

		pageMargin="";
		if( gUrl.match(/(jpg|jpeg|gif|png)$/) ){ //�摜�̏ꍇ�̓}�[�W�����[����
			pageMargin=" marginheight=\"0\" marginwidth=\"0\" hspace=\"0\" vspace=\"0\"";
		}
		node_div.innerHTML = "<iframe src=\""+gUrl+"\" frameborder=\"1\" border=\"1\" style=\"background-color:#fff;\" width=" + yokohaba + " height=" + tatehaba + pageMargin +">&nbsp;</iframe>";
		
		node_close.innerHTML = "<b onMouseover=\"hideHtmlPopUp()\">�~</b>";
		
		document.body.appendChild(node_div);
		document.body.appendChild(node_close);
	}
}

//==============================================================
// hideHtmlPopUp -- HTML�|�b�v�A�b�v���\���ɂ���֐�
// ���p���X�Ԃ��� onMouseout �ŌĂяo�����
//==============================================================

function hideHtmlPopUp(){

	if(! document.createElement){return;} //DOM��Ή�
	if(showHtmlTimerID){clearTimeout(showHtmlTimerID);} //HTML�\���f�B���C�^�C�}�[������
	if(node_div){
		node_div.style.visibility = "hidden";
		document.body.removeChild(node_div);
		node_div=false;
	}
	if(node_close){
		node_close.style.visibility = "hidden";
		document.body.removeChild(node_close);
		node_close=false;
	}
}

//==============================================================
// HTML�\���^�C�}�[����������֐�
//==============================================================
function offHtmlPopUp(){
	if(showHtmlTimerID){clearTimeout(showHtmlTimerID);} //HTML�\���f�B���C�^�C�}�[������
}


