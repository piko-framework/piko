

# \piko\Module

Module is the base class for classes containing module logic.








## Properties

| Name | Description |
|------|-------------|
| public [`$controllerMap`](#property_controllerMap) | Mapping from controller ID to controller class.  |
| public [`$controllerNamespace`](#property_controllerNamespace) | Base name space of module&#039;s controllers. Default t... |
| public [`$id`](#property_id) | The module identifier.  |
| public [`$layout`](#property_layout) | The name of the module&#039;s layout file.  |
| public [`$layoutPath`](#property_layoutPath) | The layout directory of the module.  |
| private [`$basePath`](#property_basePath) | The root directory of the module.  |

## Inherited Properties

| Name | Description |
|------|-------------|
| public [`$behaviors`](Component.md#property_behaviors) | Behaviors container.  |
| public [`$events`](Component.md#property_events) | Event handlers container.  |
| public [`$events2`](Component.md#property_events2) | Static event handlers container.  |

## Methods

| Name | Description |
|------|-------------|
| public [`getBasePath`](#method_getBasePath) | Returns the root directory of the module.  |
| public [`run`](#method_run) | Run module controller action.  |
| protected [`init`](#method_init) | Method called at the end of the constructor.  |

## Inherited Methods

| Name | Description |
|------|-------------|
| public [`__call`](Component.md#method___call) | Magic method to call a behavior.  |
| public [`__construct`](Component.md#method___construct) | Constructor  |
| public [`attachBehavior`](Component.md#method_attachBehavior) | Attach a behavior to the component instance.  |
| public [`detachBehavior`](Component.md#method_detachBehavior) | Detach a behavior.  |
| public [`on`](Component.md#method_on) | Event registration.  |
| public [`trigger`](Component.md#method_trigger) | Trigger an event. Event handlers corresponding to ... |
| public [`when`](Component.md#method_when) | Static event registration.  |
| protected [`init`](Component.md#method_init) | Method called at the end of the constructor.  |

-----


## Properties


<a name="property_controllerMap"></a>
### public $controllerMap : array
Mapping from controller ID to controller class.






<a name="property_controllerNamespace"></a>
### public $controllerNamespace : string
Base name space of module's controllers.
Default to \{baseModuleNameSpace}\\controllers





<a name="property_id"></a>
### public $id : string
The module identifier.






<a name="property_layout"></a>
### public $layout : string
The name of the module's layout file.






<a name="property_layoutPath"></a>
### public $layoutPath : string
The layout directory of the module.






<a name="property_basePath"></a>
### private $basePath : string
The root directory of the module.





-----

## Methods




<a name="method_getBasePath"></a>
### public getBasePath(): string

```php
public  getBasePath(): string
```

Returns the root directory of the module.








#### Return:
**string**
the root directory of the module.

-----



<a name="method_run"></a>
### public run(): mixed

```php
public  run(string  $controllerId, string  $actionId): mixed
```

Run module controller action.



#### Parameters
**$controllerId** :
The controller identifier.

**$actionId** :
The controller action identifier.






#### Return:
**mixed**
The module output.

-----



<a name="method_init"></a>
### protected init(): void

```php
protected  init(): void
```

Method called at the end of the constructor.






**see**  \piko\Component::init()



