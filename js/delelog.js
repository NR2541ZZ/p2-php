////
// �폜�֐�
// deleLog('host={$aThread->host}&bbs={$aThread->bbs}&key={$aThread->key}{$ttitle_en_q}{$sid_q}', {$STYLE['info_pop_size']}, 'read', this);
//
function deleLog(tquery, info_pop_width, info_pop_height, page, obj)
{
	// read.php�ł́A�y�[�W�̓ǂݍ��݂��������Ă��Ȃ���΁A�Ȃɂ����Ȃ�
	// �iread.php �͓ǂݍ��݊�������idx�L�^����������邽�߁j
	if ((page == 'read') && !gIsPageLoaded) {
		return false;
	}
	
	var xmlHttpObj = getXmlHttp();
	
	if (!xmlHttpObj) {
		// alert("Error: XMLHTTP �ʐM�I�u�W�F�N�g�̍쐬�Ɏ��s���܂����B") ;
		// XMLHTTP�i�� obj.parentNode.innerHTML�j �ɖ��Ή��Ȃ珬����
		infourl = 'info.php?' + tquery + '&popup=2&dele=true';
		return !openSubWin(infourl,info_pop_width,info_pop_height,0,0);
	}

	var url = 'httpcmd.php?' + tquery + '&cmd=delelog'; // �X�N���v�g�ƁA�R�}���h�w��
	
	var res = getResponseTextHttp(xmlHttpObj, url, 'nc');
	var rmsg = "";
	if (res) {
		if (res == '1') {
			if (page == 'subject') {
				rmsg = '��';
			} else {
				rmsg = '����';
			}
		} else if (res == '2') {
			if (page == 'subject') {
				rmsg = '��';
			} else {
				rmsg = '����';
			}
		}
		if (rmsg) {
			if (page == 'read_new') {
				obj.parentNode.parentNode.parentNode.parentNode.parentNode.parentNode.style.filter = 'Gray()'; // IE ActiveX�p
			} else if (page == 'read') {
				document.body.style.filter = 'Gray()'; // IE ActiveX�p
			}
			obj.parentNode.innerHTML = rmsg;
		}
	}
	return false;
}
