// --------------------------------------------------------------
// "���C��" �̕����񖳂��� �{�����̂ݔ�
//-------------------------------------------------------------------
function setFavJsNoStr(tquery, favdo, info_pop_width, info_pop_height, page, obj,numb)
{
	// read.php�ł́A�y�[�W�̓ǂݍ��݂��������Ă��Ȃ���΁A�Ȃɂ����Ȃ�
	// �iread.php �͓ǂݍ��݊�������idx�L�^����������邽�߁j
	if ((page == 'subject') && !gIsPageLoaded) {
		return false;
	}
	
	var objHTTP = getXmlHttp();
	if (!objHTTP) {
		// alert("Error: XMLHTTP �ʐM�I�u�W�F�N�g�̍쐬�Ɏ��s���܂����B") ;
		// XMLHTTP�i��innerHTML�j �ɖ��Ή��Ȃ珬����
		infourl = 'info_i.php?' + tquery + '&setfav=' + favdo + '&popup=2';
		return !openSubWin(infourl,info_pop_width,info_pop_height,0,0);
	}

	url = 'httpcmd.php?' + tquery + '&setfav=' + favdo + '&cmd=setfav'; // �X�N���v�g�ƁA�R�}���h�w��

	var res = getResponseTextHttp(objHTTP, url, 'nc');
	var rmsg = "";
	if (res) {
		if (res == '1') {
			rmsg = '����';
		}
		if (rmsg) {
			if (favdo == '1') {
				nextset = '0';
				favmark = '<img src="iui/icon_del.png">';
				favtitle = '���C�ɃX������O��';
			} else {
				nextset = '1';
				favmark = '<img src="iui/icon_add.png">';
				favtitle = '���C�ɃX���ɒǉ�';
			}
			if (obj.className) {
				objClass = ' class="' + obj.className + '"';
			} else {
				objClass = '';
			}
			if (page != 'subject') {
				favstr = '';
			} else {
				favstr = '';
			}
			var favhtm = '<a id="'+numb+'"' + objClass + ' href="info_i.php?' + tquery + '&amp;setfav=' + nextset + '" target="info" onClick="return setFavJsNoStr(\'' + tquery + '\', \''+nextset+'\', '+info_pop_width+', '+info_pop_height+', \'' + page + '\', this, \''+numb+ '\');" title="' + favtitle + '">' + favstr + favmark + '</a>';
			if (page != 'read') {
				obj.parentNode.innerHTML = favhtm;
			} else {
				var span = document.getElementsByTagName('span');
				for (var i = 0; i < span.length; i++) {
					if (span[i].className == 'plus' && span[i].id == numb) {
						span[i].innerHTML = favhtm;
					}
				}
			}
		}
	}
	return false;
}
