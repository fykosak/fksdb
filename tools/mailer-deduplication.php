<?php

use FKSDB\Utils\CSVParser;
use Nette\DI\Container;
use Nette\Mail\Message;

/**
 * @var Container $container
 */
$container = require './bootstrap.php';

$mailer = $container->getByType('Nette\Mail\IMailer');

$argv = $_SERVER['argv'];

if(!isset($argv[1])) {
    echo "Usage: ${argv[0]} input.csv\n";
    exit(1);
}


$parser = new CSVParser($argv[1], CSVParser::INDEX_FROM_HEADER);
foreach ($parser as $row) {
    $body = <<<EOD
Dobrý den,
skript na hledání duplicit nám vyhodnotil, že dvě osoby {$row['name1']}
s e-maily uvedenými výše by mohly být táž reálná osoba.
Patří tyto e-maily oba jedné osobě?

V případě sloučení, který e-mail si přejete zachovat?

Díky,
FKSDB
EOD;



    $message = new Message();
    $message->setBody($body);
    $message->addTo($row['email1'], $row['name1']);
    $message->addTo($row['email2'], $row['name2']);
    $message->setSubject('FKSDB identita');
    $message->addReplyTo('webmaster@fykos.cz');

    $mailer->send($message);

    echo "Mail to {$row['email1']} and {$row['email2']} sent.\n";
    sleep(rand(0, 4));
}
