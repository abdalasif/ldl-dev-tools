<?php

declare(strict_types=1);

namespace LDL\DevTools\Command;

use Exception;
use LDL\DevTools\Project;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class LDLSubProjectCommand extends Command
{    
    /**
     * current working directory path
     * 
     * @var string|null
     */
    protected $currentWorkingDirector;
    
    /**
     * the default command name
     * 
     * @var string|null
     */
    protected static $defaultName = 'create:project';

    /**
     * The default command description
     * 
     * @var string|null
     */
    protected static $defaultDescription = 'Pulls in the ldl-project-template repo to create a new library.';

    /**
     * command configuration
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->currentWorkingDirectory = getcwd();

        if (!$this->currentWorkingDirectory) {
            throw new Exception("Invalid directory. Please check the permissions.");
        }

        $this->setDefinition(
            new InputDefinition([
                new InputArgument(
                    'library',
                    InputArgument::REQUIRED,
                    'Library name with ldl prefix.'
                )
            ])
        );
    }

    /**
     * execute the command
     *
     * @param  \Symfony\Component\Console\Input\InputInterface $input
     * @param  \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $shellInput, OutputInterface $shellOutput): int
    {
        $library = $shellInput->getArgument('library');

        if('' === trim($library)){
            throw new Exception('Invalid destination directory provided');
        }

        $libraryPath = $this->currentWorkingDirectory . DIRECTORY_SEPARATOR . $library;

        if(is_dir($libraryPath)){
            $shellOutput->writeln("<error>Destination directory {$library} already exists!");
            return self::FAILURE;
        }

        if(!is_writable($this->currentWorkingDirectory)){
            $shellOutput->writeln("<error>Current working directory is not writable</error>");
            return self::FAILURE;
        }

        try {
            $project =  new Project($libraryPath);
            $project->create();
        } catch (Exception $e) {
            $shellOutput->writeln(sprintf('<error>%s: %s</error>', get_class($e), $e->getMessage()));
            return self::FAILURE;
        }

        $shellOutput->writeln("<bg=green>Library created successfully.</>");

        return self::SUCCESS;
    }
}
