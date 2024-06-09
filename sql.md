# konvencia pomenovánia SQL

- Používame snake_case na všetko 
- s výnimkou obsahu `ENUM` napr. `ENUM('myState,'otherState')`

## Primarny klúč 

- `<table_name>_id`
- nepoužívame len `id`
- nepoužívame skratky tabuliek `fyziklani_team->fyziklani_team_id` nie `team_id`
- ak je to možné použiť `INT UNSIGNED`

## Datetime vs Timestamp

- `TIMESTAMP` v prípade logovania, teda dátumu zmeny, vytvorenia...
- `DATETIME` (`DATE`/`TIME`) v prípadoch uživateľom zadaného dátumu napr. deadline, začiatok/koniec a pod.

## Pomenovanie CONSTRAINT

- `INDEX`: `idx__<table_name>__<field>[__<filed>[...]]`
- `FOREIGN KEY`: `fk__<table_name>__<references_table_name>__<field>`
- `UNIQUE [KEY|INDEX]` a :`uq__<table_name>__<field>[__<filed>[...]]`

## Ostatné vlastnosti

- charset `utf8` collage `utf8_czech_ci`
- engine `InnoDB`
```sql
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_czech_ci;
```
- používať `BOOL` namiesto `INT(1)` nette/DB to konvertuje do `bool`
- nebáť sa používať `ENUM` - ORM si s tým poradí 
- vyhýbať sa views
