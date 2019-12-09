{
  "logger"  : {
    "name"  : "DOKU_API",
    "path"  : "tmp/log/dokuapi.log",
    "count" : 90,
    "level" : 100,
    "stderr": 100
  },
  "db": {
    "host"   : "127.0.0.1",
    "port"   : "3306",
    "name"   : "dokuapp",
    "charset": "utf8",
    "user"   : "root",
    "pass"   : "password"
  },
  "routing": {
    "default": "Burdock\\DokuApi\\Controller\\NotFoundController",
    "debug"  : "Burdock\\DokuApi\\Controller\\DebugController"
  }
}