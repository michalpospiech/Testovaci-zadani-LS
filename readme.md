Testovací úkol pro Livesport.cz
=================

Aplikace byla zpracována výhradně jako testovací práce určena pro výběrové řízení společnosti Livesport s. r. o.

**Pro správné fungování je aplikaci nutné spustit v PHP min. verzi 7.2.**

Již v instalačním procesu aplikace předpokládá, že má plný přístup ke všem souborům/adresářům s
právem zápisu. Jestliže tomu tak není, tak je zapotřebí pro celý adresář, ve kterém je stažený
repozitář, tyto práva povolit.

Instalace
------------
Po stažení repozitáře v příkazovém řádku přejdi do rootu aplikace a spusť následující příkazy:

```sh
$ composer install
```

**Vytvoření konfiguračního souboru**

Aplikace se sama doptá na potřebná nastavení (mysql host, username, ...). Posléze si sama vytvoří
konfigurační soubor. Pro správný běh je nutné mít již vytvořeného mysql uživatele a databázi.
```sh
$ php www/index.php app:install
```

**Vytvoření databáze**
```sh
$ php www/index.php app:install-database
```

Spuštění aplikace
---------------
V případě, že byla aplikace v pořádku nainstalována, tak je možné spustit import výchozích
CSV souborů, které jsou uloženy v adresáři `./input`.

**Import dat**
```sh
$ php www/index.php app:import
```

**Export dat**
```sh
$ php www/index.php app:export
```

Po vyexportování dat bude vytvořen soubor v adresáři `./output`.