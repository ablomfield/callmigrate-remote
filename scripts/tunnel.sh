#!/bin/bash

settingsyaml="/opt/callmigrate/settings.yaml"
tunnelhost=$(yq eval ".Tunnels.TunnelHost" < "$settingsyaml")
tunneluser=$(yq eval ".Tunnels.TunnelUser" < "$settingsyaml")
ucmenable=$(yq eval ".Tunnels.UCMEnable" < "$settingsyaml")
cucenable=$(yq eval ".Tunnels.CUCEnable" < "$settingsyaml")
ccxenable=$(yq eval ".Tunnels.CCXEnable" < "$settingsyaml")

if [[ $ucmenable -eq "true" ]]; then
   echo Enabling UCM Proxy
   ucmport=$(yq eval ".Tunnels.UCMPort" < "$settingsyaml")
   ucmhost=$(yq eval ".Tunnels.UCMHost" < "$settingsyaml")
   /usr/bin/autossh -M 0 -o "ServerAliveInterval 30" -o "ServerAliveCountMax 3" -NR $ucmport:$ucmhost:8443 $tunneluser@$tunnelhost &
fi

if [[ $cucenable -eq "true" ]]; then
   echo Enabling CUC Proxy
   cucport=$(yq eval ".Tunnels.CUCPort" < "$settingsyaml")
   cuchost=$(yq eval ".Tunnels.CUCHost" < "$settingsyaml")
   /usr/bin/autossh -M 0 -o "ServerAliveInterval 30" -o "ServerAliveCountMax 3" -NR $cucport:$cuchost:8443 $tunneluser@$tunnelhost &
fi

if [[ $ccxenable -eq "true" ]]; then
   echo Enabling CCX Proxy
   ccxport=$(yq eval ".Tunnels.CCXPort" < "$settingsyaml")
   ccxhost=$(yq eval ".Tunnels.CCXHost" < "$settingsyaml")
   /usr/bin/autossh -M 0 -o "ServerAliveInterval 30" -o "ServerAliveCountMax 3" -NR $ccxport:$ccxhost:8443 $tunneluser@$tunnelhost &
fi
