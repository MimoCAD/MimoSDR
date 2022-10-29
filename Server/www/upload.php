<?php
namespace MimoSDR;
require('../autoload.php');

// Read JSON file and Import to SQL Database.
$MimoSDRDB = new Database('/mnt/db/MimoSDR.db');
$Audio = new Audio($MimoSDRDB);
$Audio->save();
$Audio->insert(
    $Audio->wav->getFilePath(),
    $Audio->wav->getFileSize(),
    $Audio->json->getFilePath(),
    $Audio->json->getFileSize(),
    $Audio->readJSONFile($_SERVER['DOCUMENT_ROOT'] . $Audio->json->getFilePath())
);
