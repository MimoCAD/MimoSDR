<?php
namespace MimoSDR;
opcache_invalidate(__FILE__, true);
use \TTG\DateTime;
use \TTG\Database;
use \DateTimeZone;

/**
 *
 */
class System
{
    /**
     * Setups the SQLite Datbase into the $this->sql for use with the rest of the functions.
     * 
     * @param MimoCAD\Database $sql
     */
    public function __construct(
        public Database $sql
    ){}

    /**
     * @return array [
     *  INT     'p25Id',
     *  STRING  'WACN',
     *  INT     'systemId',
     *  STRING  'nameShort',
     *  STRING  'nameLong'
     * ]
     */
    public function getSystems()
    {
        $select = $this->sql->prepare('SELECT * FROM p25');
        $select->execute();
        return $select->fetchAll();
    }

    /**
     * @param ?int $p25Id - Get only talk groups from this P25 system.
     * @return array [
     *  INT     'talkgroupId',
     *  INT     'p25Id',
     *  INT     'tgId',
     *  STRING  'mode',
     *  STRING  'alphaTag',
     *  STRING  'description',
     *  STRING  'tag',
     *  STRING  'group',
     *  INT     'priority'
     * ]
     */
    public function getTalkGroups(?int $p25Id = NULL)
    {
        if (is_null($p25Id))
        {
            $select = $this->sql->prepare('SELECT * FROM p25_talkgroups;');
            $select->execute();
        }
        else
        {
            $select = $this->sql->prepare('SELECT * FROM p25_talkgroups WHERE p25Id = :p25Id;');
            $select->execute([':p25Id' => $p25Id]);
        }

        return $select->fetchAll();
    }

    /**
     * @param string $start - DateTime string for Start Date to Search.
     * @param string $end - DateTime string for End Date to Search.
     * @param ?int $p25Id - Limit to just this p25 System.
     * @param ?int $tgId - Limit to just this Talk Group.
     * @return [
     *  INT         audioId
     *  INT         siteId
     *  INT         p25Id
     *  INT         tgId
     *  INT         freq
     *  DATETIME    timeStart
     *  DATETIME    timeStop
     *  BOOL        emergency
     *  TEXT        pathAudio
     *  INT         sizeAudio
     *  TEXT        pathJSON
     *  INT         sizeJSON
     * ]
     */
    public function getAudio(string $start = 'today', string $end = 'tomorrow', ?int $p25Id = NULL, ?int $tgId = NULL)
    {
        $start = (new DateTime($start, new DateTimeZone('America/New_York')));
        $end   = (new DateTime( $end , new DateTimeZone('America/New_York')));

        if ($p25Id !== NULL AND $tgId !== NULL)
        {
            $select = $this->sql->prepare('SELECT * FROM audio NATURAL JOIN p25_talkgroups WHERE timeStart BETWEEN :start AND :end AND p25Id = :p25Id AND tgId = :tgId ORDER BY audioId DESC;');
            $select->execute([
                'start' => $start->format('U'),
                'end' => $end->format('U'),
                'p25Id' => $p25Id,
                'tgId' => $tgId
            ]);
        }
        else if ($p25Id !== NULL)
        {
            $select = $this->sql->prepare('SELECT * FROM audio NATURAL JOIN p25_talkgroups WHERE timeStart BETWEEN :start AND :end AND p25Id = :p25Id ORDER BY audioId DESC;');
            $select->execute([
                'start' => $start->format('U'),
                'end' => $end->format('U'),
                'p25Id' => $p25Id
            ]);
        }
        else if ($tgId !== NULL)
        {
            $select = $this->sql->prepare('SELECT * FROM audio NATURAL JOIN p25_talkgroups WHERE timeStart BETWEEN :start AND :end AND tgId = :tgId ORDER BY audioId DESC;');
            $select->execute([
                'start' => $start->format('U'),
                'end' => $end->format('U'),
                'tgId' => $tgId
            ]);
        }
        else
        {
            $select = $this->sql->prepare('SELECT * FROM audio NATURAL JOIN p25_talkgroups WHERE timeStart BETWEEN :start AND :end ORDER BY audioId DESC;');
            $select->execute([
                'start' => $start->format('U'),
                'end' => $end->format('U'),
            ]);
        }
        return $select->fetchAll();
    }

}