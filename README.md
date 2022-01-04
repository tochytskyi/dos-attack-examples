# dos-attack-examples
Test couple of possible dos attacks via 2 containers: client (attacker) and server (defender)

## Attacks

```shell
docker build -f Dockerfile.attacker -t dos-attacker ./attacker
```

### Slowloris
Send hanging GET requests to http://dos-server
```shell
docker run -it --rm --name dos-attacker dos-server php /usr/scripts/slowloris.php get http://dos-server attacker-host
```

### Http flood
Send 100 async GET requests to http://dos-server until exit container
```shell
docker run -it --rm dos-attacker php /usr/scripts/http_flood.php http://dos-server 100
```

## Protection

### Nginx protection from slowloris
Protections details - https://www.nginx.com/blog/mitigating-ddos-attacks-with-nginx-and-nginx-plus/

Nginx conf file: `./server/conf/nginx/default.conf`

### Nginx protection from http flood
The simplest way is to block IP with subnet
```
location / {
    deny 192.168.1.0/24;
}
```

Or more common way to use WAF (Web application firewall). Example - https://www.cloudflare.com/lp/ppc/waf-x

