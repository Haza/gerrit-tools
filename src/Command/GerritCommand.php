<?php

namespace Gerrit\GerritTools\Cli\Command;

use GuzzleHttp\Message;
use GuzzleHttp\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Question\ChoiceQuestion;

class GerritCommand extends Command
{
  protected $config;
  protected $client;

  /**
   * Load configuration from the user's .platform file.
   *
   * Configuration is loaded only if $this->config hasn't been populated
   * already. This allows LoginCommand to avoid writing the config file
   * before using the client for the first time.
   *
   * @return array The populated configuration array.
   */
  protected function loadConfig()
  {
    if (!$this->config) {
        $configPath = $this->getHomeDirectory() . '/.gerrittools';
        $yaml = new Parser();
        $this->config = $yaml->parse(file_get_contents($configPath));
    }

    return $this->config;
  }

  /**
   * Return an instance of the Guzzle client for the gerrit endpoint.
   *
   * @return Client
   */
  protected function getClient()
  {

    $config = $this->loadConfig();
    $user = $config['user'];
    $pass = $config['pass'];
    $gerrit_uri = $config['gerrit_uri'];
      if (!$this->client) {
          $this->client = new Client(['base_url' => $gerrit_uri . '/a']);
          $this->client->setDefaultOption('auth', array(
              $user,
              $pass,
              'Digest'));
          $this->client->setDefaultOption('verify', FALSE);
      }

      return $this->client;
  }

  /**
   * Run a shell command in the current directory.
   *
   * @param string $cmd    The command.
   *
   * @throws \Exception
   *
   * @return string The command output.
   */
  protected function ShellDo($cmd) {

    $process = new Process($cmd);
    $process->run();

    // executes after the command finishes
    if (!$process->isSuccessful()) {
      throw new \RuntimeException($process->getErrorOutput());
    }

    echo $process->getOutput();
  }

  /**
   * Destructor: Write the configuration to disk.
   */
  public function __destruct()
  {
      if (is_array($this->config)) {

          $configPath = $this->getHomeDirectory() . '/.gerrittools';
          $dumper = new Dumper();
          file_put_contents($configPath, $dumper->dump($this->config));
      }
  }

  /**
   * @return string The absolute path to the user's home directory.
   */
  public function getHomeDirectory()
  {
      $home = getenv('HOME');
      if (empty($home)) {
          // Windows compatibility.
          if (!empty($_SERVER['HOMEDRIVE']) && !empty($_SERVER['HOMEPATH'])) {
              $home = $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
          }
      }

      return $home;
  }

  /**
   * To prevent against Cross Site Script Inclusion (XSSI) attacks, the JSON
   * response body starts with a magic prefix line that must be stripped before
   * feeding the rest of the response body to a JSON parser
   *
   * @param $malformattedJson
   *
   * @return string
   */
  public function fixGerritJson($malformattedJson) {
    if (substr($malformattedJson, 0, 4) === ")]}'") {
      return substr((string) $malformattedJson, 4);
    }
    return $malformattedJson;
  }


  public function getOpenChangeId($input, $output) {
    $client = $this->getClient();
    $baseUrl = $client->getBaseUrl();
    $response = $client->get($baseUrl . '/changes/');
    $body_troncated = $this->fixGerritJson((string) $response->getBody());
    $changes = json_decode($body_troncated);
    foreach ($changes as $change) {
      $available_change[$change->{'_number'}] = $change->subject;
    }
    $questionText = "Choose the change:";
    $helper = $this->getHelper('question');
    $question = new ChoiceQuestion($questionText, $available_change);
    // Fix for now to get the right key
    $changeId = array_search(
      $helper->ask($input, $output, $question),
      $available_change
    );

    return $changeId;
  }

}
