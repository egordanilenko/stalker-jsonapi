This package implements [TVIP JSON Middleware API](http://wiki.tvip.ru/en/tvip_json_middleware_api/1) protocol for Stalker Middleware

For quick install plugin into stalker:

1) Download package from github:
```
wget https://github.com/egordanilenko/stalker-jsonapi/archive/master.zip

```
2) Unzip package to any folder except /var/www
```
unzip master.zip
```

3) Make symlink to stalker folder near stalker_porta
 
```
ln -s /%anypath%/stalker-jsonapi-master/tvipapi/ /%stalker_common_directory%/tvipapi
```
4) For custom configuration see paragraph 5 and 6 at next toturial

For deploy this package on your system:

1) Make distribution: 
```
tar -zcvf jsonapi.tar.gz  --exclude .git --exclude "*.log" --exclude ".idea" jsonapi/
```

2) Transfer jsonapi.tar.gz to server where deployed Stalker Middleware, for example via scp
```
scp jsonapi.tar.gz yourname@stalker.example.com:/home/yourname
```

3) Untar your json.tar.gz near stalker main directory
```
tar -zxvf jsonapi.tar.gz -C /var/www/
```

4) Make symlink
```
ln -s /var/www/jsonapi/tvipapi /var/www/tvipapi
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
