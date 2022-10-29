<?php
namespace MimoSDR;
use \DateTimeZone;
use \TTG\DateTime;
use \TTG\Files;

class Audio
{
    const SQL_CREATE =<<<SQL
        CREATE TABLE sdr_sites (
            siteId INTEGER PRIMARY KEY,
            lat REAL,
            lng REAL,
            name TEXT,
            location TEXT
        );
        INSERT INTO sdr_sites (lat, lng, name, location) VALUES
        (0.0, 0.0, 'Santas Workshop', '1 Northpole Way');

        CREATE TABLE p25 (
            p25Id INTEGER PRIMARY KEY,
            WACN TEXT NOT NULL,
            systemId TEXT NOT NULL,
            nameShort TEXT NOT NULL,
            nameLong TEXT NOT NULL
        );
        INSERT INTO p25 (WACN, systemId, nameShort, nameLong) VALUES
        ('BEE00', '1AE', 'NCPD', 'Nassau County Police Department'),
        ('BEE00', '3CE', 'SCPD', 'Suffolk County Police Department');

        CREATE TABLE p25_talkgroups (
            talkgroupId INTEGER PRIMARY KEY,
            p25Id INTEGER NOT NULL,
            tgId INTEGER NOT NULL,
            mode TEXT NOT NULL,
            alphaTag TEXT NOT NULL,
            description TEXT NOT NULL,
            tag TEXT NOT NULL,
            'group' TEXT NOT NULL,
            priority INTEGER,
            FOREIGN KEY(p25Id) REFERENCES p25(p25Id)
        );

        CREATE TABLE p25_units (
            unitId INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
            p25Id INTEGER NOT NULL,
            src INTEGER NOT NULL,
            alias TEXT NOT NULL,
            startTime DATETIME NOT NULL,
            endTime DATETIME,
            FOREIGN KEY(p25Id) REFERENCES p25(p25Id)
        );

        CREATE TABLE audio (
            audioId INTEGER PRIMARY KEY,
            siteId INTEGER,
            p25Id INTEGER,
            tgId INTEGER,
            freq INTEGER,
            timeStart DATETIME,
            timeStop DATETIME,
            emergency BOOL,
            pathAudio TEXT,
            sizeAudio INTEGER,
            pathJSON TEXT,
            sizeJSON INTEGER,
            FOREIGN KEY(p25Id) REFERENCES p25(p25Id)
            FOREIGN KEY(siteId) REFERENCES sdr_sites(siteId)
        );

        CREATE TABLE audio_sources (
            sourceId INTEGER PRIMARY KEY,
            audioId INTEGER NOT NULL,
            src INTEGER,
            time DATETIME,
            pos REAL,
            emergency BOOL,
            signal_system TEXT,
            tag TEXT,
            FOREIGN KEY(audioId) REFERENCES audio(audioId)
        );

        CREATE TABLE audio_frequencies (
            frequencyId INTEGER PRIMARY KEY,
            audioId INTEGER NOT NULL,
            freq INTEGER,
            time DATETIME,
            pos REAL,
            len INTEGER,
            error_count INTEGER,
            spike_count INTEGER,
            FOREIGN KEY(audioId) REFERENCES audio(audioId)
        );
    SQL;

    const SQL_INSERT_AUDIO = 'INSERT INTO audio (siteId, p25Id, tgId, freq, timeStart, timeStop, emergency, pathAudio, sizeAudio, pathJSON, sizeJSON) VALUES (:siteId, :p25Id, :tgId, :freq, :timeStart, :timeStop, :emergency, :pathAudio, :sizeAudio, :pathJSON, :sizeJSON);';
    const SQL_INSERT_SOURCE = 'INSERT INTO audio_sources (audioId, src, time, pos, emergency, signal_system, tag) VALUES (:audioId, :src, :time, :pos, :emergency, :signal_system, :tag);';
    const SQL_INSERT_FREQUENCIES = 'INSERT INTO audio_frequencies (audioId, freq, time, pos, len, error_count, spike_count) VALUES (:audioId, :freq, :time, :pos, :len, :error_count, :spike_count);';

    /**
     * @property MimoCAD\Database $sql
     */
    public $sql;

    /**
     * @property MimoCAD\Files $wav
     */
    public $wav;
    /**
     * @property MimoCAD\Files $m4a
     */
    public $m4a;
    /**
     * @property MimoCAD\Files $json
     */
    public $json;

    /**
     * @property int $audioId
     */
    public int $audioId;

    /**
     * Setups the SQLite Datbase into the $this->sql for use with the rest of the functions.
     * 
     * @param MimoCAD\Database $sql
     */
    public function __construct(Database $sql)
    {
        $this->sql = $sql;
    }

    /**
     * Saves the Audio file from a POST request submitted by MimoSDR's MimoUpload.sh script.
     * @return true on success false on failure.
     */
    public function save(): bool
    {
        $path = "/{$_POST['year']}/{$_POST['month']}/{$_POST['day']}/";

        $this->wav = new Files();
        if ($this->wav->isOk('wav', false)) {
            if ($this->wav->moveSafeUpload('wav', $path)) {
                $fileLocation = $this->wav->getFilePath();
            } else {
                echo "Failed to move file WAV; {$_FILES['wav']['error']}" . PHP_EOL;
                return false;
            }
            $_FILES['wav']['path'] = $_SERVER['DOCUMENT_ROOT'] . $this->wav->getFilePath();
        } else {
            echo $this->wav->getError();
        }

        $this->m4a = new Files();
        if ($this->m4a->isOk('m4a', false)) {
            if ($this->m4a->moveSafeUpload('m4a', $path)) {
                $fileLocation = $this->m4a->getFilePath();
            } else {
                echo "Failed to move file M4A; {$_FILES['m4a']['error']}" . PHP_EOL;
                return false;
            }
            $_FILES['m4a']['path'] = $_SERVER['DOCUMENT_ROOT'] . $this->wav->getFilePath();
        } else {
            echo $this->m4a->getError();
        }

        $this->json = new Files();
        if ($this->json->isOk('json', false)) {
            if ($this->json->moveSafeUpload('json', $path)) {
                $fileLocation = $this->json->getFilePath();
            } else {
                echo "Failed to move file JSON; {$_FILES['json']['error']}" . PHP_EOL;
                return false;
            }
            $_FILES['json']['path'] = $_SERVER['DOCUMENT_ROOT'] . $this->json->getFilePath();
        } else {
            echo $this->json->getError();
        }

        return true;
    }

    /**
     * Inserts the Audio meta data from the POST request of MimoUpload.sh script into the database.
     * Takes generated JSON file and turns that into the refferental database tables.
     *
     * @param string $filePathAudio;
     * @param int $fileSizeAudio;
     * @param ?string $filePathJSON;
     * @param ?int $fileSizeJSON;
     * @param mixed $json;
     */
    public function insert(string $filePathAudio, int $fileSizeAudio, ?string $filePathJSON, ?int $fileSizeJSON, mixed $json)
    {
        $insert = $this->sql->prepare(self::SQL_INSERT_AUDIO);
        $insert->execute([
            ':siteId' => $_POST['siteId'],
            ':p25Id' => $_POST['p25Id'],
            ':tgId' => $json['talkgroup'] ?? null,
            ':freq' => $json['freq'] ?? null,
            ':timeStart' => $json['start_time'] ?? null,
            ':timeStop' => $json['stop_time'] ?? null,
            ':emergency' => $json['emergency'] ?? null,
            ':pathAudio' => $filePathAudio,
            ':sizeAudio' => $fileSizeAudio,
            ':pathJSON' => $filePathJSON,
            ':sizeJSON' => $fileSizeJSON,
        ]);
        $this->audioId = $this->sql->lastInsertId();

        foreach ($json['srcList'] ?? [] as $source)
        {
            $insert = $this->sql->prepare(self::SQL_INSERT_SOURCE);
            $insert->execute([
                ':audioId' => $this->audioId,
                ':src' => $source['src'],
                ':time' => $source['time'],
                ':pos' => $source['pos'],
                ':emergency' => $source['emergency'],
                ':signal_system' => $source['signal_system'],
                ':tag' => $source['tag']
            ]);
        }

        foreach ($json['freqList'] ?? [] as $frequency)
        {
            $insert = $this->sql->prepare(self::SQL_INSERT_FREQUENCIES);
            $insert->execute([
                ':audioId' => $this->audioId,
                ':freq' => $frequency['freq'],
                ':time' => $frequency['time'],
                ':pos' => $frequency['pos'],
                ':len' => $frequency['len'],
                ':error_count' => $frequency['error_count'],
                ':spike_count' => $frequency['spike_count'],
            ]);
        }
    }

    /**
     * Reads the JSON file and decodes it into an assocated array.
     *
     * @param string $path;
     */
    public function readJSONFile(string $path)
    {
        if (is_dir($path))
            return NULL;

        return json_decode(file_get_contents($path), true);
    }
}
