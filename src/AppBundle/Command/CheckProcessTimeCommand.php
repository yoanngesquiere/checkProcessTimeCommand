<?php
namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class CheckProcessTimeCommand extends Command
{
	protected function configure()
    {
        $this
            ->setName('check:process_time')
            ->setDescription('Check execution time of a process')
            ->addArgument(
                'process',
                InputArgument::OPTIONAL,
                'What is the name of the process to check?'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processName = $input->getArgument('process');

        $process = new Process('ps aux | grep '.$processName);
		$process->run();

		// executes after the command finishes
		if (!$process->isSuccessful()) {
		    throw new \RuntimeException($process->getErrorOutput());
		}

        $output->writeln($process->getOutput());
    }
}
