# Docker container
- nutnost: nainstalovaný `docker` a `docker-compose-plugin`
- vytvoření aktuální image: `docker compose build` (potřeba spustit před prvním spuštěním)
- vytvoření mount složky `log` a `upload` (případně `chown`, aby byla vlastněna uživatelem, pod kterým kontejner poběží)
- spuštění: `docker compose up`, případně `docker compose up --remove-orphans`
- přístupné na `localhost:8080`
