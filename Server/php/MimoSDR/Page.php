<?php
namespace MimoSDR;
use TTG\Database;

class Page extends \TTG\Page
{
    public function __construct(Database $sql, ?int $restrictedAccessLevel = null)
    {
        parent::__construct($sql);
    }

    public function getHzColor(int $Hz): string
    {
        $MHz = $Hz / 1_000_000;
        return match (TRUE) {
            $MHz >= 136 AND $MHz <= 174 => 'text-primary',  # VHF
            $MHz >= 300 AND $MHz <= 520 => 'text-danger',   # UHF
            $MHz >= 764 AND $MHz <= 870 => 'text-attention',# 700
            $MHz >= 896 AND $MHz <= 901 => 'text-success',  # 800
            $MHz >= 935 AND $MHz <= 940 => 'text-success',  # 800
            default => 'null'
        };
    }

    public function getSizeKB(int $bytes): string
    {
        return round($bytes / 1024);
    }
}
