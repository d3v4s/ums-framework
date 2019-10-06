<?php
namespace app\db;
require_once __DIR__.'/../../autoload.php';
// require __DIR__.'DbPdo.php';

class DbFactory {
    public static function create(array $options) {
        if(!array_key_exists('driver', $options)) throw new \InvalidArgumentException('Nessun driver predefinito');
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
                    throw new \InvalidArgumentException('Driver non impostato o sconosciuto');
            }
            $options['dsn'] = $dsn;
        }
        return DbPdo::getInstance($options);
    }
}

