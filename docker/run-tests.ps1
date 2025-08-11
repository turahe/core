#!/usr/bin/env pwsh

# PowerShell script to run tests with full Docker Compose environment
# This script sets up MySQL, Redis, and the app container for integration tests

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

# Function to wait for service to be ready
function Wait-ForService {
    param(
        [string]$ServiceName,
        [string]$CheckCommand,
        [int]$MaxAttempts = 30
    )
    
    Write-Status "Waiting for $ServiceName to be ready..."
    
    for ($attempt = 1; $attempt -le $MaxAttempts; $attempt++) {
        try {
            $null = Invoke-Expression $CheckCommand 2>$null
            Write-Success "$ServiceName is ready"
            return $true
        }
        catch {
            if ($attempt -eq $MaxAttempts) {
                Write-Error "$ServiceName failed to start after $MaxAttempts attempts"
                return $false
            }
            Start-Sleep -Seconds 2
        }
    }
    
    return $false
}

# Function to create test database
function New-TestDatabase {
    Write-Status "Creating test database..."
    
    try {
        $createDbCommand = "docker exec turahe-core-mysql mysql -u root -proot -e 'CREATE DATABASE IF NOT EXISTS turahe_core_testing;'"
        $null = Invoke-Expression $createDbCommand 2>$null
        
        if ($LASTEXITCODE -eq 0) {
            Write-Success "Test database created"
            return $true
        } else {
            throw "Failed to create test database"
        }
    }
    catch {
        Write-Error "Failed to create test database: $($_.Exception.Message)"
        return $false
    }
}

# Function to run tests
function Run-Tests {
    Write-Status "Running tests..."
    
    try {
        $composeCmd = Get-DockerComposeCommand
        
        $runArgs = @(
            "run",
            "--rm",
            "app",
            "php",
            "vendor/bin/phpunit"
        )
        
        # Add PHPUnit arguments if provided
        if ($PhpUnitArgs) {
            $runArgs += $PhpUnitArgs
        }
        
        $process = Start-Process -FilePath $composeCmd -ArgumentList $runArgs -Wait -PassThru -NoNewWindow
        
        return $process.ExitCode
    }
    catch {
        Write-Error "Failed to run tests: $($_.Exception.Message)"
        return 1
    }
}

# Function to clean up
function Stop-Services {
    Write-Status "Cleaning up containers..."
    
    try {
        $composeCmd = Get-DockerComposeCommand
        $null = & $composeCmd down --remove-orphans 2>$null
        Write-Success "Containers stopped and cleaned up"
    }
    catch {
        Write-Warning "Failed to stop services: $($_.Exception.Message)"
    }
}

# Main execution
function Main {
    Write-Status "Starting test environment setup..."
    
    # Check prerequisites
    if (-not (Test-DockerRunning)) {
        Write-Error "Docker is not running. Please start Docker and try again."
        exit 1
    }
    
    Write-Success "Docker is running"
    
    $composeCmd = Get-DockerComposeCommand
    if (-not $composeCmd) {
        Write-Error "Docker Compose is not available. Please install Docker Compose and try again."
        exit 1
    }
    
    Write-Success "Docker Compose is available ($composeCmd)"
    
    # Stop any existing containers
    Write-Status "Stopping existing containers..."
    & $composeCmd down --remove-orphans 2>$null
    
    # Build and start services
    Write-Status "Building and starting services..."
    $startArgs = @("up", "--build", "-d", "mysql", "redis")
    $process = Start-Process -FilePath $composeCmd -ArgumentList $startArgs -Wait -PassThru -NoNewWindow
    
    if ($process.ExitCode -ne 0) {
        Write-Error "Failed to start services"
        exit 1
    }
    
    # Wait for services to be ready
    if (-not (Wait-ForService "MySQL" "docker exec turahe-core-mysql mysqladmin ping -hlocalhost --silent")) {
        exit 1
    }
    
    if (-not (Wait-ForService "Redis" "docker exec turahe-core-redis redis-cli ping")) {
        exit 1
    }
    
    # Create test database
    if (-not (New-TestDatabase)) {
        exit 1
    }
    
    # Run tests
    $exitCode = Run-Tests
    
    if ($exitCode -eq 0) {
        Write-Success "Tests completed successfully!"
    } else {
        Write-Error "Tests failed with exit code $exitCode"
    }
    
    # Cleanup
    Stop-Services
    
    exit $exitCode
}

# Handle script interruption
trap {
    Write-Warning "Script interrupted. Cleaning up..."
    Stop-Services
    exit 1
}

# Run main function
Main
