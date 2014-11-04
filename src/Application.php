<?php

namespace Gerrit\GerritTools\Cli;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Shell;


class Application extends ConsoleApplication {

    protected $output;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct('GerritTools', '0.1');

        $this->setDefaultTimezone();

        $this->add(new Command\WelcomeCommand);
        $this->add(new Command\InitGlobalCommand);
        $this->add(new Command\InitProjectCommand);
        $this->add(new Command\GerritProjectListCommand);
        $this->add(new Command\GerritChangesListCommand);
        $this->add(new Command\GerritChangeDetailsCommand);
        $this->add(new Command\GerritChangePickCommand);

        $this->setDefaultCommand('welcome');
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultInputDefinition()
    {
        // We remove the confusing `--ansi` and `--no-ansi` options.
        return new InputDefinition(array(
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message.'),
            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message.'),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE, 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version.'),
            new InputOption('--yes', '-y', InputOption::VALUE_NONE, 'Answer "yes" to all prompts.'),
            new InputOption('--no', '-n', InputOption::VALUE_NONE, 'Answer "no" to all prompts.'),
            new InputOption('--shell', '-s', InputOption::VALUE_NONE, 'Launch the shell.'),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        // Set the input to non-interactive if the yes or no options are used.
        if ($input->hasParameterOption(array('--yes', '-y')) || $input->hasParameterOption(array('--no', '-n'))) {
            $input->setInteractive(false);
        }
        // Enable the shell.
        elseif ($input->hasParameterOption(array('--shell', '-s'))) {
            $shell = new Shell($this);
            $shell->run();
            return 0;
        }

        $this->output = $output;
        return parent::doRun($input, $output);
    }

    /**
     * @return OutputInterface
     */
    public function getOutput() {
        if (isset($this->output)) {
            return $this->output;
        }
        $stream = fopen('php://stdout', 'w');
        return new StreamOutput($stream);
    }

    /**
     * Set the default timezone.
     *
     * PHP 5.4 has removed the autodetection of the system timezone,
     * so it needs to be done manually.
     * UTC is the fallback in case autodetection fails.
     */
    protected function setDefaultTimezone() {
        $timezone = 'UTC';
        if (is_link('/etc/localtime')) {
            // Mac OS X (and older Linuxes)
            // /etc/localtime is a symlink to the timezone in /usr/share/zoneinfo.
            $filename = readlink('/etc/localtime');
            if (strpos($filename, '/usr/share/zoneinfo/') === 0) {
                $timezone = substr($filename, 20);
            }
        } elseif (file_exists('/etc/timezone')) {
            // Ubuntu / Debian.
            $data = file_get_contents('/etc/timezone');
            if ($data) {
                $timezone = trim($data);
            }
        } elseif (file_exists('/etc/sysconfig/clock')) {
            // RHEL/CentOS
            $data = parse_ini_file('/etc/sysconfig/clock');
            if (!empty($data['ZONE'])) {
                $timezone = trim($data['ZONE']);
            }
        }

        date_default_timezone_set($timezone);
     }

}
