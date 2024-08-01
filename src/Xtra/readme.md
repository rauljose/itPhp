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
