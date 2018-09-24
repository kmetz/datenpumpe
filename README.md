# Datenpumpe

Code used for the "Datenwasserpumpe" installation by Wikimedia Deutschland.


## Datenpumpe-Server
Creates content pages from predefined SPARQL queries.

Is deployed on www.wikimedia.de/extern/datenpumpe:
- List all: https://www.wikimedia.de/extern/datenpumpe/?list
- Random: https://www.wikimedia.de/extern/datenpumpe/

Queries are defined in `queries.php`, see documentation there.


## Datenpumpe-Display
Runs on the installation itself on a Raspberry Pi 3 under Raspbian Stretch.

See datempumpe-display/README.md.
