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
class ConnectionTest
{

    /**
     * The Mysql user
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

    /**
     * Rabbit MQ host
     * @var string 
     */
    private $mqHost;

    /**
     * Rabbit MQ user
     * @var string 
     */
    private $mqUser;

    /**
     * Rabbit MQ pass
     * @var string 
     */
    private $mqPass;

    /**
     * Rabbit MQ vhost
     * @var string 
     */
    private $mqVhost;

    /**
     * Rabbit MQ Port
     * @var string 
     */
    private $mqPort;

    /**
     * Disk path
     * @var string 
     */
    private $diskPath;

    /**
     * Disk path, you probably want a value like .2 
     * where any usage over 80% throws an error
     * @var string 
     */
    private $diskThreshold;

    public function run()
    {
        // Test httpd
        if ($this->checkHttpd() === true) {
            echo '-apache';
        }
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

        // Test RabbitMQ
        if ($this->checkRabbitMQ() === true) {
            echo '-rabbitmq';
        }

        // Test Disk Space
        if ($this->checkDiskSpaceOk() === true) {
            echo '-diskok';
        }
    }

    private function checkMemcached()
    {
        $handle = popen('/sbin/service memcached status', "r");
        $data = fgets($handle);
        if (strpos($data, 'running') !== false) {
            return true;
        }
        return false;
    }

    private function checkHttpd()
    {
        $handle = popen('ps axu | grep httpd | wc -l', "r");
        $data = intval(fgets($handle));
        if ($data > 2) {
            return true;
        }
        return false;
    }

    private function checkNginx()
    {
        $handle = popen('ps axu | grep nginx | wc -l', "r");
        $data = intval(fgets($handle));
        if ($data > 2) {
            return true;
        }
        return false;
    }

    private function checkPhpFpm()
    {
        $handle = popen('ps axu | grep php-fpm | wc -l', "r");
        $data = fgets($handle);
        if ($data > 2) {
            return true;
        }
        return false;
    }

    private function checkElasticsearch()
    {
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

    private function checkMysqlPDO()
    {
        try {
            $dbh = new \pdo('mysql:host=127.0.0.1:3306;dbname=' . $this->dbname, $this->dbuser, $this->dbpass, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            return true;
        } catch (\PDOException $ex) {
            return false;
        }
    }

    private function checkRabbitMQ()
    {
        $amqpConnection = new AMQPConnection();
        $amqpConnection->setHost($this->mqHost);
        $amqpConnection->setLogin($this->mqUser);
        $amqpConnection->setPassword($this->mqPass);
        $amqpConnection->setVhost($this->mqVhost);
        $amqpConnection->setPort($this->mqPort);
        $amqpConnection->connect();
        return $amqpConnection->isConnected();
    }

    private function checkDiskSpaceOk()
    {
        if (disk_free_space($this->diskPath) / disk_total_space($this->diskPath) < $this->diskThreshold) {
            return false;
        }
        return true;
    }
}

$test = new ConnectionTest();
$test->run();
// end of script
