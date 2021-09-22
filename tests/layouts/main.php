<?php
/* @var $this \piko\View */
/* @var $content string */

use piko\Piko;

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->escape($this->title) ?></title>
    <?= $this->head() ?>
  </head>
  <body>
    <?= $content ?>
    <?= $this->endBody() ?>
  </body>
</html>