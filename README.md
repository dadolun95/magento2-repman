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
![image](https://github.com/dadolun95/magento2-repman/assets/8927461/58a7d24e-b2b4-4ba6-9591-b6aededd0939)
![image](https://github.com/dadolun95/magento2-repman/assets/8927461/6f6913ff-7b34-41f1-9c34-f1be1053e6f8)
![image](https://github.com/dadolun95/magento2-repman/assets/8927461/316e6aec-b0eb-4fc5-9ead-54bf9b9c1979)

After Token verification you'll be able to add your main repman organization uri and prefix for new generated organizations.
![image](https://github.com/dadolun95/magento2-repman/assets/8927461/56fc50b3-cfdc-454f-a1a9-c5be0e8c09b3)

Create your downloadable products adding repman informations:
- Repository name
- Repository Subscription end expression (empty for unlimited updates, strtotime string expression for limited subcription time ex: "+1 year")
- Repository Type
- Installation instructions (optional, will be added as INSTALL.txt file into zipped module download)
![image](https://github.com/dadolun95/magento2-repman/assets/8927461/abab29e1-9022-4867-a9ac-e9c33bbf3e89)
- Remember to create an "empty" purchasable item
![image](https://github.com/dadolun95/magento2-repman/assets/8927461/f1d50644-3eac-45b1-87a0-09c8bd257d2c)

Each customer will be now able to see each downloadable purchased package on his account after purchase and successfull invoicing of the order
![image](https://github.com/dadolun95/magento2-repman/assets/8927461/1e2ce9f4-3e89-429e-b1f6-65c5fe8c2bf9)
The download link will give a zip file to the customer containing all module versions zipped and a INSTALL.txt file filled with data contained into product "Installation instructions" attribute.


## Contributing
Contributions are very welcome. In order to contribute, please fork this repository and submit a [pull request](https://docs.github.com/en/free-pro-team@latest/github/collaborating-with-issues-and-pull-requests/creating-a-pull-request).
