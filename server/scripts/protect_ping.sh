#!/bin/sh

iptables -A INPUT --proto icmp -j DROP
iptables -L -n -v  [List Iptables Rules]