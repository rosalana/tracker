# Rosalana Tracer

This package is a part of the Rosalana eco-system. It provides tracing and logging functionalities for applications built within the Rosalana ecosystem.

> **Note:** This package is a extension of the [Rosalana Core](https://packagist.org/packages/rosalana/core) package.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Features](#features)
    - [Route Tracing](#route-tracing)
    - [Exception Logging](#exception-logging)
    - [Service Tracing](#service-tracing)
- [License](#license)

## Installation

To install the `rosalana/tracer` package, you must first have the `rosalana/core` package installed. If you haven't installed it yet, please refer to the `rosalana/core` documentation.

After installing the `rosalana/core` package, use the `rosalana:add` command from the Rosalana CLI and select `rosalana/tracer` from the list:

```bash
php artisan rosalana:add
```

After installing the package, you should publish its assets using the following command:

```bash
php artisan rosalana:publish
```

You can specify which files to publish. Publishing **the configuration files is required** to set up the package properly. Other files are optional and can be published as needed. However, it is recommended to publish all files to take full advantage of the package features.

## Configuration

After publishing the package, you will find a `rosalana.php` configuration file in the `config` directory of your Laravel application. You can customize these options according to your needs.

This file will grow over time as you add more Rosalana packages to your application. Each package contributes its own configuration section. The `rosalana.php` file serves as the central configuration hub for all Rosalana packages.

`rosalana/tracer` package provides configuration options for:

- `tracer`: Settings related to tracing functionalities, such as enabling/disabling tracing, setting log behavior, and etc.


## Features

### Route Tracing
The tracer package automatically traces incoming HTTP requests and logs relevant information, including request method, URL, headers, and response status. This helps in monitoring application performance and identifying potential bottlenecks.

Tracking is separated by route groups, allowing you to see how different sections of your application are performing.

### Exception Logging
The tracer package captures and logs exceptions that occur within the application. It records details such as exception type, message, stack trace, and context information. This aids in debugging and identifying issues in the application.

Exceptions can be send to external monitoring immediately if you provide the exception class names in the configuration. Other exceptions are stored in the database and sent in batches periodically.

### Service Tracing
The tracer package integrates with various services within the Rosalana ecosystem, such as Outpost and Basecamp. It traces interactions with these services, logging request and response data, headers, and status codes. This allows you to monitor the communication between your application and external services.

## License

Rosalana Accounts is open-source under the [MIT license](/LICENCE), allowing you to freely use, modify, and distribute it with minimal restrictions.

You may not be able to use our systems but you can use our code to build your own.

For details on how to contribute or how the Rosalana ecosystem is maintained, please refer to each repository’s individual guidelines.

**Questions or feedback?**

Feel free to open an issue or contribute with a pull request. Happy coding with Rosalana!
