<?php
namespace MimoSDR;

enum Access: int
{
    case LoggedOut     = 0;
    case LoggedIn      = (1 << 0);
    case Member        = (1 << 1);
    case Modarator     = (1 << 2);
    case Admin         = (1 << 3);
    case SuperAdmin    = (1 << 4);
    case MimoEmployee  = (1 << 5);
    case MimoMod       = (1 << 6);
    case MimoAdmin     = (1 << 7);
    case MimoFounder   = 255;

    public function style(): string
    {
        return match ($this)
        {
            Access::LoggedIn     => 'text-primary',
            Access::Member       => 'text-secondary',
            Access::Modarator    => 'text-success',
            Access::Admin        => 'text-danger',
            Access::SuperAdmin   => 'text-warning',
            Access::MimoEmployee => 'text-info',
            Access::MimoMod      => 'text-light',
            Access::MimoAdmin    => 'text-dark',
            Access::MimoFounder  => 'text-attention',
            default              => ''
        };
    }

    public function span(): string
    {
        return "<abbr title=\"MimoCAD Access Control Class\">Access</abbr>::<span class=\"{$this->style()}\">{$this->name}</span>";
    }

    public function test(int $flags): bool
    {
        return !!($this->value & $flags);
    }

    public function bool(int $flags): string
    {
        return ($this->test($flags)) ? '<span class="text-success">TRUE</span>' : '<span class="text-danger">FALSE</span>';
    }
}
