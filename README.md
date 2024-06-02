# Laravel Schema Rules

[![Latest Version on Packagist](https://img.shields.io/packagist/v/laracraft-tech/laravel-schema-rules.svg?style=flat-square)](https://packagist.org/packages/laracraft-tech/laravel-schema-rules)
[![Tests](https://github.com/laracraft-tech/laravel-schema-rules/actions/workflows/run-tests.yml/badge.svg?branch=main)](https://github.com/laracraft-tech/laravel-schema-rules/actions/workflows/run-tests.yml)
[![Check & fix styling](https://github.com/laracraft-tech/laravel-schema-rules/actions/workflows/fix-php-code-style-issues.yml/badge.svg?branch=main)](https://github.com/laracraft-tech/laravel-schema-rules/actions/workflows/fix-php-code-style-issues.yml)
[![License](https://img.shields.io/packagist/l/laracraft-tech/laravel-schema-rules.svg?style=flat-square)](https://packagist.org/packages/laracraft-tech/laravel-schema-rules)
[![Total Downloads](https://img.shields.io/packagist/dt/laracraft-tech/laravel-schema-rules.svg?style=flat-square)](https://packagist.org/packages/laracraft-tech/laravel-schema-rules)

Automatically generate basic Laravel graphql schema types based on your database table schema!
Use these as a starting point to fine-tune and optimize your validation rules as needed. 

## Installation

You can install the package via composer:

```bash
composer require tasmidur/laravel-graphql-schema --dev
```

Then publish the config file with:

```bash
php artisan vendor:publish --tag="graphql-schema-config"
```

## ToC

- [`Generate graphql schema type,query,mutation for a whole table`](#generate-type-query-mutation-for-a-whole-table)
- [`Generate graphql schema type,query,mutation Class`](#generate-type-query-mutation-for-a-whole-class)

## Usage

Let's say you've migrated this fictional table:

````php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name', 100);
    $table->string('email');
    $table->text('bio')->nullable();
    $table->enum('gender', ['male', 'female', 'others']);
    $table->date('birth');
    $table->year('graduated');
    $table->float('body_size');
    $table->unsignedTinyInteger('children_count')->nullable();
    $table->integer('account_balance');
    $table->unsignedInteger('net_income');
    $table->boolean('send_newsletter')->nullable();
    $table->timestamps();
});
````

### Generate schema for a whole table

Now if you run:

`php artisan schema:generate-rules users`

You'll get:

GraphQL Schema type for table "schema_test" has been generated!
Copy & paste these wherever your GraphQL type is defined:
GraphQL Type Fields:
```
[
    "id"=> [
        "type"=> Type::nonNull(Type::int()),
        "description"=> "The id of the schema_test"
    ],
    "name"=> [
        "type"=> Type::nonNull(Type::string()),
        "description"=> "The name of the schema_test"
    ],
    "email"=> [
        "type"=> Type::nonNull(Type::string()),
        "description"=> "The email of the schema_test"
    ],
    "bio"=> [
        "type"=> Type::string(),
        "description"=> "The bio of the schema_test"
    ],
    "gender"=> [
        "type"=> Type::nonNull(Type::string()),
        "description"=> "The gender of the schema_test"
    ],
    "birth"=> [
        "type"=> Type::nonNull(Type::string()),
        "description"=> "The birth of the schema_test"
    ],
    "graduated"=> [
        "type"=> Type::nonNull(Type::int()),
        "description"=> "The graduated of the schema_test"
    ],
    "body_size"=> [
        "type"=> Type::nonNull(Type::float()),
        "description"=> "The body_size of the schema_test"
    ],
    "children_count"=> [
        "type"=> Type::int(),
        "description"=> "The children_count of the schema_test"
    ],
    "account_balance"=> [
        "type"=> Type::nonNull(Type::int()),
        "description"=> "The account_balance of the schema_test"
    ],
    "net_income"=> [
        "type"=> Type::nonNull(Type::int()),
        "description"=> "The net_income of the schema_test"
    ],
    "send_newsletter"=> [
        "type"=> Type::boolean(),
        "description"=> "The send_newsletter of the schema_test"
    ]
]
```
GraphQL Validation Rules:
[
    'id' => ['required', 'integer'],
    'name' => ['required', 'string', 'min:1'],
    'email' => ['required', 'string', 'min:1'],
    'bio' => ['nullable', 'string', 'min:1'],
    'gender' => ['required', 'string', 'min:1'],
    'birth' => ['required', 'date'],
    'graduated' => ['required', 'integer'],
    'body_size' => ['required', 'numeric'],
    'children_count' => ['nullable', 'integer'],
    'account_balance' => ['required', 'integer'],
    'net_income' => ['required', 'integer'],
    'send_newsletter' => ['nullable', 'boolean']
]
GraphQL Query Arguments:
[
    [
        "name"=> "id",
        "type"=> Type::int()
    ],
    [
        "name"=> "name",
        "type"=> Type::string()
    ],
    [
        "name"=> "email",
        "type"=> Type::string()
    ],
    [
        "name"=> "bio",
        "type"=> Type::string()
    ],
    [
        "name"=> "gender",
        "type"=> Type::string()
    ],
    [
        "name"=> "birth",
        "type"=> Type::string()
    ],
    [
        "name"=> "graduated",
        "type"=> Type::int()
    ],
    [
        "name"=> "body_size",
        "type"=> Type::float()
    ],
    [
        "name"=> "children_count",
        "type"=> Type::int()
    ],
    [
        "name"=> "account_balance",
        "type"=> Type::int()
    ],
    [
        "name"=> "net_income",
        "type"=> Type::int()
    ],
    [
        "name"=> "send_newsletter",
        "type"=> Type::boolean()
    ]
]

```

As you may have noticed the float-column `body_size`, just gets generated to `['required', 'numeric']`.
Proper rules for `float`, `decimal` and `double`, are not yet implemented! 

### Generate rules for specific columns

You can also explicitly specify the columns:

`php artisan schema:generate-rules persons --columns first_name,last_name,email`

Which gives you:
````
Schema-based validation rules for table "persons" have been generated!
Copy & paste these to your controller validation or form request or where ever your validation takes place:
[
    'first_name' => ['required', 'string', 'min:1', 'max:100'],
    'last_name' => ['required', 'string', 'min:1', 'max:100'],
    'email' => ['required', 'string', 'min:1', 'max:255']
]
````

### Generate Form Request Class

Optionally, you can add a `--create-request` or `-c` flag,
which will create a form request class with the generated rules for you!

```` bash
# creates app/Http/Requests/StorePersonRequest.php (store request is the default)
php artisan schema:generate-rules persons --create-request 

# creates/overwrites app/Http/Requests/StorePersonRequest.php
php artisan schema:generate-rules persons --create-request --force
 
# creates app/Http/Requests/UpdatePersonRequest.php
php artisan schema:generate-rules persons --create-request --file UpdatePersonRequest

# creates app/Http/Requests/Api/V1/StorePersonRequest.php
php artisan schema:generate-rules persons --create-request --file Api\\V1\\StorePersonRequest

# creates/overwrites app/Http/Requests/Api/V1/StorePersonRequest.php (using shortcuts)
php artisan schema:generate-rules persons -cf --file Api\\V1\\StorePersonRequest
````

### Always skip columns

To always skip columns add it in the config file, under `skip_columns` parameter.

```php
'skip_columns' => ['whatever', 'some_other_column'],
```


## Supported Drivers

Currently, the supported database drivers are `MySQL`, `PostgreSQL`, and `SQLite`.

Please note, since each driver supports different data types and range specifications,
the validation rules generated by this package may vary depending on the database driver you are using.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Zacharias Creutznacher](https://github.com/laracraft-tech)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
