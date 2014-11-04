<?php

namespace Gerrit\GerritTools\Cli\Command;

use Gerrit\GerritTools;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

use GuzzleHttp\Message;


class GerritChangePickCommand extends GerritCommand {

  protected function configure() {
    $this
      ->setName('change:pick')
      ->setDescription('Pick a change.')
      ->addArgument(
        'id',
        InputArgument::OPTIONAL,
        'The change ID'
      )
      ->addOption(
        'method',
        'm',
        InputOption::VALUE_OPTIONAL,
        'The prefered method to get the change.'
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    // Did we specified a change ID ?
    $changeId = $input->getArgument('id');
    if (empty($changeId)) {
      $changeId = $this->getOpenChangeId($input, $output);

    }

    if (!$this->isDowloadCommandsSupported()) {
      $output->writeln("Plugin 'Download Commands' not present on this Gerrit server");
      return;
    }
    $method = $input->getOption('method');
    if (empty($method)) {
      $questionText = "Choose your method:";
      $questionMethods = array('ssh', 'http');
      $helper = $this->getHelper('question');
      $question = new ChoiceQuestion($questionText, $questionMethods);
      $method = $helper->ask($input, $output, $question);
    }

    // Get the client.
    $client = $this->getClient();
    $baseUrl = $client->getBaseUrl();
    $response = $client->get($baseUrl . '/changes/?q=' . $changeId . '&o=CURRENT_REVISION&o=CURRENT_COMMIT&o=CURRENT_FILES&o=DOWNLOAD_COMMANDS');

    try {
      $body_troncated = $this->fixGerritJson((string) $response->getBody());
      $details = json_decode($body_troncated);
      $change = $details[0];

      $fetch = $change->revisions->{$change->current_revision}->fetch;
      switch ($method) {
        case 'http':
          $http = $fetch->http;
          $commands = array();
          foreach ($http->commands as $key => $command) {
            $commands[$key] = $command;
          }
          $commands['cancel'] = '- Cancel -';
          $questionText = "Which one to apply ?";
          $helper = $this->getHelper('question');
          $question = new ChoiceQuestion($questionText, $commands);
          $command = $helper->ask($input, $output, $question);
          break;
        case 'ssh':
        default:
          $ssh = $fetch->ssh;
          $commands = array();
          foreach ($ssh->commands as $key => $command) {
            $commands[$key] = $command;
          }
          $commands['cancel'] = '- Cancel -';
          $helper = $this->getHelper('question');
          $question = new ChoiceQuestion(NULL, $commands);
          $command = $helper->ask($input, $output, $question);
          break;
      }
      if ($command != '- Cancel -') {
        $this->ShellDo($ssh->commands->{'Cherry Pick'});
      }
    } catch (Exception $e) {
      echo $response->getResponse()->getRawHeaders();
    }
  }


  public function isDowloadCommandsSupported() {
    $client = $this->getClient();
    $baseUrl = $client->getBaseUrl();
    $response = $client->get($baseUrl . '/plugins/');
    $plugins = $this->fixGerritJson((string) $response->getBody());
    $plugins = json_decode($plugins);
    foreach ($plugins as $plugin) {
      if ($plugin->id == 'download-commands') {
        return TRUE;
      }
    }
    // Download commands not found :(
    return FALSE;
  }

}
