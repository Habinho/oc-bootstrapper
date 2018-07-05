<?php

namespace OFFLINE\Bootstrapper\October\Console;

use OFFLINE\Bootstrapper\October\Util\UsesTemplate;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class InitCommand
 * @package OFFLINE\Bootstrapper\October\Console
 */
class InitCommand extends Command
{
    use UsesTemplate;

    /**
     * Configure the command options.
     *
     * @throws InvalidArgumentException
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Create a new October CMS project.')
            ->addArgument('directory', InputArgument::OPTIONAL, 'Name of the working directory', '.');
    }

    /**
     * Execute the command.
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     *
     * @throws RuntimeException
     * @return mixed
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('<info>Creating project directory...</info>');

        $dir = getcwd() . DS . $input->getArgument('directory');

        $this->createWorkingDirectory($dir);

        $output->writeln('<info>Updating template files...</info>');
        $this->updateTemplateFiles();

        $template = $this->getTemplate('october.yaml');
        $target   = $dir . DS . 'october.yaml';

        $output->writeln('<info>Creating default october.yaml...</info>');

        if (file_exists($target)) {
            return $output->writeln('<comment>october.yaml already exists: ' . $target . '</comment>');
        }

        $this->copyYamlTemplate($template, $target);

        $output->writeln('<comment>Done! Now edit your october.yaml and run october install.</comment>');

        return true;
    }

    /**
     * @param $dir
     *
     * @throws \RuntimeException
     */
    protected function createWorkingDirectory($dir)
    {
        if ( ! @mkdir($dir) && ! is_dir($dir)) {
            throw new RuntimeException('Cannot create target directory: ' . $dir);
        }
    }

    /**
     * @param $template
     * @param $target
     *
     * @throws \RuntimeException
     */
    protected function copyYamlTemplate($template, $target)
    {
        if ( ! file_exists($template)) {
            throw new RuntimeException('Cannot find october.yaml template: ' . $template);
        }

        copy($template, $target);

        if ( ! file_exists($target)) {
            throw new RuntimeException('october.yaml could not be created');
        }
    }
}