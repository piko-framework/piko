

# \piko\Piko

Piko is the helper class for the Piko framework.








## Properties

| Name | Description |
|------|-------------|
| public [`$app`](#property_app) | The application instance.  |
| protected [`$aliases`](#property_aliases) | The aliases container.  |
| protected [`$registry`](#property_registry) | The registry container.  |
| protected [`$singletons`](#property_singletons) | The singletons container.  |


## Methods

| Name | Description |
|------|-------------|
| public [`configureObject`](#method_configureObject) | Configure public attributes of an object.  |
| public [`createObject`](#method_createObject) | Singleton factory method.  |
| public [`get`](#method_get) | Retrieve data from the registry.  |
| public [`getAlias`](#method_getAlias) | Translates a path alias into an actual path.  |
| public [`set`](#method_set) | Store data in the registry.  |
| public [`setAlias`](#method_setAlias) | Registers a path alias. A path alias is a short na... |
| public [`t`](#method_t) | Translate a text. This is a shortcut to translate ... |


-----


## Properties


<a name="property_app"></a>
### public $app : \piko\Application
The application instance.






<a name="property_aliases"></a>
### protected $aliases : array
The aliases container.






<a name="property_registry"></a>
### protected $registry : array
The registry container.






<a name="property_singletons"></a>
### protected $singletons : array
The singletons container.





-----

## Methods




<a name="method_configureObject"></a>
### public configureObject(): void

```php
public static  configureObject(object  $object, array  $properties = []): void
```

Configure public attributes of an object.



#### Parameters
**$object** :
The object instance.

**$properties**  (default: []):
A name-value pair array corresponding to the object public properties.






-----



<a name="method_createObject"></a>
### public createObject(): object

```php
public static  createObject(string|array  $type, array  $properties = []): object
```

Singleton factory method.



#### Parameters
**$type** :
The object type.
If it is a string, it should be the fully qualified name of the class.
If an array given, it should contain the key 'class' with the value corresponding
to the fully qualified name of the class

**$properties**  (default: []):
A name-value pair array corresponding to the object public properties.






#### Return:
**object**


-----



<a name="method_get"></a>
### public get(): mixed

```php
public static  get(string  $key, mixed  $default = null): mixed
```

Retrieve data from the registry.



#### Parameters
**$key** :
The registry key.

**$default**  (default: null):
Default value if data is not found from the registry.






#### Return:
**mixed**


-----



<a name="method_getAlias"></a>
### public getAlias(): string|bool

```php
public static  getAlias(string  $alias): string|bool
```

Translates a path alias into an actual path.



#### Parameters
**$alias** :
The alias to be translated.






#### Return:
**string|bool**
The path corresponding to the alias. False if the alias is not registered.

-----



<a name="method_set"></a>
### public set(): void

```php
public static  set(string  $key, mixed  $value): void
```

Store data in the registry.



#### Parameters
**$key** :


**$value** :







-----



<a name="method_setAlias"></a>
### public setAlias(): void

```php
public static  setAlias(string  $alias, string  $path): void
```

Registers a path alias.
A path alias is a short name representing a long path (a file path, a URL, etc.)


#### Parameters
**$alias** :
The alias name (e.g. "@web"). It must start with a '@' character.

**$path** :
the path corresponding to the alias.




**throws**  \InvalidArgumentExceptionif $path is an invalid alias.

**see**  \piko\Piko::getAlias()



-----



<a name="method_t"></a>
### public t(): string

```php
public static  t(string  $domain, string  $text, array  $params = []): string
```

Translate a text.
This is a shortcut to translate method in i18n component.


#### Parameters
**$domain** :
The translation domain, for instance 'app'.

**$text** :
The text to translate.

**$params**  (default: []):
Parameters substitution.




**see**  \piko\I18n



#### Return:
**string**
The translated text or the text itself if no translation was found.

