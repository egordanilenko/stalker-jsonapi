This package implements [a TVIP JSON Middleware API](http://wiki.tvip.ru/en/tvip_json_middleware_api/1) protocol for Stalker Middleware
 
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
    $stalker_path='/var/www/stalker_portal/';
    $staler_host= $_SERVER['HTTP_HOST'];     
```
6) if your configuration have difference with default values, please make ini configuration
 ```
touch /etc/stalker_jsonapi.in
```
and redefine config values for 
```
stalker_host
stalker_path
```