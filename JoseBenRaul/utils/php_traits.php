<?php
/** @noinspection PhpUnused */
/** @noinspection PhpMissingParamTypeInspection */

trait enumer {
    /**
     * @return array[name:value]
     */
    public static function names():array {
        return static::is_BackedEnum() ?
            array_column(static::cases(), 'name', 'value' ) :
            array_column(static::cases(), 'name');
    }

    /**
     * @return array[value:name]
     */
    public static function values():array {
        return static::is_BackedEnum() ?
            array_column(static::cases(), 'value', 'name' ) :
            array_column(static::cases(),  'name' );
    }

    public static function is_BackedEnum():bool {
        $cases = static::$cases;
        return $cases[0]  ?? null instanceof BackedEnum;
    }

    /**
     * Returns the value for the AttributeName, if repeated we return each as an array entry
     * #[Color("red")]
     * case NAME: value; // $enumName->_property('Color') // returns "red"
     *
     * @param string|Stringable $attributeName
     * @return mixed
     *
     */
    public function _property($attributeName):mixed {
        $reflection = new ReflectionEnumUnitCase(static::class, $this->name);
        $attributes = $reflection->getAttributes();
        foreach($attributes as $a)
            if($a->getName() === $attributeName)
                if(!$a->isRepeated())
                    return $a->getArguments()[0] ?? $a->getArguments();
                else
                    $ret[] = $a->getArguments()[0] ?? $a->getArguments();
        return $ret ?? null;
    }

}

trait toString {
    /**
     * @return string
     * @throws ReflectionException
     */
    public function __toString(): string {
        $toLabel = function($s) {
            return ucwords( strtolower( trim(
                preg_replace('/(\s{2,})/muS', ' ',
                    preg_replace(['/(_)|([A-Z])/muS', '/(\\d)/m'], [' $2', ' $1 '], $s)
                )))) ?? $s;
        };
        $string = [];
        $reflect = new ReflectionClass($this);
        $props   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            if($prop->isStatic())
                continue;
            $v = $prop->getValue($this) ?? 'NULL';
            if(is_scalar($v) || (is_object($v) && is_a($v, "Stringable") ) ) {
                $string[$prop->getName()] = $toLabel($prop->getName()) . ": $v" ;
                continue;
            }
            if(is_a($v, "DateTimeInterface"))
                $string[$prop->getName()] = $toLabel($prop->getName()) . ": " . $v->format('d/M/Y H:i:s') ;
        }
        $allProps = [];
        foreach($reflect->getProperties() as $p)
            $allProps[$p->getName()] = $p;
        $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $m) {
            $name = $m->getName();
            if(str_starts_with($name, 'get') && ($name[3] === '_' || ($name[3] >= 'A' && $name[3] <= 'Z'))
                && $m->getNumberOfParameters() === 0
            ) {
                $string[$name] = $toLabel($name) . ": " . $this->$name();
                continue;
            }
            if(str_ends_with($name, '_get') && $m->getNumberOfParameters() === 0) {
                $string[$name] = $toLabel($name) . ": " . $this->$name();
                continue;
            }
            if(array_key_exists($name, $allProps) && $m->getNumberOfRequiredParameters() === 0)
                $string[$name] = $toLabel($name) . ": " . $this->$name();
        }
        return implode(", ", $string);
    }

}

/*
trait propertyAttributes {

     $[value2label([$value=>$label,..])
     protected $property;

 ofrece:
     toLabel("propertyName") regresa label para el valor actual de property
     _valuesLabels("propertyName") regresa el array de value=>label

     toRadio("propertyName") radios
     toSelect("propertyName") select
     toRO("propertyName") <div><label>propertyName</label><br><span id="propertyName" data-value="value">label</span></div>


}

https://fullystacked.net/

    :empty
    li:first-child:nth-last-child(n + 5) ~ li::before { content: ','; margin: 0 0.5em 0 -0.75em; }
In Page TOC
    https://css-tricks.com/table-of-contents-with-intersectionobserver/
    https://css-tricks.com/using-the-little-known-css-element-function-to-create-a-minimap-navigator/

https://css-tricks.com/how-to-use-css-grid-for-sticky-headers-and-footers/
https://developer.mozilla.org/en-US/docs/Web/CSS/Layout_cookbook/Sticky_footers
https://css-tricks.com/making-calendars-with-accessibility-and-internationalization-in-mind/
 */
