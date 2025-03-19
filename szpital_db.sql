BEGIN;

CREATE TABLE typ_uzytkownika(
    id_typu_uzytkownika char(2) primary key,
    opis_typu_uzytkownika varchar(20) not null
);

CREATE TABLE uzytkownik(
    id_uzytkownika int primary key auto_increment,
    id_typu_uzytkownika char(2) not null,
    nazwa_uzytkownika varchar(20) not null,
    haslo varchar(256) not null,
    czy_uzupelniono_dane boolean not null default 0,
    foreign key (id_typu_uzytkownika) references typ_uzytkownika(id_typu_uzytkownika)
);
ALTER TABLE uzytkownik auto_increment=101;

CREATE TABLE schorzenie(
    id_schorzenia char(5) primary key,
    opis_schorzenia varchar(512) not null
);

CREATE TABLE oddzial(
    id_oddzialu char(5) primary key,
    opis_oddzialu varchar(128) not null
);

CREATE TABLE specjalnosc_lekarza(
    specjalnosc varchar(30) primary key
);

CREATE TABLE lekarz(
    id_lekarza int primary key,
    specjalnosc varchar(128) not null,
    id_typu_uzytkownika char(2) not null,
    imie varchar(128) not null,
    nazwisko varchar(128) not null,
    plec varchar(128) not null,
    pesel varchar(128) not null,
    data_urodzenia varchar(128) not null,
    miejsce_urodzenia varchar(128) not null,
    npwz varchar(128) not null,
    email varchar(128) not null,
    telefon varchar(128) not null,
    ulica varchar(128) not null,
    nr_domu varchar(128) not null,
    nr_mieszkania varchar(128),
    miasto varchar(128) not null,
    kod_pocztowy varchar(128) not null,
    wojewodztwo varchar(128) not null,
    data_ostatniej_zmiany_danych varchar(128),
    data_ostatniej_zmiany_hasla varchar(128),
    foreign key (id_lekarza) references uzytkownik(id_uzytkownika),
    foreign key (id_typu_uzytkownika) references typ_uzytkownika(id_typu_uzytkownika)
);

CREATE TABLE rejestrator(
    id_rejestratora int primary key,
    id_typu_uzytkownika char(2) not null,
    imie varchar(128) not null,
    nazwisko varchar(128) not null,
    plec varchar(128) not null,
    pesel varchar(128) not null,
    data_urodzenia varchar(128) not null,
    miejsce_urodzenia varchar(128) not null,
    email varchar(128) not null,
    telefon varchar(128) not null,
    ulica varchar(128) not null,
    nr_domu varchar(128) not null,
    nr_mieszkania varchar(128),
    miasto varchar(128) not null,
    kod_pocztowy varchar(128) not null,
    wojewodztwo varchar(128) not null,
    data_ostatniej_zmiany_danych varchar(128),
    data_ostatniej_zmiany_hasla varchar(128),
    foreign key (id_rejestratora) references uzytkownik(id_uzytkownika),
    foreign key (id_typu_uzytkownika) references typ_uzytkownika(id_typu_uzytkownika)
);

CREATE TABLE pacjent(
    id_pacjenta int primary key auto_increment,
    id_lekarza int,
    id_pobytu int,
    imie varchar(128) not null,
    nazwisko varchar(128) not null,
    plec varchar(128) not null,
    pesel varchar(128) not null,
    obywatelstwo varchar(128) not null,
    data_urodzenia varchar(128) not null,
    miejsce_urodzenia varchar(128) not null,
    email varchar(128) not null,
    telefon varchar(128) not null,
    ulica varchar(128) not null,
    nr_domu varchar(128) not null,
    nr_mieszkania varchar(128),
    miasto varchar(128) not null,
    kod_pocztowy varchar(128) not null,
    wojewodztwo varchar(128) not null,
    data_dodania_do_bazy datetime not null,
    historia_pacjenta LONGTEXT,
    data_rozpoczecia_ostatniego_pobytu varchar(128),
    data_zakonczenia_ostatniego_pobytu varchar(128),
    data_ostatniej_zmiany_danych varchar(128),
    foreign key (id_lekarza) references lekarz(id_lekarza)
);
ALTER TABLE pacjent auto_increment=1001;

create table pobyt(
    id_pobytu int primary key auto_increment,
    id_pacjenta int not null,
    id_lekarza int not null,
    id_oddzialu char(5) not null,
    id_schorzenia char(5) not null,
    data_rozpoczecia_pobytu varchar(128) not null,
    historia_pobytu LONGTEXT,
    data_ostatniej_zmiany_danych_pobytu varchar(128),
    data_zakonczenia_pobytu varchar(128),
    czy_wygenerowano_akt_wypisu boolean not null default 0,
    podsumowanie_pobytu LONGTEXT,
    foreign key (id_pacjenta) references pacjent(id_pacjenta),
    foreign key (id_lekarza) references lekarz(id_lekarza),
    foreign key (id_oddzialu) references oddzial(id_oddzialu),
    foreign key (id_schorzenia) references schorzenie(id_schorzenia)
);
ALTER TABLE pobyt auto_increment=10001;


CREATE TABLE udane_zalogowanie(
    id_logowania int primary key auto_increment,
    id_uzytkownika int not null,
    data_logowania varchar(128) not null,
    foreign key (id_uzytkownika) references uzytkownik(id_uzytkownika)
);

ALTER TABLE pacjent
ADD FOREIGN KEY (id_pobytu) REFERENCES pobyt(id_pobytu);

INSERT INTO typ_uzytkownika VALUES ('LK', 'Lekarz'), ('RS', 'Rejestrator');

INSERT INTO schorzenie VALUES ("ICD-A", 'Wybrane choroby zakaźne i pasożytnicze'), ('ICD-B', "Niektóre choroby zakaźne i pasożytnicze"), ('ICD-C', 'Nowotwory');
INSERT INTO schorzenie VALUES ('ICD-D', 'Nowotwory in situ'), ('ICD-E', 'Zaburzenia wydzielania wewnętrznego, stanu odżywienia i przemiany metabolicznej'), ('ICD-F', 'Zaburzenia psychiczne i zaburzenia zachowania'), ('ICD-G', 'Choroby układu nerwowego'), ('ICD-H', 'Choroby oka i przydatków oka, ucha i wyrostka sutkowatego'), ('ICD-I', 'Choroby układu krążenia'), ('ICD-J', 'Choroby układu oddechowego'), ('ICD-K', 'Choroby układu pokarmowego'), ('ICD-L', 'Choroby skóry i tkanki podskórnej'), ('ICD-M', 'Choroby układu mięśniowo-szkieletowego i tkanki łącznej'), ('ICD-N', 'Choroby układu moczowo-płciowego'), ('ICD-O', 'Ciąża, poród i połóg'), ('ICD-P', 'Wybrane stany rozpoczynające się w okresie okołoporodowym'), ('ICD-Q', 'Wady rozwojowe wrodzone, zniekształcenia i aberracje chromosomowe'), ('ICD-R', 'Objawy, cechy chorobowe oraz nieprawidłowe wyniki badań klinicznych i laboratoryjnych niesklasyfikowane gdzie indziej'), ('ICD-S', 'Urazy, zatrucia i inne określone skutki działania czynników zewnętrznych'), ('ICD-T', 'Urazy obejmujące liczne okolice ciała'), ('ICD-U', 'Kody do celów specjalnych (czynniki oporne na leki i antybiotyki, SARS, historia przebiegu COVID-19 i zdrowie pacjenta po zakończeniu COVID-19)'), ('ICD-V', 'Zewnętrzne przyczyny zachowania i zgonu (wypadki komunikacyjne w transporcie)'), ('ICD-W', 'Zewnętrzne przyczyny zachowania i zgonu (upadki, kontakt z przedmiotami, wybuchy, ciała obce, ugryzienia, zanurzenia, tonięcie, narażenie na promieniowanie, prąd lub ciepło/zimno)'), ('ICD-X', 'Zewnętrzne przyczyny zachowania i zgonu (pożar, kontakt z gorącymi substancjami, kontakt z jadowitymi zwierzętami, ofiara katastrofy naturalnej, przypadkowe zatrucia, niedobory, samookaleczenia, ofiara napaści)'), ('ICD-Y', 'Zewnętrzne przyczyny zachowania i zgonu (zatrucia lekami, alkoholem o nieokreślonym zamiarze, incydenty związane z szprzętem lekarskim, działania wojenne)'), ('ICD-Z', 'Czynniki wpływające na stan zdrowia i kontakt ze służbą zdrowia (szczepienia, badania kontrolne, rekonwalenscencja, bezobjawowy stan zakażenia HIV, badania przesiewowe)');

INSERT INTO oddzial VALUES ('O-AIT', 'Oddział Anestezjologii i Intensywnej Terapii'), ('O-CHR', 'Oddział Chirurgii'), ('O-GIP', 'Oddział Ginekologiczno-Położniczy'), ('O-LAR', 'Oddział Laryngologiczny'), ('O-NEU', 'Oddział Neurologiczny'), ('O-ONK', 'Oddział Onkologii'), ('O-KAR', 'Oddział Kardiologiczny'), ('O-OKU', 'Oddział Okulistyczny'), ('O-PED','Oddział Pediatryczny'), ('O-SOR', 'Szpitalny Oddział Ratunkowy'), ('O-CHW', 'Oddział Chorób Wewnętrznych'), ('O-CHP', 'Oddział Chorób Płuc'); 

INSERT INTO specjalnosc_lekarza VALUES ('Anestozjolog'), ('Chirurg'), ('Ginekolog'), ('Laryngolog'), ('Neuorolog'), ('Okulista'), ('Onkolog'), ('Kardiolog'), ('Pediatra'), ('Hematolog'), ('Urolog'), ('Pulmonolog'), ('Endokrynolog');

INSERT INTO uzytkownik (id_typu_uzytkownika, nazwa_uzytkownika, haslo) VALUES ("LK", "lek_login", "$2y$10$Pqc36l4fIGoIcf3.XOa2LO2vfdg8WG1RlPjKjXHlkddrpAJR6uvou");
INSERT INTO uzytkownik (id_typu_uzytkownika, nazwa_uzytkownika, haslo) VALUES ("RS", "rej_login", "$2y$10$ByKTI4N6FlDTUCC9PgWl3u.TIyLwcwt.e0DJXVMsiTcQSclUajBw.");

COMMIT;