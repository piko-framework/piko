

# \piko\Component

Component class implements events and behaviors features.
Also component public properties can be initialized with an array of configuration during instantiation.

Events offer the possibility to inject custom code when they are triggered.
Behaviors offer the possibility to add custom methods without extending the class.







## Properties

| Name | Description |
|------|-------------|
| public [`$behaviors`](#property_behaviors) | Behaviors container.  |
| public [`$events`](#property_events) | Event handlers container.  |
| public [`$events2`](#property_events2) | Static event handlers container.  |


## Methods

| Name | Description |
|------|-------------|
| public [`__call`](#method___call) | Magic method to call a behavior.  |
| public [`__construct`](#method___construct) | Constructor  |
| public [`attachBehavior`](#method_attachBehavior) | Attach a behavior to the component instance.  |
| public [`detachBehavior`](#method_detachBehavior) | Detach a behavior.  |
| public [`on`](#method_on) | Event registration.  |
| public [`trigger`](#method_trigger) | Trigger an event. Event handlers corresponding to ... |
| public [`when`](#method_when) | Static event registration.  |
| protected [`init`](#method_init) | Method called at the end of the constructor.  |


-----


## Properties


<a name="property_behaviors"></a>
### public $behaviors : callable[]
Behaviors container.






<a name="property_events"></a>
### public $events : callable[]
Event handlers container.






<a name="property_events2"></a>
### public $events2 : callable[]
Static event handlers container.





-----

## Methods




<a name="method___call"></a>
### public __call(): mixed

```php
public  __call(string  $name, array  $args): mixed
```

Magic method to call a behavior.



#### Parameters
**$name** :
The name of the behavior.

**$args** :
The behavior arguments.




**throws**  \RuntimeException



#### Return:
**mixed**


-----



<a name="method___construct"></a>
### public __construct(): void

```php
public  __construct(array  $config = []): void
```

Constructor



#### Parameters
**$config**  (default: []):
A configuration array to set public properties of the class.






-----



<a name="method_attachBehavior"></a>
### public attachBehavior(): void

```php
public  attachBehavior(string  $name, callable  $callback): void
```

Attach a behavior to the component instance.



#### Parameters
**$name** :
The behavior name.

**$callback** :
The behavior implementation. Must be  one of the following:
- A Closure (function(){ ... })
- An object method ([$object, 'methodName'])
- A static class method ('MyClass::myMethod')
- A global function ('myFunction')






-----



<a name="method_detachBehavior"></a>
### public detachBehavior(): void

```php
public  detachBehavior(string  $name): void
```

Detach a behavior.



#### Parameters
**$name** :
The behavior name.






-----



<a name="method_on"></a>
### public on(): void

```php
public  on(string  $eventName, mixed  $callback, string  $priority = 'after'): void
```

Event registration.



#### Parameters
**$eventName** :
The event name to register.

**$callback** :
The event handler to register. Must be  one of the following:
- A Closure (function(){ ... })
- An object method ([$object, 'methodName'])
- A static class method ('MyClass::myMethod')
- A global function ('myFunction')

**$priority**  (default: 'after'):
The order priority in the events stack ('after' or 'before'). Default to 'after'.






-----



<a name="method_trigger"></a>
### public trigger(): array

```php
public  trigger(string  $eventName, array  $args = []): array
```

Trigger an event.
Event handlers corresponding to this event will be called in the order they are registered.


#### Parameters
**$eventName** :
The event name to trigger.

**$args**  (default: []):
The event handlers arguments.






#### Return:
**array**


-----



<a name="method_when"></a>
### public when(): void

```php
public static  when(string  $eventName, mixed  $callback, string  $priority = 'after'): void
```

Static event registration.



#### Parameters
**$eventName** :
The event name to register.

**$callback** :
The event handler to register. Must be  one of the following:
- A Closure (function(){ ... })
- An object method ([$object, 'methodName'])
- A static class method ('MyClass::myMethod')
- A global function ('myFunction')

**$priority**  (default: 'after'):
The order priority in the events stack ('after' or 'before'). Default to 'after'.






-----



<a name="method_init"></a>
### protected init(): void

```php
protected  init(): void
```

Method called at the end of the constructor.








