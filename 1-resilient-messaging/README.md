# Resilient Messaging

W tej części warsztatów zapoznamy się z mechanizmami, które zapewniają monitoring, odporność i odzyskiwalność.

# Opis zadania

W tym ćwiczeniu mamy za zadanie zapewnić stabilną funkcjonalność składania zamówienia.
Składa się ono z dwóch kroków:

- Zapisanie zamówienia `Order` w bazie danych
- Wywołanie zewnętrznego serwisu `ShippingService` w celu wysłania zamówienia do klienta

Jako, że `ShippingService` to zewnętrzny serwis, nie możemy polegać na jego dostępności.  
Związku z tym chcemy wyizolować zapis zamówienia od wywołania `ShippingService`.       
W tym celu wydzielimy składanie zamówienia od jego wysyłki, aby akcje były przetwarzane niezależnie.  

# Kroki

Wywołaj komendę z konsoli: `docker exec -it ecotone_demo php run_example.php`.  
Zadanie zostanie zakończone sukcesem gdy nie wystąpi żaden błąd podczas wywołania powyższej komendy.  

1. Zmień `OrderService` tak, aby publikował `Event` `OrderWasPlaced` (Event należy stworzyć).
2. Dodaj `EventHandler` który będzie nasłuchiwał na `OrderWasPlaced`, a następnie wywoła `ShippingService` (Możesz go utworzyć w klasie `src/Application/OrderService.php`).
3. Wywołaj komendę z konsoli. Jak zauważysz Event Handler jest wywołany synchronicznie, czyli nie zapewniliśmy izolacji błędów.  
4. Dodaj kanał asynchroniczny o nazwie `orders`, który będzie wysyłał wiadomości do RabbitMQ: `\Ecotone\Amqp\AmqpBackedMessageChannelBuilder::create("orders")` (Możesz go utworzyć w klasie `src/Infrastructure/MessageChannelConfiguration.php`).
5. Użyj tego kanału do asynchronicznego przetwarzania `EventHandlera` (`OrderWasPlaced`) (pozostaw Command Handler synchroniczny).

### Podpowiedzi

1, 2. [Event Handling and Publishing](https://docs.ecotone.tech/modelling/command-handling/external-command-handlers/event-handling#handling-events)
3, 4. [Asynchronous Message Processing](https://docs.ecotone.tech/modelling/asynchronous-handling#running-asynchronously)