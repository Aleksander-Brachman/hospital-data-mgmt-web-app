# hospital-data-mgmt-web-app

Niniejsza aplikacja webowa została utworzona w ramach pracy inżynierskiej _System zabezpieczeń teleinformatycznych dla placówki szpitalnej – oprogramowanie systemowe i aplikacja zarządzająca danymi_ mojego autorstwa. Głównym celem pracy było utworzenie bezpiecznego środowiska serwerowego oraz napisanie  aplikacji webowej, która pozwala na bezpieczne zarządzanie danymi pacjentów przez pracowników placówki szpitalnej. Jako zestaw oprogramowania serwera wykorzystano zestaw LAMP (Linux, Apache HTTP Server, MariaDB, PHP). Zachęcam do przeczytania pracy inżynierskiej, gdzie szczegółowo opisuję projekt bazy danych i aplikacji webowej, zastosowane rozwiązania zapewniające bezpieczeństwo poszczególnych komponentów środowiska serwerowego oraz prezentuję testy funkcjonalne aplikacji.

Możliwe jest także samodzielne przetestowanie aplikacji, poniżej przedstawiam kroki w celu poprawnego uruchomienia i korzystania z aplikacji:

**1. Wymagania wstępne:**
- System operacyjny: najlepiej Ubuntu/Debian
- Zainstalowany serwer HTML Apache2 oraz baza danych MariaDB lub MySQL
- Zainstalowane w systemie pakiety php:
```sudo apt install php libapache2-mod-php php-mysql php-mbstring```

**2. Po sklonowaniu repozytorium, skopiuj poszczególne foldery i pliki do ścieżki serwera Apache (/var/www i /var/www/html) zgodnie z drzewem poniżej:**
```
/var/www
.
├── html
│   ├── Rejestracja
│   ├── Lekarz
│   ├── logowanie.php
│   ├── logowanie-style.css
│   └── uwierzytelnienie.php
├── polacz-baza
│   ├── polacz-baza-lekarz.php
│   ├── polacz-baza-rejestracja.php
│   ├── polacz-baza-uwierzytelnienie.php
│   └── polacz-baza-zmien-haslo.php
├── tfpdf
└── zabezpieczenia
    ├── szyfruj-deszyfruj.php
    └── zabezpieczenia-formularz.php
```
**3. Konfiguracja MariaDB lub MySQL:**
- W MariaDB lub MySQL utwórz bazę danych 'szpital'.
- W celu połączenia aplikacji webowej z bazą danych, utwórz w MariaDB/MySQL czterech użytkowników o nazwach: 'lekarz', 'rejestracja', 'uwierzytelnienie' i 'zmien_haslo'. Przy tworzeniu użytkownika w MariaDB/MySQL pamiętaj o nadaniu użytkownikom tego samego hasła, które znajduje się w plikach folderu 'polacz-baza'. Np. dla użytkownika 'lekarz' hasło znajduje się w pliku ```polacz-baza-lekarz.php```. Można edytować hasło znajdujące się w plikach na mniej skomplikowane, ale jedynie w celach testowych.
- Nadaj utworzonym użytkownikom uprawnienia do korzystania z bazy danych 'szpital'. W celach testowych można przyznać wszystkie uprawnienia na bazie danych dla każdego z użytkowników. Zalecane uprawnienia dla każdego z użytkowników są wyszczególnione w rozdziale 4.3.1 załączonej pracy inżynierskiej.
- Zaimportuj plik ```szpital_db.sql``` do MariaDB/MySQL w celu utworzenia tabel w bazie danych 'szpital'.

**4. Uruchom przeglądarkę i przejdź do strony ```localhost/logowanie.php```.** Powinna wyświetlić się strona logowania do aplikacji webowej.

**5. Nazwy użytkowników i hasła potrzebne do zalogowania znajdują się w pliku ```credentials.txt```.** Można zalogować się do aplikacji webowej jako użytkownik-lekarz lub jako użytkownik-rejestrator. Jako instrukcję do aplikacji można wykorzystać rozdział 5.5 załączonej pracy inżynierskiej, gdzie zaprezentowano wszystkie funkcjonalności aplikacji.

Uwagi:
- Biblioteka "tfpdf" jest wykorzystywana w aplikacji webowej do tworzenia aktu wypisu pacjenta w formie pliku .pdf. Bibloteka "tfpdf" jest modyfikacją popularnej biblioteki języka PHP "fpdf". Biblioteka "tfpdf" dodaje wsparcie dla kodowania UTF-8, a tym samym umożliwia stosowanie w pliku .pdf polskich znaków.
