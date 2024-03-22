<?php

declare(strict_types=1);

/*
 * This file is part of the symsensor/actuator-mailer-bundle package.
 *
 * (c) Kevin Studer <kreemer@me.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SymSensor\ActuatorMaintenanceBundle\Tests\Service\Health\Indicator;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Mailer\Transport\TransportInterface;
use SymSensor\ActuatorBundle\Service\Health\Health;
use SymSensor\ActuatorBundle\Service\Health\HealthStack;
use SymSensor\ActuatorBundle\Service\Health\HealthState;
use SymSensor\ActuatorMailerBundle\Service\Health\Indicator\Mailer;
use SymSensor\ActuatorMailerBundle\Service\Health\Indicator\MailerTransport\TransportHealthIndicator;

class MaintenanceTest extends TestCase
{
    /**
     * @test
     */
    public function correctName(): void
    {
        $mailer = $this->build();

        self::assertEquals('mailer', $mailer->name());
    }

    /**
     * @test
     */
    public function healthNOKIfNoTransports(): void
    {
        $mailer = $this->build();

        $health = $mailer->health();

        self::assertFalse($health->isUp());
    }

    /**
     * @test
     */
    public function willDelegateHealthCheckToTransportHealthIndicator(): void
    {
        // given
        $transport = self::createMock(TransportInterface::class);

        $transportIndicator = self::createMock(TransportHealthIndicator::class);
        $transportIndicator->expects(self::once())
            ->method('supports')
            ->with(self::equalTo($transport))
            ->willReturn(true);

        $healthTransport = Health::unknown();
        $transportIndicator->expects(self::once())
            ->method('health')
            ->with(self::equalTo($transport))
            ->willReturn($healthTransport);

        $mailer = $this->build(['name' => $transport], [$transportIndicator]);

        // when
        $health = $mailer->health();

        // then
        self::assertSame($healthTransport, $health);
    }

    /**
     * @test
     */
    public function willNotCallTransportHealthIndicatorIfNotSuitable(): void
    {
        // given
        $transport = self::createMock(TransportInterface::class);

        $transportIndicator = self::createMock(TransportHealthIndicator::class);
        $transportIndicator->expects(self::once())
            ->method('supports')
            ->with(self::equalTo($transport))
            ->willReturn(false);

        $transportIndicator->expects(self::never())
            ->method('health');

        $mailer = $this->build(['name' => $transport], [$transportIndicator]);

        // when
        $health = $mailer->health();

        // then
        self::assertEquals(HealthState::UNKNOWN, $health->getStatus());
    }

    /**
     * @test
     */
    public function multipleTransportsWithMultipleHealthChecksResultInStack(): void
    {
        // given
        $transport1 = self::createMock(TransportInterface::class);
        $transport2 = self::createMock(TransportInterface::class);

        $transportIndicator1 = self::createMock(TransportHealthIndicator::class);
        $transportIndicator1->expects(self::exactly(2))
            ->method('supports')
            ->willReturnMap([
                [$transport1, true],
                [$transport2, false],
            ])
        ;

        $transportIndicator1->expects(self::once())
            ->method('health')
            ->with(self::equalTo($transport1))
            ->willReturn(Health::up(['id' => '1']))
        ;

        $transportIndicator2 = self::createMock(TransportHealthIndicator::class);
        $transportIndicator2->expects(self::once())
            ->method('supports')
            ->with(self::equalTo($transport2))
            ->willReturn(true)
        ;

        $transportIndicator2->expects(self::once())
            ->method('health')
            ->with(self::equalTo($transport2))
            ->willReturn(Health::up(['id' => '2']))
        ;

        $mailer = $this->build(['name1' => $transport1, 'name2' => $transport2], [$transportIndicator1, $transportIndicator2]);

        // when
        $health = $mailer->health();

        // then
        self::assertTrue($health->isUp());
        self::assertInstanceOf(HealthStack::class, $health);

        self::assertArrayHasKey('name1', $health->jsonSerialize());
        self::assertArrayHasKey('name2', $health->jsonSerialize());

        self::assertInstanceOf(Health::class, $health->jsonSerialize()['name1']);
        self::assertInstanceOf(Health::class, $health->jsonSerialize()['name2']);

        self::assertEquals(['id' => 1], $health->jsonSerialize()['name1']->getDetails());
        self::assertEquals(['id' => 2], $health->jsonSerialize()['name2']->getDetails());
    }

    /**
     * @param array<string, TransportInterface> $transports
     * @param array<TransportHealthIndicator>   $transportHealthIndicators
     */
    private function build(array $transports = [], array $transportHealthIndicators = []): Mailer
    {
        return new Mailer($transports, $transportHealthIndicators);
    }
}
