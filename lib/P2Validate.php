<?php
/**
 * static�᥽�åɤ����Ѥ���
 *
 * @created  2007/10/03
 */
class P2Validate
{
    /**
     * @static
     * @access  public
     * @return  string|null  �����ʤ饨�顼��å��������֤�
     */
    function host($str)
    {
        if (!preg_match('{^[\\w\\-./:@~]+$}', $str)) {
            return sprintf('validation error: %s', __FUNCTION__);
        }
        return null;
    }
    
    /**
     * @static
     * @access  public
     * @return  string|null  �����ʤ饨�顼��å��������֤�
     */
    function bbs($str)
    {
        if (!preg_match('{^[\\w\\-]+$}', $str)) {
            return sprintf('validation error: %s', __FUNCTION__);
        }
        return null;
    }
    
    /**
     * @static
     * @access  public
     * @return  string|null  �����ʤ饨�顼��å��������֤�
     */
    function key($str)
    {
        if (!preg_match('{^[\\w]+$}', $str)) {
            return sprintf('validation error: %s', __FUNCTION__);
        }
        return null;
    }
    
    /**
     * @static
     * @access  public
     * @return  string|null  �����ʤ饨�顼��å��������֤�
     */
    function spmode($str)
    {
        if (!preg_match('{^[\\w]+$}', $str)) {
            return sprintf('validation error: %s', __FUNCTION__);
        }
        return null;
    }
    
    /**
     * @static
     * @access  public
     * @return  string|null  �����ʤ饨�顼��å��������֤�
     */
    function mail($str)
    {
        if (!preg_match("/^[_a-z0-9-]+([._a-z0-9-]+)@[a-z0-9-]+(\\.[a-z0-9-]+)*(\\.[a-z]{2,})$/i", $str)) {
            return sprintf('validation error: %s', __FUNCTION__);
        }
        return null;
    }
    
    /**
     * @static
     * @access  public
     * @return  string|null  �����ʤ饨�顼��å��������֤�
     */
    function login2chPW($str)
    {
        // ���Τʵ���ʸ���������
        if (!preg_match('~^[\\w.,@:/+-]+$~', $str)) {
            return sprintf('validation error: %s', __FUNCTION__);
        }
        return null;
    }
}
