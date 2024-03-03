import http from "http";
import app from "./src/app.js"

const hostname = '127.0.0.1';
const port = process.env.API_PORT;

const server = http.createServer((req, res) => {
  res.statusCode = 200;
  res.setHeader('Content-Type', 'text/plain');
  res.end('Servidor ok');
});

server.listen(port, hostname, () => {
    app
    console.log(`Server running at http://${hostname}:${port}/`);
});