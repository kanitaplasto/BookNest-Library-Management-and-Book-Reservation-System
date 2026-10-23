# Predaja Milestone 1

1. Raspakuj ZIP. U postojeći lokalni GitHub projekt kopiraj **sadržaj** foldera BookNest, tako da README.md, frontend i backend budu direktno u glavnom folderu repozitorija. Nemoj brisati postojeći `.git` folder.
2. Pokreni frontend prema README.md i pregledaj sve stranice. Provjeri odgovara li ti sadržaj i razumiješ li demonstracijske funkcije.
3. U Git Bash terminalu svog repozitorija pokreni:

```bash
git status
git add .
git commit -m "Complete milestone 1: BookNest SPA and database planning"
git push
```

Ako remote ili grana nisu podešeni, prvo provjeri `git remote -v` i `git branch`; naredba za prvo povezivanje zavisi od tvog postojećeg repozitorija.

4. Na GitHubu provjeri frontend, backend, docs, README i .gitignore. `.gitkeep` čuva prazne direktorije. Stvarni logovi i tajne ne trebaju biti na GitHubu.
5. Na LMS formu https://forms.gle/xNZECW5ZG1BngfaA6 pošalji naziv teme i link vlastitog repozitorija.

## Šta pokazati na demonstraciji

- SPA navigaciju bez osvježavanja stranice.
- Katalog, pretragu, filtere i detalje knjige.
- Oglednu rezervaciju i otkazivanje.
- Registraciju/prijavu, profil, posudbe i ekran bibliotekara.
- Discover stranicu kao planirano mjesto za eksterni API.
- Responsive prikaz na mobitelu.
- ERD sa osam entiteta i objašnjenje veze knjiga–primjerak–posudba.

Ovo je statični prototip. Backend i prava autentifikacija nisu dio ove predaje. Ogledne promjene nestaju pri osvježavanju.
