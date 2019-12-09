# burdock-php-dokuapi

## Setup

```bash
mkidr -p ${DOKUWIKI_ROOT}/api
mkdir -p ${DOKUWIKI_ROOT}/api/Controller
mkdir -p ${DOKUWIKI_ROOT}/api/Model
cd ${DOKUWIKI_ROOT}/api
composer require burdock/php-dokuwapi
copy ${DOKUWIKI_ROOT}/api/vendor/burdock/php-dokuapi/src/api.php ${DOKUWIKI_ROOT}/lib/exe/
copy ${DOKUWIKI_ROOT}/api/vendor/burdock/php-dokuapi/src/config.json.tpl ${DOKUWIKI_ROOT}/api/config.json
```

## Edit Configrations

## config.json

Adjust config.json for your environments.

## composer.json

Mainly setting for class and directory mappings. 
