<?php
/* @var $this \Piko\View */
/* @var $content string */

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
    <h1>Main layout override</h1>
    <?= $content ?>
    <?= $this->endBody() ?>
  </body>
</html>