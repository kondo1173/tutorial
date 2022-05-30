<?php
// namespace App\Session\Adapter;

class Database extends \Phalcon\Session\Adapter\AbstractAdapter
{
    /**
     * @var \Phalcon\Db\Adapter\AbstractAdapter
     */
    protected $connection;

    /**
     * options
     * @var array
     */
    protected $options;

    /**
     * {@inheritdoc}
     *
     * @param  array $options
     * @throws \Exception
     */
    public function __construct($options = null)
    {
        if (!isset($options['db']) || !$options['db'] instanceof \Phalcon\Db\Adapter\AbstractAdapter) {
            throw new \Exception(
                'Parameter "db" is required and it must be an instance of \Phalcon\Db\Adapter\AbstractAdapter'
            );
        }

        $this->connection = $options['db'];
        unset($options['db']);

        if (!isset($options['table']) || empty($options['table']) || !is_string($options['table'])) {
            throw new \Exception("Parameter 'table' is required and it must be a non empty string");
        }

        $columns = ['session_id', 'data', 'created_at', 'modified_at'];
        foreach ($columns as $column) {
            $oColumn = "column_$column";
            if (!isset($options[$oColumn]) || !is_string($options[$oColumn]) || empty($options[$oColumn])) {
                $options[$oColumn] = $column;
            }
        }

        $this->options = $options;
        session_set_save_handler(
            [$this, 'open'],
            [$this, 'close'],
            [$this, 'read'],
            [$this, 'write'],
            [$this, 'destroy'],
            [$this, 'gc']
        );
    }

    public function getOptions()
    {
        return $this->options;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function open($savePath, $sessionName): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function close(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $sessionId
     * @return string
     */
    public function read($sessionId): string
    {
        $maxLifetime = (int) ini_get('session.gc_maxlifetime');

        $options = $this->getOptions();
        $row = $this->connection->fetchOne(
            sprintf(
                'SELECT %s FROM %s WHERE %s = ? AND COALESCE(%s, %s) + %d >= ?',
                $this->connection->escapeIdentifier($options['column_data']),
                $this->connection->escapeIdentifier($options['table']),
                $this->connection->escapeIdentifier($options['column_session_id']),
                $this->connection->escapeIdentifier($options['column_modified_at']),
                $this->connection->escapeIdentifier($options['column_created_at']),
                $maxLifetime
            ),
            \Pdo::FETCH_NUM,
            [$sessionId, time()],
            [\Phalcon\Db\Column::BIND_PARAM_STR, \Phalcon\Db\Column::BIND_PARAM_INT]
        );

        if (empty($row)) {
            return '';
        }

        return $row[0];
    }

    /**
     * {@inheritdoc}
     *
     * @param  string $sessionId
     * @param  string $data
     * @return boolean
     */
    public function write($sessionId, $data): bool
    {
        $options = $this->getOptions();
        $row = $this->connection->fetchOne(
            sprintf(
                'SELECT COUNT(*) FROM %s WHERE %s = ?',
                $this->connection->escapeIdentifier($options['table']),
                $this->connection->escapeIdentifier($options['column_session_id'])
            ),
            \Pdo::FETCH_NUM,
            [$sessionId]
        );

        if (!empty($row) && intval($row[0]) > 0) {
            return $this->connection->execute(
                sprintf(
                    'UPDATE %s SET %s = ?, %s = ? WHERE %s = ?',
                    $this->connection->escapeIdentifier($options['table']),
                    $this->connection->escapeIdentifier($options['column_data']),
                    $this->connection->escapeIdentifier($options['column_modified_at']),
                    $this->connection->escapeIdentifier($options['column_session_id'])
                ),
                [$data, time(), $sessionId]
            );
        }

        return $this->connection->execute(
            sprintf(
                'INSERT INTO %s (%s, %s, %s) VALUES (?, ?, ?)',
                $this->connection->escapeIdentifier($options['table']),
                $this->connection->escapeIdentifier($options['column_session_id']),
                $this->connection->escapeIdentifier($options['column_data']),
                $this->connection->escapeIdentifier($options['column_created_at'])
            ),
            [$sessionId, $data, time()]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @return boolean
     */
    public function destroy($session_id = null): bool
    {

        if (is_null($session_id)) {
            $session_id = $this->getId();
        }

        $options = $this->getOptions();
        $result = $this->connection->execute(
            sprintf(
                'DELETE FROM %s WHERE %s = ?',
                $this->connection->escapeIdentifier($options['table']),
                $this->connection->escapeIdentifier($options['column_session_id'])
            ),
            [$session_id]
        );

        return $result;
    }

    /**
     * {@inheritdoc}
     * @param  integer $maxlifetime
     *
     * @return boolean
     */
    public function gc($maxlifetime): bool
    {
        $options = $this->getOptions();

        return $this->connection->execute(
            sprintf(
                'DELETE FROM %s WHERE COALESCE(%s, %s) + %d < ?',
                $this->connection->escapeIdentifier($options['table']),
                $this->connection->escapeIdentifier($options['column_modified_at']),
                $this->connection->escapeIdentifier($options['column_created_at']),
                $maxlifetime
            ),
            [time()]
        );
    }
}