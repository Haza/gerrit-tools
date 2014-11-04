<?php

namespace Gerrit\GerritTools\Cli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use GuzzleHttp\Message;
use GuzzleHttp\Client;

class WelcomeCommand extends GerritCommand
{

    protected function configure()
    {
        $this
            ->setName('welcome')
            ->setDescription('Welcome to GerritTools');
    }

    public function isEnabled() {
        // Hide the command in the list.
        global $argv;
        return !isset($argv[1]) || $argv[1] != 'list';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
      $output->writeln("\n
   _____                _ _     _______          _
  / ____|              (_) |   |__   __|        | |
 | |  __  ___ _ __ _ __ _| |_     | | ___   ___ | |___
 | | |_ |/ _ \ '__| '__| | __|    | |/ _ \ / _ \| / __|
 | |__| |  __/ |  | |  | | |_     | | (_) | (_) | \__ \
  \_____|\___|_|  |_|  |_|\__|    |_|\___/ \___/|_|___/
      ");
    }

}
