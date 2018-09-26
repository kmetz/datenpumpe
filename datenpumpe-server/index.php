<?php

namespace Datenpumpe\Server;

/**
 * Generates data views for the Datenpumpe installation.
 */


deliver_random_query_result();

/**
 * Main function, prints HTML.
 */
function deliver_random_query_result() {
  $queries = $queryData = [];
  include('./queries.php');

  if (isset($_GET['list'])) {
    print '<h2>Datenpumpen-Inhalte (' . count($queries) . ')</h2>';
    print render_query_list($queries);
    exit;
  }
  elseif (isset($_GET['id']) && is_numeric($_GET['id'])) {
    if (isset($queries[$_GET['id']])) {
      $queryData = $queries[$_GET['id']];
    }
    else {
      header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
      exit;
    }
  }
  else {
    $queryData = $queries[array_rand($queries)];
  }

  $html = '';
  switch ($queryData['type']) {
    case 'singleValue':
    case 'table':
    case 'images':
    case 'map':
      $html = render_content($queryData);
      break;

    case 'embed':
      $html = render_embed($queryData);
      break;
  }

  if (isset($_GET['show_query'])) {
    $html = '<pre>' . $queryData['query'] . '</pre>' . $html;
  }

  // Deliver
  print $html;
  exit;
}


function render_query_list($queries) {
  $html = '<ul>';
  foreach ($queries as $id => $data) {
    $html .= '<li><a href="?id=' . $id . '">' . $data['title'] . '</a>' . ' (' . $data['type'] . ')</li>';
  }
  $html .= '</ul>';

  return $html;
}


function render_content($queryData) {
  $endpointUrl = 'https://query.wikidata.org/sparql';
  $resultJson = file_get_contents($endpointUrl . '?format=json&query=' . urlencode($queryData['query']));
  $result = json_decode($resultJson);

  #print '<pre>' . $resultJson . '</pre>';
  #print '<pre>' . print_r($result, TRUE) . '</pre>';

  $html = '<!DOCTYPE html><html lang="en" dir="ltr">';
  $html .= '<head><link rel="stylesheet" href="data-style.css"></head>';
  $html .= '<body>';
  if (isset($queryData['title'])) {
    $html .= '<h1>' . $queryData['title'] . '</h1>';
  }
  $html .= '<div id="content-wrapper">';

  if (!empty($result->results)) {
    $render_function = 'Datenpumpe\Server\render_content_' . $queryData['type'];
    $html .= call_user_func($render_function, $queryData, $result);
  }

  $html .= '</div>'; // #content-wrapper
  $html .= '<img id="logo" src="res/842px-Wikidata_Stamp_Rec_Light.svg.png">';
  $html .= '</body></html>';

  return $html;
}


function render_content_singleValue($queryData, $result) {
  $row = $col = 0;
  $key = $result->head->vars[$col];
  $value = $result->results->bindings[$row]->{$key};
  $html = '<div class="single-value">' . format_value($value) . '</div>';

  return $html;
}


function render_content_table($queryData, $result) {
  $html = '<table>';
  $html .= '<thead><tr>';
  foreach ($result->head->vars as $key) {
    $html .= '<th>' . $key . '</th>';
  }
  $html .= '</tr></thead>';
  $html .= '<tbody>';
  foreach ($result->results->bindings as $row) {
    $html .= '<tr>';
    foreach ($result->head->vars as $key) {
      $html .= '<td>' . format_value($row->{$key}) . '</td>';
    }
    $html .= '</tr>';
  }
  $html .= '</tbody>';
  $html .= '</table>';

  return $html;
}


function render_content_images($queryData, $result) {
  $html = '<div class="images">';
  foreach ($result->results->bindings as $row) {
    $html .= '<div class="image">';
    if (isset($row->image) && $row->image->type === 'uri') {
      $html .= '<img src="' . $row->image->value . '?width=150">';
    }
    foreach ($result->head->vars as $key) {
      if ($key === 'image') {
        continue;
      }
      $html .= '<p>' . format_value($row->{$key}) . '</p>';
    }
    $html .= '</div>'; // .image
  }
  $html .= '</div>'; // .images

  return $html;
}


function render_content_map($queryData, $result) {

  $geodataKey = FALSE;
  $layers = [];
  foreach ($result->results->bindings[0] as $key => $value) {
    if (isset($value->datatype) && $value->datatype === 'http://www.opengis.net/ont/geosparql#wktLiteral') {
      $geodataKey = $key;
      break;
    }
  }
  if ($geodataKey) {
    foreach ($result->results->bindings as $row) {
      $layer = !empty($row->layer->value) ? $row->layer->value : '0';
      $layers[$layer][] = $row->{$geodataKey}->value;
    }
  }
  $layerNamesJson = json_encode(array_keys($layers));
  $layersJson = json_encode(array_values($layers));

  $html = '<link rel="stylesheet" href="vendor/leaflet.css"/>';
  $html .= '<script src="vendor/leaflet.js" type="text/javascript"></script>';
  $html .= '<script src="vendor/wicket.js" type="text/javascript"></script>';
  $html .= '<script src="vendor/wicket-leaflet.js" type="text/javascript"></script>';
  $html .= '<div id="map"></div>';

  $html .= <<<EOS
<script type="text/javascript">
let map = L.map('map', {
    preferCanvas: true
});
map.dragging.disable();
map.touchZoom.disable();
map.doubleClickZoom.disable();
map.scrollWheelZoom.disable();
map.boxZoom.disable();
map.keyboard.disable();
if (map.tap) map.tap.disable();

let tileLayer = L.tileLayer('https://maps.wikimedia.org/osm-intl/{z}/{x}/{y}.png', {
    id: 'base',
}).addTo(map);

let layerColors = [
  '#009ee0',
  '#e2007a',
  '#97b314',
  '#f29400',
  '#622181',
  
  '#e04700',
  '#79e200',
  '#b31496',
  '#3400f2',
  '#817121',
];

let wktLayers = $layersJson;
let layerNames = $layerNamesJson;

let markerGroup = new L.FeatureGroup();

let wicket = new Wkt.Wkt();
let geojson = {};
for (let layer in wktLayers) {
  let color = layer < layerColors.length ? layerColors[layer] : layerColors[layerColors.length];
  for (let item of wktLayers[layer]) {
    wicket.read(item);
    geojson = wicket.toJson();
    let feature = {};
    if (geojson.type === 'Point') {
      feature = L.circleMarker([geojson.coordinates[1], geojson.coordinates[0]], {
        color: color,
        radius: 5
      });
    }
    else {
      feature = wicket.toObject({});  
    }
    markerGroup.addLayer(feature);
  }
}
markerGroup.addTo(map);
map.fitBounds(markerGroup.getBounds());

if (layerNames.length > 1) {
  let legend = L.control({position: 'bottomright'});
  legend.onAdd = () => {
    let div = L.DomUtil.create('div', 'info legend');
    for (let layerId in layerNames) {
      div.innerHTML +=
        '<div class="legend-color" style="background:' + layerColors[layerId] + '"></div> ' +
         layerNames[layerId] + '<br>';
    }
    return div;
  };
  legend.addTo(map);
}

</script>
EOS;
  return $html;
}


function format_value($value) {
  if ($value->type === 'literal') {
    switch ($value->datatype) {
      case 'http://www.w3.org/2001/XMLSchema#integer':
        return '<span class="number">'
          . number_format(intval($value->value), 0, ',', strlen($value->value) > 4 ? '.' : '')
          . '</span>';

      case 'http://www.w3.org/2001/XMLSchema#decimal':
        return '<span class="number">'
          . number_format(floatval($value->value), 0, ',', '.')
          . '</span>';

      case 'http://www.w3.org/2001/XMLSchema#double':
        return '<span class="number">'
          . number_format(floatval($value->value), 4, ',', '.')
          . '</span>';
    }
  }
  if ($value->type === 'uri') {
    if (strpos($value->value, 'http://www.wikidata.org/entity/') === 0) {
      return '<span class="entity">' . end(explode('/', $value->value)) . '</span>';
    }

  }

  return $value->value;
}


/**
 * Loads a query on query.wikidata.org/embed, modifies and returns the result.
 * Quick&dirty way to use rendered output from query.wikidata.org.
 *
 * @param array $queryData
 * @return string Resulting HTML.
 */
function render_embed($queryData) {
  // Alternative iframe method:
  // $html = '<iframe src="'.$queryURL.'" width="1024" height="876"></iframe>';

  $wikidataURL = 'https://query.wikidata.org';
  $queryURL = $wikidataURL . '/embed.html';

  // Fetch embed html
  $html = file_get_contents($queryURL);
  // replace ressource URLs (make them absolute)
  $html = preg_replace('/(href|src)="\/?((?!http).*?)"/', '${1}=' . $wikidataURL . '/${2}', $html);
  // Put SPARQL query into URL hash for javascript to render correctly
  $headAppend = '<script type="text/javascript">window.location.hash = "#' . rawurlencode(trim($queryData['query'])) . '";</script>';
  // Add our stylesheet
  $headAppend .= '<link rel="stylesheet" href="data-style.css">';
  $html = str_replace('</head>', $headAppend . '</head>', $html);
  // Add title
  $bodyPrepend = '<h1><span>' . $queryData['title'] . '</span></h1>';
  $html = str_replace('<body>', '<body>' . $bodyPrepend, $html);
  // Add Logo
  $bodyAppend = '<img id="logo" src="res/842px-Wikidata_Stamp_Rec_Light.svg.png">';
    // Absolute URL
  $bodyAppend .= '<script type="text/javascript">CONFIG.api.sparql.uri = "https://query.wikidata.org/sparql"</script>';
  $html = str_replace('</body>', $bodyAppend . '</body>', $html);

  return $html;
}
