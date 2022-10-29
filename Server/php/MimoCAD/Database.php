<?php
namespace MimoCAD;

class Database extends \PDO
{
    /**
     * @param string $file - The file path where the SQLite database is.
     */
    public function __construct($file)
    {
        parent::__construct('sqlite:' . $file);
        $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->query('PRAGMA foreign_keys = ON;');
    }

    /**
     * @param string $statement - SQL Statement to prepare.
     * @param array $options - Options to fill in.
     * @return \PDOStatement - So it's compataible with the base PDO class.
     */
    public function prepare($statement, $options = []): \PDOStatement
    {
        return parent::prepare($statement, $options);
    }

    /**
     * MimoCAD Database Interactions
     */

    const SQL_DEPARTMENTS = 'SELECT * FROM departments WHERE departmentEnabled = TRUE;';
    const SQL_GET_MAX_ID = 'SELECT MAX(ID) AS ID FROM firecom WHERE FDID = :fdId';
    const SQL_GET_COUNT = "SELECT substr(strftime('%Y', 'now', 'localtime'), 3) || '-' || printf('%.04d', COUNT(*)) FROM firecom WHERE FDID = :fdId AND TRANS1 BETWEEN strftime('%Y-01-01T00:00:00.000') AND strftime('%Y-12-31T23:59:59.999');";
    const SQL_GET_FDID = 'SELECT FDID FROM departments_firecom WHERE departmentId = :departmentId';
    const SQL_GET_DEPARTMENT = 'SELECT departmentId FROM departments_firecom WHERE FDID = :FDID';

    /**
     * @return array - An array of enabled departments.
     */
    public static function getDepartments(): array
    {
        global $mimocad;

        $statement = $mimocad->prepare(self::SQL_DEPARTMENTS);
        $statement->execute();
        return $statement->fetchAll();
    }

    /**
     * This function takes the MimoCAD Department ID
     * and returns the FireCom FDID for that department or
     * if there is no FDID for that department, a NULL.
     * 
     * @param int $departmentId - MimoCAD Department ID
     * @return ?int - FDID for that department, or NULL.
     */
    public static function departmentId2FDID(?int $departmentId): ?int
    {
        if (is_null($departmentId))
            return NULL;

        global $mimocad;

        $select = $mimocad->prepare(self::SQL_GET_FDID);
        $select->execute([':departmentId' => $departmentId]);
        return $select->fetchColumn() ?? NULL;
    }

    /**
     * This function takes the MimoCAD Department ID
     * and returns the FireCom FDID for that department or
     * if there is no FDID for that department, a NULL.
     * 
     * @param int $departmentId - MimoCAD Department ID
     * @return ?int - FDID for that department, or NULL.
     */
    public static function FDID2departmentId(int $FDID): ?int
    {
        if (is_null($FDID))
            return NULL;

        global $mimocad;

        $select = $mimocad->prepare(self::SQL_GET_DEPARTMENT);
        $select->execute([':FDID' => $FDID]);
        return $select->fetchColumn() ?? NULL;
    }
    /**
    * @param int $departmentId - MimoCAD Department ID
    * @return ?int - The MAX ID or NULL;
    */
    public static function getMaxId(int $departmentId): ?int
    {
        global $mimocad;

        // Turn DepartmentId into FDID.
        if (NULL === ($fdId = self::departmentId2FDID($departmentId)))
        {
            return NULL;
        }

        // Get the MAX ID for this department.
        $statement = $mimocad->prepare(self::SQL_GET_MAX_ID);
        $statement->execute([':fdId' => $fdId]);
        return $statement->fetchColumn();
    }

    /**
    * @param int $departmentId - MimoCAD Department ID
    * @return ?string yy-nnnn - Two digit year, zero padded number or NULL.
    */
    public static function getYTDCount(int $departmentId): ?string
    {
        global $mimocad;

        // Turn DepartmentId into FDID.
        if (NULL === ($fdId = self::departmentId2FDID($departmentId)))
        {
            return NULL;
        }

        // Get the alarm count for this department.
        $statement = $mimocad->prepare(self::SQL_GET_COUNT);
        $statement->execute([':fdId' => $fdId]);
        return $statement->fetchColumn();
    }

    /**
     * @param ?string - Name of the sequence object from which the ID should be returned. 
     * @return string|false - Returns the ID of the last inserted row or sequence value.
     */
    public static function getLastRowId(?string $name = null): string|false
    {
        global $mimocad;

        return $mimocad->lastInsertId($name);
    }

    /**
    * @param array $alarm Array of alarm data key => value pair.
    * @return bool 
    */
    public static function setAlarmData(array $alarm): bool
    {
        global $mimocad;

        $row = 'INSERT INTO firecom (' . implode(', ', array_keys($alarm)) . ') VALUES (\'' . implode("', '", array_values($alarm)) . '\');' . PHP_EOL;

        try {
            $mimocad->query($row);
        } catch (\PDOException $e) {
            echo 'Exception: ' . $e->getMessage() . ' (' . $e->getCode() . ')' . PHP_EOL;
            debug_print_backtrace();
            return false;
        }

        return true;
    }

    /**
    * @return PDO
    */
    public static function getHandle(): \PDO
    {
        global $mimocad;

        return $mimocad;
    }

}
