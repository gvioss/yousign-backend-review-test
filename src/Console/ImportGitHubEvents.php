<?php

declare(strict_types=1);

namespace App\Console;

use App\Bus\CommandBus;
use App\Command\ImportGitHubArchive;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:import-github-events',
    description: 'Import GH events'
)]
class ImportGitHubEvents extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'date',
                InputArgument::OPTIONAL,
                'Date for required archive, with format YYYY-MM-DD. If not provide, today\'s date is used.',
                (new \DateTimeImmutable())->format('Y-m-d')
            )
            ->addArgument(
                'hour',
                InputArgument::OPTIONAL,
                'Hour for required archive, from 0 to 23. If not provide, the whole day is going to be imported.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $date = $input->getArgument('date');
        $hour = $input->getArgument('hour');

        try {
            $commands = array_map(
                static fn (int $hour): ImportGitHubArchive => new ImportGitHubArchive($date, $hour),
                $hour ? [$hour] : range(0, 23)
            );
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>',$e->getMessage()));
            return Command::FAILURE;
        }

        $progress = new ProgressBar($output, count($commands));
        $progress->start();
        foreach ($progress->iterate($commands) as $command) {
            $this->commandBus->dispatch($command);
        }

        $progress->finish();

        $output->writeln(['', '<info>Import initialized with success.</info>']);

        return Command::SUCCESS;
    }
}
