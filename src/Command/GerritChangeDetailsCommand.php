<?php

namespace Gerrit\GerritTools\Cli\Command;

use Gerrit\GerritTools;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

use GuzzleHttp\Message;


class GerritChangeDetailsCommand extends GerritCommand {

  protected function configure() {
    $this
      ->setName('change:detail')
      ->setDescription('Display a change\'s details.')
      ->addArgument(
        'id',
        InputArgument::OPTIONAL,
        'The change ID'
      );
  }

  protected function execute(InputInterface $input, OutputInterface $output) {

    // Did we specified a change ID ?
    $changeId = $input->getArgument('id');
    if (empty($changeId)) {
      $changeId = $this->getOpenChangeId($input, $output);
    }

    $client = $this->getClient();

    $baseUrl = $client->getBaseUrl();
    $res = $client->get($baseUrl . '/changes/?q=' . $changeId . '&o=CURRENT_REVISION&o=CURRENT_COMMIT&o=CURRENT_FILES&o=DOWNLOAD_COMMANDS');
    try {

      $body_troncated = $this->fixGerritJson((string) $res->getBody());
      $details = json_decode($body_troncated);
      $output->writeln("\n### Change detail ### \n");
      $change = $details[0];
      $output->writeln('Project:' . $change->project);
      $output->writeln('Subject:' . $change->subject);
      $output->writeln("\n### Commit information ###" . $change->subject);
      $output->writeln('sha1:' . $change->current_revision);
      $output->writeln('branch:' . $change->branch);
      $output->writeln('diff: +' . $change->insertions . '/-' . $change->deletions);

    } catch (Exception $e) {
      echo $res->getResponse()->getRawHeaders();
    }

  }

}
