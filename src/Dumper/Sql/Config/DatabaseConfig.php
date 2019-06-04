<?php
declare(strict_types=1);

namespace Smile\GdprDump\Dumper\Sql\Config;

use Smile\GdprDump\Dumper\Sql\Driver\DriverFactory;

class DatabaseConfig
{
    /**
     * @var array
     */
    private $params = [
        'driver' => 'pdo_mysql',
        'host' => 'localhost',
        'port' => '',
        'user' => 'root',
        'password' => '',
        'name' => '',
    ];

    /**
     * @var array
     */
    private $pdoSettings = [];

    /**
     * @param array $params
     */
    public function __construct(array $params)
    {
        $this->prepareConfig($params);
    }

    /**
     * Get the database driver.
     *
     * @return string
     */
    public function getDriver(): string
    {
        return $this->params['driver'];
    }

    /**
     * Get the database host.
     *
     * @return string
     */
    public function getHost(): string
    {
        return $this->params['host'];
    }

    /**
     * Get the database port.
     *
     * @return string
     */
    public function getPort(): string
    {
        return $this->params['port'];
    }

    /**
     * Get the database user.
     *
     * @return string
     */
    public function getUser(): string
    {
        return $this->params['user'];
    }

    /**
     * Get the database password.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->params['password'];
    }

    /**
     * Get the database name.
     *
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->params['name'];
    }

    /**
     * Get all parameters values.
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Get the PDO settings.
     *
     * @return array
     */
    public function getPdoSettings(): array
    {
        return $this->pdoSettings;
    }

    /**
     * Get the PDO data source name.
     *
     * @return string
     */
    public function getDsn(): string
    {
        $driver = DriverFactory::create($this->getDriver());

        return $driver->getDsn($this);
    }

    /**
     * Prepare the database config.
     *
     * @param array $params
     * @throws \UnexpectedValueException
     */
    private function prepareConfig(array $params)
    {
        if (!isset($params['name'])) {
            throw new \UnexpectedValueException(sprintf('Missing database name.'));
        }

        // PDO settings
        if (array_key_exists('pdo_settings', $params)) {
            $this->pdoSettings = $params['pdo_settings'];
            unset($params['pdo_settings']);
        }

        // Validation
        foreach ($params as $param => $value) {
            if (!array_key_exists($param, $this->params)) {
                throw new \UnexpectedValueException(sprintf('Invalid database parameter "%s".', $param));
            }

            $this->params[$param] = $value;
        }
    }
}
