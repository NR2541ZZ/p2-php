////
// �폜�֐�
// deleLog('host={$aThread->host}&bbs={$aThread->bbs}&key={$aThread->key}{$ttitle_en_q}{$sid_q}', {$STYLE['info_pop_size']}, 'read', this);
//
function deleLog(tquery, info_pop_width, info_pop_height, page, obj)
{
	// read.php�ł́A�y�[�W�̓ǂݍ��݂��������Ă��Ȃ���΁A�Ȃɂ����Ȃ�
	// �iread.php �͓ǂݍ��݊�������idx�L�^����������邽�߁j
	if ((page == 'read') && !gIsPageLoaded) {
		return true;
	}

	var xmlHttpObj = getXmlHttp();

	if (!xmlHttpObj) {
		// alert("Error: XMLHTTP �ʐM�I�u�W�F�N�g�̍쐬�Ɏ��s���܂����B") ;
		// XMLHTTP�i�� obj.parentNode.innerHTML�j �ɖ��Ή��Ȃ珬����
		infourl = 'info.php?' + tquery + '&popup=2&dele=true';
		return openSubWin(infourl,info_pop_width,info_pop_height,0,0);
	}

	var url = 'httpcmd.php?' + tquery + '&cmd=delelog'; // �X�N���v�g�ƁA�R�}���h�w��
	
	var func = function(xobj){
		var rmsg = '';
		var res = xmlHttpObj.responseText.replace(/^<\?xml .+?\?>\n?/, '');;
		if (res == '1') {
			rmsg = (page == 'subject') ? '��' : '����';
		} else if (res == '2') {
			rmsg = (page == 'subject') ? '��' : '����';
		}
		if (rmsg) {
			// Gray() �� IE ActiveX�p
			if (page == 'read_new') {
				obj.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.style.filter = 'Gray()';
			} else if (page == 'read') {
				document.body.style.filter = 'Gray()';
			}
			obj.parentNode.innerHTML = rmsg;
		}
	};

	obj.style.color = 'gray';
	getResponseTextHttp(xmlHttpObj, url, 'nc', true, func);

	return true;
}
