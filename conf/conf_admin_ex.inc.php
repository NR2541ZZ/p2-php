<?php
/*
    rep2expack - �g���p�b�N�@�\�� On/Off �ƃ��[�U�ݒ�ҏW�y�[�W����ύX�����Ȃ��ݒ�

    ���̃t�@�C���̐ݒ�́A�K�v�ɉ����ĕύX���Ă�������
*/

// ----------------------------------------------------------------------
// {{{ �S��

// ImageCache2 ���Ńt�@�C���������[�g����擾����ۂ� User-Agent
$_conf['expack.user_agent'] = ""; // ("")

// }}}
// ----------------------------------------------------------------------
// {{{ �X�L��

// �X�L���ioff:0, on:1�j
$_conf['expack.skin.enabled'] = 1; // (1)

// �ݒ�t�@�C���̃p�X
$_conf['expack.skin.setting_path'] = $_conf['pref_dir'].'/p2_user_skin.txt';

// �ݒ�t�@�C���̃p�[�~�b�V����
$_conf['expack.skin.setting_perm'] = 0606; // (0606)

// �t�H���g�ݒ�t�@�C���̃p�X
$_conf['expack.skin.fontconfig_path'] = $_conf['pref_dir'].'/p2_user_font.txt';

// �t�H���g�ݒ�t�@�C���̃p�[�~�b�V����
$_conf['expack.skin.fontconfig_perm'] = 0606; // (0606)

// }}}
// ----------------------------------------------------------------------
// {{{ tGrep

// �ꔭ�������X�g�̃p�X
$_conf['expack.tgrep.quick_file'] = $_conf['pref_dir'].'/p2_tgrep_quick.txt';

// �����������X�g�̃p�X
$_conf['expack.tgrep.recent_file'] = $_conf['pref_dir'].'/p2_tgrep_recent.txt';

// �t�@�C���̃p�[�~�b�V����
$_conf['expack.tgrep.file_perm'] = 0606; // (0606)

// }}}
// ----------------------------------------------------------------------
// {{{ �X�}�[�g�|�b�v�A�b�v���j���[

// SPM�ioff:0, on:1�j
$_conf['expack.spm.enabled'] = 1; // (1)

// }}}
// ----------------------------------------------------------------------
// {{{ �A�N�e�B�u���i�[

// AA �␳�ioff:0, on:1�j
$_conf['expack.am.enabled'] = 0; // (0)

// }}}
// ----------------------------------------------------------------------
// {{{ ���͎x��

// ActiveMona �ɂ�� AA �v���r���[�ioff:0, on:1�j
$_conf['expack.editor.with_activemona'] = 0; // (0)

// AAS �ɂ�� AA �v���r���[�ioff:0, on:1�j
$_conf['expack.editor.with_aas'] = 0; // (0)

// }}}
// ----------------------------------------------------------------------
// {{{ RSS���[�_

// RSS���[�_�ioff:0, on:1�j
$_conf['expack.rss.enabled'] = 0; // (0)

// �ݒ�t�@�C���̃p�X
$_conf['expack.rss.setting_path'] = $_conf['pref_dir'].'/p2_rss.txt';

// �ݒ�t�@�C���̃p�[�~�b�V����
$_conf['expack.rss.setting_perm'] = 0606; // (0606)

// ImageCache2���g���ă����N���ꂽ�摜���L���b�V������ioff:0, on:1�j
$_conf['expack.rss.with_imgcache'] = 0; // (0)

// }}}
// ----------------------------------------------------------------------
// {{{ ImageCache2

/*
 * ���̋@�\���g���ɂ�PHP��GD�@�\�g���܂���ImageMagick��
 * SQLite, PostgreSQL, MySQL�̂����ꂩ���K�v�B
 * ���p�ɓ������Ă� doc/ImageCache2/README.txt �� doc/ImageCache2/INSTALL.txt ��
 * �悭�ǂ�ŁA����ɏ]���Ă��������B
 */

// ImageCache2�ioff:0, PC�̂�:1, �g�т̂�:2, ����:3�j
$_conf['expack.ic2.enabled'] = 0; // (0)

// }}}
// ----------------------------------------------------------------------
// {{{ Google����

// Google�����ioff:0, on:1�j
$_conf['expack.google.enabled'] = 0; // (0)

// WSDL �̃p�X�i��F/path/to/googleapi/GoogleSearch.wsdl�j
$_conf['expack.google.wsdl'] = "./conf/GoogleSearch.wsdl"; // ("./conf/GoogleSearch.wsdl")

// }}}
// ----------------------------------------------------------------------
// {{{ AAS

// AAS�ioff:0, on:1�j
$_conf['expack.aas.enabled'] = 0; // (0)

//TrueType�t�H���g�̃p�X
$_conf['expack.aas.font_path'] = "./ttf/mona.ttf"; // ("./ttf/mona.ttf")

// ���l�Q�Ƃ̃f�R�[�h�Ɏ��s�����Ƃ��̑�֕���
$_conf['expack.aas.unknown_char'] = "?"; // ("?")

// �t�H���g�`�揈���̕����R�[�h
// "eucJP-win" �ł� configure �̃I�v�V������ --enable-gd-native-ttf ���w�肳��Ă��Ȃ��ƕ�����������
// ���̂Ƃ� Unicode �Ή��t�H���g���g���Ă���Ȃ� "UTF-8" �ɂ���Ɛ������\���ł���
$_conf['expack.aas.output_charset'] = "eucJP-win"; // ("eucJP-win")

// }}}
// ----------------------------------------------------------------------
// {{{ ���̑�

// ���C�ɃZ�b�g�؂�ւ��ioff:0, on:1�j
// �ȉ��̒l���ʂɐݒ肷��K�v����
// - favlist_set_num (���C�ɃX��)
// - favita_set_num (���C�ɔ�)
// - expack.rss.set_num (RSS)
$_conf['expack.favset.enabled'] = 0; // (0)

// ���C�ɃZ�b�g�������L�^����t�@�C���̃p�X
$_conf['expack.favset.namefile'] = $_conf['pref_dir'] . '/p2_favset.txt';

// �J�e�S��or���C�ɔ̃X���ꗗ���}�[�W���ĕ\������Ƃ��A
// �ǂݍ��ޔ��̍ő�l
// �\�[�g���Ɋ֌W�Ȃ��A���j���[�ɐ�ɏo��������̂���J�E���g�����
$_conf['expack.mergedlist.max_boards'] = 10;

// �J�e�S��or���C�ɔ̃X���ꗗ���}�[�W���ĕ\������Ƃ��A
// �ǂݍ��ރX���b�h���̍ő�l
// �\�[�g���Ɋ֌W�Ȃ��Asubject.txt �ɐ�ɏo��������̂���J�E���g�����
$_conf['expack.mergedlist.max_threads'] = 2000;

// �J�e�S��or���C�ɔ̃X���ꗗ���}�[�W���ĕ\������Ƃ��A
// �ЂƂ̔���ǂݍ��ރX���b�h���̍ő�l
// �\�[�g���Ɋ֌W�Ȃ��Asubject.txt �ɐ�ɏo��������̂���J�E���g�����
$_conf['expack.mergedlist.max_threads_per_board'] = 200;

// }}}
// ----------------------------------------------------------------------

/*
 * Local Variables:
 * mode: php
 * coding: cp932
 * tab-width: 4
 * c-basic-offset: 4
 * indent-tabs-mode: nil
 * End:
 */
// vim: set syn=php fenc=cp932 ai et ts=4 sw=4 sts=4 fdm=marker:
