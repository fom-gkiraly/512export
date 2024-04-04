<?php
namespace App;

use \PDO;

class DbManager
{
    private $host;
    private $user;
    private $password;
    private $database;

    /** @var PDO  */
    private $db = null;

    public function __construct($host, $user, $password, $database)
    {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->database = $database;
        register_shutdown_function(function() {
            echo "Disconnecting from database\n";
            $this->db = null;
        });
    }

    public function connect(): PDO
    {
        if ($this->db === null) {
            echo "Connecting to database\n";
            $this->db = new PDO(
                'mysql:host=' . $this->host . ';dbname=' . $this->database . ';charset=utf8',
                $this->user,
                $this->password
            );
            $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        }
        return $this->db;
    }

}
