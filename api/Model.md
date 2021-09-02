

# \piko\Model

Base model class.








## Properties

| Name | Description |
|------|-------------|
| protected [`$data`](#property_data) | Represents the model&#039;s data.  |

## Inherited Properties

| Name | Description |
|------|-------------|
| public [`$behaviors`](Component.md#property_behaviors) | Behaviors container.  |
| public [`$events`](Component.md#property_events) | Event handlers container.  |
| public [`$events2`](Component.md#property_events2) | Static event handlers container.  |

## Methods

| Name | Description |
|------|-------------|
| public [`__get`](#method___get) | Magick method to access model&#039;s data as class attr... |
| public [`__isset`](#method___isset) | Magick method to check if attribute is defined in ... |
| public [`__set`](#method___set) | Magick method to set model&#039;s data as class attribu... |
| public [`__unset`](#method___unset) | Magick method to unset attribute in model&#039;s data.  |
| public [`bind`](#method_bind) | Bind directly the model data.  |
| public [`toArray`](#method_toArray) | Get the model data as an associative array.  |
| public [`validate`](#method_validate) | Validate this model (Should be extended)  |

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


<a name="property_data"></a>
### protected $data : array
Represents the model's data.





-----

## Methods




<a name="method___get"></a>
### public __get(): mixed

```php
public  __get(string  $attribute): mixed
```

Magick method to access model's data as class attribute.



#### Parameters
**$attribute** :
The attribute's name.






#### Return:
**mixed**
The attribute's value.

-----



<a name="method___isset"></a>
### public __isset(): bool

```php
public  __isset(string  $attribute): bool
```

Magick method to check if attribute is defined in model's data.



#### Parameters
**$attribute** :
The attribute's name.






#### Return:
**bool**


-----



<a name="method___set"></a>
### public __set(): void

```php
public  __set(string  $attribute, mixed  $value): void
```

Magick method to set model's data as class attribute.



#### Parameters
**$attribute** :
The attribute's name.

**$value** :
The attribute's value.






-----



<a name="method___unset"></a>
### public __unset(): void

```php
public  __unset(string  $attribute): void
```

Magick method to unset attribute in model's data.



#### Parameters
**$attribute** :
The attribute's name.






-----



<a name="method_bind"></a>
### public bind(): void

```php
public  bind(array  $data): void
```

Bind directly the model data.



#### Parameters
**$data** :
An array of data (name-value pairs).






-----



<a name="method_toArray"></a>
### public toArray(): array

```php
public  toArray(): array
```

Get the model data as an associative array.








#### Return:
**array**


-----



<a name="method_validate"></a>
### public validate(): bool

```php
public  validate(): bool
```

Validate this model (Should be extended)








#### Return:
**bool**


