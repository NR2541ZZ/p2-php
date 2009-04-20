
// �Ώ�id��HTML�v�f��\��or��\������
// @param  string  hiddenClassName  ���X�B����Ă���ꍇ�̑ΏۃN���X��
function showHide(id, hiddenClassName)
{
	if (typeof(id) == 'string') {
		var obj = document.getElementById(id)
	} else {
		var obj = id;
	}

	if (obj.style.display == 'block') {
		obj.style.display = "none";
	} else if(obj.style.display == 'none') {
		obj.style.display = "block";
	} else {
		if (hiddenClassName && obj.className != hiddenClassName) {
			obj.style.display = "none";
		} else {
			obj.style.display = "block";
		}
	}
	return false;
}
