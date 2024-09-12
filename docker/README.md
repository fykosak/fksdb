# Docker container
- nutnost: nainstalovaný `docker` a `docker-compose-plugin`
- vytvoření aktuální image: `docker compose build` (potřeba spustit před prvním spuštěním)
- vytvoření mount složky `log` a `upload` (případně `chown`, aby byla vlastněna uživatelem, pod kterým kontejner poběží)
- spuštění: `docker compose up`, případně `docker compose up --remove-orphans`
- přístupné na `localhost:8080`

## Vývoj
### První spuštění
1. vytvoříme image pro spuštění
```bash
docker compose build
```
2. překopírujeme potřebné config soubory
```bash
cp ../app/config/config.local.neon.sample ../app/config/config.local.neon
```
3. spustíme kontejnery
```bash
docker compose up
```
případně je spustíme v pozadí
```bash
docker compose up -d
```

4. otevřeme FKSDB v prohlížeči na adrese `localhost:8080`

5. pokud potřebujeme spouštět příkazy, otevřeme si příkazovou řádku uvnitř kontejneru
```bash
docker compose exec -it app bash
```
nebo jednorázově spustíme příkaz uvnitř kontajneru
```bash
docker compose exec -it app <příkaz>
```
6. vypneme kontejnery pomocí `CTRL-C`, pokud běží v popředí, `docker compose stop` pokud běží v pozadí

## Testování
- `composer run initTestDatabase`
- `composer run test`
