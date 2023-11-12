# Ecotone - Warsztat PHPCon 2023

W tym repozytorium znajdziesz wszystkie materiały do warsztatu Ecotone.

# Cel Warsztatu

Poznanie podstawowych mechanizmów Ecotone, które pozwolą na zbudowanie aplikacji zgodnie z zasadami Messaging'u.  
Przyzwyczajenie do pracy z dokumentacją, aby po warsztacie móc samodzielnie korzystać z Ecotone.  
Warszat jest podzielony na 3 części:

1. [1-resilient-messaging](./1-resilient-messaging) - Odporny Messaging jako podstawa architektury
2. [2-building-blocks](./2-building-blocks) - Używanie Building Blocks do skupienia się na logice biznesowej
3. [3-testing](./3-testing) - Testowanie Building Blocks i komunikacji asynchronicznej

# Wymagania

W celu uruchomienia warsztatu potrzebny jest tylko Docker [Docker](https://docs.docker.com/engine/install/) oraz [Docker-Compose](https://docs.docker.com/compose/install/).

W przypadku braku PHPStorm'a, można użyć darmowego [Visual Studio Code](https://code.visualstudio.com/) do edycji kodu z pluginem do [PHP](https://marketplace.visualstudio.com/items?itemName=DEVSENSE.phptools-vscode).

# Instalacja

0. Otwórz zadanie warsztatowe w swoim IDE ([1-resilient-messaging](./1-resilient-messaging), [2-building-blocks](./2-building-blocks), [3-testing](./3-testing)), 
    tak abyś miał TYLKO kod dla zadania nad którym pracujesz (W innym razie IDE będzie podpowiadało klasy z kolejnych ćwieczeń).
1. W katalogu warsztatu, uruchom komendę `docker-compose pull && docker-compose up` aby uruchomić aplikację.
2. Kiedy kontener z aplikacją wystartuje, zainstaluje wszystkie zależności. Możesz to sprawdzić komendą `docker logs -f ecotone_demo`
3. Jesteśmy gotowi, możesz zacząć wykonywać zadanie.
4. Po zakończeniu warsztatu, możesz wyczyścić środowisko komendą `docker-compose down`