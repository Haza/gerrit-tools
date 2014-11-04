<?php

namespace Gerrit\GerritTools\Cli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use GuzzleHttp\Message;


class GerritChangesListCommand extends GerritCommand {

  protected function configure() {
    $this
      ->setName('change:list')
      ->setDescription('List all open changes.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    $client = $this->getClient();
    $baseUrl = $client->getBaseUrl();
    $response = $client->get($baseUrl . '/changes/');

    $changes = $this->fixGerritJson((string) $response->getBody());
    $changes = json_decode($changes);

    $table = $this->getHelperSet()->get('table');
    $table->setHeaders(array('_number', 'subject', 'project', 'branch', '+/-'));
    foreach ($changes as $change) {
      $rows[] = array(
      $change->_number, $change->subject, $change->project ,$change->branch, '+' . $change->insertions . '/-' . $change->deletions);
    }
    $table->setRows($rows);
    $table->render($output);

  }

}
