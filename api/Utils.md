

# \piko\Utils

Miscellaneous utils.










## Methods

| Name | Description |
|------|-------------|
| public [`parseEnvFile`](#method_parseEnvFile) | Parse an environment configuration file and set en... |


-----



## Methods




<a name="method_parseEnvFile"></a>
### public parseEnvFile(): void

```php
public static  parseEnvFile(string  $file): void
```

Parse an environment configuration file and set environment variables.
The expected format of the configuration file is :
```
...
ENV_KEY1 = env_value1
ENV_KEY2 = env_value2
...
```


#### Parameters
**$file** :
The file path.




**throws**  \RuntimeExceptionIf file not found.



