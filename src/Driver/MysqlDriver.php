<?php


namespace Drift\DBAL\Driver;

use Doctrine\DBAL\Query\QueryBuilder;
use Drift\DBAL\Credentials;
use Drift\DBAL\Result;
use React\EventLoop\LoopInterface;
use React\MySQL\ConnectionInterface;
use React\MySQL\Factory;
use React\MySQL\QueryResult;
use React\Promise\PromiseInterface;
use React\Socket\ConnectorInterface;

/**
 * Class MysqlDriver
 */
class MysqlDriver implements Driver
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * MysqlDriver constructor.
     *
     * @param LoopInterface $loop
     * @param ConnectorInterface $connector
     */
    public function __construct(LoopInterface $loop, ConnectorInterface $connector = null)
    {
        $this->factory = is_null($connector)
            ? new Factory($loop)
            : new Factory($loop, $connector);
    }

    /**
     * @inheritDoc
     */
    public function connect(Credentials $credentials, array $options = [])
    {
        $this->connection = $this
            ->factory
            ->createLazyConnection($credentials->toString());
    }

    /**
     * @inheritDoc
     */
    public function query(
        string $sql,
        array $parameters
    ): PromiseInterface
    {
        return $this
            ->connection
            ->query($sql, $parameters)
            ->then(function(QueryResult $queryResult) {
                return new Result($queryResult->resultRows);
            });
    }
}