<?php
namespace MimoSDR;
opcache_invalidate(__FILE__, true);
require('../bootstrap.php');
use \DateTimeZone;
use \TTG\DateTime;
use \TTG\DateTimeImmutable;

$MimoSDR = new Database('/mnt/db/MimoSDR.db');

$page = new Page($MimoSDR);
$page->title('Software Defined Radio');
$page->meta(['name' => 'apple-mobile-web-app-capable', 'content' => 'yes']);
$page->meta(['name' => 'apple-mobile-web-app-status-bar-style', 'content' => 'black']);
$page->meta(['name' => 'apple-mobile-web-app-title', 'content' => 'MimoSDR']);
$page->meta(['name' => 'format-detection', 'content' => 'telephone=no']);
$page->link(['rel' => 'apple-touch-icon', 'href' => 'favicon.ico']);

$page->link(['rel' => 'preconnect', 'href' => '//fonts.googleapis.com']);
$page->link(['rel' => 'preconnect', 'href' => '//fonts.gstatic.com', 'crossorigin']);
$page->link(['rel' => 'stylesheet', 'href' => '//fonts.googleapis.com/css2?family=Big+Shoulders+Display&display=swap']);

$page->style(<<<CSS
            body {
                -webkit-user-select: none;
                -webkit-touch-callout: none;
                -webkit-tap-highlight-color: transparent;
            }

            #interface, audio {
                width: 100%;
            }
            table {
                text-align: center;
            }
            tbody tr {
                cursor: pointer;
            }
            a {
                color: #FA0;
            }
            a:hover {
                color: #FC0;
            }
            a:active {
                color: #FF0;
            }
CSS
);
$page->emit();
(new Menu($MimoSDR))->print();

$System = new System($MimoSDR);

?>
        <main class="container">
            <form method="get">
                <div class="row">
                    <div class="form-group col-lg-3">
                        <label class="form-control-label" for="tgId">Talk Group</label>
                        <select class="form-control" id="tgId" name="tgId">
<?php   foreach ($System->getSystems() as ['p25Id' => $p25Id, 'nameShort' => $nameShort]):    ?>
                            <optgroup label="<?=$nameShort?>">
<?php       foreach ($System->getTalkGroups($p25Id) as ['tgId' => $tgId, 'alphaTag' => $alphaTag]): ?>
                                <option value="<?=$tgId?>"<?=(isset($_GET['tgId']) AND $tgId == $_GET['tgId']) ? ' selected' : NULL?>><?=$alphaTag?></option>
<?php       endforeach; ?>
                            </optgroup>
<?php   endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group col-lg-3">
                        <label class="form-control-label" for="dateStart">Date Start</label>
                        <input class="form-control" id="dateStart" name="dateStart" type="datetime-local" value="<?=(new DateTime($_GET['dateStart'] ?? 'today'))->format(DateTime::WEB)?>" />
                    </div>
                    <div class="form-group col-lg-3">
                        <label class="form-control-label" for="dateEnd">Date End</label>
                        <input class="form-control" id="dateEnd" name="dateEnd" type="datetime-local" value="<?=(new DateTime($_GET['dateEnd'] ?? 'tomorrow'))->format(DateTime::WEB)?>" />
                    </div>
                    <div class="form-group col-lg-3">
                        <label class="form-control-label">Controls</label>
                        <button class="btn btn-outline-success btn-block" type="submit">View</button>
                    </div>
                </div>
            </form>
            <div class="sticky-top" style="padding-top: 4em; margin-left: 0; margin-right: 0; background: #252830">
                <div class="form-group col-lg-12">
                    <audio preload="none" controls>
                        Sorry, your browser does not support HTML5 audio.
                    </audio>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <td>Time</td>
                        <td>Talk Group</td>
                        <td>MHz</td>
                        <td>Size</td>
                    </tr>
                </thead>
                <tbody>
<?php   foreach ($System->getAudio($_GET['dateStart'] ?? 'today', $_GET['dateEnd'] ?? 'tomorrow', $_GET['p25Id'] ?? NULL, $_GET['tgId'] ?? NULL) as ['timeStart' => $timeStart, 'alphaTag' => $alphaTag, 'freq' => $freq, 'pathAudio' => $pathAudio, 'sizeAudio' => $sizeAudio]): ?>
                    <tr data-index="<?=$timeStart?>">
                        <td><?=(new DateTime('@'.$timeStart, new DateTimeZone('UTC')))->setTimeZone(new DateTimeZone('America/New_York'))->format('H:i:s')?></td>
                        <td><?=$alphaTag?></td>
                        <td class="<?=$page->getHzColor($freq)?>"><?=sprintf("%3.4f", $freq / 1_000_000)?></td>
                        <td><a href="<?=$pathAudio?>"><?=$page->getSizeKB($sizeAudio)?>kB</a></td>
                    </tr>
<?php   endforeach; ?>
                </tbody>
            </table>
        </main>
        <script>
            var index, audio, tbody, tr;
            window.onload = _ => {
                index = null,
                audio = document.querySelector('audio'),
                tbody = document.querySelector('tbody'),
                   tr = [...tbody.getElementsByTagName('tr')];

                tbody.addEventListener('click', click => {
                    if (tr[index])
                        tr[index].classList.toggle('bg-secondary');

                    tr.forEach((thisRow, i) => {
                        if (click.target.parentElement == thisRow) {
                            index = i;
                            tr[index].classList.toggle('bg-secondary');
                        }
                    });

                    audio.src = tr[index].querySelector('a');
                    audio.load();
                    audio.play().catch(error => {
                        console.log(`${error.name}: ${error.message}`);
                    });
                }, false);

                audio.addEventListener('ended', () => {
                    tr[index--].classList.toggle('bg-secondary');
                    if (tr[index])
                        tr[index].click();
                }, false);

                // Create WebSocket connection.
                const socket = new WebSocket('wss://radio.mimocad.io/ws');

                // Connection opened
                socket.addEventListener('open', (event) => {
                    socket.send('Connected');
                });

                // getMHzColor
                function getMHzColor(MHz)
                {
                    switch (true)
                    {
                        case MHz >= 136 && MHz <= 174:
                            return 'text-primary';  // VHF
                        case MHz >= 300 && MHz <= 520:
                            return 'text-danger';   // UHF
                        case MHz >= 764 && MHz <= 870:
                            return 'text-attention';// 700
                        case MHz >= 896 && MHz <= 901:
                        case MHz >= 935 && MHz <= 940:
                            return 'text-success';  // 800
                        default:
                            return 'null';
                    };
                }

                // Listen for messages
                socket.addEventListener('message', (event) => {
                    console.log('Message from server ', event.data);
                    let data = JSON.parse(event.data);

                    pathParts = data.path.split(/[-_\/\.]/);
                    console.table(pathParts);

                    // Unix Timestamp to Local Time String
                    let date = new Date(pathParts[5] * 1000);
                    let time = date.toTimeString().match(/(\d{2}:\d{2}:\d{2})/)[1];

                    // Talk Group ID to Talk Group Alpha Tag
                    let alphaTag = tgId.querySelector(`option[value="${pathParts[4]}"]`).innerText;

                    // Hz to MHz.
                    let MHz = (pathParts[6] / 1_000_000).toFixed(4);

                    // Size
                    let kB = Math.round(data.size / 1024);

                    if (data.kind == 'new' && data.type == '.m4a')
                    {
                        tbody.insertAdjacentHTML('afterbegin', `
                            <tr data-index="${pathParts[5]}">
                                <td>${time}</td>
                                <td>${alphaTag}</td>
                                <td class="${getMHzColor(MHz)}">${MHz}</td>
                                <td>
                                    <a href="${data.path}">${kB}kB</a>
                                </td>
                            </tr>`
                        );
                    }

                    tr = [...tbody.getElementsByTagName('tr')];

                });

                // Every 10 seocnds send a ping.
                setInterval(() => {
                    if (socket.readyState == 1) {
                        socket.send('ping');
                    }
                }, 10000);
            }
        </script>
