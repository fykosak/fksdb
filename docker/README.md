# Docker
- předpoklady: nainstalovaný `docker` a `docker-compose-plugin` [návod pro Ubuntu](https://docs.docker.com/engine/install/ubuntu/#install-using-the-repository)

## Vývoj
### První spuštění

0. vnoříme se do `docker` složky, pokud zde už nejsme
```bash
cd docker
```
1. překopírujeme potřebné config soubory
```bash
cp ../app/config/config.local.neon.sample ../app/config/config.local.neon
```
2. vytvoříme image pro spuštění
```bash
docker compose build
```
3. spustíme kontejnery
```bash
docker compose up
```
případně je spustíme v pozadí
```bash
docker compose up -d
```

4. pokud potřebujeme spouštět příkazy, otevřeme si příkazovou řádku uvnitř kontejneru
```bash
docker compose exec -it app bash
```
nebo jednorázově spustíme příkaz uvnitř kontejneru
```bash
docker compose exec -it app <příkaz>
```
- při prvním spuštění je potřeba nainstalovat balíčky, co FSKDB využívá a zkompilovat překlady
```bash
./i18n/compile.sh
python3 ./i18n/compile-js.py
composer install
npm install
```
- při prvním spuštění a každé změně SCSS nebo TS souborů je potřeba tyto soubory zkompilovat
```bash
npm run build
```
nebo je možné využít `dev` módu, když tyto soubory měníme často a chceme si je nechat kompilovat průběžně
```bash
npm run dev
```

5. otevřeme FKSDB v prohlížeči na adrese `localhost:8080`

6. přihlásíme se do adminera (`localhost:8080/adminer`) (server `mariadb`, jméno `fksdb`, heslo `password`), inicializujeme data a vyzkoušíme

7. vypneme kontejnery pomocí `CTRL-C`, pokud běží v popředí, `docker compose stop` pokud běží v pozadí

### Následné použití
Obvykle nebudete nic měnit něco, co by se týkalo dockeru, stačí tedy ve složce `docker` zopakovat
kroky 3, 4, 5 a 7. Pokud potřebujeme upravit samotný dev docker a vyzkoušet změny, provedeme i krok 2.

### Testování
Před prvním spuštěním testů je potřeba inicializovat testovací databáze pomocí `composer run initTestDatabase`.
Následně můžeme testy spouštět pomocí `composer run test`.

## Produkce
- použijeme uvedený `docker-compose.prod.yml` soubor, případně upravíme podle potřeby
- vytvoříme mount složky `log`, `temp` a `upload` (případně provedeme `chown`, aby byly vlastněny uživatelem, pod kterým kontejner poběží)
- zkopírujeme `app/config/config.local.neon.sample` do `config.neon` a upravíme zde potřebné údaje (hlavně heslo k databázi)
- zkopírujeme `docker/config/msmtprc` do `msmtprc` (pokud chceme aby nám fungoval email) a upravíme potřebné údaje
- spustíme

### Spuštění jako uživatel
Protože je potřeba cron spouštět jako root, je kontejner by default spuštěn jako root uživatel a v `entrypoint.sh`
následně vytvoří uživatele a skupinu `fksdb`, která má ID podle ENV parametrů `PUID` a `GUID` a spustí `apache` pod
tímto uživatelem.

### Email
V produkčním image je nainstalován program `msmtprc`. Ten je určen pro odesílání mailů a balíček `msmtp-mta` vytváří
alias pro `sendmail`, aby to bylo možné z PHP dělat. Tento program ale není plnohodnotný mail server, slouží pouze jako
relay pro maily na plnohodnotný mailový server, například `postfix` na hlavním OS.
