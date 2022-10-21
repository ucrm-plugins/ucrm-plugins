#!/usr/bin/env bash

#cspell:ignore windir winenv winpath

if grep -qi microsoft /proc/version
then
  # WSL
  :
else
  # Linux
  echo "This script is only meant to be run in WSL, exiting!"
  exit 0
fi

function join_by
{
  local d=${1-} f=${2-}
  if shift 2; then
    printf %s "$f" "${@/#/$d}"
  fi
}

function winenv()
{
    result="$(cmd.exe /c echo "%$1%")"
    echo "${result//[$'\t\r\n']}"
}

function winpath()
{
    local path=$1
    path=${path//:/}
    path=${path//\\//}
    path=${path,,}

    local arr element
    IFS="/" read -r -a arr <<< "$path"

    declare -a parts=( "/mnt" )

    for element in "${arr[@]}"
    do
        found="false"

        # NOTE: No longer need to quote the path parts, as 'test -e' evaluates correctly!
        # if [[ "$element" =~ \ |\' ]]
        # then
        #     element="'$element'"
        # fi

        # IF the current path part exists, THEN append it to the final path...
        current="$(join_by / "${parts[@]}")/$element"
        if [[ -e "$current" ]]
        then
            parts+=( "$element" )
            found="true"
        fi

        # cspell:ignore fsutil
        # NOTE: On WSL, the default is case-insensitive and really should not need to be modified for this function.
        # This is just here on the off chance the user has enabled Windows case-sensitivity on the current path using:
        # > fsutil.exe file SetCaseSensitiveInfo C:\folder\path enable

        # IF the path was invalid, THEN perform some checks on the casing...
        if [[ "$found" != "true" ]]
        then
            # shellcheck disable=SC2001
            first="$(echo "$element" | sed -e "s/\b\(.\)/\u\1/")"
            current="$(join_by / "${parts[@]}")/$first"
            if [ -e "$current" ]
            then
                parts+=( "$first" )
                found="true"
            fi

            # shellcheck disable=SC2001
            upper="$(echo "$element" | sed -e "s/\b\(.\)/\u\1/g")"
            current="$(join_by / "${parts[@]}")/$upper"
            if [ -e "$current" ]
            then
                parts+=( "$upper" )
                found="true"
            fi

            # shellcheck disable=SC2001
            all="$(echo "$element" | sed -e "s/[a-z]/\u&/g")"
            #all="${element^^}"
            current="$(join_by / "${parts[@]}")/$all"
            if [ -e "$current" ]
            then
                parts+=( "$all" )
                found="true"
            fi
        fi

        [[ "$found" != "true" ]] && return 1

    done

    join_by / "${parts[@]}"
    return 0
}

WINDIR="$(winenv WINDIR)"
HOSTS_FILE="$(winpath "$WINDIR/System32/drivers/etc/hosts")"

exit

# Determines if the provided IPv4 address is valid!
# @param string $ip
# @return 0 = true, 1 = false
function is_valid_ipv4()
{
    local arr element
    IFS=. read -r -a arr <<< "$1"                       # convert ip string to array
    [[ ${#arr[@]} != 4 ]] && return 1                   # doesn't have four parts

    for element in "${arr[@]}"; do
        [[ $element =~ ^[0-9]+$ ]]       || return 1    # non numeric characters found
        [[ $element =~ ^0[1-9]+$ ]]      || return 1    # 0 not allowed in leading position if followed by other digits,
                                                        # to prevent it from being interpreted as on octal number
        ((element < 0 || element > 255)) && return 1    # number out of range
    done

    return 0
}

function is_valid_hostname()
{
    [[ ${#1} -lt   1 ]] && return 1
    [[ ${#1} -gt 255 ]] && return 1

    local arr element
    IFS=. read -r -a arr <<< "$1"                       # convert hostname string to array

    [[ ${#arr[@]} -lt  1 ]] && return 1                 # doesn't have any octets
    [[ ${#arr[@]} -gt 63 ]] && return 1                 # has more than 63 octets
    for element in "${arr[@]}"; do
        [[ $element =~ ^[a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9]$ ]] || return 1
    done

    return 0
}




function update_hosts()
{
    if [ $# -ne 2 ]
    then
        echo "update_hosts(ip, hostname) missing expected parameters"
        exit 1
    fi

    ip=$1
    hostname=$2

    #readarray -t lines < $HOSTS_FILE
    declare -a lines
    IFS=$'\r\n' GLOBIGNORE='*' command eval 'lines=($(cat $HOSTS_FILE))'

    found="false"
    updated="false"
    declare -a newLines

    for e in "${lines[@]}"
    do
        modified=""

        if [[ $e =~ ^[[:blank:]]*([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})[[:blank:]]+([^[:blank:]]*).*$ ]]
        then
            _ip="${BASH_REMATCH[1]}"
            _hostname="${BASH_REMATCH[2]}"

            #echo "'$_ip' '$ip'"
            #echo "'$_hostname' '$hostname'"

            if [ "$_ip" == "$ip" ] && [ "$_hostname" == "$hostname" ]
            then
                # Both IP and Hostname match!
                found="true"
                modified=$e
            fi

            if [ "$_ip" != "$ip" ] && [ "$_hostname" == "$hostname" ]
            then
                # Hostname matches, but not IP!
                found="true"

                #ipRegex="^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)(\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)){3}$"

                modified=$(echo "$e" | sed -r "s/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/$ip/")
                echo "$modified"
            fi


        fi

        if [[ "$modified" != "" ]]
        then
            updated="true"
            #newLines+=("$modified")
            newLines=("${newLines[@]}" "$modified")
            #echo "$modified"
        else
            #newLines+=("$e")
            newLines=("${newLines[@]}" "$e")
        fi

    done

    if [[ "$found" == "false" ]]
    then
        updated="true"
        #newLines+=("$ip $hostname")
        newLines=("${newLines[@]}" "$ip $hostname")
    fi

    #echo "${newLines[@]}"

}



#update_hosts "127.0.0.2" "site1.local"


#echo "${entries[@]}"
