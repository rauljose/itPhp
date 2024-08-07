<?php
/** @noinspection PhpUnused */

namespace It\Sql;

use Exception;
use mysqli;
use mysqli_result;
use mysqli_sql_exception;
use mysqli_stmt;

class Sql {
    protected mysqli $mysqli;
    protected string $charset;
    protected string $coalition;

    /**
     * @var array{
     * hostname: ?string,
     * username: ?string,
     * password: ?string,
     * database: ?string,
     * port: ?string,
     * socket: ?string,
     * flags: int,
     * } $connection_info
     */
    protected array $connection_info = [
        'hostname' => null,
        'username' => null,
        'password' => null,
        'database' => null,
        'port' => null,
        'socket' => null,
        'flags' => 0,
    ];

    protected array $connect_options = [
        MYSQLI_INIT_COMMAND => 'SET AUTOCOMMIT = 1',
    //    'MYSQLI_OPT_CONNECT_TIMEOUT' => 10,
    ];


    protected int $retries = 3;
    protected int $retrySleepMicroSeconds = 50;

    /**
     * $retryOnErrors
     * Mysqli errors on which to retry the transaction or query if not inside a transaction.
     *
     * @var array $retryOnErrors  mysqli errors on which to retry if not inside transaction
     * * Error: 1015 SQLSTATE: HY000 (ER_CANT_LOCK) Message: Can't lock file (errno: %d)
     * * Error?: 1027 SQLSTATE: HY000 (ER_FILE_USED) Message: '%s' is locked against change
     * * Error: 1689 SQLSTATE: HY000 (ER_LOCK_ABORTED) Message: Wait on a lock was aborted due to a pending exclusive lock
     * * Error: 1205 SQLSTATE: HY000 (ER_LOCK_WAIT_TIMEOUT) Message: Lock wait timeout exceeded; try restarting transaction
     * * Error: 1206 SQLSTATE: HY000 (ER_LOCK_TABLE_FULL) Message: The total number of locks exceeds the lock table size
     * * Error: 1213 SQLSTATE: 40001 (ER_LOCK_DEADLOCK) Message: Deadlock found when trying to get lock; try restarting transaction
     * * Error: 1622 SQLSTATE: HY000 (ER_WARN_ENGINE_TRANSACTION_ROLLBACK) Message: Storage engine %s does not support rollback for this statement.
     *      Transaction rolled back and must be restarted
     * * Error: 1614 SQLSTATE: XA102 (ER_XA_RBDEADLOCK) Message: XA_RBDEADLOCK: Transaction branch was rolled back: deadlock was detected
     * * Error: 2006 (CR_SERVER_GONE_ERROR) Message: MySQL server has gone away
     * * Error: 2013 (CR_SERVER_LOST) Message: Lost connection to MySQL server during query
     *
     * @see IacMysqli::runSql() IacMysqli::runSql()
     * @access protected
     */
    protected array $retryOnErrors = array(1015=>1, 1689=>1, 1205=>1, 1206=>1, 1213=>1, 1622=>1, 1614=>1, 2006=>1, 2013=>1 );

    /**
     * $reconnectOnErrors
     * Mysqli errors on which to try to reconnect.
     *
     * @var array $reconnectOnErrors mysql error codes on which to reconnect
     *
     * Error: 2006 (CR_SERVER_GONE_ERROR) Message: MySQL server has gone away
     * Error: 2013 (CR_SERVER_LOST) Message: Lost connection to MySQL server during query
     * Error: 2024 (CR_PROBE_SLAVE_CONNECT) Message: Error connecting to slave:
     * Error: 2025 (CR_PROBE_MASTER_CONNECT) Message: Error connecting to master:
     * Error: 2026 (CR_SSL_CONNECTION_ERROR) Message: SSL connection error: %s
     * --
     * Error: 2020 (CR_NET_PACKET_TOO_LARGE) Message: Got packet bigger than 'max_allowed_packet' bytes
     *  On queries larger than max_ a lost connection error may be returned
     *
     * @access protected
     */
    protected array $reconnectOnErrors = array(2006 => 1, 2013 => 1);

    public string $lastPreparedQuery = "";

    protected array $log = [];
    protected array $logError = [];

    protected int $openTransactions = 0;


    /**
     *
     *
     * @param array{hostname: ?string, username: ?string, password: ?string, database: ?string, port: ?string, socket: ?string, flags: int } $connect
     * @param array $connect_options default [MYSQLI_INIT_COMMAND => 'SET AUTOCOMMIT = 1']
     * @param string $charset default utf8mb4
     * @param string $coalition default utf8mb4_0900_ai_ci
     */
    public function __construct(array $connect, array $connect_options = [], string $charset = 'utf8mb4',
                                string $coalition = 'utf8mb4_0900_ai_ci') {
        $this->connection_info = array_merge($this->connection_info, $connect) ;
        $this->connect_options = array_merge($this->connect_options, $connect_options);
        $this->charset = $charset;
        $this->coalition = $coalition;
    }

    /**
     * @return void
     * @throws Exception
     */
    protected function connect():void {
        $this->mysqli = new mysqli();
        foreach($this->connect_options as $option => $value)
            if (!$this->mysqli->options($option, $value)) {
                die("Setting $option $value failed");
            }
        $attempts = 0;
        while(++$attempts <= $this->retries) {
            if($this->mysqli->real_connect($this->connection_info['hostname'], $this->connection_info['username'],
                $this->connection_info['password'], $this->connection_info['database'], $this->connection_info['port'],
                $this->connection_info['socket'], $this->connection_info['flags'])) {
                $charset = strit($this->charset);
                $coalition = strit($this->coalition);
                $this->mysqli->set_charset($this->charset);
                $this->query( "SET NAMES $charset COLLATE $coalition");
                return;
            }
        }
        die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
    }

    /**
     *
     *
     * @param string|mysqli_stmt $query
     * @param array $parameters
     * @return bool|mysqli_result
     * @throws Exception
     */
    public function query(string|mysqli_stmt $query, array $parameters = []):bool|mysqli_result {
        return $this->runSql($query, $parameters);
    }

    /**
     * return [$key1=>[col1=>value1],$key2=>[]], $default on not found
     *
     * @param string|mysqli_stmt $query
     * @param string $key
     * @param array $parameters
     * @param array|null $default
     * @param int $resultType MYSQLI_ASSOC|MYSQLI_NUM|MYSQLI_BOTH
     * @return array [$key1=>[col1=>value1],$key2=>[]]
     * @throws Exception
     */
    public function arrayKeyed(string|mysqli_stmt $query, string $key, array $parameters = [], array|null $default = [],
                               int $resultType = MYSQLI_ASSOC):array {
        if(empty($query))
            return $default;
        $result = $this->runSql($query, $parameters);
        for($ret = []; $tmp = $result->fetch_array($resultType);)
            $ret[$tmp[$key]] = $tmp;
        return empty($ret) ? $default : $ret;
    }

    /**
     * return [[col1=>value1],[]], $default on not found
     *
     * @param string|mysqli_stmt $query
     * @param array $parameters
     * @param array|null $default
     * @param int $resultType MYSQLI_ASSOC|MYSQLI_NUM|MYSQLI_BOTH
     * @return array [$key1=>[col1=>value1],$key2=>[]]
     * @throws Exception
     */
    public function array(string|mysqli_stmt $query, array $parameters = [], array|null $default = [],
                          int $resultType = MYSQLI_ASSOC):array {
        if(empty($query))
            return $default;
        $result = $this->runSql($query, $parameters);
        for($ret = []; $tmp = $result->fetch_array($resultType);)
            $ret[] = $tmp;
        return empty($ret) ? $default : $ret;
    }

    /**
     * @param  string|mysqli_stmt $query
     * @param array $parameters
     * @return bool|mysqli_result
     * @throws Exception
     */
    protected function runSql(string|mysqli_stmt $query, array $parameters = []):bool|mysqli_result {
        if(empty($this->mysqli))
            $this->connect();
        $this->logAdd($query, $parameters);
        $lastError = null;
        $attempts = 0;
        while(++$attempts <= $this->retries) {
            try {
                if(is_string($query)) {
                    if(empty($parameters))
                        return $this->mysqli->query($query);
                    return $this->mysqli->execute_query($query, $parameters);
                }
                $query->execute();
                $result = $query->get_result();
                $query->store_result();
                return $result;
            } catch(mysqli_sql_exception $error) {
                $this->logErrorAdd($error->getCode(), $error->getMessage(), $query, $parameters, $attempts);
                if(!$this->retryQuery($error->getCode()))
                    throw $error;
                $lastError = $error;
                usleep($this->retrySleepMicroSeconds);
            }
        }
        throw $lastError === null ? new Exception("Unknown Error") : $lastError;
    }

    /**
     * @param int $errorNumber
     * @return bool
     * @throws Exception
     */
    protected function retryQuery(int $errorNumber):bool {
        if($this->openTransactions > 0)
            return false;
        if(array_key_exists($errorNumber, $this->reconnectOnErrors))
            $this->connect();
        return array_key_exists($errorNumber, $this->retryOnErrors);
    }

    protected function logAdd(string|mysqli_stmt $query, array $parameters = []):void {
        $logEntry = is_string($query) ? $query : $this->lastPreparedQuery;
        if(!empty($parameters))
            $logEntry .= " -- (" . implode(", ", $parameters) . ")";
        $this->log[] = $logEntry;
    }

    protected function logErrorAdd(int $errorNumber, string $errorMessage, string $query, array $parameters, int $attempt):void {
        $this->log[] = ["error" => $errorNumber, "error message" => $errorMessage, "query" => $query,
          "parameters" => $parameters, "attempt" => $attempt];
    }

}
