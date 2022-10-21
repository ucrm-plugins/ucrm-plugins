
$regex = "^\s*(?:(?<ip>\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\s+(?<hostname>[^#\s]*))?(?:\s*#?\s*(?<comment>.*))$"

$defaultFile = $Env:WinDir + "\System32\drivers\etc\hosts"

Function Get-HostsEntry {

    [CmdletBinding()]
    param (
        [Parameter(Mandatory = $false,
            HelpMessage = "Error from computer.")]
        [string]$file = $defaultFile,

        [Parameter(Mandatory = $false,
            HelpMessage = "Error from computer.")]
        [string]$hostname,

        [Parameter(Mandatory = $false,
            HelpMessage = "Environment that failed. (Test, Production, Course, Acceptance...)")]
        [string]$ip
    )

    BEGIN {

    }

    PROCESS {

        $c = Get-Content $file

        $entries = @()

        foreach ($line in $c) {
            $results = [regex]::Matches($line, $regex)

            if ($results[0].Groups["ip"].Success -and $results[0].Groups["hostname"].Success) {
                if ($results[0].Groups["ip"].Value -eq $ip) {
                    $entries += $results[0].Groups["hostname"].Value
                }
            }
        }

        return $entries
    }

    END {

    }
}



function Get-HostsHostname([string]$filename, [string]$ip) {
    $c = Get-Content $filename
    $hostnames = @()

    foreach ($line in $c) {
        $results = [regex]::Matches($line, $regex)

        if ($results[0].Groups["ip"].Success -and $results[0].Groups["hostname"].Success) {
            if ($results[0].Groups["ip"].Value -eq $ip) {
                $hostnames += $results[0].Groups["hostname"].Value
            }
        }
    }

    return $hostnames
}

function Get-HostsIp([string]$filename, [string]$hostname) {
    $c = Get-Content $filename
    $ips = @()

    foreach ($line in $c) {
        $results = [regex]::Matches($line, $regex)

        if ($results[0].Groups["ip"].Success -and $results[0].Groups["hostname"].Success) {
            if ($results[0].Groups["hostname"].Value -eq $hostname) {
                $ips += $results[0].Groups["ip"].Value
            }
        }
    }

    return $ips
}





function Remove-HostsEntryByHostname([string]$filename, [string]$hostname) {
    $c = Get-Content $filename
    #$hostnames = @()
    $updatedLines = @()
    $success = $false

    foreach ($line in $c) {
        $results = [regex]::Matches($line, $regex)

        if ($results[0].Groups["ip"].Success -and $results[0].Groups["hostname"].Success) {
            if ($results[0].Groups["hostname"].Value -eq $hostname) {
                #$hostnames += $results[0].Groups["hostname"].Value
                $success = $true
                continue;
            }
        }

        $updatedLines += $line
    }

    Clear-Content $filename
    foreach ($line in $updatedLines) {
        $line | Out-File -encoding ASCII -append $filename
    }

    return $success
}


#get-hostnames $file 127.0.0.1
$ips = @(Get-HostsIp $file "uisp")
Remove-HostsEntryByHostname $file "uisp.dev"

$ips

exit


# function add-host([string]$filename, [string]$ip, [string]$hostname)
# {
# 	remove-host $filename $hostname
# 	$ip + "`t`t" + $hostname | Out-File -encoding ASCII -append $filename
# }

# function remove-host([string]$filename, [string]$hostname) {
# 	$c = Get-Content $filename
# 	$newLines = @()

# 	foreach ($line in $c) {
# 		$bits = [regex]::Split($line, "\t+")
# 		if ($bits.count -eq 2) {
# 			if ($bits[1] -ne $hostname) {
# 				$newLines += $line
# 			}
# 		} else {
# 			$newLines += $line
# 		}
# 	}

# 	# Write file
# 	Clear-Content $filename
# 	foreach ($line in $newLines) {
# 		$line | Out-File -encoding ASCII -append $filename
# 	}
# }

# function print-hosts([string]$filename) {
# 	$c = Get-Content $filename

# 	foreach ($line in $c) {
# 		$bits = [regex]::Split($line, "\t+")
# 		if ($bits.count -eq 2) {
# 			Write-Host $bits[0] `t`t $bits[1]
# 		}
# 	}
# }

# try {
# 	if ($args[0] -eq "add") {

# 		if ($args.count -lt 3) {
# 			throw "Not enough arguments for add."
# 		} else {
# 			add-host $file $args[1] $args[2]
# 		}

# 	} elseif ($args[0] -eq "remove") {

# 		if ($args.count -lt 2) {
# 			throw "Not enough arguments for remove."
# 		} else {
# 			remove-host $file $args[1]
# 		}

# 	} elseif ($args[0] -eq "show") {
# 		print-hosts $file
# 	} else {
# 		throw "Invalid operation '" + $args[0] + "' - must be one of 'add', 'remove', 'show'."
# 	}
# } catch  {
# 	Write-Host $error[0]
# 	Write-Host "`nUsage: hosts add <ip> <hostname>`n       hosts remove <hostname>`n       hosts show"
# }
