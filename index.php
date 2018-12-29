<?php

/*
2018, mortzu <mortzu@gmx.de>.
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are
permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this list of
  conditions and the following disclaimer.

* Redistributions in binary form must reproduce the above copyright notice, this list
  of conditions and the following disclaimer in the documentation and/or other materials
  provided with the distribution.

* The names of its contributors may not be used to endorse or promote products derived
  from this software without specific prior written permission.

* Feel free to send Club Mate to support the work.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS
OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDERS
AND CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR
OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.
*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

require_once __DIR__ . '/config.defaults.php';
require_once __DIR__ . '/config.php';

?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="vendor/twbs/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="vendor/simonwhitaker/github-fork-ribbon-css/gh-fork-ribbon.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="//fonts.googleapis.com/css?family=Arvo" type="text/css">
<?php
foreach ($config['view_additional_css'] as $additional_css)
  echo "    <link rel=\"stylesheet\" href=\"{$additional_css}\" type=\"text/css\">\n";
?>
    <title><?php echo $config['view_title']; ?></title>
  </head>

  <body>
    <a class="github-fork-ribbon left-top" href="https://github.com/mortzu/nodewatcher" data-ribbon="Fork me on GitHub" title="Fork me on GitHub">Fork me on GitHub</a>

    <div class="container">
<?php
  echo $config['view_header_pre'];
?>
      <hgroup>
        <h1><?php echo $config['view_title']; ?></h1>
        <h2><?php echo $config['view_subtitle']; ?></h2>
<?php
  echo $config['view_header'];
?>
</hgroup>

        <p><?php echo $config['view_text']; ?></p>

<?php

if (isset($_GET['delete']) && isset($_GET['token']) && !empty($_GET['token'])) {
  foreach (glob(__DIR__ . '/data/verified/*') as $filename) {
    $json_decoded = json_decode(file_get_contents($filename), true);

    if ($_GET['token'] == $json_decoded['token']) {
      echo '<div class="alert alert-success" role="alert">' . $config['view_text_deleted'] . '</div>';
      unlink($filename);
    }
  }
} elseif (isset($_GET['token']) && !empty($_GET['token'])) {
  foreach (glob(__DIR__ . '/data/pending/*') as $filename) {
    $json_decoded = json_decode(file_get_contents($filename), true);

    if ($_GET['token'] == $json_decoded['token']) {
      echo '<div class="alert alert-success" role="alert">' . $config['view_text_confirmed'] . '</div>';
      rename($filename, __DIR__ . '/data/verified/' . basename($filename));
    }
  }
} elseif (isset($_POST['nodename']) && !empty($_POST['nodename'])) {
  $nodename = strtolower(preg_replace('/[^A-Za-z0-9-]/', '-', $_POST['nodename']));

  if (false === $nodeinfo = file_get_contents('http://' . $nodename . '.' . $config['domain_suffix'] . '/cgi-bin/status')) {
    if (false === $nodeinfo = file_get_contents('http://' . $nodename . '.' . $config['domain_suffix'] . '/cgi-bin/nodeinfo'))
      echo '<div class="alert alert-danger" role="alert">' . $config['view_text_node_noconnect'] . '</div>';
    elseif (NULL === $nodeinfo_json = json_decode($nodeinfo, true))
      echo '<div class="alert alert-danger" role="alert">' . $config['view_text_node_parse'] . '</div>';
    elseif (!isset($nodeinfo_json['owner']['contact']) || empty($nodeinfo_json['owner']['contact']))
      echo '<div class="alert alert-danger" role="alert">' . $config['view_text_node_nomail'] . '</div>';
    else
      $nodecontact = strtolower($nodeinfo_json['owner']['contact']);
  } elseif (false === preg_match('/<dt>Contact<\/dt><dd>(.*)<\/dd>/', $nodeinfo, $nodecontact_array))
      echo '<div class="alert alert-danger" role="alert">' . $config['view_text_node_nomail'] . '</div>';
  else
    $nodecontact = strtolower($nodecontact_array[1]);

  if (!empty($nodecontact)) {
    if (!filter_var($nodecontact, FILTER_VALIDATE_EMAIL))
      echo '<div class="alert alert-danger" role="alert">' . $config['view_text_node_novalidmail'] . '</div>';
    elseif (file_exists(__DIR__ . '/data/pending/' . $nodename))
      echo '<div class="alert alert-danger" role="alert">' . $config['view_text_node_already'] . '</div>';
    else {
      echo '<div class="alert alert-success" role="alert">' . $config['view_text_confirmation'] . '</div>';

      $token = md5(time());

      file_put_contents(__DIR__ . '/data/pending/' . $nodename, json_encode(array('token' => $token, 'mail' => $nodecontact)));

      $mail = new PHPMailer;
      $mail->isSendmail();
      $mail->CharSet = 'utf-8';
      $mail->setFrom($config['email_from']);
      $mail->addAddress($nodecontact);
      $mail->isHTML(false);
      $mail->Subject = $config['email_subject_confirmation'];
      $mail->Body = str_replace(array('___LINK_CONFIRM___', '___LINK_DELETE___', '___EMAIL___'), array($_SERVER['SCRIPT_URI'] . '?token=' . $token, $_SERVER['SCRIPT_URI'] . '?delete&token=' . $token, $nodecontact), $config['email_message_confirmation']);
      $mail->send();
    }
  }
} else {

?>

        <form method="post" action="<?php echo $_SERVER['SCRIPT_URI']; ?>">
          <div class="form-group row">
            <div class="col-xs-8 col-md-10">
              <input type="text" class="form-control" id="nodename" placeholder="Nodename" name="nodename">
            </div><div class="col-xs-2 col-md-2">
              <input type="submit" name="submit" value="Eintragen" class="btn btn-default">
            </div>
          </div>
        </form>

<?php } ?>

      </div>
    </div>

    <script src="vendor/components/jquery/jquery.min.js"></script>
    <script src="vendor/twbs/bootstrap/dist/js/bootstrap.min.js" crossorigin="anonymous" integrity="sha256-VsEqElsCHSGmnmHXGQzvoWjWwoznFSZc6hs7ARLRacQ="></script>

<?php
echo $config['view_footer'];
?>
  </body>
</html>
