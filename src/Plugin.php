<?php

declare(strict_types=1);

namespace Pest\Init;

use Pest\Contracts\Plugins\HandlesArguments;
use Pest\TestSuite;
use Symfony\Component\Console\Output\OutputInterface;

final class Plugin implements HandlesArguments
{
    private const INIT_OPTION = 'init';

    private const STUBS = [
        'phpunit.xml' => 'phpunit.xml',
        'Pest.php'    => 'tests/Pest.php',
        'Helpers.php' => 'tests/Helpers.php',
    ];

    /** @var OutputInterface */
    private $output;

    /** @var TestSuite */
    private $testSuite;

    public function __construct(TestSuite $testSuite, OutputInterface $output)
    {
        $this->testSuite = $testSuite;
        $this->output    = $output;
    }

    public function handleArguments(array $originals): array
    {
        if (!array_key_exists(1, $originals) || $originals[1] !== self::INIT_OPTION) {
            return $originals;
        }

        $this->init();

        exit(0);
    }

    private function init(): void
    {
        $testsBaseDir = "{$this->testSuite->rootPath}/tests";

        if (!is_dir($testsBaseDir)) {
            if (!mkdir($testsBaseDir) && !is_dir($testsBaseDir)) {
                $this->output->writeln(sprintf(
                    '<fg=white;bg=red>[ERROR] Directory `%s` was not created</>',
                    $testsBaseDir
                ));

                return;
            }

            $this->output->writeln('[OK] Created `tests` directory');
        }

        foreach (self::STUBS as $from => $to) {
            $fromPath = __DIR__ . "/../stubs/$from";
            $toPath   = "{$this->testSuite->rootPath}/$to";

            if (file_exists($toPath)) {
                $this->output->writeln(sprintf(
                    '<fg=yellow>[INFO] File `%s` already exists, skipped</>',
                    $to
                ));

                continue;
            }

            if (!copy($fromPath, $toPath)) {
                $this->output->writeln(sprintf(
                    '<fg=white;bg=red>[WARNING] Failed to copy stub `%s` to `%s`</>',
                    $from,
                    $toPath
                ));

                continue;
            }

            $this->output->writeln(sprintf(
                '<fg=black;bg=green>[OK] Created `%s` file</>',
                $to
            ));
        }

        $this->output->writeln('<fg=black;bg=green>[OK] Pest initialised!</>');
    }
}
