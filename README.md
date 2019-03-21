需要使用到https://packagist.org/packages/symfony/finder
和 http://php.net/manual/en/class.reflectionclass.php

在專案中，
php bin/console doctrine:database:create，建立DataBase
以及
php bin/console doctrine:migrations:migrate，建立table

使用，php bin/console list 查詢可以用的command 指令
抓取method
php bin/console crawler:crawl-method + 目標位置(ex:"D:\wegames\core")
這樣子就會去找core底下所有.php檔案


