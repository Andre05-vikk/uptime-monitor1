# Test Documentation

## Autentimissüsteemi testid

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

## Testide käivitamine:

```bash
# Kõik testid
npm test

# Testid koos brauseri aknaga
npm run test:headed

# Testid UI-ga
npm run test:ui
```

## Eeldused testide käivitamiseks:

- PHP server peab töötama localhost:8000 pordil
- Rakenduse failid peavad olema projekt kaustas
- Vaikimisi kasutajanimi/parool: admin/admin
