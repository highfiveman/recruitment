API zostało przygotowane na środowisku lokalnym za pomocą XAMPP w języku PHP w wersji 8.2.12, frameworka Symfony 7.3 oraz 10.4.32-MariaDB

INSTRUKCJA

SQL:
Tabela - USER

CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, username VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'

Tabela - CALCULATION

CREATE TABLE calculation (id INT AUTO_INCREMENT NOT NULL, amount DOUBLE PRECISION NOT NULL, installments INT NOT NULL, interest_rate DOUBLE PRECISION NOT NULL, calculated_at DATETIME NOT NULL, total_interest DOUBLE PRECISION NOT NULL, is_excluded TINYINT(1) NOT NULL, schedule JSON NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'

DOSTĘPNE REQUESTY: 

Kalkulacja rat dostępna publicznie:
POST http://127.0.0.1:8000/api/calculation 
Body: x-www-form-urlencoded 
Key - value np.:
amount - 10000
installments - 18
interest_rate - 5

Rejestracja użytkownika:
POST http://127.0.0.1:8000/api/register
Body: raw
Wymagane podanie maila oraz hasła w postaci json
np. 
{
    "email": "xxxxxx@xxxx.com",
    "password": "XXXXXXX"
}

Request do pobrania tokenu:  
POST http://127.0.0.1:8000/api/login_check
Body: raw
Wymagane podanie nazwy użytkownika(email) oraz hasła w postaci json
np. 
{
    "username": "xxxxxx@xxxx.com",
    "password": "XXXXXXX"
}

Wylistowanie 4 kalkulacji uporządkowane wg sumarycznej kwoty odsetek malejąco (wszystkie)  - metoda dostępna tylko dla zarejestrowanych użytkowników
GET http://127.0.0.1:8000/api/listCalculations
Wymagany token z poprzedniego requestu
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciO...

Wylistowanie 4 kalkulacji uporządkowane wg sumarycznej kwoty odsetek malejąco (tylko niewykluczone)  - metoda dostępna tylko dla zarejestrowanych użytkowników
GET http://127.0.0.1:8000/api/listCalculations?filter=not_excluded
Wymagany token z poprzedniego requestu
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciO...

Metoda pozwalająca na wykluczenie pojedynczej kalkulacji:
PATCH http://127.0.0.1:8000/api/excludeCalculation/{id kalkulacji}
Wymagany token z poprzedniego requestu
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciO...



