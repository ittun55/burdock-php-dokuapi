{
  "app": {
    "namespace": "Api",
    "root_dir": null
  },
  "logger"  : {
    "default": {
      "name"  : "DOKU_API",
      "path"  : "/var/log/dokuapi.log",
      "rotate": 90,
      "level" : 100,
      "stderr": 100
    }
  },
  "db": {
    "default": {
      "host"   : "127.0.0.1",
      "port"   : "3306",
      "name"   : "dbtest",
      "charset": "utf8",
      "user"   : "root",
      "pass"   : "password"
    }
  },
  "smtp": {
    "default": {
      "host"   : "smtp.burdock.io",
      "port"   : "587",
      "user"   : "user@burdock.io",
      "pass"   : "password"
    }
  },
  "routing": {
    "default": "Burdock\\DokuApi\\Controller\\NotFoundController",
    "debug"  : "Burdock\\DokuApi\\Controller\\DebugController",
    "one:start": "Api\\Controller\\OneController",
    "two:start": "Api\\Controller\\TwoController",
    "three:start": "Api\\Controller\\ThreeController"
  }
}