#!/usr/bin/env pwsh

# PowerShell script to run PHPUnit tests with Docker
# This script provides better error handling and cross-platform compatibility

param(
    [Parameter(ValueFromRemainingArguments=$true)]
    [string[]]$PhpUnitArgs
)

# Colors for output
$Red = "`e[91m"
$Green = "`e[92m"
$Blue = "`e[94m"
$Yellow = "`e[93m"
$Reset = "`e[0m"

# Function to print colored output
function Write-Status {
    param([string]$Message)
    Write-Host "$Blue[INFO]$Reset $Message"
}

function Write-Success {
    param([string]$Message)
    Write-Host "$Green[SUCCESS]$Reset $Message"
}

function Write-Error {
    param([string]$Message)
    Write-Host "$Red[ERROR]$Reset $Message"
}

function Write-Warning {
    param([string]$Message)
    Write-Host "$Yellow[WARNING]$Reset $Message"
}

# Function to check if Docker is running
function Test-DockerRunning {
    try {
        $null = docker info 2>$null
        return $true
    }
    catch {
        return $false
    }
}

# Function to check Docker Compose availability
function Get-DockerComposeCommand {
    try {
        $null = docker-compose version 2>$null
        return "docker-compose"
    }
    catch {
        try {
            $null = docker compose version 2>$null
            return "docker compose"
        }
        catch {
            return $null
        }
    }
}

# Function to build Docker image
function Build-TestImage {
    Write-Status "Building test image..."
    
    try {
        $buildArgs = @(
            "build",
            "-f", "docker/test/Dockerfile",
            "-t", "turahe-core-test",
            "."
        )
        
        $process = Start-Process -FilePath "docker" -ArgumentList $buildArgs -Wait -PassThru -NoNewWindow
        
        if ($process.ExitCode -ne 0) {
            throw "Docker build failed with exit code $($process.ExitCode)"
        }
        
        Write-Success "Test image built successfully"
        return $true
    }
    catch {
        Write-Error "Failed to build test image: $($_.Exception.Message)"
        return $false
    }
}

# Function to run tests
function Run-Tests {
    Write-Status "Running tests..."
    
    try {
        $runArgs = @(
            "run",
            "--rm",
            "turahe-core-test",
            "php",
            "vendor/bin/phpunit"
        )
        
        # Add PHPUnit arguments if provided
        if ($PhpUnitArgs) {
            $runArgs += $PhpUnitArgs
        }
        
        $process = Start-Process -FilePath "docker" -ArgumentList $runArgs -Wait -PassThru -NoNewWindow
        
        return $process.ExitCode
    }
    catch {
        Write-Error "Failed to run tests: $($_.Exception.Message)"
        return 1
    }
}

# Function to clean up
function Remove-TestImage {
    Write-Status "Cleaning up test image..."
    
    try {
        $null = docker rmi turahe-core-test 2>$null
        Write-Success "Test image cleanup completed"
    }
    catch {
        Write-Warning "Failed to remove test image: $($_.Exception.Message)"
    }
}

# Function to stop any running containers
function Stop-Containers {
    Write-Status "Stopping any running containers..."
    
    try {
        # Check if docker-compose.yml exists and stop containers
        if (Test-Path "docker-compose.yml") {
            $composeCmd = Get-DockerComposeCommand
            if ($composeCmd) {
                $null = & $composeCmd down 2>$null
                Write-Success "Containers stopped"
            }
        }
    }
    catch {
        Write-Warning "Failed to stop containers: $($_.Exception.Message)"
    }
}

# Main execution
function Main {
    Write-Status "Running PHPUnit tests with Docker..."
    
    # Check if Docker is running
    if (-not (Test-DockerRunning)) {
        Write-Error "Docker is not running. Please start Docker and try again."
        exit 1
    }
    
    Write-Success "Docker is running"
    
    # Build the test image
    if (-not (Build-TestImage)) {
        exit 1
    }
    
    # Run tests
    $exitCode = Run-Tests
    
    if ($exitCode -eq 0) {
        Write-Success "Tests completed successfully!"
    } else {
        Write-Error "Tests failed with exit code $exitCode"
    }
    
    # Clean up
    Remove-TestImage
    Stop-Containers
    
    exit $exitCode
}

# Handle script interruption
trap {
    Write-Warning "Script interrupted. Cleaning up..."
    Remove-TestImage
    Stop-Containers
    exit 1
}

# Run main function
Main
