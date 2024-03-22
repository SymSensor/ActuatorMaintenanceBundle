# ActuatorMaintenanceBundle

<img src="https://github.com/SymSensor/ActuatorMaintenanceBundle/blob/main/docs/logo.png?raw=true" align="right" width="250"/>

ActuatorMaintenanceBundle extends [ActuatorBundle](https://github.com/SymSensor/ActuatorBundle) by providing health indicator which will signal if the application is in maintenance mode.

## Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
$ composer require symsensor/actuator-maintenance-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require symsensor/actuator-maintenance-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    SymSensor\ActuatorBundle\SymSensorActuatorMaintenanceBundle::class => ['all' => true],
];
```


## Configuration

The Bundle can be configured with a configuration file named `config/packages/sym_sensor_actuator.yaml`. Following snippet shows the default value for all configurations:

```yaml
sym_sensor_actuator_maintenance:
  enabled: true
  files:
    - /tmp/maintenance
    - /var/www/html/maintenance
```

List all files which should be monitored. This extension will turn the health red if the file exists, is readable by the user and contains the content "1" (without newline). If multiple files are declared, then only one of the file has to meet the criteria. 

## License

ActuatorBundle is released under the MIT Licence. See the bundled LICENSE file for details.

## Author

Originally developed by [Arkadiusz Kondas](https://twitter.com/ArkadiuszKondas)
