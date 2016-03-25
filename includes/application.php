<?php
$template->addResource(joinPath($config['site_url'], 'bower_components/jquery-ui/ui/minified/jquery-ui.min.js'), 'js', true);
$template->addResource(joinPath($config['site_url'], 'bower_components/jquery-ui/themes/base/minified/jquery-ui.min.css'), 'css', true);

$html = new HTML;
$all_cities = $sql->getById("SELECT id,name FROM City WHERE type='actual' ORDER BY name");
$all_cities[0] = 'Any';
$all_centers = $sql->getById("SELECT id,name,city_id FROM Center WHERE status='1'");
