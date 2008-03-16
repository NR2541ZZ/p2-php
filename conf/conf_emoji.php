<?php
/*
    rep2expack - �G�����ݒ�t�@�C��
*/

require_once 'Net/UserAgent/Mobile.php';

function getEmoji()
{
    $mobile = &Net_UserAgent_Mobile::singleton();
    $emoji = array();

    $emoji['ut1'] = '��';
    $emoji['ut2'] = '��';
    $emoji['dt1'] = '��';
    $emoji['dt2'] = '��';
    $emoji['lt1'] = '&lt;';
    $emoji['lt2'] = '&laquo;';
    $emoji['rt1'] = '&gt;';
    $emoji['rt2'] = '&raquo;';
    $emoji[1] = '1.';
    $emoji[2] = '2.';
    $emoji[3] = '3.';
    $emoji[4] = '4.';
    $emoji[5] = '5.';
    $emoji[6] = '6.';
    $emoji[7] = '7.';
    $emoji[8] = '8.';
    $emoji[9] = '9.';
    $emoji[0] = '0.';

    if ($mobile->isDoCoMo()) {
        $emoji['lt2'] = '&lt;&lt;';
        $emoji['rt2'] = '&gt;&gt;';
        $emoji[1] = pack('C*', 0xF9, 0x87);
        $emoji[2] = pack('C*', 0xF9, 0x88);
        $emoji[3] = pack('C*', 0xF9, 0x89);
        $emoji[4] = pack('C*', 0xF9, 0x8A);
        $emoji[5] = pack('C*', 0xF9, 0x8B);
        $emoji[6] = pack('C*', 0xF9, 0x8C);
        $emoji[7] = pack('C*', 0xF9, 0x8D);
        $emoji[8] = pack('C*', 0xF9, 0x8E);
        $emoji[9] = pack('C*', 0xF9, 0x8F);
        $emoji[0] = pack('C*', 0xF9, 0x90);
    } elseif ($mobile->isEZweb()) {
        $emoji['ut1'] = '<img localsrc="33">';
        $emoji['ut2'] = '<img localsrc="35">';
        $emoji['dt1'] = '<img localsrc="32">';
        $emoji['dt2'] = '<img localsrc="34">';
        $emoji['lt1'] = '<img localsrc="5">';
        $emoji['lt2'] = '<img localsrc="7">';
        $emoji['rt1'] = '<img localsrc="6">';
        $emoji['rt2'] = '<img localsrc="8">';
        $emoji[1] = '<img localsrc="180">';
        $emoji[2] = '<img localsrc="181">';
        $emoji[3] = '<img localsrc="182">';
        $emoji[4] = '<img localsrc="183">';
        $emoji[5] = '<img localsrc="184">';
        $emoji[6] = '<img localsrc="185">';
        $emoji[7] = '<img localsrc="186">';
        $emoji[8] = '<img localsrc="187">';
        $emoji[9] = '<img localsrc="188">';
        $emoji[0] = '<img localsrc="325">';
    } elseif ($mobile->isVodafone()) {
        $_esc = chr(0x1B) . '$';
        $_si = chr(0x0F);
        $emoji['lt1'] = $_esc . 'F[' . $_si;
        $emoji['lt2'] = $_esc . 'F]' . $_si;
        $emoji['rt1'] = $_esc . 'FZ' . $_si;
        $emoji['rt2'] = $_esc . 'F\\' . $_si;
        $emoji[1] = $_esc . 'F<' . $_si;
        $emoji[2] = $_esc . 'F=' . $_si;
        $emoji[3] = $_esc . 'F>' . $_si;
        $emoji[4] = $_esc . 'F?' . $_si;
        $emoji[5] = $_esc . 'F@' . $_si;
        $emoji[6] = $_esc . 'FA' . $_si;
        $emoji[7] = $_esc . 'FB' . $_si;
        $emoji[8] = $_esc . 'FC' . $_si;
        $emoji[9] = $_esc . 'FD' . $_si;
        $emoji[0] = $_esc . 'FE' . $_si;
    }

    return $emoji;
}

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
