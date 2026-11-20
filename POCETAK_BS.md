# BookNest — kratke upute za pokretanje

## 1. Raspakuj projekat

Raspakuj ZIP u:

`C:\xampp\htdocs\Kanita Plasto\`

Trebaš dobiti folder:

`C:\xampp\htdocs\Kanita Plasto\BookNest-Library-Management-and-Book-Reservation-System\`

Ako Windows pita da li želiš zamijeniti postojeće fajlove, izaberi zamjenu. Nemoj brisati `.git` folder ako želiš sačuvati postojeću Git historiju.

## 2. Pokreni XAMPP

U XAMPP Control Panelu pokreni **Apache** i **MySQL**.

## 3. Provjeri konfiguraciju

U `backend/config/config.php` su postavljene standardne lokalne XAMPP vrijednosti:

- host: `127.0.0.1`
- database: `booknest_db`
- username: `root`
- password: prazno

Ako tvoj MySQL koristi drugu lozinku, promijeni je u ovom fajlu.

## 4. Baza podataka

Pošto već imaš `booknest_db` i ranije smo je testirali, **nemoj ponovo uvoziti SQL fajl samo radi zamjene projekta**. Postojeću bazu ostavi kako jeste.

Ako postavljaš potpuno novu instalaciju, uvezi `backend/database/booknest.sql` kroz phpMyAdmin. Pažnja: SQL skripta briše i ponovo kreira šest BookNest tabela, zato je nemoj pokretati nad bazom koju želiš sačuvati bez prethodnog backupa.

## 5. Otvori BookNest

Frontend:

`http://localhost/Kanita%20Plasto/BookNest-Library-Management-and-Book-Reservation-System/frontend/`

API:

`http://localhost/Kanita%20Plasto/BookNest-Library-Management-and-Book-Reservation-System/backend/api`

Swagger:

`http://localhost/Kanita%20Plasto/BookNest-Library-Management-and-Book-Reservation-System/backend/docs/swagger.html`

## 6. Discover / External API

Discover radi bez dodatnog ključa koristeći Open Library fallback. Ako kasnije dodaš Google Books API key u `backend/config/config.php`, BookNest će prvo pokušati Google Books, a Open Library će ostati rezervna opcija.

ZIP uključuje `backend/vendor/`, pa Composer instalacija nije potrebna ako `backend/vendor/autoload.php` postoji. Ako folder `vendor` nedostaje, otvori terminal u `backend/` i pokreni `composer install`.
