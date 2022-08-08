# Terminals

Currently Supported IDEs:
- [PhpStorm](#PhpStorm)
- [VS Code](#VS%20Code)

## PhpStorm

This configuration will likely work on any of the IntelliJ-based IDEs, but PHPStorm makes the most sense for UCRM Plugin
development.

Navigate to the correct area of settings:

```
File | Settings | Tools | Terminal
```

_The below options append the following to the `PATH` environment variable in any newly executed Terminal in PhpStorm._
- `{PROJECT_DIR}\bin`
- `{PROJECT_DIR}\.spaethtech\bin`
- `{PROJECT_DIR}\.spaethtech\vendor\bin`
- `{PROJECT_DIR}\vendor\bin`

Choose one of the following, depending upon your preferred terminal.

> _**IMPORTANT**: Currently, PhpStorm's Terminal settings are application-wide and configuring any of the following
> will apply to ALL projects._
>
> _Due to this limitation, the commands below include a fallback in the case of a missing terminal folder or script._

## CMD
Shell Path
```
cmd /k .terminals\cmd.bat || cls
```

## PowerShell 7

> _**NOTE:** Newer versions of PhpStorm occasionally issue a terminal warning if you attempt to use a PowerShell version
> prior to v3._

Available at [PowerShell 7](https://docs.microsoft.com/en-us/powershell/scripting/install/installing-powershell-on-windows?view=powershell-7.2)

Shell Path
```
pwsh -NoExit -Command ".terminals\powershell.ps1 || cls"
```

## Cmder

Check out _[Cmder](https://github.com/cmderdev/cmder) for the download and instructions._

> **IMPORTANT** <br/>
> - Be certain to have CMDER_ROOT set in your Environment Variables
> - Also, to eliminate any issues, do NOT include any spaces in the path to Cmder.

Shell Path
```
cmd /k .terminals\cmder.cmd || cls && %CMDER_ROOT%\vendor\init.bat
```

## Git Bash

Included with the [Git for Windows](https://gitforwindows.org/) installation.

Shell Path
```
C:\Program Files\Git\bin\bash.exe --rcfile .terminals/.bashrc
```

## VS Code

Include the following JSON in your User Settings
> Ctrl+Shift+P | Preferences: Open User Settings (JSON)

```json
{
    "terminal.integrated.profiles.windows": {
        "Command Prompt": {
            "path": [ "${Env:WinDir}\\System32\\cmd.exe" ],
            "args": [ "/k", ".terminals\\cmd.bat || cls" ],
            "icon": "terminal-cmd"
        },
        "PowerShell": {
            "source": "PowerShell",
            "args": [ "-NoExit", "-Command", ".terminals\\powershell.ps1 || cls" ],
            "icon": "terminal-powershell"
        },
        "Cmder": {
            "path": [ "${env:windir}\\System32\\cmd.exe" ],
            "args": [ "/k", ".terminals\\cmder_vscode.cmd || cls && %CMDER_ROOT%\\vendor\\bin\\vscode_init.cmd" ]
        },
        "Git Bash": {
            "source": "Git Bash",
            "args": [ "--rcfile", ".terminals/.bashrc" ]
        }
    }
}
```

Then you can include the following in either the same User Settings file above, or in your Workspace Settings for each individual project.

> Ctrl+Shift+P | Preferences: Open Workspace Settings (JSON)

Choosing the Terminal you want from the options above

```json
{
    "terminal.integrated.defaultProfile.windows": "PowerShell"
}
```
