<?php
namespace app\db;
/**
 * Class factory to manage the creation of db connection
 * @author Andrea Serra (DevAS) https://devas.info
 */
class DbFactory {
    public static function create(array $options): DbPdo {
        if(!array_key_exists('driver', $options)) throw new \InvalidArgumentException('No default driver found');
        if (!isset($options['dsn'])) {
            $dsn = '';
            switch ($options['driver']){
                case 'pgsql':
                    unset($options['charset']);
                case 'mysql':
                case 'oracle':
                case 'mssql':
                    $dsn = "{$options['driver']}:host={$options['host']};dbname={$options['database']}";
                    $dsn .= isset($options['charset']) ? ";charset={$options['charset']}" : '';
                    break;
                case 'sqlite':
                    $dsn = "{$options['driver']}:{$options['database']}";
                default:
                    throw new \InvalidArgumentException('Driver not set or unknown');
            }
            $options['dsn'] = $dsn;
        }
        return DbPdo::getInstance($options);
    }
}

