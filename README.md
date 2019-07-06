# News

Representação de notícias com CRUD.

## Exemplo Cadastro

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Ciebit\News\News;
use Ciebit\News\Status;
use Ciebit\News\Storages\Database\Sql;
use PDO;

$news = News('Title News', Status::ACTIVE());
$news->setBody('Text');

$database = new Sql(new PDO('mysql:dbname=cb_news;host=localhost;charset=utf8', 'user', 'password'));
$database->store($news);

echo $news->getId();
```

## Exemplo de Busca de uma notícia pelo ID

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Ciebit\News\Storages\Database\Sql;
use PDO;

$database = new Sql(new PDO('mysql:dbname=cb_news;host=localhost;charset=utf8', 'user', 'password'));
$news = $database->addFilterById('=', '1')->findOne();

echo $news->getTitle();

```


## Exemplo de Busca de várias notícias através da data

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Ciebit\News\Storages\Database\Sql;
use DateTime;
use PDO;

$database = new Sql(new PDO('mysql:dbname=cb_news;host=localhost;charset=utf8', 'user', 'password'));
$newsCollection = $database->addFilterByDateTime('>', new DateTime('2019-07-06'))->findAll();

foreach($newsCollection as $news) {
    echo $news->getTitle() . PHP_EOL;
}

```
