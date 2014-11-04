<?php

namespace Gerrit\GerritTools\Cli\Command;

use Gerrit\GerritTools;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Dumper;

class InitGlobalCommand extends GerritCommand {

  protected function configure() {
    $this
      ->setName('init:global')
      ->setDescription('Initialize a global gerrit-tools configuration with the correct credentials.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $question = new Question('Please enter the URL of your Gerrit instance: ', '');
    $helper = $this->getHelper('question');
    $gerrit['gerrit_uri'] = $helper->ask($input, $output, $question);

    $question = new Question('Please enter your username on the gerrit instance: ', '');
    $helper = $this->getHelper('question');
    $gerrit['user'] = $helper->ask($input, $output, $question);

    $question = new Question('Please enter your password on your Gerrit instance: ', '');
    $helper = $this->getHelper('question');
    $gerrit['pass'] = $helper->ask($input, $output, $question);

    $configPath = $this->getHomeDirectory() . '/.gerrittools';
    $dumper = new Dumper();
    file_put_contents($configPath, $dumper->dump($gerrit));
    $output->writeln('Configuration has been written in '. $this->getHomeDirectory() . '/.gerrittools');
  }

}
