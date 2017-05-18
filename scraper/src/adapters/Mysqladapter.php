<?php

namespace ActivismeBe\Dataset\Adapters;

use PDO;
use PDOException;
use Dotenv\Dotenv;

/**
 * Class MysqlAdapter
 *
 * @package ActivismeBe\Dataset\Adapters
 */
class MysqlAdapter
{
    /**
     * Provided variable for the database connection.
     * @var PDO
     */
    private $dbh;

    /**
     * Provided variable for pdo errors
     *
     * @var string
     */
    private $error;

    /**
     * Provided variable for the queries.
     *
     * @var $stmt
     */
    private $stmt;

    /**
     * MysqlAdapter constructor.
     *
     * @return MySQL database connection
     * @throws \PDOException
     */
    public function __construct()
    {
        $dotenv = new Dotenv(__DIR__ . '/../../');
        $dotenv->overload();

        $dsn     = 'mysql:host=' . getenv('DB_HOST') . ';dbname=' . getenv('DB_NAME');
        $options = [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];

        try { // Create a new PDO instance
            $this->dbh = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), $options);
        } catch (PDOException $e) { // Catch any errors
            $this->error = $e->getMessage();
        }
    }

    /**
     * [Prepare]
     *
     * The query method also introduces the PDO:prepare statement.
     *
     * The Prepare function allows you to vind values in your SQL Statements. This
     * is important because it take away the threat of SQL Injection vecause you are
     * no longer having manually include the parameters into the query string.
     *
     * Using the prepare function will also improve performance when rynning the
     * same query with different parameters multiple times.
     *
     * @param  string $query The database query.
     * @return void
     */
    public function query($query)
    {
        $this->stmt = $this->dbh->prepare($query);
    }

     /**
      * [Bind]
      *
      * The next method we will be looking at is the bind method. In order to prepare
      * our SQL queries, we need to bind the inputs with the placeholders we put in
      * place. This is what the Bind method used for.
      *
      * The main part of this method is based upon the PDOStatement::bindValue PDO
      * method.
      *
      * @param  string  $param  Is the actual placeholder value that we will be using in out SQL statement.
      * @param  string  $value  Is the actual param taht we want to bind to the placeholder.
      * @param  mixed   $type   Is the datatype of the parameter, example string. default = null
      * @return mixed
      */
     public function bind($param, $value, $type = null)
     {
         if (is_null($type)) {
             switch(true) {
                 case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
             }
         }

         $this->stmt->bindValue($param, $value, $type);
     }

     /**
      * [Execute]
      *
      * The next method we will be look at is the PDOStatement::execute. The execute
      * method executes the prepared statement.
      */
     public function execute()
     {
         return $this->stmt->execute();
     }

     /**
      * [resultSet]
      *
      * The Result Set function returns an array of the result set row. it uses the
      * PDOStatement::fetchAll PDO method. First we run the execute method, then we return
      * the results.
      *
      * @return array
      */
     public function resultSet()
     {
         $this->execute();
         return $this->stmt->fetch(PDO::FETCH_ASSOC);
     }

     /**
      * [single]
      *
      * very similar to the previous method, the Single method simply retuns a single record
      * from the database. Again first we run the execute method, then we return the single
      * result. This method uses the PDO method PDOStatement::fetch
      *
      * @return array
      */
     public function single()
     {
         $this->execute();
         return $this->stmt->fetch(PDO::FETCH_ASSOC);
     }

     /**
      * [rowCount]
      *
      * The next method simply return the numer of effected rows form the previous delete,
      * update or insert statement. This method use the PDO method.
      *
      * @return integer
      */
     public function rowCount()
     {
         return $this->stmt->rowCount();
     }

     /**
      * [kastInsertId]
      *
      * The last insert id returns the last inserted ID as a string. This method used the
      * PDO method PDO::lastInsertId.
      *
      * @return string
      */
     public function lastInsertId()
     {
         return $this->dbh->lastInsertId();
     }

     /**
      * Transactions
      *
      * Transactions allows you to run multiple changes to a database all in one batch to ensure
      * That your work will not be accessed incorrectly or there will be no outside interferences
      * before you are finished. If you are running many queries that all rely upon each other, if
      * one fails an exception will be thrown and you can roll back any previous changes to the
      * start of the transaction.
      *
      * For example, say you wanted to enter a new user in your system. The create new
      * user insert worked, but then you had to create the user configuration details in a
      * seperate statement. If the second statement fails, you could then roll back to the
      * beginning of the transaction.
      *
      * Transactions alos precent anyone accessing your database from seeing inconsistent data.
      * For example, say we created the user but someone accessed that data before the user
      * configuration was set? The accessing user would see incorrect data (a user without conf.)
      * which could potentially expose our system to errors.
      */

     /**
      * [beginTransaction]
      *
      * To begin a transaction.
      *
      * @return mixed
      */
     public function beginTransaction()
     {
         return $this->dbh->beginTransaction();
     }

     /**
      * To end a transaction
      *
      * @return mixed
      */
     public function endTransaction()
     {
         return $this->dhb->commit();
     }

     /**
      * To cancel a transaction and roll back your changes:
      *
      * @return mixed
      */
     public function cancelTransaction()
     {
         return $this->dbh->rollBack();
     }

     /**
      * [debugDumpParams]
      *
      * The Debug Parameters methds dumps the information that was contained in
      * the Prepared Statement. This method uses the PDOStatement::debugDumpParams
      * PDO Method:
      *
      * @return mixed
      */
     public function debugDumpParamas()
     {
         return $this->stmt->debugDumpParams();
     }
}
