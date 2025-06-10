# Test Documentation

## Autentimissüsteemi testid (Issue #1)

### Loodud testid Issue #1 jaoks:

1. **Login formi olemasolu test**
   - Kontrollib, et login vorm on nähtav
   - Verifitseerib username ja password väljad
   - Kontrollib submit nupu olemasolu

2. **Vale sisselogimise test** 
   - Testib vale kasutajanime ja parooliga sisselogimist
   - Kontrollib, et kuvatakse veateade
   - Verifitseerib, et kasutaja jääb login lehele

3. **Õige sisselogimise test**
   - Testib õigete andmetega sisselogimist (admin/admin)
   - Kontrollib ümbersuunamist dashboard'ile
   - Verifitseerib, et dashboard sisu on nähtav

4. **Sessiooni säilimise test**
   - Kontrollib, et kasutaja jääb sisselogituks lehe värskendamisel
   - Testib sessiooni funktsionaalsust

5. **Ligipääsu kontroll test**
   - Kontrollib, et kaitstud lehtedele ei pääse ligi ilma sisselogimata
   - Verifitseerib ümbersuunamist login lehele
   - Testib, et monitor vorm on nähtav ainult sisselogitud kasutajatele

6. **Väljalogimine test**
   - Testib väljalogimine funktsionaalsust
   - Kontrollib ümbersuunamist login lehele

7. **Pärast väljalogimiset ligipääsu keelamine**
   - Verifitseerib, et pärast väljalogimiset ei saa kaitstud lehtedele ligi

8. **Vormide valideerimise test**
   - Testib tühjade väljadega esitamist
   - Kontrollib valideerimist

## Monitooringu konfiguratsiooni testid (Issue #2)

### Loodud testid Issue #2 jaoks:

1. **Monitor formi olemasolu test**
   - Kontrollib, et monitor vorm on nähtav sisselogitud kasutajale
   - Verifitseerib URL ja email väljad
   - Kontrollib submit nupu olemasolu

2. **URL valideerimise testid**
   - Testib vigaseid URL formaate
   - Kontrollib puuduvaid protokolle
   - Verifitseerib valideerimise sõnumeid

3. **Email valideerimise testid**
   - Testib vigaseid email formaate
   - Kontrollib puuduvaid @ märke
   - Verifitseerib valideerimise sõnumeid

4. **Nõutavate väljade test**
   - Testib tühje URL ja email välju
   - Kontrollib, et mõlemad väljad on nõutavad

5. **Edukas monitor sissekanne**
   - Testib õigete andmetega sisestamist
   - Kontrollib eduteadete kuvamist

6. **Duplikaatide ennetamise test**
   - Testib sama URL ja email kombinatsiooni korduvat lisamist
   - Kontrollib duplikaadi vea sõnumit

7. **Olemasolevate sissekannet kuvamise test**
   - Kontrollib, et lisatud monitorid kuvatakse
   - Testib andmete püsivust lehe värskendamisel

8. **Erinevate URL formaatide test**
   - Testib erinevaid kehtivaid URL formaate
   - Kontrollib http/https, portide, alamdomeenide toetust

9. **Erinevate email formaatide test**
   - Testib erinevaid kehtivaid email formaate
   - Kontrollib täppide, plussmärkide, alamdomeenide toetust

## Testide käivitamine:

```bash
# Kõik testid
npm test

# Testid koos brauseri aknaga
npm run test:headed

# Testid UI-ga
npm run test:ui

# Ainult autentimise testid
npx playwright test auth.spec.js

# Ainult monitooringu testid  
npx playwright test monitor-config.spec.js
```

## Eeldused testide käivitamiseks:

- PHP server peab töötama localhost:8000 pordil
- Rakenduse failid peavad olema projekt kaustas
- Vaikimisi kasutajanimi/parool: admin/admin
- Dashboard.php peab sisaldama monitooringu vormi
