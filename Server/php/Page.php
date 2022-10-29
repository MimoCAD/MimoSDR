<?php
namespace TTG;

class Page
{
    const THEMES = [
        'automatic' => null,
        'amxmod' => '',
        'light' => '',
        'night' => '',
    ];

    private string $title = 'Title';
    private ?string $inlineScript = null;
    private ?string $style = null;
    private ?array $head = null;
    private bool $menu = true;
    private bool $footer = true;
    private ?float $pageLoadEnd = null;

    private $metas = [
        ['charset' => 'utf-8'],
        ['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no, viewport-fit=cover'],
        ['name' => "theme-color", 'content' => "#252830"]
    ];

    private $links = [
        # Latest compiled and minified CSS
        ['async', 'preload', 'rel' => 'stylesheet', 'crossorigin' => 'anonymous', 'integrity' => 'sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi', 'href' => '//cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css'],
        # Font Awesome
        ['async', 'preload', 'rel' => 'stylesheet', 'href' => '//mimocad.io/static/fa-5.13.0/css/all.css'],
        # Main MimoCAD Style
        ['async', 'preload', 'rel' => 'stylesheet', 'href' => '//mimocad.io/css/mimocad.css'],
    ];

    private $script = [
        # JavaScript Bundle with Popper
        ['crossorigin' => 'anonymous', 'integrity' => 'sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7qPCqsdltbNJuaOe923+mo//f6V8Qbsw3', 'src' => '//cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js'],
        # Font Awesome
        ['crossorigin' => 'anonymous', 'src' => '//kit.fontawesome.com/fa64f7bbed.js'],
    ];

    public $dateNow = null;

    public function __construct(\PDO $sql, bool|int|null $restrictedAccessLevel = NULL)
    {
        # Quick way of saying, you must be logged in.
        if (TRUE === $restrictedAccessLevel AND !User::isLoggedIn())
        {
            $_SESSION['error'] = 'You must be logged in to view this page.';
            header('Location: /login.php');
            die();
        }

        if (isset($_GET['theme']))
        {
            $_SESSION['theme'] = $_GET['theme'];
        }

        $this->link(['async', 'preload', 'rel' => 'stylesheet', 'href' => '//mimocad.io/css/' . $this->getTheme() . '.css']);

        $this->dateNow = new DateTime();
    }

    public static function getTheme(): string
    {
        $date = new DateTime();

        if (!isset($_SESSION['theme']) OR !in_array($_SESSION['theme'], array_keys(self::THEMES)))
        {
            $_SESSION['theme'] = 'automatic';
        }

        if ($_SESSION['theme'] == 'automatic')
        {
            return ($date > $date->sunrise() AND $date < $date->sunset()) ? 'light' : 'night';
        }
        else
        {
            return $_SESSION['theme'];
        }
    }

    public static function isThemeLight(): bool
    {
        return ($_SESSION['theme'] == 'light') ? true : false;
    }

    public static function getThemeInverse(): string
    {
        return (self::isThemeLight()) ? 'night' : 'light';
    }

    public static function getBootstrapInverse(): string
    {
        return (self::isThemeLight()) ? 'dark': 'light';
    }

    public function printPhoneNumber(int $number): string
    {
        $len = strlen($number);

        return match (true)
        {
            $len  < 7 => '',
            $len == 7 => '(516) ' . substr($number, 0, 3) . '-' . substr($number, 3, 4),
            $len ==10 => '(' . substr($number, 0, 3) . ') ' . substr($number, 3, 3) . '-' . substr($number, 6, 4),
            $len ==11 => '+' . substr($number, 0, 1) . ' (' . substr($number, 1, 3) . ') ' . substr($number, 4, 3) . '-' . substr($number, 6, 4),
            default => $number,
        };
    }

    public static function dateRangeStyle(DateTime $dateTime = null)
    {
        $dateTime = $dateTime ?? new DateTime($dateTime);
        $dateNow = new DateTimeImmutable();

        return match (true)
        {
            $dateTime < $dateNow                        => 'text-danger',
            $dateTime < $dateNow->modify("+1 Month")    => 'text-warning',
            $dateTime < $dateNow->modify("+3 Month")    => 'text-primary',
            $dateTime < $dateNow->modify("+6 Months")   => 'text-info',
            $dateTime > $dateNow->modify("+6 Months")   => 'text-success',
            default                                     => 'text-attention',
        };
    }

    public static function precentToStyle(string $table, $denominator, $numerator = 0): string
    {
        if (null === $denominator)
        {
            return 'null';
        }

        if (null === $numerator)
        {
            return 'null';
        }

        if (!in_array($table, ['missed', 'percent', 'require', 'hours']))
        {
            return '';
        }

        static $missed = [
            'text-success'      => [   0,  10],
            'text-info'         => [  10,  20],
            'text-primary'      => [  20,  30],
            'text-secondary'    => [  30,  40],
            'text-warning'      => [  40,  50],
            'text-danger'       => [  50, 100]
        ];

        static $percent = [
            'null'              => [   0,   0],
            'text-danger'       => [   0,  95],
            'text-warning'      => [  95, 100],
            'text-success'      => [ 100, 200],
            'text-attention'    => [ 200,9999]
        ];

        static $require = [
            'null'              => [   0,   0],
            'text-info'         => [   0,   5],
            'text-primary'      => [   5,  10],
            'text-warning'      => [  10,  15],
            'text-danger'       => [  15,  20],
            'text-secondary'    => [  20,  25],
            'text-dark'         => [  25, 100]
        ];

        static $hours = [
            'null'              => [   0,   0],
            'text-danger'       => [   0,  44],
            'text-warning'      => [  48,  96],
            'text-success'      => [  96, 192],
            'text-attention'    => [ 192,null]
        ];

        if ($table == 'percent' OR $table == 'missed') {
            $value = ($denominator == 0) ? $denominator : (($numerator / $denominator) * 100);
        }
        else
        {
            $value = $denominator;
        }

        foreach ($$table as $css => [$min, $max])
        {
            if ($value >= $min AND $value <= $max)
            {
                return $css;
            }
        }

        return '';
    }

    public function title($title)
    {
        $this->title = $title;
        $this->meta(['name' => 'Description', 'content' => $title]);
    }

    public function meta($meta)
    {
        $this->metas[] = $meta;
    }

    public function link($link)
    {
        $this->links[] = $link;
    }

    public function style($style)
    {
        $this->style .= $style;
    }

    public function linkReset()
    {
        $this->links = [];
    }

    public function linkReplace($index, $link = null)
    {
        $this->links[$index] = $link;
    }

    public function script($script)
    {
        $this->script[] = $script;
    }

    public function scriptInline($script)
    {
        $this->inlineScript .= $script;
    }

    public function head($header)
    {
        $this->head = $header;
    }

    public function menu($menu)
    {
        $this->menu = (bool) $menu;
    }

    public function footer(bool $display)
    {
        $this->footer = $display;
    }

    public function pageTime(bool $returnString = true)
    {
        $pageLoadDelta = round((array_sum(explode(' ', $this->pageLoadEnd)) * 1000) - (array_sum(explode(' ', $_SERVER['REQUEST_TIME_FLOAT'])) * 1000), 2);

        if ($returnString === false)
        {
            return $pageLoadDelta;
        }

        return match (true)
        {
            $pageLoadDelta < 8.3  => '<span class="text-attention">' . $pageLoadDelta . '</span>', # 120 Hz (Fluid)
            $pageLoadDelta < 16.7 => '<span class="text-success">'   . $pageLoadDelta . '</span>', # 60 Hz (Real Time)
            $pageLoadDelta < 100  => '<span class="text-info">'      . $pageLoadDelta . '</span>', # Brain Process Time
            $pageLoadDelta < 250  => '<span class="text-primary">'   . $pageLoadDelta . '</span>', # Body Response Time
            $pageLoadDelta < 400  => '<span class="text-warning">'   . $pageLoadDelta . '</span>', # Doherty Threshold
            default               => '<span class="text-danger">'    . $pageLoadDelta . '</span>', # Slow Reponse Time
        };
    }

    public function pageHz()
    {
        return round(round(1000000000000 / ($this->pageTime(false))) / 1000000000, 0);
    }

    public function emit()
    {
        if (isset($_GET['justBody']))
            return;

        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
    <head>

HTML;

        foreach ($this->metas as $meta)
        {
            echo "        <meta";
            foreach ($meta as $key => $val)
            {
                echo (is_numeric($key)) ? " $val" : " $key=\"$val\"";
            }
            echo ">" . PHP_EOL;
        }
        echo <<<HTML
        <title>{$this->title}</title>

HTML;
        foreach ($this->links as $link) {
            echo "        <link";
            foreach ($link ?? [] as $key => $val)
            {
                echo (is_numeric($key)) ? " $val" : " $key=\"$val\"";
            }
            echo ">" . PHP_EOL;
        }
        if ($this->style)
        {
            echo <<<HTML
        <style type="text/css">
{$this->style}
        </style>

HTML;

        }

        echo <<<HTML
    </head>
    <body class="d-flex flex-column h-100">

HTML;

        if (isset($_SESSION['error']))
        {
            if ($_SESSION['error'] instanceof \Exception)
            {
                echo <<<HTML
                        <div class="alert alert-danger" role="alert">
                            {$_SESSION['error']->getMessage()}
                        </div>
                HTML;
            }
            else
            {
                echo <<<HTML
                        <div class="alert alert-info" role="alert">
                            {$_SESSION['error']}
                        </div>
                HTML;
            }
            unset($_SESSION['error']);
        }

    }

    public function __destruct()
    {
        if (isset($_GET['justBody']))
        {
            return;
        }

        $this->pageLoadEnd = microtime(true);

        if ($this->footer === true):  ?>
        <footer class="footer mt-auto py-3 bg-dark d-print-none">
            <div class="text-light container text-center">
                Generated in <?=$this->pageTime();?> Milliseconds (<?=$this->pageHz();?> Hz).<br />
                &copy; <?=date('Y');?> <a class="text-reset" href="//MimoCAD.io">MimoCAD Inc</a> All rights reserved.
            </div>
        </footer>
<?php      endif;  ?>
<?php      foreach ($this->script as $script): ?>
        <script<?php foreach ($script as $key => $val) echo (is_numeric($key)) ? " $val" : " $key=\"$val\"";?>></script>
<?php      endforeach; ?>
<?php      if ($this->inlineScript !== null): ?>
        <script integrity="sha384-<?=base64_encode(hash('sha384', $this->inlineScript, true))?>">
<?=$this->inlineScript?>
        </script>
<?php      endif;  ?>
    </body>
</html><?php
    }
}