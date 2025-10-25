<?php
use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;

class ChatController {
    public function listen() {
        DriverManager::loadDriver(\BotMan\Drivers\Web\WebDriver::class);

        $config = []; // tom config for WebDriver
        $botman = BotManFactory::create($config);

        $botman->hears('pris på {produkt}', function (BotMan $bot, $produkt) {
            $ean = KassalappModel::finnEan($produkt);
            $prisinfo = KassalappModel::hentPris($ean);
            $bot->reply("Den billigste prisen for $produkt er {$prisinfo['pris']} kr hos {$prisinfo['butikk']}.");
        });

        $botman->listen();
    }
}
?>