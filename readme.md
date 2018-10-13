## Install
### Composer
```json
{
    "name": "...",
    "description": "...",
    "type": "application",
    "authors": [
        {
            "name": "...",
            "email": "..."
        }
    ],

    "repositories": [
        {
            "type":"git",
            "url":"https://github.com/TsSaltan/ts-framework"
        },        

        {
            "type":"git",
            "url":"https://github.com/TsSaltan/ts-framework-composer"
        }
    ],

    "require": {
        "php": ">=7.1",
        "tssaltan/ts-framework": "dev-master",
        "tssaltan/ts-framework-composer": "dev-master"
    },


    "scripts": {
        "post-install-cmd": [
            "tsframe\\Installer::installFramework"
        ],
        "post-update-cmd": [
            "tsframe\\Installer::installFramework"
        ]
    }
```

```bash
composer install
```