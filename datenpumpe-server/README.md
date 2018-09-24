# Datenpumpe-Server

Creates content pages from predefined SPARQL queries.

Is deployed on www.wikimedia.de/extern/datenpumpe:
- List all: https://www.wikimedia.de/extern/datenpumpe/?list
- Random: https://www.wikimedia.de/extern/datenpumpe/


## Queries

Queries are defined in `queries.php`.

Each query consists of:
- Title
- Type
- SPARQL


### Title
Is shown at the top of the page, shouldn't be too long.


### Type

Can be one of:

- `singleValue`  
A big number or text value (first row/column of results).

- `table`  
  A table of data.

- `map`  
  A map with layer support (use ?layer in SELECT).

- `images`  
  Image grid with text captions, use ?image in SELECT for the image URL.

- `embed`  
  Shows any output dircetly from query.wikimedia.org, make shure to include
  a #defaultView comment (e.g. #defaultView:BubbleChart)


### SPARQL
Please test on query.wikidata.org.
