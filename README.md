# TOURNEESDELIVRAISON FOR <a href="https://www.dolibarr.org">DOLIBARR ERP CRM</a>

## Features
Module de gestion de tournée de livraison pour Dolibarrr.


<!--
![Screenshot tourneesdelivraison](img/screenshot_tourneesdelivraison.png?raw=true "TourneesDeLivraison"){imgmd}
-->

Module permettant la gestion de tournées de livraison unique ou répété. Créer une liste de client (Tournée de livraison) correspondant à une tournée régulière. A chaque occurence de tournée, vous créer uen tournée unique (TourneeUnique) avec laquel vous gérer les commandes clients, la préparation de la tournée ainsi que la livraison.
Voius pouvez éditer une liste d'étiquettes pour préparation de commande, ainsi qu'un bordereau de tournée de livraison.



### Translations

Translations can be define manually by editing files into directories *langs*.

<!--
This module contains also a sample configuration for Transifex, under the hidden directory [.tx](.tx), so it is possible to manage translation using this service.

For more informations, see the [translator's documentation](https://wiki.dolibarr.org/index.php/Translator_documentation).

There is a [Transifex project](https://transifex.com/projects/p/dolibarr-module-template) for this module.
-->


<!--

Install
-------

### From the ZIP file and GUI interface

- If you get the module in a zip file (like when downloading it from the market place [Dolistore](https://www.dolistore.com)), go into
menu ```Home - Setup - Modules - Deploy external module``` and upload the zip file.


Note: If this screen tell you there is no custom directory, check your setup is correct:

- In your Dolibarr installation directory, edit the ```htdocs/conf/conf.php``` file and check that following lines are not commented:

    ```php
    //$dolibarr_main_url_root_alt ...
    //$dolibarr_main_document_root_alt ...
    ```

- Uncomment them if necessary (delete the leading ```//```) and assign a sensible value according to your Dolibarr installation

    For example :

    - UNIX:
        ```php
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = '/var/www/Dolibarr/htdocs/custom';
        ```

    - Windows:
        ```php
        $dolibarr_main_url_root_alt = '/custom';
        $dolibarr_main_document_root_alt = 'C:/My Web Sites/Dolibarr/htdocs/custom';
        ```

### From a GIT repository

- Clone the repository in ```$dolibarr_main_document_root_alt/tourneesdelivraison```

```sh
cd ....../custom
git clone git@github.com:gitlogin/tourneesdelivraison.git tourneesdelivraison
```

### <a name="final_steps"></a>Final steps

From your browser:

  - Log into Dolibarr as a super-administrator
  - Go to "Setup" -> "Modules"
  - You should now be able to find and enable the module



-->


Licenses
--------

### Main code

![GPLv3 logo](img/gplv3.png)

GPLv3 or (at your option) any later version.

See file COPYING for more information.

#### Documentation

All texts and readmes.

![GFDL logo](img/gfdl.png)
