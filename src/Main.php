<?php
namespace App;

use \PDO;

class Main
{
    const BASE_DIR = 'galery';
    const OUTPUT_DIR = 'output';

    const DB_HOST = 'localhost';
    const DB_USER = 'root';
    const DB_PASSWORD = '';
    const DB_DATABASE = '512club';

    /** @var PDO */
    private $db;

    public function __construct()
    {
        $this->init();
        $this->run();
    }

    private function init()
    {
        // for accented filenames
        setlocale(LC_CTYPE, "en_US.UTF-8");

        // db connection
        $this->db = (new DbManager(
            self::DB_HOST,
            self::DB_USER,
            self::DB_PASSWORD,
            self::DB_DATABASE
        ))->connect();
    }

    private function run()
    {
        echo "Exporting images\n";
        $db = $this->db;
        $kepkats = [];
        $sql = '
            SELECT * 
            FROM 512_galeria_kepkat 
            ORDER BY kepkat_sorr
        ';
        foreach ($db->query($sql)->fetchAll() as $row) {
            $row->nev = rtrim(trim(strtr($row->nev, ['?' => '-', '/' => '-',])), '.');
            $dirname = sprintf(
                '[%03d_%s] %s',
                $row->kepkat_sorr,
                strtr($row->datum, [' ' => '', ':' => '', '-' => '']),
                $row->nev
            );
            $row->dirname = $dirname;
            $kepkats[$row->kepkat_id] = $row;
            @mkdir(self::OUTPUT_DIR . '/' . $dirname, 0777, true);
        }

        $sql = '
            SELECT 512_galeria_kep.*, 512_felh.nev AS felh_nev 
            FROM 512_galeria_kep 
            LEFT JOIN 512_felh ON 512_galeria_kep.felh_id = 512_felh.felh_id 
            ORDER BY kep_id
        ';
        foreach ($db->query($sql)->fetchAll() as $row) {
            $dirname = $kepkats[$row->kepkat_id]->dirname;
            $name = sprintf(
                '[%05d%05d_%s_%s] %s',
                $row->felh_id,
                $row->kep_id,
                strtr($row->datum, [' ' => '', ':' => '', '-' => '']),
                $row->felh_nev,
                $row->nev
            );
            copy(
                self::BASE_DIR . '/' .
                'kat_' . $row->kepkat_id . '/' .
                'felh_' . $row->felh_id . '/' .
                'images/' .
                $row->nev,
                self::OUTPUT_DIR . '/' . $dirname . '/' . $name
            );
        }
        echo "Export finished\n";
    }

}
