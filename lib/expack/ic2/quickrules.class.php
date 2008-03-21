<?php
/* vim: set fileencoding=cp932 ai et ts=4 sw=4 sts=4 fdm=marker: */
/* mi: charset=Shift_JIS */

require_once 'HTML/QuickForm/Rule.php';

// QuickForm�̃��[���i���͈̔́AQuickForm�̃T���v�������p�j
class RuleNumericRange extends HTML_QuickForm_Rule
{
    function validate($value, $options)
    {
        if (isset($options['min']) && floatval($value) < $options['min']) {
            return false;
        }
        if (isset($options['max']) && floatval($value) > $options['max']) {
            return false;
        }
        return true;
    }

    function getValidationScript($options = null)
    {
        $jsCheck = array();
        if (isset($options['min'])) {
            $jsCheck[] = 'Number({jsVar}) >= ' . $options['min'];
        }
        if (isset($options['max'])) {
            $jsCheck[] = 'Number({jsVar}) <= ' . $options['max'];
        }
        return array('', "{jsVar} != '' && !(" . implode(' && ', $jsCheck) . ')');
    } // end func getValidationScript
}

// QuickForm�̃��[���i�z��ɗv�f�����邩�j
class RuleInArray extends HTML_QuickForm_Rule
{
    function validate($value, $options)
    {
        if (in_array($value, $options)) {
            return true;
        }
        return false;
    }
}

// QuickForm�̃��[���i�z��ɗv�f�����邩�j
class RuleInArrayKeys extends HTML_QuickForm_Rule
{
    function validate($value, $options)
    {
        if (isset($options[$value])) {
            return true;
        }
        return false;
    }
}

?>
