# Set the base path from the project.
$ProjectDir = Resolve-Path -Path "$PSScriptRoot\.."

# Add any relative paths we want added here...
$paths = @(
".php\bin",
"bin"
#"vendor\bin"
)

# Loop through each relative path, make it absolute, and append it to the PATH environment variable...
foreach ($path in $paths)
{
    #$Env:PATH += (";$ProjectDir\$path")
    $Env:PATH = "$ProjectDir\$path;$Env:Path" # Prepend for precendence
}
