# News

Representação de notícias com CRUD.

## Exemplo Cadastro

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Ciebit\News\News;
use Ciebit\News\Status;
use Ciebit\News\Factory\NewsFactory;
use Ciebit\News\Storages\Database\Sql;
use PDO;

$news = (news NewsFactory)
    ->setTitle('Title News')
    ->setStatus(Status::ACTIVE())
    ->setBody('Text')
    ->create();

$database = new Sql(new PDO('mysql:dbname=cb_news;host=localhost;charset=utf8', 'user', 'password'));
$id = $database->store($news);

echo $id;
```

## Exemplo de Busca de uma notícia pelo ID

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Ciebit\News\Storages\Database\Sql;
use PDO;

$database = new Sql(new PDO('mysql:dbname=cb_news;host=localhost;charset=utf8', 'user', 'password'));
$newsCollection = $database->addFilterById('=', '1')->find();

echo $newsCollection->getArrayObject()->offsetGet(0)->getTitle();

```


## Exemplo de Busca de várias notícias através da data

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Ciebit\News\Storages\Database\Sql;
use DateTime;
use PDO;

$database = new Sql(new PDO('mysql:dbname=cb_news;host=localhost;charset=utf8', 'user', 'password'));
$newsCollection = $database->addFilterByDateTime('>', new DateTime('2019-07-06'))->find();

foreach($newsCollection as $news) {
    echo $news->getTitle() . PHP_EOL;
}

```
