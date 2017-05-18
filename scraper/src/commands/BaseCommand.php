<?php 

namespace ActivismeBe\Scraper\Commands;

use Dotenv\Dotenv;
use PDO;
use PDOException;
use Symfony\Component\Console\Command\Command;
use ActivismeBe\Scraper\Exceptions\DatabaseException;

/**
 * The Base command class for the scraper.
 * 
 * @author      Tim Joosten <topairy@gmail.com>
 * @license     MIT License
 * @package     GEO-dataset
 */
class BaseCommand extends Command 
{
    /**
     *  Connection variable for the database server. 
     *
     * @var PDO $connection;
     */
    public $connection; 

    /**
     * Parent function for the Command classes. 
     *
     * @return PDO
     */
    public function __construct() 
    {
        parent::__construct(); // Call the construct from the command class first.

        $dotenv = new Dotenv(__DIR__ . '/../../');      // Locate the environment file.
        $dotenv->load();                                // Load the environment file.

        try { // To connection with the database server.
            $dsn              = 'mysql: dbname=' . getenv('DB_NAME') . ';host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT');
            $this->connection = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASS'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
            return $this->connection;
        } catch (PDOException $databaseException) { // Could not connect to the server.
            throw new DatabaseException($databaseException);
        }
    }
}
