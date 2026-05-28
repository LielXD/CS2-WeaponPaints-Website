-------
# SUPPORT ISRAEL 🤍💙
-------

## Preview Website
link => https://cs2.lielxd.com/

## Link to the plugin (big credit to them)
link => https://github.com/Nereziel/cs2-WeaponPaints

## Donations
any donation would be appreciated.<br>
Skins trade link => https://steamcommunity.com/tradeoffer/new/?partner=1684190212&token=u_ofAuvN
<br>
Paypal => https://paypal.me/lielxd

### Please download the website from the releases section.

## Requirements
* Webserver
  * Apache - use .htaccess file included.<br><br>
     OR
  * Nginx - use this location block in .conf file<br>
  
     ```nginx
     location / {
          try_files $uri /index.php?path=$uri&$args;
     }
     ```
* PHP - make sure to enable in `php.ini` these:
     * extension_dir = "ext"
     * extension=curl
     * extension=pdo_mysql

## SteamID access whitelist

During setup, add SteamID64 values to `Allowed SteamID64 IDs` to limit website access to those Steam accounts. Leave it empty to allow every Steam login. Existing installs can add the same IDs to `$Website_AllowedSteamIDs` in `config.php`.

You ofc need a webhost to host the website.
