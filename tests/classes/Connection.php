<?php
namespace Ciebit\News\Tests;

use PDO;
use PHPUnit\Framework\TestCase;
use PHPUnit\DbUnit\TestCaseTrait;

abstract class Connection extends TestCase
{
    use TestCaseTrait;
    private $settings; #:array
    static private $pdo;
    private $connection;
    private function getSettings(): array
    {
        if ($this->settings != null) {
            return $this->settings;
        }
        $this->settings = parse_ini_file(__DIR__.'/../settings.ini', true);
        return $this->settings;
    }
    public function getPdo(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        $settings = $this->getSettings()['database'];
        $config = "mysql:dbname={$settings['database']};host{$settings['host']};charset=utf8";
        self::$pdo = new PDO($config, $settings['user'], $settings['password']);
        return self::$pdo;
    }
    final public function getConnection()
    {
        if ($this->connection !== null) {
            return $this->connection;
        }
        $pdo = $this->getPdo();
        $this->connection = $this->createDefaultDBConnection($pdo, '');
        return $this->connection;
    }
    public function getDataSet()
    {
        return $this->createXMLDataSet(__DIR__.'/../data.xml');
    }
}
