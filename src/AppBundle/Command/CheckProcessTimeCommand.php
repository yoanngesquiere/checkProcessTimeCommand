<?php
namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class CheckProcessTimeCommand extends Command
{
	protected function configure()
    {
        $this
            ->setName('check:process_time')
            ->setDescription('Check execution time of a process')
            ->addArgument(
                'process',
                InputArgument::REQUIRED,
                'What is the name of the process to check?'
            )
            ->addOption(
                'max-time',
                null,
                InputOption::VALUE_OPTIONAL,
                'What is the max time in seconds before alerting?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $processName = $input->getArgument('process');
        $maxTime = $input->getOption('max-time');
        $process = new Process('ps -eo "%c %t" | grep '.$processName.' ');
		$process->run();

		// executes after the command finishes
		if (!$process->isSuccessful()) {
		    throw new \RuntimeException("No process found with name ".$processName);
		}

        $output->getFormatter()
            ->setStyle(
                'alert',
                new OutputFormatterStyle('red', null, array('bold')
                ));
        
        $table = new Table($output);
        $data = explode("\n", trim($process->getOutput()));
        $table->setHeaders(array('Process', 'Time', 'Time in seconds'));

        $hours = 0;
        $minutes = 0;
        $seconds = 0;

        foreach ($data as $line) {
            $line = preg_replace('/\s+/', ' ', $line);
            $line = explode(" ", $line);

            if (substr_count($line[1], ':') < 2) {
                $line[1] = '00:'.$line[1];
            }

            sscanf($line[1], "%d:%d:%d", $hours, $minutes, $seconds);
            $line[2] = $hours * 3600 + $minutes * 60 + $seconds;
            if ($maxTime && $maxTime < $line[2]) {
                $line[2] = '<alert>' . $line[2] . '</alert>';
            }

            $table->addRow($line);
        }
        $table->render();
    }
}
