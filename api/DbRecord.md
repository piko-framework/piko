

# \piko\DbRecord

DbRecord reprensents a database table row.







## Constants

| Name | Description |
|------|-------------|
| public [`TYPE_BOOL`](#constant_TYPE_BOOL) |   |
| public [`TYPE_INT`](#constant_TYPE_INT) |   |
| public [`TYPE_STRING`](#constant_TYPE_STRING) |   |

## Properties

| Name | Description |
|------|-------------|
| protected [`$db`](#property_db) | The database instance.  |
| protected [`$primaryKey`](#property_primaryKey) | The name of the primary key. Default to &#039;id&#039;.  |
| protected [`$schema`](#property_schema) | A name-value pair that describes the structure of ... |
| protected [`$tableName`](#property_tableName) | The name of the table.  |

## Inherited Properties

| Name | Description |
|------|-------------|
| public [`$behaviors`](Component.md#property_behaviors) | Behaviors container.  |
| public [`$events`](Component.md#property_events) | Event handlers container.  |
| public [`$events2`](Component.md#property_events2) | Static event handlers container.  |
| protected [`$data`](Model.md#property_data) | Represents the model&#039;s data.  |

## Methods

| Name | Description |
|------|-------------|
| public [`__construct`](#method___construct) | Constructor  |
| public [`__get`](#method___get) | Magick method to access model&#039;s data as class attr... |
| public [`__set`](#method___set) | Magick method to set model&#039;s data as class attribu... |
| public [`delete`](#method_delete) | Delete this record.  |
| public [`load`](#method_load) | Load row data.  |
| public [`save`](#method_save) | Save this record into the table.  |
| protected [`afterDelete`](#method_afterDelete) | Method called after a delete action.  |
| protected [`afterSave`](#method_afterSave) | Method called after a save action.  |
| protected [`beforeDelete`](#method_beforeDelete) | Method called before a delete action.  |
| protected [`beforeSave`](#method_beforeSave) | Method called before a save action.  |
| protected [`checkColumn`](#method_checkColumn) | Check if column name is defined in the table schem... |

## Inherited Methods

| Name | Description |
|------|-------------|
| public [`__call`](Component.md#method___call) | Magic method to call a behavior.  |
| public [`__construct`](Component.md#method___construct) | Constructor  |
| public [`__get`](Model.md#method___get) | Magick method to access model&#039;s data as class attr... |
| public [`__isset`](Model.md#method___isset) | Magick method to check if attribute is defined in ... |
| public [`__set`](Model.md#method___set) | Magick method to set model&#039;s data as class attribu... |
| public [`__unset`](Model.md#method___unset) | Magick method to unset attribute in model&#039;s data.  |
| public [`attachBehavior`](Component.md#method_attachBehavior) | Attach a behavior to the component instance.  |
| public [`bind`](Model.md#method_bind) | Bind directly the model data.  |
| public [`detachBehavior`](Component.md#method_detachBehavior) | Detach a behavior.  |
| public [`on`](Component.md#method_on) | Event registration.  |
| public [`toArray`](Model.md#method_toArray) | Get the model data as an associative array.  |
| public [`trigger`](Component.md#method_trigger) | Trigger an event. Event handlers corresponding to ... |
| public [`validate`](Model.md#method_validate) | Validate this model (Should be extended)  |
| public [`when`](Component.md#method_when) | Static event registration.  |
| protected [`init`](Component.md#method_init) | Method called at the end of the constructor.  |

-----

<a name="constant_TYPE_BOOL"></a>
### public $TYPE_BOOL




<a name="constant_TYPE_INT"></a>
### public $TYPE_INT




<a name="constant_TYPE_STRING"></a>
### public $TYPE_STRING




-----

## Properties


<a name="property_db"></a>
### protected $db : \piko\Db
The database instance.






<a name="property_primaryKey"></a>
### protected $primaryKey : string
The name of the primary key. Default to 'id'.






<a name="property_schema"></a>
### protected $schema : array
A name-value pair that describes the structure of the table.
eg.`['id' => self::TYPE_INT, 'name' => 'id' => self::TYPE_STRING]`





<a name="property_tableName"></a>
### protected $tableName : string
The name of the table.





-----

## Methods




<a name="method___construct"></a>
### public __construct(): void

```php
public  __construct(\piko\number  $id, array  $config = []): void
```

Constructor



#### Parameters
**$id** :
The value of the row primary key in order to load the row imediately.

**$config**  (default: []):
An array of configuration.






-----



<a name="method___get"></a>
### public __get(): mixed

```php
public  __get(mixed  $attribute): mixed
```

Magick method to access model's data as class attribute.



#### Parameters
**$attribute** :
The attribute's name.




**see**  \piko\Model::__get()



#### Return:
**mixed**
The attribute's value.

-----



<a name="method___set"></a>
### public __set(): void

```php
public  __set(mixed  $attribute, mixed  $value): void
```

Magick method to set model's data as class attribute.



#### Parameters
**$attribute** :
The attribute's name.

**$value** :
The attribute's value.




**see**  \piko\Model::__set()



-----



<a name="method_delete"></a>
### public delete(): bool

```php
public  delete(): bool
```

Delete this record.






**throws**  \RuntimeException



#### Return:
**bool**


-----



<a name="method_load"></a>
### public load(): void

```php
public  load(\piko\number  $id): void
```

Load row data.



#### Parameters
**$id** :
The value of the row primary key.




**throws**  \RuntimeException



-----



<a name="method_save"></a>
### public save(): bool

```php
public  save(): bool
```

Save this record into the table.






**throws**  \RuntimeException



#### Return:
**bool**


-----



<a name="method_afterDelete"></a>
### protected afterDelete(): void

```php
protected  afterDelete(): void
```

Method called after a delete action.








-----



<a name="method_afterSave"></a>
### protected afterSave(): void

```php
protected  afterSave(): void
```

Method called after a save action.








-----



<a name="method_beforeDelete"></a>
### protected beforeDelete(): bool

```php
protected  beforeDelete(): bool
```

Method called before a delete action.








#### Return:
**bool**


-----



<a name="method_beforeSave"></a>
### protected beforeSave(): bool

```php
protected  beforeSave(bool  $insert): bool
```

Method called before a save action.



#### Parameters
**$insert** :
If the row is a new record, the value will be true, otherwise, false.






#### Return:
**bool**


-----



<a name="method_checkColumn"></a>
### protected checkColumn(): void

```php
protected  checkColumn(string  $name): void
```

Check if column name is defined in the table schema.



#### Parameters
**$name** :





**throws**  \RuntimeException

**see**  \piko\DbRecord::$schema



