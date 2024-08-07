# It library

	WIP

## itUtil

### On hashing

#### Hash function for Security
"Let me attempt to give an explanation. As of today (30/Jul/2024 users) should use in order of priority:
    - The hash function they need for interoperability: If a service provides a SHA-1 checksum, then there is no choice and SHA-1 needs to be used.
    - The hash function their security team requests them to use.
    - A function from the SHA-2 family, with SHA-256 being a good default choice, because that's the secure default choice across the industry."
	- tim@bastelstu.be  https://externals.io/message/124506#124694


#### javascript
```javascript
	// Using xxh64 (requires xxhashjs library - install via npm)
	import xxhash from 'xxhashjs';
	const string = "I'm Tzar the Great Cat";
	const seed = 0; // Optional seed
	const hashBuffer = xxhash.h64(string, seed).toBuffer();
	const hexHash = Buffer.from(hashBuffer).toString('hex');
	console.log("xxh64 Hex:", hexHash);
	// Using MD5 (built-in)
	const md5Hash = CryptoJS.MD5(string).toString();
	console.log("MD5 Hex:", md5Hash);
```
## Altri
	* https://uiverse.io/
	* marscode
	* https://www.automa.site/
	* https://dev.to/marscode/17-most-powerful-ai-tools-for-developers-e6n

```php
$sql = "SELECT clave as 'la manga de la :tanga azul' FROM tienda WHERE clave = :cacha AND tienda = :taba  and vale=':si'";
$value = ['hoy'=>1, 'tanga'=>'SOY TANGA', 'cacha'=>'La cacha', 'taba'=>'De diana', 'si' => 'INVALIDA '];
$q = namedParamsHandler($sql, $value);
print_r($q);
/**
* @param string $sql An sql statement with named parameters key=:key1_value where key1_value is a key in $values
* @param array $values An array with named parameters as key and the value to use
* @return array [query:'string',parameter:array]
* @throws InvalidArgumentException
  */
  function namedParamsHandler(string $sql, array $values):array {
  $parameters = [];
  $replaced = preg_replace_callback("/'[^']*'(*SKIP)(*FAIL)|:([a-z0-9_-]+)\b/miUS", function($match) use (&$parameters, $values) {
  if(!\array_key_exists($match[1], $values))
  throw new InvalidArgumentException("Key {$match[1]} does not exist in values array");
  $parameters[] = $values[$match[1]];
  return '? ';
  }, $sql);
  return ['query' => $replaced, 'parameters' => $parameters];
  }
```