<?php
/** @noinspection PhpMissingParamTypeInspection */

/** @noinspection PhpRedundantOptionalArgumentInspection */

namespace It\Xtra;

use \SensitiveParameter;

class ItPassword {
    protected  string|int $algorithm;
    protected array $options;

    /**
     * @param string $algorithm default to PASSWORD_DEFAULT
     * @param array $options
     */
    public function __construct( string|int $algorithm = PASSWORD_DEFAULT, array $options = []) {
        $this->algorithm = $algorithm;
        $this->options = $options;
    }

    public function hash(#[\SensitiveParameter] string $password, #[\SensitiveParameter] string $userSalt):false|null|string {
        return password_hash(
          $this->saltPassword($password, $userSalt),
          $this->algorithm,
          $this->options
        );
    }

    public function verify(string $hash, #[\SensitiveParameter] string $password, #[\SensitiveParameter] string $userSalt):bool {
        return password_verify($this->saltPassword($password, $userSalt), $hash);
    }

    public function needs_rehash(string $hash):bool {
        return password_needs_rehash($hash, $this->algorithm, $this->options);
    }

    protected function saltPassword(#[\SensitiveParameter] string $password, #[\SensitiveParameter] string $userSalt): string {
        return $userSalt.$password.$userSalt;
    }

}
