<?php

declare(strict_types=1);

/*
 * This file is part of the symsensor/actuator-maintenance-bundle package.
 *
 * (c) Kevin Studer <kreemer@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymSensor\ActuatorMaintenanceBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use SymSensor\ActuatorMaintenanceBundle\Service\Health\Indicator as HealthIndicator;

final class SymSensorActuatorMaintenanceExtension extends Extension
{
    /**
     * @param mixed[] $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if ($this->isConfigEnabled($container, $config)) {
            $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
            $loader->load('services.yaml');
            if (isset($config['files']) && \is_array($config['files'])) {
                $definition = $container->getDefinition(HealthIndicator\Maintenance::class);
                $definition->replaceArgument('$files', $config['files']);
            }
        }
    }
}
