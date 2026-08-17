ART LIFE DESIGN — ADMIN STARTER
================================

ETAPA 1
-------
- bază de date
- login admin
- sesiuni PHP
- protecție CSRF
- logout
- dashboard
- script CLI pentru crearea adminului
- tabele pregătite pentru proiecte / imagini / conținut

Nu există pagină publică de creare a unui cont admin.

1. XAMPP
--------
Pornește:
- Apache
- MySQL

2. COPIAZĂ PROIECTUL
--------------------
Copiază folderul "artlife-admin-starter" în:

C:\xampp\htdocs\

Poți să-l redenumești:
artlife

3. CREEAZĂ BAZA DE DATE
-----------------------
Intră în:
http://localhost/phpmyadmin/

Apoi:
- Import
- alege database/schema.sql
- Go / Import

4. CONFIG DATABASE
------------------
Pentru XAMPP, config/database.php este deja pregătit:

host = 127.0.0.1
database = artlife
user = root
password = gol

5. CREEAZĂ PRIMUL ADMIN
-----------------------
Deschide CMD sau PowerShell.

cd C:\xampp\htdocs\artlife

Apoi:

C:\xampp\php\php.exe scripts\create_admin.php admin@artlifedesign.md "ParolaTaFoartePuternica" "Administrator"

Parola trebuie să aibă minimum 12 caractere.

IMPORTANT:
scripts/create_admin.php rulează doar în terminal.
Din browser refuză accesul.

6. LOGIN ADMIN
--------------
http://localhost/artlife/admin/

sau:

http://localhost/artlife/admin/login.php

7. CE URMEAZĂ
-------------
În etapa următoare conectăm:
- CRUD proiecte
- upload 1–4 imagini
- ultimele proiecte automat primele
- ultimele 4 automat pe homepage
- editare homepage
- editare pagina Lucrări

8. PE IPHOST
------------
Pe IPHOST:
- creezi baza MySQL din cPanel
- creezi userul MySQL
- imporți schema.sql
- schimbi config/database.php

Pe server nu mai ai nevoie de XAMPP.
