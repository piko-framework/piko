

# \piko\Db

Db is the base class to access SQL databases. It's just a proxy to \PDO.










## Methods

| Name | Description |
|------|-------------|
| public [`__construct`](#method___construct) | Extends PDO constructor to accept an array of conf... |


-----



## Methods




<a name="method___construct"></a>
### public __construct(): mixed

```php
public  __construct(array  $config = []): mixed
```

Extends PDO constructor to accept an array of configuration.



#### Parameters
**$config**  (default: []):
An array (name-value pairs) containing
dsn, username, password and options of the database.




**see**  \PDO::__construct()



#### Return:
**mixed**


