<?php
/*
	p2 ���O�C��

	�ŐV�X�V��: 2004/10/24
*/

require_once("./conf.php");  //��{�ݒ�
require_once("./filectl_class.inc");
require_once("./login.inc");

authorize(); //���[�U�F��

$_info_msg_ht="";

if(!$login['use']){
	die("p2 info: ���݁A���[�U�F�؂́u���p���Ȃ��v�ݒ�ɂȂ��Ă��܂��B<br>���̋@�\���Ǘ����邽�߂ɂ́A�܂� conf.php �Őݒ��L���ɂ��ĉ������B");
}

//=========================================================
// �O�u����
//=========================================================
regist_set_ktai($auth_ez_file, $auth_jp_file);
regist_set_cookie();

//=========================================================
// �����o���p�ϐ�
//=========================================================
$ptitle="p2�F�؃��[�U�Ǘ�";

$autho_user_ht="";
$auth_ctl_ht="";
$auth_sub_input_ht="";
$ivalue_user="";

if($ktai){
	$status_st="�ð��";
	$autho_user_st="�F��հ��";
	$client_host_st="�[��ν�";
	$client_ip_st="�[��IP���ڽ";
	$browser_ua_st="��׳��UA";
	$p2error_st="p2 �װ";
	
	$user_st="հ��";
	$password_st="�߽ܰ��";
}else{
	$status_st="�X�e�[�^�X";
	$autho_user_st="�F�؃��[�U";
	$client_host_st="�[���z�X�g";
	$client_ip_st="�[��IP�A�h���X";
	$browser_ua_st="�u���E�UUA";
	$p2error_st="p2 �G���[";
	
	$user_st="���[�U";
	$password_st="�p�X���[�h";
}


if($login['use']){
	$autho_user_ht="{$autho_user_st}: {$login['user']}<br>";
}

//�⏕�F��=====================================
//EZ�F��===============
if($_SERVER['HTTP_X_UP_SUBNO']){
	if( file_exists($auth_ez_file) ){
		$auth_ctl_ht=<<<EOP
EZ�[��ID�F�ؓo�^��[<a href="{$_SERVER['PHP_SELF']}?regist_ez=out{$k_at_a}">����</a>]<br>
EOP;
	}else{
		if($_SERVER['PHP_AUTH_USER']){
			$auth_ctl_ht=<<<EOP
[<a href="{$_SERVER['PHP_SELF']}?regist_ez=in{$k_at_a}">EZ�[��ID�ŔF�؂�o�^</a>]<br>
EOP;
		}
		$auth_sub_input_ht=<<<EOP
	<input type="checkbox" name="regist_ez" value="in" checked>EZ�[��ID�ŔF�؂�o�^<br>
EOP;
	}

// J�F�� ================
} elseif (preg_match('{(J-PHONE|Vodafone)/([^/]+?/)+?SN(.+?) }', $_SERVER['HTTP_USER_AGENT'], $matches)) {
	if (file_exists($auth_jp_file)) {
		$auth_ctl_ht=<<<EOP
J�[��ID�F�ؓo�^��[<a href="{$_SERVER['PHP_SELF']}?regist_jp=out{$k_at_a}">����</a>]<br>
EOP;
	} else {
		if ($_SERVER['PHP_AUTH_USER']) {
			$auth_ctl_ht = <<<EOP
[<a href="{$_SERVER['PHP_SELF']}?regist_jp=in{$k_at_a}">J�[��ID�ŔF�؂�o�^</a>]<br>
EOP;
		}
		$auth_sub_input_ht = <<<EOP
	<input type="checkbox" name="regist_jp" value="in" checked>J�[��ID�ŔF�؂�o�^<br>
EOP;
	}
	
//Cookie�F��================
}else{
	if( ($_COOKIE["p2_user"]==$login['user']) && ($_COOKIE["p2_pass"] == $login['pass'])){
			$auth_cookie_ht = <<<EOP
cookie�F�ؓo�^��[<a href="cookie.php?regist_cookie=out{$k_at_a}">����</a>]<br>
EOP;
	}else{
		if($_SERVER['PHP_AUTH_USER']){
			$auth_cookie_ht = <<<EOP
[<a href="cookie.php?regist_cookie=in{$k_at_a}">cookie�ŔF�؂�o�^</a>]<br>
EOP;
		}
	}
	$auth_sub_input_ht = <<<EOP
	<input type="checkbox" name="regist_cookie" value="in" checked>cookie�ɕۑ�����<br>
EOP;
}

// Cookie�F�؃`�F�b�N ====================================
if ($_GET['regist_cookie_check']) {
	if (($_COOKIE["p2_user"] == $login['user']) && ($_COOKIE["p2_pass"] == $login['pass'])) {
		if($_GET['regist_cookie_check']=="in"){
			$_info_msg_ht .= "<p>��cookie�F�ؓo�^����</p>";
		}elseif($_GET['regist_cookie_check']=="out"){
			$_info_msg_ht .= "<p>�~cookie�F�؉������s</p>";
		}
	}else{
		if($_GET['regist_cookie_check']=="out"){
			$_info_msg_ht .= "<p>��cookie�F�؉�������</p>";
		}elseif($_GET['regist_cookie_check']=="in"){
			$_info_msg_ht .= "<p>�~cookie�F�ؓo�^���s</p>";
		}
	}
}


// �F�؃��[�U�ݒ�ǂݍ���========
if( file_exists($auth_user_file) ){
	include($auth_user_file);	
	if( isset($login['user']) ){
		$ivalue_user=$login['user'];
	}
}
if( isset($_POST['login_user']) ){
	$ivalue_user=$_POST['login_user'];
}
	
// �F�؃��[�U�o�^�t�H�[��================
$login_form_ht =<<<EOP
<form id="login_change" method="POST" action="{$_SERVER['PHP_SELF']}" target="_self">
	�F��{$user_st}����{$password_st}�̕ύX<br>
	{$k_input_ht}
	{$user_st}: <input type="text" name="login_user" value="{$ivalue_user}"><br>
	{$password_st}: <input type="password" name="login_pass"><br>
	{$auth_sub_input_ht}
	<br>
	<input type="submit" name="submit" value="�ύX�o�^">
</form>\n
EOP;


//���[�U�o�^����=================================
if ($_POST['login_user'] && $_POST['login_pass']) {

	if( isStrInvalid($_POST['login_user']) || isStrInvalid($_POST['login_pass']) ){
		$_info_msg_ht.="<p>p2 error: {$user_st}����{$password_st}�͔��p�p�����œ��͂��ĉ������B</p>";

	}else{
		$crypted_login_pass = crypt($_POST['login_pass'], $_POST['login_pass']);
		$auth_user_cont =<<<EOP
<?php
\$login['user'] = '{$_POST["login_user"]}';
\$login['pass'] = '{$crypted_login_pass}';
?>
EOP;
		FileCtl::make_datafile($auth_user_file, $pass_perm); //$auth_user_file ���Ȃ���ΐ���
		$fp = @fopen($auth_user_file,"w") or die("p2 Error: $auth_user_file ��ۑ��ł��܂���ł����B�F�؃��[�U�o�^���s�B");
		fputs($fp, $auth_user_cont);
		fclose($fp);
		
		$_info_msg_ht.="<p>���F��{$user_st}�u{$_POST['login_user']}�v��o�^���܂���</p>";
	}
	
}else{
	
	if($_POST['login_user'] || $_POST['login_pass']){
		if(!$_POST['login_user']){
			$_info_msg_ht.="<p>p2 error: {$user_st}�������͂���Ă��܂���B</p>";
		}elseif(!$_POST['login_pass']){
			$_info_msg_ht.="<p>p2 error: {$password_st}�����͂���Ă��܂���B</p>";
		}
	}
	
}

$body_onload="";
if(!$ktai){
	$body_onload=" onLoad=\"setWinTitle();\"";
}

//=========================================================
// HTML�v�����g
//=========================================================
header_nocache();
header_content_type();
if($doctype){ echo $doctype;}
echo <<<EOP
<html>
<head>
	<meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Style-Type" content="text/css">
	<meta http-equiv="Content-Script-Type" content="text/javascript">
	<title>{$ptitle}</title>
EOP;
if(!$ktai){
	@include("./style/style_css.inc");
	@include("./style/login_css.inc");
	echo <<<EOP
	<script type="text/javascript" src="{$basic_js}"></script>
EOP;
}
echo <<<EOP
</head>
<body{$body_onload}>
EOP;

if(!$ktai){
	echo <<<EOP
<p id="pan_menu"><a href="setting.php">�ݒ�</a> &gt; {$ptitle}</p>
EOP;
}

echo $_info_msg_ht;
$_info_msg_ht="";
	
echo "<p id=\"login_status\">";
echo <<<EOP
{$autho_user_ht}
{$auth_ctl_ht}
{$auth_cookie_ht}
EOP;
echo "</p>";

if($ktai){
	echo "<hr>";
}

echo $login_form_ht;

if($ktai){
	echo "<hr>\n";
	echo $k_to_index_ht;
}

echo <<<EOP
</body>
</html>
EOP;

?>
