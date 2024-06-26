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

namespace SymSensor\ActuatorMaintenanceBundle\Service\Health\Indicator;

use SymSensor\ActuatorBundle\Service\Health\Health;
use SymSensor\ActuatorBundle\Service\Health\HealthInterface;
use SymSensor\ActuatorBundle\Service\Health\Indicator\HealthIndicator;

class Maintenance implements HealthIndicator
{
    /**
     * @var string[]
     */
    private array $files;

    /**
     * @param string[] $files
     */
    public function __construct(array $files)
    {
        $this->files = $files;
    }

    public function name(): string
    {
        return 'maintenance';
    }

    public function health(): HealthInterface
    {
        foreach ($this->files as $file) {
            if (!\file_exists($file)) {
                continue;
            }

            if (!\is_readable($file)) {
                continue;
            }

            if (\filesize($file) > 100) {
                continue;
            }

            $content = @\file_get_contents($file);

            if ('1' === $content) {
                return Health::down(\sprintf('File "%s" indicates maintenance mode', $file));
            }
        }

        return Health::up();
    }
}
