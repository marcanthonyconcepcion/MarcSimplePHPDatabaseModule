<?php


require_once __DIR__.'/vendor/autoload.php';

$configuration = new Noodlehaus\Config(__DIR__.'\resources\database_demo.yaml', new Noodlehaus\Parser\Yaml);
define('HOST', $configuration->get('database.host'));
define('DBNAME', $configuration->get('database.dbname'));
define('CHARSET', 'utf8');
define('USER', $configuration->get('database.user'));
define('PASSWORD', $configuration->get('database.password'));


class Database
{
    private PDO $database;
    private PDOStatement $statement;

    function __construct() 
    {
        try
        {
            $this->database = new PDO('mysql:host='.HOST.';dbname='.DBNAME.';charset='.CHARSET, USER, PASSWORD, 
                               [  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                                  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                                  PDO::ATTR_EMULATE_PREPARES => false ]);
        }
        catch (PDOException $exception)
        {
            throw new ConnectDatabaseError($exception->getMessage());
        }
    }
          
    function run(string $statement, array $parameters=[]) : Generator
    {
        try
        {
            $this->statement = $this->database->prepare($statement);
            $this->statement->execute($parameters);
            while($row = $this->statement->fetch()) yield $row;
         }
        catch (PDOException $exception)
        {
            throw new RunDatabaseQueryError($exception->getMessage());
        }
        finally
        {
            $this->statement->closeCursor();
        }
    }

    function transact(array $queries) : array
    {
        try
        {
            $outputs = [];
            if (!$this->database->beginTransaction())
                throw new TransactDatabaseError('Start transaction fail');
            foreach ($queries as $query)
            {
                try 
                {
                    $records = $this->run($query['statement'], $query['parameters']);
                    if ($records->valid())
                        array_push($outputs, $records);
                }
                catch (RunDatabaseQueryError $exception)
                {
                    throw TransactDatabaseError($exception->getMessage());
                }
            }
            if (!$this->database->commit())
                throw new TransactDatabaseError('Commit transaction fail');
            return $outputs;
        }
        catch (TransactDatabaseError $exception)
        {
            $error_message = 'Transact database ERROR';
            if (!$this->database->rollBack())
                $error_message .= ' Rollback transaction fail. Please fix the mess directly on the database.';
            $error_message .= $exception->getMessage();
            throw new TransactDatabaseError($error_message);
        }
    }

    private static ?Database $singleton = null;
    static function get(): Database
    {
        if (null === Database::$singleton)
            Database::$singleton = new self;
        return Database::$singleton;
    }
}

class RunDatabaseQueryError extends Exception { }
class ConnectDatabaseError extends Exception { }
class TransactDatabaseError extends PDOException { }
