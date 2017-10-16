This package implements [TVIP JSON Middleware API](http://wiki.tvip.ru/en/tvip_json_middleware_api/1) protocol for Stalker Middleware

For quick install plugin into stalker:

1) Download package from github:
```
wget https://github.com/egordanilenko/stalker-jsonapi/raw/master/build/tvip-jsonapi-plugin.tgz

```
2) Unzip package to any folder except /var/www
```
tar -xzf tvip-jsonapi-plugin.tgz  
```

3) Make symlink to stalker folder near stalker_portal
 
```
ln -s  /%anypath%/tvip-jsonapi-plugin/tvipapi/ /%stalker_common_directory%/tvipapi
```

4) Make symlink on root path for simple path for stb
```
ln -s /%anypath%/tvip-jsonapi-plugin/tvipapi /var/www/tvipapi
```
5)  By default configuration use this variables:
```
    $stalker_path = '/var/www/stalker_portal/';
    $stalker_host = $_SERVER['HTTP_HOST'];     
```
6) if your configuration have difference with default values, please make ini configuration
 ```
touch /etc/stalker_jsonapi.ini
```
and redefine config values for 
```
stalker_host
stalker_path
```
7) If you use Stalker DVR, you need fix  dumpstream script:
```
mv /var/www/stalker_portal/storage/dumpstream /var/www/stalker_portal/storage/dumpstream.backup
ln -s  /%anypath%/tvip-jsonapi-plugin/dumpstream.py /var/www/stalker_portal/storage/dumpstream

```
for applying new dumpstream you need to restart dumpstream.

For connection from TVIP STB: use  Middleware API and http://<stalker_server>/ or http://<stalker_server>/stalker_portal. 

Device will search /tvipapi path after main URL
