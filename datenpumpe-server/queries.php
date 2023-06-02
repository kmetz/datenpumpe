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
SELECT ?occupation ?occupationLabel ?count WHERE {
  VALUES (?occupation ?occupationLabel ?count) {
    (<http://www.wikidata.org/entity/Q6606110> "industrialist"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1415090> "film score composer"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q593362> "draper"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1937431> "organ builder"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1941338> "burgess"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q16387970> "Q16387970" "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q66495020> "estate owner"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q11620114> "kadōka"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q189290> "military officer"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q947305> "skald"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q81096> "engineer"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q639669> "musician"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q16533> "judge"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q11900058> "explorer"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q4122737> "Vogt"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q593644> "chemist"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q11063> "astronomer"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q2259451> "stage actor"@en "1"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q482980> "author"@en "2"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q219477> "missionary"@en "2"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q11513337> "athletics competitor"@en "2"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q955464> "parson"@en "2"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1622272> "university teacher"@en "2"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1234713> "theologian"@en "2"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q211423> "goldsmith"@en "2"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q193391> "diplomat"@en "2"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q500251> "ship-owner"@en "2"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q765778> "organist"@en "2"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1416611> "postmaster"@en "2"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q512314> "socialite"@en "2"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q372436> "statesperson"@en "2"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1463475> "mintmaster"@en "3"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1688932> "jiedushi"@en "3"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q12826225> "Lord"@en "3"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q36180> "writer"@en "3"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q715222> "lady-in-waiting"@en "3"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q2374149> "botanist"@en "3"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q618532> "landlord"@en "4"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q39631> "physician"@en "4"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q2516866> "publisher"@en "4"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q854997> "Bhikkhu"@en "4"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1402561> "military leader"@en "4"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q175151> "printer"@en "4"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q3303330> "calligrapher"@en "4"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q10732476> "art collector"@en "4"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q42973> "architect"@en "4"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1279683> "Nasi"@en "4"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q10800557> "film actor"@en "4"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q3303297> "ironmaster"@en "5"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q42603> "priest"@en "5"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q49757> "poet"@en "5"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q4964182> "philosopher"@en "6"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q102083> "knight"@en "6"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q40348> "lawyer"@en "6"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1250916> "warrior"@en "7"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1934684> "homemaker"@en "8"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q215536> "merchant"@en "8"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q40881196> "printer-bookseller"@en "10"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1483709> "land owner"@en "10"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q131524> "entrepreneur"@en "11"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q2928765> "busshi"@en "12"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1281618> "sculptor"@en "12"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q3519259> "count"@en "14"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q36834> "composer"@en "15"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q12097> "king"@en "16"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q844586> "gentry"@en "17"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q43845> "businessperson"@en "17"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q131512> "farmer"@en "20"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q48146261> "kabuki actor"@en "22"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q133485> "rabbi"@en "24"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q16744001> "noble"@en "30"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q33999> "actor"@en "30"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1028181> "painter"@en "36"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q806798> "banker"@en "46"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q47064> "military personnel"@en "48"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q38142> "samurai"@en "49"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q273108> "condottiero"@en "55"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q98103687> "Ancient Roman military personnel"@en "64"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q11545923> "military commander"@en "74"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1975935> "bushi"@en "80"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1409420> "feudatory"@en "83"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q97667506> "Ancient Roman politician"@en "103"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1097498> "ruler"@en "109"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q1062083> "billionaire"@en "131"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q2304859> "sovereign"@en "158"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q116> "monarch"@en "265"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q2478141> "aristocrat"@en "1061"^^<http://www.w3.org/2001/XMLSchema#integer>)
    (<http://www.wikidata.org/entity/Q82955> "politician"@en "2081"^^<http://www.w3.org/2001/XMLSchema#integer>)
  }
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
SELECT ?date ?count ?label WHERE {
  VALUES (?date ?count ?label) {
    ("1919-02-11T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "79"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1919-02-11T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "3"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1922-11-23T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "3"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1922-11-23T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "60"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1924-01-15T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "50"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1924-01-15T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "8"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1924-12-09T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "51"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1924-12-09T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "10"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1929-07-02T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "52"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1929-07-02T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "16"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1931-11-10T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "57"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1931-11-10T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "15"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1935-12-03T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "76"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1935-12-03T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "15"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1945-08-15T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "56"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1945-08-15T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "26"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1950-03-06T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "52"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1950-03-06T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "21"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1951-11-06T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "21"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1951-11-06T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "53"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1955-06-09T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "63"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1955-06-09T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "28"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1959-10-27T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "74"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1959-10-27T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "26"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1964-11-03T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "29"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1964-11-03T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "55"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1966-04-21T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "58"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1966-04-21T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "28"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1970-07-02T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "62"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1970-07-02T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "28"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1974-03-12T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "23"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1974-03-12T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "60"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1974-10-29T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "66"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1974-10-29T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "28"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1979-05-15T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "68"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1979-05-15T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "23"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1983-06-22T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "62"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1983-06-22T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "28"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1987-06-25T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "44"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1987-06-25T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "61"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1992-05-06T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "65"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("1992-05-06T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "56"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1997-05-14T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "47"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("1997-05-14T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "122"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("2001-06-20T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "119"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("2001-06-20T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "43"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("2005-05-17T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "129"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("2005-05-17T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "41"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("2010-05-25T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "25"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("2010-05-25T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "149"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("2015-05-27T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "20"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("2015-05-27T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "197"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("2017-06-21T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "20"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("2017-06-21T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "213"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
    ("2019-12-19T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "19"^^<http://www.w3.org/2001/XMLSchema#integer> "Johns"@en)
    ("2019-12-19T00:00:00Z"^^<http://www.w3.org/2001/XMLSchema#dateTime> "229"^^<http://www.w3.org/2001/XMLSchema#integer> "women"@en)
  }
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
