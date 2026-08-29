#Requires -Version 5.1

$ErrorActionPreference = "Stop"

function Test-Administrator {
    $identity = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = [Security.Principal.WindowsPrincipal]::new($identity)
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

function Get-WslDistros {
    $output = & wsl.exe --list --quiet 2>$null
    if ($LASTEXITCODE -ne 0) {
        return @()
    }

    # Some Windows versions return UTF-16 text containing NUL characters.
    return @($output | ForEach-Object { ($_ -replace "`0", "").Trim() } | Where-Object { $_ })
}

function Show-Distros {
    $distros = @(Get-WslDistros)
    if ($distros.Count -eq 0) {
        Write-Host "No WSL distributions are installed."
    }
    else {
        Write-Host "Installed WSL distributions:"
        $distros | ForEach-Object { Write-Host "  - $_" }
    }
    return $distros
}

function Remove-OneDistro {
    $distros = @(Show-Distros)
    if ($distros.Count -eq 0) { return }

    $name = Read-Host "Enter the exact distribution name to reset"
    if ($name -notin $distros) {
        Write-Warning "'$name' is not in the installed distribution list. Nothing was deleted."
        return
    }

    Write-Warning "This permanently deletes every file and program inside '$name'."
    $confirmation = Read-Host "Type the exact distribution name again to continue"
    if ($confirmation -cne $name) {
        Write-Host "Cancelled. Nothing was deleted."
        return
    }

    & wsl.exe --shutdown
    & wsl.exe --unregister $name
    if ($LASTEXITCODE -ne 0) { throw "Could not unregister '$name'." }
    Write-Host "'$name' was reset. WSL2 is still installed." -ForegroundColor Green
}

function Remove-WslCompletely {
    $distros = @(Show-Distros)
    Write-Warning "Complete removal permanently deletes every selected WSL distribution."
    Write-Warning "Back up your Linux files before continuing."
    $confirmation = Read-Host "Type REMOVE WSL to continue"
    if ($confirmation -cne "REMOVE WSL") {
        Write-Host "Cancelled. Nothing was changed."
        return
    }

    & wsl.exe --shutdown 2>$null

    foreach ($distro in $distros) {
        Write-Host "Unregistering '$distro'..."
        & wsl.exe --unregister $distro
        if ($LASTEXITCODE -ne 0) { throw "Could not unregister '$distro'." }
    }

    Write-Host "Trying the modern WSL uninstall command (if supported)..."
    & wsl.exe --uninstall 2>$null

    Write-Host "Disabling Windows Subsystem for Linux..."
    Disable-WindowsOptionalFeature -Online -FeatureName Microsoft-Windows-Subsystem-Linux -NoRestart | Out-Null

    Write-Host "Disabling Virtual Machine Platform..."
    Disable-WindowsOptionalFeature -Online -FeatureName VirtualMachinePlatform -NoRestart | Out-Null

    Write-Host "WSL2 components were removed or disabled. Restart Windows to finish." -ForegroundColor Green
}

if (-not (Test-Administrator)) {
    throw "Run PowerShell as administrator, then run this script again."
}

Write-Host "WSL2 Classroom Cleanup"
Write-Host "1. List installed distributions"
Write-Host "2. Reset one distro only (keep WSL2 installed)"
Write-Host "3. Remove WSL2 completely"
Write-Host "4. Cancel"

switch (Read-Host "Choose 1, 2, 3, or 4") {
    "1" { [void](Show-Distros) }
    "2" { Remove-OneDistro }
    "3" { Remove-WslCompletely }
    default { Write-Host "Cancelled. Nothing was changed." }
}
