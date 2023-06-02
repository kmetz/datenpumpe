<?php

namespace Datenpumpe\Server;

/**
 * Type can be:
 * - singleValue
 * - table
 * - map
 * - images
 * - embed
 */


$queries = [

  [
    'type' => 'table',
    'title' => 'How many popes had how many children?',
    'query' => <<<SPARQL
SELECT ?Papst (YEAR(?date) AS ?geboren) (COUNT(?child) as ?Kinder) WHERE {
  ?child wdt:P22 ?father.
  ?father wdt:P31 wd:Q5;
  wdt:P39 wd:Q19546;
  wdt:P569 ?date.
  OPTIONAL { ?father wdt:P18 ?picture. }
  ?father rdfs:label ?Papst.
  FILTER(LANG(?Papst) = 'en').
}
GROUP BY ?Papst ?date
ORDER BY DESC(?Kinder) ?Papst
SPARQL
  ],


  [
    'type' => 'images',
    'title' => 'Which national flags show stars?',
    'query' => <<<SPARQL
SELECT DISTINCT ?flag ?flagLabel (MIN(?images) AS ?image) WHERE {
  ?flag wdt:P31/wdt:P279* wd:Q186516;
  wdt:P180 ?depicted.
  ?flag wdt:P18 ?images.
  VALUES ?astronomical { wd:Q8928 wd:Q523 } ?depicted wdt:P31?/wdt:P279* ?astronomical.
  SERVICE wikibase:label {
    bd:serviceParam wikibase:language "en".
    ?flag rdfs:label ?flagLabel.
    ?depicted rdfs:label ?depictedLabel.
  }
}
GROUP BY ?flag ?flagLabel
ORDER BY MD5(CONCAT(STR(NOW()), STR(?flag)))
SPARQL
  ],


  [
    'type' => 'map',
    'title' => 'Only 25 international airports are named after women',
    'query' => <<<SPARQL
SELECT ?airport ?airportLabel ?location ?person ?personLabel (?gender AS ?layer) WHERE {
  ?airport wdt:P31/wdt:P279* wd:Q1248784;
            wdt:P625 ?location;
            wdt:P138 ?person.
  ?person wdt:P31 wd:Q5.
  OPTIONAL {
    ?person wdt:P21/wdt:P279* wd:Q6581072.
    BIND("female"@en AS ?female)
  }
  OPTIONAL {
    ?person wdt:P21/wdt:P279* wd:Q6581097.
    BIND("male"@en AS ?male)
  }
  OPTIONAL {
    ?person wdt:P21 ?gender.
    MINUS { ?gender wdt:P279* wd:Q6581097. }
    MINUS { ?gender wdt:P279* wd:Q6581072. }
    FILTER(!ISBLANK(?gender))
    BIND("other"@en AS ?other)
  }
  BIND(COALESCE(?female, ?male, ?other, "unknown"@en) AS ?gender)
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
}
SPARQL
  ],


  [
    'type' => 'map',
    'title' => 'Higher education institutions named after women',
    'query' => <<<SPARQL
SELECT ?airport ?airportLabel ?location ?person ?personLabel (?gender AS ?layer) WHERE {
  ?airport wdt:P31/wdt:P279* wd:Q38723;
            wdt:P625 ?location;
            wdt:P138 ?person.
  ?person wdt:P31 wd:Q5.
  OPTIONAL {
    ?person wdt:P21/wdt:P279* wd:Q6581097.
    BIND("male"@en AS ?male)
  }
  OPTIONAL {
    ?person wdt:P21/wdt:P279* wd:Q6581072.
    BIND("female"@en AS ?female)
  }
  OPTIONAL {
    ?person wdt:P21 ?gender.
      MINUS { ?gender wdt:P279* wd:Q6581097. }
    MINUS { ?gender wdt:P279* wd:Q6581072. }
    FILTER(!ISBLANK(?gender))
    BIND("other"@en AS ?other)
  }
  BIND(COALESCE(?female, ?male, ?other, "unknown"@en) AS ?gender)
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
}
SPARQL
  ],


  [
    'type' => 'embed',
    'title' => 'Most inherited professions',
    'query' => <<<SPARQL
#defaultView:BubbleChart
SELECT ?occupation ?occupationLabel ?count WITH {
  SELECT ?occupation (COUNT(DISTINCT ?person1) AS ?count) WHERE {
    wd:Q5 ^wdt:P31 ?person1, ?person2, ?person3, ?person4, ?person5.
    ?person1 wdt:P22|wdt:P25 ?person2.
    ?person2 wdt:P22|wdt:P25 ?person3.
    ?person3 wdt:P22|wdt:P25 ?person4.
    ?person4 wdt:P22|wdt:P25 ?person5.
    
    ?occupation ^wdt:P106 ?person1, ?person2, ?person3, ?person4, ?person5.
  }
  GROUP BY ?occupation ?occupationLabel
} AS %results WHERE {
  INCLUDE %results  .
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
}
SPARQL
  ],


  [
    'type' => 'images',
    'title' => 'Selection of early women’s art',
    'query' => <<<SPARQL
#defaultView:ImageGrid
SELECT ?image ?artistLabel ?artLabel ?date WHERE {
  {
    SELECT (SAMPLE(?image) AS ?image) ?artist ?art (YEAR(MIN(?date)) AS ?date) WHERE {
    ?art wdt:P170 ?artist;
           wdt:P18 ?image;
           wdt:P571 ?date.
      ?artist wdt:P31 wd:Q5.
      { ?artist wdt:P21 wd:Q6581072. } UNION { ?artist wdt:P21 wd:Q1052281. } # using ?artist wdt:P21/wdt:P279? wd:Q6581072 instead would be nice, but is unfortunately too inefficient
    }
    GROUP BY ?artist ?art
    ORDER BY ASC(?date)
    LIMIT 20
  }
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
}
ORDER BY MD5(CONCAT(STR(NOW()), STR(?art))) # simulate random order – ORDER BY RAND() doesn’t have the desired effect
LIMIT 20
SPARQL
  ],


  [
    'type' => 'map',
    'title' => 'Auf "ing" endende Ortschaften in Deutschland',
    'query' => <<<SPARQL
#defaultView:Map
SELECT ?item ?name ?itemCoords WHERE {
  ?item wdt:P31 wd:Q262166;
        rdfs:label ?name.
  FILTER(LANG(?name) = "de")
  FILTER(STRENDS(?name, "ing"@de))
  ?item wdt:P625 ?itemCoords.
}
SPARQL
  ],


  [
    'type' => 'map',
    'title' => 'Auf "stedt" endende Ortschaften in Deutschland',
    'query' => <<<SPARQL
#defaultView:Map
SELECT ?item ?name ?itemCoords WHERE {
  ?item wdt:P31 wd:Q262166;
        rdfs:label ?name.
  FILTER(LANG(?name) = "de")
  FILTER(STRENDS(?name, "stedt"@de))
  ?item wdt:P625 ?itemCoords.
}
SPARQL
  ],


  [
    'type' => 'map',
    'title' => 'Mit "a" endende Ortschaften in Deutschland',
    'query' => <<<SPARQL
#defaultView:Map
SELECT ?item ?name ?itemCoords WHERE {
  ?item wdt:P31 wd:Q262166;
        rdfs:label ?name.
  FILTER(LANG(?name) = "de")
  FILTER(STRENDS(?name, "a"@de))
  ?item wdt:P625 ?itemCoords.
}
SPARQL
  ],


  [
    'type' => 'singleValue',
    'title' => 'How old are members of the 20th Bundestag on average?',
    'query' => <<<SPARQL
SELECT (AVG(?age) AS ?avgAge) WHERE {
?mdb wdt:P31 wd:Q5;
     p:P39 [
ps:P39 wd:Q1939555;
       pq:P2937 wd:Q33091469
     ];
     wdt:P569 ?dob.
  BIND(FLOOR((NOW() - ?dob)/365.2425) AS ?age)
}
SPARQL
  ],


  [
    'type' => 'map',
    'title' => 'Language distribution in Switzerland',
    'query' => <<<SPARQL
#defaultView:Map
  SELECT ?item (SAMPLE(?title) AS ?itemLabel) (SAMPLE(?location) AS ?location) (SAMPLE(?language) AS ?layer)
WITH {
  SELECT * WHERE {
    wd:Q39 p:P1332/psv:P1332/wikibase:geoLatitude ?n;
           p:P1333/psv:P1333/wikibase:geoLatitude ?s;
           p:P1334/psv:P1334/wikibase:geoLongitude ?e;
           p:P1335/psv:P1335/wikibase:geoLongitude ?w.
  }
} AS %switzerlandBoundingBox
WITH {
  SELECT ?item ?location WHERE {
    ?item wdt:P17 wd:Q39;
          wdt:P625 ?location.
      # filter out some stray results that have country Switzerland but coordinates outside it (e. g. rivers)
      INCLUDE %switzerlandBoundingBox.
      BIND(geof:latitude(?location) AS ?lat)
    BIND(geof:longitude(?location) AS ?lon)
    FILTER(?s <= ?lat && ?lat <= ?n &&
           ?w <= ?lon && ?lon <= ?e)
  }
} AS %swissItems
WHERE {
  INCLUDE %swissItems.
  VALUES ?wiki { <https://de.wikipedia.org/> <https://fr.wikipedia.org/> <https://it.wikipedia.org/> <https://rm.wikipedia.org/> }
    ?article a schema:Article;
           schema:about ?item;
           schema:isPartOf ?wiki;
           schema:inLanguage ?language;
           schema:name ?title.
}
GROUP BY ?item
    HAVING(COUNT(DISTINCT ?wiki) = 1)
SPARQL
  ],


  [
    'type' => 'embed',
    'title' => 'Number of women and Johns in British parliaments',
    'query' => <<<SPARQL
# UK parliaments with count of Johns and count of women
#defaultView:LineChart
SELECT ?date ?count ?label WITH {
  # all MPs with their associated parliament
  SELECT DISTINCT ?mp ?parliament WHERE {
    ?mp wdt:P31 wd:Q5.
    {
      # new data model: position held – Member of the nth Parliament of the United Kingdom
      ?mp p:P39/ps:P39 ?position.
      ?position wdt:P279 wd:Q16707842;
                p:P279/pq:P2937|wdt:P2937 ?parliament.
    } UNION {
      # old data model: member of – nth Parliament of the United Kingdom
      ?mp p:P463/ps:P463 ?parliament.
      ?parliament wdt:P31 wd:Q21094819.
    }
  }
} AS %MPsWithParliament WITH {
  # count of Johns in each parliament
  SELECT ?parliament (COUNT(DISTINCT ?mp) AS ?johns) WHERE {
    INCLUDE %MPsWithParliament.
    ?mp wdt:P735 wd:Q4925477.
  }
  GROUP BY ?parliament
} AS %johns WITH {
  # count of women in each parliament
  SELECT ?parliament (COUNT(DISTINCT ?mp) AS ?women) WHERE {
    INCLUDE %MPsWithParliament.
    ?mp wdt:P21/wdt:P279* wd:Q6581072. # (P279*: include transgender women – none yet, but will probably happen in the future)
  }
  GROUP BY ?parliament
} AS %women WHERE {
  # get ?johns and ?women
  INCLUDE %johns.
  INCLUDE %women.
  # fan out single result
  # ?parliament ?johns ?women
  # into two results
  # ?parliament "Johns" ?johns
  # ?parliament "women" ?women
  # so the line chart works
  VALUES ?label { "Johns"@en "women"@en }
  BIND(IF(?label = "Johns"@en, ?johns, ?women) AS ?count)
  # get parliament date for the chart
  ?parliament wdt:P571|wdt:P580 ?date
}
SPARQL
  ],


  [
    'type' => 'table',
    'title' => 'Ratio of female characters per fictional universe',
    'query' => <<<SPARQL
# ratio of female characters per fictional universe
# (assuming that any item with “from fictional universe” and “sex or gender” is a fictional character)
SELECT ?universeLabel (?percentString AS ?Anteil) (?females AS ?Frauen) (?total as ?Alle) WHERE { # add ?percent if you want to sort in the table
  {
    SELECT ?universe (SUM(?female) AS ?females) (COUNT(*) AS ?total) WHERE {
    ?character wdt:P1080 ?universe;
                 wdt:P21 ?gender.
      BIND(IF(?gender IN (wd:Q6581072, wd:Q1052281), 1, 0) AS ?female)
    }
    GROUP BY ?universe
  }
  BIND(?females/?total AS ?ratio)
  BIND(100*?ratio AS ?percent)
  BIND(CONCAT(SUBSTR(STR(?percent), 1, 5), "%") AS ?percentString)
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
}
ORDER BY DESC(?total)
LIMIT 20
SPARQL
  ],


  [
    'type' => 'table',
    'title' => 'Data about famous cats',
    'query' => <<<SPARQL
SELECT ?cat ?catLabel ?catDescription (YEAR(?date) as ?born) (GROUP_CONCAT(?ownerLabel; SEPARATOR=", ") AS ?owner)
WHERE {
  ?cat wdt:P31 wd:Q146 .
  ?cat wdt:P569 ?date .
  ?cat wdt:P127 ?ownerItem.
  ?ownerItem rdfs:label ?ownerLabel.
  FILTER(LANG(?ownerLabel) = 'en').
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en" }
}
GROUP BY ?cat ?catLabel ?catDescription ?date
ORDER BY MD5(CONCAT(STR(NOW()), STR(?item)))
LIMIT 13
SPARQL
  ],


  [
    'type' => 'table',
    'title' => 'Which big cities are located lowest on Earth?',
    'query' => <<<SPARQL
SELECT ?cityLabel ?countryLabel ?altitude ?population WHERE
{
  ?city wdt:P31/wdt:P279* wd:Q1549591 ;
    wdt:P2044 ?altitude ;
    wdt:P17 ?country ;
    wdt:P1082 ?population .
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en" }
}
ORDER BY ?altitude ?cityLabel
LIMIT 19
SPARQL
  ],


  [
    'type' => 'table',
    'title' => 'Which big cities are located highest on Earth?',
    'query' => <<<SPARQL
SELECT ?cityLabel ?countryLabel ?altitude ?population WHERE
{
  ?city wdt:P31/wdt:P279* wd:Q1549591 ;
    wdt:P2044 ?altitude ;
    wdt:P17 ?country ;
    wdt:P1082 ?population .
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en" }
}
ORDER BY DESC(?altitude) ?cityLabel
LIMIT 19
SPARQL
  ],


  [
    'type' => 'map',
    'title' => 'Coal power plants in Germany',
    'query' => <<<SPARQL
#defaultView:Map
SELECT DISTINCT ?item ?itemLabel ?image ?coor
  WHERE {
  ?item wdt:P31/wdt:P279* wd:Q6558431;
      ?range wd:Q183;
      wdt:P625 ?coor.
  FILTER NOT EXISTS { ?item wdt:P576 ?closed. }
  FILTER NOT EXISTS { ?item wdt:P582 ?enddate. }
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
}
SPARQL
  ],


  [
    'type' => 'map',
    'title' => 'Offshore wind farms in Europe',
    'query' => <<<SPARQL
#defaultView:Map
SELECT DISTINCT ?item ?coor
  WHERE {
  ?item wdt:P31/wdt:P279* wd:Q1357601 .
  wd:Q46 wdt:P625 ?europeLoc .
  SERVICE wikibase:around {
    ?item wdt:P625 ?coor .
    ?place wdt:P625 ?location .
    bd:serviceParam wikibase:center ?europeLoc .
    bd:serviceParam wikibase:radius "4000" .
  } .
}
SPARQL
  ],


  /**
  [
    'type' => 'embed',
    'title' => 'Gestation periods of mammals',
    'query' => <<<SPARQL
# Average gestation period of genera, color-coded by order
#defaultView:BubbleChart
    SELECT ?genus (AVG(?period) AS ?period) (SAMPLE(?label) AS ?genusLabel) (SAMPLE(?rgb) AS ?rgb)
WHERE
{
  {
    BIND("en" AS ?language)
    # find species with gestation period normalized to seconds
    ?species p:P3063/psn:P3063/wikibase:quantityAmount ?periodSeconds.
  # convert to days
  BIND(?periodSeconds/(60*60*24) AS ?period)
    # find genus
    ?species wdt:P171* ?genus.
    ?genus wdt:P105 wd:Q34740.
  # find a good label – trivial name, else label, else “<no name>”
  OPTIONAL {
    ?genus wdt:P1843 ?trivialName.
    FILTER(LANG(?trivialName) = ?language)
    }
    OPTIONAL {
    ?genus rdfs:label ?genusLabel.
    FILTER(LANG(?genusLabel) = ?language)
    }
    BIND(COALESCE(?trivialName, ?genusLabel, "<no name>"@en) AS ?label)
    # find order
    ?genus wdt:P171* ?order.
    ?order wdt:P105 wd:Q36602.
  # choose “random but deterministic” color per order; you can play around with "-" (can be any string) to find a pleasing resulting color distribution
  BIND(UCASE(SUBSTR(SHA256(CONCAT("-", STR(?order))), 1, 6)) AS ?rgb)
  }
  UNION
  {
    # add scale
    VALUES (?genus ?period ?label) {
    (wd:Q23387 7 "week"@en)
      (wd:Q5151 30.436875 "month"@en)
      (wd:Q1643308 91.310625 "3 months"@en)
      (wd:Q2269240 182.62125 "6 months"@en)
      (wd:Q577 365.2425 "year"@en)
    }
    BIND("CCCCCC" AS ?rgb)
  }
}
GROUP BY ?genus
SPARQL
  ],
   **/

  [
    'type' => 'embed',
    'title' => 'Genghis Khan\'s children',
    'query' => <<<SPARQL
#Children of Genghis Khan
#defaultView:Graph
  PREFIX gas: <http://www.bigdata.com/rdf/gas#>

SELECT ?item ?itemLabel ?pic ?linkTo
  WHERE {
  SERVICE gas:service {
    gas:program gas:gasClass "com.bigdata.rdf.graph.analytics.SSSP" ;
                gas:in wd:Q720 ;
                gas:traversalDirection "Forward" ;
                gas:out ?item ;
                gas:out1 ?depth ;

                gas:maxIterations 3 ;
                gas:linkType wdt:P40 .
  }
  OPTIONAL { ?item wdt:P40 ?linkTo }
  OPTIONAL { ?item wdt:P18 ?pic }
  SERVICE wikibase:label {bd:serviceParam wikibase:language "en" }
}
SPARQL
  ],


  [
    'type' => 'images',
    'title' => 'Named after Nelson Mandela',
    'query' => <<<SPARQL
SELECT ?label ?image ?loc WHERE {
  ?item wdt:P138 wd:Q8023.
  ?item wdt:P18 ?image.
  ?item wdt:P131 ?location.
  SERVICE wikibase:label {
    bd:serviceParam wikibase:language "en" . 
    ?item rdfs:label ?label.
    ?location rdfs:label ?loc.
  }
}
ORDER BY MD5(CONCAT(STR(NOW()), STR(?item)))
SPARQL
  ],


  [
    'type' => 'map',
    'title' => 'Where were astronauts born?',
    'query' => <<<SPARQL
SELECT ?astronaut ?coord (?genderlabel AS ?layer) WHERE {
  ?astronaut ?x1 wd:Q11631;
             wdt:P31 wd:Q5;
             wdt:P21 ?gender;
  wdt:P19 ?birthplace.
  ?birthplace wdt:P625 ?coord.
  SERVICE wikibase:label {
    bd:serviceParam wikibase:language "en" . 
    ?gender rdfs:label ?genderlabel.
  }
}
ORDER BY DESC(?gender)
SPARQL
  ],


  [
    'type' => 'map',
    'title' => 'U-Bahn stations in Berlin',
    'query' => <<<SPARQL
  SELECT ?coord (?label AS ?layer)
WHERE {
  ?station wdt:P31/wdt:P279* wd:Q928830;
          wdt:P131 wd:Q64;
          wdt:P81 ?line;
          wdt:P625 ?coord.
  SERVICE wikibase:label {
    bd:serviceParam wikibase:language "en,de" . 
    ?line rdfs:label ?label.
  }
}
ORDER BY ?label
SPARQL
  ],


  [
    'type' => 'embed',
    'title' => 'Number of train lines',
    'query' => <<<SPARQL
#defaultView:BubbleChart
SELECT ?country ?countryLabel (COUNT(?station) AS ?stations)
WHERE {
  ?station wdt:P31/wdt:P279* wd:Q15141321;
             wdt:P17 ?country;
    SERVICE wikibase:label { bd:serviceParam wikibase:language "en" }
}
GROUP BY ?country ?countryLabel
SPARQL
  ],


  [
    'type' => 'singleValue',
    'title' => 'Number of species in Wikidata',
    'query' => <<<SPARQL
SELECT (COUNT(?item) as ?count)
WHERE {
  ?item wdt:P105 wd:Q7432.
}
SPARQL
  ],


  [
    'type' => 'singleValue',
    'title' => 'Number of people in Wikidata',
    'query' => <<<SPARQL
SELECT (COUNT(?item) as ?count)
WHERE {
  ?item wdt:P31 wd:Q5.
}
SPARQL
  ],


  [
    'type' => 'images',
    'title' => 'named after Nelson Mandela',
    'query' => <<<SPARQL
SELECT ?label ?image ?loc WHERE {
  ?item wdt:P138 wd:Q8023.
  ?item wdt:P18 ?image.
  ?item wdt:P131 ?location.
  SERVICE wikibase:label {
    bd:serviceParam wikibase:language "en" . 
    ?item rdfs:label ?label.
    ?location rdfs:label ?loc.
  }
}
ORDER BY MD5(CONCAT(STR(NOW()), STR(?item)))
SPARQL
  ],


  [
    'type' => 'embed',
    'title' => 'origin of Nobel Prize winners',
    'query' => <<<SPARQL
#defaultView:BubbleChart
SELECT ?CountryLabel (COUNT(?Person) AS ?Preisträger)
WHERE
{
  ?NobelPrize wdt:P279?/wdt:P31? wd:Q7191 .
  ?Person wdt:P166? ?NobelPrize ;
          wdt:P19 ?Place .
  ?Place wdt:P17 ?Country .
  SERVICE wikibase:label {
    bd:serviceParam wikibase:language "en" .
  }
}
GROUP BY ?CountryLabel
ORDER BY DESC(?Preisträger)
SPARQL
  ],


  [
    'type' => 'images',
    'title' => 'paintings in the Louvre',
    'query' => <<<SPARQL
SELECT ?label ?image WHERE {
  ?item wdt:P195 wd:Q3044768 .
  ?item wdt:P18 ?image .
  SERVICE wikibase:label {
    bd:serviceParam wikibase:language "en" . 
    ?item rdfs:label ?label .
  }
}
ORDER BY MD5(CONCAT(STR(NOW()), STR(?label))) # simulate random order
SPARQL
  ],


  [
    'type' => 'embed',
    'title' => 'Timeline of space probes',
    'query' => <<<SPARQL
#defaultView:Timeline
  SELECT ?image ?launchdate ?itemLabel
    WHERE
{
  ?item wdt:P31 wd:Q26529 .
   ?item wdt:P619 ?launchdate .
  OPTIONAL { ?item wdt:P18 ?image }
   SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
}
SPARQL
  ],


  [
    'type' => 'map',
    'title' => 'Electrical socket types around the world',
    'query' => <<<SPARQL
SELECT ?geoshape ?coord (?plugLabel AS ?layer) WHERE {
  ?country wdt:P2853 ?plug ;
           wdt:P3896 ?geoshape ;
           wdt:P625 ?coord .
  SERVICE wikibase:label {
    bd:serviceParam wikibase:language "en" .
    ?plug rdfs:label ?plugLabel.
  }
}
ORDER BY ?plugLabel
SPARQL
  ],


  [
    'type' => 'embed',
    'title' => 'Duration of films over time',
    'query' => <<<SPARQL
#defaultView:LineChart
SELECT ?year (AVG(?durationInMinutes) AS ?avgDurationInMinutes) ?genreLabel WHERE {
  {
    SELECT ?genre WHERE {
      ?film wdt:P31/wdt:P279* wd:Q11424;
            wdt:P136 ?genre.
    }
    GROUP BY ?genre
    ORDER BY DESC(COUNT(DISTINCT ?film))
    LIMIT 25
  }
  ?film wdt:P31/wdt:P279* wd:Q11424;
        wdt:P577 ?date;
        p:P2047/psn:P2047/wikibase:quantityAmount ?durationInSeconds;
        wdt:P136 ?genre.
  FILTER(?durationInMinutes < 60*24) # I take the editorial liberty to exclude these films (some stupidly long documentaries), because they just absurdly skew the averages
  BIND(STR(YEAR(?date)) AS ?year)
  BIND(?durationInSeconds/60 AS ?durationInMinutes)
  SERVICE wikibase:label { bd:serviceParam wikibase:language "en". }
}
GROUP BY ?year ?genreLabel
HAVING(COUNT(DISTINCT ?film) >= 10)

SPARQL
  ],


  [
    'type' => 'map',
    'title' => 'Earthquakes and nuclear power stations',
    'query' => <<<SPARQL
SELECT ?event ?eventLabel ?coordinates ?layer WHERE {
  {
    ?event wdt:P31/wdt:P279* wd:Q7944;
                wdt:P625 ?coordinates.
    BIND("earthquake"@en AS ?layer)
  } UNION {
    ?event wdt:P31/wdt:P279* wd:Q134447;
           wdt:P625 ?coordinates.
    BIND("nuclear power station"@en AS ?layer)
  }
}
SPARQL
  ],

  [
    'type' => 'images',
    'title' => 'Welche Gemälde zeigen Regenbogen?',
    'query' => <<<SPARQL
SELECT ?painting ?paintingLabel ?painter ?painterLabel ?image WHERE {
  ?painting wdt:P180 wd:Q1052;
            wdt:P170 ?painter;
            wdt:P18 ?image.
  SERVICE wikibase:label { bd:serviceParam wikibase:language "[AUTO_LANGUAGE],en". }
}
ORDER BY MD5(CONCAT(STR(NOW()), STR(?flag)))
SPARQL
  ],

];
