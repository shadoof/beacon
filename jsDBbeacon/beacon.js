
var loki = require('lokijs');

// var beaconRecord = TAFFY([{
// "mode": 0,
// "categ1": 0,
// "categ2": 0,
// "categ3": 0,
// "categ4": 0,
// "state1": 0,
// "state2": 0,
// "state3": 0,
// "state4": 0
// }]);

var beaconState = new loki('beacon.json');

console.log();

const http = require('http');

const hostname = '127.0.0.1';
const port = 8888;

const server = http.createServer((req, res) => {
  res.statusCode = 200;
  res.setHeader('Content-Type', 'text/plain');
  res.end('Hello World\n' + beaconRecord().first().mode);
});

server.listen(port, hostname, () => {
  console.log(`Server running at http://${hostname}:${port}/`);
});
