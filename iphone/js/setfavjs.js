////
// ���C�ɃZ�b�g�֐�
// setFavJs('host={$aThread->host}{$bbs_q}{$key_q}{$ttitle_en_q}{$sid_q}', '{$favdo}',{$STYLE['info_pop_size']}, this);
//
function setFavJs(tquery, favdo, info_pop_width, info_pop_height, page, obj)
{
	// read.php�ł́A�y�[�W�̓ǂݍ��݂��������Ă��Ȃ���΁A�Ȃɂ����Ȃ�
	// �iread.php �͓ǂݍ��݊�������idx�L�^����������邽�߁j
	if ((page == 'read') && !gIsPageLoaded) {
		return false;
	}
	
	var objHTTP = getXmlHttp();
	if (!objHTTP) {
		// alert("Error: XMLHTTP �ʐM�I�u�W�F�N�g�̍쐬�Ɏ��s���܂����B") ;
		// XMLHTTP�i��innerHTML�j �ɖ��Ή��Ȃ珬����
		infourl = 'info.php?' + tquery + '&setfav=' + favdo + '&popup=2';
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
				favmark = '��';
				favtitle = '���C�ɃX������O��';
			} else {
				nextset = '1';
				favmark = '+';
				favtitle = '���C�ɃX���ɒǉ�';
			}
			if (obj.className) {
				objClass = ' class="' + obj.className + '"';
			} else {
				objClass = '';
			}
			if (page != 'subject') {
				favstr = '���C��';
			} else {
				favstr = '';
			}
			var favhtm = '<a' + objClass + ' href="info_i.php?' + tquery + '&amp;setfav=' + nextset + '" target="info" onClick="return setFavJs(\'' + tquery + '\', \''+nextset+'\', '+info_pop_width+', '+info_pop_height+', \'' + page + '\', this);" title="' + favtitle + '">' + favmark + '</a>';
			if (page != 'read') {
				obj.parentNode.innerHTML = favhtm;
			} else {
				var span = document.getElementsByTagName('span');
				for (var i = 0; i < span.length; i++) {
					if (span[i].className == 'favdo') {
						span[i].innerHTML = favhtm;
					}
				}
			}
		}
	}
	return false;
}
