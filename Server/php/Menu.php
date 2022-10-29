<?php
namespace TTG;

class Menu
{
    const SQL_SELECT = 'SELECT * FROM menu WHERE flags & :flags ORDER BY sort ASC';

    public function __construct(public Database $sql) {}

    public function print()
    {
        $this->printHeader();
        $this->printMenuItems();
        $this->printFooter();
    }

    public function printHeader(string $brand = 'MimoCAD', string $img = '/favicon.ico')
    {
?>
        <header class="d-print-none">
            <nav class="navbar navbar-expand-md navbar-dark bg-dark">
                <div class="container">
                    <a class="navbar-brand" href="/">
                        <?=($img !== NULL) ? "<img src=\"$img\" width=\"30\" height=\"30\" class=\"d-inline-block align-top\" alt=\"$brand\"> $brand" : $brand?>

                    </a>
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navBar" aria-controls="navBar" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
<?php
    }

    public function printMenuItems()
    {
        $select = $this->sql->prepare(self::SQL_SELECT);
        $select->execute([':flags' => $_SESSION['flags'] ?? 0]);

?>
                    <div id="navBar" class="collapse navbar-collapse">
                        <ul class="navbar-nav me-auto mb-2 mb-md-0">
<?php   if (isset($_SESSION['departmentId']) AND 1 == $_SESSION['departmentId']):   ?>
                            <li class="nav-item"><a class="nav-link" href="//wlvac.mimocad.io"><i class="fa-fw fad fa-building"></i> WLVAC</a></li>
<?php   endif;  ?>
<?php   if (isset($_SESSION['departmentId']) AND 2 == $_SESSION['departmentId']):   ?>
                            <li class="nav-item"><a class="nav-link" href="//mvac.mimocad.io"><i class="fa-fw fad fa-building"></i> MVAC</a></li>
<?php   endif;  ?>
<?php   foreach ($select->fetchAll() as ['href' => $href, 'icon' => $icon, 'text' => $text]):  ?>
                            <li class="nav-item"><a class="nav-link<?=($_SERVER['SCRIPT_NAME'] == $href) ? ' active' : NULL?>" href="<?=$href?>"><i class="<?=$icon?>"></i> <?=$text?></a></li>
<?php   endforeach; ?>
                            <li class="nav-item"><a class="nav-link" href="?theme=<?=Page::getThemeInverse()?>" aria-label="Theme Invert"><i class="fa-fw fad fa-adjust"></i></a></li>
                        </ul>
                    </div>
<?php
    }

    public function printFooter()
    {
?>
                </div>
            </nav>
        </header>
<?php
    }
}
