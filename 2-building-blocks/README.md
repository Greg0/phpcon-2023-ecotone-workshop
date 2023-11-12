# Building Blocks

W tej części warsztatów zapoznamy się z wysoko poziomowym API - Building Blocks.

# Opis Zadania

W tym ćwiczeniu zapoznamy się z wzorcem Aggregate, który pozwoli nam bardziej skupić się na logice biznesowej.
Połączymy nasz `Order` Aggregate z Messagingiem i udostępnimy API do jego obsługi.  
 
W tym scenariuszu złożymy zamówienie i je anulujemy, co spowoduje przeliczenie stanu magazynowego.    
Jeżeli uruchomimy `run_example.php` to zakończy się ono sukcesem, jednakże obecna implementacja zawiera dużo kodu, który nie jest związany z logiką biznesową.  
Naszym celem będzie usunięcie kodu orkiestrującego i pozostawienie tylko kodu biznesowego, przy zapewnieniu że API nie ulegnie zmianie.   

* Repozytoria dla Aggregate'u są już skonfigurowane, więc możemy skupić się na komendach i eventach :)
* Aby zobaczyć Flow z wysokiego poziomu, możesz dalej korzystać z Tracing'u dostępnego pod http://localhost:4004/search

# Kroki

Uruchom komendę z konsoli: `docker exec -it ecotone_demo php run_example.php`.
Po każdym kroku uruchom komendę z konsoli, aby sprawdzić czy Twoje rozwiązanie jest nadal poprawne.  

1. Usuń `cancelOrder` z klasy `OrderService`, a następnie:
   a) Ustaw Command Handler w klasie `Order` dla komendy `CancelOrder`.
   b) Opublikuj Event `OrderWasCancelled` z `Order` po przez `Event Bus` (Możesz wstrzyknąć go bezpośrednio jako drugi parameter po komendzie).
2. Usuń referencję do `EventBus` z `CancelOrder` Command Handler i użyj bezpośredniej publikacji Eventów z Aggregatu, korzystając z `recordThat(new OrderWasCancelled)`.
3. Usuń klasę `OrderService` i ustaw Factory Method dla `PlaceOrder` w klasie `Order`. Następnie opublikuj Event bezpośrednio z Aggregate'u (w constructorze).
4. Usuń klasę `CancelOrder` i użyj Routingu w Command Handlerze o nazwie `order.cancel`.

## Dodatkowe zadanie
Jeżeli skończyłeś pierwsze zadania i masz jeszcze czas, możesz spróbować rozwiązać zadanie dodatkowe :)

`ProductStockSubscriber` nasłuchuje na Event `OrderWasPlaced` oraz `OrderWasCancelled` a następnie aktualizuje stan magazynowy.    
Jednak kod znajdujący się w `ProductStockSubscriber` to kod orkiestrujący nie domenowy i nie musimy go utrzymywać.  
W tym celu będzie nasłuchiwać na Eventy `OrderWasPlaced` oraz `OrderWasCancelled` w Aggregacie `Order` i tam zaktualizujemy stan magazynowy.  

5. Dodaj Event Handler w Aggregacie `ProductStock` dla Eventów `OrderWasPlaced` metoda `decreaseStock`, oraz `OrderWasCancelled` metoda `increaseStock`.    
6. Nadaj nazwę dla Event'ów `OrderWasPlaced` - `order.placed` oraz `OrderWasCancelled` - `order.cancelled`, korzystając z atrybutu `#[NamedEvent('order.cancelled')]`
7. Nasłuchaj na Eventy `order.placed` oraz `order.cancelled` bez wykorzystania klas, przy wykorzystaniu `listenTo` - `#[EventHandler(listenTo: "order.cancelled")]`

### Podpowiedzi

1. [Aggregate Action Method](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-command-handlers#aggregate-action-method)
2. [Recording events in Aggregate](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-event-handlers#publishing-events-from-aggregate)
3. [Aggregate Factory Method](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-command-handlers#aggregate-factory-method)
4. [Aggregate Command Handler Routing](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-command-handlers#calling-aggregate-without-command-class)
5. [Subscribing to Events from Aggregate](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-event-handlers)
6, 7. [Publishing Named Events from Aggregate](https://docs.ecotone.tech/modelling/command-handling/state-stored-aggregate/aggregate-event-handlers#sending-named-events)