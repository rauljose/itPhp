<?php
/** @noinspection PhpUnused */

/* test chaca
$chaca = new chaca('');
$p = '\\wamp\\www\\ppSan\\enumUtil\\src\\Invoke.php';
$chaca->registerFile($p,['aa'=>1111, 'b'=>2222]);
$p = '\\wamp\\www\\ppSan\\enumUtil\\src\\ExtraFunctions.php';
$chaca->registerFile($p,['aa'=>1, 'b'=>22]);
$chaca->registerFile($p,['a'=>1, 'b'=>2]);
echo "\r\n$p = " . print_r( $chaca->getFilePath() , true);
$p = 'ppSan\\enum\\ExtraFunctions::nameValue';
$chaca->registerFqn($p,['aa'=>1, 'b'=>22]);
$chaca->registerFqn($p,['a'=>1, 'b'=>2]);
echo "\r\n$p = " . print_r( $chaca->getFqn(), true );
*/
class chaca {
    protected string $documentRoot;
    protected array $fqn = [];
    protected array $filePath = [];
    protected int $fqnDeep = 0;
    protected int $filePathDeep = 0;
    protected array $fqnDataKeys = [];
    protected array $fileDataKeys = [];

    public function __construct(string $documentRoot = '') {
        $this->documentRoot = str_replace("\\", "/", $documentRoot);
    }

    /**
     * @return array
     */
    public function getFqn(): array {
        return $this->fqn;
    }

    /**
     * @return array
     */
    public function getFilePath(): array {
        return $this->filePath;
    }

    /**
     * @return int
     */
    public function getFqnDeep(): int
    {
        return $this->fqnDeep;
    }

    /**
     * @return int
     */
    public function getFilePathDeep(): int
    {
        return $this->filePathDeep;
    }


    public function registerFile($path, array $keyPairValues = []):void {
        // \wamp\www\ppSan\enumUtil\src\ExtraFunctions.php
        $level = 0;
        $path = str_replace("\\", "/", $path);
        if($this->documentRoot !== '' && str_starts_with($path, $this->documentRoot))
            $path = str_replace($this->documentRoot, '', $path);
        $p = &$this->filePath;
        foreach(explode("/", $path) as $n) {
            $level++;
            if(!array_key_exists($n, $p))
                $p[$n] = [];
            $p = &$p[$n];
        }
        if($level > $this->filePathDeep)
            $this->filePathDeep = $level;
        $p = array_merge($p,  $keyPairValues);
        $theKeys = array_keys($keyPairValues);
        $this->fileDataKeys = array_merge( $this->fileDataKeys, array_combine($theKeys, $theKeys));
    }

    public function registerFqn(string $fqn, array $keyPairValues = []):void {
        // ppSan\enum\ExtraFunctions::nameValue
        $level = 0;
        $p = &$this->fqn;
        foreach(explode("\\", $fqn) as $n) {
            $level++;
            if(str_contains($n, "::")) {
                $class = explode('::', $n);
                if(!array_key_exists($class[0], $p))
                    $p[$class[0]] = [];
                $p = &$p[$class[0]];
                $level++;
                if(!array_key_exists($class[1], $p))
                    $p[$class[1]] = $keyPairValues;
                else
                    $p[$class[1]] = array_merge($p[$class[1]], $keyPairValues);
                $theKeys = array_keys($keyPairValues);
                $this->fqnDataKeys = array_merge( $this->fqnDataKeys, array_combine($theKeys, $theKeys));
            } else {
                if(!array_key_exists($n, $p))
                    $p[$n] = [];
                $p = &$p[$n];
            }
        }
        if($level > $this->fqnDeep)
            $this->fqnDeep = $level;
    }

}
// https://github.com/MariaNattestad/d3-superTable
// https://github.com/nicolaskruchten/pivottable https://pivottable.js.org/examples/
    // https://github.com/pranjal-goswami/multifact-pivottable //  https://github.com/nagarajanchinnasamy/subtotal