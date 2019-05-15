<?php
declare(strict_types=1);

namespace Smile\Anonymizer\Dumper\Sql\Config;

use Smile\Anonymizer\Config\ConfigInterface;

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
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->prepareConfig($config);
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
    public function getName(): string
    {
        return $this->params['name'];
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
     * Get the parameter values.
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->params;
    }

    /**
     * Prepare the config.
     *
     * @param ConfigInterface $config
     * @throws \UnexpectedValueException
     */
    private function prepareConfig(ConfigInterface $config)
    {
        $databaseData = $config->get('database', []);

        if (!isset($databaseData['name'])) {
            throw new \UnexpectedValueException(sprintf('Missing database name.'));
        }

        // PDO settings
        if (array_key_exists('pdoSettings', $databaseData)) {
            $this->pdoSettings = $databaseData['pdoSettings'];
            unset($databaseData['pdoSettings']);
        }

        foreach ($databaseData as $param => $value) {
            if (!array_key_exists($param, $this->params)) {
                throw new \UnexpectedValueException(sprintf('Invalid database parameter "%s".', $param));
            }

            $this->params[$param] = $value;
        }
    }
}
