<?php
namespace pp\utils;

use function array_key_exists;
use function file_get_contents;
use function is_array;
use function is_string;
use function json_decode;
use function mb_scrub;
use function preg_replace;
use function trim;

function markdown($before = null, $markdownPercentage = null, $comission = null, $after = null):array {
    if($before !== null) {
        $before = floatval($before);
        if($markdownPercentage !== NULL) {
            $markdownPercentage = floatval($markdownPercentage);
            $comission = $before * $markdownPercentage;
            $after = $before - $comission;
        } elseif($comission !== NULL) {
            $comission = floatval($comission);
            $markdownPercentage = 'TO do';
            $after = $before - $comission;
        } elseif($after !== NULL) {
            $after = floatval($after);
            $markdownPercentage = 'TO do';
            $comission = $before - $after;
        } else {
            // faltaron datos
        }
    } elseif($markdownPercentage !== NULL) {
        $markdownPercentage = floatval($markdownPercentage);
        if($comission !== NULL) {
            $comission = floatval($comission);
            $bruto = "To do";
            $after = "To do";
        } elseif($after !== NULL) {
            $after = floatval($after);
            $bruto = "To do";
            $comission = "To do";
        } else {
            // faltaron datos
        }
    } elseif($comission !== NULL) {
        $comission = floatval($comission);
        if($after !== NULL) {
            $bruto = "To do";
            $markdownPercentage = "To do";
        } else {
            // faltaron datos
        }
    } else {
        // faltaron datos!;
    }
    return ['before' => $before, 'markdownPercentage' => $markdownPercentage, 'comission' => $comission, 'after' => $after];
}

class datum {
    protected string $name;
    protected string $type; // string, number, bool, date, datetime, time, text, html, set(que es enum/set). Special fields lat, lng,email,tel,pwd,url
    protected int $maxlength = 191;
    protected bool $required = false;
    protected bool $nullable = false;
    protected string $format = "";
    protected string|int|float|bool|array|null $defaultValue = "";

    protected int $integers = 2;
    protected int $decimals = 2;

    protected mixed $min = null; //special than field?
    protected mixed $max = null;
    protected array $set = [];
    protected int $setMinItems = 1;
    protected int $setMaxItems = 1;

    // sensitive data
    // number as string?

    protected bool $numeric = false;

    // display
    // protected string|null $label = null;
    // protected array $attributes = []; // special are: title, pattern
    // displayType

    // User Related
    // protected string $permision = "R/W"; // R/W, R/O, NONE

    // DB REALTED
    //primarykey or partof primarykey
    //unique or partof uniquekey
    //link_to [table, field, rules]
    //link_from [table, field, rules]

    /**
     * @param string $name
     * @param string $type
     * @param int $maxlength
     * @param bool $required
     * @param bool $nullable
     * @param string $format
     * @param array|bool|float|int|string|null $defaultValue
     * @param int $integers
     * @param int $decimals
     * @param mixed|null $min
     * @param mixed|null $max
     * @param array $set
     * @param int $setMinItems
     * @param int $setMaxItems
     */
    public function __construct(
      string $name, string $type, int $maxlength = 191, bool $required = false, bool $nullable = false,
      string $format = "", float|array|bool|int|string|null $defaultValue = "",
      int $integers = 8, int $decimals = 2, mixed $min = null, mixed $max = null,
      array $set = [], int $setMinItems = 1, int $setMaxItems = 1) {
        $this->type = $type;
        $this->maxlength = $maxlength;
        $this->required = $required;
        $this->nullable = $nullable;
        $this->format = $format;
        $this->defaultValue = $defaultValue;
        $this->integers = $integers;
        $this->decimals = $decimals;
        $this->min = $min;
        $this->max = $max;
        $this->set = $set;
        $this->setMinItems = $setMinItems;
        $this->setMaxItems = $setMaxItems;
    }


    public function is_numeric():bool {return $this->numeric;}
}

class mediumint extends datum {
    public function __construct(bool $unsigned = true, $min = null, $max = null, $format="#,##0") {
        parent::__construct('entero',"int", decimals: 0);
    }
}

/**
 * TYPE:
 *   string maxlength/minlength
 *   textarea maxlength/minlength
 *   int max/min
 *   float max/min
 *
 */
/**
 *  Get parameters from http request: $param = Param::getInstance();
 *  $param->param('name', 'valueWhenNotPresent');
 *
 */
final class Param {
    public const PARAM_SCRUB_IT = 1;
    public const PARAM_TRIM_IT = 2;
    public const PARAM_REMOVE_COMMAS = 4;

    private static $instance;

    private array|null $raw = null;

    private string $encoding;

    private string|null $phpInputStream;
    private bool $phpInputStreamGetIt = true;

    public static function getInstance($doScrub = true, string $encoding = "UTF-8") {
        if (null === Param::$instance)
            Param::$instance = new Param($doScrub, $encoding);
        return Param::$instance;
    }

    private function __construct($encoding = "UTF-8") {
        $this->encoding = $encoding;
    }

    public function exists($key):bool {
        if(array_key_exists($key, $_REQUEST))
            return true;
        return array_key_exists($key, $this->getRaw());
    }

    public function param(string $key, string|array|null $defaultValue = '', int $options = 3):string|array|null {
        if(array_key_exists($key, $_POST))
            return $this->process( $_GET[$key], $options);
        if(array_key_exists($key, $this->getRaw()))
            return $this->process( $this->raw[$key], $options);
        return array_key_exists($key, $_GET) ? $this->process($_GET[$key], $options) : $defaultValue;
    }

    public function post(string $key, string|array|null $defaultValue = '', int $options = 3):string|array|null {
        if(array_key_exists($key, $_POST))
            return $this->process( $_GET[$key], $options);
        if(array_key_exists($key, $this->getRaw()))
            return $this->process( $this->raw[$key], $options);
        return $defaultValue;
    }

    public function keys(array $keys, int $options = 3) {
        $return = [];
        if(array_is_list($keys)) {
            foreach($keys as $key)
                $return[$key] = $this->param($key);
            return $return;
        }
        // key=>default?
        foreach($keys as $key => $default)
            $return[$key] = $this->param($key, $default);
        return $return;
    }

    public function getInputStream():string|null {
        if($this->phpInputStreamGetIt) {
            $this->phpInputStream = file_get_contents('php://input');
            $this->phpInputStreamGetIt = false;
        }
        return $this->phpInputStream;
    }

    private function getRaw():array {
        if($this->raw === null)
            $this->raw = $this->getInputStream() === null ? [] : json_decode($this->getInputStream(),true);
        return $this->raw;
    }

    private function process($param, int $options):string|array {
        if(($options & Param::PARAM_SCRUB_IT) === Param::PARAM_SCRUB_IT)
            $param = $this->scrub( $param );
        if(($options & Param::PARAM_TRIM_IT) === Param::PARAM_TRIM_IT)
            $param = $this->sTrim( $param );
        return $param;
    }

    private function scrub($param):string|array {
        if(is_array($param)) {
            foreach($param as &$v)
                $v = $this->scrub($v);
            return $param;
        }
        return $param === null ? "" : mb_scrub($param, $this->encoding);
    }

    private function sTrim($str):string|array {
        if($str === null)
            return '';
        if(is_array($str)) {
            foreach($str as &$d)
                $d = self::sTrim($d);
            return $str;
        }
        if(!is_string($str))
            $str = "$str";
        $s1 = preg_replace('/[\pZ\pC]/muS',' ',$str);
        if(preg_last_error()) {
            $s1 = preg_replace('/[\pZ\pC]/muS',' ',  iconv("UTF-8","UTF-8//IGNORE",$str));
            if(preg_last_error())
                return trim(preg_replace('/ {2,}/mS',' ',$str));
        }
        return trim(preg_replace('/ {2,}/muS',' ',$s1));
    }

    private function __clone() {}

    private function __wakeup() {}

    private function __sleep() {}

}
?>
<script>
    function markdown(bruto, porcentaje, comision, neto) {
        if(typeof bruto === 'undefined' || bruto === '')
            bruto = null;
        if(typeof porcentaje === 'undefined' || porcentaje === '')
            porcentaje = null;
        if(typeof comision === 'undefined' || comision === '')
            comision = null;
        if(typeof neto === 'undefined' || neto === '')
            neto = null;
        let ok = true;
        if(bruto !== null) {
            bruto = parseFloat(bruto);
            if(porcentaje !== null) {
                porcentaje = parseFloat(porcentaje);
                comision = bruto * porcentaje/100.00;
                neto = bruto - comision;
            }
            else if(comision !== null) {
                comision = parseFloat(comision);
                porcentaje = comision/bruto * 100.00;
                neto = bruto - comision;
            }
            else if(neto !== null) {
                neto = parseFloat(neto);
                comision = bruto - neto;
                porcentaje = comision/bruto * 100.00;
            } else {
                ok = false;
            }
        } else if(porcentaje !== null) {
            porcentaje = parseFloat(porcentaje);
            if(comision !== null) {
                comision = parseFloat(comision);
                bruto = porcentaje / comision * 100.00 * 100.00;
                neto = bruto - comision;
            } else if(neto !== null) {
                neto = parseFloat(neto);
                bruto = neto / (1 - porcentaje / 100.00);
                comision = bruto - neto;
            } else {
                ok = false;
            }
        } else if(comision !== null) {
            comision = parseFloat(comision);
            if(neto !== null) {
                neto = parseFloat(neto);
                bruto = parseFloat(neto) + comision;
                porcentaje =  comision/bruto*100.00;
            } else {
                ok = false;
            }
        }
        return {ok, bruto, porcentaje, comision, neto}
    }
</script>
