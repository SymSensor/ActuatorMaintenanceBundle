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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sym_sensor_actuator_maintenance');

        $rootNode = $treeBuilder->getRootNode();

        $rootNode // @phpstan-ignore-line
            ->canBeDisabled()
            ->children()
                ->arrayNode('files')
                    ->scalarPrototype()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
