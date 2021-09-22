<?php
/* @var $this \piko\View */
/* @var $content string */

use piko\Piko;

$app = Piko::$app;
?>
<!DOCTYPE html>
<html lang="<?= $app->language ?>">
  <head>
    <meta charset="<?= $app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->escape($this->title) ?></title>
    <?= $this->head() ?>
  </head>
  <body>
    <?= $content ?>
    <?= $this->endBody() ?>
  </body>
</html>