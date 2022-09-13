#!/bin/bash

echo "Disabling IPv6..."

# The list of interfaces for which to disable IPv6.
# NOTE: This can be set from the Vagrantfile via the `env` hash.
IFS=', ' read -r -a interfaces <<< "$IPV6_DISABLE"

# Remove any existing entries.
sed -i -E '/net\.ipv6\.conf\.(\w+)\.disable_ipv6\s*=\s*(0|1)/d' /etc/sysctl.conf

# Loop through each provided interface...
for i in "${interfaces[@]}"
do
    # ...and append the appropriate line to the kernel parameters!
    echo "net.ipv6.conf.$i.disable_ipv6 = 1" >> /etc/sysctl.conf
done

# Reload kernel parameters
sysctl -p
