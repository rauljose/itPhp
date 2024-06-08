<?php


declare(strict_types = 1);

namespace coso\html;

use Stringable;

/**
 * Helpers for writing html
 */
class HtmlIt {
    protected const CHECKED = 'checked="checked" ';
    protected const SELECTED = 'selected="selected" ';

    /**
     * @param string|Stringable|int|float|bool|null $value
     * @param string|Stringable|array<int|string, string|int|float|bool|null> $checkedValues
     * @return string " value='$value' " or " value='$value' checked='checked' "
     */
    public function valueChecked($value, $checkedValues):string {
        $val = $value instanceof Stringable ? (string)$value : $value;
        $valueTag = " value='" . htmlspecialchars($val, ENT_QUOTES, "UTF-8") . "' "; // Use htmlspecialchars instead of htmlentities to protect against XSS attack
        if(is_array($checkedValues)) {
            return $valueTag . (in_array($val, $checkedValues, false) ? self::CHECKED : '');
        }
        if($checkedValues instanceof Stringable) {
            return $valueTag . ((string)$checkedValues === $val ? self::CHECKED : '');
        }
        return $valueTag . ($checkedValues == $value ? self::CHECKED : '');
    }

    /**
     * Returns htmlentity protected value tag, and selectedTag if $value in $selectedValues
     *
     * @param string|Stringable|int|float|bool|null $value
     * @param string|Stringable|array<int|string, string|int|float|bool|null> $selectedValues
     * @return string " value='$value' " or " value='$value' selected='selected' "
     */
    function valueSelected($value, $selectedValues):string {
        $val = $value instanceof Stringable ? (string)$value : $value;
        $valueTag = " value='" . htmlspecialchars((string)$val, ENT_QUOTES, "UTF-8") . "' "; // Use htmlspecialchars instead of htmlentities to protect against XSS attack
        if(is_array($selectedValues)) {
            return $valueTag . (in_array($value, $selectedValues, false) ? self::SELECTED : " ");
        }
        if($selectedValues instanceof Stringable) {
            return $valueTag . ((string)$selectedValues == $val ? self::SELECTED : " ");
        }
        return $valueTag . ($selectedValues == $value ? self::SELECTED : " ");
    }

    function optionKeyValue($keyValue, $selectedValues):string {
        $ret = [];
        foreach($keyValue as $value => $display) {
            $ret[] = "<option " . $this->valueSelected($value, $selectedValues) . ">" .
                htmlentities($display) . "</option>";
        }
        return implode("", $ret);
    }

    function optionVector($keyValue, $selectedValues):string {
        $ret = [];
        foreach($keyValue as $value) {
            $ret[] = "<option " . $this->valueSelected($value, $selectedValues) . ">" .
                htmlentities($value) . "</option>";
        }
        return implode("", $ret);
    }

    function optionKeyArray($array, $selectedValues, $displayField = ''):string{
        $ret = [];
        foreach($array as $value => $d) {
            $display = $d[$displayField] ?? reset($d);
            $ret[] = "<option " . $this->valueSelected($value, $selectedValues) . ">" .
                htmlentities($display) . "</option>";
        }
        return implode("", $ret);
    }

    function optionArray($array, $selectedValues, $displayField = '', $valueField = ''):string{
        $ret = [];
        foreach($array as $d) {
            $value = $d[$valueField]  ?? reset($d);
            $display = $d[$displayField] ?? reset($d);
            $ret[] = "<option " . $this->valueSelected($value, $selectedValues) . ">" .
                htmlentities($display) . "</option>";
        }
        return implode("", $ret);
    }



}