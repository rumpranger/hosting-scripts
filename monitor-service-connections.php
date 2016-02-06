<?php

/**
 * ConnectionTest
 * 
 * Dirty script to monitor service status on centos boxes
 * Monitor script needs to check for the output string.
 * Modify the services depending on the host deployed to.
 * 
 * Created 2016-01-21
 * @author Charles Weiss <charlesw@ex-situ.com>
 */
class ConnectionTest {

    /**
     *The Mysql user
     * @var string
     */
    private $dbuser;
    
    /**
     * The Mysql pass
     * @var string 
     */
    private $dbpass;
    
    /**
     * The Mysql database name
     * @var string
     */
    private $dbname;
    
    public function run() {

        // Test memcached
        if ($this->checkMemcached() === true) {
            echo '-memcached';
        }

        // Test Nginx
        if ($this->checkNginx() === true) {
            echo '-nginx';
        }

        // Test PHP
        if ($this->checkPhpFpm() === true) {
            echo '-php';
        }

        // Test Elastic Search
        if ($this->checkElasticsearch() === true) {
            echo '-elastic';
        }
        
        // Test MYSQL
        if ($this->checkMysqlPDO() === true) {
            echo '-mysql';
        }
    }

    private function checkMemcached() {
        $handle = popen('/sbin/service memcached status', "r");
        $data = fgets($handle);
        if (strpos($data, 'running') !== false) {
            return true;
        }
        return false;
    }

    private function checkHttpd() {
        $handle = popen('/sbin/service httpd status', "r");
        $data = fgets($handle);
        if (strpos($data, 'running') !== false) {
            return true;
        }
        return false;
    }

    private function checkNginx() {
        $handle = popen('ps axu | grep nginx | wc -l', "r");
        $data = fgets($handle);
        if ($data > 1) {
            return true;
        }
        return false;
    }

    private function checkPhpFpm() {
        $handle = popen('ps axu | grep php-fpm | wc -l', "r");
        $data = fgets($handle);
        if ($data > 1) {
            return true;
        }
        return false;
    }

    private function checkElasticsearch() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:9200/?pretty');
        $data = curl_exec($ch);
        curl_close($ch);
        if (strpos($data, 'You Know, for Search') !== false) {
            return true;
        }
        return false;
    }

    private function checkMysqlPDO() {
        try {
            $dbh = new \pdo('mysql:host=127.0.0.1:3306;dbname=' . $this->dbname, $this->dbuser, $this->dbpass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            return true;
        } catch (\PDOException $ex) {
            return false;
        }
    }

}

$test = new ConnectionTest();
$test->run();
// end of script
