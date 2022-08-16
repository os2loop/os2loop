<!doctype html>
<?php
  // phpcs:ignoreFile
  $color_names = [
    'loop-green',
    'loop-green-600',
    'loop-green-400',
    'loop-green-300',
    'loop-green-200',
    'loop-green-100',

    'tag-blue',
    'tag-purple',
    'tag-green',
    'tag-orange',

    'taxonomy-color-1',
    'taxonomy-color-2',
    'taxonomy-color-3',
    'taxonomy-color-4',

    'primary',
    'secondary',
    'success',
    'info',
    'warning',
    'danger'
  ];

  $themes = [
    'default',
    'blue',
    'green',
    'red',
    'yellow',
    'lightblue',
  ];

  $active_theme = $_GET['theme'] ?? 'default';
  if (!in_array($active_theme, $themes, true)) {
    $active_theme = $themes[0];
  }
?>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OS2Loop colors</title>
    <link rel="stylesheet" href="../build/<?php print $active_theme ?>.css" />
  </head>
  <body>
    <div class="container-fluid">
      <nav class="navbar navbar-expand-lg bg-light">
        <div class="container-fluid">
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
              <?php foreach ($themes as $theme): ?>
                <li class="nav-item">
                  <a class="nav-link <?php print $theme == $active_theme ? 'active' : '' ?>" href="?theme=<?php print $theme ?>"><?php print $theme ?></a>
                </li>
              <?php endforeach ?>
            </ul>
          </div>
        </div>
      </nav>

      <div class="os2loop-colors">
        <h1><?php print $active_theme ?></h1>
        <?php
          foreach ($color_names as $color_name) {
            printf('<div class="color-%s"></div>', $color_name);
          }
        ?>
      </div>
    </div>
  </body>
</html>
