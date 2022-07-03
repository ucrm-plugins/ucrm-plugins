# Set the base path from the project.
$base = $pwd.Path

# Add any relative paths we want added here...
$paths = @(
    "bin",
    "vendor\bin"
)

# Loop through each relative path, make it absolute, and append it to the PATH environment variable...
foreach ($path in $paths)
{
    $Env:PATH += (";$base\$path")
}
