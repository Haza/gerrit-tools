<?php

namespace Gerrit\GerritTools\Cli\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use GuzzleHttp\Message;


class GerritProjectListCommand extends GerritCommand {

  protected function configure() {
    $this
      ->setName('project:list')
      ->setDescription('List all your projects.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    $client = $this->getClient();

    $baseUrl = $client->getBaseUrl();
    $res = $client->get($baseUrl . '/projects/');
    $body_troncated = $this->fixGerritJson((string) $res->getBody());
    $projects = json_decode($body_troncated);
    $table = $this->getHelperSet()->get('table');
    $table->setHeaders(array('Machine name', 'Title', 'Description'));
    foreach ($projects as $key => $project) {
      $rows[] = array(
        urldecode($key),
        urldecode($project->id),
        !empty($project->description) ? urldecode($project->description) : NULL,
      );
    }
    $table->setRows($rows);
    $table->render($output);

  }
}
