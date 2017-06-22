**Inštalácia**

  * nainšatalovať deb balík `sudo apt-get install npm`
  * nainštalovať node balíčky `npm install` zoznam balíškov je v `package.json`
  po tomto kroku by mali byť všetky potrebené balíčky nainštalované. 
  
**Kompilácia /product environment/**

Uistite sa, že v `./src/components/app.tsx` je spravene nastavený store         
teda: `const store = createStore(app);`,
gitujeme celé `src` a `dist/bundle.min.js` čo je výsledný js ktorý sa includuje.


**Kompilácia /dev environment/**
                                                                         
Nasaďte v `./src/components/app.tsx` middleware `const store = createStore(app, applyMiddleware(logger));`
následne spustite `webpack --watch --color`, zabezpečí to auto preklad pri zmene súboru a farbičky :)
                                                         