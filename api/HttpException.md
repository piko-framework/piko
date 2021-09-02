

# \piko\HttpException

HttpException convert exception code to http status header.





**see**  \Exception






## Methods

| Name | Description |
|------|-------------|
| public [`__construct`](#method___construct) | Constructor sends http header if php SAPI != cli.  |


-----



## Methods




<a name="method___construct"></a>
### public __construct(): mixed

```php
public  __construct(string  $message = null, int  $code = null, \Throwable  $previous = null): mixed
```

Constructor sends http header if php SAPI != cli.



#### Parameters
**$message**  (default: null):
The exception message.

**$code**  (default: null):
The exception code (should be an HTTP status code, eg. 404)

**$previous**  (default: null):
A previous exception.






#### Return:
**mixed**


