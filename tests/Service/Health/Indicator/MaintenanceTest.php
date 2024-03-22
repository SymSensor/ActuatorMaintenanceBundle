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

namespace SymSensor\ActuatorMaintenanceBundle\Tests\Service\Health\Indicator;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\TestCase;
use SymSensor\ActuatorMaintenanceBundle\Service\Health\Indicator\Maintenance;

class MaintenanceTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $root;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup('exampleDir');
    }

    /**
     * @test
     */
    public function correctName(): void
    {
        $indicator = $this->build();

        self::assertEquals('maintenance', $indicator->name());
    }

    /**
     * @test
     */
    public function healthOKIfFileExist(): void
    {
        $indicator = $this->build();
        $health = $indicator->health();

        self::assertTrue($health->isUp());
    }

    /**
     * @test
     */
    public function healthOKIfFileNotReadable(): void
    {
        // given
        $file = vfsStream::newFile('test', 0000)->at($this->root);
        $indicator = $this->build([$file->url()]);

        // when
        $health = $indicator->health();

        // then
        self::assertTrue($health->isUp());
    }

    /**
     * @test
     */
    public function healthOKIfFileIsToBigToRead(): void
    {
        // given
        $file = vfsStream::newFile('test')->at($this->root);
        \file_put_contents($file->url(), \mb_substr(\str_shuffle(\str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', (int) \ceil(1024 / \mb_strlen($x)))), 1, 1024));
        $indicator = $this->build([$file->url()]);

        // when
        $health = $indicator->health();

        // then
        self::assertTrue($health->isUp());
    }

    /**
     * @test
     */
    public function healthOKIfFileContentIsNot1(): void
    {
        // given
        $file = vfsStream::newFile('test')->at($this->root);
        \file_put_contents($file->url(), '0');
        $indicator = $this->build([$file->url()]);

        // when
        $health = $indicator->health();

        // then
        self::assertTrue($health->isUp());
    }

    /**
     * @test
     */
    public function healthNOKIfFileContentIs1(): void
    {
        // given
        $file = vfsStream::newFile('test')->at($this->root);
        \file_put_contents($file->url(), '1');
        $indicator = $this->build([$file->url()]);

        // when
        $health = $indicator->health();

        // then
        self::assertFalse($health->isUp());
    }

    /**
     * @test
     */
    public function healthNOKReturnsFileAsError(): void
    {
        // given
        $file = vfsStream::newFile('test')->at($this->root);
        \file_put_contents($file->url(), '1');
        $indicator = $this->build([$file->url()]);

        // when
        $health = $indicator->health();

        // then
        $json = $health->jsonSerialize();
        self::assertIsArray($json);
        self::assertArrayHasKey('error', $json);
        self::assertStringContainsString($file->url(), $json['error']);
    }

    /**
     * @test
     */
    public function healthOKIfMultipleFilesAreAllOk(): void
    {
        // given
        $file1 = vfsStream::newFile('test1')->at($this->root);
        $file2 = vfsStream::newFile('test2')->at($this->root);

        \file_put_contents($file1->url(), '0');
        \file_put_contents($file2->url(), '0');

        $indicator = $this->build([$file1->url(), $file2->url()]);

        // when
        $health = $indicator->health();

        // then
        self::assertTrue($health->isUp());
    }

    /**
     * @test
     */
    public function healthNOKIfMultipleFilesAreNOk(): void
    {
        // given
        $file1 = vfsStream::newFile('test1')->at($this->root);
        $file2 = vfsStream::newFile('test2')->at($this->root);

        \file_put_contents($file1->url(), '1');
        \file_put_contents($file2->url(), '1');

        $indicator = $this->build([$file1->url(), $file2->url()]);

        // when
        $health = $indicator->health();

        // then
        self::assertFalse($health->isUp());
    }

    /**
     * @test
     */
    public function healthNOKIfOneOfMultipleFilesAreNOk(): void
    {
        // given
        $file1 = vfsStream::newFile('test1')->at($this->root);
        $file2 = vfsStream::newFile('test2')->at($this->root);

        \file_put_contents($file1->url(), '0');
        \file_put_contents($file2->url(), '1');

        $indicator = $this->build([$file1->url(), $file2->url()]);

        // when
        $health = $indicator->health();

        // then
        self::assertFalse($health->isUp());
    }

    /**
     * @param string[] $files
     */
    private function build(array $files = []): Maintenance
    {
        return new Maintenance($files);
    }
}
