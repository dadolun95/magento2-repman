# Repman - Magento2 integration module <img src="https://avatars.githubusercontent.com/u/168457?s=40&v=4" alt="magento" /> 

[![Latest Stable Version](https://poser.pugx.org/dadolun95/magento2-repman/v/stable)](https://packagist.org/packages/dadolun95/magento2-repman)

## Features
Syncronization functionality for Repman - Magento2 integration.
This module integrates your Magento2 site with Repman allowing you to supply your magento module's directly from your e-commerce.

## Compatibility
Fully tested and working on Magento CE(EE) 2.4.4, 2.4.5, 2.4.6

## Installation
You can install this module adding it on app/code folder or with composer.
```
composer require dadolun95/magento2-repman
```
Then you'll need to enable the module and update your database and files:
```
php bin/magento module:enable Dadolun_Repman
php bin/magento setup:upgrade
php bin/magento setup:di:compile
```

##### CONFIGURATION
You must enable the module from "Stores > Configurations > Dadolun > Repman > Integration management" section enabling the integration and adding your Repman Rest API Token.
After Token verification you'll be able to add your main repman organization uri and prefix for new generated organizations.

Create your downloadable products specifying:
- Repository name
- Repository Subscription end expression (empty for unlimited updates, strtotime string expression for limited subcription time ex: "+1 year")
- Repository Type
- Installation instructions (optional, will be added as INSTALL.txt file into zipped module download)

## Contributing
Contributions are very welcome. In order to contribute, please fork this repository and submit a [pull request](https://docs.github.com/en/free-pro-team@latest/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request).
