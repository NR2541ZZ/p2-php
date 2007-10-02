<?php
// �O���[�o���֐�

/**
 * htmlspecialchars() �̕ʖ��݂����Ȃ���
 *
 * @param   string  $alt  �l����̂Ƃ��̑�֕�����
 * @return  string|null
 */
function hs($str, $alt = '', $quoteStyle = ENT_QUOTES)
{
    return (isset($str) && strlen($str) > 0) ? htmlspecialchars($str, $quoteStyle) : $alt;
}

/**
 * notice �̗}�������Ă���� hs()
 * �Q�ƂŒl���󂯎��̂̓C�}�C�`�����A�������Ȃ����notice�̗}�����ł��Ȃ�
 *
 * @param   &string  $str  ������ϐ��̎Q��
 * @return  string|null
 */
function hsi(&$str, $alt = '', $quoteStyle = ENT_QUOTES)
{
    return (isset($str) && strlen($str) > 0) ? htmlspecialchars($str, $quoteStyle) : $alt;
}

/**
 * echo hs()
 *
 * @return  void
 */
function eh($str, $alt = '', $quoteStyle = ENT_QUOTES)
{
    echo hs($str, $alt, $quoteStyle);
}

/**
 * echo hs() �inotice��}������j
 *
 * @param   &string  $str  ������ϐ��̎Q��
 * @return  void
 */
function ehi(&$str, $alt = '', $quoteStyle = ENT_QUOTES)
{
    echo hs($str, $alt, $quoteStyle);
}

/**
 * ���݂��Ȃ��ϐ��� notice ���o�����ƂȂ��A�ϐ��̒l���擾����
 *
 * @return  mixed
 */
function geti(&$var, $alt = null)
{
    return isset($var) ? $var : $alt;
}

/**
 * ���s��t���ĕ�������o�͂���Bcli(\n)��web(<br>)�ŏo�͂��ω�����B
 * �����̕�����͕�����邱�Ƃ��\�B�������Ȃ���Ή��s�������o�͂���B
 *
 * @return  void
 */
function echoln()
{
    $n = (php_sapi_name() == 'cli') ? "\n" : '<br>';
    
    if ($args = func_get_args()) {
        foreach ($args as $v) {
            echo $v . $n;
        }
    } else {
        echo $n;
    }
}

/**
 * p2 error ���b�Z�[�W��\�����ďI��
 *
 * @param   string  $err    �G���[�T�v
 * @param   string  $msg    �ڍׂȐ���
 * @param   boolean $raw    �ڍׂȐ������G�X�P�[�v����Ȃ�true
 * @return  void
 */
function p2die($err, $msg = null, $raw = false)
{
    echo '<html><head><title>p2 error</title></head><body>';
    printf('<h4>p2 error: %s</h4>', htmlspecialchars($err, ENT_QUOTES));
    if ($msg !== null) {
        if ($raw) {
            printf('<p>%s</p>', nl2br(htmlspecialchars($msg, ENT_QUOTES)));
        } else {
            echo $msg;
        }
    }
    echo '</body></html>';
    
    exit;
}
